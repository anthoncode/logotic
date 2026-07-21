<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-admin.php');

if (!$auth->is_loggedin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

function makeSlug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

$pageId = (int)($_POST['page_id'] ?? 0);
$isEdit = $pageId > 0;

$title      = htmlspecialchars(strip_tags(trim($_POST['title'] ?? '')));
$slug       = makeSlug($_POST['slug'] ?? $title);
$content    = trim($_POST['content'] ?? '');
$excerpt    = htmlspecialchars(strip_tags(trim($_POST['excerpt'] ?? '')));
$level      = (int)($_POST['level'] ?? 0);
$active     = (int)($_POST['active'] ?? 1);
$meta_title = htmlspecialchars(strip_tags(trim($_POST['meta_title'] ?? '')));
$meta_desc  = htmlspecialchars(strip_tags(trim($_POST['meta_desc'] ?? '')));

$errors = [];
if (empty($title))   $errors[] = 'Title is required.';
if (empty($slug))    $errors[] = 'Slug is required.';
if (empty($content)) $errors[] = 'Content is required.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Slug único (excluyendo la página actual)
$slugChk = $DB_con->prepare("SELECT id FROM " . PFX . "custompages WHERE slug_page = :slug AND id != :id");
$slugChk->execute([':slug' => $slug, ':id' => $pageId]);
if ($slugChk->fetchColumn()) {
    $slug .= '-' . time();
}

// Cover image
$cover_img = null;
$coverChanged = false;
if (!empty($_FILES['cover_img']['name']) && $_FILES['cover_img']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['cover_img'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
        echo json_encode(['success' => false, 'message' => 'Cover must be JPG, PNG or WebP.']);
        exit;
    }
    if ($file['size'] > 3 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Cover image max size is 3MB.']);
        exit;
    }
    $uploadDir = __DIR__ . '/../system/assets/uploads/pages/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $cover_img = 'page-cover-' . time() . '-' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $cover_img)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload cover image.']);
        exit;
    }
    $coverChanged = true;

    if ($isEdit) {
        $oldStmt = $DB_con->prepare("SELECT cover_img FROM " . PFX . "custompages WHERE id = :id");
        $oldStmt->execute([':id' => $pageId]);
        $oldCover = $oldStmt->fetchColumn();
        if ($oldCover && file_exists($uploadDir . $oldCover)) unlink($uploadDir . $oldCover);
    }
}

if ($isEdit) {
    $sql = "UPDATE " . PFX . "custompages SET
                title = :title, slug_page = :slug, content = :content, excerpt = :excerpt,
                level = :level, active = :active, meta_title = :meta_title, meta_desc = :meta_desc,
                modified = NOW()";
    if ($coverChanged) $sql .= ", cover_img = :cover_img";
    $sql .= " WHERE id = :id";

    $params = [
        ':title' => $title, ':slug' => $slug, ':content' => $content, ':excerpt' => $excerpt,
        ':level' => $level, ':active' => $active, ':meta_title' => $meta_title, ':meta_desc' => $meta_desc,
        ':id' => $pageId,
    ];
    if ($coverChanged) $params[':cover_img'] = $cover_img;

    $DB_con->prepare($sql)->execute($params);
    $savedId = $pageId;
} else {
    $stmt = $DB_con->prepare("
        INSERT INTO " . PFX . "custompages
        (slug_page, title, content, cover_img, excerpt, meta_title, meta_desc, indate, level, active)
        VALUES
        (:slug, :title, :content, :cover_img, :excerpt, :meta_title, :meta_desc, CURDATE(), :level, :active)
    ");
    $stmt->execute([
        ':slug' => $slug, ':title' => $title, ':content' => $content, ':cover_img' => $cover_img,
        ':excerpt' => $excerpt, ':meta_title' => $meta_title, ':meta_desc' => $meta_desc,
        ':level' => $level, ':active' => $active,
    ]);
    $savedId = $DB_con->lastInsertId();
}

$coverUrl = $cover_img
    ? $setting['website_url'] . '/system/assets/uploads/pages/' . $cover_img
    : null;

echo json_encode([
    'success'   => true,
    'id'        => $savedId,
    'slug'      => $slug,
    'cover_url' => $coverUrl,
    'message'   => $isEdit ? 'Page updated successfully' : 'Page created successfully',
]);