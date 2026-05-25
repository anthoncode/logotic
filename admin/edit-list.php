<?php
/*actualiza la lista de logos en all-logos.php*/
require_once '../system/config-admin.php';

$productDetails = $product->details($_REQUEST['id']);
$output = array('error' => false);

$id_list            = $_POST['id'];
$name_list          = $_POST['name'];
$slug               = Product::formatName($_POST['name']);//slug del item
$description_list   = $_POST['description'];
$website            = $_POST['website'];
$tags_list          = $_POST['tags'];


$sql_upload3 = $DB_con->prepare("UPDATE " . PFX . "products SET name=:name2, slug_lg=:slug, description=:description, tags=:tags, website=:website WHERE id=:id");

$sql_upload3->bindparam(":name2", $name_list);
$sql_upload3->bindparam(":slug", $slug);
$sql_upload3->bindparam(":description", $description_list);
$sql_upload3->bindparam(":tags", $tags_list);
$sql_upload3->bindparam(":website", $website);
$sql_upload3->bindparam(":id", $id_list);


if($sql_upload3->execute()){
    $output['message'] = 'Updated successfully!';
    //return the updated member
    $output['member'] = array(
        'name' => $name_list,
        'description' => $description_list,
        'tags' => $tags_list,
        'website' => $website
    );
    
}
else{
    $output['error'] = true;
    $output['message'] = 'Cannot update member';
}

echo json_encode($output);

?>

