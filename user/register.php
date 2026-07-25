<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Create an account';
$pageMeta  = 'Create a Logotic account to download and upload logos for free.';

require_once('../system/config-user.php');

$googleClientId    = $setting['google_client_id']    ?? '';
$googleEnabled     = ($setting['google_oauth_enabled'] ?? '0') == '1' && !empty($googleClientId);

// Redirigir si ya está logueado
if ($user->is_loggedin()) {
    $dest = $_SESSION['login_redirect'] ?? $setting['website_url'] . '/user/';
    unset($_SESSION['login_redirect']);
    header('Location: ' . $dest);
    exit;
}

$errors  = [];
$success = '';

// ← CSRF debe generarse aquí, no dentro del if POST
$csrfToken = $user->generateCsrfToken();

// ── Redirect seguro tras registro ──
function getSafeRedirect($setting)
{
    $target = $_REQUEST['redirect'] ?? '';
    $base   = $setting['website_url'];
    if (!empty($target) && strpos($target, $base) === 0) {
        return $target;
    }
    return $base . '/user/';
}
if (!empty($_GET['redirect'])) {
    $_SESSION['login_redirect'] = getSafeRedirect($setting);
}

// ── Reenviar verificación ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'resend') {
    $email = strtolower(trim($_POST['resend_email'] ?? ''));
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $user->resendVerification($email);
        $success = 'If that email exists and is unverified, a new link has been sent.';
    } else {
        $errors[] = 'Invalid email address.';
    }
}

// ── Registro ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'signup') {

    // CSRF
    if (!$user->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid. Please refresh and try again.';
    }

    // Honeypot
    elseif (!empty($_POST['website'])) {
        sleep(2);
        $errors[] = 'Registration failed. Please try again.';
    } else {
        // Sanitización
        $fname    = htmlspecialchars(strip_tags(trim($_POST['new_name']     ?? '')));
        $username = htmlspecialchars(strip_tags(trim($_POST['new_username'] ?? '')));
        $email    = strtolower(trim($_POST['new_email']  ?? ''));
        $pwd      = $_POST['new_pwd']  ?? '';
        $pwd2     = $_POST['new_pwd2'] ?? '';
        $agree    = $_POST['agree']    ?? '';

        // ── Validaciones server-side ──
        if (empty($fname)) {
            $errors[] = 'First name is required.';
        } elseif (mb_strlen($fname) < 2) {
            $errors[] = 'First name must be at least 2 characters.';
        } elseif (mb_strlen($fname) > 99) {
            $errors[] = 'First name cannot exceed 99 characters.';
        } elseif (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-\']+$/u', $fname)) {
            $errors[] = 'First name contains invalid characters.';
        }

        if (empty($username)) {
            $errors[] = 'Username is required.';
        } elseif (mb_strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters.';
        } elseif (mb_strlen($username) > 50) {
            $errors[] = 'Username cannot exceed 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_\.]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, underscores and dots.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } elseif (mb_strlen($email) > 99) {
            $errors[] = 'Email is too long.';
        }

        if (empty($pwd)) {
            $errors[] = 'Password is required.';
        } elseif (mb_strlen($pwd) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        } elseif (mb_strlen($pwd) > 128) {
            $errors[] = 'Password is too long.';
        } elseif (!preg_match('/[A-Z]/', $pwd)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[0-9]/', $pwd)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if ($pwd !== $pwd2) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($agree)) {
            $errors[] = 'You must agree to the Terms and Conditions.';
        }

        // reCAPTCHA
        if ($setting['captcha'] == '1') {
            $recap = $_POST['g-recaptcha-response'] ?? '';
            if (empty($recap)) {
                $errors[] = 'Please complete the CAPTCHA verification.';
            } else {
                $verify = file_get_contents(
                    "https://www.google.com/recaptcha/api/siteverify?secret=" .
                        $setting['secret_key_captcha'] . "&response=" . $recap
                );
                $captchaResult = json_decode($verify);
                if (!$captchaResult->success) {
                    $errors[] = 'CAPTCHA verification failed. Please try again.';
                }
            }
        }

        if (empty($errors)) {
            $newId = $user->add($fname, $username, $email, $pwd);

            if ($newId) {

                error_log('New user ID: ' . $newId);
                error_log('email_verification: ' . ($setting['email_verification'] ?? 'NOT SET'));
                // Verificación por email
                if ($setting['email_verification'] == '1') {
                    $user->sendVerificationEmail($newId, $email, $fname);
                    $success = 'verify_email';
                } else {
                    // Sin verificación — login directo
                    $loginResult = $user->login($email, $pwd);
                    if ($loginResult === true) {
                        $dest = $_SESSION['login_redirect'] ?? $setting['website_url'] . '/user/';
                        unset($_SESSION['login_redirect']);
                        header('Location: ' . $dest);
                        exit;
                    }
                    $success = 'registered';
                }
            } else {
                $errors[] = $user->error ?: 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . ' — ' . $setting['site_name']; ?></title>
    <meta name="description" content="<?php echo $pageMeta; ?>">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/system/assets/css/all.min.css">
    <?php if ($setting['captcha'] == '1'): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --bg: #0d0f1c;
            --card: #13152a;
            --border: rgba(255, 255, 255, .08);
            --accent: #d4ff00;
            --text: #f0f2ff;
            --muted: #8b8fa8;
            --danger: #ff4d4d;
            --success: #2dc653;
            --radius: 14px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 50% at 20% 20%, rgba(212, 255, 0, .06) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(29, 122, 243, .06) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .login-logo img {
            height: 36px;
        }

        .login-logo span {
            display: block;
            font-size: .75rem;
            color: var(--muted);
            margin-top: .35rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 2rem;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 800;
            margin-bottom: .25rem;
        }

        .card-sub {
            font-size: .8rem;
            color: var(--muted);
            margin-bottom: 1.5rem;
        }

        .alert {
            border-radius: 10px;
            padding: .75rem 1rem;
            font-size: .8rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: .5rem;
        }

        .alert-error {
            background: rgba(255, 77, 77, .1);
            border: 1px solid rgba(255, 77, 77, .3);
            color: var(--danger);
        }

        .alert-success {
            background: rgba(45, 198, 83, .1);
            border: 1px solid rgba(45, 198, 83, .3);
            color: var(--success);
        }

        .alert-info {
            background: rgba(6, 182, 212, .1);
            border: 1px solid rgba(6, 182, 212, .3);
            color: #06b6d4;
        }

        .alert ul {
            margin: .3rem 0 0 1rem;
            padding: 0;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: .35rem;
            margin-bottom: .9rem;
        }

        .field-label {
            font-size: .78rem;
            font-weight: 600;
            color: var(--muted);
        }

        .input-wrap {
            position: relative;
        }

        .field-input {
            width: 100%;
            background: rgba(255, 255, 255, .04);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-size: .88rem;
            padding: .6rem 2.5rem .6rem .9rem;
            outline: none;
            font-family: inherit;
            transition: border-color .2s, background .2s;
        }

        .field-input:focus {
            border-color: var(--accent);
            background: rgba(212, 255, 0, .03);
        }

        .field-input::placeholder {
            color: var(--muted);
        }

        .field-input.is-error {
            border-color: var(--danger) !important;
        }

        .field-input.is-ok {
            border-color: var(--success) !important;
        }

        .input-icon {
            position: absolute;
            right: .75rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: .85rem;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
            transition: color .15s;
        }

        .input-icon:hover {
            color: var(--text);
        }

        .field-hint {
            font-size: .7rem;
            min-height: .9rem;
            display: block;
        }

        .field-hint.error {
            color: var(--danger);
        }

        .field-hint.ok {
            color: var(--success);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: .75rem;
        }

        /* Strength bar */
        .pwd-strength {
            height: 3px;
            border-radius: 99px;
            background: var(--border);
            margin-top: .3rem;
            overflow: hidden;
        }

        .pwd-strength-bar {
            height: 100%;
            border-radius: 99px;
            transition: width .3s, background .3s;
            width: 0;
        }

        .strength-label {
            font-size: .7rem;
            color: var(--muted);
            margin-top: .2rem;
        }

        /* Password rules */
        .pwd-rules {
            background: rgba(255, 255, 255, .03);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: .65rem .85rem;
            margin-top: .3rem;
        }

        .pwd-rule {
            font-size: .72rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: .4rem;
            padding: .1rem 0;
            transition: color .2s;
        }

        .pwd-rule.pass {
            color: var(--success);
        }

        .pwd-rule.pass i::before {
            content: "\f058";
        }

        /* Checkbox */
        .check-row {
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            margin-top: .25rem;
        }

        .check-row input {
            accent-color: var(--accent);
            margin-top: .15rem;
            flex-shrink: 0;
        }

        .check-row label {
            font-size: .78rem;
            color: var(--muted);
            line-height: 1.4;
        }

        .check-row a {
            color: var(--accent);
        }

        /* Button */
        .btn-primary {
            width: 100%;
            background: var(--accent);
            color: #0d0f1c;
            border: none;
            border-radius: 99px;
            font-size: .9rem;
            font-weight: 800;
            padding: .75rem;
            cursor: pointer;
            font-family: inherit;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            margin-top: 1.1rem;
        }

        .btn-primary:hover {
            background: #bfe600;
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.1rem 0;
            font-size: .72rem;
            color: var(--muted);
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .btn-google {
            width: 100%;
            background: rgba(255, 255, 255, .05);
            border: 1px solid var(--border);
            border-radius: 99px;
            color: var(--text);
            font-size: .85rem;
            font-weight: 600;
            padding: .65rem;
            cursor: pointer;
            font-family: inherit;
            transition: all .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .65rem;
            text-decoration: none;
        }

        .btn-google:hover {
            background: rgba(255, 255, 255, .09);
            border-color: rgba(255, 255, 255, .2);
        }

        .btn-google img {
            width: 18px;
            height: 18px;
        }

        .register-footer {
            text-align: center;
            margin-top: 1.25rem;
            font-size: .78rem;
            color: var(--muted);
        }

        .register-footer a {
            color: var(--accent);
            text-decoration: none;
        }

        /* Verify email screen */
        .verify-screen {
            text-align: center;
            padding: 1rem 0;
        }

        .verify-icon {
            font-size: 3.5rem;
            color: var(--accent);
            margin-bottom: 1rem;
            display: block;
        }

        .verify-title {
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: .5rem;
        }

        .verify-sub {
            color: var(--muted);
            font-size: .85rem;
            line-height: 1.6;
            margin-bottom: 1.25rem;
        }

        .verify-email-badge {
            display: inline-block;
            background: rgba(212, 255, 0, .1);
            border: 1px solid rgba(212, 255, 0, .2);
            border-radius: 99px;
            padding: .3rem 1rem;
            font-size: .82rem;
            color: var(--accent);
            font-weight: 600;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <div class="wrap">

        <!-- Logo -->
        <div class="login-logo">
            <a href="<?php echo $setting['website_url']; ?>" style="text-decoration:none;">
                <?php if ($setting['site_logo']): ?>
                    <img src="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_logo']; ?>"
                        alt="<?php echo $setting['site_name']; ?>">
                <?php else: ?>
                    <span style="font-size:1.5rem;font-weight:800;color:var(--accent);"><?php echo $setting['site_name']; ?></span>
                <?php endif; ?>
            </a>
            <span>The logo bank for designers & brands</span>
        </div>

        <?php if ($success === 'verify_email'): ?>
            <!-- ── Pantalla verificación enviada ── -->
            <div class="card">
                <div class="verify-screen">
                    <i class="fa-regular fa-envelope-circle-check verify-icon"></i>
                    <div class="verify-title">Check your inbox!</div>
                    <div class="verify-sub">
                        We've sent a verification link to:
                    </div>
                    <div class="verify-email-badge">
                        <?php echo htmlspecialchars($_POST['new_email'] ?? ''); ?>
                    </div>
                    <div class="verify-sub">
                        Click the link in the email to activate your account.
                        The link expires in <strong style="color:#f0f2ff;">24 hours</strong>.
                    </div>
                    <a href="login.php" style="color:var(--accent);font-size:.82rem;text-decoration:none;">
                        <i class="fa-regular fa-arrow-left"></i> Back to login
                    </a>
                </div>
            </div>

        <?php elseif ($success === 'registered'): ?>
            <!-- ── Registro sin verificación ── -->
            <div class="card">
                <div class="verify-screen">
                    <i class="fa-solid fa-circle-check verify-icon" style="color:#2dc653;"></i>
                    <div class="verify-title">Account created!</div>
                    <div class="verify-sub">Your account is ready. Redirecting to login...</div>
                </div>
            </div>
            <script>
                setTimeout(function() {
                    window.location.href = 'login.php';
                }, 2000);
            </script>

        <?php else: ?>
            <!-- ── Formulario de registro ── -->
            <div class="card">
                <div class="card-title">Create your account</div>
                <div class="card-sub">Join <?php echo $setting['site_name']; ?> — it's completely free.</div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-xmark" style="flex-shrink:0;margin-top:.1rem;"></i>
                        <div>
                            <?php if (count($errors) === 1): ?>
                                <?php echo htmlspecialchars($errors[0]); ?>
                            <?php else: ?>
                                <ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($setting['login'] == '1'): ?>
                    <!-- Google -->
                    <?php
                    $googleState  = bin2hex(random_bytes(8));
                    $_SESSION['oauth_state'] = $googleState;
                    $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                        'client_id'     => '648071122021-kbdish25vulmve3por1f6lfc6hbt1uli.apps.googleusercontent.com',
                        'redirect_uri'  => $setting['website_url'] . '/user/google-callback.php',
                        'response_type' => 'code',
                        'scope'         => 'email profile',
                        'state'         => $googleState,
                        'prompt'        => 'select_account',
                    ]);
                    ?>
                    <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="btn-google">
                        <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCI+PHBhdGggZmlsbD0iI0ZGQzEwNyIgZD0iTTQzLjYxMSwyMC4wODNIMjRWMjguMDgzSDM1LjMwMkMzMy4yNTMsMzMuNzI0LDI5LjA5NSwzNy4xMjMsMjQsMzcuMTIzQzE4LjA3MywzNy4xMjMsMTMuMzA5LDMyLjM1OSwxMy4zMDksMjYuNDMyQzEzLjMwOSwyMC41MDUsMTguMDczLDE1Ljc0MSwyNCwxNS43NDFDMjYuNzU5LDE1Ljc0MSwyOS4yNDYsMTYuNzQ2LDMxLjEzMSwxOC40MjNMMzYuNzA5LDEyLjg0NUM0MC44MzMsOS4xMzgsMzYuNzEzLDUuOTU5LDMyLjg5MSw2QzI4Ljk3Miw2LjA0MiwyNS4xNDIsNy4xNzEsMjEuNzkyLDkuMjQ4QzE1LjgyNywxMi45OTYsMTEuNzQ0LDE5LjIxMiwxMS43NDQsMjYuNDMyQzExLjc0NCwzNy4wNzQsMjAuMzU4LDQ1LjY4OCwzMC45OTksNDUuNjg4QzM4LjQ2NSw0NS42ODgsNDQuOTk5LDQxLjUzNiw0Ny44NzgsMzUuMTU3TDQ3Ljg3OCwzNS4xNTdDNDkuNTg5LDMxLjM3OCw0OS44OTcsMjYuOTgxLDQ4LjU1LDIyLjk0QzQ4LjAyOCwyMS4yMiw0Ni4xMzIsMjAuMDgzLDQzLjYxMSwyMC4wODNaIi8+PHBhdGggZmlsbD0iI0ZGMzMwMCIgZD0iTTYuMzA2LDE0LjY5MUwxMi44NzcsMTkuNTFDMTQuNjMxLDE0Ljk0NyAxOS4wMDgsMTEuNzQxIDI0LDExLjc0MUMyNi43NTksMTEuNzQxIDI5LjI0NiwxMi43NDYgMzEuMTMxLDE0LjQyM0wzNi43MDksOC44NDVDNDBUODE0LDUuMTM4IDM2LjcxMywxLjk1OSAzMi44OTEsMkMyNS42MjYsMi4wNjQgMTkuMjE5LDYuMjE5IDE1LjEzNywxMS42NzkgMTQuMDgzLDEzLjA5IDYuMzA2LDE0LjY5MSA2LjMwNiwxNC42OTFaIi8+PHBhdGggZmlsbD0iIzRDQUYzRSIgZD0iTTI0LDQ0QzI5LjQ4Myw0NCAzMy45NjIsNDEuOTYzIDM3LjE0NCwzOC42NzNMMzEuMDU2LDMzLjI2NkMyOS4yNjMsMzQuNTkgMjcuMjEzLDM1LjMwNCAxNS45ODYsMzUuMzA0TDE1LjI3NywzNS4zMDRDMTIuNjE1LDM1LjE5MiA4LjQ3NCwzMy4yNjYgNi4zMDYsMzMuMjY2TDYuMzA2LDMzLjI2NkMxMC42MDIsMzkuNjAzIDE2Ljg3OCw0NCAyNCw0NFoiLz48cGF0aCBmaWxsPSIjMTk3NkQyIiBkPSJNNDMuNjExLDIwLjA4M0gyNFYyOC4wODNIMzUuMzAyQzM0LjI0MywzMS4xMjEgMzIuMzY1LDMzLjU5NiAzMC4wNTYsMzUuMjY2TDMxLjA1NiwzMy4yNjZDMzMuNDA4LDMxLjc0MSAzNS4zMDIsMjkuNjkxIDM2LjM2NCwyNy4wMzNMMzYuMzY0LDI3LjAzM0M0MS4wMjksMjcuMDMzIDQ0LjE0NiwyMy4yMzggNDMuNjExLDIwLjA4M1oiLz48L3N2Zz4=" alt="Google">
                        Sign up with Google
                    </a>
                    <div class="divider">or sign up with email</div>
                <?php endif; ?>

                <form action="register.php" method="POST" id="registerForm" novalidate>
                    <input type="hidden" name="action" value="signup">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                    <!-- Honeypot -->
                    <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">

                    <!-- Nombre y username en grid -->
                    <div class="grid-2">
                        <div class="field">
                            <label class="field-label" for="fname">First Name *</label>
                            <div class="input-wrap">
                                <input class="field-input" id="fname" name="new_name" type="text"
                                    placeholder="John"
                                    value="<?php echo htmlspecialchars($_POST['new_name'] ?? ''); ?>"
                                    maxlength="99" required autocomplete="given-name">
                            </div>
                            <span class="field-hint" id="fname-hint"></span>
                        </div>
                        <div class="field">
                            <label class="field-label" for="username">Username *</label>
                            <div class="input-wrap">
                                <input class="field-input" id="username" name="new_username" type="text"
                                    placeholder="john_doe"
                                    value="<?php echo htmlspecialchars($_POST['new_username'] ?? ''); ?>"
                                    maxlength="50" required autocomplete="username">
                            </div>
                            <span class="field-hint" id="username-hint"></span>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="field">
                        <label class="field-label" for="email">Email Address *</label>
                        <div class="input-wrap">
                            <input class="field-input" id="email" name="new_email" type="email"
                                placeholder="you@example.com"
                                value="<?php echo htmlspecialchars($_POST['new_email'] ?? ''); ?>"
                                maxlength="99" required autocomplete="email">
                            <i class="fa-regular fa-envelope input-icon" style="cursor:default;"></i>
                        </div>
                        <span class="field-hint" id="email-hint"></span>
                    </div>

                    <!-- Password -->
                    <div class="field">
                        <label class="field-label" for="pwd">Password *</label>
                        <div class="input-wrap">
                            <input class="field-input" id="pwd" name="new_pwd" type="password"
                                placeholder="Min 8 chars, uppercase & number"
                                maxlength="128" required autocomplete="new-password">
                            <button type="button" class="input-icon" id="togglePwd">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <div class="pwd-strength">
                            <div class="pwd-strength-bar" id="strengthBar"></div>
                        </div>
                        <div class="strength-label" id="strengthLabel"></div>
                        <div class="pwd-rules" id="pwdRules" style="display:none;">
                            <div class="pwd-rule" id="r-len"><i class="fa-regular fa-circle"></i> At least 8 characters</div>
                            <div class="pwd-rule" id="r-upper"><i class="fa-regular fa-circle"></i> One uppercase letter</div>
                            <div class="pwd-rule" id="r-num"><i class="fa-regular fa-circle"></i> One number</div>
                        </div>
                        <span class="field-hint" id="pwd-hint"></span>
                    </div>

                    <!-- Confirm Password -->
                    <div class="field">
                        <label class="field-label" for="pwd2">Confirm Password *</label>
                        <div class="input-wrap">
                            <input class="field-input" id="pwd2" name="new_pwd2" type="password"
                                placeholder="Repeat your password"
                                maxlength="128" required autocomplete="new-password">
                            <button type="button" class="input-icon" id="togglePwd2">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                        <span class="field-hint" id="pwd2-hint"></span>
                    </div>

                    <!-- reCAPTCHA -->
                    <?php if ($setting['captcha'] == '1'): ?>
                        <div style="margin-bottom:.75rem;">
                            <div class="g-recaptcha" data-sitekey="<?php echo $setting['site_key_captcha']; ?>"></div>
                        </div>
                    <?php endif; ?>

                    <!-- Terms -->
                    <div class="check-row">
                        <input type="checkbox" id="agree" name="agree" value="1" required>
                        <label for="agree">
                            I agree to the <a href="<?php echo $setting['website_url']; ?>/terms/" target="_blank">Terms of Service</a>
                            and <a href="<?php echo $setting['website_url']; ?>/privacy/" target="_blank">Privacy Policy</a>
                        </label>
                    </div>
                    <span class="field-hint" id="agree-hint" style="margin-top:.3rem;"></span>

                    <button class="btn-primary" type="submit" id="btnRegister">
                        <i class="fa-regular fa-user-plus"></i> Create Account
                    </button>
                </form>
            </div>

            <div class="register-footer">
                Already have an account? <a href="login.php">Sign In</a>
            </div>

        <?php endif; ?>
    </div>

    <script>
        // ── Toggle passwords ──
        function togglePass(inputId, btnId) {
            var inp = document.getElementById(inputId);
            var icon = document.querySelector('#' + btnId + ' i');
            inp.type = inp.type === 'password' ? 'text' : 'password';
            icon.className = inp.type === 'password' ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
        }
        document.getElementById('togglePwd')?.addEventListener('click', function() {
            togglePass('pwd', 'togglePwd');
        });
        document.getElementById('togglePwd2')?.addEventListener('click', function() {
            togglePass('pwd2', 'togglePwd2');
        });

        // ── Validaciones en tiempo real ──
        function setState(input, hint, state, msg) {
            input.classList.remove('is-error', 'is-ok');
            hint.classList.remove('error', 'ok');
            if (state) {
                input.classList.add(state === 'error' ? 'is-error' : 'is-ok');
                hint.classList.add(state);
            }
            hint.textContent = msg;
        }

        // Nombre
        var fnameInput = document.getElementById('fname');
        fnameInput?.addEventListener('input', function() {
            var v = this.value.trim();
            var h = document.getElementById('fname-hint');
            if (!v) return setState(this, h, 'error', 'First name is required.');
            if (v.length < 2) return setState(this, h, 'error', 'At least 2 characters.');
            if (v.length > 99) return setState(this, h, 'error', 'Max 99 characters.');
            setState(this, h, 'ok', '');
        });

        // Username
        var unInput = document.getElementById('username');
        unInput?.addEventListener('input', function() {
            var v = this.value.trim();
            var h = document.getElementById('username-hint');
            if (!v) return setState(this, h, 'error', 'Username is required.');
            if (v.length < 3) return setState(this, h, 'error', 'At least 3 characters.');
            if (v.length > 50) return setState(this, h, 'error', 'Max 50 characters.');
            if (!/^[a-zA-Z0-9_\.]+$/.test(v)) return setState(this, h, 'error', 'Letters, numbers, _ and . only.');
            setState(this, h, 'ok', '');
        });

        // Email
        var emailInput = document.getElementById('email');
        emailInput?.addEventListener('input', function() {
            var v = this.value.trim();
            var h = document.getElementById('email-hint');
            if (!v) return setState(this, h, 'error', 'Email is required.');
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return setState(this, h, 'error', 'Invalid email format.');
            setState(this, h, 'ok', '');
        });

        // Password + strength
        var pwdInput = document.getElementById('pwd');
        pwdInput?.addEventListener('input', function() {
            var v = this.value;
            var h = document.getElementById('pwd-hint');
            var bar = document.getElementById('strengthBar');
            var lbl = document.getElementById('strengthLabel');
            var rules = document.getElementById('pwdRules');

            if (v) rules.style.display = 'block';

            var rLen = v.length >= 8;
            var rUpper = /[A-Z]/.test(v);
            var rNum = /[0-9]/.test(v);
            var rLong = v.length >= 12;

            setRule('r-len', rLen);
            setRule('r-upper', rUpper);
            setRule('r-num', rNum);

            var score = [rLen, rUpper, rNum, rLong].filter(Boolean).length;
            var colors = ['', '#ff4d4d', '#f4d03f', '#06b6d4', '#2dc653'];
            var labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
            bar.style.width = (score * 25) + '%';
            bar.style.background = colors[score] || '';
            lbl.textContent = labels[score] || '';
            lbl.style.color = colors[score] || '';

            if (!v) setState(this, h, 'error', 'Password is required.');
            else if (v.length < 8) setState(this, h, 'error', 'At least 8 characters.');
            else if (!rUpper) setState(this, h, 'error', 'Add an uppercase letter.');
            else if (!rNum) setState(this, h, 'error', 'Add a number.');
            else setState(this, h, 'ok', '');

            // Re-check confirm
            var p2 = document.getElementById('pwd2').value;
            if (p2) checkConfirm();
        });

        function checkConfirm() {
            var p2 = document.getElementById('pwd2');
            var h = document.getElementById('pwd2-hint');
            if (!p2.value) return setState(p2, h, 'error', 'Please confirm your password.');
            if (p2.value !== pwdInput.value) setState(p2, h, 'error', 'Passwords do not match.');
            else setState(p2, h, 'ok', '');
        }

        document.getElementById('pwd2')?.addEventListener('input', checkConfirm);

        function setRule(id, pass) {
            var el = document.getElementById(id);
            if (!el) return;
            el.classList.toggle('pass', pass);
        }

        // ── Submit ──
        document.getElementById('registerForm')?.addEventListener('submit', function(e) {
            var valid = true;

            // Trigger validations
            [fnameInput, unInput, emailInput, pwdInput].forEach(function(inp) {
                if (inp) inp.dispatchEvent(new Event('input'));
            });
            checkConfirm();

            // Check agree
            var agree = document.getElementById('agree');
            var agreeHint = document.getElementById('agree-hint');
            if (!agree.checked) {
                agreeHint.className = 'field-hint error';
                agreeHint.textContent = 'You must accept the Terms and Conditions.';
                valid = false;
            } else {
                agreeHint.textContent = '';
            }

            if (document.querySelector('.field-input.is-error')) valid = false;

            if (!valid) {
                e.preventDefault();
                document.querySelector('.field-input.is-error')?.focus();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            } else {
                var btn = document.getElementById('btnRegister');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Creating account...';
            }
        });
    </script>

</body>

</html>