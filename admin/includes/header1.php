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

    <style>
    /* ── Layout ── */
    *, *::before, *::after { box-sizing: border-box; }

    body {
        margin: 0;
        background: var(--adm-bg);
        color: var(--adm-text);
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 14px;
        display: flex;
        min-height: 100vh;
    }

    /* ── Sidebar ── */
    .adm-sidebar {
        width: 240px;
        flex-shrink: 0;
        background: var(--adm-card);
        border-right: 1px solid var(--adm-border);
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0; left: 0; bottom: 0;
        overflow-y: auto;
        z-index: 100;
        transition: transform .3s;
    }

    .adm-sidebar::-webkit-scrollbar { width: 4px; }
    .adm-sidebar::-webkit-scrollbar-track { background: transparent; }
    .adm-sidebar::-webkit-scrollbar-thumb { background: var(--adm-border); border-radius: 99px; }

    /* Logo */
    .adm-sidebar-logo {
        padding: 1.25rem 1.25rem 1rem;
        border-bottom: 1px solid var(--adm-border);
        display: flex;
        align-items: center;
        gap: .75rem;
        text-decoration: none;
    }

    .adm-sidebar-logo img { height: 28px; object-fit: contain; }
    .adm-sidebar-logo span { font-size: .85rem; font-weight: 700; color: var(--adm-text); }

    .adm-sidebar-badge {
        margin-left: auto;
        font-size: .65rem;
        font-weight: 700;
        background: rgba(212,255,0,.15);
        color: var(--adm-accent);
        border: 1px solid rgba(212,255,0,.2);
        border-radius: 99px;
        padding: 2px 8px;
    }

    /* Nav sections */
    .adm-nav { padding: .75rem 0; flex: 1; }

    .adm-nav-section {
        font-size: .65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--adm-muted);
        padding: .75rem 1.25rem .35rem;
    }

    .adm-nav-item {
        display: flex;
        align-items: center;
        gap: .65rem;
        padding: .55rem 1.25rem;
        color: var(--adm-muted);
        text-decoration: none;
        font-size: .83rem;
        transition: all .15s;
        border-left: 2px solid transparent;
        cursor: pointer;
    }

    .adm-nav-item:hover {
        color: var(--adm-text);
        background: rgba(212,255,0,.04);
        border-left-color: rgba(212,255,0,.3);
    }

    .adm-nav-item.active {
        color: var(--adm-accent);
        background: rgba(212,255,0,.07);
        border-left-color: var(--adm-accent);
        font-weight: 600;
    }

    .adm-nav-item i { width: 16px; text-align: center; font-size: .85rem; flex-shrink: 0; }

    .adm-nav-badge {
        margin-left: auto;
        font-size: .65rem;
        font-weight: 700;
        background: var(--adm-warning);
        color: #0d0f1c;
        border-radius: 99px;
        padding: 2px 7px;
        min-width: 20px;
        text-align: center;
    }

    .adm-nav-badge-soon {
        margin-left: auto;
        font-size: .62rem;
        font-weight: 700;
        background: rgba(6,182,212,.15);
        color: #06b6d4;
        border: 1px solid rgba(6,182,212,.2);
        border-radius: 99px;
        padding: 1px 7px;
    }

    /* Submenu */
    .adm-submenu { display: none; }
    .adm-submenu.open { display: block; }

    .adm-submenu .adm-nav-item {
        padding-left: 2.75rem;
        font-size: .8rem;
    }

    .adm-nav-item.has-sub::after {
        content: '\f078';
        font-family: 'Font Awesome 6 Pro', 'Font Awesome 6 Free';
        font-weight: 900;
        font-size: .6rem;
        margin-left: auto;
        color: var(--adm-muted);
        transition: transform .2s;
    }

    .adm-nav-item.has-sub.open::after { transform: rotate(180deg); }

    /* Sidebar footer */
    .adm-sidebar-footer {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--adm-border);
    }

    .adm-sidebar-footer a {
        display: flex;
        align-items: center;
        gap: .65rem;
        color: var(--adm-muted);
        text-decoration: none;
        font-size: .82rem;
        padding: .4rem 0;
        transition: color .15s;
    }

    .adm-sidebar-footer a:hover { color: var(--adm-danger); }

    /* ── Topbar ── */
    .adm-topbar {
        position: fixed;
        top: 0; left: 240px; right: 0;
        height: 56px;
        background: rgba(13,15,28,.9);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--adm-border);
        display: flex;
        align-items: center;
        padding: 0 1.5rem;
        gap: 1rem;
        z-index: 99;
    }

    .adm-topbar-title {
        font-size: .9rem;
        font-weight: 700;
        color: var(--adm-text);
        flex: 1;
    }

    .adm-topbar-actions { display: flex; align-items: center; gap: .75rem; }

    .adm-topbar-btn {
        display: flex;
        align-items: center;
        gap: .5rem;
        background: rgba(255,255,255,.05);
        border: 1px solid var(--adm-border);
        border-radius: 99px;
        color: var(--adm-muted);
        font-size: .78rem;
        padding: .35rem .9rem;
        text-decoration: none;
        transition: all .15s;
        cursor: pointer;
    }

    .adm-topbar-btn:hover { color: var(--adm-text); border-color: var(--adm-text); }

    .adm-topbar-btn.primary {
        background: var(--adm-accent);
        color: #0d0f1c;
        border-color: var(--adm-accent);
        font-weight: 700;
    }

    .adm-topbar-btn.primary:hover { background: #bfe600; }

    /* ── Main content ── */
    .adm-main {
        margin-left: 240px;
        margin-top: 56px;
        flex: 1;
        min-height: calc(100vh - 56px);
        background: var(--adm-bg);
    }

    /* ── Mobile toggle ── */
    .adm-menu-toggle {
        display: none;
        background: transparent;
        border: 1px solid var(--adm-border);
        border-radius: 8px;
        color: var(--adm-text);
        width: 36px; height: 36px;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: .9rem;
    }

    /* ── Alerts in topbar area ── */
    .adm-main .alert { border-radius: 10px; margin: 1rem 1.5rem 0; }

    @media (max-width: 768px) {
        .adm-sidebar { transform: translateX(-100%); }
        .adm-sidebar.open { transform: translateX(0); }
        .adm-topbar { left: 0; }
        .adm-main { margin-left: 0; }
        .adm-menu-toggle { display: flex; }
    }
    </style>

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

        <a href="<?php echo $setting['website_url']; ?>/admin/generate-sitemap.php" class="adm-nav-item <?php echo $currentPage === 'generate-sitemap.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-sitemap"></i> Generate Sitemap
        </a>

        <a href="<?php echo $setting['website_url']; ?>/admin/extract-colors.php" class="adm-nav-item <?php echo $currentPage === 'extract-colors.php' ? 'active' : ''; ?>">
            <i class="fa-solid fa-palette"></i> Extract Colors
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