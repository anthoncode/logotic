<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Downloads';
$pg = '2';
require_once('../system/config-user.php');
//$sales = $purchases->all($crypt->decrypt($_SESSION['uid'],'USER'));
$sales = $purchases->all($crypt->decrypt($_SESSION['uid'], 'USER')); //class.sale.php
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 5;
$num = count($sales);
$pages = $num / $maxres;
$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
require_once('includes/header.php');
if (isset($_GET['type']) && isset($_GET['msg'])) {
  echo  "<div class=\"alert mt-3 " . $_GET['type'] . "\" style=\"display:block;\">" . $_GET['msg'] . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
} ?>

<div class="alert alert-primary alert-dismissible fade show mb-0 mt-3" role="alert">
  This is your download history in Logotic
</div>

<div class="row">
  <div class="col-4">
    <div class="my-3 p-3 bg-white rounded box-shadow">
      <?php include 'includes/sidenav.php'; ?>
    </div>
  </div>
  <div class="col-8 pl-0">
    <?php if ($num > 0) {
    ?>
      <div class="my-3 p-3 bg-white rounded box-shadow">
        <table class="table">
          <thead>
            <tr>
              <th scope="col">img</th>
              <th scope="col">Product Name</th>
              <th scope="col">Date</th>
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody>
            <?php
            /*for($i = $start; $i <= $last; $i++) {
if(!isset($sales[$i])){
break;
}*/
            //$pro_details= $product->details_downloads($sales[$i]['id']);
            //$prod1 = $product->details($sales[$i]['id']);
            //$prod0 = $product->details($sales[$i]['id']);
            //$prod1 = $sales[$i]['id'];
            //$tran_details= $transaction->details($sales[$i]['transaction_id']);
            foreach ($sales as $salee) {
              if ($salee['products_id'] != '0') {
                $prod = $product->details_downloads($salee['products_id']);
                $prod1 = $prod['name'];
                $prod2 = $prod['description'];
              }
            ?>

              <tr>
                <td class="align-middle"><a href="<?php echo $setting['website_url'] . '/item/' . $sales[$i]['pro_id']; ?>/"><img class="img-fluid rounded" width="80" height="80" src="../system/assets/uploads/vector-files/<?php echo $pro_details['icon_img']; ?>" /></a></td>
                <td class="align-middle"><b><?php echo $prod1; ?></b></td>
                <td class="align-middle"><?php echo $prod2; ?></td>
                <td class="align-middle">
                  <div class="btn-group"><a href="download.php?id=<?php echo $sales[$i]['pro_id']; ?>" class="btn btn-primary">Download <i class="fa fa-cloud-download" aria-hidden="true"></i></a><button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                      <form id="myform" method="post" action="invoice.php">
                        <input type="hidden" name="product-name" value="<?php echo $pro_details['name']; ?>">
                        <input type="hidden" name="product-date" value="<?php echo $sales[$i]['date']; ?>">
                        <input type="hidden" name="product-downloaded" value="<?php echo ($sales[$i]['downloaded'] == '1' ? 'Yes' : 'No'); ?>">
                        <input type="hidden" name="product-price" value="<?php echo $tran_details['amount']; ?>">
                        <button class="dropdown-item" type="submit">Generate Invoice</button>
                      </form>
                    </div>
                  </div>
                </td>
              </tr>

            <?php } ?>
          </tbody>
        </table>
      </div>
      <br>
      <ul class="pagination justify-content-center">
        <?php
        $back = (($currpage == 1) ? '#' : 'downloads.php?page=' . ($currpage - 1));
        $next = (($currpage == $pages) ? 'downloads.php?page=' . $currpage : 'downloads.php?page=' . ($currpage + 1));
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
      echo  "<div class='my-3 p-3 bg-white rounded box-shadow'><div class='alert alert-primary mb-0'>You have not purchased anything!</div></div>";
    } ?>
  </div>

  <?php require_once('includes/footer.php');
  ?>