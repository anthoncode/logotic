<?php

$pageTitle = 'Transactions';
require_once('../system/config-admin.php');
$maxres = 50;
$num  = $transaction->countAll();

$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;

$pages = $num / $maxres;
$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
$transactions = $transaction->getTransactions($start, $maxres);
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
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/transactions.php">All Transactions</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/transactions-search.php">Search Transactions</a>
        </li>
      </ul>
    </div>
  </nav>


  <div class="my-3 p-3 bg-white rounded box-shadow">

    <?php
    if ($num > 0) { ?>
      <div class="row">
        <div class="col-md-6">
          <form class="form-inline" action="transactions-search.php">

            <div class="input-group w-100 mb-3">
              <input type="text" class="form-control" name="user" placeholder="User ID" aria-describedby="basic-addon2">
              <div class="input-group-append">
                <button class="btn btn-outline-primary" type="submit">Search</button>
              </div>
            </div>

          </form>
        </div>
        <div class="col-md-6">
          <form class="form-inline" action="transactions-search.php">

            <div class="input-group w-100 mb-3">
              <input type="text" class="form-control" name="transaction" placeholder="Transaction ID" aria-describedby="basic-addon2">
              <div class="input-group-append">
                <button class="btn btn-outline-primary" type="submit">Search</button>
              </div>
            </div>

          </form>
        </div>
      </div>

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
                  <td><?php echo ($trans['callback'] == '1' ? '<span data-toggle="tooltip" data-placement="top" title="Completed" class="badge badge-primary badge-pill"><i class="fa fa-check" aria-hidden="true"></i></span>' : '<span data-toggle="tooltip" data-placement="top" title="Failed" class="badge badge-primary badge-pill"><i class="fa fa-close" aria-hidden="true"></i></span>'); ?></td>
                </tr>
              <?php
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
  </div>

  <br>
  <ul class="pagination justify-content-center">
    <?php
      $back = (($currpage == 1) ? '#' : 'transactions.php?page=' . ($currpage - 1));
      $next = (($currpage == $pages) ? 'transactions.php?page=' . $currpage : 'transactions.php?page=' . ($currpage + 1));
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
    } else {
      echo  "<div class='alert'>No transactions found</div>";
    }
    require_once('includes/footer.php');
?>