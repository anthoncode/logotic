<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-admin.php');
$pageTitle = 'Admin Sign In';
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == "recover")) {
	if (isset($_REQUEST['email']) && empty($_REQUEST['email'])) {
		$reseterror = 'Please enter email';


	} elseif (isset($_REQUEST['email'])) {

		$email = trim($_REQUEST['email']);
		$generated_password = substr(md5(rand(999, 999999)), 0, 16);
		if (!$auth->error) {
			$headers  = "MIME-Version: 1.0\n";
			$headers .= "From: " . $setting['site_name'] . " <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
			$headers .= "Content-type: text/html; charset=utf-8\n";
			$headers .= 'Content-Transfer-Encoding: 8bit\n';
			$subject = "Reset Password";
			$message = '<html>
         <head>
           <meta charset="utf-8" />
         </head>
         <body>
           <font color="#303030";>
             <div align="center">
               <table width="600px">
                 <tr>
                   <td>
                   <div align="center"><img src="' . $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon'] . '" width="80" height="80"></div><br>
                     <div align="center"><font size="4">Hi, <b>' . $email . '</b>!</font></div><br>
                     <div align="center">Your new password is: <b>' . $generated_password . '</b></div><hr>
                     <div align="center"><b>As soon as you sign in, you will be forced to change it!</b></div>
                   </td>
                 </tr>
                 <br><br>
                 <tr>
                   <td align="center">
                     <font size="2">
                       Copyright &copy; ' . $setting['site_name'] . '
                     </font>
                   </td>
                 </tr>
               </table>
             </div>
           </font>
         </body>
         </html>';
			mail($email, $subject, $message, $headers);
			$generated_password = md5($generated_password);
			$result = $auth->updateve($email, 'password_recover', '1');
			$result = $auth->updateve($email, 'password', $generated_password);
			$success = 'We have sent you an email with your password!';
		} else {
			$reseterror = $auth->error;
		}
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
	<link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/bootstrap.min.css">
	<script type="text/javascript" src="<?php echo $setting['website_url']; ?>/system/assets/js/bootstrap.bundle.min.js"></script>
	<link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/my-login.css">
	<title><?php echo $pageTitle . " | " . $setting['site_name']; ?></title>
	<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="my-login-page">
	<?php
	if (isset($_REQUEST['action']) && ($_REQUEST['action'] == "recover")) {
	?>

		<section class="">
			<div class="container h-100">
				<div class="row justify-content-md-center align-items-center h-100">
					<div class="card-wrapper mt-5">
						<div class="card fat">
							<div class="brand">
								<a href="<?php echo $setting['website_url']; ?>"><img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon']; ?>"></a>
							</div>
							<div class="card-body">
								<h4 class="card-title text-center">Forgot Password</h4>
								<form action="login.php" method="POST">
									<?php if (isset($reseterror)) {
										echo  "<div class=\"alert alert-danger\" style=\"display:block;\">" . $reseterror . "<button  type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
									} elseif (isset($success)) {
										echo  "<div class=\"alert alert-success\" style=\"display:block;\">" . $success . "<button   type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
									}
									?>
									<div class="form-group">
										<label for="email">E-Mail Address</label>
										<input id="email" type="email" class="form-control" name="email" value="" required autofocus>
										<input class="form-control" type="hidden" name="action" value="recover">
										<div class="form-text text-muted">
											By clicking "Reset Password" we will send a password reset link
										</div>
									</div>

									<div class="form-group no-margin">
										<button type="submit" name="submit" class="btn btn-primary btn-block">
											Reset Password
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

	<?php
	} else {
	?>



		<section class="">
			<div class="container h-100">
				<div class="row justify-content-md-center h-100">
					<div class="card-wrapper mt-5">
						<div class="card fat">
							<div class="card-body">
								<?php
								if (isset($error)) {
									echo  "<div class=\"alert alert-danger\" style=\"display:block;\">" . $error . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
								}
								?>
								<div class="brand">
									<a href="<?php echo $setting['website_url']; ?>"><img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon']; ?>"></a>
								</div>

								<h4 class="card-title text-center">Admin Sign In</h4>
								<form action="login.php" method="POST">

									<div class="form-group">
										<label for="email">E-Mail Address</label>

										<input id="email" type="email" class="form-control" name="email" value="" required autofocus>
									</div>

									<div class="form-group">
										<label for="password">Password
											<a href="login.php?action=recover" class="float-right">
												Forgot Password?
											</a>
										</label>
										<input id="password" type="password" class="form-control" name="pwd" required data-eye>
									</div>

									<input class="form-control" type="hidden" name="login">

									<?php if ($setting['captcha'] == '1') { ?>
									<!-- google recaptcha -->
									<div class="mb-3">
										<div class="g-recaptcha" data-sitekey="<?php echo	$setting['site_key_captcha']; ?>"></div>
									</div>
									<?php } ?>

									<div class="form-group no-margin">
										<button type="submit" class="btn btn-primary btn-block">
											Sign In
										</button>
									</div>

								</form>
							</div>
						</div>
						<div class="footer">
							Copyright &copy; <a href="<?php echo $setting['website_url']; ?>"><?php echo $setting['site_name']; ?></a>
						</div>
					</div>
				</div>
			</div>
		</section>
	<?php
	}
	?>
	<script src="../system/assets/js/my-login.js"></script>
</body>

</html>