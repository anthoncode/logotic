<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-admin.php');

if (!$auth->is_loggedin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid logo ID']);
    exit;
}

try {
    // Buscar el archivo SVG para borrarlo también
    $stmt = $DB_con->prepare("SELECT icon_img FROM " . PFX . "products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $logo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$logo) {
        echo json_encode(['success' => false, 'message' => 'Logo not found']);
        exit;
    }

    // Borrar el archivo SVG del disco
    if (!empty($logo['icon_img'])) {
        $filePath = '../system/assets/uploads/vector-files/' . $logo['icon_img'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    // Borrar el registro
    $DB_con->prepare("DELETE FROM " . PFX . "products WHERE id = :id")->execute([':id' => $id]);

    echo json_encode(['success' => true, 'message' => 'Logo deleted successfully']);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Could not delete the logo']);
}