<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


$pageTitle = 'Favorites';
$pg = '3';
require_once('../system/config-user.php');
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 10;
$pages = $wishcount / $maxres;
$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
$wishlists = $wishlist->getUserWishlist($crypt->decrypt($_SESSION['uid'], 'USER'), $start, $maxres);
require_once('includes/header.php');
if (isset($_GET['type']) && isset($_GET['msg'])) {
  echo  "<div class=\"alert " . $_GET['type'] . "\" style=\"display:block;\">" . $_GET['msg'] . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
}
?>
<div class="row">
  <div class="col-4">

    <div class="my-3 p-3 bg-white rounded box-shadow">

      <?php include 'includes/sidenav.php'; ?>

    </div>

  </div>
  <div class="col-8 pl-0">
    <?php if ($wishcount > 0) {
    ?>

      <div class="my-3 p-3 bg-white rounded box-shadow">
        <table class="table table-striped table-hover table-bordered">
          <thead>
            <tr>
              <th><?php echo $l['item_name'] ?></th>
              <th><?php echo $l['price'] ?></th>
              <th><?php echo $l['actions'] ?></th>
            </tr>
          </thead>
          <tbody>
            <?php
            foreach ($wishlists as $wishy) {
              if ($wishy['product_id'] != '0') {
                $prod = $product->details($wishy['product_id']);
                $prod1 = $prod['name'];
              }
            ?>
              <tr>
                <td><?php echo $prod1; ?></td>
                <td><span class="badge badge-secondary"><?php echo $prod['views']; ?></span></td>
                <td>
                  <div class="btn-group" role="group" aria-label="Wishlist Options">
                    <a href="<?php echo $setting['website_url']; ?>/item.php?id=<?php echo $wishy['product_id']; ?>" class="btn btn-sm btn-outline-primary"><?php echo $l['view'] ?></a>
                    <a href="<?php echo $setting['website_url']; ?>/user/wishlist-del.php?delete_id=<?php echo $wishy['w_id']; ?>" class="btn btn-sm btn-outline-primary"><?php echo $l['remove'] ?></a>
                  </div>
                </td>
              </tr>
            <?php
            }
            ?>
          </tbody>
        </table>
      </div>

      <br>
      <ul class="pagination justify-content-center">
        <?php
        $back = (($currpage == 1) ? '#' : 'wishlist.php?page=' . ($currpage - 1));
        $next = (($currpage == $pages) ? 'wishlist.php?page=' . $currpage : 'wishlist.php?page=' . ($currpage + 1));
        ?>
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == 1) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Previous" href="<?php echo $back; ?>" tabindex="-1"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
        </li>
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == $pages) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Next" href="<?php echo $next; ?>"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
        </li>
      </ul>
    <?php
    } else {
      echo  '<div class="my-3 p-3 bg-white rounded box-shadow"><div class="alert alert-primary mb-0">' . $l['noitemsinwish'] . '</div></div>';
    } ?>
  </div>
  <?php
  require_once('includes/footer.php');
  ?>