<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once '../system/config-admin.php';
//$productDetails = $product->details($_REQUEST['id']);
$output = array('error' => false);

$id_list            = $_POST['id'];
$name_list          = $_POST['name'];
$slug               = Product::formatName($_POST['name']) . "-logo";//slug del item
//$description_list   = $_POST['description'];
$tags_list          = strtolower($_POST['tags']);

$sql_upload = $DB_con->prepare("UPDATE " . PFX . "products SET name=:name2, slug_lg=:slug, tags=:tags WHERE id=:id");
$sql_upload->bindparam(":id", $id_list);
$sql_upload->bindparam(":name2", $name_list);
$sql_upload->bindparam(":slug", $slug);
$sql_upload->bindparam(":tags", $tags_list);

if ($sql_upload->execute()) {

    //$sql_upload = $_POST;
    //$id['id'] = $DB_con->lastInsertId;
    //$data = array_merge($id,$sql_upload);

    //$output['member'] = array(
    //    'name' => $name_list,
    //  'id' => $id_list,
   // );

    //sleep(3);
    //echo json_encode($output);

    echo '<span class="text-success">Product Updated55555!</span>';
} else {
    echo "Error: " . $sql_upload->error;
}


?>