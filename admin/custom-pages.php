<?php

$pageTitle = 'Custom Pages';
require_once('../system/config-admin.php');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
$num = $pages->countAll();
$cpages = $pages->get_all_pages();
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
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/custom-pages.php">All Custom Pages</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-page.php">Add Custom Page</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-pages.php">Deleted Custom Pages</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <?php if ($num == 0) {
    ?>
      <h2 align='center'>No Custom Pages Added</h2>
    <?php
    } else { ?>

      <table class="table table-hover table-striped table-bordered">
        <thead>
          <tr>
            <th>Title</th>
            <th>Rules</th>
            <th>Date Added</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($cpages as $cpag) {
          ?>
            <tr>
              <td><a href="" class="header"><?php echo $cpag['title'] ?></a></td>
              <td><?php echo ($cpag['level'] == '1' ? '<span class="badge badge-primary badge-pill">Logged in Users Only</span>' : '<span class="badge badge-primary badge-pill">Visible to all</span>'); ?></td>
              <td>
                <p><?php echo $cpag['indate'] ?></p>
              </td>
              <td>
                <p><?php echo ($cpag['active'] == '1' ? '<span class="badge badge-primary badge-pill">Published</span>' : '<span class="badge badge-warning badge-pill">Draft</span>'); ?></p>
              </td>
              <td>
                <div class="btn-group btn-group-sm" role="group" aria-label="AActions"><a href="<?php echo $setting['website_url']; ?>/page/<?php echo $cpag['id']; ?>/" class="btn btn-outline-primary">View</a><a href="edit-page.php?id=<?php echo $cpag['id']; ?>" class="btn btn-outline-primary">Edit</a><a href="remove-page.php?id=<?php echo $cpag['id']; ?>" class="btn btn-outline-primary">Delete</a></div>
              </td>
            </tr>
          <?php } ?>

        </tbody>
      </table>

    <?php
    }
    require_once('includes/footer.php');

    ?>