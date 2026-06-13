<?php
require_once('../system/config-admin.php');
require_once('../system/classes/class.mailer.php');

if (!$auth->is_loggedin()) {
    echo json_encode(['success' => false, 'msg' => 'Unauthorized']);
    exit;
}

$to = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$to) {
    echo json_encode(['success' => false, 'msg' => 'Invalid email address']);
    exit;
}

$mailer = new Mailer($DB_con, $setting);
$body   = $mailer->template(
    'SMTP Test Successful ✓',
    'This is a test email from <strong style="color:#f0f2ff;">' . htmlspecialchars($setting['site_name']) . '</strong>.<br><br>
     Your SMTP configuration is working correctly.',
    'Visit Site',
    $setting['website_url']
);

$sent = $mailer->send($to, 'SMTP Test — ' . $setting['site_name'], $body);
echo json_encode([
    'success' => $sent,
    'msg'     => $sent ? 'Test email sent to ' . $to : 'Failed to send. Check your SMTP settings and logs.',
]);