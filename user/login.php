<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-user.php');

// Configuración Google OAuth
$googleClientId    = $setting['google_client_id']    ?? '';
$googleEnabled     = ($setting['google_oauth_enabled'] ?? '0') == '1' && !empty($googleClientId);

$pageTitle = 'Sign In';
$pageMeta  = 'Sign in to your Logotic account';
$errors    = [];
$info      = [];

// Generar CSRF token
$csrfToken = $user->generateCsrfToken();

// Generar OAuth state
if (!isset($_SESSION['oauth_state'])) {
    $_SESSION['oauth_state'] = bin2hex(random_bytes(16));
}

$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id'     => $googleClientId,
    'redirect_uri'  => $googleRedirectUri,
    'response_type' => 'code',
    'scope'         => 'email profile',
    'state'         => $_SESSION['oauth_state'],
    'access_type'   => 'online',
    'prompt'        => 'select_account',
]);

// Ya está logueado
if ($user->is_loggedin()) {
    header('Location: ' . $setting['website_url'] . '/user/');
    exit;
}

// ── PASO 2FA ──
if (isset($_SESSION['2fa_pending']) && $_SESSION['2fa_pending'] === true) {

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_2fa') {

        if (!$user->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Security token invalid. Please refresh and try again.';
        } else {
            $code   = preg_replace('/\D/', '', $_POST['code'] ?? '');
            $userId = $_SESSION['2fa_user_id'];

            if (empty($code) || strlen($code) !== 6) {
                $errors[] = 'Please enter the 6-digit code.';
            } elseif ($user->verify2FACode($userId, $code)) {
                $user->completeLogin($userId);
                header('Location: ' . $setting['website_url'] . '/user/');
                exit;
            } else {
                $errors[] = $user->error ?: 'Invalid or expired code.';
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'resend_2fa') {
        $user->send2FACode($_SESSION['2fa_user_id'], $_SESSION['2fa_email'], $_SESSION['2fa_fname']);
        $info[] = 'A new code has been sent to ' . $_SESSION['2fa_email'];
    }

    // Mostrar formulario 2FA
    $show2FA = true;

} else {

    // ── LOGIN NORMAL ──
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {

        // CSRF
        if (!$user->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $errors[] = 'Security token invalid. Please refresh and try again.';
        } else {
            // Honeypot
            if (!empty($_POST['website'])) {
                sleep(2);
                $errors[] = 'Invalid credentials.';
            } else {
                $email    = strtolower(trim($_POST['email'] ?? ''));
                $password = $_POST['password'] ?? '';

                // Client-side fallback server validation
                if (empty($email)) {
                    $errors[] = 'Email is required.';
                } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Invalid email format.';
                } elseif (strlen($email) > 150) {
                    $errors[] = 'Email too long.';
                } elseif (empty($password)) {
                    $errors[] = 'Password is required.';
                } elseif (strlen($password) < 6) {
                    $errors[] = 'Invalid credentials.';
                } else {
                    $result = $user->login($email, $password);

                    if ($result === true) {
                        header('Location: ' . $setting['website_url'] . '/user/');
                        exit;
                    } elseif ($result === '2fa') {
                        $show2FA = true;
                        $info[]  = 'A verification code has been sent to your email.';
                    } else {
                        $errors[] = $user->error ?: 'Invalid credentials.';
                    }
                }
            }
        }
    }
    $show2FA = isset($show2FA) ? $show2FA : false;
}

// Errores desde redirect
if (isset($_GET['error'])) {
    $errMap = [
        'google_auth_failed' => 'Google sign-in failed. Please try again.',
        'invalid_state'      => 'Invalid security state. Please try again.',
    ];
    $errors[] = $errMap[$_GET['error']] ?? 'An error occurred.';
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

    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --bg:      #0d0f1c;
        --card:    #13152a;
        --border:  rgba(255,255,255,.08);
        --accent:  #d4ff00;
        --text:    #f0f2ff;
        --muted:   #8b8fa8;
        --danger:  #ff4d4d;
        --success: #2dc653;
        --radius:  14px;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg);
        color: var(--text);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    /* Background mesh */
    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 50% at 20% 20%, rgba(212,255,0,.06) 0%, transparent 60%),
            radial-gradient(ellipse 60% 40% at 80% 80%, rgba(29,122,243,.06) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    .login-wrap {
        position: relative;
        z-index: 1;
        width: 100%;
        max-width: 420px;
    }

    /* Logo */
    .login-logo {
        text-align: center;
        margin-bottom: 1.75rem;
    }

    .login-logo a {
        display: inline-block;
        text-decoration: none;
    }

    .login-logo img { height: 36px; }

    .login-logo span {
        display: block;
        font-size: .75rem;
        color: var(--muted);
        margin-top: .35rem;
    }

    /* Card */
    .login-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 2rem;
    }

    .login-title {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--text);
        margin-bottom: .3rem;
    }

    .login-sub {
        font-size: .8rem;
        color: var(--muted);
        margin-bottom: 1.5rem;
    }

    /* Alerts */
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
        background: rgba(255,77,77,.1);
        border: 1px solid rgba(255,77,77,.3);
        color: var(--danger);
    }

    .alert-info {
        background: rgba(6,182,212,.1);
        border: 1px solid rgba(6,182,212,.3);
        color: #06b6d4;
    }

    .alert ul { margin: .3rem 0 0 1rem; padding: 0; }

    /* Fields */
    .field { display: flex; flex-direction: column; gap: .35rem; margin-bottom: 1rem; }
    .field:last-of-type { margin-bottom: 0; }

    .field-label {
        font-size: .78rem;
        font-weight: 600;
        color: var(--muted);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .field-label a {
        font-weight: 400;
        color: var(--accent);
        text-decoration: none;
        font-size: .72rem;
    }

    .field-label a:hover { text-decoration: underline; }

    .input-wrap { position: relative; }

    .field-input {
        width: 100%;
        background: rgba(255,255,255,.04);
        border: 1px solid var(--border);
        border-radius: 10px;
        color: var(--text);
        font-size: .88rem;
        padding: .65rem 2.5rem .65rem .9rem;
        outline: none;
        font-family: inherit;
        transition: border-color .2s, background .2s;
    }

    .field-input:focus {
        border-color: var(--accent);
        background: rgba(212,255,0,.03);
    }

    .field-input::placeholder { color: var(--muted); }
    .field-input.is-error { border-color: var(--danger) !important; }
    .field-input.is-ok    { border-color: var(--success) !important; }

    .input-icon {
        position: absolute;
        right: .75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--muted);
        font-size: .85rem;
        cursor: pointer;
        transition: color .15s;
        background: none;
        border: none;
        padding: 0;
        line-height: 1;
    }

    .input-icon:hover { color: var(--text); }

    .field-hint {
        font-size: .7rem;
        min-height: .9rem;
        display: block;
    }

    .field-hint.error { color: var(--danger); }
    .field-hint.ok    { color: var(--success); }

    /* Divider */
    .divider {
        display: flex;
        align-items: center;
        gap: .75rem;
        margin: 1.25rem 0;
        font-size: .72rem;
        color: var(--muted);
    }

    .divider::before, .divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    /* Buttons */
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
        margin-top: 1.25rem;
    }

    .btn-primary:hover { background: #bfe600; transform: translateY(-1px); }
    .btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    .btn-google {
        width: 100%;
        background: rgba(255,255,255,.05);
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
        background: rgba(255,255,255,.09);
        border-color: rgba(255,255,255,.2);
        color: var(--text);
    }

    .btn-google img { width: 18px; height: 18px; }

    /* 2FA code input */
    .code-inputs {
        display: flex;
        gap: .5rem;
        justify-content: center;
        margin: 1.25rem 0;
    }

    .code-digit {
        width: 48px;
        height: 56px;
        background: rgba(255,255,255,.04);
        border: 1.5px solid var(--border);
        border-radius: 10px;
        color: var(--text);
        font-size: 1.4rem;
        font-weight: 700;
        text-align: center;
        outline: none;
        font-family: inherit;
        transition: border-color .2s;
        caret-color: var(--accent);
    }

    .code-digit:focus {
        border-color: var(--accent);
        background: rgba(212,255,0,.04);
    }

    /* 2FA info */
    .twofa-email {
        font-size: .82rem;
        color: var(--muted);
        text-align: center;
        margin-bottom: .5rem;
    }

    .twofa-email strong { color: var(--text); }

    .twofa-resend {
        text-align: center;
        font-size: .75rem;
        color: var(--muted);
        margin-top: .75rem;
    }

    .twofa-resend button {
        background: none;
        border: none;
        color: var(--accent);
        cursor: pointer;
        font-size: .75rem;
        font-family: inherit;
        padding: 0;
        text-decoration: underline;
    }

    /* Footer */
    .login-footer {
        text-align: center;
        margin-top: 1.25rem;
        font-size: .78rem;
        color: var(--muted);
    }

    .login-footer a { color: var(--accent); text-decoration: none; }
    .login-footer a:hover { text-decoration: underline; }

    /* Remember me */
    .remember-row {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin-top: .75rem;
    }

    .remember-row input[type="checkbox"] { accent-color: var(--accent); }
    .remember-row label { font-size: .78rem; color: var(--muted); cursor: pointer; }

    /* Strength */
    .pwd-strength { height: 3px; border-radius: 99px; background: var(--border); margin-top: .3rem; overflow: hidden; }
    .pwd-strength-bar { height: 100%; border-radius: 99px; transition: width .3s, background .3s; }

    /* Back link */
    .back-link {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: var(--muted);
        font-size: .75rem;
        text-decoration: none;
        margin-bottom: 1rem;
        transition: color .15s;
    }

    .back-link:hover { color: var(--text); }
    </style>
</head>
<body>

<div class="login-wrap">

    <!-- Logo -->
    <div class="login-logo">
        <a href="<?php echo $setting['website_url']; ?>">
            <?php if ($setting['site_logo']): ?>
                <img src="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_logo']; ?>"
                     alt="<?php echo $setting['site_name']; ?>">
            <?php else: ?>
                <span style="font-size:1.5rem;font-weight:800;color:var(--accent);"><?php echo $setting['site_name']; ?></span>
            <?php endif; ?>
        </a>
        <span>The logo bank for designers & brands</span>
    </div>

    <?php if ($show2FA): ?>
    <!-- ── 2FA FORM ── -->
    <div class="login-card">
        <a href="login.php" class="back-link">
            <i class="fa-regular fa-arrow-left"></i> Back to login
        </a>

        <div class="login-title">Verify your identity</div>
        <div class="login-sub">Enter the 6-digit code we sent to your email.</div>

        <?php foreach ($errors as $e): ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-xmark"></i> <?php echo htmlspecialchars($e); ?></div>
        <?php endforeach; ?>
        <?php foreach ($info as $i): ?>
            <div class="alert alert-info"><i class="fa-regular fa-circle-info"></i> <?php echo htmlspecialchars($i); ?></div>
        <?php endforeach; ?>

        <p class="twofa-email">
            Code sent to <strong><?php echo htmlspecialchars($_SESSION['2fa_email'] ?? ''); ?></strong>
        </p>

        <form action="login.php" method="POST" id="form2fa">
            <input type="hidden" name="action" value="verify_2fa">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <input type="hidden" name="code" id="codeHidden">

            <div class="code-inputs">
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input type="text" class="code-digit" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                <?php endfor; ?>
            </div>

            <button class="btn-primary" type="submit" id="btnVerify" disabled>
                <i class="fa-regular fa-shield-check"></i> Verify Code
            </button>
        </form>

        <div class="twofa-resend">
            Didn't receive the code?
            <form action="login.php" method="POST" style="display:inline;">
                <input type="hidden" name="action" value="resend_2fa">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                <button type="submit">Resend code</button>
            </form>
        </div>
    </div>

    <?php else: ?>
    <!-- ── LOGIN FORM ── -->
    <div class="login-card">
        <div class="login-title">Welcome back</div>
        <div class="login-sub">Sign in to your account to continue.</div>

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

        <!-- Google -->
       <?php if ($googleEnabled && $setting['login'] == '1'): ?>
        <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="btn-google">
            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCA0OCA0OCI+PHBhdGggZmlsbD0iI0ZGQzEwNyIgZD0iTTQzLjYxMSwyMC4wODNIMjRWMjguMDgzSDM1LjMwMkMzMy4yNTMsMzMuNzI0LDI5LjA5NSwzNy4xMjMsMjQsMzcuMTIzQzE4LjA3MywzNy4xMjMsMTMuMzA5LDMyLjM1OSwxMy4zMDksMjYuNDMyQzEzLjMwOSwyMC41MDUsMTguMDczLDE1Ljc0MSwyNCwxNS43NDFDMjYuNzU5LDE1Ljc0MSwyOS4yNDYsMTYuNzQ2LDMxLjEzMSwxOC40MjNMMzYuNzA5LDEyLjg0NUM0MC44MzMsOS4xMzgsMzYuNzEzLDUuOTU5LDMyLjg5MSw2QzI4Ljk3Miw2LjA0MiwyNS4xNDIsNy4xNzEsMjEuNzkyLDkuMjQ4QzE1LjgyNywxMi45OTYsMTEuNzQ0LDE5LjIxMiwxMS43NDQsMjYuNDMyQzExLjc0NCwzNy4wNzQsMjAuMzU4LDQ1LjY4OCwzMC45OTksNDUuNjg4QzM4LjQ2NSw0NS42ODgsNDQuOTk5LDQxLjUzNiw0Ny44NzgsMzUuMTU3TDQ3Ljg3OCwzNS4xNTdDNDkuNTg5LDMxLjM3OCw0OS44OTcsMjYuOTgxLDQ4LjU1LDIyLjk0QzQ4LjAyOCwyMS4yMiw0Ni4xMzIsMjAuMDgzLDQzLjYxMSwyMC4wODNaIi8+PHBhdGggZmlsbD0iI0ZGMzMwMCIgZD0iTTYuMzA2LDE0LjY5MUwxMi44NzcsMTkuNTFDMTQuNjMxLDE0Ljk0NyAxOS4wMDgsMTEuNzQxIDI0LDExLjc0MUMyNi43NTksMTEuNzQxIDI5LjI0NiwxMi43NDYgMzEuMTMxLDE0LjQyM0wzNi43MDksOC44NDVDNDAUODE0LDUuMTM4IDM2LjcxMywxLjk1OSAzMi44OTEsMkMyNS42MjYsMi4wNjQgMTkuMjE5LDYuMjE5IDE1LjEzNywxMS42NzkgMTQuMDgzLDEzLjA5IDYuMzA2LDE0LjY5MSA2LjMwNiwxNC42OTFaIi8+PHBhdGggZmlsbD0iIzRDQUYzRSIgZD0iTTI0LDQ0QzI5LjQ4Myw0NCAzMy45NjIsNDEuOTYzIDM3LjE0NCwzOC42NzNMMzEuMDU2LDMzLjI2NkMyOS4yNjMsMzQuNTkgMjcuMjEzLDM1LjMwNCAxNS45ODYsMzUuMzA0TDE1LjI3NywzNS4zMDRDMTIuNjE1LDM1LjE5MiA4LjQ3NCwzMy4yNjYgNi4zMDYsMzMuMjY2TDYuMzA2LDMzLjI2NkMxMC42MDIsMzkuNjAzIDE2Ljg3OCw0NCAyNCw0NFoiLz48cGF0aCBmaWxsPSIjMTk3NkQyIiBkPSJNNDMuNjExLDIwLjA4M0gyNFYyOC4wODNIMzUuMzAyQzM0LjI0MywzMS4xMjEgMzIuMzY1LDMzLjU5NiAzMC4wNTYsMzUuMjY2TDMxLjA1NiwzMy4yNjZDMzMuNDA4LDMxLjc0MSAzNS4zMDIsMjkuNjkxIDM2LjM2NCwyNy4wMzNMMzYuMzY0LDI3LjAzM0M0MS4wMjksMjcuMDMzIDQ0LjE0NiwyMy4yMzggNDMuNjExLDIwLjA4M1oiLz48L3N2Zz4=" alt="Google">
            Continue with Google
        </a>

        <div class="divider">or sign in with email</div>
        <?php endif; ?>

        <!-- Email/Password form -->
        <form action="login.php" method="POST" id="loginForm" novalidate>
            <input type="hidden" name="action" value="login">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            <!-- Honeypot -->
            <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">

            <div class="field">
                <label class="field-label" for="email">Email address</label>
                <div class="input-wrap">
                    <input class="field-input" id="email" type="email" name="email"
                           placeholder="you@example.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           maxlength="150" required autocomplete="email">
                    <i class="fa-regular fa-envelope input-icon" style="cursor:default;"></i>
                </div>
                <span class="field-hint" id="email-hint"></span>
            </div>

            <div class="field">
                <label class="field-label" for="password">
                    Password
                    <a href="login.php?action=recover">Forgot password?</a>
                </label>
                <div class="input-wrap">
                    <input class="field-input" id="password" type="password" name="pwd"
                           placeholder="Your password"
                           maxlength="128" required autocomplete="current-password">
                    <button type="button" class="input-icon" id="togglePwd" aria-label="Show password">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <span class="field-hint" id="pwd-hint"></span>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Keep me signed in for 48 hours</label>
            </div>

            <button class="btn-primary" type="submit" id="btnLogin">
                <i class="fa-regular fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>
    </div>

    <div class="login-footer">
        Don't have an account?
        <a href="<?php echo $setting['website_url']; ?>/user/register.php">Create one — it's free</a>
    </div>

    <?php if ($setting['captcha'] == '1'): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
    <?php endif; ?>

</div>

<script>
// ── Toggle password visibility ──
document.getElementById('togglePwd')?.addEventListener('click', function() {
    var input = document.getElementById('password');
    var icon  = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
});

// ── Client validation ──
var emailInput = document.getElementById('email');
var pwdInput   = document.getElementById('password');

function validateEmail() {
    var v    = (emailInput.value || '').trim();
    var hint = document.getElementById('email-hint');
    var re   = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!v) return setState(emailInput, hint, 'error', 'Email is required.');
    if (!re.test(v)) return setState(emailInput, hint, 'error', 'Invalid email format.');
    if (v.length > 150) return setState(emailInput, hint, 'error', 'Email too long.');
    setState(emailInput, hint, 'ok', '');
}

function validatePassword() {
    var v    = pwdInput.value || '';
    var hint = document.getElementById('pwd-hint');
    if (!v) return setState(pwdInput, hint, 'error', 'Password is required.');
    if (v.length < 6) return setState(pwdInput, hint, 'error', 'Password too short.');
    setState(pwdInput, hint, 'ok', '');
}

emailInput?.addEventListener('blur', validateEmail);
pwdInput?.addEventListener('blur', validatePassword);

document.getElementById('loginForm')?.addEventListener('submit', function(e) {
    validateEmail();
    validatePassword();
    var hasError = document.querySelector('.field-input.is-error');
    if (hasError) {
        e.preventDefault();
        hasError.focus();
    } else {
        var btn = document.getElementById('btnLogin');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Signing in...';
    }
});

function setState(input, hint, state, msg) {
    input.classList.remove('is-error', 'is-ok');
    hint.classList.remove('error', 'ok');
    input.classList.add(state === 'error' ? 'is-error' : 'is-ok');
    hint.classList.add(state);
    hint.textContent = msg;
}

// ── 2FA digit inputs ──
var digits = document.querySelectorAll('.code-digit');
if (digits.length) {
    digits.forEach(function(input, idx) {
        input.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(-1);
            if (this.value && idx < digits.length - 1) digits[idx + 1].focus();
            updateCodeHidden();
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                digits[idx - 1].focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            pasted.split('').slice(0, 6).forEach(function(char, i) {
                if (digits[i]) digits[i].value = char;
            });
            updateCodeHidden();
            var next = Math.min(pasted.length, 5);
            digits[next].focus();
        });
    });

    function updateCodeHidden() {
        var code = Array.from(digits).map(function(d) { return d.value; }).join('');
        document.getElementById('codeHidden').value = code;
        document.getElementById('btnVerify').disabled = code.length !== 6;
    }

    digits[0]?.focus();
}
</script>

</body>
</html>