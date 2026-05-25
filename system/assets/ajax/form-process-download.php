<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    require_once '../../config-user.php';

    if (empty($_POST['name'])) {
        echo '<div class="ui error visible message"><p>We need your name!!!</p></div>';
        die();
    }
    if (empty($_POST['email'])) {
        echo '<div class="ui error visible message"><p>We need your email!</p></div>';
        die();
    }

    $name      = $_POST['name'];
    $productid = $_POST['pid'];
    $uid       = $_POST['uid'];
    $email     = $_POST['email'];
//$to = $email;

    /*    $headers  = "MIME-Version: 1.0\n";
    $headers .= "From: $setting['support_email'] \n";
    $headers .= "Content-type: text/html; charset=utf-8\n";
    $headers .='Content-Transfer-Encoding: 8bit\n';
    $subject = 'Free Product Download!';
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
    <div align="center"><font size="4">Hi, <b>'.$name.'</b>!</font></div><br>
    <div align="center"><font size="2">Here is your FREE file!</font></div><br>
    <div align="center"><a href="'.$setting['website_url'].'/system/download.php?pid='.$productid.'">Download!</a></div><hr>
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

    mail($to, $subject, $message, $headers); *///Sends email with download link

    echo '<hr><a rel="nofollow" href="' . $setting['website_url'] . '/system/download.php?pid=' . $productid . '">Start Download!</a>';
    /*$setting['website_url'] . '/system/download.php?pid=' . $productid*/

//
}
/*else {
header('location:'.$url.'');
}*/
