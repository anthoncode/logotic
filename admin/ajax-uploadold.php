<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  require_once('../system/config-admin.php');
  if (!preg_match('/^([1-9][0-9]*|0)(\.[0-9]{2})?$/', $_POST['price'])) {
    echo '<span class="text-danger">You need to put a price</span>';
    die();
  }
  if (empty($_POST['name'])) {
    echo '<span class="text-danger">Product Name is required!</span>';
    die();
  }

  if (empty($_POST['description'])) {
    echo '<span class="text-danger">Description is required.</span>';
    die();
  }
  if (empty($_POST['cat_id'])) {
    echo '<span class="text-danger">Category is required.</span>';
    die();
  }
  if ($_FILES['previewimgfile']['size'] < 1) {
    echo 'The previewimgfile file needs to be uploaded';
    die();
  }

  if (isset($_FILES['previewimgfile'])) {
    $image_name = $_FILES['previewimgfile']['name'];
    $image_name = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", "", $image_name);
    $tmp_name   = $_FILES['previewimgfile']['tmp_name'];
    $image_type = $_FILES['previewimgfile']['type'];
    $image_size = $_FILES['previewimgfile']['size'];
    $allowed_image = array('image/png', 'image/PNG', 'image/jpeg', 'image/JPEG', 'image/GIF', 'image/gif');
    if (!in_array($image_type, $allowed_image)) {
      echo '<span class="text-danger">Please select a jpg/png/gif for preview img!</span>';
      die();
    } else {
      if ($image_size > 5767168) {
        echo 'file too big';
        die();
      } else {
        $new_image_name = time() . $image_name;
        move_uploaded_file($tmp_name, '../system/assets/uploads/products/' . $new_image_name . '');
      }
    }
  }
  if (isset($_FILES['iconimgfile'])) {
    $aimage_name = $_FILES['iconimgfile']['name'];
    $aimage_name = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", "", $aimage_name);
    $tmp_name   = $_FILES['iconimgfile']['tmp_name'];
    $aimage_type = $_FILES['iconimgfile']['type'];
    $aimage_size = $_FILES['iconimgfile']['size'];
    $aallowed_image = array('image/png', 'image/PNG', 'image/jpeg', 'image/JPEG', 'image/GIF', 'image/gif');
    if (!in_array($aimage_type, $aallowed_image)) {
      echo '<span class="text-danger">Please select a jpg/png/gif for icon img 00!</span>';
      die();
    } else {
      if ($aimage_size > 5767168) {
        echo 'file too big';
        die();
      } else {
        $new_image_name1 = time() . $aimage_name;
        move_uploaded_file($tmp_name, '../system/assets/uploads/products/' . $new_image_name1 . '');
      }
    }
  }
  if (isset($_FILES['mainfile'])) {
    $name = $_FILES['mainfile']['name'];
    $name = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", "", $name);
    $tmp_name   = $_FILES['mainfile']['tmp_name'];
    $file_size = $_FILES['mainfile']['size'];
    if ($file_size < 1) {
      echo 'No file selcted try again!';
      die();
    }
    $file_type = pathinfo($name);
    $file_type = $file_type['extension'];
    $allowed_file = array('zip', 'jpeg', 'txt');
    if (!in_array($file_type, $allowed_file)) {
      echo '<span class="text-danger">Please select a .zip file for the main file</span>';
    } else {
      $new_file_name = time() . $name;
      move_uploaded_file('' . $tmp_name . '', '../system/assets/uploads/product-files/' . $new_file_name . '');
    }
  }


  $name2 = $_POST['name'];
  $description = $_POST['description'];
  $cat_id = $_POST['cat_id'];
  $scat_id = (isset($_POST['subcat']) ? $_POST['subcat'] : null);
  $demo = $_POST['demo'];
  $price = $_POST['price'];
  $date = date("Y-m-d");
  $featured = (isset($_POST['featured'])) ? 1 : 0;
  $support = (isset($_POST['support'])) ? 1 : 0;

  $views_off = (isset($_POST['views_off'])) ? 1 : 0;
  $download_off = (isset($_POST['download_off'])) ? 1 : 0;


  //$preview_img = $_FILES['previewimgfile'];
  //$icon_img = $_FILES['iconimgfile'];
  //$mainfile = $_FILES['mainfile']; 

  $sql_upload = $DB_con->prepare("INSERT INTO " . PFX . "products (name, description, cat_id, subc_id, icon_img, preview_img, file, price, demo, created, modified, support, featured, views_off, download_off, active) VALUES (:name2, :description, :cat_id, :scat_id, :icon_img, :preview_img, :file, :price, :demo, :created, :modified, :support, :featured, :views_off, :download_off, '1')");

  $sql_upload->bindparam(":name2", $name2);
  $sql_upload->bindparam(":description", $description);
  $sql_upload->bindparam(":cat_id", $cat_id);
  $sql_upload->bindparam(":scat_id", $scat_id);
  $sql_upload->bindparam(":icon_img", $new_image_name1);
  $sql_upload->bindparam(":preview_img", $new_image_name);
  $sql_upload->bindparam(":file", $new_file_name);
  $sql_upload->bindparam(":price", $price);
  $sql_upload->bindparam(":demo", $demo);
  $sql_upload->bindparam(":created", $date);
  $sql_upload->bindparam(":modified", $date);
  $sql_upload->bindparam(":support", $support);
  $sql_upload->bindparam(":featured", $featured);

  $sql_upload->bindparam(":views_off", $views_off);
  $sql_upload->bindparam(":download_off", $download_off);


  if ($sql_upload->execute()) {
    echo '<span class="text-success">Product added for selling.</span>';
  } else {
    echo "Error: " . $sql_upload->error;
  }

  /*$sql_upload = "INSERT INTO " . PFX . "products (name, short_des, description, cat_id, icon_img, preview_img, file, price, demo, created, modified, support, featured, active) VALUES ('$name2', '$sdesc', '$description', '$cat_id', '$new_image_name1', '$new_image_name', '$new_file_name', '$price', '$demo', '$date', '$date', '$support', '$featured', '1')";
      if($DB_con->query($sql_upload) === TRUE)
      {
        echo '<span class="text-success">Product added for selling.</span>';
      }*/
} else {
  header('location: ../index.php');
}
