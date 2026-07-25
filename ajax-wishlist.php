<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('system/config-global.php');

// Debe estar logueado
if (!$user->is_loggedin()) {
    echo json_encode(['success' => false, 'reason' => 'auth', 'message' => 'Please sign in to save favorites']);
    exit;
}

$uid = $crypt->decrypt($_SESSION['uid'], 'USER');
$action = $_POST['action'] ?? '';

// ── Añadir o quitar (toggle) ──
if ($action === 'toggle') {
    $productId = (int)($_POST['product_id'] ?? 0);
    if ($productId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid logo']);
        exit;
    }

    // ¿Ya está guardado?
    $chk = $DB_con->prepare("SELECT w_id FROM " . PFX . "wishlists WHERE user_id = :uid AND product_id = :pid");
    $chk->execute([':uid' => $uid, ':pid' => $productId]);
    $existing = $chk->fetchColumn();

    if ($existing) {
        // Quitar
        $del = $DB_con->prepare("DELETE FROM " . PFX . "wishlists WHERE user_id = :uid AND product_id = :pid");
        $del->execute([':uid' => $uid, ':pid' => $productId]);
        $saved = false;
    } else {
        // Añadir
        $ins = $DB_con->prepare("INSERT INTO " . PFX . "wishlists (product_id, user_id) VALUES (:pid, :uid)");
        $ins->execute([':pid' => $productId, ':uid' => $uid]);
        $saved = true;
    }

    // Contar total actualizado
    $cnt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE user_id = :uid");
    $cnt->execute([':uid' => $uid]);
    $total = $cnt->fetchColumn();

    echo json_encode([
        'success' => true,
        'saved'   => $saved,
        'count'   => (int)$total,
        'message' => $saved ? 'Saved to favorites' : 'Removed from favorites',
    ]);
    exit;
}

// ── Quitar uno específico ──
if ($action === 'remove') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $DB_con->prepare("DELETE FROM " . PFX . "wishlists WHERE user_id = :uid AND product_id = :pid")
           ->execute([':uid' => $uid, ':pid' => $productId]);
    $cnt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE user_id = :uid");
    $cnt->execute([':uid' => $uid]);
    echo json_encode(['success' => true, 'count' => (int)$cnt->fetchColumn()]);
    exit;
}

// ── Vaciar todos ──
if ($action === 'clear') {
    $DB_con->prepare("DELETE FROM " . PFX . "wishlists WHERE user_id = :uid")->execute([':uid' => $uid]);
    echo json_encode(['success' => true, 'count' => 0]);
    exit;
}

// ── Solo obtener el conteo ──
if ($action === 'count') {
    $cnt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE user_id = :uid");
    $cnt->execute([':uid' => $uid]);
    echo json_encode(['success' => true, 'count' => (int)$cnt->fetchColumn()]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown action']);