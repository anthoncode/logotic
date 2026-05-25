<?php

$pageTitle = 'Settings';
require_once('../system/config-admin.php');
require_once('../system/gateways.php');
$active = 'web';
if (isset($_REQUEST['save'])) {
  if ($_REQUEST['save'] == 'web') {
    $newsettings = array();
    $newsettings['website_url'] = $_REQUEST['website_url'];
    $newsettings['site_name'] = $_REQUEST['site_name'];
    $newsettings['description'] = $_REQUEST['description'];
    $newsettings['footer_desc'] = $_REQUEST['footer_description'];
    $newsettings['keywords'] = $_REQUEST['keywords'];
    $newsettings['author'] = $_REQUEST['author'];

    //$newsettings['site_logo'] = $_REQUEST['site_logo'];
    $newsettings['support_email'] = $_REQUEST['support_email'];
    $newsettings['mail_admin'] = $_REQUEST['mail_admin'];
    $newsettings['homepage_header'] = $_REQUEST['homepage_header'];
    $newsettings['homepage_subheader'] = $_REQUEST['homepage_subheader'];
    $newsettings['global_message'] = $_REQUEST['global_message'];
    $newsettings['alert_type'] = $_REQUEST['alert_type'];

    $newsettings['site_key_captcha'] = $_REQUEST['site_key_captcha'];
    $newsettings['secret_key_captcha'] = $_REQUEST['secret_key_captcha'];
    //  $newsettings['show_card_sde'] = $_REQUEST['show_card_sde'];

    $newsettings['ads_1'] = $_REQUEST['ads_1'];
    $newsettings['ads_2'] = $_REQUEST['ads_2'];
    $newsettings['code_header'] = $_REQUEST['code_header'];
    $newsettings['show_ads'] = (isset($_POST['show_ads'])) ? 1 : 0;
    $newsettings['wishlist'] = (isset($_POST['wishlist'])) ? 1 : 0;
    $newsettings['captcha'] = (isset($_POST['captcha'])) ? 1 : 0;
    $newsettings['login'] = (isset($_POST['login'])) ? 1 : 0;
    $newsettings['notification_header'] = (isset($_POST['notification_header'])) ? 1 : 0;
    $newsettings['show_card_sde'] = (isset($_POST['show_card_sde'])) ? 1 : 0;

    if (empty($newsettings['website_url']) || empty($newsettings['site_name']) || empty($newsettings['homepage_header']) || empty($newsettings['homepage_subheader']) || empty($newsettings['support_email'])) {
      $settings->error = 'All fields are required';
    }
    $result = (!$settings->error ? $settings->update($newsettings) : $settings->error);
    $active = 'web';
  } elseif ($_REQUEST['save'] == 'payment') {
    $newsettings = array();
    $newsettings['txn'] = $_REQUEST['txn'];
    $newsettings['currency'] = $_REQUEST['currency'];
    $newsettings['currency_sym'] = $_REQUEST['currency_sym'];
    $newsettings['paypal_email'] = $_REQUEST['paypal_email'];

    if (empty($newsettings['txn']) || empty($newsettings['currency']) || empty($newsettings['currency_sym']) || empty($newsettings['paypal_email'])) {
      $settings->error = 'All fields are required';
    }
    if (!is_numeric($newsettings['txn'])) {
      $settings->error = 'Transaction charges must be a numeric value';
    }
    $result = (!$settings->error ? $settings->update($newsettings) : $settings->error);
    $active = 'payment';
  }
}
$setting = $settings->get_all();

if (!empty($settings->msg)) {
  $success = $settings->msg;
}
if (!empty($settings->error)) {
  $error = $settings->error;
}

require_once('includes/header1.php');
?>

<div class="my-3 p-3 bg-white rounded box-shadow">
  <form action="settings.php" method="post" class="form-horizontal">
    <input class="form-control" type="hidden" name="save" value="web">
    <h3>Overall Settings</h3>
    <div class="form-group">
      <label class="control-label" for="website-url">Website URL</label>
      <input class="form-control" type="text" name="website_url" value="<?php echo $setting['website_url']; ?>" id="website-url">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Site Name</label>
      <input class="form-control" type="text" name="site_name" value="<?php echo $setting['site_name']; ?>" id="site-name">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Description</label>
      <input class="form-control" type="text" name="description" value="<?php echo $setting['description']; ?>" id="site-name" maxlength="141">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Footer Description</label>
      <input class="form-control" type="text" name="footer_description" value="<?php echo $setting['footer_desc']; ?>" id="site-name">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Keywords</label>
      <input class="form-control" type="text" name="keywords" value="<?php echo $setting['keywords']; ?>" id="site-name">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Author</label>
      <input class="form-control" type="text" name="author" value="<?php echo $setting['author']; ?>" id="site-name">
    </div>

    <!-- <div class="form-group">
      <label class="control-label" for="site-name">Notification in header</label>
      <input class="form-control" type="text" name="notification_header" value="<?php echo $setting['notification_header']; ?>" id="site-name">
    </div>
 -->


    <div class="form-group">
      <label class="control-label" for="site-name">Notification message</label>
      <input class="form-control" type="text" name="notification_msg" value="<?php echo $setting['notification_msg']; ?>" id="site-name">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Notification URL</label>
      <input class="form-control" type="url" name="url_msg" value="<?php echo $setting['url_msg']; ?>" id="site-name">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Notification color</label>
      <input class="form-control" type="text" name="bg_color" value="<?php echo $setting['bg_color']; ?>" id="site-name">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Support Email</label>
      <input class="form-control" type="email" name="support_email" value="<?php echo $setting['support_email']; ?>" id="site-name">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Admin Email</label>
      <input class="form-control" type="email" name="mail_admin" value="<?php echo $setting['mail_admin']; ?>" id="site-name">
    </div>
    <hr>
    <div class="form-group">
      <label class="control-label" for="site-name">Homepage Header</label>
      <input class="form-control" type="text" name="homepage_header" value="<?php echo $setting['homepage_header']; ?>" id="site-name">
    </div>
    <div class="form-group">
      <label class="control-label" for="site-name">Homepage Subheader</label>
      <input class="form-control" type="text" name="homepage_subheader" value="<?php echo $setting['homepage_subheader']; ?>" id="site-name">
    </div>
    <hr>

    <div class="form-group">
      <label>Global Message (to disable leave empty):</label>
      <div class="input-group">
        <input type="text" class="form-control" value="<?php echo $setting['global_message']; ?>" name="global_message">
        <div class="input-group-append">
          <select class="form-control" name="alert_type">
            <option value="success">Success</option>
            <option value="info" selected>Info</option>
            <option value="warning">Warning</option>
            <option value="primary">Primary</option>
            <option value="danger">Danger</option>
          </select>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="control-label" for="site_key">Site Key Google reCaptcha</label>
      <input class="form-control" type="text" name="site_key_captcha" value="<?php echo $setting['site_key_captcha']; ?>" id="site_key">
    </div>
    <hr>
    <div class="form-group">
      <label class="control-label" for="secret_key">Secret Key Google reCaptcha</label>
      <input class="form-control" type="text" name="secret_key_captcha" value="<?php echo $setting['secret_key_captcha']; ?>" id="secret_key">
    </div>
    <hr>


    <div class="form-group">
      <input type="checkbox" name="login" <?php if ($setting['login'] == 1) {
                                            echo 'checked="checked"';
                                          } ?> id="login">
      <label class="form-check-label" for="login">Enable login</label>
    </div>

    <div class="form-group">
      <input type="checkbox" name="notification_header" <?php if ($setting['notification_header'] == 1) {
                                                          echo 'checked="checked"';
                                                        } ?> id="notification_header">
      <label class="form-check-label" for="notification_header">Notification in header</label>
    </div>

    <div class="form-group">
      <input type="checkbox" name="show_card_sde" <?php if ($setting['show_card_sde'] == 1) {
                                                    echo 'checked="checked"';
                                                  } ?> id="show_card_sde">
      <label class="form-check-label" for="show_card_sde">Show Short Description on cards?</label>
    </div>

    <div class="form-group">
      <input type="checkbox" name="show_ads" <?php if ($setting['show_ads'] == 1) {
                                                echo 'checked="checked"';
                                              } ?> id="show_ads">
      <label class="form-check-label" for="show_ads">Show ads</label>
    </div>

    <div class="form-group">
      <input type="checkbox" name="wishlist" <?php if ($setting['wishlist'] == 1) {
                                                echo 'checked="checked"';
                                              } ?> id="wishlist">
      <label class="form-check-label" for="wishlist">Wishlist</label>
    </div>
    <div class="form-group">
      <input type="checkbox" name="captcha" <?php if ($setting['captcha'] == 1) {
                                                echo 'checked="checked"';
                                              } ?> id="captcha">
      <label class="form-check-label" for="captcha">reCaptcha</label>
    </div>

    <div class="form-group">
      <label class="control-label">Ads 1</label>
      <textarea name="ads_1" class="form-control" rows="3"><?php echo $setting['ads_1']; ?></textarea>
    </div>

    <div class="form-group">
      <label class="control-label">Ads 2</label>
      <textarea name="ads_2" class="form-control" rows="3"><?php echo $setting['ads_2']; ?></textarea>
    </div>

    <div class="form-group">
      <label class="control-label">Add code to header</label>
      <textarea name="code_header" class="form-control" rows="3"><?php echo $setting['code_header']; ?></textarea>
    </div>

    <!-- <div class="form-group">
 <label class="col-md-2 control-label" for="site-logo">Site Logo</label><div class="col-md-4">
<input class="form-control" type="text" name="site_logo" value="<?php echo $setting['site_logo']; ?>" id="site-logo">
 </div>-->

    <div class="form-group">
      <button class="btn btn-primary" type="submit" name="submit">Save</button>
    </div>
  </form>
</div>

<div class="my-3 p-3 bg-white rounded box-shadow">
  <form name="upload1" id="upload1" class="form-horizontal">
    <h3>Brand Settings</h3>

    <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_logo']; ?>" width="" height="30">

    <div class="form-group"> <label>Logo (.PNG,.JPG):</label>
      <div class="input-group">
        <div class="custom-file">
          <input type="file" name="logoimg" class="custom-file-input" id="inputGroupFile04">
          <label class="custom-file-label" for="inputGroupFile04">Choose File</label>
        </div>
      </div>
    </div>

    <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_favicon']; ?>" width="" height="30">

    <div class="form-group"> <label>Favicon/Login Logo (.PNG,.JPG):</label>
      <div class="input-group">
        <div class="custom-file">
          <input type="file" name="faviconimg" class="custom-file-input" id="inputGroupFile04">
          <label class="custom-file-label" for="inputGroupFile04">Choose File</label>
        </div>
      </div>
    </div>

    <hr>
    <input class="form-control" type="hidden" name="id" value="<?php //echo $productDetails['id']; 
                                                                ?>">
    <button type="submit" id="btn" class="btn btn-primary w-100">Update Files</button>
    <script type="text/javascript">
      $("#upload1").on("submit", (function(e) {
        e.preventDefault();
        $.ajax({
          url: "<?php echo $setting['website_url']; ?>/admin/ajax-update-settings.php",
          type: "POST",
          data: new FormData(this),
          contentType: false,
          cache: false,
          processData: false,
          beforeSend: function() {
            $("#res1").html('Updating..Please wait!');
          },
          success: function(response) {
            $("#res1").html(response);
          }
        });
      }));
    </script>
    <div id="res1"></div>
  </form>
</div>

<div class="my-3 p-3 bg-white rounded box-shadow">
  <form action="settings.php" method="post" class="form-horizontal">
    <input class="form-control" type="hidden" name="save" value="payment">
    <h3>Payment Settings</h3>
    <div class="form-group">
      <label class="control-label" for="txn">Transaction Fee</label>
      <input class="form-control" type="text" name="txn" value="<?php echo $setting['txn']; ?>" id="txn">
    </div>
    <div class="form-group">
      <label class="control-label" for="curr">Currency</label>
      <input class="form-control" type="text" name="currency" value="<?php echo $setting['currency']; ?>" id="curr">
    </div>
    <div class="form-group">
      <label class="control-label" for="curr-sym">Currency Symbols</label>
      <input class="form-control" type="text" name="currency_sym" value="<?php echo $setting['currency_sym']; ?>" id="curr-sym">
    </div>
    <div class="form-group">
      <label class="control-label" for="curr-sym">PayPal Email</label>
      <input class="form-control" type="text" name="paypal_email" value="<?php echo $setting['paypal_email']; ?>" id="curr-sym">
    </div>
    <div class="form-group">
      <button class="btn btn-primary" type="submit" name="submit">Save</button>
    </div>
  </form>
</div>




<?php
require_once('includes/footer.php');

?>