<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Settings';
require_once('../system/config-admin.php');
require_once('../system/gateways.php');

$setting    = $settings->get_all();
$activeTab  = $_REQUEST['tab'] ?? 'general';

if (isset($_REQUEST['save'])) {
  switch ($_REQUEST['save']) {

    case 'general':
      $newsettings = [
        'website_url'   => trim($_POST['website_url']        ?? ''),
        'site_name'     => trim($_POST['site_name']          ?? ''),
        'description'   => trim($_POST['description']        ?? ''),
        'footer_desc'   => trim($_POST['footer_description'] ?? ''),
        'keywords'      => trim($_POST['keywords']           ?? ''),
        'author'        => trim($_POST['author']             ?? ''),
        'support_email' => trim($_POST['support_email']      ?? ''),
        'mail_admin'    => trim($_POST['mail_admin']         ?? ''),
      ];
      if (empty($newsettings['website_url'])) {
        $settings->error = 'Website URL is required';
        break;
      }
      if (empty($newsettings['site_name'])) {
        $settings->error = 'Site Name is required';
        break;
      }
      if (!filter_var($newsettings['support_email'], FILTER_VALIDATE_EMAIL)) {
        $settings->error = 'Invalid support email';
        break;
      }
      if (!filter_var($newsettings['mail_admin'],    FILTER_VALIDATE_EMAIL)) {
        $settings->error = 'Invalid admin email';
        break;
      }
      $settings->update($newsettings);
      $activeTab = 'general';
      break;

    case 'homepage':
      $newsettings = [
        'homepage_header'    => trim($_POST['homepage_header']    ?? ''),
        'homepage_subheader' => trim($_POST['homepage_subheader'] ?? ''),
        'global_message'     => trim($_POST['global_message']     ?? ''),
        'alert_type'         => trim($_POST['alert_type']         ?? 'info'),
        'notification_msg'   => trim($_POST['notification_msg']   ?? ''),
        'url_msg'            => trim($_POST['url_msg']            ?? ''),
      ];
      if (empty($newsettings['homepage_header'])) {
        $settings->error = 'Homepage header is required';
        break;
      }
      if (empty($newsettings['homepage_subheader'])) {
        $settings->error = 'Homepage subheader is required';
        break;
      }
      $settings->update($newsettings);
      $activeTab = 'homepage';
      break;

    case 'security':
    $newsettings = [
        'site_key_captcha'    => trim($_POST['site_key_captcha']    ?? ''),
        'secret_key_captcha'  => trim($_POST['secret_key_captcha']  ?? ''),
        'google_client_id'    => trim($_POST['google_client_id']    ?? ''),
        'google_client_secret'=> trim($_POST['google_client_secret']?? ''),
        'google_oauth_enabled'=> isset($_POST['google_oauth_enabled']) ? '1' : '0',
    ];
    $settings->update($newsettings);
    $activeTab = 'security';
    break;

    case 'email':
      $smtpHost  = trim($_POST['smtp_host']       ?? '');
      $smtpUser  = trim($_POST['smtp_user']        ?? '');
      $smtpFrom  = trim($_POST['smtp_from_email']  ?? '');
      $smtpPort  = (int)($_POST['smtp_port']        ?? 587);
      $smtpEnc   = in_array($_POST['smtp_encryption'] ?? '', ['tls', 'ssl', 'none'])
        ? $_POST['smtp_encryption'] : 'tls';

      // Validaciones
      if ($smtpFrom && !filter_var($smtpFrom, FILTER_VALIDATE_EMAIL)) {
        $settings->error = 'Invalid From email address.';
        $activeTab = 'email';
        break;
      }
      if ($smtpPort < 1 || $smtpPort > 65535) {
        $settings->error = 'Invalid SMTP port.';
        $activeTab = 'email';
        break;
      }

      $newsettings = [
        'smtp_enabled'       => isset($_POST['smtp_enabled'])       ? '1' : '0',
        'smtp_host'          => $smtpHost,
        'smtp_port'          => $smtpPort,
        'smtp_user'          => $smtpUser,
        'smtp_pass'          => !empty($_POST['smtp_pass']) ? $_POST['smtp_pass'] : ($setting['smtp_pass'] ?? ''),
        'smtp_from_name'     => htmlspecialchars(strip_tags(trim($_POST['smtp_from_name'] ?? ''))),
        'smtp_from_email'    => $smtpFrom,
        'smtp_encryption'    => $smtpEnc,
        'email_verification' => isset($_POST['email_verification']) ? '1' : '0',
      ];
      $settings->update($newsettings);
      $activeTab = 'email';
      break;

    case 'appearance':
      $newsettings = [
        'bg_color' => trim($_POST['bg_color'] ?? '#0d0f1c'),
      ];
      if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $newsettings['bg_color'])) {
        $settings->error = 'Invalid color format';
        break;
      }
      $settings->update($newsettings);
      $activeTab = 'appearance';
      break;

    case 'features':
      //($_POST); // ← debug temporal
      $newsettings = [
        'login'               => isset($_POST['login'])               ? 1 : 0,
        'captcha'             => isset($_POST['captcha'])             ? 1 : 0,
        'show_ads'            => isset($_POST['show_ads'])            ? 1 : 0,
        'wishlist'            => isset($_POST['wishlist'])            ? 1 : 0,
        'notification_header' => isset($_POST['notification_header']) ? 1 : 0,
        'show_card_sde'       => isset($_POST['show_card_sde'])       ? 1 : 0,
      ];
      $settings->update($newsettings);
      $activeTab = 'features';
      break;

    case 'ads':
      $newsettings = [
        'ads_1'       => $_POST['ads_1']       ?? '',
        'ads_2'       => $_POST['ads_2']       ?? '',
        'code_header' => $_POST['code_header'] ?? '',
      ];
      $settings->update($newsettings);
      $activeTab = 'ads';
      break;

      case 'downloads':
      $guestLimit  = (int)($_POST['dl_guest_limit']  ?? 10);
      $guestPeriod = (int)($_POST['dl_guest_period'] ?? 24);
      $rateMax     = (int)($_POST['dl_rate_max']     ?? 8);

      // Rangos sensatos
      if ($guestLimit < 0)   $guestLimit = 0;
      if ($guestLimit > 500) $guestLimit = 500;
      if ($guestPeriod < 1)  $guestPeriod = 1;
      if ($rateMax < 1)      $rateMax = 1;
      if ($rateMax > 100)    $rateMax = 100;

      $newsettings = [
        'dl_limit_enabled' => isset($_POST['dl_limit_enabled']) ? '1' : '0',
        'dl_guest_limit'   => (string)$guestLimit,
        'dl_guest_period'  => (string)$guestPeriod,
        'dl_rate_max'      => (string)$rateMax,
      ];
      $settings->update($newsettings);
      $activeTab = 'downloads';
      break;
  }

  $setting = $settings->get_all();
}

if (!empty($settings->msg))   $success = $settings->msg;
if (!empty($settings->error)) $error   = $settings->error;

require_once('includes/header1.php');
?>



<div class="adm-wrap">

  <div class="adm-page-header">
    <div class="adm-page-icon">
      <i class="fa-regular fa-gear" style="color:var(--adm-accent);"></i>
    </div>
    <div>
      <h1 class="adm-page-title">Settings</h1>
      <p class="adm-page-sub">Manage your site configuration and preferences</p>
    </div>
  </div>

  <?php if (isset($success)): ?>
    <div class="adm-alert adm-alert-success"><i class="fa-solid fa-circle-check"></i> <?php echo $success; ?></div>
  <?php endif; ?>
  <?php if (isset($error)): ?>
    <div class="adm-alert adm-alert-error"><i class="fa-solid fa-circle-xmark"></i> <?php echo $error; ?></div>
  <?php endif; ?>

  <!-- Tabs — links <a> para mantener tab activo tras guardar -->
  <!-- Tabs -->
  <div class="adm-tabs">
    <a class="adm-tab <?php echo $activeTab === 'general'    ? 'active' : '' ?>" href="?tab=general"><i class="fa-regular fa-globe"></i> General</a>
    <a class="adm-tab <?php echo $activeTab === 'homepage'   ? 'active' : '' ?>" href="?tab=homepage"><i class="fa-regular fa-house"></i> Homepage</a>
    <a class="adm-tab <?php echo $activeTab === 'security'   ? 'active' : '' ?>" href="?tab=security"><i class="fa-regular fa-shield"></i> Security</a>
    <a class="adm-tab <?php echo $activeTab === 'email'      ? 'active' : '' ?>" href="?tab=email"><i class="fa-regular fa-envelope"></i> Email & SMTP</a>
    <a class="adm-tab <?php echo $activeTab === 'appearance' ? 'active' : '' ?>" href="?tab=appearance"><i class="fa-regular fa-palette"></i> Appearance</a>
    <a class="adm-tab <?php echo $activeTab === 'features'   ? 'active' : '' ?>" href="?tab=features"><i class="fa-regular fa-toggle-on"></i> Features</a>
    <a class="adm-tab <?php echo $activeTab === 'ads'        ? 'active' : '' ?>" href="?tab=ads"><i class="fa-regular fa-code"></i> Ads & Code</a>
    <a class="adm-tab <?php echo $activeTab === 'downloads'  ? 'active' : '' ?>" href="?tab=downloads"><i class="fa-regular fa-download"></i> Downloads</a>
  </div>

  <!-- ── GENERAL ── -->
  <?php if ($activeTab === 'general'): ?>
    <form action="settings.php?tab=general" method="POST">
      <input type="hidden" name="save" value="general">
      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-globe"></i> Site Info</div>
        <div class="adm-grid-2">
          <div class="adm-field">
            <label class="adm-label">Website URL *</label>
            <input class="adm-input" type="url" name="website_url" value="<?php echo htmlspecialchars($setting['website_url']); ?>" required placeholder="https://yourdomain.com">
          </div>
          <div class="adm-field">
            <label class="adm-label">Site Name *</label>
            <input class="adm-input" type="text" name="site_name" value="<?php echo htmlspecialchars($setting['site_name']); ?>" required maxlength="100">
          </div>
        </div>
        <div class="adm-field">
          <label class="adm-label">Description <span style="font-weight:400;color:var(--adm-muted);">(max 141 chars)</span></label>
          <input class="adm-input" type="text" name="description" value="<?php echo htmlspecialchars($setting['description']); ?>" maxlength="141">
        </div>
        <div class="adm-field">
          <label class="adm-label">Footer Description</label>
          <input class="adm-input" type="text" name="footer_description" value="<?php echo htmlspecialchars($setting['footer_desc']); ?>">
        </div>
        <div class="adm-grid-2">
          <div class="adm-field">
            <label class="adm-label">Keywords</label>
            <input class="adm-input" type="text" name="keywords" value="<?php echo htmlspecialchars($setting['keywords']); ?>">
          </div>
          <div class="adm-field">
            <label class="adm-label">Author</label>
            <input class="adm-input" type="text" name="author" value="<?php echo htmlspecialchars($setting['author']); ?>">
          </div>
        </div>
        <div class="adm-grid-2">
          <div class="adm-field">
            <label class="adm-label">Support Email *</label>
            <input class="adm-input" type="email" name="support_email" value="<?php echo htmlspecialchars($setting['support_email']); ?>" required>
          </div>
          <div class="adm-field">
            <label class="adm-label">Admin Email *</label>
            <input class="adm-input" type="email" name="mail_admin" value="<?php echo htmlspecialchars($setting['mail_admin']); ?>" required>
          </div>
        </div>
      </div>
      <button class="adm-save" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save General</button>
    </form>

    <!-- ── HOMEPAGE ── -->
  <?php elseif ($activeTab === 'homepage'): ?>
    <form action="settings.php?tab=homepage" method="POST">
      <input type="hidden" name="save" value="homepage">
      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-house"></i> Hero Section</div>
        <div class="adm-field">
          <label class="adm-label">Homepage Header *</label>
          <input class="adm-input" type="text" name="homepage_header" value="<?php echo htmlspecialchars($setting['homepage_header']); ?>" required maxlength="100">
        </div>
        <div class="adm-field">
          <label class="adm-label">Homepage Subheader *</label>
          <input class="adm-input" type="text" name="homepage_subheader" value="<?php echo htmlspecialchars($setting['homepage_subheader']); ?>" required maxlength="200">
        </div>
      </div>
      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-bell"></i> Global Notification</div>
        <div class="adm-grid-2">
          <div class="adm-field">
            <label class="adm-label">Message <span style="font-weight:400;color:var(--adm-muted);">(leave empty to disable)</span></label>
            <input class="adm-input" type="text" name="global_message" value="<?php echo htmlspecialchars($setting['global_message'] ?? ''); ?>">
          </div>
          <div class="adm-field">
            <label class="adm-label">Alert Type</label>
            <select class="adm-input" name="alert_type">
              <?php foreach (['success', 'info', 'warning', 'primary', 'danger'] as $t): ?>
                <option value="<?php echo $t; ?>" <?php echo ($setting['alert_type'] ?? 'info') == $t ? 'selected' : ''; ?>><?php echo ucfirst($t); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="adm-grid-2">
          <div class="adm-field">
            <label class="adm-label">Notification Message (header bar)</label>
            <input class="adm-input" type="text" name="notification_msg" value="<?php echo htmlspecialchars($setting['notification_msg'] ?? ''); ?>">
          </div>
          <div class="adm-field">
            <label class="adm-label">Notification URL</label>
            <input class="adm-input" type="url" name="url_msg" value="<?php echo htmlspecialchars($setting['url_msg'] ?? ''); ?>">
          </div>
        </div>
      </div>
      <button class="adm-save" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save Homepage</button>
    </form>

    <!-- ── SECURITY ── -->
  <?php elseif ($activeTab === 'security'): ?>
<form action="settings.php?tab=security" method="POST">
    <input type="hidden" name="save" value="security">

    <!-- reCAPTCHA -->
    <div class="adm-card" style="margin-bottom:1rem;">
        <div class="adm-card-title"><i class="fa-regular fa-shield"></i> Google reCAPTCHA v2</div>
        <div class="adm-field">
            <label class="adm-label">Site Key (public)</label>
            <input class="adm-input" type="text" name="site_key_captcha"
                   value="<?php echo htmlspecialchars($setting['site_key_captcha'] ?? ''); ?>"
                   placeholder="6LeT72gi...">
        </div>
        <div class="adm-field">
            <label class="adm-label">Secret Key (private)</label>
            <input class="adm-input" type="text" name="secret_key_captcha"
                   value="<?php echo htmlspecialchars($setting['secret_key_captcha'] ?? ''); ?>"
                   placeholder="6LeT72gi...">
        </div>
        <p style="font-size:.75rem;color:var(--adm-muted);margin-top:.5rem;">
            <i class="fa-regular fa-circle-info"></i> Get your keys at
            <a href="https://www.google.com/recaptcha/admin/create" target="_blank"
               style="color:var(--adm-accent);">google.com/recaptcha</a>
        </p>
    </div>

    <!-- Google OAuth -->
    <div class="adm-card" style="margin-bottom:1rem;">
        <div class="adm-card-title"><i class="fa-brands fa-google"></i> Google OAuth</div>

        <label class="adm-toggle" style="margin-bottom:1rem;">
            <div>
                <div class="adm-toggle-label">Enable Google Sign-In</div>
                <div class="adm-toggle-sub">Show "Sign in with Google" button on login and register</div>
            </div>
            <label class="adm-switch">
                <input type="checkbox" name="google_oauth_enabled"
                       <?php echo ($setting['google_oauth_enabled'] ?? '0') == '1' ? 'checked' : ''; ?>>
                <span class="adm-slider"></span>
            </label>
        </label>

        <div class="adm-field">
            <label class="adm-label">Client ID</label>
            <input class="adm-input" type="text" name="google_client_id"
                   value="<?php echo htmlspecialchars($setting['google_client_id'] ?? ''); ?>"
                   placeholder="648071122021-xxx.apps.googleusercontent.com">
        </div>
        <div class="adm-field">
            <label class="adm-label">Client Secret</label>
            <div style="position:relative;">
                <input class="adm-input" type="password" name="google_client_secret"
                       id="googleSecret"
                       value="<?php echo htmlspecialchars($setting['google_client_secret'] ?? ''); ?>"
                       placeholder="GOCSPX-..."
                       style="padding-right:2.5rem;">
                <button type="button"
                        onclick="toggleGoogleSecret()"
                        style="position:absolute;right:.7rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--adm-muted);cursor:pointer;font-size:.85rem;padding:0;">
                    <i class="fa-regular fa-eye" id="googleSecretIcon"></i>
                </button>
            </div>
        </div>
        <div style="background:rgba(212,255,0,.05);border:1px solid rgba(212,255,0,.15);border-radius:10px;padding:.85rem 1rem;font-size:.75rem;color:var(--adm-muted);">
            <div style="font-weight:600;color:var(--adm-accent);margin-bottom:.4rem;">
                <i class="fa-regular fa-circle-info"></i> Required Authorized Redirect URIs
            </div>
            <div style="font-family:monospace;font-size:.72rem;margin-bottom:.2rem;">
                <?php echo $setting['website_url']; ?>/user/google-callback.php
            </div>
            <div style="font-size:.7rem;margin-top:.4rem;">
                Add this URL in <a href="https://console.cloud.google.com/apis/credentials"
                target="_blank" style="color:var(--adm-accent);">Google Cloud Console</a>
                → Credentials → OAuth Client → Authorized redirect URIs
            </div>
        </div>
    </div>

    <button class="adm-save" type="submit">
        <i class="fa-regular fa-floppy-disk"></i> Save Security
    </button>
</form>

<script>
function toggleGoogleSecret() {
    var inp  = document.getElementById('googleSecret');
    var icon = document.getElementById('googleSecretIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
}
</script>

    <!-- ── APPEARANCE ── -->
     <!-- ── EMAIL & SMTP ── -->
<?php elseif ($activeTab === 'email'): ?>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;align-items:start;">

    <!-- Columna izquierda: SMTP -->
    <div>
        <form action="settings.php?tab=email" method="POST">
            <input type="hidden" name="save" value="email">

            <!-- Email Verification toggle -->
            <div class="adm-card" style="margin-bottom:1rem;">
                <div class="adm-card-title"><i class="fa-regular fa-envelope-circle-check"></i> Email Verification</div>
                <label class="adm-toggle" style="margin-bottom:0;">
                    <div>
                        <div class="adm-toggle-label">Require Email Verification</div>
                        <div class="adm-toggle-sub">New users must verify their email before logging in</div>
                    </div>
                    <label class="adm-switch">
                        <input type="checkbox" name="email_verification"
                               <?php echo ($setting['email_verification'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <span class="adm-slider"></span>
                    </label>
                </label>
            </div>

            <!-- SMTP Config -->
            <div class="adm-card" style="margin-bottom:1rem;">
                <div class="adm-card-title">
                    <i class="fa-regular fa-server"></i> SMTP Configuration
                </div>

                <label class="adm-toggle" style="margin-bottom:1rem;">
                    <div>
                        <div class="adm-toggle-label">Use SMTP</div>
                        <div class="adm-toggle-sub">Use custom SMTP instead of PHP mail()</div>
                    </div>
                    <label class="adm-switch">
                        <input type="checkbox" name="smtp_enabled" id="smtpToggle"
                               <?php echo ($setting['smtp_enabled'] ?? '0') == '1' ? 'checked' : ''; ?>>
                        <span class="adm-slider"></span>
                    </label>
                </label>

                <div id="smtpFields" style="<?php echo ($setting['smtp_enabled'] ?? '0') != '1' ? 'opacity:.4;pointer-events:none;' : ''; ?>">
                    <div class="adm-grid-2">
                        <div class="adm-field">
                            <label class="adm-label">SMTP Host *</label>
                            <input class="adm-input" type="text" name="smtp_host"
                                   value="<?php echo htmlspecialchars($setting['smtp_host'] ?? ''); ?>"
                                   placeholder="smtp.gmail.com">
                        </div>
                        <div class="adm-field">
                            <label class="adm-label">Port *</label>
                            <input class="adm-input" type="number" name="smtp_port"
                                   value="<?php echo (int)($setting['smtp_port'] ?? 587); ?>"
                                   min="1" max="65535" placeholder="587">
                        </div>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label">Encryption</label>
                        <select class="adm-input" name="smtp_encryption">
                            <?php foreach (['tls' => 'TLS (recommended)', 'ssl' => 'SSL', 'none' => 'None'] as $val => $label): ?>
                                <option value="<?php echo $val; ?>"
                                    <?php echo ($setting['smtp_encryption'] ?? 'tls') === $val ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="adm-grid-2">
                        <div class="adm-field">
                            <label class="adm-label">SMTP Username</label>
                            <input class="adm-input" type="text" name="smtp_user"
                                   value="<?php echo htmlspecialchars($setting['smtp_user'] ?? ''); ?>"
                                   placeholder="your@email.com"
                                   autocomplete="username">
                        </div>
                        <div class="adm-field">
                            <label class="adm-label">
                                SMTP Password
                                <span style="font-weight:400;font-size:.7rem;color:var(--adm-muted);">(leave empty to keep current)</span>
                            </label>
                            <div style="position:relative;">
                                <input class="adm-input" type="password" name="smtp_pass"
                                       placeholder="••••••••"
                                       autocomplete="new-password"
                                       id="smtpPass"
                                       style="padding-right:2.5rem;">
                                <button type="button" onclick="toggleSmtpPass()"
                                        style="position:absolute;right:.7rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--adm-muted);cursor:pointer;font-size:.85rem;padding:0;">
                                    <i class="fa-regular fa-eye" id="smtpPassIcon"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="adm-grid-2">
                        <div class="adm-field">
                            <label class="adm-label">From Name</label>
                            <input class="adm-input" type="text" name="smtp_from_name"
                                   value="<?php echo htmlspecialchars($setting['smtp_from_name'] ?? ''); ?>"
                                   placeholder="<?php echo htmlspecialchars($setting['site_name']); ?>"
                                   maxlength="100">
                        </div>
                        <div class="adm-field">
                            <label class="adm-label">From Email *</label>
                            <input class="adm-input" type="email" name="smtp_from_email"
                                   value="<?php echo htmlspecialchars($setting['smtp_from_email'] ?? ''); ?>"
                                   placeholder="noreply@yourdomain.com"
                                   maxlength="150">
                        </div>
                    </div>
                </div>
            </div>

            <button class="adm-save" type="submit">
                <i class="fa-regular fa-floppy-disk"></i> Save Email Settings
            </button>
        </form>
    </div>

    <!-- Columna derecha: Test + Info -->
    <div>
        <!-- Test SMTP -->
        <div class="adm-card" style="margin-bottom:1rem;">
            <div class="adm-card-title"><i class="fa-regular fa-paper-plane"></i> Test Connection</div>
            <div class="adm-field">
                <label class="adm-label">Send test email to</label>
                <input class="adm-input" type="email" id="testEmail"
                       placeholder="test@example.com"
                       value="<?php echo htmlspecialchars($setting['mail_admin'] ?? ''); ?>">
            </div>
            <button type="button" class="adm-save" style="margin-top:.5rem;" onclick="testSmtp()">
                <i class="fa-regular fa-paper-plane"></i> Send Test Email
            </button>
            <div id="smtpTestResult" style="margin-top:.75rem;font-size:.82rem;display:none;"></div>
        </div>

        <!-- Providers info -->
        <div class="adm-card">
            <div class="adm-card-title"><i class="fa-regular fa-circle-info"></i> Common SMTP Providers</div>
            <div style="font-size:.78rem;color:var(--adm-muted);">
                <?php
                $providers = [
                    ['Gmail',     'smtp.gmail.com',     587, 'TLS', 'Use App Password, not your regular password'],
                    ['Outlook',   'smtp.office365.com', 587, 'TLS', 'Use your Microsoft account credentials'],
                    ['Yahoo',     'smtp.mail.yahoo.com',587, 'TLS', 'Requires App Password'],
                    ['Mailgun',   'smtp.mailgun.org',   587, 'TLS', 'Use SMTP credentials from Mailgun dashboard'],
                    ['SendGrid',  'smtp.sendgrid.net',  587, 'TLS', 'Use apikey as username and API key as password'],
                ];
                foreach ($providers as $p): ?>
                <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.6rem 0;border-bottom:1px solid var(--adm-border);">
                    <div style="width:70px;font-weight:600;color:var(--adm-text);flex-shrink:0;"><?php echo $p[0]; ?></div>
                    <div>
                        <div style="color:var(--adm-accent);font-size:.75rem;"><?php echo $p[1]; ?>:<?php echo $p[2]; ?> (<?php echo $p[3]; ?>)</div>
                        <div style="font-size:.72rem;margin-top:.15rem;"><?php echo $p[4]; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="padding:.6rem 0;">
                    <div style="font-weight:600;color:var(--adm-text);margin-bottom:.25rem;">Gmail App Password:</div>
                    Google Account → Security → 2-Step Verification → App Passwords
                </div>
            </div>
        </div>
    </div>
</div>

  <?php elseif ($activeTab === 'appearance'): ?>

    <!-- Form colores -->
    <form action="settings.php?tab=appearance" method="POST">
      <input type="hidden" name="save" value="appearance">
      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-solid fa-palette"></i> Colors</div>
        <div class="adm-field">
          <label class="adm-label">Navbar Background Color</label>
          <div style="display:flex;gap:.75rem;align-items:center;">
            <input class="adm-input" type="text" id="bgColorText" name="bg_color"
              value="<?php echo htmlspecialchars($setting['bg_color'] ?? '#0d0f1c'); ?>"
              pattern="^#[0-9a-fA-F]{3,6}$" placeholder="#0d0f1c" style="flex:1;">
            <input type="color" id="bgColorPicker"
              value="<?php echo htmlspecialchars($setting['bg_color'] ?? '#0d0f1c'); ?>"
              style="width:40px;height:40px;border-radius:8px;border:1px solid var(--adm-border);cursor:pointer;background:transparent;">
          </div>
        </div>
      </div>
      <button class="adm-save" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save Colors</button>
    </form>

    <!-- Form upload — form separado con enctype correcto -->
    <form id="uploadForm" enctype="multipart/form-data" style="margin-top:1rem;">
      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-image"></i> Logo & Favicon</div>

        <div class="adm-brand-preview">
          <img id="logoPreview" src="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_logo']; ?>" alt="Logo">
          <span>Current logo — <strong><?php echo $setting['site_logo']; ?></strong></span>
        </div>
        <div class="adm-field">
          <label class="adm-label">New Logo (.SVG, .PNG, .JPG — max 5MB)</label>
          <input type="file" name="logoimg" class="adm-input" style="padding:.4rem;" accept=".svg,.png,.jpg,.jpeg">
        </div>

        <div class="adm-brand-preview" style="margin-top:1rem;">
          <img id="faviconPreview" src="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>" alt="Favicon">
          <span>Current favicon — <strong><?php echo $setting['site_favicon']; ?></strong></span>
        </div>
        <div class="adm-field">
          <label class="adm-label">New Favicon (.SVG, .PNG, .JPG — max 5MB)</label>
          <input type="file" name="faviconimg" class="adm-input" style="padding:.4rem;" accept=".svg,.png,.jpg,.jpeg">
        </div>


        <div id="uploadResult" style="margin-top:.75rem;font-size:.82rem;"></div>
      </div>
      <button type="submit" class="adm-save" style="margin-top:.75rem;">
        <i class="fa-regular fa-arrow-up-from-bracket"></i> Upload Files
      </button>
    </form>

    <!-- ── FEATURES ── -->
  <?php elseif ($activeTab === 'features'): ?>
    <div class="adm-card">
      <div class="adm-card-title"><i class="fa-regular fa-toggle-on"></i> Feature Toggles</div>
      <div id="toggleMsg" style="margin-bottom:.75rem;font-size:.82rem;display:none;"></div>
      <div class="adm-toggles">
        <?php
        $toggles = [
          ['name' => 'login',               'label' => 'User Login',          'sub' => 'Enable login & registration'],
          ['name' => 'captcha',              'label' => 'reCAPTCHA',           'sub' => 'Protect forms with Google reCAPTCHA'],
          ['name' => 'show_ads',             'label' => 'Show Ads',            'sub' => 'Display ad blocks on the site'],
          ['name' => 'wishlist',             'label' => 'Wishlist',            'sub' => 'Allow users to save favorites'],
          ['name' => 'notification_header',  'label' => 'Header Notification', 'sub' => 'Show notification bar on top'],
          ['name' => 'show_card_sde',        'label' => 'Card Description',    'sub' => 'Show short desc on logo cards'],
        ];
        foreach ($toggles as $t): ?>
          <label class="adm-toggle">
            <div>
              <div class="adm-toggle-label"><?php echo $t['label']; ?></div>
              <div class="adm-toggle-sub"><?php echo $t['sub']; ?></div>
            </div>
            <label class="adm-switch">
              <input type="checkbox"
                class="feature-toggle"
                data-name="<?php echo $t['name']; ?>"
                <?php echo ($setting[$t['name']] ?? 0) == 1 ? 'checked' : ''; ?>>
              <span class="adm-slider"></span>
            </label>
          </label>
        <?php endforeach; ?>
      </div>
    </div>

<!-- ── DOWNLOADS ── -->
  <?php elseif ($activeTab === 'downloads'): ?>
    <form action="settings.php?tab=downloads" method="POST">
      <input type="hidden" name="save" value="downloads">

      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-download"></i> Download Limits</div>

        <div class="adm-toggle" style="margin-bottom:1.25rem;">
          <div>
            <div class="adm-toggle-label">Enable guest download limit</div>
            <div class="adm-toggle-sub">When off, everyone downloads without limits</div>
          </div>
          <label class="adm-switch">
            <input type="checkbox" name="dl_limit_enabled" <?php echo ($setting['dl_limit_enabled'] ?? '1') == '1' ? 'checked' : ''; ?>>
            <span class="adm-slider"></span>
          </label>
        </div>

        <div class="adm-field">
          <label class="adm-label">Guest download limit</label>
          <input class="adm-input" type="number" name="dl_guest_limit" min="0" max="500"
                 value="<?php echo (int)($setting['dl_guest_limit'] ?? 10); ?>">
          <span style="font-size:.72rem;color:var(--adm-muted);margin-top:.3rem;display:block;">
            How many logos a visitor without an account can download per period. Logged-in users are unlimited.
          </span>
        </div>

        <div class="adm-field">
          <label class="adm-label">Reset period (hours)</label>
          <input class="adm-input" type="number" name="dl_guest_period" min="1" max="720"
                 value="<?php echo (int)($setting['dl_guest_period'] ?? 24); ?>">
          <span style="font-size:.72rem;color:var(--adm-muted);margin-top:.3rem;display:block;">
            After this many hours the guest counter resets. 24 = daily, 168 = weekly.
          </span>
        </div>
      </div>

      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-shield-halved"></i> Anti-Bot Protection</div>

        <div class="adm-field">
          <label class="adm-label">Max downloads per minute (per IP)</label>
          <input class="adm-input" type="number" name="dl_rate_max" min="1" max="100"
                 value="<?php echo (int)($setting['dl_rate_max'] ?? 8); ?>">
          <span style="font-size:.72rem;color:var(--adm-muted);margin-top:.3rem;display:block;">
            Blocks rapid-fire downloads from bots. A real user rarely downloads more than a few per minute.
          </span>
        </div>
      </div>

      <button class="adm-save" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save Download Settings</button>
    </form>

    <!-- ── ADS & CODE ── -->
  <?php elseif ($activeTab === 'ads'): ?>
    <form action="settings.php?tab=ads" method="POST">
      <input type="hidden" name="save" value="ads">
      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-rectangle-ad"></i> Advertisement Blocks</div>
        <div class="adm-field">
          <label class="adm-label">Ads Block 1</label>
          <textarea class="adm-input" name="ads_1" rows="4"><?php echo htmlspecialchars($setting['ads_1'] ?? ''); ?></textarea>
        </div>
        <div class="adm-field">
          <label class="adm-label">Ads Block 2</label>
          <textarea class="adm-input" name="ads_2" rows="4"><?php echo htmlspecialchars($setting['ads_2'] ?? ''); ?></textarea>
        </div>
      </div>
      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-code"></i> Custom Header Code</div>
        <div class="adm-field">
          <label class="adm-label">Code injected in &lt;head&gt; <span style="font-weight:400;color:var(--adm-muted);">(analytics, pixels, etc.)</span></label>
          <textarea class="adm-input" name="code_header" rows="5" style="font-family:monospace;font-size:.8rem;"><?php echo htmlspecialchars($setting['code_header'] ?? ''); ?></textarea>
        </div>
      </div>
      <button class="adm-save" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save Ads & Code</button>
    </form>

  <?php endif; ?>

</div>

<script>

  // SMTP toggle
const smtpToggle = document.getElementById('smtpToggle');
const smtpFields = document.getElementById('smtpFields');
if (smtpToggle && smtpFields) {
    smtpToggle.addEventListener('change', function() {
        smtpFields.style.opacity        = this.checked ? '1' : '.4';
        smtpFields.style.pointerEvents  = this.checked ? 'auto' : 'none';
    });
}

// Toggle SMTP password
function toggleSmtpPass() {
    var inp  = document.getElementById('smtpPass');
    var icon = document.getElementById('smtpPassIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    icon.className = inp.type === 'password' ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
}

// Test SMTP
function testSmtp() {
    var email  = document.getElementById('testEmail').value.trim();
    var result = document.getElementById('smtpTestResult');
    if (!email) {
        result.style.display = 'block';
        result.style.color   = 'var(--adm-danger)';
        result.innerHTML     = '<i class="fa-solid fa-circle-xmark"></i> Enter a valid email address.';
        return;
    }
    result.style.display = 'block';
    result.style.color   = 'var(--adm-muted)';
    result.innerHTML     = '<i class="fa-regular fa-spinner fa-spin"></i> Sending test email...';

    $.ajax({
        url: '<?php echo $setting['website_url']; ?>/admin/ajax-test-smtp.php',
        type: 'POST',
        data: { email: email },
        success: function(res) {
            var data = JSON.parse(res);
            result.style.color = data.success ? 'var(--adm-success)' : 'var(--adm-danger)';
            result.innerHTML   = '<i class="fa-solid fa-' + (data.success ? 'circle-check' : 'circle-xmark') + '"></i> ' + data.msg;
        },
        error: function() {
            result.style.color = 'var(--adm-danger)';
            result.innerHTML   = '<i class="fa-solid fa-circle-xmark"></i> Request failed.';
        }
    });
}



  // Color picker sync
  const bgText = document.getElementById('bgColorText');
  const bgPicker = document.getElementById('bgColorPicker');
  if (bgText && bgPicker) {
    bgPicker.addEventListener('input', function() {
      bgText.value = this.value;
    });
    bgText.addEventListener('input', function() {
      if (/^#[0-9a-fA-F]{3,6}$/.test(this.value)) bgPicker.value = this.value;
    });
  }

  // Upload AJAX
  const uploadForm = document.getElementById('uploadForm');
  if (uploadForm) {
    uploadForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const result = document.getElementById('uploadResult');
      result.innerHTML = '<span style="color:var(--adm-muted);">Uploading...</span>';
      $.ajax({
        url: '<?php echo $setting['website_url']; ?>/admin/ajax-update-settings.php',
        type: 'POST',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function(response) {
          result.innerHTML = response;
          // Refrescar previews sin recargar la página
          const t = Date.now();
          ['logoPreview', 'faviconPreview'].forEach(id => {
            const img = document.getElementById(id);
            if (img) img.src = img.src.split('?')[0] + '?t=' + t;
          });
        },
        error: function() {
          result.innerHTML = '<span style="color:#ff4d4d;">Upload failed. Check server logs.</span>';
        }
      });
    });
  }

  // Feature toggles — guardan automáticamente al cambiar
  document.querySelectorAll('.feature-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
      const name = this.dataset.name;
      const value = this.checked ? '1' : '0';
      const msg = document.getElementById('toggleMsg');

      $.ajax({
        url: '<?php echo $setting['website_url']; ?>/admin/ajax-toggle-feature.php',
        type: 'POST',
        data: {
          name: name,
          value: value
        },
        success: function(res) {
          const data = JSON.parse(res);
          msg.style.display = 'block';
          if (data.success) {
            msg.style.color = '#2dc653';
            msg.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + name + ' ' + (value == '1' ? 'enabled' : 'disabled');
          } else {
            msg.style.color = '#ff4d4d';
            msg.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Error: ' + data.msg;
          }
          setTimeout(() => {
            msg.style.display = 'none';
          }, 2000);
        }
      });
    });
  });
</script>

<?php require_once('includes/footer.php'); ?>