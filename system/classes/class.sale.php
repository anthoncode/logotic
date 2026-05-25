<?php

class Sale{
		
	 var $error = '';
	 var $msg = '';
	 
	 	private $db;
	
	function __construct($DB_con)
	{
		$this->db = $DB_con;
	}
 
public function all00($parm = null, $value= null){
	global $crypt;
	
		if($parm && $value){
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "sales WHERE $parm = '$value'  ORDER BY  `id` DESC");
        $result->execute();
		}else{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "sales  ORDER BY  `id` DESC");
        $result->execute();
		}
	    $sales = array();
	    while($row = $result->fetch(PDO::FETCH_ASSOC)) {
		$row['id']= $crypt->encrypt($row['id'],'SALE');
		$row['user_id']= $crypt->encrypt($row['user_id'],'USER');
		$sales[]=$row;
		}
	    return $sales;
	    
}	


public function all($us){
	global $crypt;
		
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "downloads WHERE `user_id` = '$us'  ORDER BY  `id` DESC ");
		$result->execute();
		$uwishlist = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
		//$row['id']= $crypt->encrypt($row['id'],'WISHLIST');
		$row['user_id']= $crypt->encrypt($row['user_id'],'USER');
		$uwishlist[]=$row;
		}
	    return $uwishlist;
	    
}

public function countAll(){
    
		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "sales");
        $result->execute();
		$sales = $result->fetchColumn();
	    return $sales;
	    
}
	
public function is_purchase($id){
	global $crypt;
	
	    $id = $crypt->decrypt($id,'SALE');
		$result = $this->db->prepare("SELECT id FROM  " . PFX . "sales WHERE id = :id");
  		$result->bindParam(':id', $id);
		$result->execute();
		if ($result){
    	return true;
		}
		$this->error = "No such purchase exists";
		return false;
		
}

public function details($id){
	global $crypt;
	
		if($this->is_purchase($id)){
		$id = $crypt->decrypt($id,'SALE');
		
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "sales WHERE id = '$id'");
        $result->execute();
		while($result = $result->fetch(PDO::FETCH_ASSOC)) {
		$result['id']= $crypt->encrypt($result['id'],'SALE');
		$result['user_id']= $crypt->encrypt($result['user_id'],'USER');
		return $result;
		}
		}
		return false;
		
}


//si el producto ha sido descargado
public function is_purchased($id,$fid){
	global $crypt;
	
    	$id = $crypt->decrypt($id,'USER');
		$result = $this->db->prepare("SELECT id FROM  " . PFX . "downloads WHERE user_id = '$id' AND products_id = '$fid'");
		$result->execute();
		if ($result->fetchColumn() >= 1){
    	return true;
		}
		$this->error = "No such purchase exists";
		return false;
	
}	
	
public function is_coupon_fresh($id,$code){
	global $crypt;
	
		$result = $this->db->prepare("SELECT id FROM  " . PFX . "sales WHERE user_id = '$id' AND coupon = '$code'");
		$result->execute();
		if ($result->fetchColumn() >= 1){
    	return false;
		}
		return true;
	
}

public function add($userID,$productID,$transaction,$method,$coupon = null){
	global $crypt;
	global $user;
	global $product;
	
	    $userID = $crypt->decrypt($userID,'USER');
	    $transaction = $crypt->decrypt($transaction,'TRANSACTION');
	    $date = date("Y-m-d");
	    $add = $this->db->prepare("INSERT INTO " . PFX . "sales (`id`, `user_id`, `pro_id`,`date`,`coupon`,`transaction_id`,`method`) VALUES (NULL, '$userID' , '$productID', '$date', '$coupon', '$transaction','$method')");
		$add->execute();
	    $saleID = $crypt->encrypt($this->db->lastInsertId(),'SALE');
	    if($add){
	    $userID = $crypt->encrypt($userID,'USER');
		$error = ($user->add_purchase($userID)?false:$user->error);
		$error = ($product->add_sale($productID)?$error:$product->error);
		if($error){
		return $saleID ;
		}else{
		$this->error = $error;
		return false;
		}
		}
		$this->error = "Oops something wrong happened. Please try later.";
		return false;
		
	}

public function is_user($id){
	global $crypt;
	
		$id = $crypt->decrypt($id,'USER');
		$result = $this->db->prepare("SELECT id FROM  " . PFX . "users WHERE id = '$id'");
		$result->execute();
		if ($result){
    	return true;
		}
		$this->error = "No such customer exists";
		return false;
		
}

public function update($id,$pid,$col,$value){
    global $crypt;
        $id = $crypt->decrypt($id,'USER');
        $result = $this->db->prepare("UPDATE " . PFX . "sales SET `$col` = '$value' WHERE user_id = '$id' AND pro_id = '$pid'");
		$result->execute();
        if($result){
        return true;
        }
        $this->error = "Error occurred";
        return false;
		
}
  
  public function updatestock($pid){
        $result = $this->db->prepare("UPDATE " . PFX . "products SET stock = stock - 1 WHERE id = ?");
        $result->execute(array($pid));
        if($result){
        return true;
        }
        $this->error = "Error occurred";
        return false;
		
}
  
public function updatedl($id,$pid){
    global $crypt;
        $id = $crypt->decrypt($id,'USER');
        $result = $this->db->prepare("UPDATE " . PFX . "sales SET downloadrem = downloadrem - 1 WHERE user_id = '$id' AND pro_id = '$pid'");
		$result->execute();
        if($result){
        return true;
        }
        $this->error = "Error occurred";
        return false;
		
}

}
