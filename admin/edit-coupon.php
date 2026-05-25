<?php

$pageTitle = 'Edit Coupon';
require_once('../system/config-admin.php');
if (isset($_REQUEST['coupon_id'])) {
  if (!empty($_REQUEST['coupon_id'])) {
    $details = $coupon->details(trim($_REQUEST['coupon_id']));
  } else {
    header("location: coupons.php");
  }
} else {
  $details = $coupon->details('0');
}
if (isset($_REQUEST['coupon_id']) && isset($_REQUEST['coupon_code']) && isset($_REQUEST['coupon_off'])  && isset($_REQUEST['order_min'])) {

  $coupon_id =   trim($_REQUEST['coupon_id']);
  $coupon_code = trim($_REQUEST['coupon_code']);
  $coupon_off = trim($_REQUEST['coupon_off']);
  $coupon_off_type = trim($_REQUEST['coupon_off_type']);
  $order_min = trim($_REQUEST['order_min']);

  $result = $coupon->update($coupon_id, $coupon_code, $coupon_off, $coupon_off_type, $order_min);
  $details = $coupon->details(trim($_REQUEST['coupon_id']));
}
if (!empty($coupon->msg)) {
  $success = $coupon->msg;
}
if (!empty($coupon->error)) {
  $error = $coupon->error;
}
require_once('includes/header1.php');

?>
<div class="content">
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

        <form action="edit-coupon.php?coupon_id=<?php echo $details['id']; ?>" method="post" class="form-horizontal">

          <input type="hidden" class="form-control" name="id" value="<?php echo $details['id']; ?>" disabled>

          <div class="form-group">
            <label>Coupon Code:</label>
            <input type="text" class="form-control" name="coupon_code" value="<?php echo $details['code']; ?>" id="coupon-code">
          </div>

          <div class="form-group">
            <label>Coupon Discount:</label>
            <div class="input-group">
              <input type="number" class="form-control" name="coupon_off" value="<?php echo $details['off']; ?>" id="coupon-code">
              <div class="input-group-append">
                <select class="form-control" name="coupon_off_type" id="coupon-off-type">
                  <option disabled value="1" <?php if ($details['off_type'] == 1) {
                                                echo 'selected';
                                              } ?>>%</option>
                  <option value="2" <?php if ($details['off_type'] == 2) {
                                      echo 'selected';
                                    } ?>><?php echo $setting['currency_sym']; ?></option>
                </select>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Coupon Min Valid Price:</label>

            <div class="input-group mb-3">
              <div class="input-group-prepend">
                <span class="input-group-text"><?php echo $setting['currency_sym']; ?></span>
              </div>
              <input type="number" class="form-control" name="order_min" value="<?php echo $details['order_min']; ?>">
            </div>

          </div>

          <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
      </div>
    <?php } ?>
    </div>
    <?php
    require_once('includes/footer.php');

    ?>