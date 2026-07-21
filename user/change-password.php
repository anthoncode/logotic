<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Change Password';
$pg = '7';

require_once('../system/config-user.php');

$uid     = $crypt->decrypt($_SESSION['uid'], 'USER');
$errors  = [];
$success = '';

$csrfToken = $user->generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_pwd') {
    if (!$user->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid.';
    } else {
        $current = $_POST['current_pwd'] ?? '';
        $new     = $_POST['new_pwd']     ?? '';
        $new2    = $_POST['new_pwd2']    ?? '';

        if (empty($current)) $errors[] = 'Current password is required.';
        if (empty($new)) {
            $errors[] = 'New password is required.';
        } elseif (mb_strlen($new) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $new)) {
            $errors[] = 'Password must contain an uppercase letter.';
        } elseif (!preg_match('/[0-9]/', $new)) {
            $errors[] = 'Password must contain a number.';
        }
        if ($new !== $new2) $errors[] = 'New passwords do not match.';

        if (empty($errors)) {
            // Verificar contraseña actual
            $stmt = $DB_con->prepare("SELECT password FROM " . PFX . "users WHERE id = :id");
            $stmt->execute([':id' => $uid]);
            $hash = $stmt->fetchColumn();

            $valid = (strlen($hash) === 32 && ctype_xdigit($hash))
                ? md5($current) === $hash
                : password_verify($current, $hash);

            if (!$valid) {
                $errors[] = 'Current password is incorrect.';
            } else {
                $newHash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
                $upd = $DB_con->prepare("UPDATE " . PFX . "users SET password = :pwd WHERE id = :id");
                $upd->execute([':pwd' => $newHash, ':id' => $uid]);
                $success = 'Password changed successfully.';
            }
        }
    }
}

require_once('includes/header.php');
?>

<div class="user-panel-head">
    <h1>Change Password</h1>
    <p>Update your password to keep your account secure</p>
</div>

<style>
.cp-field { display:flex; flex-direction:column; gap:.4rem; margin-bottom:1.1rem; }
.cp-label { font-size:.8rem; font-weight:600; color:var(--text-muted); }
.cp-wrap { position:relative; }
.cp-input {
    width:100%; background:rgba(255,255,255,.04);
    border:1px solid var(--border); border-radius:10px;
    color:var(--text-primary); font-size:.88rem;
    padding:.65rem 2.5rem .65rem .9rem; outline:none;
    font-family:inherit; transition:border-color .2s;
}
.cp-input:focus { border-color:var(--accent); }
.cp-input.is-error { border-color:#ff4d4d; }
.cp-input.is-ok { border-color:#2dc653; }
.cp-toggle {
    position:absolute; right:.7rem; top:50%; transform:translateY(-50%);
    background:none; border:none; color:var(--text-muted);
    cursor:pointer; font-size:.85rem; padding:0;
}
.cp-hint { font-size:.7rem; min-height:.9rem; }
.cp-hint.error { color:#ff4d4d; }
.cp-hint.ok { color:#2dc653; }

.cp-strength { height:3px; border-radius:99px; background:var(--border); margin-top:.3rem; overflow:hidden; }
.cp-strength-bar { height:100%; border-radius:99px; transition:all .3s; width:0; }

.cp-rules {
    background:rgba(255,255,255,.03); border:1px solid var(--border);
    border-radius:8px; padding:.65rem .85rem; margin-top:.3rem;
}
.cp-rule { font-size:.72rem; color:var(--text-muted); display:flex; align-items:center; gap:.4rem; padding:.1rem 0; }
.cp-rule.pass { color:#2dc653; }
.cp-rule.pass i::before { content:"\f058"; }

.cp-btn {
    background:var(--accent); color:#0d0f1c; border:none;
    border-radius:99px; font-size:.85rem; font-weight:800;
    padding:.65rem 1.5rem; cursor:pointer; font-family:inherit;
    display:inline-flex; align-items:center; gap:.5rem; transition:all .2s;
}
.cp-btn:hover { background:#bfe600; }
.cp-btn:disabled { opacity:.5; cursor:not-allowed; }

.cp-alert { border-radius:10px; padding:.75rem 1rem; font-size:.82rem; margin-bottom:1rem; display:flex; gap:.5rem; }
.cp-alert-error { background:rgba(255,77,77,.1); border:1px solid rgba(255,77,77,.3); color:#ff4d4d; }
.cp-alert-success { background:rgba(45,198,83,.1); border:1px solid rgba(45,198,83,.3); color:#2dc653; }
.cp-alert ul { margin:.3rem 0 0 1rem; padding:0; }
</style>

<?php if ($success): ?>
    <div class="cp-alert cp-alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="cp-alert cp-alert-error">
        <i class="fa-solid fa-circle-xmark" style="margin-top:.1rem;"></i>
        <div>
            <?php if (count($errors) === 1): echo htmlspecialchars($errors[0]);
            else: ?><ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul><?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="up-card" style="max-width:480px;">
    <div class="up-card-title"><i class="fa-regular fa-lock"></i> Update Password</div>
    <form action="change-password.php" method="POST" id="pwdForm" novalidate>
        <input type="hidden" name="action" value="change_pwd">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

        <div class="cp-field">
            <label class="cp-label">Current Password *</label>
            <div class="cp-wrap">
                <input class="cp-input" id="current_pwd" type="password" name="current_pwd" required>
                <button type="button" class="cp-toggle" data-target="current_pwd"><i class="fa-regular fa-eye"></i></button>
            </div>
        </div>

        <div class="cp-field">
            <label class="cp-label">New Password *</label>
            <div class="cp-wrap">
                <input class="cp-input" id="new_pwd" type="password" name="new_pwd" required minlength="8" maxlength="128">
                <button type="button" class="cp-toggle" data-target="new_pwd"><i class="fa-regular fa-eye"></i></button>
            </div>
            <div class="cp-strength"><div class="cp-strength-bar" id="strengthBar"></div></div>
            <div class="cp-rules">
                <div class="cp-rule" id="r-len"><i class="fa-regular fa-circle"></i> At least 8 characters</div>
                <div class="cp-rule" id="r-upper"><i class="fa-regular fa-circle"></i> One uppercase letter</div>
                <div class="cp-rule" id="r-num"><i class="fa-regular fa-circle"></i> One number</div>
            </div>
        </div>

        <div class="cp-field">
            <label class="cp-label">Confirm New Password *</label>
            <div class="cp-wrap">
                <input class="cp-input" id="new_pwd2" type="password" name="new_pwd2" required>
                <button type="button" class="cp-toggle" data-target="new_pwd2"><i class="fa-regular fa-eye"></i></button>
            </div>
            <span class="cp-hint" id="confirm-hint"></span>
        </div>

        <button class="cp-btn" type="submit" id="btnChange" disabled>
            <i class="fa-regular fa-lock"></i> Change Password
        </button>
    </form>
</div>

<script>
document.querySelectorAll('.cp-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var inp = document.getElementById(this.dataset.target);
        var icon = this.querySelector('i');
        inp.type = inp.type === 'password' ? 'text' : 'password';
        icon.className = inp.type === 'password' ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
    });
});

var newPwd  = document.getElementById('new_pwd');
var newPwd2 = document.getElementById('new_pwd2');
var btn     = document.getElementById('btnChange');

function setRule(id, pass) { document.getElementById(id).classList.toggle('pass', pass); }

function check() {
    var v = newPwd.value, v2 = newPwd2.value;
    var rLen = v.length >= 8, rUp = /[A-Z]/.test(v), rNum = /[0-9]/.test(v);
    var rMatch = v === v2 && v.length > 0;

    setRule('r-len', rLen); setRule('r-upper', rUp); setRule('r-num', rNum);

    var score = [rLen, rUp, rNum, v.length >= 12].filter(Boolean).length;
    var colors = ['','#ff4d4d','#f4d03f','#06b6d4','#2dc653'];
    var bar = document.getElementById('strengthBar');
    bar.style.width = (score*25) + '%';
    bar.style.background = colors[score] || '';

    var h = document.getElementById('confirm-hint');
    if (v2 && !rMatch) { h.className = 'cp-hint error'; h.textContent = 'Passwords do not match.'; }
    else if (v2) { h.className = 'cp-hint ok'; h.textContent = ''; }

    btn.disabled = !(rLen && rUp && rNum && rMatch && document.getElementById('current_pwd').value);
}

newPwd.addEventListener('input', check);
newPwd2.addEventListener('input', check);
document.getElementById('current_pwd').addEventListener('input', check);
</script>

<?php require_once('includes/footer.php'); ?>