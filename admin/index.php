<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = "Admin Dashboard";
require_once('../system/config-admin.php');
require_once('../system/gateways.php');
$customer = new Customer($DB_con);
$allU = $customer->countAll();
$allPro = $product->countAll();
$allB = $customer->countBanned();
$allDownload = $product->countDownload();
//$allC = $coupon->countAll();
//$allS = $sale->countAll();
//$tmade = $transaction->AddAll();

$maxres = 5;
//$num  = $transaction->countAll();

$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;

//$pages = $num / $maxres;
//$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
//$transactions = $transaction->getTransactions($start, $maxres);

$top = $customer->newUsers();

require_once('includes/header1.php');
?>
<div class="content">
   <div class="btn-controls">
      <div class="row my-3">
         <div class="col-xl-3 col-sm-6">
            <div class="card text-white bg-primary o-hidden h-100">
               <div class="card-body">
                  <div class="card-body-icon"> <i class="fa-solid fa-user fa-fw"></i></div>
                  <div class="mr-5"><strong><?php echo $allU; ?></strong><br> Customers</div>
               </div>
               <a class="card-footer text-white clearfix small z-1" href="<?php echo $setting['website_url']; ?>/admin/users.php"> <span class="float-left">View Customers</span> <span class="float-right"> <i class="fa fa-angle-right"></i> </span> </a>
            </div>
         </div>


         <div class="col-xl-3 col-sm-6">
            <div class="card text-white bg-primary o-hidden h-100">
               <div class="card-body">
                  <div class="card-body-icon"> <i class="fa-solid fa-download fa-fw"></i> </div>
                  <div class="mr-5 text-white"><b><?php echo $allDownload; ?></b><br> Total Download</div>
               </div>
               <a class="card-footer text-white clearfix small z-1" href="<?php echo $setting['website_url']; ?>/admin/best-selling-products.php"> <span class="float-left">View downloads</span> <span class="float-right"> <i class="fa fa-angle-right"></i> </span> </a>
            </div>
         </div>

         <div class="col-xl-3 col-sm-6">
            <div class="card text-white bg-primary o-hidden h-100">
               <div class="card-body">
                  <div class="card-body-icon"> <i class="fa-solid fa-files fa-fw"></i> </div>
                  <div class="mr-5"><strong><?php echo $allPro; ?></strong><br> Vector Files</div>
               </div>
               <a class="card-footer text-white clearfix small z-1" href="<?php echo $setting['website_url']; ?>/admin/products.php"> <span class="float-left">File Vector</span> <span class="float-right"> <i class="fa fa-angle-right"></i> </span> </a>
            </div>
         </div>

         <div class="col-xl-3 col-sm-6">
            <div class="card text-white bg-primary o-hidden h-100">
               <div class="card-body">
                  <div class="card-body-icon"> <i class="fa-solid fa-spinner fa-fw"></i> </div>
                  <div class="mr-5"><strong><?php echo "load"; ?></strong><br> Pending</div>
               </div>
               <a class="card-footer text-white clearfix small z-1" href="<?php echo $setting['website_url']; ?>/admin/pending.php"> <span class="float-left">View pending</span> <span class="float-right"> <i class="fa fa-angle-right"></i> </span> </a>
            </div>
         </div>
      </div>

      

      <!--/#btn-controls-->
      <div class="module box-shadow">
         <!-- <div class="module-head">
            <h3>
               Recent Transactions</h3>
         </div> -->
         <!-- <div class="module-body">
            <div class="table-responsive">
               <table class="table table-striped table-hover table-bordered">
                  <thead>
                     <tr>

                        <th scope="col">ID</th>
                        <th scope="col">Date</th>
                        <th>User ID</th>
                        <th scope="col">Details</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>

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
                           <td><?php echo $trans['id']; ?></td>
                           <td><?php echo $trans['date']; ?></td>
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
         </div> -->
      </div>
      <!--/.module-->
      <div class="module hide">
         <div class="module-head">
            <h3>
               Recent New Customers</h3>
         </div>
         <div class="module-body">
            <div class="table">
               <table class="table table-hover table-striped table-bordered">
                  <thead>
                     <tr>
                        <th>User ID</th>
                        <th>User Email</th>

                        <th class="hidden-phone">Purchases</th>
                        <th class="hidden-phone">Credits</th>
                        <th>Actions</th>
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

   </div>
   <!--/.content-->
</div>
<!--/.span9-->
</div>
</div>
<!--/.container-->
</div>
<!--/.wrapper-->
<div class="footer">
   <div class="container">
      <b class="copyright">Copyright &copy; <?php echo date("Y"); ?> <a href="https://logotic.me" target="_blank">Logotic</a> </b>All rights reserved.
   </div>
</div>

</body>


</html>