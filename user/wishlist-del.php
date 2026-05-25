<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Remove item from wishlist';
require_once('../system/config-user.php');

require_once('includes/header.php');

if (isset($_POST['btn-del'])) {
      $id = $_GET['delete_id'];
      $wishlist->delete($id, $crypt->decrypt($_SESSION['uid'], 'USER'));
      header("Location: wishlist-del.php?deleted");
}

?>
<div class="row">
      <div class="col-sm-4">
            <div class="my-3 p-3 bg-white rounded box-shadow">
                  <?php include 'includes/sidenav.php'; ?>
            </div>
      </div>
      <div class="col-sm-8 pl-0">
            <div class="my-3 p-3 bg-white rounded box-shadow">
                  <?php
                  if (isset($_GET['deleted'])) {
                  ?>
                        <div class="alert alert-success mb-0">
                              <strong>Success!</strong> <?php echo $l['itemremovedw'] ?>
                        </div>
                  <?php
                  } else {
                  ?>
                        <div class="alert alert-danger">
                              <strong><?php echo $l['aitemremovedw'] ?></strong>
                        </div>
                  <?php
                  }
                  ?>


                  <?php
                  if (isset($_GET['delete_id'])) {
                  ?>
                        <form method="post">
                              <input type="hidden" name="id" value="" />
                              <button class="btn btn-large btn-primary" type="submit" name="btn-del"><?php echo $l['proceed'] ?></button>
                              <a href="index.php" class="btn btn-large btn-outline-danger"><?php echo $l['nah'] ?></a>
                        </form>
                  <?php
                  } else {
                  }
                  ?>
            </div>
      </div>
<?php
require_once('includes/footer.php');
?>