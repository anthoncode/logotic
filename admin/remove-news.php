<?php

$pageTitle = 'Delete News';
require_once('../system/config-admin.php');
if (isset($_REQUEST['id'])) {
	if (!empty($_REQUEST['id'])) {
		$details = $newsl->details(trim($_REQUEST['id']));
	} else {
		header("location:news.php");
	}
} else {
	$details = $newsl->details('0');
}
if (isset($_REQUEST['id']) && 'delete' == @$_REQUEST['action']) {
	if (!empty($_REQUEST['id'])) {

		$result = $newsl->remove($_REQUEST['id']);
		unset($details);
	}
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