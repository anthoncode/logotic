<?php
/*include_once 'db.php';
ob_start("sanitize_output");
header("Access-Control-Allow-Origin: *");
session_name('DSP');
session_start();*/

// Important classes
/*include_once 'classes/class.coupon.php';
include_once 'classes/class.crypt.php';
include_once 'classes/class.customer.php';
include_once 'classes/class.product.php';
include_once 'classes/class.sale.php';
include_once 'classes/class.settings.php';
include_once 'classes/class.transaction.php';
include_once 'classes/class.validate.php';
include_once 'classes/class.news.php';
include_once 'classes/class.wishlist.php';*/

/*$coupon = new Coupon($DB_con);
$crypt = new encryption_class($DB_con);
$product = new Product($DB_con);
$settings = new Settings($DB_con);
$transaction = new Transaction($DB_con);
$validate = new Validate($DB_con);
$user = new Customer($DB_con);
$purchases = new Sale($DB_con);
$newsl = new News($DB_con);
$wishlist = new Wishlist($DB_con);*/

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$id = $_GET['id'];
$error = false;
$error = (!$product->is_product($id) ? $products->error : $error);
$error = (!$purchases->is_purchased($_SESSION['uid'], $id) ? $purchases->error : $error);
if (!$error) {
	$file = $product->details($id);
	//$dlname = $file['dlname'];
	$file_url = $file['file'];
	$file_location = '../system/assets/uploads/product-files/' . $file_url;
	$downloaded = $purchases->update($_SESSION['uid'], $id, 'downloaded', '1');
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
