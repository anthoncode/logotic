<?php

$pageTitle = "New Users";
require_once('../system/config-admin.php');
$customer = new Customer($DB_con);
$top = $customer->newUsers();
$num = count($top);
require_once('includes/header1.php');
?>

<?php
require_once('includes/footer.php');
?>