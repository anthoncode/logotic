<?php

$pageTitle = 'Add News';
require_once('../system/config-admin.php');

if (isset($_REQUEST['title']) && isset($_REQUEST['content'])) {

  $title = trim($_REQUEST['title']);
  $content = trim($_REQUEST['content']);
  $result = $newsl->add($title, $content);
}
if (!empty($newsl->msg)) {
  $success = $newsl->msg;
}
if (!empty($newsl->error)) {
  $error = $newsl->error;
}
require_once('includes/header1.php');

?>






  <?php
  require_once('includes/footer.php');

  ?>