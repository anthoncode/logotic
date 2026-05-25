<?php

require_once('db.php');

ob_start();
header("Access-Control-Allow-Origin: *");
session_name('DSP');
session_start();

include_once 'classes/class.coupon.php';
include_once 'classes/class.crypt.php';
include_once 'classes/class.customer.php';
include_once 'classes/class.product.php';
include_once 'classes/class.sale.php';
include_once 'classes/class.settings.php';
include_once 'classes/class.transaction.php';
include_once 'classes/class.validate.php';
include_once 'classes/class.news.php';
include_once 'classes/class.wishlist.php';

$DB_con = new PDO('mysql:host=' . DB_host . ';dbname=' . DB_name, DB_user, DB_pass);
$DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$coupon = new Coupon($DB_con);
$crypt = new encryption_class($DB_con);
$product = new Product($DB_con);
$settings = new Settings($DB_con);
//$transaction = new Transaction($DB_con);
$validate = new Validate($DB_con);
$user = new Customer($DB_con);
$purchases = new Sale($DB_con);
$newsl = new News($DB_con);
$wishlist = new Wishlist($DB_con);



ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

if (isset($_GET['pid'])) {
    $id = $_GET['pid'];

    $pro_id = $id;
    $ip = $_SERVER['REMOTE_ADDR'];
    $date = date("Y-m-d H:i:s");


    $error = false;
    $error = (!$product->is_product($id) ? $products->error : $error);
} else {
    echo 'Invalid Data!';
    die();
}
if (!$error) {
    $file = $product->details($id);
    /*if($file['free'] == 0){
    echo'This item is not available for FREE!';
    die();
}*/



    //$dlname = $file['dlname'];
    $file_url = $file['icon_img'];
    $file_location = '../system/assets/uploads/vector-files/' . $file_url;

    //$result = $product->addreview($productid,$uid,$message1,$rating);
    $downloaded = $product->addDownload($pro_id, $ip, $date);
    //Works


    ob_clean();
    ob_end_flush();
    header('Content-type: application/zip');
    header("Content-Transfer-Encoding: Binary");
    header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");
    readfile($file_location);
    //unlink($file_location);

}
echo $error;
