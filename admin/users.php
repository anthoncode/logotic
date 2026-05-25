<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = "User Management";
require_once('../system/config-admin.php');
$customer = new Customer($DB_con);
$allCustomers = $customer->all();
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 25;
$num = $customer->countAll();
$pages = $num / $maxres;
$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
$allCustomers = $customer->getUsers($start, $maxres);
if (isset($_GET['msg'])) {
  $success =  $_GET['msg'];
} else {
  unset($success);
}

require_once('includes/header1.php');
?>

<nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
    <ul class="navbar-nav">
      <li class="nav-item active">
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


<?php if ($num > 0) { ?>
  <div class="my-3 p-3 bg-white rounded box-shadow">
    <div class="row">
      <div class="col-md-12">
        <div class="table">
          <table class="table table-hover table-striped table-bordered">
            <thead>
              <tr>
                <th>User ID</th>
                <th>User Email</th>
                <th class="hidden-phone">Purchases</th>
                <th class="hidden-phone">Newsletter</th>
                <th class="hidden-phone">Credits</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ($allCustomers as $customer) {
              ?>
                <tr>
                  <td><?php echo $customer['id']; ?></td>
                  <td><?php echo $customer['email']; ?></td>
                  <td class="hidden-phone"><?php echo $customer['purchases']; ?></td>
                  <td class="hidden-phone"><?php echo ($customer['allow_email'] == '1' ? '<span data-toggle="tooltip" data-placement="top" title="Yes" class="badge badge-primary badge-pill"><i class="fa fa-check" aria-hidden="true"></i></span>' : '<span data-toggle="tooltip" data-placement="top" title="No" class="badge badge-primary badge-pill"><i class="fa fa-close" aria-hidden="true"></i></span>'); ?></td>
                  <td class="hidden-phone"><?php echo $setting['currency_sym'] . " " . $customer['balance']; ?></td>
                  <td>
                    <div class="btn-group btn-group-sm" role="group" aria-label="AActions"><a href="edit-user.php?id=<?php echo $customer['username']; ?>" class="btn btn-outline-primary">Edit User</a><a href="user-purchases.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary">View User Purchases</a><a href="add-funds.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary">Add Credits</a><a href="ban-user.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary">Ban User</a></div>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
  <br>
  <ul class="pagination justify-content-center">
    <?php
    $back = (($currpage == 1) ? '#' : 'users.php?page=' . ($currpage - 1));
    $next = (($currpage == $pages) ? 'users.php?page=' . $currpage : 'users.php?page=' . ($currpage + 1));
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
  echo  "<div class='alert alert-danger'>No Users Registered or Activated!</div>";
}
require_once('includes/footer.php');
?>