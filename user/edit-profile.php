<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Edit Profile';
$pg = '6';

require_once('../system/config-user.php');

$uid    = $crypt->decrypt($_SESSION['uid'], 'USER');
$errors = [];
$success = '';

$csrfToken = $user->generateCsrfToken();

// ── Actualizar foto de perfil ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'photo') {
    if (!$user->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid.';
    } elseif (empty($_FILES['profile']['name'])) {
        $errors[] = 'Please choose an image.';
    } else {
        $ext     = strtolower(pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        $maxSize = 2 * 1024 * 1024;
        if (!in_array($ext, $allowed)) {
            $errors[] = 'Only JPG, PNG or WebP allowed.';
        } elseif ($_FILES['profile']['size'] > $maxSize) {
            $errors[] = 'Image must be 2MB or less.';
        } else {
            if ($user->change_profile_image($_SESSION['uid'], $_FILES['profile']['tmp_name'], $ext)) {
                $success = 'Profile photo updated.';
                $userDetails = $user->details($_SESSION['uid']);
            } else {
                $errors[] = 'Could not upload photo. Try again.';
            }
        }
    }
}

// ── Actualizar perfil ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'profile') {
    if (!$user->validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Security token invalid.';
    } else {
        $fname      = htmlspecialchars(strip_tags(trim($_POST['fname'] ?? '')));
        $email      = strtolower(trim($_POST['email'] ?? ''));
        $newsletter = isset($_POST['allow_email']) ? 1 : 0;

        if (empty($fname)) {
            $errors[] = 'Name is required.';
        } elseif (mb_strlen($fname) < 2) {
            $errors[] = 'Name must be at least 2 characters.';
        } elseif (mb_strlen($fname) > 99) {
            $errors[] = 'Name cannot exceed 99 characters.';
        } elseif (!preg_match('/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s\-\']+$/u', $fname)) {
            $errors[] = 'Name contains invalid characters.';
        }

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        } else {
            // Unicidad
            $chk = $DB_con->prepare("SELECT id FROM " . PFX . "users WHERE email = :email AND id != :id");
            $chk->execute([':email' => $email, ':id' => $uid]);
            if ($chk->fetchColumn()) {
                $errors[] = 'That email is already in use.';
            }
        }

        if (empty($errors)) {
            $user->update($_SESSION['uid'], 'fname', $fname);
            $user->update($_SESSION['uid'], 'email', $email);
            $user->update($_SESSION['uid'], 'allow_email', $newsletter);
            $success = 'Profile updated successfully.';
            $userDetails = $user->details($_SESSION['uid']);
        }
    }
}

require_once('includes/header.php');
?>

<div class="user-panel-head">
    <h1>Edit Profile</h1>
    <p>Update your personal information and profile photo</p>
</div>

<style>
.ep-field { display:flex; flex-direction:column; gap:.4rem; margin-bottom:1.1rem; }
.ep-label { font-size:.8rem; font-weight:600; color:var(--text-muted); }
.ep-input {
    width:100%; background:rgba(255,255,255,.04);
    border:1px solid var(--border); border-radius:10px;
    color:var(--text-primary); font-size:.88rem;
    padding:.65rem .9rem; outline:none; font-family:inherit;
    transition:border-color .2s;
}
.ep-input:focus { border-color:var(--accent); }
.ep-input:disabled { opacity:.5; cursor:not-allowed; }
.ep-input.is-error { border-color:#ff4d4d; }
.ep-input.is-ok { border-color:#2dc653; }
.ep-hint { font-size:.7rem; min-height:.9rem; }
.ep-hint.error { color:#ff4d4d; }
.ep-hint.ok { color:#2dc653; }

.ep-btn {
    background:var(--accent); color:#0d0f1c;
    border:none; border-radius:99px;
    font-size:.85rem; font-weight:800;
    padding:.65rem 1.5rem; cursor:pointer;
    font-family:inherit; transition:all .2s;
    display:inline-flex; align-items:center; gap:.5rem;
}
.ep-btn:hover { background:#bfe600; }

.ep-photo-row { display:flex; align-items:center; gap:1.25rem; margin-bottom:1rem; }
.ep-photo-current {
    width:80px; height:80px; border-radius:50%;
    object-fit:cover; border:2px solid var(--accent);
}
.ep-photo-letter {
    width:80px; height:80px; border-radius:50%;
    background:rgba(212,255,0,.12); border:2px solid var(--accent);
    display:flex; align-items:center; justify-content:center;
    font-size:2rem; font-weight:800; color:var(--accent);
}
.ep-file-label {
    display:inline-flex; align-items:center; gap:.5rem;
    background:rgba(255,255,255,.05); border:1px solid var(--border);
    border-radius:10px; padding:.55rem 1rem;
    font-size:.82rem; color:var(--text-secondary);
    cursor:pointer; transition:all .2s;
}
.ep-file-label:hover { border-color:var(--accent); color:var(--text-primary); }
.ep-file-label input { display:none; }

.ep-check-row { display:flex; align-items:center; gap:.6rem; margin:1rem 0; }
.ep-check-row input { accent-color:var(--accent); }
.ep-check-row label { font-size:.82rem; color:var(--text-muted); cursor:pointer; }

.ep-alert {
    border-radius:10px; padding:.75rem 1rem;
    font-size:.82rem; margin-bottom:1rem;
    display:flex; align-items:flex-start; gap:.5rem;
}
.ep-alert-error { background:rgba(255,77,77,.1); border:1px solid rgba(255,77,77,.3); color:#ff4d4d; }
.ep-alert-success { background:rgba(45,198,83,.1); border:1px solid rgba(45,198,83,.3); color:#2dc653; }
.ep-alert ul { margin:.3rem 0 0 1rem; padding:0; }
</style>

<?php if ($success): ?>
    <div class="ep-alert ep-alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="ep-alert ep-alert-error">
        <i class="fa-solid fa-circle-xmark" style="margin-top:.1rem;"></i>
        <div>
            <?php if (count($errors) === 1): echo htmlspecialchars($errors[0]);
            else: ?><ul><?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul><?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Foto de perfil -->
<div class="up-card" style="margin-bottom:1rem;">
    <div class="up-card-title"><i class="fa-regular fa-image"></i> Profile Photo</div>
    <form action="edit-profile.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="photo">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
        <div class="ep-photo-row">
            <?php if (!empty($userDetails['profile']) && $userDetails['profile'] !== '../system/assets/uploads/user-img/default.png'): ?>
                <img class="ep-photo-current" src="<?php echo $userDetails['profile']; ?>" alt="Profile">
            <?php else: ?>
                <div class="ep-photo-letter"><?php echo strtoupper(mb_substr($userDetails['fname'] ?? 'U', 0, 1)); ?></div>
            <?php endif; ?>
            <div>
                <label class="ep-file-label">
                    <i class="fa-regular fa-upload"></i> <span id="fileLabel">Choose image</span>
                    <input type="file" name="profile" accept=".jpg,.jpeg,.png,.webp"
                           onchange="document.getElementById('fileLabel').textContent = this.files[0]?.name || 'Choose image'">
                </label>
                <div style="font-size:.7rem;color:var(--text-muted);margin-top:.4rem;">JPG, PNG or WebP · max 2MB</div>
            </div>
        </div>
        <button class="ep-btn" type="submit"><i class="fa-regular fa-floppy-disk"></i> Upload Photo</button>
    </form>
</div>

<!-- Datos del perfil -->
<div class="up-card">
    <div class="up-card-title"><i class="fa-regular fa-user"></i> Personal Information</div>
    <form action="edit-profile.php" method="POST" id="profileForm" novalidate>
        <input type="hidden" name="action" value="profile">
        <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">

        <div class="ep-field">
            <label class="ep-label">Full Name *</label>
            <input class="ep-input" id="fname" type="text" name="fname"
                   value="<?php echo htmlspecialchars($userDetails['fname']); ?>"
                   maxlength="99" required>
            <span class="ep-hint" id="fname-hint"></span>
        </div>

        <div class="ep-field">
            <label class="ep-label">Email Address *</label>
            <input class="ep-input" id="email" type="email" name="email"
                   value="<?php echo htmlspecialchars($userDetails['email']); ?>"
                   maxlength="150" required>
            <span class="ep-hint" id="email-hint"></span>
        </div>

        <div class="ep-field">
            <label class="ep-label">Username</label>
            <input class="ep-input" type="text" value="<?php echo htmlspecialchars($userDetails['username']); ?>" disabled>
            <span class="ep-hint" style="color:var(--text-muted);">Username cannot be changed</span>
        </div>

        <div class="ep-check-row">
            <input type="checkbox" id="allow_email" name="allow_email" value="1"
                   <?php echo $userDetails['allow_email'] == 1 ? 'checked' : ''; ?>>
            <label for="allow_email">Receive newsletter and product updates</label>
        </div>

        <button class="ep-btn" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save Changes</button>
    </form>
</div>

<script>
function setState(input, hint, state, msg) {
    input.classList.remove('is-error','is-ok');
    hint.classList.remove('error','ok');
    if (state) { input.classList.add(state === 'error' ? 'is-error' : 'is-ok'); hint.classList.add(state); }
    hint.textContent = msg;
}

var fname = document.getElementById('fname');
var email = document.getElementById('email');

fname.addEventListener('input', function() {
    var v = this.value.trim(), h = document.getElementById('fname-hint');
    if (!v) return setState(this, h, 'error', 'Name is required.');
    if (v.length < 2) return setState(this, h, 'error', 'At least 2 characters.');
    setState(this, h, 'ok', '');
});

email.addEventListener('input', function() {
    var v = this.value.trim(), h = document.getElementById('email-hint');
    if (!v) return setState(this, h, 'error', 'Email is required.');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return setState(this, h, 'error', 'Invalid email.');
    setState(this, h, 'ok', '');
});

document.getElementById('profileForm').addEventListener('submit', function(e) {
    fname.dispatchEvent(new Event('input'));
    email.dispatchEvent(new Event('input'));
    if (document.querySelector('.ep-input.is-error')) {
        e.preventDefault();
        document.querySelector('.ep-input.is-error').focus();
    }
});
</script>

<?php require_once('includes/footer.php'); ?>