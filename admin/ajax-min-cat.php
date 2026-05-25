<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
  require_once('../system/config-admin.php');

  //$productDetails = $product->details($_REQUEST['id']);
  $output = array('error' => false);

    /*obtiene el nombre del archivo*/
    $id = $_POST['id'];
    $cat_id = (isset($_POST['cat_id']) ? $_POST['cat_id'] : null);
    $scat_id = (isset($_POST['subc_id']) ? $_POST['subc_id'] : null);

    if ($_POST['cat_id']) {
      $sql_upload = $DB_con->prepare("UPDATE " . PFX . "products SET cat_id=:cat_id WHERE id=:id");
      $sql_upload->bindparam(":cat_id", $cat_id);
      //$sql_upload->bindparam(":scat_id", $scat_id);
      $sql_upload->bindparam(":id", $id);
    
      if ($sql_upload->execute()) {
      } else {
        echo "Error: " . $sql_upload->error;
      }
    }

    if ($_POST['subc_id']) {
      $sql_upload = $DB_con->prepare("UPDATE " . PFX . "products SET subc_id=:scat_id WHERE id=:id");
      //$sql_upload->bindparam(":cat_id", $cat_id);
      $sql_upload->bindparam(":scat_id", $scat_id);
      $sql_upload->bindparam(":id", $id);
    
      if ($sql_upload->execute()) {
      } else {
        echo "Error: " . $sql_upload->error;
      }
    }

  $id = $_POST['cat_id'];
  //run a query 
  $stmt = $DB_con->query('SELECT id,name FROM ' . PFX . 'subcat WHERE active = 1 AND cat_id = '.$DB_con->quote($id).' ORDER BY name');

  //loop through all returned rows
  while($row = $stmt->fetch(PDO::FETCH_OBJ)) {
      echo "<option value='$row->id'>$row->name</option>";
      //".($id == $row->id ? "selected" : )."
  }


} else {
  header('location: ../index.php');
}
