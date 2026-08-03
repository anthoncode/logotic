<?php
// user/ajax-update-user-logo.php — Actualiza nombre y tags de un logo propio (pendiente)
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-user.php');

if (!$user->is_loggedin()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$uid = $crypt->decrypt($_SESSION['uid'], 'USER');

$logoId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$name   = trim($_POST['name'] ?? '');
$tags   = trim($_POST['tags'] ?? '');

if ($logoId <= 0 || $name === '') {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

// Verificar que el logo pertenece a este usuario y sigue pendiente
$chk = $DB_con->prepare("SELECT id FROM " . PFX . "products WHERE id = :id AND submit_user_id = :uid");
$chk->execute([':id' => $logoId, ':uid' => $uid]);
if (!$chk->fetchColumn()) {
    echo json_encode(['success' => false, 'message' => 'Logo not found']);
    exit;
}

// Limpiar tags: separar por comas, quitar vacíos, volver a unir
$tagArr = array_filter(array_map('trim', explode(',', $tags)));
$tagsClean = implode(', ', $tagArr);

// Slug del nombre
$slugLg = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $name), '-'));
if ($slugLg === '') $slugLg = 'logo';
$slugLg .= '-' . $logoId;

try {
    $upd = $DB_con->prepare("
        UPDATE " . PFX . "products
        SET name = :name, tags = :tags, slug_lg = :slug
        WHERE id = :id AND submit_user_id = :uid
    ");
    $upd->execute([
        ':name' => substr($name, 0, 99),
        ':tags' => $tagsClean,
        ':slug' => $slugLg,
        ':id'   => $logoId,
        ':uid'  => $uid,
    ]);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}