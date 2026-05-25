<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = "Add Credits";
require_once('../system/config-admin.php');
$customer = new Customer($DB_con);
$_SESSION['payment']['use_prepaid'] = false;
if (isset($_REQUEST['id'])) {
  $customerDetails = $customer->details($_REQUEST['id']);
  $error = ($customer->error ? $customer->error : false);

  if (isset($_GET['action']) && $_GET['action'] == 'add') {

    if (!$error) {
      $customer->add_funds($_REQUEST['id'], $_REQUEST['amt']);
      $error = ($customer->error ? $customer->error : false);

      $success = $error ? $error : 'Credits added to user!';
    }
  }
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
    <li class="breadcrumb-item active" aria-current="page">Add Credits</li>
  </ol>
</nav>

<div class="my-3 p-3 bg-white rounded box-shadow">
  <div class="row">
    <div class="col-md-12">
      <?php if (isset($customerDetails)) {
      ?>
        <form class="form-horizontal" id="customerFund" method="POST" action="add-funds.php?action=add">

          <input class="form-control" type="hidden" name="id" value="<?php echo $customerDetails['id']; ?>">

          <div class="form-group">
            <label for="exampleInputPassword1">Current Credits</label>
            <input type="text" class="form-control" id="exampleInputPassword1" name="bal" value="<?php echo $customerDetails['balance']; ?>" disabled>
          </div>

          <div class="form-group">
            <label for="exampleInputEmail1">Credit Amount</label>
            <input type="number" class="form-control" id="exampleInputEmail1" name="amt" aria-describedby="emailHelp" placeholder="Enter Credits">
            <small id="emailHelp" class="form-text text-muted">The user will revieve a notification once you have added credits!</small>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-outline-primary w-100">Add Credits</button>
          </div>
        </form>
      <?php
      }
      ?>
    </div>
  </div>
</div>
<?php
require_once('includes/footer.php');
?>