<?php
/**
 * notify-admin.php
 * Envía un correo al admin avisando de un logo nuevo pendiente de revisión.
 * Se llama desde upload-logo.php (en segundo plano) al terminar la subida.
 * Opción: un correo por cada logo, sin límite.
 */
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-user.php');

// Solo usuarios logueados pueden disparar la notificación
if (!$user->is_loggedin()) {
    echo json_encode(['ok' => false]);
    exit;
}

$logoId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($logoId <= 0) {
    echo json_encode(['ok' => false]);
    exit;
}

// Responder de inmediato al navegador y seguir en segundo plano
echo json_encode(['ok' => true]);

if (function_exists('fastcgi_finish_request')) {
    @session_write_close();
    @fastcgi_finish_request();
} else {
    @ignore_user_abort(true);
    if (ob_get_level() > 0) { @ob_end_flush(); }
    @flush();
}

// ── A partir de aquí, el usuario ya no espera ──
try {
    $adminEmail = $setting['mail_admin'] ?? '';
    if (empty($adminEmail)) exit;

    // Traer el logo (debe estar pendiente y ser de este usuario)
    $uid = $crypt->decrypt($_SESSION['uid'], 'USER');
    $stmt = $DB_con->prepare("
        SELECT p.id, p.name, p.created, p.status,
               u.username, u.fname, u.email
        FROM " . PFX . "products p
        LEFT JOIN " . PFX . "users u ON p.submit_user_id = u.id
        WHERE p.id = :id AND p.submit_user_id = :uid AND p.status = 'pending'
        LIMIT 1
    ");
    $stmt->execute([':id' => $logoId, ':uid' => $uid]);
    $logo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$logo) exit; // no existe, no es suyo, o ya no está pendiente

    $logoName     = $logo['name'] ?: 'Untitled logo';
    $uploaderName = $logo['username'] ?: ($logo['fname'] ?: 'A user');
    $uploaderMail = $logo['email'] ?? '';
    $when         = date('Y-m-d H:i');

    // Cuántos pendientes hay en total (contexto)
    $pendCount = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE status = 'pending'")->fetchColumn();

    $pendingUrl = $setting['website_url'] . '/admin/pending.php';

    $content = "A new logo is waiting for review.<br><br>"
             . "<strong>Logo:</strong> " . htmlspecialchars($logoName) . "<br>"
             . "<strong>Uploaded by:</strong> " . htmlspecialchars($uploaderName)
             . ($uploaderMail ? " (" . htmlspecialchars($uploaderMail) . ")" : "") . "<br>"
             . "<strong>Date:</strong> " . $when . "<br>"
             . "<strong>Pending in total:</strong> " . $pendCount . "<br>";

    $subject = "New logo pending review — " . $logoName;

    if (method_exists($mailer, 'template')) {
        $body = $mailer->template('New logo to review', $content, 'Go to moderation', $pendingUrl);
    } else {
        $body = $content . "<br><a href='" . $pendingUrl . "'>Go to moderation</a>";
    }

    $mailer->send($adminEmail, $subject, $body);

} catch (Throwable $e) {
    // Silencioso: si el correo falla, no afecta nada (el logo ya se guardó)
}