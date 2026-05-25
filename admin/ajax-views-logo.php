<?php
  require_once('../system/config-global.php'); //config-global.php es para todo usuario (sin iniciar sesión)

  //recorre todos los archivos cargados
  if (isset($_POST['id'])) {
    $id = $_POST['id'];
    $vLogos = $product->details($id);
    $vLogos = $vLogos['views'] + 1; //suma +1 a la cantidad anterior
    $sql_upload = $DB_con->prepare("UPDATE " . PFX . "products SET `views` = '$vLogos' WHERE id ='$id'");
    $sql_upload->bindparam(":views", $vLogos);
    $sql_upload->bindParam(':id', $id);

    if ($sql_upload->execute()) {

    } else {
      echo "Error: " . $sql_upload->error;
    }

} else {
  header('location: ../index.php');
}
