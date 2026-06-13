<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-user.php');

$token  = $_GET['token'] ?? '';
$result = $user->verifyEmailToken($token);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification — <?php echo $setting['site_name']; ?></title>
    <link rel="icon" href="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/system/assets/css/all.min.css">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
        font-family: 'Poppins', sans-serif;
        background: #0d0f1c;
        color: #f0f2ff;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .card {
        background: #13152a;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 14px;
        padding: 2.5rem 2rem;
        max-width: 420px;
        width: 100%;
        text-align: center;
    }
    .icon { font-size: 3rem; margin-bottom: 1rem; display: block; }
    h1 { font-size: 1.3rem; font-weight: 800; margin-bottom: .5rem; }
    p  { color: #8b8fa8; font-size: .85rem; line-height: 1.6; margin-bottom: 1.25rem; }
    .btn {
        display: inline-flex; align-items: center; gap: .5rem;
        background: #d4ff00; color: #0d0f1c;
        border-radius: 99px; padding: .65rem 1.75rem;
        font-weight: 800; font-size: .9rem;
        text-decoration: none; transition: all .2s;
    }
    .btn:hover { background: #bfe600; }
    .btn-outline {
        background: transparent;
        border: 1px solid rgba(255,255,255,.1);
        color: #8b8fa8;
        margin-top: .5rem;
    }
    .btn-outline:hover { border-color: #f0f2ff; color: #f0f2ff; background: transparent; }
    </style>
</head>
<body>
    <div class="card">
        <?php if ($result): ?>
            <span class="icon" style="color:#2dc653;">
                <i class="fa-solid fa-circle-check"></i>
            </span>
            <h1>Email Verified!</h1>
            <p>Your account is now active. You can sign in and start exploring thousands of logos.</p>
            <a href="login.php" class="btn">
                <i class="fa-regular fa-arrow-right-to-bracket"></i> Sign In Now
            </a>
        <?php else: ?>
            <span class="icon" style="color:#ff4d4d;">
                <i class="fa-solid fa-circle-xmark"></i>
            </span>
            <h1>Link Expired or Invalid</h1>
            <p>This verification link has expired or is invalid. Request a new one below.</p>
            <form action="register.php" method="POST">
                <input type="hidden" name="action" value="resend">
                <div style="margin-bottom:1rem;">
                    <input type="email" name="resend_email"
                           placeholder="Enter your email"
                           style="width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:#f0f2ff;font-size:.85rem;padding:.6rem .9rem;outline:none;font-family:inherit;"
                           required>
                </div>
                <button type="submit" class="btn" style="width:100%;justify-content:center;">
                    <i class="fa-regular fa-envelope"></i> Resend Verification
                </button>
            </form>
            <br>
            <a href="login.php" class="btn btn-outline">← Back to login</a>
        <?php endif; ?>
    </div>
</body>
</html>