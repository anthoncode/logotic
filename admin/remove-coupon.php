<?php

$pageTitle = 'Delete Coupon';
require_once('../system/config-admin.php');
if (isset($_REQUEST['coupon_id'])) {
  if (!empty($_REQUEST['coupon_id'])) {
    $details = $coupon->details(trim($_REQUEST['coupon_id']));
  } else {
    header("location:coupons.php");
  }
} else {
  $details = $coupon->details('0');
}
if (isset($_REQUEST['coupon_id']) && 'delete' == @$_REQUEST['action']) {
  if (!empty($_REQUEST['coupon_id'])) {

    $result = $coupon->remove($_REQUEST['coupon_id']);
    unset($details);
  }
}

if (!empty($coupon->msg)) {
  $success = $coupon->msg;
}
if (!empty($coupon->error)) {
  $error = $coupon->error;
}

require_once('includes/header1.php');

?>

<?php

if (isset($details)) {

?>

  <div class="content">

    <nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/coupons.php">All Coupons</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-coupon.php">Add Coupon</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-coupons.php">Deleted Coupons</a>
          </li>
        </ul>
      </div>
    </nav>

    <div class="my-3 p-3 bg-white rounded box-shadow">

      <h3>Are you sure you want to delete this coupon?</h3><br>
      <form action="remove-coupon.php?coupon_id=<?php echo $details['id']; ?>" method="post" class="form-horizontal">

        <div class="form-group">
          <div class="col-md-4 col-md-offset-3">
            <input class="form-control" type="hidden" name="action" value="delete">
            <button class="btn btn-danger">Delete</button>
          </div>
        </div>
      </form>
    </div>
  <?php } ?>
  <?php
  require_once('includes/footer.php');

  ?>