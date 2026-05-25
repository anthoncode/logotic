<?php

$pageTitle = "Remove Product";
require_once('../system/config-admin.php');
if (isset($_REQUEST['id'])) {
  $productDetails = $product->details($_REQUEST['id']);
  $error = ($product->error ? $product->error : false);

  if (isset($_GET['action']) && $_GET['action'] == 'remove') {

    if (!$error) {
      $product->remove($_REQUEST['id']);
      //unlink("../system/assets/uploads/products/" . $productDetails['preview_img']); // preview img (.png)
      unlink("../system/assets/uploads/vector-files/" . $productDetails['icon_img']); //vector file (.svg)
      $error = ($product->error ? $product->error : false);
      $success = ($product->msg ? $product->msg : false);
      //echo "<meta http-equiv= 'Refresh' content= '2'>";
      //(3);
      //header("location: products.php");
    }


    if (isset($_REQUEST['ajax'])) {
      echo (@$error ? $error : 'success');
      exit;
    }
  }

  if (empty($error)) {
    unset($error);
  }
} else {
  echo 'Invalid request';
  exit;
}
?>
