<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-admin.php');

if (!$auth->is_loggedin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = (int)($_POST['user_id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user']);
    exit;
}

$action = $_POST['action'] ?? 'save';

// ── Acciones rápidas ──
if ($action === 'unlock') {
    $DB_con->prepare("UPDATE " . PFX . "users SET locked_until = NULL, login_attempts = 0 WHERE id = :id")
           ->execute([':id' => $id]);
    echo json_encode(['success' => true, 'message' => 'Account unlocked']);
    exit;
}

if ($action === 'unlink_google') {
    $DB_con->prepare("DELETE FROM " . PFX . "oauth WHERE user_id = :id AND provider = 'google'")
           ->execute([':id' => $id]);
    $DB_con->prepare("UPDATE " . PFX . "users SET google_id = NULL WHERE id = :id")
           ->execute([':id' => $id]);
    echo json_encode(['success' => true, 'message' => 'Google account unlinked']);
    exit;
}

if ($action === 'reset_photo') {
    $DB_con->prepare("UPDATE " . PFX . "users SET profile = :p WHERE id = :id")
           ->execute([':p' => '../system/assets/uploads/user-img/default.png', ':id' => $id]);
    echo json_encode(['success' => true, 'message' => 'Profile photo reset']);
    exit;
}

// ── Guardado principal ──
$fname    = htmlspecialchars(strip_tags(trim($_POST['fname'] ?? '')));
$username = strtolower(preg_replace('/[^a-zA-Z0-9_\-]/', '', trim($_POST['username'] ?? '')));
$email    = strtolower(trim($_POST['email'] ?? ''));
$newPwd   = $_POST['new_password'] ?? '';

$errors = [];
if (empty($fname))    $errors[] = 'Name is required.';
if (empty($username)) $errors[] = 'Username is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';

if (!empty($newPwd)) {
    if (mb_strlen($newPwd) < 8)        $errors[] = 'Password must be at least 8 characters.';
    elseif (!preg_match('/[A-Z]/', $newPwd)) $errors[] = 'Password needs an uppercase letter.';
    elseif (!preg_match('/[0-9]/', $newPwd)) $errors[] = 'Password needs a number.';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Email único
$chk = $DB_con->prepare("SELECT id FROM " . PFX . "users WHERE email = :email AND id != :id");
$chk->execute([':email' => $email, ':id' => $id]);
if ($chk->fetchColumn()) {
    echo json_encode(['success' => false, 'message' => 'That email is already used by another account.']);
    exit;
}

// Toggles
$active     = isset($_POST['active'])             ? 1 : 0;
$verified   = isset($_POST['verified'])           ? 1 : 0;
$moderator  = isset($_POST['moderator'])          ? 1 : 0;
$allowEmail = isset($_POST['allow_email'])        ? 1 : 0;
$forceReset = isset($_POST['password_recover'])   ? 1 : 0;
$twoFactor  = isset($_POST['two_factor_enabled']) ? 1 : 0;

$sql = "UPDATE " . PFX . "users SET
            fname = :fname, username = :username, email = :email,
            active = :active, verified = :verified, moderator = :moderator,
            allow_email = :allow_email, password_recover = :recover,
            two_factor_enabled = :twofa";
$params = [
    ':fname' => $fname, ':username' => $username, ':email' => $email,
    ':active' => $active, ':verified' => $verified, ':moderator' => $moderator,
    ':allow_email' => $allowEmail, ':recover' => $forceReset, ':twofa' => $twoFactor,
    ':id' => $id,
];

if (!empty($newPwd)) {
    $sql .= ", password = :password, login_attempts = 0, locked_until = NULL";
    $params[':password'] = password_hash($newPwd, PASSWORD_BCRYPT, ['cost' => 12]);
}

$sql .= " WHERE id = :id";
$DB_con->prepare($sql)->execute($params);

echo json_encode([
    'success' => true,
    'message' => !empty($newPwd) ? 'User updated and password changed' : 'User updated successfully',
]);