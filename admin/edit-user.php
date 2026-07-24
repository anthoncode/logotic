<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Edit User';
require_once('../system/config-admin.php');

$ref = trim($_GET['id'] ?? '');
if ($ref === '') { header('Location: users.php'); exit; }

// Acepta ID numérico o username
if (ctype_digit($ref)) {
    $stmt = $DB_con->prepare("SELECT * FROM " . PFX . "users WHERE id = :ref");
} else {
    $stmt = $DB_con->prepare("SELECT * FROM " . PFX . "users WHERE username = :ref");
}
$stmt->execute([':ref' => $ref]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$u) { header('Location: users.php?msg=' . urlencode('User not found')); exit; }

$id = (int)$u['id']; // a partir de aquí siempre el ID numérico
// ── Eliminar usuario ──
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    // Los logos subidos pasan al sistema, no se borran
    $DB_con->prepare("UPDATE " . PFX . "products SET submit_user_id = 0 WHERE submit_user_id = :id")
           ->execute([':id' => $id]);
    // Descargas, favoritos, 2FA y OAuth se limpian solos por las FK CASCADE
    $DB_con->prepare("DELETE FROM " . PFX . "users WHERE id = :id")->execute([':id' => $id]);
    header('Location: users.php?msg=' . urlencode('User deleted'));
    exit;
}

// ── Estadísticas ──
$q = function($sql, $p) use ($DB_con) {
    $s = $DB_con->prepare($sql); $s->execute($p); return $s->fetchColumn();
};
$totalDownloads = $q("SELECT COUNT(*) FROM " . PFX . "downloads WHERE user_id = :id", [':id' => $id]);
$totalFavs      = $q("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE user_id = :id", [':id' => $id]);
$totalUploads   = $q("SELECT COUNT(*) FROM " . PFX . "products WHERE submit_user_id = :id", [':id' => $id]);
$totalViews     = $q("SELECT COALESCE(SUM(views),0) FROM " . PFX . "products WHERE submit_user_id = :id", [':id' => $id]);

// ¿Google vinculado?
$hasGoogle = $q("SELECT COUNT(*) FROM " . PFX . "oauth WHERE user_id = :id AND provider = 'google'", [':id' => $id]) > 0
             || !empty($u['google_id']);

// ¿Bloqueado?
$isLocked = !empty($u['locked_until']) && strtotime($u['locked_until']) > time();

$hasPhoto = !empty($u['profile']) && strpos($u['profile'], 'default') === false;

require_once('includes/header1.php');
?>

<style>
.eu-grid { display:grid; grid-template-columns:1fr 340px; gap:1rem; align-items:start; }
.eu-toggle-row { display:flex; align-items:center; justify-content:space-between; padding:.75rem 0; border-bottom:1px solid var(--adm-border); }
.eu-toggle-row:last-child { border-bottom:none; }
.eu-toggle-label { font-size:.83rem; color:var(--adm-text); }
.eu-toggle-label small { display:block; font-size:.7rem; color:var(--adm-muted); margin-top:.1rem; }
.eu-toggle-label.danger { color:var(--adm-danger); }
.eu-avatar { width:88px; height:88px; border-radius:50%; margin:0 auto .75rem; display:flex; align-items:center; justify-content:center; overflow:hidden; border:2px solid var(--adm-accent); }
.eu-avatar img { width:100%; height:100%; object-fit:cover; }
.eu-avatar-letter { background:rgba(212,255,0,.12); color:var(--adm-accent); font-size:2.2rem; font-weight:800; width:100%; height:100%; display:flex; align-items:center; justify-content:center; }
.eu-meta-row { display:flex; justify-content:space-between; font-size:.76rem; padding:.4rem 0; border-bottom:1px solid var(--adm-border); }
.eu-meta-row:last-child { border-bottom:none; }
.eu-meta-row span:first-child { color:var(--adm-muted); }
.eu-stats { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; margin-bottom:1rem; }
.eu-stat { background:rgba(255,255,255,.03); border:1px solid var(--adm-border); border-radius:10px; padding:.7rem; text-align:center; }
.eu-stat-num { font-size:1.2rem; font-weight:800; color:var(--adm-text); }
.eu-stat-label { font-size:.66rem; color:var(--adm-muted); text-transform:uppercase; letter-spacing:.05em; }
.eu-danger { border-color:rgba(255,77,77,.3) !important; }
</style>

<div class="adm-wrap">

    <div class="adm-page-header">
        <div class="adm-page-icon"><i class="fa-regular fa-user-pen" style="color:var(--adm-accent);"></i></div>
        <div>
            <h1 class="adm-page-title">Edit User</h1>
            <p class="adm-page-sub"><?php echo htmlspecialchars($u['fname']); ?> · @<?php echo htmlspecialchars($u['username']); ?></p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            <a href="<?php echo $setting['website_url']; ?>/profile/<?php echo htmlspecialchars($u['username']); ?>/" target="_blank" class="adm-topbar-btn">
                <i class="fa-regular fa-eye"></i> Profile
            </a>
            <a href="users.php" class="adm-topbar-btn"><i class="fa-regular fa-list"></i> All Users</a>
        </div>
    </div>

    <?php if ($isLocked): ?>
    <div class="adm-alert adm-alert-error" style="margin-bottom:1rem;">
        <i class="fa-solid fa-lock"></i>
        Account locked until <?php echo date('d M Y H:i', strtotime($u['locked_until'])); ?>
        (<?php echo (int)$u['login_attempts']; ?> failed attempts).
        <button class="adm-btn" style="margin-left:.75rem;" onclick="quickAction('unlock')">Unlock now</button>
    </div>
    <?php endif; ?>

    <div id="alertBox"></div>

    <div class="eu-grid">

        <!-- Columna principal -->
        <div>
            <form id="userForm">
                <input type="hidden" name="user_id" value="<?php echo $id; ?>">

                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-user"></i> Account Details</div>

                    <div class="adm-field">
                        <label class="adm-label">Full Name *</label>
                        <input class="adm-input" type="text" name="fname" maxlength="99" required
                               value="<?php echo htmlspecialchars($u['fname']); ?>">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="adm-field">
                            <label class="adm-label">Username *</label>
                            <input class="adm-input" type="text" name="username" maxlength="50" required
                                   value="<?php echo htmlspecialchars($u['username']); ?>">
                            <span style="font-size:.7rem;color:var(--adm-muted);margin-top:.3rem;display:block;">
                                Changing this breaks existing profile links
                            </span>
                        </div>
                        <div class="adm-field">
                            <label class="adm-label">Email *</label>
                            <input class="adm-input" type="email" name="email" maxlength="99" required
                                   value="<?php echo htmlspecialchars($u['email']); ?>">
                        </div>
                    </div>
                </div>

                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-lock"></i> Password</div>
                    <div class="adm-field" style="margin-bottom:.5rem;">
                        <label class="adm-label">Set New Password</label>
                        <div style="position:relative;">
                            <input class="adm-input" type="password" name="new_password" id="newPwd"
                                   placeholder="Leave empty to keep current password"
                                   maxlength="128" style="padding-right:2.5rem;" autocomplete="new-password">
                            <button type="button" onclick="togglePwd()"
                                    style="position:absolute;right:.7rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--adm-muted);cursor:pointer;">
                                <i class="fa-regular fa-eye" id="pwdIcon"></i>
                            </button>
                        </div>
                        <span style="font-size:.7rem;color:var(--adm-muted);margin-top:.3rem;display:block;">
                            Min 8 characters, one uppercase and one number. Setting a password also unlocks the account.
                        </span>
                    </div>

                    <div class="eu-toggle-row">
                        <div class="eu-toggle-label">Force password reset
                            <small>User must set a new password on next login</small>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" name="password_recover" <?php echo $u['password_recover'] == 1 ? 'checked' : ''; ?>>
                            <span class="adm-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-shield"></i> Permissions & Status</div>

                    <div class="eu-toggle-row">
                        <div class="eu-toggle-label">Account active
                            <small>Turn off to ban the user from signing in</small>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" name="active" <?php echo $u['active'] == 1 ? 'checked' : ''; ?>>
                            <span class="adm-slider"></span>
                        </label>
                    </div>

                    <div class="eu-toggle-row">
                        <div class="eu-toggle-label">Email verified
                            <small>Unverified users cannot sign in</small>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" name="verified" <?php echo $u['verified'] == 1 ? 'checked' : ''; ?>>
                            <span class="adm-slider"></span>
                        </label>
                    </div>

                    <div class="eu-toggle-row">
                        <div class="eu-toggle-label">Moderator
                            <small>Can approve logos and manage content</small>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" name="moderator" <?php echo $u['moderator'] == 1 ? 'checked' : ''; ?>>
                            <span class="adm-slider"></span>
                        </label>
                    </div>

                    <div class="eu-toggle-row">
                        <div class="eu-toggle-label">Two-factor authentication
                            <small>Email code required on every login</small>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" name="two_factor_enabled" <?php echo $u['two_factor_enabled'] == 1 ? 'checked' : ''; ?>>
                            <span class="adm-slider"></span>
                        </label>
                    </div>

                    <div class="eu-toggle-row">
                        <div class="eu-toggle-label">Newsletter
                            <small>Receives marketing emails</small>
                        </div>
                        <label class="adm-switch">
                            <input type="checkbox" name="allow_email" <?php echo $u['allow_email'] == 1 ? 'checked' : ''; ?>>
                            <span class="adm-slider"></span>
                        </label>
                    </div>
                </div>

                <button class="adm-save" type="submit" id="saveBtn" style="width:100%;justify-content:center;display:flex;">
                    <i class="fa-regular fa-floppy-disk"></i> <span>Save Changes</span>
                </button>
            </form>
        </div>

        <!-- Columna lateral -->
        <div>
            <div class="adm-card" style="margin-bottom:1rem;text-align:center;">
                <div class="eu-avatar">
                    <?php if ($hasPhoto): ?>
                        <img src="<?php echo htmlspecialchars($u['profile']); ?>" alt="">
                    <?php else: ?>
                        <div class="eu-avatar-letter"><?php echo strtoupper(mb_substr($u['fname'] ?: 'U', 0, 1)); ?></div>
                    <?php endif; ?>
                </div>
                <div style="font-weight:700;"><?php echo htmlspecialchars($u['fname']); ?></div>
                <div style="font-size:.76rem;color:var(--adm-muted);margin-bottom:.75rem;">@<?php echo htmlspecialchars($u['username']); ?></div>
                <?php if ($hasPhoto): ?>
                <button class="adm-btn" style="width:100%;justify-content:center;" onclick="quickAction('reset_photo')">
                    <i class="fa-regular fa-image-slash"></i> Reset photo
                </button>
                <?php endif; ?>
            </div>

            <div class="adm-card" style="margin-bottom:1rem;">
                <div class="adm-card-title"><i class="fa-regular fa-chart-simple"></i> Activity</div>
                <div class="eu-stats">
                    <div class="eu-stat">
                        <div class="eu-stat-num"><?php echo number_format($totalDownloads); ?></div>
                        <div class="eu-stat-label">Downloads</div>
                    </div>
                    <div class="eu-stat">
                        <div class="eu-stat-num"><?php echo number_format($totalFavs); ?></div>
                        <div class="eu-stat-label">Favorites</div>
                    </div>
                    <div class="eu-stat">
                        <div class="eu-stat-num"><?php echo number_format($totalUploads); ?></div>
                        <div class="eu-stat-label">Logos</div>
                    </div>
                    <div class="eu-stat">
                        <div class="eu-stat-num"><?php echo number_format($totalViews); ?></div>
                        <div class="eu-stat-label">Views</div>
                    </div>
                </div>

                <div class="eu-meta-row">
                    <span>Registered</span>
                    <span><?php echo $u['created'] ? date('d M Y', strtotime($u['created'])) : '—'; ?></span>
                </div>
                <div class="eu-meta-row">
                    <span>Last login</span>
                    <span><?php echo $u['last_login'] ? date('d M Y H:i', strtotime($u['last_login'])) : 'Never'; ?></span>
                </div>
                <div class="eu-meta-row">
                    <span>Last IP</span>
                    <span style="font-family:monospace;font-size:.72rem;"><?php echo htmlspecialchars($u['ip_address'] ?: '—'); ?></span>
                </div>
                <div class="eu-meta-row">
                    <span>Failed attempts</span>
                    <span><?php echo (int)$u['login_attempts']; ?></span>
                </div>
                <div class="eu-meta-row">
                    <span>User ID</span>
                    <span>#<?php echo $id; ?></span>
                </div>
            </div>

            <div class="adm-card" style="margin-bottom:1rem;">
                <div class="adm-card-title"><i class="fa-brands fa-google"></i> Linked Accounts</div>
                <?php if ($hasGoogle): ?>
                    <div style="font-size:.8rem;color:var(--adm-success);margin-bottom:.75rem;">
                        <i class="fa-solid fa-circle-check"></i> Google account linked
                    </div>
                    <button class="adm-btn adm-btn-del" style="width:100%;justify-content:center;"
                            onclick="if(confirm('Unlink Google? The user will need a password to sign in.')) quickAction('unlink_google')">
                        <i class="fa-regular fa-link-slash"></i> Unlink Google
                    </button>
                <?php else: ?>
                    <div style="font-size:.8rem;color:var(--adm-muted);">No linked accounts</div>
                <?php endif; ?>
            </div>

            <div class="adm-card eu-danger">
                <div class="adm-card-title" style="color:var(--adm-danger);">
                    <i class="fa-regular fa-triangle-exclamation" style="color:var(--adm-danger);"></i> Danger Zone
                </div>
                <p style="font-size:.75rem;color:var(--adm-muted);margin-bottom:.75rem;">
                    Deleting removes downloads, favorites and 2FA codes.
                    <?php if ($totalUploads > 0): ?>
                        Their <?php echo $totalUploads; ?> logo(s) will be kept and reassigned to the site.
                    <?php endif; ?>
                </p>
                <a href="?id=<?php echo $id; ?>&action=delete" class="adm-btn adm-btn-del"
                   style="width:100%;justify-content:center;"
                   onclick="return confirm('Delete this user permanently? This cannot be undone.')">
                    <i class="fa-regular fa-trash"></i> Delete user
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePwd() {
    var i = document.getElementById('newPwd'), ic = document.getElementById('pwdIcon');
    i.type = i.type === 'password' ? 'text' : 'password';
    ic.className = i.type === 'password' ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
}

function showAlert(type, msg) {
    var cls  = type === 'success' ? 'adm-alert-success' : 'adm-alert-error';
    var icon = type === 'success' ? 'circle-check' : 'circle-xmark';
    document.getElementById('alertBox').innerHTML =
        '<div class="adm-alert ' + cls + '" style="margin-bottom:1rem;"><i class="fa-solid fa-' + icon + '"></i> ' + msg + '</div>';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function quickAction(action) {
    var fd = new FormData();
    fd.append('user_id', '<?php echo $id; ?>');
    fd.append('action', action);
    fetch('<?php echo $setting['website_url']; ?>/admin/ajax-save-user.php', {
        method: 'POST', credentials: 'same-origin', body: fd
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        if (d.success) { showAlert('success', d.message); setTimeout(function(){ location.reload(); }, 900); }
        else { showAlert('error', d.message); }
    });
}

$('#userForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $('#saveBtn');
    btn.prop('disabled', true).find('span').text('Saving...');

    fetch('<?php echo $setting['website_url']; ?>/admin/ajax-save-user.php', {
        method: 'POST', credentials: 'same-origin', body: new FormData(this)
    })
    .then(function(r){ return r.json(); })
    .then(function(d){
        btn.prop('disabled', false).find('span').text('Save Changes');
        showAlert(d.success ? 'success' : 'error', d.message);
        if (d.success) document.getElementById('newPwd').value = '';
    })
    .catch(function(){
        btn.prop('disabled', false).find('span').text('Save Changes');
        showAlert('error', 'Connection error.');
    });
});
</script>

<?php require_once('includes/footer.php'); ?>