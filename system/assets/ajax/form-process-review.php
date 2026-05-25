<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
require_once('../../config-user.php');

if(empty($_POST['review']))
{
	echo '<div class="ui error visible message"><p>Make sure you write something...</p></div>';
	die();
}

$rating = $_POST['rating'];
$name = $_POST['name'];
$productid = $_POST['pid'];
$uid = $_POST['uid'];
$email = $_POST['email'];
$subject1 = $_POST['subject'];
$message1 = $_POST['review'];
$to = $setting['support_email'];

if($product->is_alreadyadd($uid,$productid)){
    	echo '<div class="alert alert-danger" role="alert"><i class="fas fa-times-circle"></i> You have already left a review on this item! Delete your current one and try again!</div>';
	die();
}

			$headers  = "MIME-Version: 1.0\n";
			    $headers .= "From: $email \n";
    $headers .= "Content-type: text/html; charset=utf-8\n";
    $headers .='Content-Transfer-Encoding: 8bit\n';
	$subject = 'New Product Review - '.$subject1.'';
	$message = '<html>
         <head>
           <meta charset="utf-8" />
         </head>
         <body>
           <font color="#303030";>
             <div align="center">
               <table width="500px">
                 <tr>
                   <td>
                   <div align="center"><img src="'.$setting['website_url']."/system/assets/uploads/img/".$setting['site_favicon'].'" width="80" height="80"></div><br>
                     <div align="center"><font size="4">Hi, <b>Author</b>!</font></div><br>
                     <div align="center"><font size="2">The user below has left a review on your item!</font></div><br>
                     <div align="center">Name: <b>'.$name.'</b></div>
                     <div align="center">Email: <b>'.$email.'</b></div><hr>
                     <div align="center">Product Name: <b>'.$subject1.'</b></div>
                     <div align="center">Review: <b>The user has given you a '.$rating.' star rating and said...'.$message1.'</b></div><hr>
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

mail($to, $subject, $message, $headers);

$result = $product->addreview($productid,$uid,$message1,$rating);

echo '<span class="text-success">Review Posted!</span>';

//
}
/*else {
	header('location:'.$url.'');
}*/
?>