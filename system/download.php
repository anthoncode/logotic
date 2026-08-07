<?php
// ═══════════════════════════════════════════════════════════
// system/download.php — Descarga segura de logos (SVG + PNG)
// ═══════════════════════════════════════════════════════════
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/config-global.php';

// No indexar este endpoint en buscadores
header('X-Robots-Tag: noindex, nofollow', true);

// ── Helper: responder error como JSON o redirect según el caso ──
function dlError($code, $reason, $message) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'reason' => $reason, 'message' => $message]);
    exit;
}

// ── 1. Filtrar bots por user-agent ──
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$botPatterns = ['bot', 'crawl', 'spider', 'slurp', 'curl', 'wget', 'python', 'scrapy', 'httpclient', 'java/', 'go-http'];
foreach ($botPatterns as $p) {
    if ($ua === '' || stripos($ua, $p) !== false) {
        dlError(403, 'bot', 'Automated access is not allowed.');
    }
}

// ── 2. Sanitizar entrada ──
$pid    = isset($_GET['pid']) ? intval($_GET['pid']) : 0;   // intval corta "123/basura" → 123
$format = strtolower($_GET['format'] ?? 'svg');
$size   = isset($_GET['size']) ? intval($_GET['size']) : 1000;

if ($pid <= 0) {
    dlError(400, 'invalid', 'Invalid request.');
}
if (!in_array($format, ['svg', 'png'], true)) {
    $format = 'svg';
}
$size = max(16, min(3000, $size));  // límite de tamaño del PNG

// ── 3. Config del admin ──
$limitEnabled = ($setting['dl_limit_enabled'] ?? '1') == '1';
$guestLimit   = (int)($setting['dl_guest_limit']  ?? 10);
$guestPeriod  = (int)($setting['dl_guest_period'] ?? 24);   // horas
$rateMax      = (int)($setting['dl_rate_max']     ?? 8);    // por minuto

// ── 4. Buscar el logo en la DB ──
$stmt = $DB_con->prepare("SELECT id, name, slug_lg, icon_img, status, download_off FROM " . PFX . "products WHERE id = :id");
$stmt->execute([':id' => $pid]);
$logo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$logo || ($logo['status'] ?? '') !== 'approved') {
    dlError(404, 'notfound', 'Logo not found.');
}

// Descarga deshabilitada por el admin para este logo
if (($logo['download_off'] ?? 0) == 1) {
    dlError(403, 'disabled', 'Download not available for this logo.');
}

// ── 5. Validar la ruta física (anti path-traversal) ──
$baseDir = realpath(__DIR__ . '/assets/uploads/vector-files');
$svgPath = realpath($baseDir . '/' . $logo['icon_img']);

if ($svgPath === false || strpos($svgPath, $baseDir) !== 0) {
    dlError(404, 'notfound', 'File not found.');
}

// ── 6. Identificar usuario ──
$ip         = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$isLoggedIn = $user->is_loggedin();
$uid        = null;
if ($isLoggedIn) {
    $uid = $crypt->decrypt($_SESSION['uid'], 'USER');
}

// ── 7. Rate limiting (aplica a todos, evita ráfagas de bots) ──
$rateChk = $DB_con->prepare("
    SELECT COUNT(*) FROM " . PFX . "downloads
    WHERE ip_address = :ip AND date_created > (NOW() - INTERVAL 1 MINUTE)
");
$rateChk->execute([':ip' => $ip]);
if ($rateChk->fetchColumn() >= $rateMax) {
    dlError(429, 'rate', 'Too many downloads. Please wait a moment and try again.');
}

// ── 8. Límite de invitados (solo si no está logueado y el límite está activo) ──
if (!$isLoggedIn && $limitEnabled && $guestLimit > 0) {

    // Contar por IP en el período
    $ipChk = $DB_con->prepare("
        SELECT COUNT(*) FROM " . PFX . "downloads
        WHERE ip_address = :ip AND user_id IS NULL
        AND date_created > (NOW() - INTERVAL :hrs HOUR)
    ");
    $ipChk->bindValue(':ip', $ip);
    $ipChk->bindValue(':hrs', $guestPeriod, PDO::PARAM_INT);
    $ipChk->execute();
    $ipCount = (int)$ipChk->fetchColumn();

    // Contar por cookie
    $cookieCount = isset($_COOKIE['dl_count']) ? (int)$_COOKIE['dl_count'] : 0;

    // El límite salta si cualquiera de los dos se supera
    $used = max($ipCount, $cookieCount);

    if ($used >= $guestLimit) {
        dlError(403, 'limit', "You've reached the free download limit ({$guestLimit}). Sign in for unlimited downloads.");
    }
}

// ── 9. Registrar la descarga ──
// ── 9. Registrar la descarga (solo en GET real, no en checks) ──
$isCheck = isset($_GET['check']);
if (!$isCheck) {
    $ins = $DB_con->prepare("
        INSERT INTO " . PFX . "downloads (products_id, user_id, ip_address, date_created)
        VALUES (:pid, :uid, :ip, NOW())
    ");
    $ins->execute([':pid' => $pid, ':uid' => $uid, ':ip' => $ip]);

    // Actualizar cookie de conteo para invitados
    if (!$isLoggedIn && $limitEnabled) {
        $newCount = (isset($_COOKIE['dl_count']) ? (int)$_COOKIE['dl_count'] : 0) + 1;
        setcookie('dl_count', $newCount, [
            'expires'  => time() + ($guestPeriod * 3600),
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

// Si es solo un check y pasó todas las validaciones, responder OK sin servir archivo
if ($isCheck) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// ── 10. Nombre de descarga limpio ──
$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '-', $logo['slug_lg'] ?: $logo['name']);
$siteName = preg_replace('/[^a-zA-Z0-9_\-]/', '-', strtolower($setting['site_name'] ?? 'logotic'));
$safeName = trim($safeName, '-');

// ── 11. Servir SVG ──
if ($format === 'svg') {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: image/svg+xml');
    header('Content-Disposition: attachment; filename="' . $safeName . '-' . $siteName . '.svg"');
    header('Content-Length: ' . filesize($svgPath));
    header('Cache-Control: private');
    readfile($svgPath);
    exit;
}

// ── 12. Servir PNG (convertido con Imagick) ──
if ($format === 'png') {
    if (!extension_loaded('imagick')) {
        dlError(500, 'server', 'PNG conversion is not available.');
    }
    try {
        $im = new Imagick();
        $im->setBackgroundColor(new ImagickPixel('transparent'));
        $im->setResolution(300, 300);   // mayor resolución antes de rasterizar = PNG más nítido
        $im->readImage($svgPath);
        $im->setImageFormat('png32');
        $im->resizeImage($size, 0, Imagick::FILTER_LANCZOS, 1);  // 0 = alto automático

        $pngBlob = $im->getImageBlob();
        $im->clear();
        $im->destroy();

        if (ob_get_level()) ob_end_clean();
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $safeName . '-' . $siteName . '.png"');
        header('Content-Length: ' . strlen($pngBlob));
        header('Cache-Control: private');
        echo $pngBlob;
        exit;
    } catch (Exception $e) {
        error_log('PNG conversion failed for pid ' . $pid . ': ' . $e->getMessage());
        dlError(500, 'server', 'Could not generate PNG.');
    }
}