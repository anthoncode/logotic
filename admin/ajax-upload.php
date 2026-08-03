<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-admin.php');

$output = array('error' => false);

if (isset($_FILES['file'])) {

    $vector    = $_FILES['file']['name'];
    $tmp_name  = $_FILES['file']['tmp_name'];
    $vect_name = Product::getFileExtension($vector);
    $clean_name = Product::formatName($vect_name[0]);
    $slug_vect  = $clean_name . '-' . time() . '-' . 'logotic';
    $join_name_vec = $slug_vect . '-brand' . '.' . end($vect_name);
    $imgn = $join_name_vec;
    $template_vect = $slug_vect . '-tmpl' . '.' . end($vect_name);
    $new_image_name1 = $imgn;

    $categ = $_POST['cat_id'] ?? '';

    if ($categ == 1) {
        move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $new_image_name1);
    } else {
        move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $template_vect);
    }

    $origen_name = $_FILES['file']['name'];
    $filename    = pathinfo($origen_name, PATHINFO_FILENAME);
    $clean_file  = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", " ", $filename);
    $strlower    = mb_strtolower($clean_file, 'UTF-8');
    $final_name  = ucwords($strlower);

    $id_admin    = 0;
    $filename_tl = $final_name;
    $slug        = Product::formatName($filename_tl) . "-logo";
    $cat_id      = $_POST['cat_id'] ?? null;
    $scat_id     = !empty($_POST['subcat']) ? $_POST['subcat'] : null;
    $date        = date("Y-m-d");

$tags = ''; // vacío por ahora, se actualiza después con ajax-update-logo.php

$sql_upload = $DB_con->prepare("INSERT INTO " . PFX . "products (submit_user_id, slug_lg, name, cat_id, subc_id, icon_img, tags, created, modified, active) VALUES (:id_admin, :slug, :name2, :cat_id, :scat_id, :icon_img, :tags, :created, :modified, '1')");

$sql_upload->bindParam(":id_admin", $id_admin);
$sql_upload->bindParam(":slug",     $slug);
$sql_upload->bindParam(":name2",    $filename_tl);
$sql_upload->bindParam(":cat_id",   $cat_id);
$sql_upload->bindParam(":scat_id",  $scat_id);
$icon_img = ($categ == 1) ? $new_image_name1 : $template_vect;
$sql_upload->bindParam(":icon_img", $icon_img);
$sql_upload->bindParam(":tags",     $tags); // ← agrega esto
$sql_upload->bindParam(":created",  $date);
$sql_upload->bindParam(":modified", $date);

    if ($sql_upload->execute()) {
        $id['id'] = $DB_con->lastInsertId();
        $data = array_merge($id, $_POST);
        echo json_encode($data);
    } else {
        echo json_encode(['error' => true, 'message' => 'Database error']);
    }

} else {
    echo json_encode(['error' => true, 'message' => 'No file received']);
}