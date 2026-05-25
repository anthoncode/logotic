<?php 

class Coupon{
	var $error = '';
	var $msg = '';
		private $db;
	
	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}

public function all(){
	    
	    $result = $this->db->prepare("SELECT * FROM  " . PFX . "coupons WHERE active = 1");
		$result->execute();
		$coupons = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
        $coupons[]=$row;
	    }
	    return $coupons;
	    
}

public function getCoupons($start,$total){

		$result = $this->db->prepare("SELECT * FROM  " . PFX . "coupons WHERE active = 1 ORDER BY `id` DESC LIMIT $start , $total");
		$result->execute();
		$coupons = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
        $coupons[]=$row;
	    }
	    return $coupons;
	
}

public function countAll(){
    
		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "coupons WHERE active = 1");
		$result->execute();
		$coupons = $result->fetchColumn();
	    return $coupons;
	    
}

public function is_coupon($id){
		
		$result = $this->db->prepare("SELECT active FROM  " . PFX . "coupons WHERE id = ? AND  active = 1");
		$result->execute(array($id));
		if ($result){
    	return true;
		}
		$this->error = "No such coupon exists";
		return false;
		
}
		
public function is_new($code){
    
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "coupons WHERE code = :code");
  		$result->bindParam(':code', $code);
		$result->execute();
		if ($result->fetchColumn() == 0){
    		return true;
		}
		$this->error = "Coupon with code '$code' already exists.";
		return false;
		
}

public function details($id){
    
		if($this->is_coupon($id)){
        $result = $this->db->prepare("SELECT * FROM  " . PFX . "coupons WHERE id = :id");
        $result->bindParam(':id', $id);
        $result->execute();
		while($result = $result->fetch(PDO::FETCH_ASSOC)) {
		return $result;
		}
		}
		return false;
		
}

public function getDetails($code){
		
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "coupons WHERE code = :code");
  		$result->bindParam(':code', $code);
		$result->execute();
	    while($result=$result->fetch(PDO::FETCH_ASSOC)){
        return $result;
	    }
	    return false;
		
}

public function add($code,$off,$type,$min){
    
		$code = trim($code);
		$code = strtoupper($code);
		$off = trim($off);
		$type = trim($type);
		$min = trim($min);
	    if(empty($code) || empty($off)){
		$this->error = 'Please input all details';
		return false;
		}
		if(!is_numeric($off)){
		$this->error = 'Invalid details';
		return false;
		}
		if(!is_numeric($type) || $type > 2 || $type < 1){
		$this->error = 'Invalid details';
		return false;
		}
		if(!is_numeric($min)){
		$this->error = 'Invalid details';
		return false;
		}
		if($type == '1' && $off > 100){
		$this->error = 'Invalid discount price';
		return false;
		}
		if($type == '2' && $off > $min){
		$this->error = 'Invalid discount price';
		return false;
		}
		if($type == '2'){
		$off = number_format($off,2);
		}
		/*if(!$this->is_new($code)){
		return false;
		}*/
		$add = $this->db->prepare("INSERT INTO " . PFX . "coupons (`id`, `code`, `off`,`off_type`,`order_min`,`active`) VALUES (NULL, '$code' , '$off','$type','$min','1')");
		$add->execute();
	    	if($add){
		$this->msg = "Coupon added successfully";
		return true;
		}	
		$this->error = 'Error saving coupon';
		return false;	
		
}
	
public function update($id,$code,$off,$type,$min){
    
		$code = trim($code);
		$code = strtoupper($code);
		$off = trim($off);
		$min = trim($min);
		if($this->is_coupon($id)){
		if(empty($code) || empty($off)){
		$this->error = 'Please input all details';
		return false;
		}
		
		if(!is_numeric($type) || $type > 2 || $type < 1){
		$this->error = 'Invalid details';
		return false;
		}
		if(!is_numeric($off)){
		$this->error = 'Invalid details';
		return false;
		}
		if(!is_numeric($min)){
		$this->error = 'Invalid details';
		return false;
		}
		if($type == '1' && $off > 100){
		$this->error = 'Invalid discount price';
		return false;
		}
		if($type == '2' && $off > $min){
		$this->error = 'Invalid discount price';
		return false;
		}
		if($type == '2'){
		$off = number_format($off,2);
		}
		/*if(!$this->is_new($code)){
		return false;
		}*/
		
		$update = $this->db->prepare("UPDATE " . PFX . "coupons  SET `code` = '$code',`off` = '$off',`off_type`= '$type',`order_min` = '$min' WHERE id ='$id'");
		$update->execute();
		/*if ($update->fetchColumn() == 1){
		$this->msg = "Coupon updated successfully";
    		return true;
		}*/
		if($update){
		$this->msg = "Coupon updated successfully";
		return true;
	}
	    	$this->error = "Error saving coupon";
	    	return false;
	    	}
    		$this->error = "Error saving coupon";
	    	return false;

}

public function remove($id){
    
		if($this->is_coupon($id)){
		$update = $this->db->prepare("UPDATE " . PFX . "coupons  SET `active` = '0' WHERE id = :id");
        $update->bindParam(':id', $id);
		$update->execute();
								
	    if($update){
		$this->msg = "Coupon removed successfully";
		return true;
	    }
	    $this->error = "Error removing coupon";
	    return false;
	    }
	    $this->error = "Error removing coupon";
	    return false;
	    
}	

public function restore($id){
		
		$update = $this->db->prepare("UPDATE " . PFX . "coupons  SET `active` = '1' WHERE id = :id");
  		$update->bindParam(':id', $id);
		$update->execute();
								
	    if($update){
		$this->msg = "Coupon restored successfully";
		return true;
	    }
	    $this->error = "Error restoring coupon";
	    return false;
	    
}

public function getDeletedCoupons($start,$total){
	
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "coupons WHERE active = 0 ORDER BY `id` DESC LIMIT $start , $total");
		$result->execute();
		$coupons = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
        $coupons[]=$row;
	    }
	    return $coupons;
	    
}

public function countAllDeleted(){
    
    	$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "coupons WHERE active = 0");
		$result->execute();
		$coupons = $result->fetchColumn();
	    return $coupons;
	
}

}
