<?php

$pageTitle = 'Edit News';
require_once('../system/config-admin.php');
if (isset($_REQUEST['id'])) {
  if (!empty($_REQUEST['id'])) {
    $details = $newsl->details(trim($_REQUEST['id']));
  } else {
    header("location: news.php");
  }
} else {
  $details = $newsl->details('0');
}
if (isset($_REQUEST['id']) && isset($_REQUEST['title']) && isset($_REQUEST['content'])) {

  $id =   trim($_REQUEST['id']);
  $title = trim($_REQUEST['title']);
  $content = trim($_REQUEST['content']);

  $result = $newsl->updatenews($id, $title, $content);
  $details = $newsl->details(trim($_REQUEST['id']));
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