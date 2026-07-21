<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-user.php');

if (!isset($_GET['code'])) {
    header('Location: login.php');
    exit;
}

// Validar state CSRF
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    header('Location: login.php?error=invalid_state');
    exit;
}
unset($_SESSION['oauth_state']);

$clientId     = $setting['google_client_id']     ?? '';
$clientSecret = $setting['google_client_secret'] ?? '';
$redirectUri  = $setting['website_url'] . '/user/google-callback.php';

error_log('Google callback — code: ' . substr($_GET['code'], 0, 20));
error_log('Client ID empty: ' . (empty($clientId) ? 'YES' : 'NO'));
error_log('Redirect URI: ' . $redirectUri);

// ── 1. Intercambiar code por access_token ──
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_POSTFIELDS     => http_build_query([
        'code'          => $_GET['code'],
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri'  => $redirectUri,
        'grant_type'    => 'authorization_code',
    ]),
]);
$tokenResponse = curl_exec($ch);
$curlError     = curl_error($ch);
curl_close($ch);

error_log('Token response: ' . $tokenResponse);
if ($curlError) error_log('cURL error: ' . $curlError);

if (!$tokenResponse) {
    header('Location: login.php?error=google_auth_failed');
    exit;
}

$tokenData = json_decode($tokenResponse, true);

if (empty($tokenData['access_token'])) {
    error_log('Token error: ' . json_encode($tokenData));
    header('Location: login.php?error=google_auth_failed');
    exit;
}

// ── 2. Obtener info del usuario ──
$ch2 = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt_array($ch2, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $tokenData['access_token']],
]);
$userInfoResponse = curl_exec($ch2);
curl_close($ch2);

error_log('User info: ' . $userInfoResponse);

$googleUser = json_decode($userInfoResponse, true);

if (empty($googleUser['sub']) || empty($googleUser['email'])) {
    error_log('Invalid user info: ' . json_encode($googleUser));
    header('Location: login.php?error=google_auth_failed');
    exit;
}

// ── 3. Login o registro ──
$result = $user->loginWithGoogle([
    'sub'        => $googleUser['sub'],
    'email'      => $googleUser['email'],
    'name'       => $googleUser['name']       ?? '',
    'given_name' => $googleUser['given_name'] ?? $googleUser['name'] ?? 'User',
]);

if ($result === true) {
    $redirect = $_SESSION['oauth_redirect'] ?? $setting['website_url'] . '/user/';
    unset($_SESSION['oauth_redirect']);
    header('Location: ' . $redirect);
    exit;
} else {
    error_log('loginWithGoogle failed: ' . $user->error);
    header('Location: login.php?error=' . urlencode($user->error ?: 'google_auth_failed'));
    exit;
}