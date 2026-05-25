<?php

$pageTitle = 'Deleted Pages';
require_once('../system/config-admin.php');
if (isset($_REQUEST['action'])) {
  switch ($_REQUEST['action']) {
    case 'restore':
      $result = $pages->restore($_REQUEST['id']);
      break;
  }
}

// Pagination
$currpage = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres = 20;
$num = $pages->countAllDeleted();
$pages1 = $num / $maxres;
$pages1 = ceil($pages1);
$start = ($currpage - 1) * $maxres;
$last = $start + $maxres - 1;
////////////////
$news = $pages->getDeletedPages($start, $maxres);

if (!empty($pages->msg)) {
  $success = $pages->msg;
}
if (!empty($pages->error)) {
  $error = $pages->error;
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
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/custom-pages.php">All Custom Pages</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-page.php">Add Custom Page</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-pages.php">Deleted Custom Pages</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <?php if ($num == 0) {
    ?>
      <h2 align='center'>Nothing Deleted...</h2>
    <?php
    } else { ?>

      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>Title</th>
            <th>Content</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($news as $newsll) { ?>
            <tr>
              <td><?php echo $newsll['title']; ?></td>
              <td><?php echo $newsll['content']; ?></td>
              <td><?php echo $newsll['indate']; ?></td>
              <td><a class="btn btn-outline-primary btn-sm" href="deleted-pages.php?action=restore&id=<?php echo $newsll['id']; ?>" title="Reactivate Custom Page">Restore</a></td>
            </tr>
          <?php
          } ?>

        </tbody>
      </table>
  </div>
  <br>
  <ul class="pagination justify-content-center">
    <?php
      $back = (($currpage == 1) ? '#' : 'deleted-news.php?page=' . ($currpage - 1));
      $next = (($currpage == $pages1) ? 'deleted-news.php?page=' . $currpage : 'news.php?page=' . ($currpage + 1));
    ?>
    <li class="page-item">
      <a class="page-link" <?php echo ($currpage == 1) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Previous" href="<?php echo $back; ?>" tabindex="-1"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
    </li>
    <li class="page-item">
      <a class="page-link" <?php echo ($currpage == $pages1) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Next" href="<?php echo $next; ?>"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
    </li>
  </ul>

</div>
</div>
<?php
    }
    require_once('includes/footer.php');

?>