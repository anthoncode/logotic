<?php

$pageTitle = 'Add News';
require_once('../system/config-admin.php');

if (isset($_REQUEST['title']) && isset($_REQUEST['content'])) {

  $title = trim($_REQUEST['title']);
  $content = trim($_REQUEST['content']);
  $result = $newsl->add($title, $content);
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

  <nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/news.php">All News</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-news.php">Add News</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-news.php">Deleted News</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <form action="add-news.php" method="post" class="form-horizontal">

      <div class="form-group">
        <label>Title:</label>
        <input type="text" class="form-control" name="title" id="coupon-code">
      </div>

      <div class="form-group">
        <label>Content:</label>
        <textarea type="text" class="form-control" name="content" id="coupon-code"></textarea>
      </div>

      <button type="submit" class="btn btn-primary w-100">Add News</button>
    </form>

  </div>
  <?php
  require_once('includes/footer.php');

  ?>