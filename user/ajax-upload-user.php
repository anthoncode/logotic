<?php
// user/ajax-upload-user.php — Procesa la subida de un logo por un usuario
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-user.php');

// Debe estar logueado
if (!$user->is_loggedin()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$uid = $crypt->decrypt($_SESSION['uid'], 'USER');

// ── LIMITE DIARIO: máximo 50 logos en las últimas 24h ──
$limitStmt = $DB_con->prepare("
    SELECT COUNT(*) FROM " . PFX . "products
    WHERE submit_user_id = :uid AND created >= (NOW() - INTERVAL 1 DAY)
");
$limitStmt->execute([':uid' => $uid]);
$todayCount = (int)$limitStmt->fetchColumn();

if ($todayCount >= 50) {
    echo json_encode(['success' => false, 'message' => 'Daily upload limit reached (50 logos). Please try again tomorrow.']);
    exit;
}

// Validar entrada
$catId = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;
$subId = isset($_POST['subcat']) ? (int)$_POST['subcat'] : 0;

if ($catId <= 0 || $subId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a category and subcategory']);
    exit;
}

// Validar archivo
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file received']);
    exit;
}

$file = $_FILES['file'];

// Solo SVG por extensión
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($ext !== 'svg') {
    echo json_encode(['success' => false, 'message' => 'Only SVG files are allowed']);
    exit;
}

// Tamaño (máx 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB)']);
    exit;
}

// Leer contenido y verificar que es SVG real
$content = file_get_contents($file['tmp_name']);
if (stripos($content, '<svg') === false) {
    echo json_encode(['success' => false, 'message' => 'The file does not appear to be a valid SVG']);
    exit;
}

// ── HASH para detectar duplicados ──
$hash = hash('sha256', $content);

$dupStmt = $DB_con->prepare("
    SELECT id FROM " . PFX . "products
    WHERE submit_user_id = :uid AND file_hash = :hash LIMIT 1
");
$dupStmt->execute([':uid' => $uid, ':hash' => $hash]);
if ($dupStmt->fetchColumn()) {
    echo json_encode(['success' => false, 'message' => 'You already uploaded this logo.']);
    exit;
}

// Sanitizar: quitar scripts del SVG (seguridad)
$content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
$content = preg_replace('/on\w+\s*=\s*"[^"]*"/i', '', $content);
$content = preg_replace("/on\w+\s*=\s*'[^']*'/i", '', $content);

// Nombre base del archivo
$baseName = pathinfo($file['name'], PATHINFO_FILENAME);
$slugBase = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $baseName), '-'));
if ($slugBase === '') $slugBase = 'logo';

// Nombre único físico
$fileName = $slugBase . '-' . time() . '-' . mt_rand(100, 999) . '-logotic.svg';
$destDir  = '../system/assets/uploads/vector-files/';
$destPath = $destDir . $fileName;

// Guardar el archivo saneado
if (file_put_contents($destPath, $content) === false) {
    echo json_encode(['success' => false, 'message' => 'Could not save the file']);
    exit;
}

// Título inicial
$initialName = ucwords(str_replace('-', ' ', $slugBase));
$slugLg = $slugBase . '-' . time();

// Insertar como PENDIENTE
try {
    $ins = $DB_con->prepare("
        INSERT INTO " . PFX . "products
        (submit_user_id, slug_lg, name, icon_img, file_hash, cat_id, subc_id, tags, active, status, created)
        VALUES (:uid, :slug, :name, :icon, :hash, :cat, :sub, '', 0, 'pending', CURDATE())
    ");
    $ins->execute([
        ':uid'  => $uid,
        ':slug' => $slugLg,
        ':name' => $initialName,
        ':icon' => $fileName,
        ':hash' => $hash,
        ':cat'  => $catId,
        ':sub'  => $subId,
    ]);
    $newId = $DB_con->lastInsertId();

    echo json_encode([
        'success' => true,
        'id'      => $newId,
        'name'    => $initialName,
        'preview' => $setting['website_url'] . '/system/assets/uploads/vector-files/' . $fileName,
    ]);
} catch (Throwable $e) {
    @unlink($destPath);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}