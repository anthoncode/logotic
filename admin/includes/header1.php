<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle . " | " . $setting['site_name']; ?> — Admin</title>
    <link rel="icon" type="image/png" href="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>">

    <!-- Core -->
    <script src="<?php echo $setting['website_url']; ?>/admin/css/jquery.min.js"></script>
    <link href="<?php echo $setting['website_url']; ?>/system/assets/css/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $setting['website_url']; ?>/admin/css/bootstrap.bundle.min.js"></script>

    <!-- Plugins -->
    <script src="<?php echo $setting['website_url']; ?>/admin/css/dropzone.min.js"></script>
    <script src="<?php echo $setting['website_url']; ?>/admin/css/app-zone.js"></script>
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/admin/css/dropzone.min.css">
    <link href="<?php echo $setting['website_url']; ?>/admin/css/sweetalert2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/system/assets/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/admin/css/toastr.min.css">
    <script src="<?php echo $setting['website_url']; ?>/admin/css/toastr.min.js"></script>
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/admin/css/bootstrap-tagsinput.css">
    <script defer src="<?php echo $setting['website_url']; ?>/admin/css/bootstrap-tagsinput.js"></script>
    <script defer src="<?php echo $setting['website_url']; ?>/admin/css/custom_tags_input.js"></script>

    <!-- Admin Design System -->
    <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/admin/css/logotic.admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
    </script>
</head>

<body>
<?php if ($auth->is_loggedin()): ?>

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$allPending  = $product->countPending();
?>

<!-- ── Sidebar ── -->
<aside class="adm-sidebar" id="admSidebar">

    <a class="adm-sidebar-logo" href="<?php echo $setting['website_url']; ?>/admin/">
        <?php if (!empty($setting['site_logo'])): ?>
            <img src="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_logo']; ?>" alt="Logo">
        <?php else: ?>
            <span><?php echo $setting['site_name']; ?></span>
        <?php endif; ?>
        <span class="adm-sidebar-badge">Admin</span>
    </a>

    <nav class="adm-nav">

        <!-- Main -->
        <div class="adm-nav-section">Main</div>

        <a href="<?php echo $setting['website_url']; ?>/admin/" class="adm-nav-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-gauge-high"></i> Dashboard
        </a>

        <!-- Logos -->
        <div class="adm-nav-section">Logos</div>

        <div class="adm-nav-item has-sub <?php echo in_array($currentPage, ['add-product.php','all-logos.php','best-selling-products.php','duplicate-logos.php']) ? 'open active' : ''; ?>" onclick="toggleSub(this, 'subLogos')">
            <i class="fa-regular fa-images"></i> Logos
        </div>
        <div class="adm-submenu <?php echo in_array($currentPage, ['add-product.php','all-logos.php','best-selling-products.php','duplicate-logos.php']) ? 'open' : ''; ?>" id="subLogos">
            <a href="<?php echo $setting['website_url']; ?>/admin/add-product.php" class="adm-nav-item <?php echo $currentPage === 'add-product.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-plus"></i> Add Logo
            </a>
            <a href="<?php echo $setting['website_url']; ?>/admin/all-logos.php" class="adm-nav-item <?php echo $currentPage === 'all-logos.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-list"></i> All Logos
            </a>
            <a href="<?php echo $setting['website_url']; ?>/admin/best-selling-products.php" class="adm-nav-item <?php echo $currentPage === 'best-selling-products.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-fire"></i> Top Downloads
            </a>
            <a href="<?php echo $setting['website_url']; ?>/admin/duplicate-logos.php" class="adm-nav-item <?php echo $currentPage === 'duplicate-logos.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-copy"></i> Duplicates
            </a>
        </div>

        <a href="<?php echo $setting['website_url']; ?>/admin/pending.php" class="adm-nav-item <?php echo $currentPage === 'pending.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-clock"></i> Pending
            <?php if ($allPending > 0): ?>
                <span class="adm-nav-badge"><?php echo $allPending; ?></span>
            <?php endif; ?>
        </a>

        <!-- Categories -->
        <div class="adm-nav-section">Categories</div>

        <div class="adm-nav-item has-sub <?php echo in_array($currentPage, ['categories.php','add-category.php','all-category.php']) ? 'open active' : ''; ?>" onclick="toggleSub(this, 'subCats')">
            <i class="fa-regular fa-folder"></i> Categories
        </div>
        <div class="adm-submenu <?php echo in_array($currentPage, ['categories.php','add-category.php','all-category.php']) ? 'open' : ''; ?>" id="subCats">
            <a href="<?php echo $setting['website_url']; ?>/admin/add-category.php" class="adm-nav-item <?php echo $currentPage === 'add-category.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-plus"></i> Add Category
            </a>
        </div>

        <!-- Users -->
        <div class="adm-nav-section">Users</div>

        <div class="adm-nav-item has-sub <?php echo in_array($currentPage, ['users.php','banned-users.php','send-mail.php']) ? 'open active' : ''; ?>" onclick="toggleSub(this, 'subUsers')">
            <i class="fa-regular fa-users"></i> Users
        </div>
        <div class="adm-submenu <?php echo in_array($currentPage, ['users.php','banned-users.php','send-mail.php']) ? 'open' : ''; ?>" id="subUsers">
            <a href="<?php echo $setting['website_url']; ?>/admin/users.php" class="adm-nav-item <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-list"></i> All Users
            </a>
            <a href="<?php echo $setting['website_url']; ?>/admin/send-mail.php" class="adm-nav-item <?php echo $currentPage === 'send-mail.php' ? 'active' : ''; ?>">
                <i class="fa-regular fa-envelope"></i> Newsletter
            </a>
        </div>

        <!-- Content -->
        <div class="adm-nav-section">Content</div>

        <div class="adm-nav-item has-sub <?php echo in_array($currentPage, ['all-pages.php','new-page.php']) ? 'open active' : ''; ?>" onclick="toggleSub(this, 'subPages')">
            <i class="fa-regular fa-file"></i> Custom Pages
        </div>
        <div class="adm-submenu <?php echo in_array($currentPage, ['all-pages.php','new-page.php']) ? 'open' : ''; ?>" id="subPages">
            <a href="<?php echo $setting['website_url']; ?>/admin/all-pages.php" class="adm-nav-item">
                <i class="fa-regular fa-list"></i> All Pages
            </a>
            <a href="<?php echo $setting['website_url']; ?>/admin/new-page.php" class="adm-nav-item">
                <i class="fa-regular fa-plus"></i> Add Page
            </a>
        </div>

        <div class="adm-nav-item has-sub" onclick="toggleSub(this, 'subNews')">
            <i class="fa-regular fa-newspaper"></i> News
        </div>
        <div class="adm-submenu" id="subNews">
            <a href="<?php echo $setting['website_url']; ?>/admin/news.php" class="adm-nav-item">
                <i class="fa-regular fa-list"></i> All News
            </a>
            <a href="<?php echo $setting['website_url']; ?>/admin/add-news.php" class="adm-nav-item">
                <i class="fa-regular fa-plus"></i> Add News
            </a>
        </div>
        <div class="adm-nav-item has-sub" onclick="toggleSub(this, 'subBlog')">
            <i class="fa-regular fa-rss"></i> Blog
        </div>
        <div class="adm-submenu" id="subBlog">
            <a href="<?php echo $setting['website_url']; ?>/admin/all-posts.php" class="adm-nav-item">
                <i class="fa-regular fa-list"></i> All Posts
            </a>
            <a href="<?php echo $setting['website_url']; ?>/admin/new-post.php" class="adm-nav-item">
                <i class="fa-regular fa-plus"></i> Add Post
            </a>
            <a href="<?php echo $setting['website_url']; ?>/admin/post-categories.php" class="adm-nav-item">
                <i class="fa-regular fa-plus"></i> Add Categories
            </a>
        </div>
 

        <!-- Tools -->
        <div class="adm-nav-section">Tools</div>
        <a href="<?php echo $setting['website_url']; ?>/admin/search-insights.php" class="adm-nav-item <?php echo $currentPage === 'search-insights.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-magnifying-glass"></i> Search Insights

        <a href="<?php echo $setting['website_url']; ?>/admin/generate-sitemap.php" class="adm-nav-item <?php echo $currentPage === 'generate-sitemap.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-sitemap"></i> Generate Sitemap
        </a>

        <a href="<?php echo $setting['website_url']; ?>/admin/extract-colors.php" class="adm-nav-item <?php echo $currentPage === 'extract-colors.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-palette"></i> Extract Colors
        </a>

        <a href="<?php echo $setting['website_url']; ?>/admin/error-logs.php" class="adm-nav-item <?php echo $currentPage === 'error-logs.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-bug"></i> Error logs
        </a>

        <div class="adm-nav-item has-sub" onclick="toggleSub(this, 'subSoon')">
            <i class="fa-regular fa-comment-question"></i> Support
            <span class="adm-nav-badge-soon">Soon</span>
        </div>
        <div class="adm-submenu" id="subSoon">
            <a href="#" class="adm-nav-item">
                <i class="fa-regular fa-ticket"></i> All Tickets
            </a>
        </div>

    </nav>

    <div class="adm-sidebar-footer">
        <a href="<?php echo $setting['website_url']; ?>/admin/settings.php">
            <i class="fa-regular fa-gear"></i> Settings
        </a>
        <a href="<?php echo $setting['website_url']; ?>/admin/account.php">
            <i class="fa-regular fa-circle-user"></i> My Account
        </a>
        <a href="<?php echo $setting['website_url']; ?>/admin/login.php?logout" style="color:var(--adm-danger) !important;">
            <i class="fa-regular fa-arrow-right-from-bracket"></i> Sign Out
        </a>
    </div>

</aside>

<!-- ── Topbar ── -->
<div class="adm-topbar">
    <button class="adm-menu-toggle" id="menuToggle">
        <i class="fa-regular fa-bars"></i>
    </button>
    <div class="adm-topbar-title"><?php echo $pageTitle; ?></div>
    <div class="adm-topbar-actions">
        <?php if ($allPending > 0): ?>
        <a href="<?php echo $setting['website_url']; ?>/admin/pending.php" class="adm-topbar-btn" style="border-color:rgba(244,208,63,.4);color:var(--adm-warning);">
            <i class="fa-regular fa-clock"></i> <?php echo $allPending; ?> pending
        </a>
        <?php endif; ?>
        <a href="<?php echo $setting['website_url']; ?>" target="_blank" class="adm-topbar-btn">
            <i class="fa-regular fa-globe"></i> Site
        </a>
        <a href="<?php echo $setting['website_url']; ?>/admin/login.php?logout" class="adm-topbar-btn">
            <i class="fa-regular fa-arrow-right-from-bracket"></i> Sign Out
        </a>
    </div>
</div>

<!-- ── Main Content ── -->
<main class="adm-main">

<?php if (isset($success)): ?>
    <div class="adm-alert adm-alert-success" style="margin:1rem 1.5rem 0;">
        <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
    </div>
<?php endif; ?>

<?php if (isset($error)): ?>
    <div class="adm-alert adm-alert-error" style="margin:1rem 1.5rem 0;">
        <i class="fa-solid fa-circle-xmark"></i> <?php echo $error; ?>
    </div>
<?php endif; ?>

<script>
// Submenus
function toggleSub(el, id) {
    const sub = document.getElementById(id);
    const isOpen = sub.classList.contains('open');
    el.classList.toggle('open', !isOpen);
    sub.classList.toggle('open', !isOpen);
}

// Mobile sidebar
document.getElementById('menuToggle')?.addEventListener('click', function() {
    document.getElementById('admSidebar').classList.toggle('open');
});

// Cerrar sidebar al hacer clic fuera en móvil
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('admSidebar');
    const toggle  = document.getElementById('menuToggle');
    if (window.innerWidth <= 768 && !sidebar.contains(e.target) && !toggle.contains(e.target)) {
        sidebar.classList.remove('open');
    }
});


</script>

<?php else: ?>
    <script>window.location.href = '<?php echo $setting['website_url']; ?>/admin/login.php';</script>
<?php endif; ?>