<?php

/*class Transaction{
		
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
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "transactions WHERE `$parm` = '$value' ORDER BY  `id` DESC");
		$result->execute();
		}else{
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "transactions ORDER BY  `id` DESC");
		$result->execute();
		}
	    $transactions = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
        $row['id']= $crypt->encrypt($row['id'],'TRANSACTION');
		$row['user_id']= $crypt->encrypt($row['user_id'],'USER');
		$transactions[]=$row;
	    }
	    return $transactions;
	
}	

public function countAll($parm = null, $value= null){
	global $crypt;
	
		if($parm && $value){
		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "transactions WHERE `$parm` = '$value' ORDER BY  `id` DESC");
		$result->execute();
		}else{
		$result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "transactions ORDER BY  `id` DESC");
		$result->execute();
		}
	
	    $rows = $result->fetchColumn();
	    return $rows;
	
}	

public function AddAll(){
	global $crypt;
	
		$result = $this->db->prepare("SELECT SUM(amount) FROM  " . PFX . "transactions WHERE callback = '1' AND type = '2'");
		$result->execute();
	
	    $rows = $result->fetchColumn();
	    //return money_format("%.2n", $rows['SUM(price)']);
	    return $rows;
	
}	

public function getTransactions($start,$total){
	global $crypt;
		
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "transactions ORDER BY  `id` DESC LIMIT $start , $total");
		$result->execute();
		$transactions = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
		$row['id']= $crypt->encrypt($row['id'],'TRANSACTION');
		$row['user_id']= $crypt->encrypt($row['user_id'],'USER');
		$transactions[]=$row;
		}
	    return $transactions;
	    
}	

public function getUserTransactions($u,$start,$total){
	global $crypt;
		
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "transactions WHERE `user_id` = '$u'  ORDER BY  `id` DESC LIMIT $start , $total");
		$result->execute();
		$transactions = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
		$row['id']= $crypt->encrypt($row['id'],'TRANSACTION');
		$row['user_id']= $crypt->encrypt($row['user_id'],'USER');
		$transactions[]=$row;
		}
	    return $transactions;
	    
}
	
public function add($userID,$amount,$product='0',$type,$callback){
	global $crypt;
	global $user;
	
	    $date = date("Y-m-d");
	    $userEnc = $userID;
	    $userID = $crypt->decrypt($userID,'USER');
	    $add = $this->db->prepare("INSERT INTO " . PFX . "transactions (`id`, `user_id`, `amount`,`product`,`type`,`callback`,`date`) VALUES (NULL, '$userID' , '$amount', '$product', '$type','$callback','$date')");
		$add->execute();
	    $trans_id = $this->db->lastInsertId();
	    if($add){
		if($_SESSION['payment']['use_prepaid']){
		$userDetails= $user->details($userEnc);
		$balance = $userDetails['balance'] - $amount;
		$update = $user->update($userEnc,'balance',$balance);
		if($update ){
		return $crypt->encrypt($trans_id ,'TRANSACTION'); 
		}	else	{
				$this->error = "Oops something wrong happened. Please try later.";
				return false;
				}
		}
		return $crypt->encrypt($trans_id ,'TRANSACTION');
		}
		$this->error = "Oops something wrong happened. Please try later.";
		return false;
		
}

public function update($id,$col,$value){
	global $crypt;
	
	    if($this->is_transaction($id)){
	    $id = $crypt->decrypt($id,'TRANSACTION');

        $result = $this->db->prepare("UPDATE  " . PFX . "transactions SET `$col` = '$value' WHERE id = '$id'");
		$result->execute();

	    if($result){
	    return true;
	    }
		$this->error = "Error occurred";
		return false;
			}
		$this->error = "No such transaction exists";
		return false;
		
}

public function is_transaction($id){
	global $crypt;
	
		$id = $crypt->decrypt($id,'TRANSACTION');
		$result = $this->db->prepare("SELECT id FROM  " . PFX . "transactions WHERE id = '$id'");
		$result->execute();
		if ($result){
    	return true;
		}
		$this->error = "No such transaction exists";
		return false;
		
	}
	
	public function details($id){
		if($this->is_transaction($id)){
			
			$result = $this->db->prepare("SELECT * FROM  " . PFX . "transactions WHERE id = :id");
          $result->bindParam(':id', $id);
		$result->execute();
			
			while($result=$result->fetch(PDO::FETCH_ASSOC)){
			return $result;
			}
			}
		return false;
	}
	
}*/
