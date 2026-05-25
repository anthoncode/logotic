<?php

//DB Connect
include_once 'db.php';

try
{
	$DB_con = new PDO('mysql:host='. DB_host .';dbname=' .DB_name. ';charset=utf8', DB_user,DB_pass);
	$DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$DB_con->exec("set names utf8");
}
catch(PDOException $e)
{
	echo $e->getMessage();
}

$myLang = "en";
include_once('lang/' . $myLang . '.php');

function sanitize_output($buffer)
{
    $search = array(
        '/\>[^\S ]+/s', //strip whitespaces after tags, except space
        '/[^\S ]+\</s', //strip whitespaces before tags, except space
        '/(\s)+/s'  // shorten multiple whitespace sequences
        );
    $replace = array(
        '>',
        '<',
        '\\1'
        );
    $buffer = preg_replace($search, $replace, $buffer);

    return $buffer;
}
/*minify html code*/
//ob_start("sanitize_output");

header("Access-Control-Allow-Origin: *");
session_name('DSP');

session_start();



//Important classes
#include_once 'classes/class.crud.php';
#include_once 'classes/class.auth.php';
include_once 'classes/class.coupon.php';
include_once 'classes/class.crypt.php';
include_once 'classes/class.customer.php';
include_once 'classes/class.product.php';
include_once 'classes/class.sale.php';
include_once 'classes/class.settings.php';
include_once 'classes/class.transaction.php';
include_once 'classes/class.validate.php';
include_once 'classes/class.wishlist.php';
include_once 'classes/class.pages.php';

#$crud = new crud($DB_con);
#$auth = new Auth($DB_con);
$coupon = new Coupon($DB_con);
$crypt = new encryption_class($DB_con);
$product = new Product($DB_con);
$settings = new Settings($DB_con);
//$transaction = new Transaction($DB_con);
$validate = new Validate($DB_con);
$wishlist = new Wishlist($DB_con);
$pages = new Pages($DB_con);

$user = new Customer($DB_con);
$purchases = new Sale($DB_con);

//Fetch Settings
$setting = $settings->get_all();

//Login Checker
if(isset($_REQUEST['login'])){
$error =false;
		if(empty($_REQUEST['email']) || empty($_REQUEST['pwd'])){
			$error = 'Enter your email and password';
		}
		else{
		$email=trim($_REQUEST['email']);
		$password=trim($_REQUEST['pwd']);
	if(!$user->login($email,$password)){

		$error = $user->error;
	}
	}
	if(isset($_REQUEST['ajax'])){
	echo ($error?$error:"success");
	exit;
	}
}
if($user->is_loggedin()){
define('USER',$_SESSION['curr_user']);

	$userDetails = $user->details($_SESSION['uid']);
}

if(basename($_SERVER["PHP_SELF"]) == 'login.php'){

if(isset($_REQUEST['logout'])){
	$user->logout();
}
if($user->is_loggedin()){ 
echo'<script>window.location = "'.$setting['website_url'].'/user/index.php"</script>';
}
}

error_reporting(E_ALL);
