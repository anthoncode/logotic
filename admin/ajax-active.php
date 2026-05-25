<?php
require_once '../system/config-admin.php';

$productDetails = $product->details($_REQUEST['id']);
$output = array('error' => false);

$id_list            = $_POST['id'];
$active_logo        = $_POST['active'];



$sql_upload3 = $DB_con->prepare("UPDATE " . PFX . "products SET active=:active WHERE id=:id");

$sql_upload3->bindparam(":active", $active_logo);
$sql_upload3->bindparam(":id", $id_list);


if($sql_upload3->execute()){
    $output['message'] = 'Updated successfully!';
    //return the updated member
    $output['member'] = array(
        'active' => $active_logo,
    );
    
}
else{
    $output['error'] = true;
    $output['message'] = 'Cannot update member';
}

echo json_encode($output);

?>

