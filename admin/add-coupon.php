<?php

$pageTitle = 'Add Coupon';
require_once('../system/config-admin.php');

if (isset($_REQUEST['coupon_code']) && isset($_REQUEST['coupon_off'])) {

  $coupon_code = trim($_REQUEST['coupon_code']);
  $coupon_off = trim($_REQUEST['coupon_off']);
  $coupon_off_type = trim($_REQUEST['coupon_off_type']);
  $order_min = trim($_REQUEST['order_min']);
  $result = $coupon->add($coupon_code, $coupon_off, $coupon_off_type, $order_min);
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

  <nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/coupons.php">All Coupons</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-coupon.php">Add Coupon</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-coupons.php">Deleted Coupons</a>
        </li>
      </ul>
    </div>
  </nav>


  <!--                                             <div class="my-3 p-3 bg-white rounded box-shadow">
                        
<nav class="nav nav-pills nav-fill">
  <a class="nav-item nav-link" href="<?php echo $setting['website_url']; ?>/admin/coupons.php">All Coupons</a>
  <a class="nav-item nav-link active" href="<?php echo $setting['website_url']; ?>/admin/add-coupon.php">Add Coupon</a>
  <a class="nav-item nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-coupons.php">Deleted Coupons</a>
</nav>

</div>-->

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <form action="add-coupon.php" method="post" class="form-horizontal">

      <div class="form-group">
        <label>Coupon Code:</label>
        <input type="text" class="form-control" name="coupon_code" id="coupon-code">
      </div>

      <div class="form-group">
        <label>Coupon Discount:</label>
        <div class="input-group">
          <input type="number" class="form-control" name="coupon_off" id="coupon-code">
          <div class="input-group-append">
            <select class="form-control" name="coupon_off_type" id="coupon-off-type">
              <option value="1" disabled>%</option>
              <option value="2" selected><?php echo $setting['currency_sym']; ?></option>
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
          <input type="number" class="form-control" name="order_min">
        </div>

      </div>



      <button class="btn btn-primary w-100">Add Coupon</button>
    </form>
  </div>
</div>
<?php
require_once('includes/footer.php');

?>