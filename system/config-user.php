<?php

// DB Connect
include_once 'db.php';

try {
	$DB_con = new PDO('mysql:host=' . DB_host . ';dbname=' . DB_name, DB_user, DB_pass);
	$DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

catch(PDOException $e) {
	echo $e->getMessage();
}

$myLang = "en";
include_once('lang/' . $myLang . '.php');

function sanitize_output($buffer)
{
	$search = array(
		'/\>[^\S ]+/s', //strip whitespaces after tags, except space
		'/[^\S ]+\</s', //strip whitespaces before tags, except space
		'/(\s)+/s'

		// shorten multiple whitespace sequences

	);
	$replace = array(
		'>',
		'<',
		'\\1'
	);
	$buffer = preg_replace($search, $replace, $buffer);
	return $buffer;
}

function get_timeago($ptime)
{
	$estimate_time = time() - $ptime;
	if ($estimate_time < 1) {
		return 'less than 1 second ago';
	}

	$condition = array(
		12 * 30 * 24 * 60 * 60 => 'year',
		30 * 24 * 60 * 60 => 'month',
		24 * 60 * 60 => 'day',
		60 * 60 => 'hour',
		60 => 'minute',
		1 => 'second'
	);
	foreach($condition as $secs => $str) {
		$d = $estimate_time / $secs;
		if ($d >= 1) {
			$r = round($d);
			return '' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
		}
	}
}

//ob_start("sanitize_output");
header("Access-Control-Allow-Origin: *");
session_name('DSP');
session_start();//inicio de sesión
  //$_SESSION["start"] = time();


/*if (isset($_SESSION["uid"])) {
  if (time() - $_SESSION["start"] > 600) {
    echo "hola"; echo time(); echo "_"; echo $_SESSION["start"];
  }
 else {
  echo "chau";
}
}*/

// Important classes
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

// Fetch Settings
$setting = $settings->get_all();

// Login Checker
if (isset($_REQUEST['login'])) {
	$error = false;
	if (empty($_REQUEST['email']) || empty($_REQUEST['pwd'])) {
		$error = 'Please enter Email and Password';
	}
	else {
		$email = trim($_REQUEST['email']);
		$password = trim($_REQUEST['pwd']);
		if (!$user->login($email, $password)) {
			$error = $user->error;
		}
	}

	if (isset($_REQUEST['ajax'])) {
		echo ($error ? $error : "success");
		exit;
	}
}

/*login download*/
if (!$user->is_loggedin() && (basename($_SERVER["PHP_SELF"]) != 'facebook.php') && (basename($_SERVER["PHP_SELF"]) != 'json-load.php') && (basename($_SERVER["PHP_SELF"]) != 'buy.php') && (basename($_SERVER["PHP_SELF"]) != 'login.php') && (basename($_SERVER["PHP_SELF"]) != 'register.php') && (basename($_SERVER["PHP_SELF"]) != 'recover.php')) {
	header("location:" . $setting['website_url'] . "/user/login.php");
	exit;
}

$current_file = explode('/', $_SERVER['SCRIPT_NAME']);
$current_file = end($current_file);

if ($user->is_loggedin()) {

	define('USER', $_SESSION['curr_user']);
	$userDetails = $user->details($_SESSION['uid']);
	$wishcount = $wishlist->countAll('user_id',$crypt->decrypt($_SESSION['uid'],'USER'));
	
	if($current_file !== 'resetpwd.php' && $current_file != 'login.php?logout' && $userDetails['password_recover'] == 1){
        header('Location: resetpwd.php?force');
        exit();
    	}
	
}

if (basename($_SERVER["PHP_SELF"]) == 'login.php') {
	if (isset($_REQUEST['logout'])) {
		$user->logout();
	}

	if ($user->is_loggedin()) {
		echo '<script>window.location = "' . $setting['website_url'] . '/user/index.php"</script>';
	}
}

error_reporting(E_ALL);
