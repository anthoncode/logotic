<?php
// user/ajax-subcat.php — Devuelve las subcategorías de una categoría (para el select)
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once('../system/config-user.php');

$catId = isset($_GET['cat_id']) ? (int)$_GET['cat_id'] : 0;
if ($catId <= 0) exit;

$stmt = $DB_con->prepare("SELECT id, name FROM " . PFX . "subcat WHERE cat_id = :cat AND active = 1 ORDER BY name ASC");
$stmt->execute([':cat' => $catId]);
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($subs as $s) {
    echo '<option value="' . (int)$s['id'] . '">' . htmlspecialchars($s['name']) . '</option>';
}