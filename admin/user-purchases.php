<?php

$pageTitle = "Customer Purchases 0";
require_once('../system/config-admin.php');
$customer = new Customer($DB_con);
if (isset($_REQUEST['id'])) {
  $customerDetails = $customer->details($_REQUEST['id']);
  $error = ($customer->error ? $customer->error : false);
  $userPurchases = $sale->all('user_id', $crypt->decrypt($_REQUEST['id'], 'USER'));
  $userCount = count($userPurchases);
} else {
  require_once('includes/header1.php');
  echo 'Invalid request';
  require_once('includes/footer.php');
  exit;
}
if (empty($error)) {
  unset($error);
}
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
      <li class="nav-item">
        <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/top-buyers.php">Top Buyers</a>
      </li>
    </ul>
  </div>
</nav>

<nav aria-label="breadcrumb">
  <ol class="breadcrumb mt-3">
    <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
    <li class="breadcrumb-item" aria-current="page"><a href="#">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page"><?php echo $customerDetails['fname']; ?>'s Purchases</li>
  </ol>
</nav>

<div class="my-3 p-3 bg-white rounded box-shadow">
  <div class="row">
    <div class="col-md-12"><?php if ($userCount > 0) { ?><table class="table table-striped table-hover table-bordered">
          <thead>
            <tr>
              <th>Date</th>
              <th>Product</th>
              <th>Has Downloaded?</th>
              <th>Purchase ID</th>
              <th>Transaction ID</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($userPurchases as $userPurchase) {
                                $productName = $product->details($userPurchase['pro_id']);
                                $productName = $productName['name'];
            ?>
              <tr>
                <td><?php echo $userPurchase['date']; ?></td>
                <td><?php echo $productName . " (" . $userPurchase['pro_id'] . ")"; ?></td>
                <td><?php echo ($userPurchase['downloaded'] == '1' ? '<span class="badge badge-primary">Yes</span>' : '<span class="badge badge-primary">No</span>'); ?></td>
                <td><?php echo $userPurchase['id']; ?></td>
                <td><?php echo $crypt->encrypt($userPurchase['transaction_id'], 'TRANSACTION'); ?></td>
              </tr>
          <?php
                              }
                              echo "</tbody></table></div></div></div>";
                            } else {
                              echo  "<div class='alert alert-danger'>No purchases found</div>";
                            }
          ?>
    </div>
  </div>
  <?php
  require_once('includes/footer.php');
  ?>