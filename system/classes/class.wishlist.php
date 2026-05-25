<?php

class Wishlist{
		
	 var $error = '';
	 var $msg = '';
	 
 	private $db;
	
	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}
 
public function all($parm = null, $value= null){
	global $crypt;
	
		if($parm && $value){
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "wishlists WHERE `$parm` = '$value' ORDER BY  `w_id` DESC");
		$result->execute();
		}else{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "wishlists ORDER BY  `w_id` DESC");
		$result->execute();
		}
	    $uwishlist = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
        $row['w_id']= $crypt->encrypt($row['w_id'],'WISHLIST');
		$row['user_id']= $crypt->encrypt($row['user_id'],'USER');
		$uwishlist[]=$row;
	    }
	    return $uwishlist;
	
}	

public function countAll($parm = null, $value= null){
	global $crypt;
	
		if($parm && $value){
		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "wishlists WHERE `$parm` = '$value' ORDER BY  `w_id` DESC");
		$result->execute();
		}else{
		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "wishlists ORDER BY  `w_id` DESC");
		$result->execute();
		}
	
	    $rows = $result->fetchColumn();
	    return $rows;
	
}	

public function getUserWishlist($u,$start,$total){
	global $crypt;
		
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "wishlists WHERE `user_id` = '$u'  ORDER BY  `w_id` DESC LIMIT $start , $total");
		$result->execute();
		$uwishlist = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
		//$row['w_id']= $crypt->encrypt($row['w_id'],'WISHLIST');
		$row['user_id']= $crypt->encrypt($row['user_id'],'USER');
		$uwishlist[]=$row;
		}
	    return $uwishlist;
	    
}

public function is_alreadyadd($userID,$productID){
	global $crypt;
	
    	$userID = $crypt->decrypt($userID,'USER');

		$result = $this->db->prepare("SELECT * FROM  " . PFX . "wishlists WHERE user_id = '$userID' AND product_id = '$productID'");
		$result->execute();
		if ($result->fetchColumn() >= 1){
    	return true;
		}
		$this->error = "No such purchase exists";
		return false;
	
}	

public function is_wishlist($id){
	global $crypt;
	
		$id = $crypt->decrypt($id,'WISHLIST');
		$result = $this->db->prepare("SELECT w_id FROM  " . PFX . "wishlists WHERE w_id = '$id'");
		$result->execute();
		if ($result){
    	return true;
		}
		$this->error = "No such wishlist exists";
		return false;
		
	}

public function details($id){
    
        $result = $this->db->prepare("SELECT * FROM  " . PFX . "wishlists WHERE w_id = '$id'");
        $result->execute();
		while($result = $result->fetch(PDO::FETCH_ASSOC)) {
		return $result;
		}
		
		##return false;
		
}

public function add($userID,$productID){
	global $crypt;
	global $user;
	global $product;
	
/*	if(!$this->is_new($userID,$productID)){
		return true;
		} */
	    $userID = $crypt->decrypt($userID,'USER');
	    $add = $this->db->prepare("INSERT INTO " . PFX . "wishlists (`product_id`, `user_id`) VALUES (:pid, :uid)");
	    $add->bindparam(":pid",$productID);
	$add->bindparam(":uid",$userID);
	$add->execute();
	  if($add){
    	return true;
		}
		$this->error = "Failed to add to your wishlist";
		return false;
		
	}

	public function delete($id,$uid)
	{
		$delete = $this->db->prepare("DELETE FROM " . PFX . "wishlists WHERE w_id=:id AND user_id=:uid");
		$delete->bindparam(":id",$id);
		$delete->bindparam(":uid",$uid);
		$delete->execute();
		return true;
	}	
}
