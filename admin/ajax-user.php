<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  require_once('../system/config-admin.php');
  if (empty($_POST['fname'])) {
    echo '<span class="text-danger">First Name is required!</span>';
    die();
  }
  if (empty($_POST['email'])) {
    echo '<span class="text-danger">Email is required.</span>';
    die();
  }

  $id = $_POST['id'];
  $name2 = $_POST['fname'];
  $email = $_POST['email'];
  $recover = (isset($_POST['password_recover'])) ? 1 : 0;
  $newsletter = (isset($_POST['allow_email'])) ? 1 : 0;
  $moderator = (isset($_POST['moderator'])) ? 1 : 0;
  $verified = (isset($_POST['verified'])) ? 1 : 0;

  $sql_upload = $DB_con->prepare("UPDATE " . PFX . "users SET fname=:name2, email=:sdesc, allow_email=:newsletter, password_recover=:recover, moderator=:moderator, verified=:verified WHERE username=:username");

  $sql_upload->bindparam(":name2", $name2);
  $sql_upload->bindparam(":sdesc", $email);
  $sql_upload->bindparam(":recover", $recover);
  $sql_upload->bindparam(":newsletter", $newsletter);
  $sql_upload->bindparam(":moderator", $moderator);
  $sql_upload->bindparam(":verified", $verified);
  $sql_upload->bindparam(":username", $id);

  if ($sql_upload->execute()) {
    echo '<span class="text-success">User Updated!</span>';
  } else {
    echo "Error: " . $sql_upload->error;
  }
} else {
  header('location: ../index.php');
}
