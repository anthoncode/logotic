<?php
// user/ajax-upload-user.php — Procesa la subida de un logo por un usuario
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-user.php');

// ── Helpers para color dominante del SVG (mismo método que extract-colors.php) ──
function svgHexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (!ctype_xdigit($hex)) return [0, 0, 0];
    if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    if (strlen($hex) !== 6) return [0, 0, 0];
    return [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
}
function svgColorDistance($h1, $h2) {
    [$r1,$g1,$b1] = svgHexToRgb($h1);
    [$r2,$g2,$b2] = svgHexToRgb($h2);
    return sqrt(pow($r1-$r2,2) + pow($g1-$g2,2) + pow($b1-$b2,2));
}
function svgNearestBase($hex, $colorMap) {
    $nearest = 'black'; $minDist = PHP_INT_MAX;
    foreach ($colorMap as $base => $shades) {
        foreach ($shades as $c) {
            $dist = svgColorDistance($hex, $c);
            if ($dist < $minDist) { $minDist = $dist; $nearest = $base; }
        }
    }
    return $nearest;
}
function svgDominantHex($content) {
    if (!$content) return null;
    preg_match_all('/(fill|stroke)=["\']([^"\']+)["\']/', $content, $matches);
    preg_match_all('/(fill|stroke):\s*([#a-zA-Z][^;"\'\s]+)/', $content, $styleMatches);
    $colors = array_merge($matches[2], $styleMatches[2]);
    $freq = [];
    foreach ($colors as $color) {
        $color = strtolower(trim($color));
        if (in_array($color, ['none','transparent','inherit','currentcolor'])) continue;
        $named = ['black'=>'#000000','white'=>'#ffffff','red'=>'#ff0000','blue'=>'#0000ff','green'=>'#008000','yellow'=>'#ffff00','orange'=>'#ffa500','purple'=>'#800080','pink'=>'#ffc0cb'];
        if (isset($named[$color])) $color = $named[$color];
        if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $color)) continue;
        if (in_array($color, ['#fff','#ffffff','#000','#000000'])) continue;
        $freq[$color] = ($freq[$color] ?? 0) + 1;
    }
    if (empty($freq)) return null;
    arsort($freq);
    return array_key_first($freq);
}
function svgDominantColor($content) {
    $colorMap = [
        'red'    => ['#e63946','#ff0000','#cc0000','#ff3333','#dc143c','#b22222','#ff4500','#ff6347','#cd5c5c'],
        'blue'   => ['#1d7af3','#0000ff','#0066cc','#1e90ff','#4169e1','#00008b','#add8e6','#87ceeb','#6495ed'],
        'green'  => ['#2dc653','#008000','#00ff00','#228b22','#32cd32','#90ee90','#006400','#3cb371','#66bb6a'],
        'yellow' => ['#f4d03f','#ffff00','#ffd700','#ffa500','#ffeb3b','#ffc107','#ff9800'],
        'orange' => ['#f18d35','#ff6600','#ff8c00','#ff7043','#ff5722'],
        'purple' => ['#8b5cf6','#800080','#9b59b6','#6a0dad','#9c27b0','#673ab7','#7b1fa2'],
        'pink'   => ['#ec4899','#ff69b4','#ff1493','#db7093','#e91e63','#f06292'],
        'cyan'   => ['#06b6d4','#00bcd4','#00ffff','#40e0d0','#00ced1','#20b2aa'],
        'black'  => ['#000000','#111111','#1a1a1a','#222222','#333333','#0d0d0d'],
        'white'  => ['#ffffff','#f0f0f0','#fafafa','#eeeeee','#e0e0e0'],
    ];
    $hex = svgDominantHex($content);
    return $hex ? svgNearestBase($hex, $colorMap) : null;
}

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

// ── COLOR DOMINANTE (sobre el contenido original, antes de sanear) ──
$dominant_color = svgDominantColor($content);

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
        (submit_user_id, slug_lg, name, icon_img, file_hash, dominant_color, cat_id, subc_id, tags, status, created)
        VALUES (:uid, :slug, :name, :icon, :hash, :color, :cat, :sub, '', 'pending', CURDATE())
    ");
    $ins->execute([
        ':uid'   => $uid,
        ':slug'  => $slugLg,
        ':name'  => $initialName,
        ':icon'  => $fileName,
        ':hash'  => $hash,
        ':color' => $dominant_color,
        ':cat'   => $catId,
        ':sub'   => $subId,
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