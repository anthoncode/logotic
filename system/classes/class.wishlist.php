<?php
class Wishlist {

    var $error = '';
    var $msg   = '';
    private $db;

    function __construct($DB_con)
    {
        $this->db = $DB_con;
    }

    public function all($parm = null, $value = null)
    {
        global $crypt;

        // Whitelist de columnas permitidas — evita inyección por nombre de columna
        $allowed = ['w_id', 'product_id', 'user_id'];

        if ($parm && $value !== null) {
            if (!in_array($parm, $allowed, true)) {
                $this->error = 'Invalid column';
                return [];
            }
            $result = $this->db->prepare("SELECT * FROM " . PFX . "wishlists WHERE `$parm` = :value ORDER BY `w_id` DESC");
            $result->bindParam(':value', $value);
            $result->execute();
        } else {
            $result = $this->db->prepare("SELECT * FROM " . PFX . "wishlists ORDER BY `w_id` DESC");
            $result->execute();
        }

        $uwishlist = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['w_id']    = $crypt->encrypt($row['w_id'], 'WISHLIST');
            $row['user_id'] = $crypt->encrypt($row['user_id'], 'USER');
            $uwishlist[] = $row;
        }
        return $uwishlist;
    }

    public function countAll($parm = null, $value = null)
    {
        $allowed = ['w_id', 'product_id', 'user_id'];

        if ($parm && $value !== null) {
            if (!in_array($parm, $allowed, true)) {
                $this->error = 'Invalid column';
                return 0;
            }
            $result = $this->db->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE `$parm` = :value");
            $result->bindParam(':value', $value);
            $result->execute();
        } else {
            $result = $this->db->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists");
            $result->execute();
        }
        return $result->fetchColumn();
    }

    public function getUserWishlist($u, $start, $total)
    {
        global $crypt;

        // LIMIT no acepta parámetros ligados en algunos drivers → forzar enteros
        $start = (int) $start;
        $total = (int) $total;

        $result = $this->db->prepare("SELECT * FROM " . PFX . "wishlists WHERE `user_id` = :u ORDER BY `w_id` DESC LIMIT $start, $total");
        $result->bindParam(':u', $u);
        $result->execute();

        $uwishlist = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $row['user_id'] = $crypt->encrypt($row['user_id'], 'USER');
            $uwishlist[] = $row;
        }
        return $uwishlist;
    }

    public function is_alreadyadd($userID, $productID)
    {
        global $crypt;
        $userID = $crypt->decrypt($userID, 'USER');

        $result = $this->db->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE user_id = :uid AND product_id = :pid");
        $result->bindParam(':uid', $userID);
        $result->bindParam(':pid', $productID);
        $result->execute();

        if ($result->fetchColumn() >= 1) {
            return true;
        }
        $this->error = "No such purchase exists";
        return false;
    }

    public function is_wishlist($id)
    {
        global $crypt;
        $id = $crypt->decrypt($id, 'WISHLIST');

        $result = $this->db->prepare("SELECT w_id FROM " . PFX . "wishlists WHERE w_id = :id");
        $result->bindParam(':id', $id);
        $result->execute();

        if ($result->fetchColumn()) {
            return true;
        }
        $this->error = "No such wishlist exists";
        return false;
    }

    public function details($id)
    {
        $result = $this->db->prepare("SELECT * FROM " . PFX . "wishlists WHERE w_id = :id");
        $result->bindParam(':id', $id);
        $result->execute();
        return $result->fetch(PDO::FETCH_ASSOC) ?: false;
    }

    public function add($userID, $productID)
    {
        global $crypt;
        $userID    = $crypt->decrypt($userID, 'USER');
        $productID = (int) $productID;

        $add = $this->db->prepare("INSERT INTO " . PFX . "wishlists (`product_id`, `user_id`) VALUES (:pid, :uid)");
        $add->bindParam(":pid", $productID);
        $add->bindParam(":uid", $userID);

        if ($add->execute()) {
            return true;
        }
        $this->error = "Failed to add to your wishlist";
        return false;
    }

    public function delete($id, $uid)
    {
        $delete = $this->db->prepare("DELETE FROM " . PFX . "wishlists WHERE w_id = :id AND user_id = :uid");
        $delete->bindParam(":id", $id);
        $delete->bindParam(":uid", $uid);
        return $delete->execute();
    }
}