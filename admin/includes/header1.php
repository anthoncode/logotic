<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $pageTitle . " | " . $setting['site_name']; ?></title>
         <link rel="icon" type="image/png" href="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>">
        <script src="<?php echo $setting['website_url']; ?>/admin/css/jquery.min.js"></script>
        <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/admin/css/dropzone.min.js"></script>
        <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/admin/css/app-zone.js"></script>
        <link href="<?php echo $setting['website_url']; ?>/system/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css">

        <link href="<?php echo $setting['website_url']; ?>/admin/css/sweetalert2.min.css" rel="stylesheet" type="text/css">

        <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/admin/css/dropzone.min.css">

        <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/system/assets/css/all.min.css">

        <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/admin/css/bootstrap.bundle.min.js"></script>

        <link type="text/css" href="<?php echo $setting['website_url']; ?>/admin/css/theme.css" rel="stylesheet">
        <link type="text/css" href="<?php echo $setting['website_url']; ?>/admin/css/bootstrap-tagsinput.css" rel="stylesheet">

        <script defer src="<?php echo $setting['website_url']; ?>/admin/css/bootstrap-tagsinput.js"></script>
        <script defer src="<?php echo $setting['website_url']; ?>/admin/css/custom_tags_input.js"></script>
        <script>
            $(document).ready(function() {

                $('[data-toggle="tooltip"]').tooltip();

            });
        </script>
        <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/admin/css/toastr.min.css">
        <script src="<?php echo $setting['website_url']; ?>/admin/css/toastr.min.js"></script>
        
    </head>

<body>
    <?php if ($auth->is_loggedin()) {
    ?>
        <div class="d-flex flex-column flex-md-row align-items-center p-3 px-md-4 mb-3 bg-white border-bottom box-shadow">
            <h5 class="my-0 mr-md-auto font-weight-normal"><a href="<?php echo $setting['website_url']; ?>"><?php if (empty($setting['site_logo'])) {?><?php echo $setting['site_name']; ?><?php } else {?><img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_logo']; ?>" width="" height="30"><?php }?></a></h5>
            <nav class="my-2 my-md-0 mr-md-3">
                <a class="p-2 text-dark" target="_blank" href="<?php echo $setting['website_url']; ?>"><i class="fa-regular fa-globe"></i> Website</a>
            </nav>
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Hi, Admin
                </button>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="<?php echo $setting['website_url']; ?>/admin/account.php">Account</a>
                    <a class="dropdown-item" href="<?php echo $setting['website_url']; ?>/admin/login.php?logout">Sign Out</a>
                </div>
            </div>
        </div>
        <!-- /navbar -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-3">
                    <div class="my-3 sidebar">
                        <ul class="widget widget-menu unstyled">
                            <li class="active"><a href="<?php echo $setting['website_url']; ?>/admin/"><i class="menu-icon icon-dashboard"></i>Dashboard
                                </a></li>
                            <li><a class="collapsed" data-toggle="collapse" href="#toggleProducts"><i class="menu-icon"></i>Products </a>
                                <ul id="toggleProducts" class="collapse unstyled">
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/add-product.php"><i class="icon-inbox"></i>Add Logo</a></li>

                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/all-logos.php"><i class="icon-inbox"></i>All logos</a></li>

                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/best-selling-products.php"><i class="icon-inbox"></i>Top downloads </a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/duplicate-logos.php"><i class="icon-inbox"></i>Duplicate logos </a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/all-category.php"><i class="icon-inbox"></i>All categories</a></li>
                                </ul>
                            </li>

                            <li>
                                <a href="<?php echo $setting['website_url']; ?>/admin/pending.php"><i class="menu-icon icon-signout"></i>Pending 
                                        <?php $allPending = $product->countPending();
                                        if ($allPending) {
                                            echo "<b class='label bg-warning pull-right'> $allPending </b>";
                                        }
                                        ?>
                                </a>
                            </li>

                            <li><a class="collapsed" data-toggle="collapse" href="#toggleCustomers"><i class="menu-icon"></i>Users</a>
                                <ul id="toggleCustomers" class="collapse unstyled">
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/users.php"><i class="icon-inbox"></i>All Users</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/banned-users.php"><i class="icon-inbox"></i>Banned Users</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/top-buyers.php"><i class="icon-inbox"></i>Top Buyers</a></li>
                                    <!-- <li><a href="<?php echo $setting['website_url']; ?>/admin/add-user.php"><i class="icon-inbox"></i>Add User</a></li>-->
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/send-mail.php"><i class="icon-inbox"></i>Send Newsletter</a></li>
                                </ul>
                            </li>
                            <li><a class="collapsed" data-toggle="collapse" href="#toggleCoupons"><i class="menu-icon"></i>Coupons</a>
                                <ul id="toggleCoupons" class="collapse unstyled">
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/coupons.php"><i class="icon-inbox"></i>All Coupons</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/add-coupon.php"><i class="icon-inbox"></i>Add Coupon</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/deleted-coupons.php"><i class="icon-inbox"></i>Deleted Coupons</a></li>
                                </ul>
                            </li>
                            <li><a href="<?php echo $setting['website_url']; ?>/admin/transactions.php"><i class="menu-icon icon-signout"></i>Transactions</a></li>
                        </ul>
                        <!--/.widget-nav-->


                        <ul class="widget widget-menu unstyled">
                            <li><a class="collapsed" data-toggle="collapse" href="#toggleCat"><i class="menu-icon"></i>Categories</a>
                                <ul id="toggleCat" class="collapse unstyled">
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/categories.php"><i class="icon-inbox"></i>All Categories</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/add-category.php"><i class="icon-inbox"></i>Add Category</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/deleted-categories.php"><i class="icon-inbox"></i>Deleted Categories</a></li>
                                </ul>
                            </li>
                            <li><a class="collapsed" data-toggle="collapse" href="#toggleCus"><i class="menu-icon"></i>Custom Pages</a>
                                <ul id="toggleCus" class="collapse unstyled">
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/custom-pages.php"><i class="icon-inbox"></i>All Custom Pages</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/add-page.php"><i class="icon-inbox"></i>Add Custom Page</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/deleted-pages.php"><i class="icon-inbox"></i>Deleted Custom Pages</a></li>
                                </ul>
                            </li>
                            <li><a class="collapsed" data-toggle="collapse" href="#toggleNews"><i class="menu-icon"></i>News</a>
                                <ul id="toggleNews" class="collapse unstyled">
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/news.php"><i class="icon-inbox"></i>All News</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/add-news.php"><i class="icon-inbox"></i>Add News</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/deleted-news.php"><i class="icon-inbox"></i>Deleted News</a></li>
                                </ul>
                            </li>
                            <li><a class="collapsed" data-toggle="collapse" href="#toggleSupport"><i class="menu-icon"></i>Support <b class="label bg-primary pull-right text-white">
                                        Soon!</b></a>
                                <ul id="toggleSupport" class="collapse unstyled">
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/all-tickets.php"><i class="icon-inbox"></i>All Tickets</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/open-tickets.php"><i class="icon-inbox"></i>Open Tickets</a></li>
                                    <li><a href="<?php echo $setting['website_url']; ?>/admin/closed-tickets.php"><i class="icon-inbox"></i>Closed Tickets</a></li>
                                </ul>
                            </li>
                            <li><a class="collapsed" data-toggle="collapse" href="#toggleForum"><i class="menu-icon"></i>Forum <b class="label bg-primary pull-right text-white">
                                        Soon!</b></a>
                                <ul id="toggleForum" class="collapse unstyled">
                                    <li><a href="#"><i class="icon-inbox"></i>Visit Forum</a></li>
                                    <li><a href="#"><i class="icon-inbox"></i>Forum Admin</a></li>
                                </ul>
                            </li>
                            <li><a class="collapsed" data-toggle="collapse" href="#toggleBlog"><i class="menu-icon"></i>Blog <b class="label bg-primary pull-right text-white">
                                        Soon!</b></a>
                                <ul id="toggleBlog" class="collapse unstyled">
                                    <li><a href="#"><i class="icon-inbox"></i>Visit Blog</a></li>
                                    <li><a href="#"><i class="icon-inbox"></i>All Posts</a></li>
                                    <li><a href="#"><i class="icon-inbox"></i>Add Post</a></li>
                                    <li><a href="#"><i class="icon-inbox"></i>Deleted Posts</a></li>
                                </ul>
                            </li>
                            <!--  <li><a class="collapsed" data-toggle="collapse" href="#toggleComments"><i class="menu-icon"></i>Comments <b class="label bg-primary pull-right text-white">
                                    Soon!</b></a>
                                    <ul id="toggleComments" class="collapse unstyled">
                                        <li><a href="#"><i class="icon-inbox"></i>All Comments</a></li>
                                        <li><a href="#"><i class="icon-inbox"></i>Pending Comments</a></li>
                                        <li><a href="#"><i class="icon-inbox"></i>Deleted Comments</a></li>
                                    </ul>
                                </li>-->
                        </ul>
                        <!--/.widget-nav-->
                        <ul class="widget widget-menu unstyled">
                            <li><a href="<?php echo $setting['website_url']; ?>/admin/settings.php"><i class="menu-icon icon-signout"></i>Settings</a></li>
                            <li><a href="<?php echo $setting['website_url']; ?>/admin/login.php?logout"><i class="menu-icon icon-signout"></i>Sign Out</a></li>
                        </ul>
                    </div>
                    <!--/.sidebar-->
                </div>
                <!--/.span3-->

                <div class="col-sm-9">

                    <div class="jumbotron p-3 p-md-5 my-3 text-white rounded bg-dark box-shadow">
                        <div class="col-md-12 px-0">
                            <h1 class="display-4 text-white text-center"><?php echo $pageTitle; ?></h1>
                        </div>
                    </div>
                    <?php
if (isset($success)) {
        echo "<div class=\"alert alert-success\" style=\"display:block;\">" . $success . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div><br>";
    }
    if (isset($error)) {
        echo "<div class=\"alert alert-danger\" style=\"display:block;\">" . $error . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div><br>";
    }

    ?>
                <?php } else {?>

                <?php }?>