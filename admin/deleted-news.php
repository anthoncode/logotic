<?php

$pageTitle = 'Deleted News';
require_once('../system/config-admin.php');
if (isset($_REQUEST['action'])) {
  switch ($_REQUEST['action']) {
    case 'restore':
      $result = $newsl->restore($_REQUEST['id']);
      break;
  }
}

// Pagination
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 20;
$num = $newsl->countAllDeleted();
$pages = $num / $maxres;
$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
////////////////
$news = $newsl->getDeletedNews($start, $maxres);

if (!empty($newsl->msg)) {
  $success = $newsl->msg;
}
if (!empty($newsl->error)) {
  $error = $newsl->error;
}


require_once('includes/header1.php');

?>

<?php
    }
    require_once('includes/footer.php');

?>