<?php

$pageTitle = 'News Management';
require_once('../system/config-admin.php');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
$num = $newsl->countAll();
$news = $newsl->get_all_news();
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
    <?php if ($num == 0) {
    ?>
      <h2 align='center'>No News Added</h2>
    <?php
    } else { ?>

      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>Title</th>
            <th>Content</th>
            <th>Date Posted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($news as $newsl) {
          ?>
            <tr>
              <td><a href="" class="header"><?php echo $newsl['title'] ?></a></td>
              <td><?php echo $newsl['content'] ?></td>
              <td>
                <p><?php echo $newsl['date'] ?></p>
              </td>
              <td>
                <div class="btn-group btn-group-sm" role="group" aria-label="AActions"><a href="edit-news.php?id=<?php echo $newsl['id']; ?>" class="btn btn-outline-primary">Edit</a><a href="remove-news.php?id=<?php echo $newsl['id']; ?>" class="btn btn-outline-primary">Delete</a></div>
              </td>
            </tr>
          <?php } ?>

        </tbody>
      </table>

    <?php
    }
    require_once('includes/footer.php');

    ?>