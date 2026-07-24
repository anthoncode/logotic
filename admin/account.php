<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'My Account';
require_once('../system/config-admin.php');

$details = $auth->details($_SESSION['uid']);
$errors  = [];
$success = '';

// ── SERVER VALIDATION & PROCESSING ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Actualizar perfil ──
    if (isset($_POST['account']) && $_POST['account'] === 'profile') {

        // Sanitización
        $fname = htmlspecialchars(strip_tags(trim($_POST['fname'] ?? '')));
        $email = strtolower(trim($_POST['email'] ?? ''));

        // Validaciones
        if (empty($fname)) {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($fname) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        } elseif (mb_strlen($fname) > 100) {
            $errors[] = 'Name cannot exceed 100 characters.';
        } elseif (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-\']+$/u', $fname)) {
            $errors[] = 'Name contains invalid characters.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } elseif (mb_strlen($email) > 150) {
            $errors[] = 'Email is too long.';
        } else {
            // Verificar unicidad — que no exista ese email en otro admin
            $stmt = $DB_con->prepare("SELECT id FROM " . PFX . "admin WHERE email = :email AND id != :id");
            $stmt->execute([':email' => $email, ':id' => $_SESSION['uid']]);
            if ($stmt->fetchColumn()) {
                $errors[] = 'This email is already in use by another account.';
            }
        }

        if (empty($errors)) {
            $auth->update($_SESSION['uid'], 'email', $email);
            $auth->update($_SESSION['uid'], 'fname', $fname);
            $success = 'profile';
            $details = $auth->details($_SESSION['uid']); // refrescar
        }
    }

    // ── Cambiar contraseña ──
    if (isset($_POST['account']) && $_POST['account'] === 'cpassword') {

        $currentPwd = $_POST['edit_pwd']      ?? '';
        $newPwd     = $_POST['edit_new_pwd']  ?? '';
        $newPwd2    = $_POST['edit_new_pwd2'] ?? '';

        // Validaciones
        if (empty($currentPwd)) {
            $errors[] = 'Current password is required.';
        }
        if (empty($newPwd)) {
            $errors[] = 'New password is required.';
        } elseif (mb_strlen($newPwd) < 8) {
            $errors[] = 'New password must be at least 8 characters.';
        } elseif (mb_strlen($newPwd) > 128) {
            $errors[] = 'Password is too long (max 128 characters).';
        } elseif (!preg_match('/[A-Z]/', $newPwd)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        } elseif (!preg_match('/[0-9]/', $newPwd)) {
            $errors[] = 'Password must contain at least one number.';
        }
        if ($newPwd !== $newPwd2) {
            $errors[] = 'New passwords do not match.';
        }

        if (empty($errors)) {
            // Verificar contraseña actual — solo un md5, no doble
            if (md5($currentPwd) !== $details['password']) {
                $errors[] = 'Current password is incorrect.';
            } else {
                $hashedNew = md5($newPwd);
                $auth->update($_SESSION['uid'], 'password', $hashedNew);
                $success = 'password';
            }
        }
    }
}

require_once('includes/header1.php');
?>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-circle-user" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">My Account</h1>
            <p class="adm-page-sub">Manage your admin profile and security settings</p>
        </div>
        <!-- Avatar inicial -->
        <div style="margin-left:auto;">
            <div style="
                width: 44px; height: 44px; border-radius: 50%;
                background: rgba(212,255,0,.15); border: 2px solid rgba(212,255,0,.3);
                display: flex; align-items: center; justify-content: center;
                font-size: 1.1rem; font-weight: 800; color: var(--adm-accent);
            ">
                <?php echo strtoupper(mb_substr($details['fname'] ?? 'A', 0, 1)); ?>
            </div>
        </div>
    </div>

    <!-- Alertas globales de éxito -->
    <?php if ($success === 'profile'): ?>
        <div class="adm-alert adm-alert-success" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-check"></i> Profile updated successfully.
        </div>
    <?php elseif ($success === 'password'): ?>
        <div class="adm-alert adm-alert-success" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-check"></i> Password changed successfully.
        </div>
    <?php endif; ?>

    <!-- Errores del servidor -->
    <?php if (!empty($errors)): ?>
        <div class="adm-alert adm-alert-error" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-xmark"></i>
            <ul style="margin:.3rem 0 0 1rem;padding:0;">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo $e; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:start;">

        <!-- ── PERFIL ── -->
        <div class="adm-card">
            <div class="adm-card-title">
                <i class="fa-regular fa-user"></i> Profile Information
            </div>

            <form action="account.php" method="POST" id="formProfile" novalidate>
                <input type="hidden" name="account" value="profile">

                <div class="adm-field">
                    <label class="adm-label">Full Name *</label>
                    <input class="adm-input" id="fname" type="text" name="fname"
                           value="<?php echo htmlspecialchars($details['fname'] ?? ''); ?>"
                           placeholder="Your name"
                           maxlength="100" required
                           autocomplete="name">
                    <span class="field-hint" id="fname-hint"></span>
                </div>

                <div class="adm-field">
                    <label class="adm-label">Email Address *</label>
                    <input class="adm-input" id="email" type="email" name="email"
                           value="<?php echo htmlspecialchars($details['email'] ?? ''); ?>"
                           placeholder="admin@example.com"
                           maxlength="150" required
                           autocomplete="email">
                    <span class="field-hint" id="email-hint"></span>
                </div>

                <div style="display:flex;align-items:center;gap:.75rem;margin-top:1.25rem;">
                    <button class="adm-save" type="submit" style="margin-top:0;">
                        <i class="fa-regular fa-floppy-disk"></i> Save Profile
                    </button>
                    <span style="font-size:.75rem;color:var(--adm-muted);">
                        Last update: <?php echo date('d M Y'); ?>
                    </span>
                </div>
            </form>
        </div>

        <!-- ── CONTRASEÑA ── -->
        <div class="adm-card">
            <div class="adm-card-title">
                <i class="fa-regular fa-lock"></i> Change Password
            </div>

            <form action="account.php" method="POST" id="formPassword" novalidate>
                <input type="hidden" name="account" value="cpassword">

                <div class="adm-field">
                    <label class="adm-label">Current Password *</label>
                    <div style="position:relative;">
                        <input class="adm-input" id="edit_pwd" type="password" name="edit_pwd"
                               placeholder="Enter current password" required
                               autocomplete="current-password"
                               style="padding-right:2.5rem;">
                        <button type="button" class="pwd-toggle" data-target="edit_pwd" title="Show/hide">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <span class="field-hint" id="pwd-hint"></span>
                </div>

                <div class="adm-field">
                    <label class="adm-label">New Password *</label>
                    <div style="position:relative;">
                        <input class="adm-input" id="edit_new_pwd" type="password" name="edit_new_pwd"
                               placeholder="Min 8 chars, 1 uppercase, 1 number" required
                               minlength="8" maxlength="128"
                               autocomplete="new-password"
                               style="padding-right:2.5rem;">
                        <button type="button" class="pwd-toggle" data-target="edit_new_pwd" title="Show/hide">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <!-- Strength bar -->
                    <div style="margin-top:.4rem;">
                        <div style="height:3px;background:var(--adm-border);border-radius:99px;overflow:hidden;">
                            <div id="strength-bar" style="height:100%;width:0;border-radius:99px;transition:all .3s;"></div>
                        </div>
                        <span id="strength-label" style="font-size:.7rem;color:var(--adm-muted);"></span>
                    </div>
                    <span class="field-hint" id="newpwd-hint"></span>
                </div>

                <div class="adm-field">
                    <label class="adm-label">Confirm New Password *</label>
                    <div style="position:relative;">
                        <input class="adm-input" id="edit_new_pwd2" type="password" name="edit_new_pwd2"
                               placeholder="Repeat new password" required
                               autocomplete="new-password"
                               style="padding-right:2.5rem;">
                        <button type="button" class="pwd-toggle" data-target="edit_new_pwd2" title="Show/hide">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                    <span class="field-hint" id="confirm-hint"></span>
                </div>

                <!-- Reglas de contraseña -->
                <div style="background:rgba(255,255,255,.03);border:1px solid var(--adm-border);border-radius:8px;padding:.75rem;margin-bottom:.5rem;">
                    <div style="font-size:.72rem;color:var(--adm-muted);margin-bottom:.4rem;font-weight:600;">Password requirements:</div>
                    <div class="pwd-rule" id="rule-len"><i class="fa-regular fa-circle"></i> At least 8 characters</div>
                    <div class="pwd-rule" id="rule-upper"><i class="fa-regular fa-circle"></i> One uppercase letter</div>
                    <div class="pwd-rule" id="rule-num"><i class="fa-regular fa-circle"></i> One number</div>
                    <div class="pwd-rule" id="rule-match"><i class="fa-regular fa-circle"></i> Passwords match</div>
                </div>

                <button class="adm-save" type="submit" id="btnChangePwd" style="margin-top:.75rem;" disabled>
                    <i class="fa-regular fa-lock"></i> Change Password
                </button>
            </form>
        </div>

    </div>
</div>


<script>
// ── Mostrar/ocultar contraseña ──
document.querySelectorAll('.pwd-toggle').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var input = document.getElementById(this.dataset.target);
        var icon  = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye';
        }
    });
});

// ── CLIENT VALIDATION: Perfil ──
var fname = document.getElementById('fname');
var email = document.getElementById('email');

fname.addEventListener('input', function() {
    var v = this.value.trim();
    var hint = document.getElementById('fname-hint');
    if (!v) {
        setFieldState(this, hint, 'error', 'Name is required.');
    } else if (v.length < 2) {
        setFieldState(this, hint, 'error', 'At least 2 characters.');
    } else if (v.length > 100) {
        setFieldState(this, hint, 'error', 'Max 100 characters.');
    } else {
        setFieldState(this, hint, 'ok', '');
    }
});

email.addEventListener('input', function() {
    var v = this.value.trim();
    var hint = document.getElementById('email-hint');
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!v) {
        setFieldState(this, hint, 'error', 'Email is required.');
    } else if (!emailRegex.test(v)) {
        setFieldState(this, hint, 'error', 'Invalid email format.');
    } else {
        setFieldState(this, hint, 'ok', '');
    }
});

document.getElementById('formProfile').addEventListener('submit', function(e) {
    var fnameVal  = fname.value.trim();
    var emailVal  = email.value.trim();
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var valid = true;

    if (!fnameVal || fnameVal.length < 2 || fnameVal.length > 100) valid = false;
    if (!emailVal || !emailRegex.test(emailVal)) valid = false;

    if (!valid) {
        e.preventDefault();
        fname.dispatchEvent(new Event('input'));
        email.dispatchEvent(new Event('input'));
    }
});

// ── CLIENT VALIDATION: Contraseña ──
var newPwd    = document.getElementById('edit_new_pwd');
var newPwd2   = document.getElementById('edit_new_pwd2');
var submitBtn = document.getElementById('btnChangePwd');

function checkPasswordRules() {
    var val  = newPwd.value;
    var val2 = newPwd2.value;

    var ruleLen   = val.length >= 8;
    var ruleUpper = /[A-Z]/.test(val);
    var ruleNum   = /[0-9]/.test(val);
    var ruleMatch = val === val2 && val.length > 0;

    setRule('rule-len',   ruleLen);
    setRule('rule-upper', ruleUpper);
    setRule('rule-num',   ruleNum);
    setRule('rule-match', ruleMatch);

    // Strength bar
    var score = [ruleLen, ruleUpper, ruleNum, val.length >= 12].filter(Boolean).length;
    var bar   = document.getElementById('strength-bar');
    var label = document.getElementById('strength-label');
    var colors = ['', '#ff4d4d', '#f4d03f', '#06b6d4', '#2dc653'];
    var labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    bar.style.width  = (score * 25) + '%';
    bar.style.background = colors[score] || '';
    label.textContent    = labels[score] || '';

    var allPass = ruleLen && ruleUpper && ruleNum && ruleMatch;
    submitBtn.disabled = !allPass;

    return allPass;
}

newPwd.addEventListener('input', checkPasswordRules);
newPwd2.addEventListener('input', function() {
    checkPasswordRules();
    var hint = document.getElementById('confirm-hint');
    if (newPwd2.value && newPwd.value !== newPwd2.value) {
        setFieldState(newPwd2, hint, 'error', 'Passwords do not match.');
    } else if (newPwd2.value) {
        setFieldState(newPwd2, hint, 'ok', '');
    }
});

document.getElementById('formPassword').addEventListener('submit', function(e) {
    if (!checkPasswordRules()) {
        e.preventDefault();
    }
    var pwd = document.getElementById('edit_pwd').value;
    if (!pwd) {
        e.preventDefault();
        var hint = document.getElementById('pwd-hint');
        setFieldState(document.getElementById('edit_pwd'), hint, 'error', 'Current password is required.');
    }
});

// ── Helpers ──
function setFieldState(input, hint, state, msg) {
    input.classList.remove('is-error', 'is-ok');
    hint.classList.remove('error', 'ok');
    if (state === 'error') {
        input.classList.add('is-error');
        hint.classList.add('error');
        hint.textContent = msg;
    } else {
        input.classList.add('is-ok');
        hint.classList.add('ok');
        hint.textContent = msg;
    }
}

function setRule(id, pass) {
    var el = document.getElementById(id);
    el.classList.toggle('pass', pass);
}
</script>

<?php require_once('includes/footer.php'); ?>