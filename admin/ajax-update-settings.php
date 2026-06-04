<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    require_once('../system/config-admin.php');

    $uploadDir = '../system/assets/uploads/img/';
    $allowedMimes = ['image/png', 'image/jpeg', 'image/svg+xml'];
    $allowedExts  = ['png', 'jpg', 'jpeg', 'svg'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $messages = [];

    // ── Función para subir y reemplazar ──
    function uploadFile($fileKey, $settingKey, $uploadDir, $allowedMimes, $allowedExts, $maxSize, $DB_con) {
        if (empty($_FILES[$fileKey]['name'])) return null;

        $file      = $_FILES[$fileKey];
        $tmpName   = $file['tmp_name'];
        $origName  = $file['name'];
        $mimeType  = $file['type'];
        $fileSize  = $file['size'];
        $ext       = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

        // Validar extensión
        if (!in_array($ext, $allowedExts)) {
            return ['error' => "Invalid file type for {$fileKey}. Allowed: PNG, JPG, SVG"];
        }

        // Validar MIME type
        if (!in_array($mimeType, $allowedMimes)) {
            // Doble check para SVG que a veces viene como text/xml
            if (!($ext === 'svg' && in_array($mimeType, ['text/xml', 'text/plain', 'application/xml']))) {
                return ['error' => "Invalid MIME type for {$fileKey}"];
            }
        }

        // Validar tamaño
        if ($fileSize > $maxSize) {
            return ['error' => "File too large for {$fileKey}. Max 5MB"];
        }

        // Obtener el archivo anterior para eliminarlo
        $stmt = $DB_con->prepare("SELECT value FROM " . PFX . "settings WHERE setting = :setting");
        $stmt->execute([':setting' => $settingKey]);
        $oldFile = $stmt->fetchColumn();

        // Nombre único para evitar conflictos
        $newName = $settingKey . '_' . time() . '.' . $ext;
        $dest    = $uploadDir . $newName;

        if (!move_uploaded_file($tmpName, $dest)) {
            return ['error' => "Failed to upload {$fileKey}"];
        }

        // Eliminar archivo anterior si existe y no es el default
        if ($oldFile && !in_array($oldFile, ['default.png', 'default.jpg']) && file_exists($uploadDir . $oldFile)) {
            unlink($uploadDir . $oldFile);
        }

        // Actualizar DB
        $update = $DB_con->prepare("UPDATE " . PFX . "settings SET value = :value WHERE setting = :setting");
        $update->execute([':value' => $newName, ':setting' => $settingKey]);

        return ['success' => true, 'name' => $newName];
    }

    // ── Procesar logo ──
    $logoResult = uploadFile('logoimg', 'site_logo', $uploadDir, $allowedMimes, $allowedExts, $maxSize, $DB_con);
    if ($logoResult) {
        if (isset($logoResult['error'])) {
            $messages[] = '<span style="color:#ff4d4d;"><i class="fa-solid fa-circle-xmark"></i> ' . $logoResult['error'] . '</span>';
        } else {
            $messages[] = '<span style="color:#2dc653;"><i class="fa-solid fa-circle-check"></i> Logo updated successfully</span>';
        }
    }

    // ── Procesar favicon ──
    $faviconResult = uploadFile('faviconimg', 'site_favicon', $uploadDir, $allowedMimes, $allowedExts, $maxSize, $DB_con);
    if ($faviconResult) {
        if (isset($faviconResult['error'])) {
            $messages[] = '<span style="color:#ff4d4d;"><i class="fa-solid fa-circle-xmark"></i> ' . $faviconResult['error'] . '</span>';
        } else {
            $messages[] = '<span style="color:#2dc653;"><i class="fa-solid fa-circle-check"></i> Favicon updated successfully</span>';
        }
    }

    if (empty($messages)) {
        $messages[] = '<span style="color:#8b8fa8;">No files were selected</span>';
    }

    echo implode('<br>', $messages);

} else {
    header('Location: ../index.php');
}
?>