<?php

$pageTitle = 'Coupons';
require_once('../system/config-admin.php');
// Pagination
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 10;
//$num = $coupon->countAll();
/*$pages = $num / $maxres;
$pages = ceil($pages);*/
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;

//$coupons = $coupon->getCoupons($start, $maxres);

require_once('includes/header1.php');

?>

<div class="content">

  <nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
      <ul class="navbar-nav">
        <li class="nav-item active">
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


    <?php if ($num == 0) {
    ?>
      <h2 align='center'>No Coupons Added</h2>
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
          <?php foreach ($coupons as $coupon) {
          ?>
            <tr>
              <td><?php echo $coupon['id']; ?></td>
              <td><?php echo $coupon['code']; ?></td>
              <td><?php echo $coupon['off'] . " ";
                  if ($coupon['off_type'] == 1) {
                    echo '%';
                  } elseif ($coupon['off_type'] == 2) {
                    echo $setting['currency_sym'];
                  } ?></td>
              <td><?php echo $coupon['order_min'] . " (" . $setting['currency_sym'] . ")"; ?></td>
              <td>
                <div class="btn-group btn-group-sm" role="group" aria-label="AActions"><a href="edit-coupon.php?coupon_id=<?php echo $coupon['id']; ?>" class="btn btn-outline-primary">Edit</a><a href="remove-coupon.php?coupon_id=<?php echo $coupon['id']; ?>" class="btn btn-outline-primary">Delete</a></div>
              </td>
            </tr>
          <?php } ?>

        </tbody>
      </table>
  </div>

  <br>
  <ul class="pagination justify-content-center">
    <?php
      $back = (($currpage == 1) ? '#' : 'coupons.php?page=' . ($currpage - 1));
      $next = (($currpage == $pages) ? 'coupons.php?page=' . $currpage : 'coupons.php?page=' . ($currpage + 1));
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