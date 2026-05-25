<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('../config-user.php');
$paypal_email = $setting['paypal_email']; // Set Your Paypal Email HERE
$redir = $_SESSION['payment']['redir'];
if(isset($_REQUEST) && isset($_REQUEST['crf'])){
$order_id= $_REQUEST['transaction_id'];
$a_amount = $_REQUEST['amount'];
$c_amount = $_REQUEST['amount'];
$detail = $_REQUEST['detail'];
$amount = $_REQUEST['amount'];
$admin_curr = $_REQUEST['currency'];
$chk_curr = $_REQUEST['currency'];


}
class paypal_class {
    
   var $last_error;                 
   
   var $ipn_log;                    
   
   var $ipn_log_file;               
   var $ipn_response;               
   var $ipn_data = array();         
   
   var $fields = array();           

   
   function __construct() {
       
     
      $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
      
      $this->last_error = '';
      
      $this->ipn_log_file = '.ipn_results.log';
      $this->ipn_log = true; 
      $this->ipn_response = '';
      
    

      $this->add_field('rm','2');           
      $this->add_field('cmd','_xclick'); 
      
   }
   
   function add_field($field, $value) {
      
   $this->fields["$field"] = $value;
   }

   function submit_paypal_post() {
 
     

      echo "<html>\n";
      echo "<head><title>Redirecting you to PayPal...</title><link href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css' rel='stylesheet'  type='text/css'></head>\n";
      echo "<body onLoad=\"document.forms['paypal_form'].submit();\">\n<br><br><div class=\"card\">";
      echo " <center><img src=\"../assets/uploads/img/91.gif\"></center>\n";
      echo "<center><h2>Redirecting you to PayPal...</h2></center>\n";
      echo "<form method=\"post\" name=\"paypal_form\" ";
      echo "action=\"".$this->paypal_url."\">\n";

      foreach ($this->fields as $name => $value) {
         echo "<input type=\"hidden\" name=\"$name\" value=\"$value\"/>\n";
      }
      echo "\n";
//      echo "<input type=\"submit\" class=\"btn btn-primary\" value=\"Click Here\"></center>\n";
      
      echo "</form>\n";
      echo "</div></body></html>\n";
    
   }
   
   function validate_ipn() {

      
      $url_parsed=parse_url($this->paypal_url);        

     
      $post_string = '';    
      foreach ($_POST as $field=>$value) { 
         $this->ipn_data["$field"] = $value;
         $post_string .= $field.'='.urlencode(stripslashes($value)).'&'; 
      }
      $post_string.="cmd=_notify-validate"; // append ipn command

      // open the connection to paypal
      $fp = fsockopen($url_parsed[host],"80",$err_num,$err_str,30); 
      if(!$fp) {
          
         // could not open the connection.  If loggin is on, the error message
         // will be in the log.
         $this->last_error = "fsockopen error no. $errnum: $errstr";
         $this->log_ipn_results(false);       
         return false;
         
      } else { 
 
         // Post the data back to paypal
         fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n"); 
         fputs($fp, "Host: $url_parsed[host]\r\n"); 
         fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
         fputs($fp, "Content-length: ".strlen($post_string)."\r\n"); 
         fputs($fp, "Connection: close\r\n\r\n"); 
         fputs($fp, $post_string . "\r\n\r\n"); 

         // loop through the response from the server and append to variable
         while(!feof($fp)) { 
            $this->ipn_response .= fgets($fp, 1024); 
         } 

         fclose($fp); // close connection

      }
      
      if (preg_match("VERIFIED",$this->ipn_response)) {
  
         // Valid IPN transaction.
         $this->log_ipn_results(true);
         return true;       
         
      } else {
  
         // Invalid IPN transaction.  Check the log for details.
         $this->last_error = 'IPN Validation Failed.';
         $this->log_ipn_results(false);   
         return false;
         
      }
      
   }
   
   function log_ipn_results($success) {
       
      if (!$this->ipn_log) return;  // is logging turned off?
      
      // Timestamp
      $text = '['.date('m/d/Y g:i A').'] - '; 
      
      // Success or failure being logged?
      if ($success) $text .= "SUCCESS!\n";
      else $text .= 'FAIL: '.$this->last_error."\n";
      
      // Log the POST variables
      $text .= "IPN POST Vars from Paypal:\n";
      foreach ($this->ipn_data as $key=>$value) {
         $text .= "$key=$value, ";
      }
 
      // Log the response from the paypal server
      $text .= "\nIPN Response from Paypal Server:\n ".$this->ipn_response;
      
      // Write to log
      $fp=fopen($this->ipn_log_file,'a');
      fwrite($fp, $text . "\n\n"); 

      fclose($fp);  // close file
   }

   function dump_fields() {
 
      // Used for debugging, this function will output all the field/value pairs
      // that are currently defined in the instance of the class using the
      // add_field() function.
      
      echo "<h3>paypal_class->dump_fields() Output:</h3>";
      echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>"; 
      
      ksort($this->fields);
      foreach ($this->fields as $key => $value) {
         echo "<tr><td>$key</td><td>".urldecode($value)."&nbsp;</td></tr>";
      }
 
      echo "</table><br>"; 
   }
}         

$p = new paypal_class;             // initiate an instance of the class
//$p->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';   // testing paypal url

$p->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';     // paypal url
            
// setup a variable for this script (ie: 'http://www.micahcarrick.com/paypal.php')
$this_script = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];

// if there is not action variable, set the default action of 'process'
if (empty($_GET['action'])) $_GET['action'] = 'process';  

switch ($_GET['action']) {
    
   case 'process':      // Process and order...

      
      
      $p->add_field('business', $paypal_email);
      $p->add_field('return', $this_script.'?action=success');
      $p->add_field('cancel_return', $this_script.'?action=cancel');
     // $p->add_field('notify_url', $this_script.'?action=ipn');
      $p->add_field('item_name', $detail);
      $p->add_field('amount', $amount);

      $p->submit_paypal_post(); // submit the fields to paypal
     
      break;
      
   case 'success':      // Order was successful...
   
   
 
if(isset($_SESSION['payment']['id'] )){
$order_id = $_SESSION['payment']['id'] ;
}else{
$order_id = 0;
$_SESSION['payment']['id']  = $order_id;
}
switch ($_POST['payment_status']){
	case 'Completed':
		$transaction->update($_SESSION['payment']['id'] ,'callback','1');
						if($_SESSION['payment']['action']== "deposit"){
				$curr = $user->details($_SESSION['uid']);
				$newBal = $curr['balance'] + $_SESSION['payment']['amount'];
				$user->update($_SESSION['uid'],'balance',$newBal);
				}	
				if($_SESSION['payment']['action']== "purchase"){
                $stockminus = $purchases->updatestock($_SESSION['payment']['product_id']);
				$buy = $purchases->add($_SESSION['uid'],$_SESSION['payment']['product_id'],$_SESSION['payment']['id'],'1');
				}
		
					$headers  = "MIME-Version: 1.0\n";
			    $headers .= "From: ".$setting['site_name']." <noreply@".$_SERVER['HTTP_HOST']."> \n";
    $headers .= "Content-type: text/html; charset=utf-8\n";
    $headers .='Content-Transfer-Encoding: 8bit\n';
	$subject = "Purchase Successful!";
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
                     <div align="center"><font size="4">Hi, <b>'.$_SESSION['curr_user'].'</b>!</font></div><br>
                     <div align="center">You just made a transaction of: <b>'.$setting['currency_sym']. $_SESSION['payment']['amount'].'</b></div>
                     <div align="center">Transaction ID: <b>'.$_SESSION['payment']['id'].'</b></div><hr>
                     <div align="center"><b>Thankyou for shopping with us!</b></div>
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
	mail($_SESSION['curr_user'],$subject,$message,$headers);
				
				
unset($_SESSION['payment']);header('location:../../user/'.$redir.'?msg=Transaction Successful!&type=alert-success');
		exit;	break;	
	case 'Pending':
	unset($_SESSION['payment']);header('location:../../user/'.$redir.'?msg=Transaction Failed&type=alert-danger');
			
	exit;	break;
	unset($_SESSION['payment']);header('location:../../user/'.$redir.'?msg=Transaction Failed&type=alert-danger');
	exit;	break;
	default:
	unset($_SESSION['payment']);header('location:../../user/'.$redir.'?msg=Transaction Failed&type=alert-danger');
			
}
  case 'cancel':  
unset($_SESSION['payment']);header('location:../../user/'.$redir.'?msg=Transaction Failed&type=alert-danger');
		break;  
}

?>