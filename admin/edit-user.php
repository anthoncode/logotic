<?php

$pageTitle = "Edit User Profile";
require_once('../system/config-admin.php');

if (isset($_REQUEST['id'])) {
  $customer = new Customer($DB_con);
  $customerDetails = $customer->userdetails($_REQUEST['id']);
  $error = ($customer->error ? $customer->error : false);

  if (empty($error)) {
    unset($error);
  }
} else {
  require_once('includes/header1.php');
  echo 'Invalid request';
  require_once('includes/footer.php');
  exit;
}

require_once('includes/header1.php');
?>
<script src="//cdnjs.cloudflare.com/ajax/libs/tinymce/4.6.5/tinymce.min.js"></script>
<div class="content">

  <nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/users.php">All Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/new-users.php">New Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/banned-users.php">Banned Users</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/top-buyers.php">Top Buyers</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">


    <form id="upload" class="form-horizontal">
      <div class="form-group"> <label>First Name:</label> <input class="form-control" value="<?php echo $customerDetails['fname']; ?>" name="fname" id="coupon-code" type="text"></div>
      <div class="form-group"> <label>Email:</label> <input class="form-control" value="<?php echo $customerDetails['email']; ?>" name="email" id="coupon-code" type="email"></div>

      <hr>

      <div class="form-check">
        <input type="checkbox" name="allow_email" <?php if ($customerDetails['allow_email'] == 1) {
                                                    echo 'checked="checked"';
                                                  } ?> id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">Allow Newsletter?</label>
      </div>
      <div class="form-check">
        <input type="checkbox" name="password_recover" <?php if ($customerDetails['password_recover'] == 1) {
                                                          echo 'checked="checked"';
                                                        } ?> id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">Force user to reset password on next login?</label>
      </div>
      <div class="form-check">
        <input type="checkbox" name="moderator" <?php if ($customerDetails['moderator'] == 1) {
                                                  echo 'checked="checked"';
                                                } ?> id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">Staff Moderator? (Delete Reviews, Forum Control, Blog, Item Approval)</label>
      </div>
      <div class="form-check">
        <input type="checkbox" name="verified" <?php if ($customerDetails['verified'] == 1) {
                                                  echo 'checked="checked"';
                                                } ?> id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">Approve Author? (Allow user to submit own items and make money [Multi-Vendor Only])</label>
      </div>


      <hr>
      <input class="form-control" type="hidden" name="id" value="<?php echo $customerDetails['username']; ?>">
      <button type="submit" id="btn" class="btn btn-primary w-100">Update</button>
      <script type="text/javascript">
        $("#upload").on("submit", (function(e) {

          tinyMCE.triggerSave();

          e.preventDefault();
          $.ajax({
            url: "<?php echo $setting['website_url']; ?>/admin/ajax-user.php",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
              $("#res").html('Updating..Please wait!');
            },
            success: function(response) {
              $("#res").html(response);
            }
          });
        }));
      </script>
      <div id="res"></div>
    </form>
  </div>

  <?php
  require_once('includes/footer.php');
  ?>