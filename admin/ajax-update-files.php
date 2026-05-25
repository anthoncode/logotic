<?php
 error_reporting(E_ALL);
  ini_set('display_errors', 1);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  require_once('../system/config-admin.php');
  $productDetails = $product->details($_REQUEST['id']); //para llamar los atributos de producto


  $details    = $product->details($_REQUEST['id']);
  $cat = $product->catdetails($details['cat_id']);  
  $catid = $cat['id']; //obtiene id de cate de acuerdo al id del item

  if ($productDetails['icon_img']) {
    if (!empty($_FILES['iconimgfile']['name'])) {
      if (isset($_FILES['iconimgfile'])) {

        $vector = $_FILES['iconimgfile']['name']; //título del archivo vector
        //$tmp_name   = $_FILES['file']['tmp_name'];
        $vect_name = Product::getFileExtension($vector);  //explode, separa nombre de extensión
        $clean_name = Product::formatName($vect_name[0]);
        $slug_vect = $clean_name . '-' . time() . '-' . 'logotic';
        $join_name_vec = $slug_vect . '-brand' . '.' . end($vect_name); //une slug con extensión 1[png]
        $brand_logo = $join_name_vec; //slug completo
        $template_vect = $slug_vect . '-tmpl' . '.' . end($vect_name);

        //$image_name = $join_name_vec;


        //$aimage_name = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", "-", $aimage_name);
        $tmp_name   = $_FILES['iconimgfile']['tmp_name'];
        $aimage_type = $_FILES['iconimgfile']['type'];
        $aimage_size = $_FILES['iconimgfile']['size'];
        $aallowed_image = array('image/svg+xml'); //solo acepta archivos .svg
        if (!in_array($aimage_type, $aallowed_image)) {
          echo '<span class="text-danger">Please select a svg for icon img!</span>';
          die();
        } else {
          if ($aimage_size > 5767168) {
            echo 'file too big';
            die();
          } else {

            if (file_exists($productDetails['icon_img'])) {
            unlink("../system/assets/uploads/vector-files/" . $productDetails['icon_img']);
            }

            $categ = $catid;
            if ($categ == 1) {
              move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $brand_logo . '');
            }else{
              move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $template_vect . '');
            }

            }
          }
        }
      }
    }
  }



  if ($_FILES['iconimgfile']['name'] !== "") {

    $id = $_POST['id'];
    $date = date("Y-m-d");
    $file1 = $brand_logo;
    $file2 = $template_vect;

    $sql_upload = $DB_con->prepare("UPDATE " . PFX . "products SET icon_img=:icon_img WHERE id=:id");
    if ($categ == 1) {
    $sql_upload->bindparam(":icon_img", $file1);
    }
    if ($categ == 2) {
    $sql_upload->bindparam(":icon_img", $file2);
    }
    //$sql_upload->bindparam(":icon_img", $file2);
    $sql_upload->bindparam(":id", $id);

    if ($sql_upload->execute()) {
      echo '<span class="text-success">Logo updated! </span>';
      echo $categ;
    } else {
      echo "Error: " . $sql_upload->error;
      echo $categ;
    }
  } else {
     echo '<span class="text-danger">Select vector file </span>';
  }
