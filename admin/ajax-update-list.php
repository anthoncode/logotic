
<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once '../system/config-admin.php';

$id_list            = $_POST['id'];
$name_list          = $_POST['name'];
$description_list   = $_POST['description'];
$tags_list          = $_POST['tags'];


$sql_upload = $DB_con->prepare("UPDATE " . PFX . "products SET name=:name2, description=:description, tags=:tags WHERE id=:id");

$sql_upload->bindparam(":name2", $name_list);
$sql_upload->bindparam(":description", $description_list);
$sql_upload->bindparam(":tags", $tags_list);
$sql_upload->bindparam(":id", $id_list);

if ($sql_upload->execute()) {
    echo '<span class="text-success">Product Updated55555!</span>';
} else {
    echo "Error: " . $sql_upload->error;
}


?>

