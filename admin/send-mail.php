<?php

$pageTitle = 'Send Newsletter';
require_once('../system/config-admin.php');
$customer = new Customer($DB_con);
require_once('includes/header1.php');

?>

<?php
require_once('includes/footer.php');

?>