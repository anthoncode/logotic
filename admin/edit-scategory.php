<?php

$pageTitle = 'Edit SubCategory';
require_once('../system/config-admin.php');
if (isset($_REQUEST['id'])) {
  if (!empty($_REQUEST['id'])) {
    $details = $product->scatdetails(trim($_REQUEST['id']));
  } else {
    header("location: category.php");
  }
} else {
  $details = $product->scatdetails('0');
}
if (isset($_REQUEST['id']) && isset($_REQUEST['name'])) {

  $id =   trim($_REQUEST['id']);
  $name = trim($_REQUEST['name']);
  $cid = trim($_REQUEST['cat_id']);

  $result = $product->updatescat($id, $name, $cid);
  $details = $product->scatdetails(trim($_REQUEST['id']));
}
if (!empty($product->msg)) {
  $success = $product->msg;
}
if (!empty($product->error)) {
  $error = $product->error;
}

$category = $product->get_categories();

require_once('includes/header1.php');

?>
<div class="content">
  <?php

  if (isset($details)) {
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
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-category.php">Add Category</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-categories.php">Deleted Categories</a>
            </li>
          </ul>
        </div>
      </nav>



      <div class="my-3 p-3 bg-white rounded box-shadow">
        <form action="edit-scategory.php?id=<?php echo $details['id']; ?>" method="post" class="form-horizontal">

          <input type="hidden" class="form-control" name="id" value="<?php echo $details['id']; ?>" disabled>

          <div class="form-group">
            <label>SubCategory Parent:</label>
            <div class="input-group mb-3">
              <select class="custom-select" name="cat_id" id="cat_id" required="">
                <option selected="" value="2">Select Parent Category...</option>
                <option selected value="<?php echo $details['cat_id']; ?>">Change Parent Category...</option>
                <?php foreach ($category as $cat) {
                ?>
                  <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Name:</label>
            <input type="text" class="form-control" name="name" value="<?php echo $details['name']; ?>" id="coupon-code">
          </div>

          <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
      </div>
    <?php } ?>
    </div>
    <?php
    require_once('includes/footer.php');

    ?>