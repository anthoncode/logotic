<?php
$pageTitle = "Top downloads";
require_once('../system/config-admin.php');
$topProducts = $product->top();
$num = count($topProducts);
require_once('includes/header1.php');
?>
<nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/products.php">All logos</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-product.php">Add Logo</a>
      </li>
      <li class="nav-item active">
        <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/best-selling-products.php">Top</a>
      </li>
    </ul>
  </div>
</nav>

<div class="my-3 p-3 bg-white rounded box-shadow">
  <?php if ($num > 0) { ?>
    <table class="table table-hover table-striped table-bordered">
      <thead>
        <tr>
          <th>Product Name</th>
          <th>Downloads</th>
          <th>Views</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($topProducts as $pro) {
          $ip_item = $pro['id'];
          $download = $product->downloadCount($ip_item);
        ?>
          <tr>
            <td><?php echo $pro['name']; ?></td>
            <td><?php echo $download['doCount']; ?></td>
            <td><?php echo $pro['views']; ?></td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  <?php
  } else {
    echo  "<div class='alert alert-primary'>No Products added yet</div>";
  }
  require_once('includes/footer.php');
  ?>