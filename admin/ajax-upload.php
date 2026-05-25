<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  error_reporting(E_ALL);
  ini_set('display_errors', 0);
  require_once('../system/config-admin.php');

  //$productDetails = $product->details($_REQUEST['id']);
  $output = array('error' => false);


  //recorre todos los archivos cargados
  if (isset($_FILES['file'])) {

    $vector = $_FILES['file']['name']; //título del archivo vector
    $tmp_name   = $_FILES['file']['tmp_name'];
    $vect_name = Product::getFileExtension($vector);  //explode, separa nombre de extensión (nom - png)
    $clean_name = Product::formatName($vect_name[0]);
    $slug_vect = $clean_name . '-' . time() . '-' . 'logotic'; //slug (nombre + tiempo + logotic)
    $join_name_vec = $slug_vect . '-brand' . '.' . end($vect_name); //une slug con extensión 1[png]
    $imgn = $join_name_vec; //slug completo

    $template_vect = $slug_vect . '-tmpl' . '.' . end($vect_name);

    $new_image_name1 = $imgn;

    $categ = $_POST['cat_id'];
    if ($categ == 1) {
      //brand logo
      move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $new_image_name1 . '');
    }else{
      //logo template
      move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $template_vect . '');
    }
    

    /*obtiene el nombre del archivo*/
    $origen_name = $_FILES['file']['name'];
    $filename = pathinfo($origen_name, PATHINFO_FILENAME);
    $clean_file = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", " ", $filename);
    $strlower = mb_strtolower($clean_file,'UTF-8');
    $final_name = ucwords($strlower);

    $id_admin = 0; //admin
    $filename_tl = $final_name;
    $slug = Product::formatName($filename_tl) . "-logo";//slug del item
    $cat_id = $_POST['cat_id'];
    $scat_id = (isset($_POST['subcat']) ? $_POST['subcat'] : null);
    $date = date("Y-m-d");

    $sql_upload = $DB_con->prepare("INSERT INTO " . PFX . "products (submit_user_id, slug_lg, name, cat_id, subc_id, icon_img, created, modified, active) VALUES (:id_admin, :slug, :name2, :cat_id, :scat_id, :icon_img, :created, :modified, '1')");

    $sql_upload->bindparam(":id_admin", $id_admin);
    $sql_upload->bindparam(":slug", $slug);
    $sql_upload->bindparam(":name2", $filename_tl);
    //$sql_upload->bindparam(":description", $description);
    $sql_upload->bindparam(":cat_id", $cat_id);
    $sql_upload->bindparam(":scat_id", $scat_id);

    if ($categ == 1) {
      $sql_upload->bindparam(":icon_img", $new_image_name1);
    }else{
      $sql_upload->bindparam(":icon_img", $template_vect);
    }

    //$sql_upload->bindparam(":tags", $tags);
    $sql_upload->bindparam(":created", $date);
    $sql_upload->bindparam(":modified", $date);


    if ($sql_upload->execute()) {

      $post_data = $_POST;
      $post_file = $_FILES;
      $id['id'] = $DB_con->lastInsertId();
      $data = array_merge($id, $post_file, $post_data );
      //sleep(3);
      echo json_encode($data);

      //agregar un update
      //obtener ultimo lastid de insert
      //actualizar gen_id
    } else {
      echo "Error: " . $sql_upload->error;
      //$output['error'] = true;
      //$output['message'] = 'Cannot update member';
    }

  //}
}


} else {
  header('location: ../index.php');
}
