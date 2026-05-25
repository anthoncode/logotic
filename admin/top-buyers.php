<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
$pageTitle = "Top Buyers";
require_once('../system/config-admin.php');
$customer = new Customer($DB_con);
$top = $customer->top();
$num = count($top);
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
      <li class="nav-item">
        <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/banned-users.php">Banned Users</a>
      </li>
      <li class="nav-item active">
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
              <th>User Email</th>

              <th class="hidden-phone">Purchases</th>
              <th class="hidden-phone">Credits</th>
              <th class="hidden-phone">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($top as $topUser) { ?>
              <tr>
                <td><?php echo $topUser['id']; ?></td>
                <td><?php echo $topUser['email']; ?></td>

                <td class="hidden-phone"><?php echo $topUser['purchases']; ?></td>
                <td class="hidden-phone"><?php echo $setting['currency_sym'] . " " . $topUser['balance']; ?></td>
                <td>
                  <div class="btn-group btn-group-sm" role="group" aria-label="AActions"><a href="user-purchases.php?id=<?php echo $topUser['id']; ?>" class="btn btn-outline-primary">View User Purchases</a><a href="add-funds.php?id=<?php echo $topUser['id']; ?>" class="btn btn-outline-primary">Add Credits</a><a href="ban-user.php?id=<?php echo $topUser['id']; ?>" class="btn btn-outline-primary">Ban User</a></div>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php
} else {
  echo  "<div class='alert'>No users registered</div>";
}
require_once('includes/footer.php');
?>