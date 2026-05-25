<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  require_once('../system/config-admin.php');

  if (!empty($_FILES['logoimg']['name'])) {
    if (isset($_FILES['logoimg'])) {
      $image_name = $_FILES['logoimg']['name'];
      $image_name = $image_name;
      $tmp_name   = $_FILES['logoimg']['tmp_name'];
      $image_type = $_FILES['logoimg']['type'];
      $image_size = $_FILES['logoimg']['size'];
      $allowed_image = array('image/png', 'image/PNG', 'image/jpeg', 'image/JPEG');
      if (!in_array($image_type, $allowed_image)) {
        echo '<span class="text-danger">Please select a jpg/png for logo img!</span>';
        die();
      } else {
        if ($image_size > 5767168) {
          echo 'file too big';
          die();
        } else {
          $new_image_name = $image_name;
          move_uploaded_file($tmp_name, '../system/assets/uploads/img/' . $new_image_name . '');
        }
      }
    }
  }
  if (!empty($_FILES['faviconimg']['name'])) {
    if (isset($_FILES['faviconimg'])) {
      $aimage_name = $_FILES['faviconimg']['name'];
      $aimage_name = $aimage_name;
      $tmp_name   = $_FILES['faviconimg']['tmp_name'];
      $aimage_type = $_FILES['faviconimg']['type'];
      $aimage_size = $_FILES['faviconimg']['size'];
      $aallowed_image = array('image/png', 'image/PNG', 'image/jpeg', 'image/JPEG');
      if (!in_array($aimage_type, $aallowed_image)) {
        echo '<span class="text-danger">Please select a jpg/png for favicon img!</span>';
        die();
      } else {
        if ($aimage_size > 5767168) {
          echo 'file too big';
          die();
        } else {
          $new_image_name1 = $aimage_name;
          move_uploaded_file($tmp_name, '../system/assets/uploads/img/' . $new_image_name1 . '');
        }
      }
    }
  }


  if ($_FILES['logoimg']['name'] !== "") {

    $sql_upload = $DB_con->prepare("UPDATE " . PFX . "settings SET value=:value WHERE setting = 'site_logo'");

    $sql_upload->bindparam(":value", $new_image_name);

    if ($sql_upload->execute()) {
      echo '<span class="text-success">Site Logo Updated! </span>';
    } else {
      echo "Error: " . $sql_upload->error;
    }
  }
  if ($_FILES['faviconimg']['name'] !== "") {

    $sql_upload = $DB_con->prepare("UPDATE " . PFX . "settings SET value=:value WHERE setting = 'site_favicon'");

    $sql_upload->bindparam(":value", $new_image_name1);

    if ($sql_upload->execute()) {
      echo '<span class="text-success">Favicon/Login Icon Updated! </span>';
    } else {
      echo "Error: " . $sql_upload->error;
    }
  }
} else {
  header('location: ../index.php');
}
