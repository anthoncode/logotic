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

if (isset($details)) {

?>

	<nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
			<ul class="navbar-nav">
				<li class="nav-item">
					<a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/news.php">All News</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-news.php">Add News</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-news.php">Deleted News</a>
				</li>
			</ul>
		</div>
	</nav>

	<div class="my-3 p-3 bg-white rounded box-shadow">
		<h3>Are you sure you want to delete this news article?</h3><br>
		<form action="remove-news.php?id=<?php echo $details['id']; ?>" method="post" class="form-horizontal">

			<div class="form-group">
				<div class="col-md-4 col-md-offset-3">
					<input class="form-control" type="hidden" name="action" value="delete">
					<button class="btn btn-danger">Delete</button>
				</div>
			</div>
		</form>
	</div>
<?php } ?>
<?php
require_once('includes/footer.php');

?>