<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-admin.php');
$pageTitle = 'Admin Sign In';

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == "recover")) {
    if (isset($_REQUEST['email']) && empty($_REQUEST['email'])) {
        $reseterror = 'Please enter your email address';
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
         <head><meta charset="utf-8" /></head>
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
                 <tr><td align="center"><font size="2">Copyright &copy; ' . $setting['site_name'] . '</font></td></tr>
               </table>
             </div>
           </font>
         </body>
         </html>';
            mail($email, $subject, $message, $headers);
            $generated_password = md5($generated_password);
            $result = $auth->updateve($email, 'password_recover', '1');
            $result = $auth->updateve($email, 'password', $generated_password);
            $success = 'We have sent you an email with your new password!';
        } else {
            $reseterror = $auth->error;
        }
    }
}

$isRecover = isset($_REQUEST['action']) && $_REQUEST['action'] == "recover";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title><?php echo $pageTitle . " | " . $setting['site_name']; ?></title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="icon" href="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/system/assets/css/all.min.css">
    <script src="<?php echo $setting['website_url']; ?>/system/assets/js/jquery.min.js"></script>
    <?php if ($setting['captcha'] == '1'): ?>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>

    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --adm-bg: #0d0f1c;
        --adm-card: #13152a;
        --adm-border: rgba(255,255,255,.08);
        --adm-accent: #d4ff00;
        --adm-text: #f0f2ff;
        --adm-muted: #8b8fa8;
        --adm-danger: #ff4d4d;
        --adm-success: #2dc653;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: var(--adm-bg);
        color: var(--adm-text);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 50% at 20% 20%, rgba(212,255,0,.06) 0%, transparent 60%),
            radial-gradient(ellipse 60% 40% at 80% 80%, rgba(29,122,243,.05) 0%, transparent 60%);
        pointer-events: none;
    }

    .adm-login-wrap {
        position: relative;
        width: 100%;
        max-width: 400px;
    }

    .adm-login-brand {
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .adm-login-brand img { height: 42px; }

    .adm-login-brand-tag {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--adm-accent);
        background: rgba(212,255,0,.08);
        border: 1px solid rgba(212,255,0,.2);
        border-radius: 99px;
        padding: .25rem .85rem;
        margin-top: .75rem;
    }

    .adm-login-card {
        background: var(--adm-card);
        border: 1px solid var(--adm-border);
        border-radius: 16px;
        padding: 2rem;
    }

    .adm-login-title {
        font-size: 1.25rem;
        font-weight: 800;
        text-align: center;
        margin-bottom: .3rem;
    }

    .adm-login-sub {
        font-size: .8rem;
        color: var(--adm-muted);
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .adm-alert {
        border-radius: 10px;
        padding: .75rem 1rem;
        font-size: .8rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: .5rem;
    }
    .adm-alert-error { background: rgba(255,77,77,.1); border: 1px solid rgba(255,77,77,.3); color: var(--adm-danger); }
    .adm-alert-success { background: rgba(45,198,83,.1); border: 1px solid rgba(45,198,83,.3); color: var(--adm-success); }

    .adm-field { display: flex; flex-direction: column; gap: .4rem; margin-bottom: 1.1rem; }

    .adm-field-label {
        font-size: .78rem;
        font-weight: 600;
        color: var(--adm-muted);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .adm-field-label a { color: var(--adm-accent); text-decoration: none; font-size: .72rem; font-weight: 400; }
    .adm-field-label a:hover { text-decoration: underline; }

    .adm-input-wrap { position: relative; }

    .adm-login-input {
        width: 100%;
        background: rgba(255,255,255,.04);
        border: 1px solid var(--adm-border);
        border-radius: 10px;
        color: var(--adm-text);
        font-size: .88rem;
        padding: .7rem 2.5rem .7rem .9rem;
        outline: none;
        font-family: inherit;
        transition: border-color .2s, background .2s;
    }
    .adm-login-input:focus { border-color: var(--adm-accent); background: rgba(212,255,0,.03); }
    .adm-login-input::placeholder { color: var(--adm-muted); }

    .adm-input-icon {
        position: absolute;
        right: .75rem; top: 50%;
        transform: translateY(-50%);
        color: var(--adm-muted);
        font-size: .85rem;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0;
    }
    .adm-input-icon:hover { color: var(--adm-text); }

    .adm-login-btn {
        width: 100%;
        background: var(--adm-accent);
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
        margin-top: .5rem;
    }
    .adm-login-btn:hover { background: #bfe600; transform: translateY(-1px); }

    .adm-login-hint {
        font-size: .72rem;
        color: var(--adm-muted);
        margin-top: .4rem;
    }

    .adm-login-footer {
        text-align: center;
        margin-top: 1.25rem;
        font-size: .75rem;
        color: var(--adm-muted);
    }
    .adm-login-footer a { color: var(--adm-accent); text-decoration: none; }

    .adm-back-link {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: var(--adm-muted);
        font-size: .75rem;
        text-decoration: none;
        margin-bottom: 1rem;
        transition: color .15s;
    }
    .adm-back-link:hover { color: var(--adm-text); }

    .g-recaptcha { margin-bottom: 1rem; transform: scale(0.95); transform-origin: left; }
    </style>
</head>
<body>

<div class="adm-login-wrap">

    <!-- Brand -->
    <div class="adm-login-brand">
        <a href="<?php echo $setting['website_url']; ?>">
            <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon']; ?>" alt="<?php echo $setting['site_name']; ?>">
        </a>
        <div>
            <span class="adm-login-brand-tag">
                <i class="fa-solid fa-shield-halved"></i> Admin Panel
            </span>
        </div>
    </div>

    <?php if ($isRecover): ?>
    <!-- ── RECOVER PASSWORD ── -->
    <div class="adm-login-card">
        <a href="login.php" class="adm-back-link">
            <i class="fa-regular fa-arrow-left"></i> Back to sign in
        </a>

        <div class="adm-login-title">Forgot Password</div>
        <div class="adm-login-sub">Enter your email to receive a new password</div>

        <?php if (isset($reseterror)): ?>
            <div class="adm-alert adm-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?php echo $reseterror; ?></div>
        <?php elseif (isset($success)): ?>
            <div class="adm-alert adm-alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="action" value="recover">
            <div class="adm-field">
                <label class="adm-field-label" for="email">Email Address</label>
                <div class="adm-input-wrap">
                    <input class="adm-login-input" id="email" type="email" name="email" required autofocus placeholder="admin@example.com">
                    <i class="fa-regular fa-envelope adm-input-icon" style="cursor:default;"></i>
                </div>
                <span class="adm-login-hint">We'll send a password reset link to this email.</span>
            </div>
            <button class="adm-login-btn" type="submit">
                <i class="fa-regular fa-paper-plane"></i> Reset Password
            </button>
        </form>
    </div>

    <?php else: ?>
    <!-- ── SIGN IN ── -->
    <div class="adm-login-card">
        <div class="adm-login-title">Welcome back</div>
        <div class="adm-login-sub">Sign in to access the admin panel</div>

        <?php if (isset($error)): ?>
            <div class="adm-alert adm-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="adm-field">
                <label class="adm-field-label" for="email">Email Address</label>
                <div class="adm-input-wrap">
                    <input class="adm-login-input" id="email" type="email" name="email" required autofocus placeholder="admin@example.com">
                    <i class="fa-regular fa-envelope adm-input-icon" style="cursor:default;"></i>
                </div>
            </div>

            <div class="adm-field">
                <label class="adm-field-label" for="password">
                    Password
                    <a href="login.php?action=recover">Forgot password?</a>
                </label>
                <div class="adm-input-wrap">
                    <input class="adm-login-input" id="password" type="password" name="pwd" required placeholder="Your password">
                    <button type="button" class="adm-input-icon" id="togglePwd">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <input type="hidden" name="login">

            <?php if ($setting['captcha'] == '1'): ?>
            <div class="g-recaptcha" data-sitekey="<?php echo $setting['site_key_captcha']; ?>"></div>
            <?php endif; ?>

            <button class="adm-login-btn" type="submit">
                <i class="fa-regular fa-arrow-right-to-bracket"></i> Sign In
            </button>
        </form>
    </div>

    <div class="adm-login-footer">
        Copyright &copy; <a href="<?php echo $setting['website_url']; ?>"><?php echo $setting['site_name']; ?></a>
    </div>
    <?php endif; ?>

</div>

<script>
document.getElementById('togglePwd')?.addEventListener('click', function() {
    var input = document.getElementById('password');
    var icon = this.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
});
</script>

</body>
</html>