<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-user.php');
$pageTitle = 'Sign into your account';
$pageMeta = "Log in to Logotic to upload logos, share your own designs or publish brand logos, before logging in remember to register";

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == "recover")) {
    if (isset($_REQUEST['email']) && empty($_REQUEST['email'])) {
        $reseterror = 'Please enter email';
    } elseif (isset($_REQUEST['email'])) {
        $email = trim($_REQUEST['email']);
        $generated_password = substr(md5(rand(999, 999999)), 0, 8);
        if (!$user->error) {
            $headers  = "MIME-Version: 1.0\n";
            $headers .= "From: " . $setting['site_name'] . " <noreply@" . $_SERVER['HTTP_HOST'] . "> \n";
            $headers .= "Content-type: text/html; charset=utf-8\n";
            $headers .= 'Content-Transfer-Encoding: 8bit\n';
            $subject  = "Reset Password";
            $message  = '<html><head><meta charset="utf-8"/></head><body>
                <div align="center"><img src="' . $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon'] . '" width="80" height="80"><br>
                <font size="4">Hi, <b>' . $email . '</b>!</font><br>
                Your new password is: <b>' . $generated_password . '</b><hr>
                <b>As soon as you sign in, you will be forced to change it!</b></div>
                </body></html>';
            mail($email, $subject, $message, $headers);
            $generated_password = md5($generated_password);
            $result = $user->updateve($email, 'password_recover', '1');
            $result = $user->updateve($email, 'password', $generated_password);
            $success = 'We have sent you an email with your new password!';
        } else {
            $reseterror = $user->error;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo $pageTitle . " | " . $setting['site_name']; ?></title>
    <meta name="description" content="<?php echo $pageMeta; ?>" />
    <script src="<?php echo $setting['website_url']; ?>/system/assets/js/jquery.min.js"></script>
    <link href="<?php echo $setting['website_url']; ?>/system/assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $setting['website_url']; ?>/system/assets/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/system/assets/css/my-login.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body class="my-login-page">

<?php if (isset($_REQUEST['action']) && $_REQUEST['action'] == "recover"): ?>

    <!-- ── FORGOT PASSWORD ── -->
    <section class="">
        <div class="container h-100">
            <div class="row justify-content-md-center align-items-center h-100">
                <div class="card-wrapper mt-5">
                    <div class="card fat">
                        <div class="brand">
                            <a href="<?php echo $setting['website_url']; ?>">
                                <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon']; ?>">
                            </a>
                        </div>
                        <div class="card-body">
                            <h4 class="card-title text-center">Forgot Password</h4>
                            <form action="login.php" method="POST">
                                <?php if (isset($reseterror)): ?>
                                    <div class="alert alert-danger" style="display:block;"><?php echo $reseterror; ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
                                <?php elseif (isset($success)): ?>
                                    <div class="alert alert-success" style="display:block;"><?php echo $success; ?><button type="button" class="close" data-dismiss="alert">&times;</button></div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="email">E-Mail Address</label>
                                    <input id="email" type="email" class="form-control" name="email" required autofocus>
                                    <input type="hidden" name="action" value="recover">
                                    <div class="form-text text-muted">We'll send you a new password by email.</div>
                                </div>

                                <div class="form-group no-margin">
                                    <button type="submit" name="submit" class="btn btn-primary btn-block">Reset Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="footer">Copyright &copy; <?php echo $setting['site_name']; ?></div>
                </div>
            </div>
        </div>
    </section>

<?php else: ?>

    <!-- ── SIGN IN ── -->
    <section class="">
        <div class="container h-100">
            <div class="row justify-content-md-center h-100">
                <div class="card-wrapper mt-5">
                    <div class="card fat">
                        <div class="card-body">
                            <div class="brand">
                                <a href="<?php echo $setting['website_url']; ?>">
                                    <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon']; ?>">
                                </a>
                            </div>
                            <h4 class="card-title text-center">Sign into your account</h4>
                            <form action="login.php" method="POST">

                                <div class="form-group">
                                    <label for="email">E-Mail Address</label>
                                    <input id="email" type="email" class="form-control" name="email" required autofocus>
                                </div>

                                <div class="form-group">
                                    <label for="password">Password
                                        <a href="login.php?action=recover" class="float-right">Forgot Password?</a>
                                    </label>
                                    <input id="password" type="password" class="form-control" name="pwd" required data-eye>
                                </div>

                                <input type="hidden" name="login">

                                <!-- ← CAPTCHA aquí, solo en Sign In -->
                                <?php if ($setting['captcha'] == '1'): ?>
                                <div class="mb-3">
                                    <div class="g-recaptcha" data-sitekey="<?php echo $setting['site_key_captcha']; ?>"></div>
                                </div>
                                <?php endif; ?>

                                <div class="form-group no-margin">
                                    <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                                </div>
                                <div class="margin-top20 text-center">
                                    Don't have an account? <a href="register.php">Sign Up</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="footer">Copyright &copy; <?php echo $setting['site_name']; ?></div>
                </div>
            </div>
        </div>
    </section>

<?php endif; ?>

<script src="<?php echo $setting['website_url']; ?>/system/assets/js/my-login.js"></script>
</body>
</html>