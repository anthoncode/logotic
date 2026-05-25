<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);




$pageTitle = 'Overview';
$pg = '1';
require_once('../system/config-user.php');
require_once('includes/header.php');
//$num  = $transaction->countAll('user_id',$crypt->decrypt($_SESSION['uid'],'USER'));
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 5;
//$pages = $num / $maxres;
//$pages = ceil($pages);
//$start = ( $currpage - 1 ) * $maxres ;
//$last = $start + $maxres -1;
//$transactions = $transaction->getUserTransactions($crypt->decrypt($_SESSION['uid'],'USER'),$start,$maxres);

?>



<div class="mt-3 p-3 bg-white rounded box-shadow">
  <!-- <div class="row">
        <div class="col">
          <div class="widget-small primary coloured-icon"><i class="icon fa fa-money"></i>
            <div class="info">
              <h4>Current Credits</h4>
              <h5><b><span class="badge badge-pill badge-primary"><?php echo $setting['currency_sym'] . $userDetails['balance']; ?></span></b></h5>
            </div>
          </div>
        </div>
                <div class="col">
          <div class="widget-small primary coloured-icon"><i class="icon fa fa-gift"></i>
            <div class="info">
              <h4>Wishlist Items</h4>
              <h5><b><span class="badge badge-pill badge-primary"><?php echo $wishcount; ?></span></b></h5>
            </div>
          </div>
        </div>
        <div class="col">
          <div class="widget-small danger coloured-icon"><i class="icon fa fa-shopping-cart"></i>
            <div class="info">
              <h4>Total Purchases</h4>
              <h5><b><span class="badge badge-pill badge-primary"><?php echo $userDetails['purchases']; ?></span></b></h5>
            </div>
          </div>
        </div>
      </div>
   </div> -->

  <div class="row">
    <div class="col-4">

      <div class="my-3 p-3 bg-white rounded box-shadow">

        <?php include 'includes/sidenav.php'; ?>

        <!--                   <div class="card mt-3">
                    <div class="card-body">
                        <h5><i class="fa fa-trophy mr-3"></i>Achievements<br></h5>
                        <hr>
                        <ul class="list-inline">
                           <?php if ($userDetails['active'] == 1) { ?> <li class="list-inline-item"><i class="fa fa-certificate" aria-hidden="true"></i> Pioneer</li><?php } ?>
                           <?php if ($userDetails['verified'] == 1) { ?> <li class="list-inline-item"><i class="fa fa-user-md" aria-hidden="true"></i> Became an Author</li><?php } ?>
                           <?php if ($userDetails['allow_email'] == 1) { ?> <li class="list-inline-item"><i class="fa fa-newspaper-o" aria-hidden="true"></i> Newsletter Squad</li><?php } ?>
                           <?php if ($userDetails['moderator'] == 1) { ?> <li class="list-inline-item"><i class="fa fa-user-secret" aria-hidden="true"></i> Staff Moderator</li><?php } ?>
                           <?php if ($userDetails['purchases'] >= 1 && $userDetails['purchases'] <= 10) { ?> <li class="list-inline-item"><i class="fa fa-cart-plus" aria-hidden="true"></i> Buyer Level 1</li><?php } ?>
                           <?php if ($userDetails['purchases'] >= 11 && $userDetails['purchases'] <= 30) { ?> <li class="list-inline-item"><i class="fa fa-cart-plus" aria-hidden="true"></i> Buyer Level 2</li><?php } ?>
                           <?php if ($userDetails['purchases'] >= 31 && $userDetails['purchases'] <= 100) { ?> <li class="list-inline-item"><i class="fa fa-cart-plus" aria-hidden="true"></i> Buyer Level 3 (max level)</li><?php } ?>
                        </ul>
                    </div>
                </div>   -->

      </div>

    </div>
    <div class="col-8 pl-0">

      <div class="my-3 p-3 bg-white rounded box-shadow">
        <h4 class="pb-2 mb-2">Dashboard</h4>


        <div class="row">
          <div class="col-sm-12" id="resetDummyData" style="display: none;">
            <div class="callout callout-info">
              <h4>Hola</h4>
              <p>Esta es la versión <code>DEMO</code> de <strong>Lesson PRO</strong>, explora las nuevas novedades de esta nueva plataforma.</p>
            </div>
          </div>


          <div class="col-md-6 col-xs-6">
            <div class="small-box ">
              <a class="small-box-footer bg-orange-dark" href="#">
                <div class="icon bg-orange-dark" style="padding: 9.5px 18px 8px 18px;">
                  <i class="fa-solid fa-arrow-up-from-arc"></i>
                </div>
                <div class="inner ">
                  <h3 class="text-white"><?php 
                  //$id = $_GET['id'];
                  //echo $id;
                  $num = $product->countUpload($crypt->decrypt($_SESSION['uid'],'USER')); echo $num;?></h3>
                  <p class="text-white">
                    Uploads </p>
                </div>
              </a>
            </div>
          </div>
          <div class="col-md-6 col-xs-6">
            <div class="small-box ">
              <a class="small-box-footer bg-teal-light" href="#">
                <div class="icon bg-teal-light" style="padding: 9.5px 18px 8px 18px;">
                  <i class="fa-solid fa-arrow-down-to-arc"></i>
                </div>
                <div class="inner ">
                  <h3 class="text-white">0</h3>
                  <p class="text-white">
                    Downloads </p>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>



    </div>
  </div>


  <?php
  require_once('includes/footer.php');
  ?>