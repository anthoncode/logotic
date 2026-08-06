<?php

class Customer
{

    public  $error = '';
    public  $msg   = '';
    private $db;

    // Configuración de seguridad
    const MAX_ATTEMPTS      = 3;
    const LOCKOUT_MINUTES   = 30;
    const LOCKOUT_DAY_MIN   = 1440; // 24 horas
    const SESSION_DURATION  = 172800; // 48 horas
    const BCRYPT_COST       = 12;
    const TWO_FA_EXPIRY_MIN = 10;

    public function __construct($DB_con)
    {
        $this->db = $DB_con;
    }

    // ══════════════════════════════════════════════
    // ── HELPERS INTERNOS
    // ══════════════════════════════════════════════

    private function getIP()
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function getUserAgent()
    {
        return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
    }

    private function logEvent($user_id, $email, $action, $details = null)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO " . PFX . "login_logs
                (user_id, email, ip_address, user_agent, action, details)
                VALUES (:uid, :email, :ip, :ua, :action, :details)
            ");
            $stmt->execute([
                ':uid'     => $user_id,
                ':email'   => $email,
                ':ip'      => $this->getIP(),
                ':ua'      => $this->getUserAgent(),
                ':action'  => $action,
                ':details' => $details,
            ]);
        } catch (Exception $e) {
            // Silencioso — no interrumpir el flujo por un log fallido
        }
    }

    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => self::BCRYPT_COST]);
    }

    private function verifyPassword($password, $hash)
    {
        // Soporte migración MD5 → bcrypt
        if (strlen($hash) === 32 && ctype_xdigit($hash)) {
            return md5($password) === $hash;
        }
        return password_verify($password, $hash);
    }

    private function needsRehash($hash)
    {
        return strlen($hash) === 32 && ctype_xdigit($hash);
    }

    private function rehashPassword($userId, $plainPassword)
    {
        $newHash = $this->hashPassword($plainPassword);
        $stmt = $this->db->prepare("UPDATE " . PFX . "users SET password = :hash WHERE id = :id");
        $stmt->execute([':hash' => $newHash, ':id' => $userId]);
    }

    // ══════════════════════════════════════════════
    // ── RATE LIMITING
    // ══════════════════════════════════════════════

    public function isLockedOut($email)
    {
        $ip = $this->getIP();

        // Limpiar intentos viejos (más de 24h)
        $this->db->prepare("
            DELETE FROM " . PFX . "login_attempts
            WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->execute();

        // Verificar si el usuario tiene locked_until activo
        $stmt = $this->db->prepare("
            SELECT locked_until FROM " . PFX . "users
            WHERE email = :email AND locked_until IS NOT NULL AND locked_until > NOW()
        ");
        $stmt->execute([':email' => $email]);
        $lock = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($lock) {
            $remaining = ceil((strtotime($lock['locked_until']) - time()) / 60);
            $this->error = "Account temporarily locked. Try again in {$remaining} minutes.";
            return true;
        }

        // Contar intentos fallidos recientes por IP
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM " . PFX . "login_attempts
            WHERE ip_address = :ip
              AND success = 0
              AND attempted_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ");
        $stmt->execute([':ip' => $ip]);
        $ipAttempts = (int)$stmt->fetchColumn();

        if ($ipAttempts >= self::MAX_ATTEMPTS * 3) {
            $this->error = "Too many failed attempts from your IP. Try again later.";
            $this->logEvent(null, $email, 'blocked', 'IP blocked: ' . $ip);
            return true;
        }

        return false;
    }

    private function recordAttempt($email, $success)
    {
        $ip = $this->getIP();
        $stmt = $this->db->prepare("
            INSERT INTO " . PFX . "login_attempts (ip_address, email, success)
            VALUES (:ip, :email, :success)
        ");
        $stmt->execute([':ip' => $ip, ':email' => $email, ':success' => $success ? 1 : 0]);

        if (!$success) {
            // Incrementar contador en usuario
            $stmt2 = $this->db->prepare("
                UPDATE " . PFX . "users
                SET login_attempts = login_attempts + 1
                WHERE email = :email
            ");
            $stmt2->execute([':email' => $email]);

            // Obtener total de intentos del usuario
            $stmt3 = $this->db->prepare("
                SELECT login_attempts FROM " . PFX . "users WHERE email = :email
            ");
            $stmt3->execute([':email' => $email]);
            $attempts = (int)$stmt3->fetchColumn();

            if ($attempts >= self::MAX_ATTEMPTS * 2) {
                // Bloqueo de 24 horas
                $until = date('Y-m-d H:i:s', time() + self::LOCKOUT_DAY_MIN * 60);
                $this->db->prepare("
                    UPDATE " . PFX . "users SET locked_until = :until WHERE email = :email
                ")->execute([':until' => $until, ':email' => $email]);
                $this->logEvent(null, $email, 'blocked', 'Locked 24h after ' . $attempts . ' attempts');
            } elseif ($attempts >= self::MAX_ATTEMPTS) {
                // Bloqueo de 30 minutos
                $until = date('Y-m-d H:i:s', time() + self::LOCKOUT_MINUTES * 60);
                $this->db->prepare("
                    UPDATE " . PFX . "users SET locked_until = :until WHERE email = :email
                ")->execute([':until' => $until, ':email' => $email]);
                $this->logEvent(null, $email, 'blocked', 'Locked 30min after ' . $attempts . ' attempts');
            }
        } else {
            // Reset intentos tras login exitoso
            $this->db->prepare("
                UPDATE " . PFX . "users
                SET login_attempts = 0, locked_until = NULL
                WHERE email = :email
            ")->execute([':email' => $email]);
        }
    }

    // ══════════════════════════════════════════════
    // ── CSRF
    // ══════════════════════════════════════════════

    public function generateCsrfToken()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function validateCsrfToken($token)
    {
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    // ══════════════════════════════════════════════
    // ── 2FA POR EMAIL
    // ══════════════════════════════════════════════

    public function send2FACode($userId, $email, $fname)
    {
        global $setting, $mailer;

        // Limpiar códigos anteriores
        $this->db->prepare("
            DELETE FROM " . PFX . "2fa_codes WHERE user_id = :uid
        ")->execute([':uid' => $userId]);

        $code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires = date('Y-m-d H:i:s', time() + self::TWO_FA_EXPIRY_MIN * 60);

        $stmt = $this->db->prepare("
            INSERT INTO " . PFX . "2fa_codes (user_id, code, expires_at, ip_address)
            VALUES (:uid, :code, :exp, :ip)
        ");
        $stmt->execute([
            ':uid'  => $userId,
            ':code' => password_hash($code, PASSWORD_BCRYPT),
            ':exp'  => $expires,
            ':ip'   => $this->getIP(),
        ]);

        // Enviar email
        $subject = "Your login verification code — " . $setting['site_name'];
        $message = '
        <html><head><meta charset="utf-8"></head>
        <body style="font-family:sans-serif;background:#0d0f1c;color:#f0f2ff;padding:2rem;">
            <div style="max-width:480px;margin:0 auto;background:#13152a;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:2rem;">
                <img src="' . $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon'] . '" width="50" style="display:block;margin:0 auto 1rem;">
                <h2 style="text-align:center;color:#d4ff00;margin:0 0 .5rem;">Verification Code</h2>
                <p style="text-align:center;color:#8b8fa8;margin:0 0 1.5rem;">Hi <strong style="color:#f0f2ff;">' . htmlspecialchars($fname) . '</strong>, use this code to complete your login:</p>
                <div style="text-align:center;font-size:2.5rem;font-weight:800;letter-spacing:.5rem;color:#d4ff00;background:rgba(212,255,0,.08);border:1px solid rgba(212,255,0,.2);border-radius:10px;padding:1rem;margin-bottom:1.5rem;">' . $code . '</div>
                <p style="text-align:center;color:#8b8fa8;font-size:.8rem;">This code expires in <strong>' . self::TWO_FA_EXPIRY_MIN . ' minutes</strong>.<br>If you did not request this, please ignore this email.</p>
            </div>
        </body></html>';

        // Enviar por la clase Mailer (respeta el toggle SMTP)
        if (isset($mailer)) {
            $sent = $mailer->send($email, $subject, $message);
        } else {
            $headers  = "MIME-Version: 1.0\n";
            $headers .= "From: " . $setting['site_name'] . " <" . ($setting['smtp_from_email'] ?? ('noreply@' . $_SERVER['HTTP_HOST'])) . ">\n";
            $headers .= "Content-type: text/html; charset=utf-8\n";
            $sent = mail($email, $subject, $message, $headers);
        }
        $this->logEvent($userId, $email, '2fa_sent');
        return $sent;
    }

    public function verify2FACode($userId, $inputCode)
    {
        $stmt = $this->db->prepare("
            SELECT id, code FROM " . PFX . "2fa_codes
            WHERE user_id = :uid
              AND used = 0
              AND expires_at > NOW()
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $this->error = 'Code expired or not found. Please request a new one.';
            return false;
        }

        if (!password_verify($inputCode, $row['code'])) {
            $this->error = 'Invalid verification code.';
            return false;
        }

        // Marcar como usado
        $this->db->prepare("
            UPDATE " . PFX . "2fa_codes SET used = 1 WHERE id = :id
        ")->execute([':id' => $row['id']]);

        return true;
    }

    // ══════════════════════════════════════════════
    // ── SESSION
    // ══════════════════════════════════════════════

    private function startSecureSession($user)
    {
        global $crypt;

        // Regenerar ID para prevenir session fixation
        session_regenerate_id(true);

        $encId = $crypt->encrypt($user['id'], 'USER');

        $_SESSION['uid']        = $encId;
        $_SESSION['curr_user']  = $user['email'];
        $_SESSION['auth']       = true;
        $_SESSION['start']      = time();
        $_SESSION['expire']     = time() + self::SESSION_DURATION;
        $_SESSION['user_agent'] = hash('sha256', $this->getUserAgent());
        $_SESSION['ip']         = $this->getIP();
        $_SESSION['token']      = hash_hmac('sha256', $user['email'] . $user['id'], session_id());

        // Cookie segura de sesión por 48 horas
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            [
                'expires'  => time() + self::SESSION_DURATION,
                'path'     => $params['path'],
                'domain'   => $params['domain'],
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    public function is_loggedin()
    {
        if (!isset($_SESSION['auth'], $_SESSION['curr_user'], $_SESSION['token'], $_SESSION['expire'])) {
            return false;
        }

        // Verificar expiración
        if (time() > $_SESSION['expire']) {
            $this->logout();
            return false;
        }

        // Verificar token HMAC
        global $crypt;
        $uid  = $crypt->decrypt($_SESSION['uid'], 'USER');
        $stmt = $this->db->prepare("SELECT id, email FROM " . PFX . "users WHERE id = :id AND active = 1");
        $stmt->execute([':id' => $uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;

        $expected = hash_hmac('sha256', $user['email'] . $user['id'], session_id());
        if (!hash_equals($expected, $_SESSION['token'])) {
            $this->logout();
            return false;
        }

        // Verificar IP y user agent (opcional pero recomendado)
        if ($_SESSION['ip'] !== $this->getIP()) {
            // No cerrar sesión por IP — puede cambiar en móvil, solo loguear
        }

        // Renovar sesión si queda menos de 1 hora
        if ($_SESSION['expire'] - time() < 3600) {
            $_SESSION['expire'] = time() + self::SESSION_DURATION;
        }

        return true;
    }

    public function logout()
    {
        $email = $_SESSION['curr_user'] ?? null;
        global $crypt;
        $uid = isset($_SESSION['uid']) ? $crypt->decrypt($_SESSION['uid'], 'USER') : null;
        $this->logEvent($uid, $email, 'logout');

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        header('Location: login.php');
        exit;
    }

    // ══════════════════════════════════════════════
    // ── LOGIN
    // ══════════════════════════════════════════════

    public function login($email, $password)
    {
        $email = strtolower(trim($email));

        // Rate limiting
        if ($this->isLockedOut($email)) return false;

        // Buscar usuario
        $stmt = $this->db->prepare("
            SELECT * FROM " . PFX . "users
            WHERE email = :email
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mensaje genérico para no revelar si el email existe
        if (!$user) {
            $this->recordAttempt($email, false);
            $this->logEvent(null, $email, 'failed', 'User not found');
            $this->error = 'Invalid credentials.';
            return false;
        }

        // Verificar cuenta activa
        if ($user['active'] != 1) {
            $this->logEvent($user['id'], $email, 'failed', 'Account inactive/banned');
            $this->error = 'Your account has been suspended. Contact support.';
            return false;
        }

        // Verificar email verificado — SOLO si la verificación está activada en ajustes
        global $setting;
        $verificationRequired = ($setting['email_verification'] ?? '0') == '1';

        if ($verificationRequired && $user['verified'] != 1) {
            $this->logEvent($user['id'], $email, 'failed', 'Email not verified');
            $this->error = 'Please verify your email address before logging in.';
            return false;
        }

        // Verificar contraseña
        if (!$this->verifyPassword($password, $user['password'])) {
            $this->recordAttempt($email, false);
            $this->logEvent($user['id'], $email, 'failed', 'Wrong password');
            $this->error = 'Invalid credentials.';
            return false;
        }

        // Migrar MD5 a bcrypt si es necesario
        if ($this->needsRehash($user['password'])) {
            $this->rehashPassword($user['id'], $password);
        }

        // Actualizar IP y last_login
        $this->db->prepare("
            UPDATE " . PFX . "users
            SET last_login = NOW(), ip_address = :ip
            WHERE id = :id
        ")->execute([':ip' => $this->getIP(), ':id' => $user['id']]);

        $this->recordAttempt($email, true);
        $this->logEvent($user['id'], $email, 'login');

        // Si tiene 2FA activado — poner en estado pendiente
        if ($user['two_factor_enabled'] == 1) {
            $_SESSION['2fa_pending']  = true;
            $_SESSION['2fa_user_id']  = $user['id'];
            $_SESSION['2fa_email']    = $user['email'];
            $_SESSION['2fa_fname']    = $user['fname'];
            $this->send2FACode($user['id'], $user['email'], $user['fname']);
            return '2fa';
        }

        $this->startSecureSession($user);
        return true;
    }

    public function completeLogin($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . PFX . "users WHERE id = :id AND active = 1");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return false;

        $this->logEvent($user['id'], $user['email'], '2fa_verified');
        unset($_SESSION['2fa_pending'], $_SESSION['2fa_user_id'], $_SESSION['2fa_email'], $_SESSION['2fa_fname']);
        $this->startSecureSession($user);
        return true;
    }

    // ══════════════════════════════════════════════
    // ── GOOGLE OAUTH
    // ══════════════════════════════════════════════

    public function loginWithGoogle($googleUser)
    {
        $email     = strtolower(trim($googleUser['email']));
        $googleId  = $googleUser['sub'];
        $fname     = $googleUser['given_name'] ?? $googleUser['name'] ?? 'User';

        // Buscar si ya existe OAuth vinculado a un usuario existente
        $stmt = $this->db->prepare("
        SELECT u.* FROM " . PFX . "users u
        INNER JOIN " . PFX . "oauth o ON u.id = o.user_id
        WHERE o.provider = 'google' AND o.provider_id = :gid
    ");
        $stmt->execute([':gid' => $googleId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            // ── Limpiar registro OAuth huérfano (usuario borrado) ──
            // Si existe un oauth con este provider_id pero sin usuario válido, lo eliminamos
            $orphan = $this->db->prepare("
            SELECT o.id FROM " . PFX . "oauth o
            LEFT JOIN " . PFX . "users u ON u.id = o.user_id
            WHERE o.provider = 'google' AND o.provider_id = :gid AND u.id IS NULL
        ");
            $orphan->execute([':gid' => $googleId]);
            if ($orphan->fetch()) {
                $this->db->prepare("
                DELETE FROM " . PFX . "oauth
                WHERE provider = 'google' AND provider_id = :gid
            ")->execute([':gid' => $googleId]);
            }

            // Buscar por email
            $stmt2 = $this->db->prepare("SELECT * FROM " . PFX . "users WHERE email = :email");
            $stmt2->execute([':email' => $email]);
            $user = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Vincular Google a cuenta existente — verificar que no exista ya el vínculo
                $chkLink = $this->db->prepare("
                SELECT id FROM " . PFX . "oauth
                WHERE provider = 'google' AND provider_id = :gid
            ");
                $chkLink->execute([':gid' => $googleId]);
                if (!$chkLink->fetch()) {
                    $stmt3 = $this->db->prepare("
                    INSERT INTO " . PFX . "oauth (user_id, provider, provider_id, email)
                    VALUES (:uid, 'google', :gid, :email)
                ");
                    $stmt3->execute([':uid' => $user['id'], ':gid' => $googleId, ':email' => $email]);
                }
            } else {
                // Crear cuenta nueva
                $username  = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $fname)) . '_' . substr($googleId, -4);
                $password  = $this->hashPassword(bin2hex(random_bytes(16)));
                $date      = date('Y-m-d H:i:s');
                $profile   = '../system/assets/uploads/user-img/default.png';

                $add = $this->db->prepare("
                INSERT INTO " . PFX . "users
                (fname, username, email, password, active, verified, created, profile, allow_email, purchases, balance, password_recover, moderator)
                VALUES (:fname, :username, :email, :password, 1, 1, :date, :profile, 1, 0, 0, 0, 0)
            ");
                $add->execute([
                    ':fname'    => $fname,
                    ':username' => $username,
                    ':email'    => $email,
                    ':password' => $password,
                    ':date'     => $date,
                    ':profile'  => $profile,
                ]);
                $newId = $this->db->lastInsertId();

                $this->db->prepare("
                INSERT INTO " . PFX . "oauth (user_id, provider, provider_id, email)
                VALUES (:uid, 'google', :gid, :email)
            ")->execute([':uid' => $newId, ':gid' => $googleId, ':email' => $email]);

                $stmt4 = $this->db->prepare("SELECT * FROM " . PFX . "users WHERE id = :id");
                $stmt4->execute([':id' => $newId]);
                $user = $stmt4->fetch(PDO::FETCH_ASSOC);
            }
        }

        if (!$user || $user['active'] != 1) {
            $this->error = 'Your account has been suspended.';
            return false;
        }

        $this->db->prepare("
        UPDATE " . PFX . "users SET last_login = NOW(), ip_address = :ip WHERE id = :id
    ")->execute([':ip' => $this->getIP(), ':id' => $user['id']]);

        $this->logEvent($user['id'], $email, 'google');
        $this->startSecureSession($user);
        return true;
    }

    // ══════════════════════════════════════════════
    // ── REGISTRO
    // ══════════════════════════════════════════════

    public function add($name, $username, $email, $password)
    {
        global $setting;

        if (!$this->is_new_user($email, $username)) return false;

        $date     = date('Y-m-d H:i:s');
        $hash     = $this->hashPassword($password);
        $profile  = '../system/assets/uploads/user-img/default.png';

        // Si la verificación por email NO está activada, el usuario queda verificado de una vez
        $verificationRequired = ($setting['email_verification'] ?? '0') == '1';
        $verifiedValue = $verificationRequired ? 0 : 1;

        $add = $this->db->prepare("
            INSERT INTO " . PFX . "users
            (fname, username, email, password, active, created, profile, allow_email, purchases, balance, verified, password_recover, moderator)
            VALUES (:fname, :username, :email, :password, 1, :date, :profile, 1, 0, 0, :verified, 0, 0)
        ");
        $add->execute([
            ':fname'    => $fname = htmlspecialchars(strip_tags(trim($name))),
            ':username' => htmlspecialchars(strip_tags(trim($username))),
            ':email'    => strtolower(trim($email)),
            ':password' => $hash,
            ':date'     => $date,
            ':profile'  => $profile,
            ':verified' => $verifiedValue,
        ]);

        if ($add) {
            $this->sendWelcomeEmail($email, $name);
            $this->msg = 'Registered successfully';
            return $this->db->lastInsertId();
        }

        $this->error = 'An error occurred. Please try again.';
        return false;
    }

    private function sendWelcomeEmail($email, $name)
    {
        global $setting, $mailer;
        $subject  = 'Welcome to ' . $setting['site_name'];
        $message  = '<html><body style="font-family:sans-serif;background:#0d0f1c;color:#f0f2ff;padding:2rem;">
            <div style="max-width:480px;margin:0 auto;background:#13152a;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:2rem;text-align:center;">
                <img src="' . $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon'] . '" width="50" style="margin-bottom:1rem;">
                <h2 style="color:#d4ff00;">Welcome, ' . htmlspecialchars($name) . '!</h2>
                <p style="color:#8b8fa8;">Your account has been created successfully. Start exploring thousands of logos.</p>
                <a href="' . $setting['website_url'] . '" style="display:inline-block;margin-top:1rem;background:#d4ff00;color:#0d0f1c;padding:.6rem 1.5rem;border-radius:99px;text-decoration:none;font-weight:700;">Explore Logos</a>
            </div>
        </body></html>';

        // Enviar por la clase Mailer (respeta el toggle SMTP)
        if (isset($mailer)) {
            $mailer->send($email, $subject, $message);
        } else {
            $headers  = "MIME-Version: 1.0\n";
            $headers .= "From: " . $setting['site_name'] . " <" . ($setting['smtp_from_email'] ?? ('noreply@' . $_SERVER['HTTP_HOST'])) . ">\n";
            $headers .= "Content-type: text/html; charset=utf-8\n";
            mail($email, $subject, $message, $headers);
        }
    }

    public function is_new_user($email, $username)
    {
        $stmt = $this->db->prepare("
            SELECT id FROM " . PFX . "users
            WHERE email = :email OR username = :username
            LIMIT 1
        ");
        $stmt->execute([':email' => strtolower(trim($email)), ':username' => trim($username)]);
        if ($stmt->fetchColumn()) {
            $this->error = 'An account with that email or username already exists.';
            return false;
        }
        return true;
    }

    // ══════════════════════════════════════════════
    // ── GETTERS / UTILITIES
    // ══════════════════════════════════════════════

    public function countAll($search = null)
    {
        if ($search) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM " . PFX . "users WHERE active = 1 AND (fname LIKE :s OR email LIKE :s OR username LIKE :s)");
            $stmt->execute([':s' => '%' . $search . '%']);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM " . PFX . "users WHERE active = 1");
            $stmt->execute();
        }
        return $stmt->fetchColumn();
    }

    public function countBanned()
    {
        return $this->db->query("SELECT COUNT(*) FROM " . PFX . "users WHERE active = 0")->fetchColumn();
    }

    public function details($id)
    {
        global $crypt;
        $realId = $crypt->decrypt($id, 'USER');
        $stmt = $this->db->prepare("SELECT * FROM " . PFX . "users WHERE id = :id");
        $stmt->execute([':id' => $realId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return false;
        $row['id']      = $crypt->encrypt($row['id'], 'USER');
        $row['balance'] = !empty($row['balance']) ? $row['balance'] : '0';
        unset($row['password']); // Nunca exponer el hash
        return $row;
    }

    public function update($id, $col, $value)
    {
        global $crypt;
        $realId = $crypt->decrypt($id, 'USER');
        $allowedCols = ['fname', 'email', 'profile', 'allow_email', 'verified', 'active', 'moderator', 'two_factor_enabled'];
        if (!in_array($col, $allowedCols)) {
            $this->error = 'Invalid field.';
            return false;
        }
        $stmt = $this->db->prepare("UPDATE " . PFX . "users SET `$col` = :value WHERE id = :id");
        $stmt->execute([':value' => $value, ':id' => $realId]);
        return true;
    }

    public function updateve($email, $col, $value)
    {
        $allowedCols = ['password', 'password_recover', 'verified', 'active'];
        if (!in_array($col, $allowedCols)) {
            $this->error = 'Invalid field.';
            return false;
        }
        $stmt = $this->db->prepare("UPDATE " . PFX . "users SET `$col` = :value WHERE email = :email");
        $stmt->execute([':value' => $value, ':email' => strtolower(trim($email))]);
        return true;
    }

    public function userdetails($username)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . PFX . "users WHERE username = :username AND active = 1");
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) unset($row['password']);
        return $row ?: false;
    }

    public function getUsers($start, $total)
    {
        global $crypt;
        $stmt = $this->db->prepare("SELECT * FROM " . PFX . "users WHERE active = 1 ORDER BY id DESC LIMIT :start, :total");
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':total', (int)$total, PDO::PARAM_INT);
        $stmt->execute();
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            unset($row['password']);
            $row['id'] = $crypt->encrypt($row['id'], 'USER');
            $users[]   = $row;
        }
        return $users;
    }

    public function banned($start, $total)
    {
        global $crypt;
        $stmt = $this->db->prepare("SELECT * FROM " . PFX . "users WHERE active = 0 LIMIT :start, :total");
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':total', (int)$total, PDO::PARAM_INT);
        $stmt->execute();
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            unset($row['password']);
            $row['id'] = $crypt->encrypt($row['id'], 'USER');
            $users[]   = $row;
        }
        return $users;
    }

    public function newUsers()
    {
        global $crypt;
        $stmt = $this->db->prepare("SELECT * FROM " . PFX . "users WHERE active = 1 ORDER BY id DESC LIMIT 50");
        $stmt->execute();
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            unset($row['password']);
            $row['id'] = $crypt->encrypt($row['id'], 'USER');
            $users[]   = $row;
        }
        return $users;
    }

    public function mail_users($subject, $body)
    {
        global $setting;
        $stmt = $this->db->prepare("SELECT email, fname FROM " . PFX . "users WHERE allow_email = 1");
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $headers  = "MIME-Version: 1.0\nContent-type: text/html; charset=utf-8\n";
            $headers .= "From: " . $setting['site_name'] . " <noreply@" . $_SERVER['HTTP_HOST'] . ">\n";
            $message  = '<html><body><h1>Hello ' . htmlspecialchars($row['fname']) . '!</h1>' . $body . '</body></html>';
            mail($row['email'], $subject, $message, $headers);
        }
    }

    public function change_profile_image($id, $file_temp, $file_extn)
    {
        global $crypt;
        $realId    = $crypt->decrypt($id, 'USER');
        $allowed   = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array(strtolower($file_extn), $allowed)) {
            $this->error = 'Invalid image format.';
            return false;
        }
        $file_path = '../system/assets/uploads/user-img/' . bin2hex(random_bytes(8)) . '.' . $file_extn;
        if (move_uploaded_file($file_temp, $file_path)) {
            $stmt = $this->db->prepare("UPDATE " . PFX . "users SET profile = :path WHERE id = :id");
            return $stmt->execute([':path' => $file_path, ':id' => $realId]);
        }
        return false;
    }

    public function add_purchase($id)
    {
        global $crypt;
        $realId = $crypt->decrypt($id, 'USER');
        $stmt = $this->db->prepare("UPDATE " . PFX . "users SET purchases = purchases + 1 WHERE id = :id");
        return $stmt->execute([':id' => $realId]);
    }

    // ── Verificación de email ──
    public function sendVerificationEmail($userId, $email, $fname)
    {
        global $setting, $mailer;

        error_log('sendVerificationEmail called for: ' . $email);
        error_log('mailer exists: ' . (isset($mailer) ? 'yes' : 'NO'));
        error_log('email_verification setting: ' . ($setting['email_verification'] ?? 'NOT SET'));

        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 86400);

        error_log('Token generated: ' . $token);

        try {
            $stmt = $this->db->prepare("
            UPDATE " . PFX . "users
            SET email_token = :token, email_token_expires = :expires
            WHERE id = :id
        ");
            $result = $stmt->execute([':token' => $token, ':expires' => $expires, ':id' => $userId]);
            error_log('Token saved to DB: ' . ($result ? 'yes' : 'NO'));
        } catch (Exception $e) {
            error_log('DB error saving token: ' . $e->getMessage());
            return false;
        }

        $verifyUrl = $setting['website_url'] . '/user/verify-email.php?token=' . $token;
        error_log('Verify URL: ' . $verifyUrl);

        $body = $mailer->template(
            'Verify your email address',
            "Hi <strong style='color:#f0f2ff;'>" . htmlspecialchars($fname) . "</strong>,<br><br>
         Thanks for creating your account on <strong style='color:#f0f2ff;'>" . $setting['site_name'] . "</strong>.<br>
         Please verify your email address to activate your account.",
            'Verify Email Address',
            $verifyUrl
        );

        error_log('Template generated, sending email...');
        $sent = $mailer->send($email, 'Verify your email — ' . $setting['site_name'], $body);
        error_log('Email sent result: ' . ($sent ? 'YES' : 'NO'));

        return $sent;
    }

    public function verifyEmailToken($token)
    {
        if (empty($token) || strlen($token) !== 64) return false;

        $stmt = $this->db->prepare("
        SELECT id, email FROM " . PFX . "users
        WHERE email_token = :token
          AND email_token_expires > NOW()
          AND verified = 0
        LIMIT 1
    ");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return false;

        $update = $this->db->prepare("
        UPDATE " . PFX . "users
        SET verified = 1, email_token = NULL, email_token_expires = NULL
        WHERE id = :id
    ");
        $update->execute([':id' => $user['id']]);
        return $user;
    }

    public function resendVerification($email)
    {
        $stmt = $this->db->prepare("
        SELECT id, fname, email, verified FROM " . PFX . "users
        WHERE email = :email AND active = 1
    ");
        $stmt->execute([':email' => strtolower(trim($email))]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$u) {
            $this->error = 'Email not found.';
            return false;
        }
        if ($u['verified'] == 1) {
            $this->error = 'Account already verified.';
            return false;
        }

        return $this->sendVerificationEmail($u['id'], $u['email'], $u['fname']);
    }
}