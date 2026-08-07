<?php

$pageTitle = 'News Management';
require_once('../system/config-admin.php');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
$num = $newsl->countAll();
$news = $newsl->get_all_news();
require_once('includes/header1.php');

?>


    <?php
    require_once('includes/footer.php');

    ?>