<?php

$pageTitle = 'Deleted Categories';
require_once('../system/config-admin.php');
if (isset($_REQUEST['action'])) {
  switch ($_REQUEST['action']) {
    case 'restore':
      $result = $product->restorecat($_REQUEST['id']);
      break;
    case 'restore-sub':
      $result = $product->restorescat($_REQUEST['id']);
      break;
  }
}

// Pagination
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 20;
$num = $product->countAllCatDeleted();
$pages = $num / $maxres;
$pages = ceil($pages);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
////////////////
$categories = $product->getDeletedCategories($start, $maxres);

$num1 = $product->countAllCatSDeleted();
$subcategories = $product->getDeletedSubCategories();

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
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-category.php">Add Category</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-categories.php">Deleted Categories</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <?php if ($num == 0) {
    ?>
      <h2 align='center'>Nothing Deleted (Categories)...</h2><br>
    <?php
    } else { ?>

      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Category Name</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($categories as $catde) { ?>
            <tr>
              <td><?php echo $catde['id']; ?></td>
              <td><?php echo $catde['name']; ?></td>
              <td><a class="btn btn-outline-primary btn-sm" href="deleted-categories.php?action=restore&id=<?php echo $catde['id']; ?>" title="Reactivate">Restore</a></td>
            </tr>
          <?php
          } ?>

        </tbody>
      </table>

      <br>
      <ul class="pagination justify-content-center">
        <?php
        $back = (($currpage == 1) ? '#' : 'deleted-categories.php?page=' . ($currpage - 1));
        $next = (($currpage == $pages) ? 'deleted-categories.php?page=' . $currpage : 'categories.php?page=' . ($currpage + 1));
        ?>
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == 1) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Previous" href="<?php echo $back; ?>" tabindex="-1"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
        </li>
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == $pages) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Next" href="<?php echo $next; ?>"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
        </li>
      </ul>
      <br>
    <?php
    }
    ?>


    <?php if ($num1 == 0) {
    ?>
      <h2 align='center'>Nothing Deleted (SubCategories)...</h2>
    <?php
    } else { ?>

      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>SubCategory Name</th>
            <th>SubCategory Parent</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($subcategories as $scatde) {
            $catname = $product->catdetails($scatde['cat_id']);
          ?>
            <tr>
              <td><?php echo $scatde['id']; ?></td>
              <td><?php echo $scatde['name']; ?></td>
              <td><?php echo $catname['name']; ?></td>
              <td><a class="btn btn-outline-primary btn-sm" href="deleted-categories.php?action=restore-sub&id=<?php echo $scatde['id']; ?>" title="Reactivate">Restore</a></td>
            </tr>
          <?php
          } ?>

        </tbody>
      </table>
  </div>
</div>

</div>
</div>
<?php
    }

    require_once('includes/footer.php');

?>