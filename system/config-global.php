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
	// No mostrar el error de DB al visitante (riesgo de seguridad); loguearlo
	error_log('DB Connection failed: ' . $e->getMessage());
	http_response_code(503);
	die('Service temporarily unavailable. Please try again shortly.');
}

// ═══════════════════════════════════════════════════════
// Sistema de manejo de errores
// ═══════════════════════════════════════════════════════

// Detección de entorno
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'])
           || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false
           || strpos($_SERVER['HTTP_HOST'] ?? '', '.test') !== false;

// Carpeta propia de logs (NO en la raíz del dominio)
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) { @mkdir($logDir, 0755, true); }
$errorLogFile = $logDir . '/php-errors.log';

// Configuración según entorno
if ($isLocal) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);   // nunca mostrar al visitante en producción
    error_reporting(E_ALL);          // capturamos todo, filtramos en el handler
}
ini_set('log_errors', 1);
ini_set('error_log', $errorLogFile);

// Rotación: si el archivo pasa de 5 MB, conserva solo la mitad reciente
if (file_exists($errorLogFile) && filesize($errorLogFile) > 5 * 1024 * 1024) {
    $lines = file($errorLogFile);
    file_put_contents($errorLogFile, implode('', array_slice($lines, (int)(count($lines) / 2))));
}

// Función: guardar error en la tabla (agrupando duplicados)
function logErrorToDB($DB_con, $level, $message, $file, $line) {
    try {
        $chk = $DB_con->prepare("SELECT id FROM " . PFX . "error_logs
            WHERE message = :msg AND file = :file AND line = :line AND resolved = 0 LIMIT 1");
        $chk->execute([':msg' => $message, ':file' => $file, ':line' => $line]);
        $existing = $chk->fetchColumn();

        if ($existing) {
            $DB_con->prepare("UPDATE " . PFX . "error_logs
                SET count = count + 1, last_seen = NOW() WHERE id = :id")
                ->execute([':id' => $existing]);
        } else {
            $DB_con->prepare("INSERT INTO " . PFX . "error_logs
                (level, message, file, line, url, ip, user_agent, created_at, last_seen)
                VALUES (:level, :msg, :file, :line, :url, :ip, :ua, NOW(), NOW())")
                ->execute([
                    ':level' => $level,
                    ':msg'   => $message,
                    ':file'  => $file,
                    ':line'  => $line,
                    ':url'   => substr($_SERVER['REQUEST_URI'] ?? '', 0, 255),
                    ':ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
                    ':ua'    => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
                ]);
        }
    } catch (Throwable $e) {
        // Si falla el guardado en DB, el archivo de log lo respalda
    }
}

// Handler de errores PHP
set_error_handler(function($severity, $message, $file, $line) use ($DB_con) {
    if (!(error_reporting() & $severity)) return false;

    $levels = [
        E_ERROR => 'error', E_WARNING => 'warning', E_NOTICE => 'notice',
        E_USER_ERROR => 'error', E_USER_WARNING => 'warning', E_USER_NOTICE => 'notice',
        E_DEPRECATED => 'deprecated', E_STRICT => 'notice',
    ];
    $level = $levels[$severity] ?? 'error';

    // Solo guardar en DB los relevantes (no notices/deprecations)
    if (in_array($level, ['error', 'warning'])) {
        logErrorToDB($DB_con, $level, $message, $file, $line);
    }
    return false;  // PHP también lo escribe al archivo
});

// Handler de excepciones no capturadas
set_exception_handler(function($e) use ($DB_con) {
    logErrorToDB($DB_con, 'error', $e->getMessage(), $e->getFile(), $e->getLine());
    error_log("Uncaught exception: " . $e->getMessage());
});

// Handler de errores fatales (shutdown)
register_shutdown_function(function() use ($DB_con) {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        logErrorToDB($DB_con, 'fatal', $err['message'], $err['file'], $err['line']);
    }
});

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