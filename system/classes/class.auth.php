<?php
class Auth {

    var $error = '';
    var $msg   = '';
    private $db;

    // Seguridad
    const MAX_ATTEMPTS    = 5;      // intentos antes de bloquear
    const LOCKOUT_MINUTES = 15;     // minutos de bloqueo
    const BCRYPT_COST     = 12;

    // Columnas permitidas para updates dinámicos
    private $allowedCols = ['email', 'password', 'active', 'password_recover', 'fname'];

    function __construct($DB_con)
    {
        $this->db = $DB_con;
    }

    // ── Helpers de seguridad ──
    private function getIP()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]);
    }

    // Verifica MD5 (legacy) o bcrypt
    private function verifyPassword($password, $hash)
    {
        // MD5 legacy: 32 caracteres hexadecimales
        if (strlen($hash) === 32 && ctype_xdigit($hash)) {
            return md5($password) === $hash;
        }
        // bcrypt
        return password_verify($password, $hash);
    }

    private function isLegacyHash($hash)
    {
        return strlen($hash) === 32 && ctype_xdigit($hash);
    }

    public function all()
    {
        global $crypt;
        $result = $this->db->prepare("SELECT * FROM " . PFX . "admins WHERE active = 1");
        $result->execute();
        $admins = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['id'] = $crypt->encrypt($row['id'], 'ADMIN');
            $admins[] = $row;
        }
        return $admins;
    }

    public function is_admin($id)
    {
        global $crypt;
        $id = $crypt->decrypt($id, 'ADMIN');
        $result = $this->db->prepare("SELECT id FROM " . PFX . "admins WHERE id = ?");
        $result->execute([$id]);
        if ($result->fetchColumn() == 1) {
            return true;
        }
        $this->error = "No such admin exists";
        return false;
    }

    public function details($id)
    {
        global $crypt;
        if ($this->is_admin($id)) {
            $id = $crypt->decrypt($id, 'ADMIN');
            $result = $this->db->prepare("SELECT * FROM " . PFX . "admins WHERE id = ?");
            $result->execute([$id]);
            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                $row['id'] = $crypt->encrypt($row['id'], 'ADMIN');
                // Nota: ya NO devolvemos la password re-hasheada.
                // La verificación de password se hace con verifyCurrentPassword()
                return $row;
            }
        }
        $this->error = "No such admin exists";
        return false;
    }

    // Verifica la contraseña actual de un admin (para cambio de contraseña)
    public function verifyCurrentPassword($id, $password)
    {
        global $crypt;
        $realId = $crypt->decrypt($id, 'ADMIN');
        $stmt = $this->db->prepare("SELECT password FROM " . PFX . "admins WHERE id = ?");
        $stmt->execute([$realId]);
        $hash = $stmt->fetchColumn();
        if (!$hash) return false;
        return $this->verifyPassword($password, $hash);
    }

    // Cambia la contraseña (siempre guarda en bcrypt)
    public function changePassword($id, $newPassword)
    {
        global $crypt;
        $realId  = $crypt->decrypt($id, 'ADMIN');
        $newHash = $this->hashPassword($newPassword);
        $stmt = $this->db->prepare("UPDATE " . PFX . "admins SET password = :pwd WHERE id = :id");
        $stmt->bindParam(':pwd', $newHash);
        $stmt->bindParam(':id', $realId);
        return $stmt->execute();
    }

    public function update($id, $col, $value)
    {
        global $crypt;
        if (!in_array($col, $this->allowedCols, true)) {
            $this->error = "Invalid column";
            return false;
        }
        if ($this->is_admin($id)) {
            $id = $crypt->decrypt($id, 'ADMIN');
            // Si actualizan la password por esta vía, hashearla
            if ($col === 'password') {
                $value = $this->hashPassword($value);
            }
            $result = $this->db->prepare("UPDATE " . PFX . "admins SET `$col` = :value WHERE id = :id");
            $result->bindParam(':value', $value);
            $result->bindParam(':id', $id);
            if ($result->execute()) {
                return true;
            }
            $this->error = "Error occurred";
            return false;
        }
        $this->error = "No such admin exists";
        return false;
    }

    public function is_loggedin()
    {
        if (isset($_SESSION['auth'], $_SESSION['curr_user'], $_SESSION['token'], $_SESSION['token_secret'])) {
            // Token basado en un secreto aleatorio guardado en sesión (no predecible)
            $expected = hash_hmac('sha256', $_SESSION['curr_user'], $_SESSION['token_secret']);
            if (hash_equals($expected, $_SESSION['token'])) {
                return true;
            }
        }
        return false;
    }

    public function updateve($email, $col, $value)
    {
        if (!in_array($col, $this->allowedCols, true)) {
            $this->error = "Invalid column";
            return false;
        }
        $email = trim($email);
        if ($col === 'password') {
            $value = $this->hashPassword($value);
        }
        $result = $this->db->prepare("UPDATE " . PFX . "admins SET `$col` = :value WHERE email = :email");
        $result->bindParam(':value', $value);
        $result->bindParam(':email', $email);
        if ($result->execute()) {
            return true;
        }
        $this->error = "Error occurred";
        return false;
    }

    public function login($email, $password)
    {
        global $crypt;
        $email = trim($email);
        $ip    = $this->getIP();

        // Buscar admin por email
        $stmt = $this->db->prepare("SELECT * FROM " . PFX . "admins WHERE email = :email AND active = 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Error genérico (no revelar si el email existe)
        if (!$admin) {
            $this->error = 'Invalid email or password';
            return false;
        }

        // ── Bloqueo por intentos ──
        // ── Bloqueo por intentos (mensaje genérico) ──
        if (!empty($admin['locked_until']) && strtotime($admin['locked_until']) > time()) {
            $this->error = 'Invalid email or password';
            return false;
        }

        // ── Verificar contraseña ──
        // ── Verificar contraseña ──
        if (!$this->verifyPassword($password, $admin['password'])) {
            $attempts = (int)$admin['login_attempts'] + 1;
            if ($attempts >= self::MAX_ATTEMPTS) {
                $lockUntil = date('Y-m-d H:i:s', time() + self::LOCKOUT_MINUTES * 60);
                $this->db->prepare("UPDATE " . PFX . "admins SET login_attempts = :a, locked_until = :lu WHERE id = :id")
                         ->execute([':a' => $attempts, ':lu' => $lockUntil, ':id' => $admin['id']]);
            } else {
                $this->db->prepare("UPDATE " . PFX . "admins SET login_attempts = :a WHERE id = :id")
                         ->execute([':a' => $attempts, ':id' => $admin['id']]);
            }
            // Opción B: mensaje siempre genérico
            $this->error = 'Invalid email or password';
            return false;
        }

        // ── Contraseña correcta ──

        // Migración automática MD5 → bcrypt
        if ($this->isLegacyHash($admin['password'])) {
            $newHash = $this->hashPassword($password);
            $this->db->prepare("UPDATE " . PFX . "admins SET password = :pwd WHERE id = :id")
                     ->execute([':pwd' => $newHash, ':id' => $admin['id']]);
        }

        // Resetear intentos y registrar login
        $this->db->prepare("UPDATE " . PFX . "admins SET login_attempts = 0, locked_until = NULL, last_login = NOW(), ip_address = :ip WHERE id = :id")
                 ->execute([':ip' => $ip, ':id' => $admin['id']]);

        // ── Crear sesión segura ──
        session_regenerate_id(true); // evita fijación de sesión

        $tokenSecret = bin2hex(random_bytes(32));
        $_SESSION['uid']          = $crypt->encrypt($admin['id'], 'ADMIN');
        $_SESSION['curr_user']    = $admin['email'];
        $_SESSION['token_secret'] = $tokenSecret;
        $_SESSION['token']        = hash_hmac('sha256', $admin['email'], $tokenSecret);
        $_SESSION['auth']         = true;
        $_SESSION['start']        = time();

        return true;
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        header("location: login.php");
        exit;
    }

    public function add($email, $password)
    {
        if ($this->is_new_admin($email)) {
            $hashed = $this->hashPassword($password);
            $add = $this->db->prepare("INSERT INTO " . PFX . "admins (`id`, `email`, `password`, `active`) VALUES (NULL, :email, :password, '1')");
            $add->bindParam(':email', $email);
            $add->bindParam(':password', $hashed);
            if ($add->execute()) {
                $this->msg = "Registered successfully";
                return $this->db->lastInsertId();
            }
            $this->error = "Oops something wrong happened. Please try later.";
            return false;
        }
        $this->error = "Admin already exists with that email";
        return false;
    }

    public function is_new_admin($email)
    {
        $result = $this->db->prepare("SELECT id FROM " . PFX . "admins WHERE email = ?");
        $result->execute([$email]);
        if ($result->fetchColumn() == 0) {
            return true;
        }
        $this->error = "Admin already exists";
        return false;
    }
}