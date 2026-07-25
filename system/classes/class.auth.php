<?php
class Auth {

    var $error = '';
    var $msg   = '';
    private $db;

    // Columnas permitidas para updates dinámicos
    private $allowedCols = ['email', 'password', 'active', 'password_recover', 'fname'];

    function __construct($DB_con)
    {
        $this->db = $DB_con;
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
        $this->error = "No such customer exists";
        return false;
    }

    public function details($id)
    {
        global $crypt;
        if ($this->is_admin($id)) {
            $id = $crypt->decrypt($id, 'ADMIN');
            $result = $this->db->prepare("SELECT * FROM " . PFX . "admins WHERE id = ?");
            $result->execute([$id]);
            while ($result = $result->fetch(PDO::FETCH_ASSOC)) {
                $result['id']       = $crypt->encrypt($result['id'], 'ADMIN');
                $result['password'] = md5($result['password']);
                return $result;
            }
        }
        $this->error = "No such customer exists";
        return false;
    }

    public function update($id, $col, $value)
    {
        global $crypt;
        // Validar la columna contra la whitelist
        if (!in_array($col, $this->allowedCols, true)) {
            $this->error = "Invalid column";
            return false;
        }
        if ($this->is_admin($id)) {
            $id = $crypt->decrypt($id, 'ADMIN');
            $result = $this->db->prepare("UPDATE " . PFX . "admins SET `$col` = :value WHERE id = :id");
            $result->bindParam(':value', $value);
            $result->bindParam(':id', $id);
            if ($result->execute()) {
                return true;
            }
            $this->error = "Error occurred";
            return false;
        }
        $this->error = "No such customer exists";
        return false;
    }

    public function is_loggedin()
    {
        if (isset($_SESSION['auth']) && isset($_SESSION['curr_user']) && isset($_SESSION['token'])) {
            $checksum = md5($_SESSION['curr_user'] . 'dsp' . date('ymd'));
            if ($checksum == $_SESSION['token']) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function updateve($email, $col, $value)
    {
        // Validar la columna contra la whitelist
        if (!in_array($col, $this->allowedCols, true)) {
            $this->error = "Invalid column";
            return false;
        }
        $email  = trim($email);
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
        $email    = trim($email);
        $password = md5($password);

        $result = $this->db->prepare("SELECT * FROM " . PFX . "admins WHERE active = 1 AND email = :email AND password = :password");
        $result->bindParam(':email', $email);
        $result->bindParam(':password', $password);
        $result->execute();

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['id'] = $crypt->encrypt($row['id'], 'ADMIN');
            $_SESSION['uid']       = $row['id'];
            $_SESSION['curr_user'] = $row['email'];
            $_SESSION['token']     = md5($row['email'] . 'dsp' . date('ymd'));
            $_SESSION['auth']      = true;
            $_SESSION['start']     = time();
            $_SESSION['expire']    = $_SESSION['start'] + (1 * 60);
            return true;
        }
        $this->error = 'Invalid username / Password';
        return false;
    }

    public function logout()
    {
        session_destroy();
        header("location: login.php");
        exit;
    }

    public function add($email, $password)
    {
        if ($this->is_new_admin($email)) {
            $password = base64_encode($password);
            $add = $this->db->prepare("INSERT INTO " . PFX . "admins (`id`, `email`, `password`, `active`) VALUES (NULL, :email, :password, '1')");
            $add->bindParam(':email', $email);
            $add->bindParam(':password', $password);
            if ($add->execute()) {
                $headers  = "MIME-Version: 1.0\n";
                $headers .= "Content-type: text/html; charset=utf-8\n";
                $headers .= "From: 'Logotic' <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
                $subject = "Account Signup Information";
                $message = "<h4>Hello, " . htmlspecialchars($email) . "</h4><br />";
                $message .= "Congratulations, you have successfully registered at " . $_SERVER['HTTP_HOST'] . " with this email id. <br />";
                $message .= "&copy; " . $_SERVER['HTTP_HOST'];
                mail($email, $subject, $message, $headers);
                $this->msg = "Registered successfully";
                return $this->db->lastInsertId();
            }
            $this->error = "Oops something wrong happened. Please try later.";
            return false;
        }
        $this->error = "admin already exists with email id";
        return false;
    }

    public function is_new_admin($email)
    {
        $result = $this->db->prepare("SELECT id FROM " . PFX . "admins WHERE email = ?");
        $result->execute([$email]);
        if ($result->fetchColumn() == 0) {
            return true;
        }
        $this->error = "User already exists";
        return false;
    }

    public function get_pass($email)
    {
        $email  = trim($email);
        $result = $this->db->prepare("SELECT password FROM " . PFX . "admins WHERE email = ?");
        $result->execute([$email]);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            return base64_decode($row['password']);
        }
        $this->error = "User not found";
        return false;
    }
}