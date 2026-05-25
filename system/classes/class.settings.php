<?php

class Settings
{
    var $error = false;
    var $msg = false;
    
    private $db;
    
    function __construct($DB_con)
    {
        $this->db = $DB_con;
    }
    
    public function get_all()
    {
        $setting = array();
        
        $result = $this->db->prepare("SELECT * FROM  " . PFX . "settings");
        $result->execute();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $setting[$row['setting']] = $row['value'];
        }
        return $setting;
    }
    public function update($newsettings)
    {
        foreach ($newsettings as $key => $value) {
            
            $update = $this->db->prepare("UPDATE " . PFX . "settings  SET `value` = :value WHERE setting = :key");
          	$update->bindParam(':value', $value);
          	$update->bindParam(':key', $key);
            $update->execute();
            
            if (!$update) {
                $this->error = "Error saving settings";
                return false;
            }
        }
        if (empty($this->error)) {
            $this->msg = "Settings updated successfully";
            return true;
        }
    }
    
}
