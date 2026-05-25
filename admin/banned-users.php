<?php

$pageTitle = "Banned Users";
require_once('../system/config-admin.php');
$customer = new Customer($DB_con);
if (isset($_REQUEST['id'])) {
  $customerDetails = $customer->details($_REQUEST['id']);
  $error = ($customer->error ? $customer->error : false);

  if (isset($_GET['action']) && $_GET['action'] == 'unban') {

    if (!$error) {
      $customer->update($_REQUEST['id'], 'active', '1');
      $error = ($customer->error ? $customer->error : false);

      $success = $error ? $error : 'User Restored!';
      //$success =($customer->msg?$customer->msg:false);
    }
  }
}
if (empty($error)) {
  unset($error);
}
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 50;
$num = $customer->countBanned();
$pages = $num / $maxres;
$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
$allCustomers = $customer->banned($start, $maxres);
require_once('includes/header1.php');
?>
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
      <li class="nav-item active">
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
        <table class="table table-hover table-striped table-bordered">
          <thead>
            <tr>
              <th>User ID</th>
              <th>Email</th>
              <th class="hidden-phone">Purchases</th>
              <th class="hidden-phone">Balance</th>
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
                <td class="hidden-phone"><?php echo $setting['currency_sym'] . " " . $customer['balance']; ?></td>
                <td>
                  <div class="btn-group btn-group-sm" role="group" aria-label="AActions"><a href="user-purchases.php?id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary">View User Purchases</a><a href="banned-users.php?action=unban&id=<?php echo $customer['id']; ?>" class="btn btn-outline-primary">Restore User</a></div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
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
<?php  } else {
  echo  "<div class='alert alert-danger'>No Users Banned!</div>";
}
require_once('includes/footer.php');
?>