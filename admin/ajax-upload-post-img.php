<?php
require_once('../system/config-admin.php');

if (!$auth->is_loggedin()) {
    echo json_encode(['error' => ['message' => 'Unauthorized', 'type' => 'authentication']]);
    exit;
}

$uploadDir = '../system/assets/uploads/blog/covers/';
$allowedMimes = ['image/jpeg','image/png','image/webp','image/gif'];
$allowedExts  = ['jpg','jpeg','png','webp','gif'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!isset($_FILES['file'])) {
    echo json_encode(['error' => ['message' => 'No file received', 'type' => 'no_file']]);
    exit;
}

$file = $_FILES['file'];
$ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExts) || !in_array($file['type'], $allowedMimes)) {
    echo json_encode(['error' => ['message' => 'Invalid file type', 'type' => 'invalid_filetype']]);
    exit;
}

if ($file['size'] > $maxSize) {
    echo json_encode(['error' => ['message' => 'File too large (max 5MB)', 'type' => 'size']]);
    exit;
}

$newName = 'post-img-' . time() . '-' . uniqid() . '.' . $ext;
$dest    = $uploadDir . $newName;

if (move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['location' => $setting['website_url'] . '/system/assets/uploads/blog/covers/' . $newName]);
} else {
    echo json_encode(['error' => ['message' => 'Upload failed', 'type' => 'upload']]);
}