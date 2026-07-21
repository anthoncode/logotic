<?php
require_once('../system/config-global.php');

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false]);
    exit;
}

$id = (int)$_POST['id'];
if ($id <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

// Obtener IP real del visitante
function getVisitorIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) return $_SERVER['HTTP_CF_CONNECTING_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
$ip = getVisitorIP();

// ── Filtro 1: no contar bots ──
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$botPatterns = ['bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit',
                'whatsapp', 'telegram', 'preview', 'fetch', 'monitor', 'headless'];
foreach ($botPatterns as $pattern) {
    if (stripos($userAgent, $pattern) !== false) {
        echo json_encode(['success' => false, 'reason' => 'bot']);
        exit;
    }
}

// ── Filtro 2: no contar si es admin logueado ──
if (isset($_SESSION['uid']) && !empty($_SESSION['is_admin'])) {
    echo json_encode(['success' => false, 'reason' => 'admin']);
    exit;
}

// ── Filtro 3: deduplicación — misma IP no cuenta 2 veces en 24h ──
$dedup = $DB_con->prepare("
    SELECT id FROM " . PFX . "logo_views
    WHERE product_id = :pid AND ip_address = :ip
      AND viewed_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    LIMIT 1
");
$dedup->execute([':pid' => $id, ':ip' => $ip]);

if ($dedup->fetchColumn()) {
    // Ya vio este logo en las últimas 24h — no contar de nuevo
    echo json_encode(['success' => false, 'reason' => 'duplicate']);
    exit;
}

// ── Registrar la vista y sumar +1 ──
try {
    $DB_con->beginTransaction();

    // Registrar en el log
    $log = $DB_con->prepare("
        INSERT INTO " . PFX . "logo_views (product_id, ip_address, viewed_at)
        VALUES (:pid, :ip, NOW())
    ");
    $log->execute([':pid' => $id, ':ip' => $ip]);

    // Incrementar contador
    $upd = $DB_con->prepare("UPDATE " . PFX . "products SET views = views + 1 WHERE id = :id");
    $upd->execute([':id' => $id]);

    $DB_con->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $DB_con->rollBack();
    echo json_encode(['success' => false, 'reason' => 'error']);
}