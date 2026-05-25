<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
require_once('../../config-user.php');

if(empty($_POST['message']))
{
	echo '<div class="ui error visible message"><p>Make sure you tell us what is wrong!</p></div>';
	die();
}
$name = $_POST['name'];
$email = $_POST['email'];
$subject1 = $_POST['subject'];
$message1 = $_POST['message'];
$to = $setting['support_email'];

			$headers  = "MIME-Version: 1.0\n";
			    $headers .= "From: $email \n";
    $headers .= "Content-type: text/html; charset=utf-8\n";
    $headers .='Content-Transfer-Encoding: 8bit\n';
	$subject = 'Product Support - '.$subject1.'';
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
                   <div align="center"><img src="'.$setting['site_favicon'].'" width="80" height="80"></div><br>
                     <div align="center"><font size="4">Hi, <b>Author</b>!</font></div><br>
                     <div align="center"><font size="2">The user below has requested for support on this item!</font></div><br>
                     <div align="center">Name: <b>'.$name.'</b></div>
                     <div align="center">Email: <b>'.$email.'</b></div><hr>
                     <div align="center">Product Name: <b>'.$subject1.'</b></div>
                     <div align="center">Message: <b>'.$message1.'</b></div><hr>
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

echo '<span class="text-success">Message Sent!</span>';

//
}
/*else {
	header('location:'.$url.'');
}*/
?>