<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-user.php');

// Leer el JSON del POST
$input = json_decode(file_get_contents('php://input'), true);
$credential = $input['credential'] ?? '';

if (empty($credential)) {
    echo json_encode(['success' => false, 'error' => 'No credential']);
    exit;
}

// Validar el JWT con la API de Google (tokeninfo)
$ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($credential));
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 10,
]);
$response = curl_exec($ch);
curl_close($ch);

$payload = json_decode($response, true);

// Verificar que el token es válido y del client correcto
$clientId = $setting['google_client_id'] ?? '';

if (empty($payload['sub']) || empty($payload['email'])) {
    error_log('One Tap invalid payload: ' . $response);
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

// Verificar que el aud (audience) coincide con nuestro client_id
if (($payload['aud'] ?? '') !== $clientId) {
    error_log('One Tap aud mismatch. Expected: ' . $clientId . ' Got: ' . ($payload['aud'] ?? ''));
    echo json_encode(['success' => false, 'error' => 'Token audience mismatch']);
    exit;
}

// Verificar que el email está verificado por Google
if (($payload['email_verified'] ?? 'false') !== 'true' && ($payload['email_verified'] ?? false) !== true) {
    echo json_encode(['success' => false, 'error' => 'Email not verified']);
    exit;
}

// Login o registro
error_log('One Tap — email: ' . $payload['email'] . ' | sub: ' . $payload['sub']);
$result = $user->loginWithGoogle([
    'sub'        => $payload['sub'],
    'email'      => $payload['email'],
    'name'       => $payload['name']       ?? '',
    'given_name' => $payload['given_name'] ?? $payload['name'] ?? 'User',
]);
error_log('One Tap loginWithGoogle result: ' . var_export($result, true));
error_log('One Tap session after login: ' . session_id() . ' | uid: ' . ($_SESSION['uid'] ?? 'NONE'));

if ($result === true) {
    echo json_encode(['success' => true, 'redirect' => $setting['website_url'] . '/user/']);
} else {
    echo json_encode(['success' => false, 'error' => $user->error ?: 'Login failed']);
}