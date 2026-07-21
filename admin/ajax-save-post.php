<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-admin.php');

if (!$auth->is_loggedin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Autor = admin logueado
$adminDetails = $auth->details($_SESSION['uid']);
$authorName   = $adminDetails['fname'] ?? 'Admin';

function makeSlug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

$postId = (int)($_POST['post_id'] ?? 0);
$isEdit = $postId > 0;

// Sanitización
$title        = htmlspecialchars(strip_tags(trim($_POST['title'] ?? '')));
$slug         = makeSlug($_POST['slug'] ?? $title);
$content      = trim($_POST['content'] ?? '');
$excerpt      = htmlspecialchars(strip_tags(trim($_POST['excerpt'] ?? '')));
$cat_id       = (int)($_POST['category_id'] ?? 0);
$status       = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
$meta_title   = htmlspecialchars(strip_tags(trim($_POST['meta_title'] ?? '')));
$meta_desc    = htmlspecialchars(strip_tags(trim($_POST['meta_desc'] ?? '')));
$meta_keywords = htmlspecialchars(strip_tags(trim($_POST['meta_keywords'] ?? '')));

// Validaciones
$errors = [];
if (empty($title))   $errors[] = 'Title is required.';
if (empty($slug))    $errors[] = 'Slug is required.';
if (empty($content)) $errors[] = 'Content is required.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// Verificar slug único (excluyendo el post actual si es edición)
$slugChk = $DB_con->prepare("SELECT id FROM " . PFX . "posts WHERE slug = :slug AND id != :id");
$slugChk->execute([':slug' => $slug, ':id' => $postId]);
if ($slugChk->fetchColumn()) {
    $slug .= '-' . time();
}

// ── Cover image ──
$cover_img = null;
$coverChanged = false;
if (!empty($_FILES['cover_img']['name']) && $_FILES['cover_img']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['cover_img'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts  = ['jpg','jpeg','png','webp'];
    $allowedMimes = ['image/jpeg','image/png','image/webp'];
    $maxSize      = 3 * 1024 * 1024;

    if (!in_array($ext, $allowedExts) || !in_array($file['type'], $allowedMimes)) {
        echo json_encode(['success' => false, 'message' => 'Cover must be JPG, PNG or WebP.']);
        exit;
    }
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Cover image max size is 3MB.']);
        exit;
    }

    $uploadDir = __DIR__ . '/../system/assets/uploads/blog/covers/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $cover_img = 'post-cover-' . time() . '-' . uniqid() . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $cover_img)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload cover image.']);
        exit;
    }
    $coverChanged = true;

    // Borrar cover anterior si es edición
    if ($isEdit) {
        $oldStmt = $DB_con->prepare("SELECT cover_img FROM " . PFX . "posts WHERE id = :id");
        $oldStmt->execute([':id' => $postId]);
        $oldCover = $oldStmt->fetchColumn();
        if ($oldCover && file_exists($uploadDir . $oldCover)) {
            unlink($uploadDir . $oldCover);
        }
    }
}

// ── Guardar ──
if ($isEdit) {
    // UPDATE
    $sql = "UPDATE " . PFX . "posts SET
                title = :title, slug = :slug, content = :content, excerpt = :excerpt,
                category_id = :cat_id, status = :status,
                meta_title = :meta_title, meta_desc = :meta_desc, meta_keywords = :meta_keywords,
                modified = NOW()";
    if ($coverChanged) $sql .= ", cover_img = :cover_img";
    $sql .= " WHERE id = :id";

    $params = [
        ':title' => $title, ':slug' => $slug, ':content' => $content, ':excerpt' => $excerpt,
        ':cat_id' => $cat_id ?: null, ':status' => $status,
        ':meta_title' => $meta_title ?: $title, ':meta_desc' => $meta_desc, ':meta_keywords' => $meta_keywords,
        ':id' => $postId,
    ];
    if ($coverChanged) $params[':cover_img'] = $cover_img;

    $DB_con->prepare($sql)->execute($params);
    $savedId = $postId;
} else {
    // INSERT
    $stmt = $DB_con->prepare("
        INSERT INTO " . PFX . "posts
        (title, slug, content, excerpt, cover_img, author, category_id, status, meta_title, meta_desc, meta_keywords)
        VALUES
        (:title, :slug, :content, :excerpt, :cover_img, :author, :category_id, :status, :meta_title, :meta_desc, :meta_keywords)
    ");
    $stmt->execute([
        ':title' => $title, ':slug' => $slug, ':content' => $content, ':excerpt' => $excerpt,
        ':cover_img' => $cover_img, ':author' => $authorName,
        ':category_id' => $cat_id ?: null, ':status' => $status,
        ':meta_title' => $meta_title ?: $title, ':meta_desc' => $meta_desc, ':meta_keywords' => $meta_keywords,
    ]);
    $savedId = $DB_con->lastInsertId();
}

// Devolver datos actualizados
$coverUrl = $cover_img
    ? $setting['website_url'] . '/system/assets/uploads/blog/covers/' . $cover_img
    : null;

echo json_encode([
    'success'   => true,
    'id'        => $savedId,
    'slug'      => $slug,
    'status'    => $status,
    'cover_url' => $coverUrl,
    'author'    => $authorName,
    'message'   => $isEdit ? 'Post updated successfully' : 'Post published successfully',
]);