<?php

$pageTitle = 'Search Transactions';
require_once('../system/config-admin.php');
if (isset($_GET['transaction'])) {
  $id = $crypt->decrypt($_GET['transaction'], 'TRANSACTION');
  $id = (!empty($id) ? $id : 'x');
  $transactions = $transaction->all('id', $id);
}
if (isset($_GET['user'])) {
  $id = $crypt->decrypt($_GET['user'], 'USER');
  $id = (!empty($id) ? $id : 'x');
  $transactions = $transaction->all('user_id', $id);
}

$num  = (isset($transactions) ? count($transactions) : 0);

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
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/coupons.php">All Transactions</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-coupon.php">Search Transactions</a>
        </li>
      </ul>
    </div>
  </nav>


  <div class="my-3 p-3 bg-white rounded box-shadow">

    <div class="row">
      <div class="col-md-6">
        <form class="form-inline">

          <div class="input-group w-100 mb-3">
            <input type="text" class="form-control" name="user" placeholder="User ID" aria-describedby="basic-addon2">
            <div class="input-group-append">
              <button class="btn btn-outline-primary" type="submit">Search</button>
            </div>
          </div>

        </form>
      </div>
      <div class="col-md-6">
        <form class="form-inline">

          <div class="input-group w-100 mb-3">
            <input type="text" class="form-control" name="transaction" placeholder="Transaction ID" aria-describedby="basic-addon2">
            <div class="input-group-append">
              <button class="btn btn-outline-primary" type="submit">Search</button>
            </div>
          </div>

        </form>
      </div>
    </div>
    <?php
    if ($num > 0) { ?>
      <div class="row">
        <div class="col-md-12">
          <table class="table table-striped table-hover table-bordered">
            <thead>
              <tr>
                <th>Date</th>
                <th>Transaction ID</th>
                <th>User</th>
                <th>Detail</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
              foreach ($transactions as $trans) {
                if ($trans['product'] != '0') {
                  $prod = $product->details($trans['product']);
                  $prod = $prod['name'];
                } else {
                  $prod = 'Purchase Credits';
                }
                if ($trans['type'] == '3') {
                  $prod = 'Free Credits';
                }
              ?>
                <tr>
                  <td><?php echo $trans['date']; ?></td>
                  <td><?php echo $trans['id']; ?></td>
                  <td><?php echo $trans['user_id']; ?></td>
                  <td><?php echo $prod; ?></td>
                  <td><?php echo $setting['currency_sym'] . " " . $trans['amount']; ?></td>
                  <td><?php echo ($trans['callback'] == '1' ? 'Completed' : 'Failed'); ?></td>
                </tr>
              <?php
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php
    } elseif (isset($_GET['user']) || isset($_GET['transaction'])) {
      echo  "<div class='alert alert-danger'>No transactions found</div>";
    }
    require_once('includes/footer.php');
    ?>