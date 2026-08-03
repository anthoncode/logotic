<?php
//DB Details
ob_start();//se pueden enviar los headers en cualquier lugar del documento.
include_once 'db.php';

//DB Connect
try
{
	$DB_con = new PDO('mysql:host='. DB_host .';dbname=' .DB_name. ';charset=utf8' ,DB_user,DB_pass);
	$DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e)
{
	echo $e->getMessage();
}

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

//ob_start("sanitize_output");
// ── Configuración de sesión del admin ──
// Detecta si estamos en local o producción
$isLocal = (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false)
        || (strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false);

// Local: 7 días (cómodo para desarrollo) · Producción: 4 horas (seguro)
$adminSessionLife = $isLocal ? 604800 : 14400;

ini_set('session.gc_maxlifetime', $adminSessionLife);
ini_set('session.cookie_lifetime', $adminSessionLife);
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Carpeta propia de sesiones
$adminSessionPath = __DIR__ . '/sessions';
if (!is_dir($adminSessionPath)) {
    @mkdir($adminSessionPath, 0700, true);
}
if (is_dir($adminSessionPath) && is_writable($adminSessionPath)) {
    ini_set('session.save_path', $adminSessionPath);
}

session_name('DSP_ADMIN');
session_set_cookie_params($adminSessionLife);
session_start();



//Important classes
#include_once 'classes/class.crud.php';
include_once 'classes/class.auth.php';
include_once 'classes/class.crypt.php';
include_once 'classes/class.customer.php';
include_once 'classes/class.product.php';
include_once 'classes/class.sale.php';
include_once 'classes/class.settings.php';
include_once 'classes/class.transaction.php';
include_once 'classes/class.validate.php';
include_once 'classes/class.news.php';
include_once 'classes/class.pages.php';

#$crud = new crud($DB_con);
$auth = new Auth($DB_con);
$crypt = new encryption_class($DB_con);
$product = new Product($DB_con);
$settings = new Settings($DB_con);
//$transaction = new Transaction($DB_con);
$validate = new Validate($DB_con);
$sale = new Sale($DB_con);
$newsl = new News($DB_con);
$pages = new Pages($DB_con);


//Fetch Settings
$setting = $settings->get_all();

//Login Checker
if($auth->is_loggedin())
{
	$setting = $settings->get_all();

	$userDetails = $auth->details($_SESSION['uid']);
}

if(!$auth->is_loggedin() && (basename($_SERVER["PHP_SELF"]) != 'login.php') && (basename($_SERVER["PHP_SELF"]) != 'new.php')&& (basename($_SERVER["PHP_SELF"]) != 'recover.php') ){
	header("location:login.php");
	exit;
}

$current_file = explode('/', $_SERVER['SCRIPT_NAME']);
$current_file = end($current_file);

if($auth->is_loggedin()){
define('USER',$_SESSION['curr_user']);
$setting = $settings->get_all();
	$userDetails = $auth->details($_SESSION['uid']);
	
	if($current_file !== 'resetpwd.php' && $current_file != 'login.php?logout' && $userDetails['password_recover'] == 1){
        header('Location: resetpwd.php?force');
        exit();
    	}
	
}

if(basename($_SERVER["PHP_SELF"]) == 'login.php'){


if(isset($_REQUEST['login'])){

		

		if ($setting['captcha'] == '1') {
			$google_captcha = empty($_POST['g-recaptcha-response']);
		}



		if(empty($_REQUEST['email']) || empty($_REQUEST['pwd']) || $google_captcha ){
			$error = 'Please enter Email and Password';
		}
		else{
		$email=trim($_REQUEST['email']);
		$password=trim($_REQUEST['pwd']);
			if(!$auth->login($email,$password)){
				$error = $auth->error;
			}
		}

		//captcha
		$ip = $_SERVER['REMOTE_ADDR'];
		$captcha = $_POST['g-recaptcha-response'];
		$secretkey = $setting['secret_key_captcha'];
		$requestCapt = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretkey&response=$captcha&remoteip=$ip");
		$attrib = json_decode($requestCapt ,TRUE);
		if (!$attrib['success']) {
			$error = 'The field is required';
		}
		//

}

if(isset($_REQUEST['logout'])){
	$auth->logout();
}
if($auth->is_loggedin()){
	header("location: index.php");	
}
}

error_reporting(E_ALL);
