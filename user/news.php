<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'News Announcements';
$pg = '7';
require_once('../system/config-user.php');
require_once('includes/header.php');
$news = $newsl->get_all_news();
//$news1 = $newsl->get_important_news();
?>

    <?php
    require_once('includes/footer.php');
    ?>