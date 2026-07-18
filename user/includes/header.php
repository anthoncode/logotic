<!doctype html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/system/assets/js/jquery.min.js"></script>
  <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/admin/css/dropzone.min.js"></script>
  <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/admin/css/dropzone.min.css">
  <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/admin/css/app-zone.js"></script>
  <link rel="icon" type="image/x-icon" href="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>">
  <link href="<?php echo $setting['website_url']; ?>/system/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">
  <!--<script src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>-->
  <link href="<?php echo $setting['website_url']; ?>/system/assets/css/all.min.css" rel="stylesheet" type="text/css">
  <link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/styles.css">
  <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/admin/css/bootstrap.bundle.min.js"></script>
  <link type="text/css" href="<?php echo $setting['website_url']; ?>/admin/css/bootstrap-tagsinput.css" rel="stylesheet">

  <script defer src="<?php echo $setting['website_url']; ?>/admin/css/bootstrap-tagsinput.js"></script>
  <script defer src="<?php echo $setting['website_url']; ?>/admin/css/custom_tags_input.js"></script>
  <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/system/assets/js/bootstrap.bundle.min.js"></script>
  
  <title><?php echo $pageTitle . " | " . $setting['site_name']; ?></title>

  <script>
    $(document).ready(function() {
      $('.js-open').on('click', function() {
        var target = $(this).attr('data-target');
        $(target).toggleClass('is-visible');
      });

      $('.js-close').on('click', function() {
        $(this).parent().removeClass('is-visible');
      });

      $('[data-toggle="tooltip"]').tooltip();

    });
  </script>

  <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/admin/css/toastr.min.css">
  <script src="<?php echo $setting['website_url']; ?>/admin/css/toastr.min.js"></script>
</head>

<body>



  <header class="blog-header py-3 bg-white">
    <div class="container">
      <div class="row flex-nowrap justify-content-between align-items-center">

        <div class="col-4 text-center">
          <a class="text-dark" href="<?php echo $setting['website_url']; ?>"><?php if (empty($setting['site_logo'])) { ?><?php echo $setting['site_name']; ?><?php } else { ?><img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_logo']; ?>" width="" height="30"><?php } ?></a>
        </div>
        <div class="col-4 d-flex justify-content-end align-items-center">
          <?php if ($user->is_loggedin()) { ?>
            <div class="dropdown">
              <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Hi, <?php echo $userDetails['fname']; ?>
              </button>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                <a class="dropdown-item" href="<?php echo $setting['website_url']; ?>/user/">Overview</span></a>
                <a class="dropdown-item" href="<?php echo $setting['website_url']; ?>/user/downloads.php">Downloads</a>
                <a class="dropdown-item" href="<?php echo $setting['website_url']; ?>/user/login.php?logout">Sign Out</a>
              </div>
            </div> <?php } else { ?>

            <button class="btn btn-sm btn-outline-primary" type="button">
              Login
            </button>

          <?php } ?>

        </div>
      </div>
    </div>
  </header>

  <?php if ($user->is_loggedin()) { ?>
    <div class="nav-scroller bg-white box-shadow justify-content-md-center">
      <nav class="nav nav-underline justify-content-md-center">
        <a class="nav-link <?php echo $pg == '1' ? 'active' : NULL ?>" href="<?php echo $setting['website_url']; ?>/user/">Overview</a>


        <!-- <a class="nav-link <?php echo $pg == '2' ? 'active' : NULL ?>" href="<?php echo $setting['website_url']; ?>/user/downloads.php">Downloads</a>

        <a class="nav-link <?php echo $pg == '3' ? 'active' : NULL ?>" href="<?php echo $setting['website_url']; ?>/user/favorites.php">Favorites <span class="badge badge-pill badge-primary align-text-bottom"><?php echo $wishcount; ?></span></a> -->

        <a class="nav-link <?php echo $pg == '4' ? 'active' : NULL ?>" href="<?php echo $setting['website_url']; ?>/user/account.php">My Account</a>

        <a class="nav-link <?php echo $pg == '5' ? 'active' : NULL ?>" href="<?php echo $setting['website_url']; ?>/user/upload-logo.php">Upload</a>
      </nav>
    </div> <?php } ?>



  <main role="main" class="container">
    <h4 class="text-left mt-3"><?php echo $pageTitle; ?></h4>