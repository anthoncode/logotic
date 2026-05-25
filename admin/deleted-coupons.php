<?php

$pageTitle = 'Deleted Coupons';
require_once('../system/config-admin.php');
if (isset($_REQUEST['action'])) {
  switch ($_REQUEST['action']) {
    case 'restore':
      $result = $coupon->restore($_REQUEST['coupon_id']);
      break;
  }
}

// Pagination
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 20;
$num = $coupon->countAllDeleted();
$pages = $num / $maxres;
$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
////////////////
$coupons = $coupon->getDeletedCoupons($start, $maxres);

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
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-coupon.php">Add Coupon</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-coupons.php">Deleted Coupons</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <?php if ($num == 0) {
    ?>
      <h2 align='center'>Nothing Deleted...</h2>
    <?php
    } else { ?>

      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>Coupon ID</th>
            <th>Coupon Code</th>
            <th>Coupon Discount</th>
            <th>Coupon Min Valid Price</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($coupons as $onecoupon) { ?>
            <tr>
              <td><?php echo $onecoupon['id']; ?></td>
              <td><?php echo $onecoupon['code']; ?></td>
              <td><?php echo $onecoupon['off'] . " ";
                  if ($onecoupon['off_type'] == 1) {
                    echo '%';
                  } elseif ($onecoupon['off_type'] == 2) {
                    echo $setting['currency_sym'];
                  } ?></td>
              <td><?php echo $onecoupon['order_min'] . " (" . $setting['currency_sym'] . ")"; ?></td>
              <td><a class="btn btn-outline-primary btn-sm" href="deleted-coupons.php?action=restore&coupon_id=<?php echo $onecoupon['id']; ?>" title="Reactivate Coupon">Restore Coupon</a></td>
            </tr>
          <?php
          } ?>

        </tbody>
      </table>
  </div>
  <br>
  <ul class="pagination justify-content-center">
    <?php
      $back = (($currpage == 1) ? '#' : 'deleted-coupons.php?page=' . ($currpage - 1));
      $next = (($currpage == $pages) ? 'deleted-coupons.php?page=' . $currpage : 'coupons.php?page=' . ($currpage + 1));
    ?>
    <li class="page-item">
      <a class="page-link" <?php echo ($currpage == 1) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Previous" href="<?php echo $back; ?>" tabindex="-1"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
    </li>
    <li class="page-item">
      <a class="page-link" <?php echo ($currpage == $pages) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Next" href="<?php echo $next; ?>"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
    </li>
  </ul>

</div>
</div>
<?php
    }
    require_once('includes/footer.php');

?>