<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
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
        'site_key_captcha'   => trim($_POST['site_key_captcha']   ?? ''),
        'secret_key_captcha' => trim($_POST['secret_key_captcha'] ?? ''),
      ];
      $settings->update($newsettings);
      $activeTab = 'security';
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
  <div class="adm-tabs">
    <a class="adm-tab <?php echo $activeTab === 'general'    ? 'active' : '' ?>" href="?tab=general"> <i class="fa-regular fa-globe"></i> General</a>
    <a class="adm-tab <?php echo $activeTab === 'homepage'   ? 'active' : '' ?>" href="?tab=homepage"> <i class="fa-regular fa-house"></i> Homepage</a>
    <a class="adm-tab <?php echo $activeTab === 'security'   ? 'active' : '' ?>" href="?tab=security"> <i class="fa-regular fa-shield"></i> Security</a>
    <a class="adm-tab <?php echo $activeTab === 'appearance' ? 'active' : '' ?>" href="?tab=appearance"> <i class="fa-regular fa-palette"></i> Appearance</a>
    <a class="adm-tab <?php echo $activeTab === 'features'   ? 'active' : '' ?>" href="?tab=features"> <i class="fa-regular fa-toggle-on"></i> Features</a>
    <a class="adm-tab <?php echo $activeTab === 'ads'        ? 'active' : '' ?>" href="?tab=ads"> <i class="fa-regular fa-code"></i> Ads & Code</a>
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
      <div class="adm-card">
        <div class="adm-card-title"><i class="fa-regular fa-shield"></i> Google reCAPTCHA v2</div>
        <div class="adm-field">
          <label class="adm-label">Site Key (public)</label>
          <input class="adm-input" type="text" name="site_key_captcha" value="<?php echo htmlspecialchars($setting['site_key_captcha'] ?? ''); ?>" placeholder="6LeT72gi...">
        </div>
        <div class="adm-field">
          <label class="adm-label">Secret Key (private)</label>
          <input class="adm-input" type="text" name="secret_key_captcha" value="<?php echo htmlspecialchars($setting['secret_key_captcha'] ?? ''); ?>" placeholder="6LeT72gi...">
        </div>
        <p style="font-size:.75rem;color:var(--adm-muted);margin-top:.5rem;">
          <i class="fa-regular fa-circle-info"></i> Get your keys at
          <a href="https://www.google.com/recaptcha/admin/create" target="_blank" style="color:var(--adm-accent);">google.com/recaptcha</a>
        </p>
      </div>
      <button class="adm-save" type="submit"><i class="fa-regular fa-floppy-disk"></i> Save Security</button>
    </form>

    <!-- ── APPEARANCE ── -->
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