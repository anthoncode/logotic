<?php

$pageTitle = 'Categories';
require_once('../system/config-admin.php');
$num = $product->countAllCat();
$num1 = $product->countAllSCat();

$category = $product->get_categories();
$subcategory = $product->get_scategories();

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


    <?php if ($num == 0) {
    ?>
      <h2 align='center'>No Categories Added</h2><br>
    <?php
    } else { ?>
      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>Category ID</th>
            <th>Category Name</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($category as $cat) {
          ?>
            <tr>
              <td><?php echo $cat['id']; ?></td>
              <td><?php echo $cat['name']; ?></td>
              <td>
                <div class="btn-group btn-group-sm" role="group" aria-label="AActions"><a href="edit-category.php?id=<?php echo $cat['id']; ?>" class="btn btn-outline-primary">Edit</a><a href="remove-category.php?id=<?php echo $cat['id']; ?>" class="btn btn-outline-primary">Delete</a></div>
              </td>
            </tr>
          <?php } ?>

        </tbody>
      </table>
    <?php } ?>
    <br>

    <?php if ($num1 == 0) {
    ?>
      <h2 align='center'>No SubCategories Added</h2>
    <?php
    } else { ?>
      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>SubCategory ID</th>
            <th>SubCategory Name</th>
            <th>SubCategory Parent</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($subcategory as $scat) {
            $catname = $product->catdetails($scat['cat_id']);
          ?>
            <tr>
              <td><?php echo $scat['id']; ?></td>
              <td><?php echo $scat['name']; ?></td>
              <td><?php echo $catname['name']; ?></td>
              <td>
                <div class="btn-group btn-group-sm" role="group" aria-label="AActions"><a href="edit-scategory.php?id=<?php echo $scat['id']; ?>" class="btn btn-outline-primary">Edit</a><a href="remove-scategory.php?id=<?php echo $scat['id']; ?>" class="btn btn-outline-primary">Delete</a></div>
              </td>
            </tr>
          <?php } ?>

        </tbody>
      </table>

  </div>



</div>
</div>
<?php
    }
    require_once('includes/footer.php');

?>