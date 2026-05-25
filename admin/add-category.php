<?php

$pageTitle = 'Add Category';
require_once('../system/config-admin.php');

$category = $product->get_categories();

if (isset($_REQUEST['name'])) {

  $name = trim($_REQUEST['name']);
  $result = $product->addcat($name);
}
if (isset($_REQUEST['subname'])) {

  $name = trim($_REQUEST['subname']);
  $cid = trim($_REQUEST['cat_id']);
  $result = $product->addscat($name, $cid);
}
if (!empty($product->msg)) {
  $success = $product->msg;
}
if (!empty($product->error)) {
  $error = $product->error;
}
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
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/categories.php">All Categories</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-category.php">Add Category</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-categories.php">Deleted Categories</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">


    <div class="bd-example bd-example-tabs">
      <nav class="nav nav-tabs" id="nav-tab" role="tablist">
        <a class="nav-item nav-link active" id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="home" aria-expanded="true">Add Category</a>
        <a class="nav-item nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-profile" role="tab" aria-controls="profile" aria-expanded="false">Add SubCategory</a>
      </nav>
      <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade active show" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" aria-expanded="true">

          <br>
          <form action="add-category.php" method="post" class="form-horizontal">

            <div class="form-group">
              <label>Category Name:</label>
              <input type="text" class="form-control" name="name" id="coupon-code">
            </div>


            <button type="submit" class="btn btn-primary w-100">Add Category</button>
          </form>

        </div>
        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" aria-expanded="false">

          <br>
          <form action="add-category.php" method="post" class="form-horizontal">

            <div class="form-group">
              <label>SubCategory Parent:</label>
              <div class="input-group mb-3">
                <select class="custom-select" name="cat_id" id="cat_id" required="">
                  <option selected="" value="2">Select Parent Category...</option>
                  <?php foreach ($category as $cat) {
                  ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>SubCategory Name:</label>
              <input type="text" class="form-control" name="subname" id="coupon-code">
            </div>


            <button type="submit" class="btn btn-primary w-100">Add SubCategory</button>
          </form>

        </div>
      </div>
    </div>

  </div>
  <?php
  require_once('includes/footer.php');

  ?>