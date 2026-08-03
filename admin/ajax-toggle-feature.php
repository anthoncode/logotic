<?php
require_once('../system/config-admin.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    // Lista blanca de toggles que se pueden guardar por AJAX
    $allowed = [
        'login',
        'captcha',
        'show_ads',
        'notification_header',
        'google_oauth_enabled',
        'smtp_enabled',
        'email_verification',
        'dl_limit_enabled',
    ];
    $name  = $_POST['name'];
    $value = $_POST['value'] == '1' ? 1 : 0;

    if (!in_array($name, $allowed)) {
        echo json_encode(['success' => false, 'msg' => 'Invalid feature']);
        exit;
    }

    $stmt = $DB_con->prepare("UPDATE " . PFX . "settings SET value = :value WHERE setting = :name");
    $stmt->execute([':value' => $value, ':name' => $name]);

    echo json_encode(['success' => true, 'name' => $name, 'value' => $value]);
}