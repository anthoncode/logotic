<?php

class Customer{
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
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE `$parm` = '$value' AND active = 1 ORDER BY  `id` DESC");
		$result->execute();
		}else{
	    $result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE active = 1");
		$result->execute();
	    }
	    $users = array();
		while($row=$result->fetch(PDO::FETCH_ASSOC)){
	    $row['id']= $crypt->encrypt($row['id'],'USER');
		$users[]=$row;
	    }
	    return $users;
	    
}

public function countAll(){
    global $crypt;
    
	    $result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "users WHERE active = 1");
		$result->execute();
		$rows = $result->fetchColumn();
	    return $rows;
	    
}

public function getUsers($start,$total){
    global $crypt;
    
	    $result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE active = 1 LIMIT $start , $total");
		$result->execute();
	    $users = array();
		while($row=$result->fetch(PDO::FETCH_ASSOC)){
	    $row['id']= $crypt->encrypt($row['id'],'USER');
		$users[]=$row;
	    }
	    return $users;
	    
}

	public function is_userdata($id){
		
		$result = $this->db->prepare("SELECT active FROM  " . PFX . "users WHERE username = '$id' AND  active = 1");
		$result->execute();
		
		if ($result){
    	return true;
		}
		$this->error = "No such product exists";
		return false;
		
	}

	public function userdetails($id){
		if($this->is_userdata($id)){
			
			$result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE username = :username");
          $result->bindParam(':username', $id);
		$result->execute();
			
			while($result=$result->fetch(PDO::FETCH_ASSOC)){
			return $result;
			}
			}
		return false;
	}

public function is_user($id){
	global $crypt;
	
		$id = $crypt->decrypt($id,'USER');
		$result = $this->db->prepare("SELECT id FROM  " . PFX . "users WHERE id = :id");
  		$result->bindParam(':id', $id);
		$result->execute();
		if ($result){
    	return true;
		}
		$this->error = "No such customer exists";
		return false;
		
}

public function details($id){
	global $crypt;
	
		if($this->is_user($id)){
		$id = $crypt->decrypt($id,'USER');
		$result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE id = :id");
          $result->bindParam(':id', $id);
			$result->execute();
			while($result=$result->fetch(PDO::FETCH_ASSOC)){
			
			$result['id']= $crypt->encrypt($result['id'],'USER');
			$result['balance']= !empty($result['balance'])?$result['balance']:'0';
			$result['password']=  md5($result['password']);
			return $result;
			
			}
	
}
$this->error = "No such customer exists";
		return false;
}

public function change_profile_image($id, $file_temp, $file_extn){
global $crypt;
global $setting;
if($this->is_user($id)){
        $id = $crypt->decrypt($id,'USER');
	$file_path = '../system/assets/uploads/user-img/' . substr(md5(time()), 0, 10) . '.' . $file_extn;
	if(move_uploaded_file($file_temp, $file_path) == true){
	$result = $this->db->prepare("UPDATE " . PFX . "users SET profile = '$file_path' WHERE id = '$id'");
		$result->execute();
		if($result){
			return true;
		}else{
			return false;
		}
	}else{
		return false;
	}
}
}

public function mail_users($subject, $body){
global $setting;
		$result = $this->db->prepare("SELECT `email`, `fname` FROM " . PFX . "users WHERE `allow_email` = 1");
		$result->execute();
while($row=$result->fetch(PDO::FETCH_ASSOC)){
        
            		    $to = $row['email'];

$message = '<html><body>';
$message .= '<h1>Hello ' . $row['fname'] . '!</h1>';
$message .= ''.$body.'';
$message .= '</body></html>';
            		   

$headers  = 'MIME-Version: 1.0' . "\r\n";

$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$headers .= "From: ".$setting['site_name']." <noreply@".$_SERVER['HTTP_HOST']."> \n";

mail($to, $subject, $message, $headers);
        
    }
}

public function update($id,$col,$value){
    global $crypt;
        if($this->is_user($id)){
        $id = $crypt->decrypt($id,'USER');
        $result = $this->db->prepare("UPDATE " . PFX . "users SET `$col` = '$value' WHERE id = :id");
        $result->bindParam(':id', $id);
		$result->execute();
        if($result){
        return true;
        }
        $this->error = "Error occurred";
        return false;
        }
        $this->error = "No such customer exists";
		return false;
		
}

public function updateve($email,$col,$value){
    global $crypt;
    
    $email = trim($email);
        $result = $this->db->prepare("UPDATE " . PFX . "users SET `$col` = '$value' WHERE email = :email");
  		$result->bindParam(':email', $email);
		$result->execute();
        if($result){
        return true;
        }
        $this->error = "Error occurred";
        return false;
		
}

public function add_funds($id,$amt){
    global $transaction;
    
        if(!is_numeric($amt)){
        $this->error = 'Invalid amount';
        return false;
        }
        $curBal = $this->details($id);
        $curBal = $curBal['balance'];
        $newBal = $amt + $curBal;
        $result = $this->update($id,'balance',$newBal);
        $result = $transaction->add($id,$amt,$product='0','3','1');
        if($result){
        return true;
        }
        $this->error = 'Unknown error';
        return false;
        
}

public function top(){
	global $crypt;
	
	    $result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE active = 1 ORDER BY `purchases` DESC LIMIT 0 , 50");
		$result->execute();
	    $users = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
		$row['id']= $crypt->encrypt($row['id'],'USER');
		$users[]=$row;
	    }	
	    return $users;
	    
}

public function banned($start,$total){
	global $crypt;
	
	    $result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE active = 0 LIMIT $start , $total");
		$result->execute();
	    $users = array();
		while($row=$result->fetch(PDO::FETCH_ASSOC)){
        $row['id']= $crypt->encrypt($row['id'],'USER');
		$users[]=$row;
	    }	
	    return $users;
	    
}

public function countBanned(){
	global $crypt;
	
	    $result = $this->db->prepare("SELECT count(*) FROM  " . PFX . "users WHERE active = 0");
		$result->execute();
		$users = $result->fetchColumn();
	    return $users;
	    
}

public function inactive(){
	global $crypt;
	
	    $result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE verified = 0");
		$result->execute();
	    $users = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
        $row['id']= $crypt->encrypt($row['id'],'USER');
		$users[]=$row;
	    }
	    return $users;
	    
}

public function newUsers(){
	global $crypt;
	
	    $result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE active = 1 ORDER BY `id` DESC LIMIT 0 , 50");
		$result->execute();
	    $users = array();
	    while($row=$result->fetch(PDO::FETCH_ASSOC)){
        $row['id']= $crypt->encrypt($row['id'],'USER');
		$users[]=$row;
	    }
	    return $users;
	    
}

// FrontEnd

public function is_loggedin(){
		
		if(isset($_SESSION['auth']) && isset($_SESSION['curr_user']) && isset($_SESSION['token'])){
		$checksum = md5($_SESSION['curr_user'] . 'dsp' . date('ymd'));
			if($checksum == $_SESSION['token']){
			return true;
			}
			return false;
		}
		return false;	
	}
	
public function login($email,$password){
	global $crypt;
	
	$password = md5($password);
	$result = $this->db->prepare("SELECT * FROM  " . PFX . "users WHERE active = 1  AND email = '$email' AND password = '$password'");
	$result->execute();
	while($row=$result->fetch(PDO::FETCH_ASSOC)){
	$row['id']= $crypt->encrypt($row['id'],'USER');
		$_SESSION['uid'] = $row['id'];
		$_SESSION['curr_user'] = $row['email'];
		$_SESSION['token'] = md5($row['email'] . 'dsp' . date('ymd'));
		$_SESSION['auth'] = true;
		$_SESSION['start'] = time();
		$_SESSION['expire'] = $_SESSION['start'] + (1 * 10);
		return true;
	}
	$this->error = 'Invalid Username / Password';
	return false;
}

public function LoginExpired(){

	$login_session_duration = 5; 
	$_SESSION['loggedin_time'] = time();
	$current_time = time(); 
	if(isset($_SESSION['loggedin_time']) and isset($_SESSION["uid"])){  
		if(((time() - $_SESSION['loggedin_time']) > $login_session_duration)){ 
			return true; 
		} 
	}
	/*$a = 10;
	$b = 5;
	if ($a > $b) {
		return true; 
	}*/
	return false;
}

public function logout(){
	session_destroy();
	header("location:login.php");
	exit;
}	

public function add($name,$username,$email,$password){ //register.php
	global $setting;
		if($this->is_new_user($email,$username)){
		$date = date("Y-m-d H:i:s");
		$password = md5($password);
		$profileimg = '../system/assets/uploads/user-img/default.png';
		$add = $this->db->prepare("INSERT INTO " . PFX . "users (`id`, `fname`, `username`, `email`, `password`,`active`,`created`,`profile`,`allow_email`,`purchases`,`balance`,`verified`,`password_recover`,`moderator`) VALUES (NULL, '$name', '$username' , '$email', '$password', '1', '$date', '$profileimg', '1', '0','0','0','0','0')");
        $add->execute();
	if($add){
	
	$headers  = "MIME-Version: 1.0\n";
	$headers .= "From: ".$setting['site_name']." <noreply@".$_SERVER['HTTP_HOST']."> \n";
    $headers .= "Content-type: text/html; charset=utf-8\n";
    $headers .='Content-Transfer-Encoding: 8bit\n';
	$subject = "Account all set up!";
	$message = '<html>
         <head>
           <meta charset="utf-8" />
         </head>
         <body>
           <font color="#303030";>
             <div align="center">
               <table width="600px">
                 <tr>
                   <td>
                   <div align="center"><img src="'.$setting['website_url']."/system/assets/uploads/img/".$setting['site_favicon'].'" width="80" height="80"></div><br>
                     <div align="center"><font size="4">Hi, <b>'.$email .'</b>!</font></div><br>
                     <div align="center">Congratulations, you have successfully completed your registration!</div><hr>
                   </td>
                 </tr>
                 <br><br>
                 <tr>
                   <td align="center">
                     <font size="2">
                       Copyright &copy; '.$setting['site_name'].'
                     </font>
                   </td>
                 </tr>
               </table>
             </div>
           </font>
         </body>
         </html>';   
	mail($email,$subject,$message,$headers);
	$this->msg = "Registered Successfully";
		return $this->db->lastInsertId();
		}
		$this->error = "Oops something wrong happened. Please try later.";
		return false;
	}
	$this->error = "User already exists with email id";
	return false;
	}

	public function newLogo($email, $email_user){ //register.php
		global $setting;


		$headers  = "MIME-Version: 1.0\n";
		$headers .= "From: ".$setting['site_name']." <noreply@".$_SERVER['HTTP_HOST']."> \n";
	    $headers .= "Content-type: text/html; charset=utf-8\n";
	    $headers .='Content-Transfer-Encoding: 8bit\n';
		$subject = "New logo uploaded to Logotic!";
		$message = '<html>
	         <head>
	           <meta charset="utf-8" />
	         </head>
	         <body>
	           <font color="#303030";>
	             <div align="center">
	               <table width="600px">
	                 <tr>
	                   <td>
	                   <div align="center"><img src="'.$setting['website_url']."/system/assets/uploads/img/".$setting['site_favicon'].'" width="80" height="80"></div><br>
	                     <div align="center"><font size="4">New logo uploaded by: <b>'.$email_user .'</b>!</font></div><br>
	                     <div align="center">Cool!!! There is a new logo that was uploaded to Logotic.</div><hr>
	                   </td>
	                 </tr>
	                 <br><br>
	                 <tr>
	                   <td align="center">
	                     <font size="2">
	                       Copyright &copy; '.$setting['site_name'].'
	                     </font>
	                   </td>
	                 </tr>
	               </table>
	             </div>
	           </font>
	         </body>
	         </html>';   
		mail($email,$subject,$message,$headers);
		$this->msg = "Files uploaded successfully";
	}


	
		public function is_new_user($email,$username){
	
		$result = $this->db->prepare("SELECT id FROM  " . PFX . "users WHERE email = '$email' AND username = '$username' OR username = '$username' OR email = '$email'");
        $result->execute();
		if ($result->fetchColumn() == 0){
    	return true;
		}
		$this->error = "User already exists";
		return false;
		
	}
	
	public function get_pass($email){
		$email = trim($email);
        $result = $this->db->prepare("INSERT password FROM  " . PFX . "users WHERE email = :email");
        $result->bindParam(':email', $email);
        $result->execute();
		while($row=$result->fetch(PDO::FETCH_ASSOC)){
		return md5($row['password']);	
		}
	$this->error = "User not found";
		return false;
}

public function add_purchase($id){
global $crypt;
	
		if($this->is_user($id)){
		$purchases = $this->details($id);
		$purchases = $purchases['purchases'] + 1;
		$id = $crypt->decrypt($id,'USER');
		$update = $this->db->prepare("UPDATE " . PFX . "users  SET `purchases` = '$purchases' WHERE id ='$id'");
        $update->execute();
		if($update){
		return true;
		}
		return false;
		}
	}
}
