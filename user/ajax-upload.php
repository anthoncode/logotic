<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  error_reporting(E_ALL);
  ini_set('display_errors', 0);
  require_once('../system/config-user.php');

  if (empty($_POST['name'])) {
    echo '<span class="text-danger">Product Name is required!</span>';
    die();
  }
  /*valida si existe*/
  $name = $_POST['name'];
  $proTo = $product->ToProducts($name);

  foreach ($proTo as $row) {
    $str = $row['name'];
  }
  if ($str == $_POST['name']) {
    echo '<span class="text-danger">This product already exists!</span>';
    die();
  }
  /*if(empty($_POST['description']))
{
  echo '<span class="text-danger">Description is required.</span>';
  die();
}*/
  if (empty($_POST['tags'])) {
    echo '<span class="text-danger">Product Tags is required!</span>';
    die();
  }
  if (empty($_POST['cat_id'])) {
    echo '<span class="text-danger">Category is required.</span>';
    die();
  }
  /*if($_FILES['previewimgfile']['size'] < 1)
{
echo 'The previewimgfile file needs to be uploaded';
die();
}*/

  if (isset($_FILES['previewimgfile'])) {

    $productDetails = $_POST['name']; //$post['name'] nombre de del archivo
    $img = $_FILES['previewimgfile']['name']; //título de producto
    $img_na = Product::getFileExtension($img);  //explode, separa nombre de extensión
    $slug_img = $productDetails . '-' . time() . '-' . 'logotic'; //concatena nombre, time y logotic
    $join_name = Product::formatName($slug_img . '.' . end($img_na)); //une título de prod. con extensión 1[png]

    $image_name = $join_name;
    $image_name = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", "-", $image_name);
    $tmp_name   = $_FILES['previewimgfile']['tmp_name'];
    $image_type = $_FILES['previewimgfile']['type'];
    $image_size = $_FILES['previewimgfile']['size'];
    $allowed_image = array('image/png', 'image/PNG');
    if (!in_array($image_type, $allowed_image)) {
      echo '<span class="text-danger">Please select a .png for preview img!</span>';
      die();
    } else {
      if ($image_size > 5767168) {
        echo 'file too big';
        die();
      } else {
        $new_image_name = $image_name;
        move_uploaded_file($tmp_name, '../system/assets/uploads/products/' . $new_image_name . '');
      }
    }
  }
  if (isset($_FILES['iconimgfile'])) {

    $productDetails = $_POST['name'];
    $vector = $_FILES['iconimgfile']['name']; //título del archivo vector
    $vect_name = Product::getFileExtension($vector);  //explode, separa nombre de extensión
    $slug_vect = $productDetails . '-' . time() . '-' . 'logotic';
    $join_name_vec = Product::formatName($slug_vect . '.' . end($vect_name)); //une título de prod. con extensión 1[png]

    $aimage_name = $join_name_vec;
    $aimage_name = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", "-", $aimage_name);
    $tmp_name   = $_FILES['iconimgfile']['tmp_name'];
    $aimage_type = $_FILES['iconimgfile']['type'];
    $aimage_size = $_FILES['iconimgfile']['size'];
    $aallowed_image = array('image/svg+xml');
    if (!in_array($aimage_type, $aallowed_image)) {
      echo '<span class="text-danger">Please select a svg for icon img!</span>';
      die();
    } else {
      if ($aimage_size > 5767168) {
        echo 'file too big';
        die();
      } else {
        $new_image_name1 = $aimage_name;
        move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $new_image_name1 . '');
      }
    }
  }

  /*if(isset($_FILES['mainfile'])){
  $name = $_FILES['mainfile']['name'];
  $name = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", "-", $name);
  $tmp_name   = $_FILES['mainfile']['tmp_name'];
  $file_size = $_FILES['mainfile']['size'];
if($file_size < 1){
    echo 'No file selcted try again!';
    die();
}
  $file_type = pathinfo($name);
  $file_type = $file_type['extension'];
  $allowed_file = array('zip','jpeg','txt');
  if(!in_array($file_type, $allowed_file)){
    echo '<span class="text-danger">Please select a .zip file for the main file</span>';
  }
  else
  {
    $new_file_name = time().$name;
    move_uploaded_file(''.$tmp_name.'', '../system/assets/uploads/product-files/'.$new_file_name.'');
}
  }*/

  $id_admin = 0; //admin
  $name2 = $_POST['name'];
  $description = $_POST['description'];
  $cat_id = $_POST['cat_id'];
  $scat_id = (isset($_POST['subcat']) ? $_POST['subcat'] : null);
  $tags = str_replace(' ', '-', $_POST['tags']);

  $date = date("Y-m-d");
  $featured = (isset($_POST['featured'])) ? 1 : 0;
  $icon = (isset($_POST['icon'])) ? 1 : 0;

  $views_off = (isset($_POST['views_off'])) ? 1 : 0;
  $download_off = (isset($_POST['download_off'])) ? 1 : 0;


  $sql_upload = $DB_con->prepare("INSERT INTO " . PFX . "products (submit_user_id, name, description, cat_id, subc_id, icon_img, preview_img, tags, created, modified, icon, featured, views_off, download_off, active) VALUES (:id_admin, :name2, :description, :cat_id, :scat_id, :icon_img, :preview_img, :tags,:created, :modified, :icon, :featured, :views_off, :download_off, '0')");

  $sql_upload->bindparam(":id_admin", $id_admin);

  $sql_upload->bindparam(":name2", $name2);
  $sql_upload->bindparam(":description", $description);
  $sql_upload->bindparam(":cat_id", $cat_id);
  $sql_upload->bindparam(":scat_id", $scat_id);
  $sql_upload->bindparam(":icon_img", $new_image_name1);
  $sql_upload->bindparam(":preview_img", $new_image_name);

  $sql_upload->bindparam(":tags", $tags);
  $sql_upload->bindparam(":created", $date);
  $sql_upload->bindparam(":modified", $date);
  $sql_upload->bindparam(":icon", $icon);
  $sql_upload->bindparam(":featured", $featured);

  $sql_upload->bindparam(":views_off", $views_off);
  $sql_upload->bindparam(":download_off", $download_off);


  if ($sql_upload->execute()) {
    echo '<span class="text-success">Product Successfully Added!</span>';
  } else {
    echo "Error: " . $sql_upload->error;
  }
} else {
  header('location: ../index.php');
}
