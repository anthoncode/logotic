<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-admin.php');
$pageTitle = 'Reset Password';

if ($_POST && isset($_REQUEST['submit'])) {

	$npwd = $_POST['edit_new_pwd'];
	$npwd2 = $_POST['edit_new_pwd2'];

	if (!empty($npwd)) {
		$details = $auth->details($_SESSION['uid']);
		if ($npwd == $npwd2) {
			$result = $auth->update($_SESSION['uid'], 'password', md5($npwd));
			$result = $auth->update($_SESSION['uid'], 'password_recover', '0');
			$auth->msg = "Password changed successfully!";
			echo '<script>window.location = "' . $setting['website_url'] . '/admin/index.php"</script>';
		} else {
			$auth->error  = 'Passwords do not match!';
		}
	} else {
		$auth->error  = 'All fields are required!';
	}
}

?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<script src="<?php echo $setting['website_url']; ?>/system/assets/js/jquery.min.js">
	</script>
	<link href="<?php echo $setting['website_url']; ?>/system/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="<?php echo $setting['website_url']; ?>/system/assets/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/my-login.css">
	<title><?php echo $pageTitle . " | " . $setting['site_name']; ?></title>
</head>

<body class="my-login-page">

	<section class="">
		<div class="container h-100">
			<div class="row justify-content-md-center align-items-center h-100">
				<div class="card-wrapper mt-5">
					<div class="card fat">
						<div class="brand">
							<a href="<?php echo $setting['website_url']; ?>"><img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon']; ?>"></a>
						</div>
						<div class="card-body">
							<?php
							if ($auth->msg) {
								echo  "<div class=\"alert alert-success\" style=\"display:block;\">" . $auth->msg . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
							}
							if ($auth->error) {
								echo "<div class=\"alert alert-danger\" style=\"display:block;\">" . $auth->error . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
							}
							?>

							<h4 class="card-title text-center">Reset Password</h4>
							<form action="resetpwd.php" method="POST">

								<div class="form-group">
									<div class="form-group">
										<label for="email">New Password</label>

										<input id="email" type="password" class="form-control" name="edit_new_pwd" value="" minlength="8" pattern=".{8,}" autofocus>
									</div>
									<div class="form-group">
										<label for="email">Confirm Password</label>

										<input id="email" type="password" class="form-control" name="edit_new_pwd2" value="" minlength="8" pattern=".{8,}" autofocus>
									</div>
									<div class="form-text text-muted">
										You must change your password as you requested a new password!
									</div>
								</div>

								<div class="form-group no-margin">
									<button type="submit" name="submit" class="btn btn-primary btn-block">
										Change Password
									</button>
								</div>
							</form>
						</div>
					</div>
					<div class="footer">
						Copyright &copy; <?php echo $setting['site_name']; ?>
					</div>
				</div>
			</div>
		</div>
	</section>


	<script src="<?php echo $setting['website_url']; ?>/system/assets/js/my-login.js"></script>
</body>

</html>