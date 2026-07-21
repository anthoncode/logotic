<?php

// ── DB Connect ──
include_once 'db.php';

try {
    $DB_con = new PDO(
        'mysql:host=' . DB_host . ';dbname=' . DB_name . ';charset=utf8',
        DB_user,
        DB_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('DB Connection failed: ' . $e->getMessage());
    http_response_code(503);
    die('Service temporarily unavailable.');
}

// ── Idioma ──
$myLang = "en";
include_once('lang/' . $myLang . '.php');

// ── Sesión segura ──
$sessionLifetime = 2592000; // 30 días

ini_set('session.gc_maxlifetime', $sessionLifetime);
ini_set('session.cookie_lifetime', $sessionLifetime);
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', 0);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

// Secure solo en HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// Carpeta propia de sesiones (evita que macOS/XAMPP limpie /tmp)
$sessionPath = __DIR__ . '/sessions';
if (!is_dir($sessionPath)) {
    @mkdir($sessionPath, 0700, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    ini_set('session.save_path', $sessionPath);
}

session_name('DSP');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Renovar la cookie en cada visita: el mes cuenta desde la última actividad
if (isset($_SESSION['uid'])) {
    setcookie(session_name(), session_id(), [
        'expires'  => time() + $sessionLifetime,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'),
    ]);
}

// ── Headers de seguridad ──
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Access-Control-Allow-Origin: *');

// ── Clases ──
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

// ── Instancias ──
$coupon   = new Coupon($DB_con);
$crypt    = new encryption_class($DB_con);
$product  = new Product($DB_con);
$settings = new Settings($DB_con);
$validate = new Validate($DB_con);
$user     = new Customer($DB_con);
$purchases = new Sale($DB_con);
$newsl    = new News($DB_con);
$wishlist  = new Wishlist($DB_con);

// ── Settings ──
$setting = $settings->get_all();

include_once 'classes/class.mailer.php';
$mailer = new Mailer($DB_con, $setting);

// ── Helpers ──
function get_timeago($ptime) {
    $estimate_time = time() - $ptime;
    if ($estimate_time < 1) return 'just now';
    $condition = [
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60       => 'month',
        24 * 60 * 60            => 'day',
        60 * 60                 => 'hour',
        60                      => 'minute',
        1                       => 'second',
    ];
    foreach ($condition as $secs => $str) {
        $d = $estimate_time / $secs;
        if ($d >= 1) {
            $r = round($d);
            return $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
        }
    }
}

// ── Archivos excluidos del check de sesión ──
$excluded = [
    'login.php', 'register.php', 'recover.php',
    'resetpwd.php', 'google-callback.php', 'google-onetap.php',
    'facebook.php', 'json-load.php', 'buy.php'
];

$currentFile = basename($_SERVER['PHP_SELF']);

// ── Logout ──
if ($currentFile === 'login.php' && isset($_REQUEST['logout'])) {
    $user->logout();
}

// ── Redirect a login si no está autenticado ──
if (!$user->is_loggedin()
    && !in_array($currentFile, $excluded)
    && !isset($_SESSION['2fa_pending'])
) {
    $redirect = urlencode($_SERVER['REQUEST_URI'] ?? '');
    header('Location: ' . $setting['website_url'] . '/user/login.php?redirect=' . $redirect);
    exit;
}

// ── Redirect si ya está logueado y va a login ──
if ($currentFile === 'login.php' && $user->is_loggedin()) {
    header('Location: ' . $setting['website_url'] . '/user/');
    exit;
}

// ── Datos del usuario logueado ──
if ($user->is_loggedin()) {
    define('USER', $_SESSION['curr_user']);
    $userDetails = $user->details($_SESSION['uid']);
    $wishcount   = $wishlist->countAll('user_id', $crypt->decrypt($_SESSION['uid'], 'USER'));

    // Forzar cambio de contraseña si fue recovery
    if (
        isset($userDetails['password_recover']) &&
        $userDetails['password_recover'] == 1 &&
        $currentFile !== 'resetpwd.php'
    ) {
        header('Location: ' . $setting['website_url'] . '/user/resetpwd.php?force');
        exit;
    }
}

error_reporting(E_ALL);