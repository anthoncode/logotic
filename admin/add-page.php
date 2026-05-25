<?php

$pageTitle = 'Add Custom Page';
require_once('../system/config-admin.php');

if (isset($_REQUEST['title']) && isset($_REQUEST['content']) && isset($_REQUEST['level'])) {

  $title = trim($_REQUEST['title']);
  $slug = trim($_REQUEST['title']);
  $content = trim($_REQUEST['content']);
  $level = trim($_REQUEST['level']);

  $result = $pages->add($title, $slug, $content, $level);
}
if (!empty($pages->msg)) {
  $success = $pages->msg;
}
if (!empty($pages->error)) {
  $error = $pages->error;
}
require_once('includes/header1.php');

?>
<script src="//cdnjs.cloudflare.com/ajax/libs/tinymce/4.6.5/tinymce.min.js"></script>
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
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-page.php">Add Custom Page</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/deleted-pages.php">Deleted Custom Pages</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <form action="add-page.php" method="post" class="form-horizontal">

      <div class="form-group">
        <label>Title:</label>
        <input type="text" class="form-control" name="title" id="coupon-code">
      </div>

      <div class="form-group">
        <label>Content:</label>
        <textarea type="text" class="form-control" name="content" id="coupon-code"></textarea>
      </div>

      <div class="form-group"> <label>Page Access Settings:</label>
        <div class="input-group mb-3"> <select class="custom-select" name="level" required="">
            <option value="1">Must be logged in to access</option>
            <option value="0">Visible to all</option>
          </select></div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Add Custom Page</button>
    </form>

  </div>
  <script type="text/javascript">
    tinymce.init({
      selector: "textarea",
      themes: "modern",
      branding: false,
      plugins: [
        'advlist autolink lists link image charmap preview',
        'visualblocks code',
        'insertdatetime media contextmenu paste code'
      ],
      toolbar: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code'
    });
  </script>
  <?php
  require_once('includes/footer.php');

  ?>