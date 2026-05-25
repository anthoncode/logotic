<?php

$pageTitle = 'Edit News';
require_once('../system/config-admin.php');
if (isset($_REQUEST['id'])) {
  if (!empty($_REQUEST['id'])) {
    $details = $newsl->details(trim($_REQUEST['id']));
  } else {
    header("location: news.php");
  }
} else {
  $details = $newsl->details('0');
}
if (isset($_REQUEST['id']) && isset($_REQUEST['title']) && isset($_REQUEST['content'])) {

  $id =   trim($_REQUEST['id']);
  $title = trim($_REQUEST['title']);
  $content = trim($_REQUEST['content']);

  $result = $newsl->updatenews($id, $title, $content);
  $details = $newsl->details(trim($_REQUEST['id']));
}
if (!empty($newsl->msg)) {
  $success = $newsl->msg;
}
if (!empty($newsl->error)) {
  $error = $newsl->error;
}
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
              <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/news.php">All News</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-news.php">Add News</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-news.php">Deleted News</a>
            </li>
          </ul>
        </div>
      </nav>



      <div class="my-3 p-3 bg-white rounded box-shadow">
        <form action="edit-news.php?id=<?php echo $details['id']; ?>" method="post" class="form-horizontal">

          <input type="hidden" class="form-control" name="id" value="<?php echo $details['id']; ?>">

          <div class="form-group">
            <label>Title:</label>
            <input type="text" class="form-control" name="title" value="<?php echo $details['title']; ?>" id="coupon-code">
          </div>
          <div class="form-group"> <label>Content:</label> <textarea type="text" class="form-control" name="content" id="coupon-code"><?php echo $details['content']; ?></textarea></div>

          <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>
      </div>
    <?php } ?>
    </div>
    <?php
    require_once('includes/footer.php');

    ?>