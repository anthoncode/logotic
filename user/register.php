<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


$pageTitle = 'Create an account to upload logos';
$pageMeta = 'Create a Logotic account to upload logos and icons, you only need an SVG image, you can upload files in bulk';
require_once('../system/config-user.php');

$error = false;
if (isset($_REQUEST['error'])) {
	$user->error = $_REQUEST['error'];
}

if ($_POST && isset($_REQUEST['signup'])) {

	$name = $_POST['new_name'];
	$username = $_POST['new_username'];
	$email = $_POST['new_email'];
	$npwd = $_POST['new_pwd'];
	$npwd2 = $_POST['new_pwd2'];

	if (!$validate->email($email)) {
		$user->error  = 'Please enter a valid email address!';
	} else {

		if ($user->is_new_user($email, $username)) {
			if ($npwd == $npwd2 && !$error) {
				$result = $user->add($name, $username, $email, $npwd);
				if ($result == true) {
					$result = $user->login($email, $npwd);
					if (!isset($_REQUEST['ajax'])) {
						echo ("<script> top.location.href='index.php'</script>");
					}
				}
			} else {
				$user->error  = 'Passwords do not match!';
			}
		} else {

			$user->error  = 'User already exists.';
		}
	}
	if (isset($_REQUEST['ajax'])) {
		echo ($user->error ? $user->error : "success");
		exit;
	}
} ?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<title><?php echo $pageTitle . " | " . $setting['site_name']; ?></title>
	<meta name="description" content="<?php echo $pageMeta; ?>" />
	<script src="<?php echo $setting['website_url']; ?>/system/assets/js/jquery.min.js">
	</script>
	<link href="<?php echo $setting['website_url']; ?>/system/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="<?php echo $setting['website_url']; ?>/system/assets/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/my-login.css">
</head>

<body class="my-login-page">

	<section class="">
		<div class="container h-100">
			<div class="row justify-content-md-center align-items-center h-100">
				<div class="card-wrapper mt-5">
					<div class="card fat">
						<div class="card-body">

							<div class="brand">
								<a href="<?php echo $setting['website_url']; ?>"><img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon']; ?>"></a>
							</div>



							<h4 class="card-title text-center">Create an account</h4>
							<form action"register.php" method="POST">

								<?php

								if ($user->msg) {
									echo  "<div class=\"alert alert-success\" style=\"display:block;\">" . $user->msg . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
								}
								if ($user->error) {
									echo  "<div class=\"alert alert-danger\" style=\"display:block;\">" . $user->error . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
								} ?>

								<div class="form-group">
									<label for="name">First Name</label>
									<input id="name" class="form-control" name="new_name" required="" autofocus="" type="text">
								</div>

								<div class="form-group">
									<label for="email">Username</label>
									<input id="email" class="form-control" name="new_username" required="" type="text">
								</div>

								<div class="form-group">
									<label for="email">E-Mail Address</label>
									<input id="email" class="form-control" name="new_email" required="" type="email">
								</div>

								<div class="form-group">
									<label for="password">Password</label>
									<input id="password" type="password" class="form-control" name="new_pwd" minlength="8" pattern=".{8,}" required>
								</div>

								<div class="form-group">
									<label for="password">Confirm Password</label>
									<input id="password" type="password" class="form-control" name="new_pwd2" minlength="8" pattern=".{8,}" required>
								</div>

								<div class="form-group">
									<label>
										<input name="aggree" value="1" type="checkbox" required> I agree to the Terms and Conditions
									</label>
								</div>
								<input class="form-control" type="hidden" name="signup" value="true">
								<div class="form-group no-margin">
									<button type="submit" name="submit" class="btn btn-primary btn-block">
										Sign Up
									</button>
								</div>
								<div class="margin-top20 text-center">
									Already have an account? <a href="login.php">Sign In</a>
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