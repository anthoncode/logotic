<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = "Admin Dashboard";
require_once('../system/config-admin.php');
require_once('../system/gateways.php');

$customer = new Customer($DB_con);

// Stats principales
$totalUsers    = $customer->countAll();
$totalLogos    = $product->countAll();
$totalDownload = $product->countDownload();
$bannedUsers   = $customer->countBanned();

// Stats adicionales
$activeUsers   = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE active = 1")->fetchColumn();
$unverified    = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE verified = 0")->fetchColumn();
$newUsersToday = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE DATE(created) = CURDATE()")->fetchColumn();
$newLogosToday = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE DATE(created) = CURDATE() AND active = 1")->fetchColumn();
$pendingLogos  = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE active = 0")->fetchColumn();
$totalViews    = $DB_con->query("SELECT SUM(views) FROM " . PFX . "products")->fetchColumn() ?? 0;

// Logos recientes
$recentLogos = $DB_con->query("SELECT * FROM " . PFX . "products WHERE active = 1 ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

// Usuarios recientes
$recentUsers = $DB_con->query("SELECT * FROM " . PFX . "users ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Top downloads
$topDownloads = $DB_con->query("
    SELECT p.*, COUNT(d.id) as dl_count
    FROM " . PFX . "products p
    INNER JOIN " . PFX . "downloads d ON p.id = d.products_id
    WHERE p.active = 1
    GROUP BY p.id
    ORDER BY dl_count DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Sitemap info
$sitemapPath = $_SERVER['DOCUMENT_ROOT'] . '/logotic/sitemap.xml';

require_once('includes/header1.php');
?>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-solid fa-gauge-high" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">Dashboard</h1>
            <p class="adm-page-sub">Welcome back — <?php echo date('l, d F Y'); ?></p>
        </div>
    </div>

    <!-- Stats principales -->
    <div class="adm-stats">
        <a href="users.php" class="adm-stat">
            <i class="adm-stat-icon fa-solid fa-users"></i>
            <div class="adm-stat-num"><?php echo number_format($totalUsers); ?></div>
            <div class="adm-stat-label">Total Users</div>
            <div class="adm-stat-link"><i class="fa-regular fa-arrow-right"></i> <?php echo $newUsersToday; ?> today</div>
        </a>
        <a href="products.php" class="adm-stat">
            <i class="adm-stat-icon fa-solid fa-image"></i>
            <div class="adm-stat-num"><?php echo number_format($totalLogos); ?></div>
            <div class="adm-stat-label">Total Logos</div>
            <div class="adm-stat-link"><i class="fa-regular fa-arrow-right"></i> <?php echo $newLogosToday; ?> today</div>
        </a>
        <a href="best-selling-products.php" class="adm-stat">
            <i class="adm-stat-icon fa-solid fa-download"></i>
            <div class="adm-stat-num"><?php echo number_format($totalDownload); ?></div>
            <div class="adm-stat-label">Total Downloads</div>
            <div class="adm-stat-link"><i class="fa-regular fa-arrow-right"></i> View ranking</div>
        </a>
        <a href="pending.php" class="adm-stat">
            <i class="adm-stat-icon fa-solid fa-clock"></i>
            <div class="adm-stat-num" style="color:<?php echo $pendingLogos > 0 ? 'var(--adm-warning)' : 'var(--adm-accent)'; ?>">
                <?php echo number_format($pendingLogos); ?>
            </div>
            <div class="adm-stat-label">Pending Approval</div>
            <div class="adm-stat-link" style="color:<?php echo $pendingLogos > 0 ? 'var(--adm-warning)' : 'var(--adm-accent)'; ?>">
                <i class="fa-regular fa-arrow-right"></i> Review now
            </div>
        </a>
    </div>

    <!-- Stats secundarias -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.3rem;color:var(--adm-success);"><?php echo number_format($activeUsers); ?></div>
            <div class="adm-stat-label">Active Users</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.3rem;color:var(--adm-danger);"><?php echo number_format($bannedUsers); ?></div>
            <div class="adm-stat-label">Banned Users</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.3rem;color:var(--adm-warning);"><?php echo number_format($unverified); ?></div>
            <div class="adm-stat-label">Unverified</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.3rem;color:var(--adm-info);"><?php echo number_format($totalViews); ?></div>
            <div class="adm-stat-label">Total Views</div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="adm-quick-links">
        <a href="products.php" class="adm-quick-link">
            <i class="fa-regular fa-images"></i>
            <span>Logos</span>
            <small>Manage all logos</small>
        </a>
        <a href="users.php" class="adm-quick-link">
            <i class="fa-regular fa-users"></i>
            <span>Users</span>
            <small>Manage users</small>
        </a>
        <a href="pending.php" class="adm-quick-link">
            <i class="fa-regular fa-clock"></i>
            <span>Pending</span>
            <small><?php echo $pendingLogos; ?> waiting</small>
        </a>
        <a href="categories.php" class="adm-quick-link">
            <i class="fa-regular fa-folder"></i>
            <span>Categories</span>
            <small>Manage categories</small>
        </a>
        <a href="generate-sitemap.php" class="adm-quick-link">
            <i class="fa-regular fa-sitemap"></i>
            <span>Sitemap</span>
            <small><?php echo file_exists($sitemapPath) ? 'Last: ' . date('d M', filemtime($sitemapPath)) : 'Not generated'; ?></small>
        </a>
        <a href="settings.php" class="adm-quick-link">
            <i class="fa-regular fa-gear"></i>
            <span>Settings</span>
            <small>Site configuration</small>
        </a>
    </div>

    <!-- Grid: Recent Logos + Top Downloads + Recent Users -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">

        <!-- Recent Logos -->
        <div class="adm-card">
            <div class="adm-card-title">
                <i class="fa-regular fa-clock"></i> Recently Added Logos
                <a href="products.php" style="margin-left:auto;font-size:.72rem;color:var(--adm-accent);text-decoration:none;">View all →</a>
            </div>
            <div class="adm-recent-list">
                <?php foreach ($recentLogos as $logo): ?>
                <a href="edit-product.php?id=<?php echo $logo['id']; ?>" class="adm-recent-item" style="text-decoration:none;">
                    <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $logo['icon_img']; ?>" alt="<?php echo $logo['name']; ?>">
                    <div>
                        <div class="adm-recent-name"><?php echo htmlspecialchars($logo['name']); ?></div>
                        <div class="adm-recent-meta"><?php echo date('d M Y', strtotime($logo['created'])); ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Downloads -->
        <div class="adm-card">
            <div class="adm-card-title">
                <i class="fa-solid fa-fire"></i> Top Downloaded
                <a href="best-selling-products.php" style="margin-left:auto;font-size:.72rem;color:var(--adm-accent);text-decoration:none;">View all →</a>
            </div>
            <div class="adm-recent-list">
                <?php foreach ($topDownloads as $i => $logo): ?>
                <a href="edit-product.php?id=<?php echo $logo['id']; ?>" class="adm-recent-item" style="text-decoration:none;">
                    <div style="width:24px;text-align:center;font-size:.8rem;font-weight:800;color:<?php echo $i < 3 ? 'var(--adm-warning)' : 'var(--adm-muted)'; ?>;">
                        #<?php echo $i + 1; ?>
                    </div>
                    <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $logo['icon_img']; ?>" alt="<?php echo $logo['name']; ?>">
                    <div>
                        <div class="adm-recent-name"><?php echo htmlspecialchars($logo['name']); ?></div>
                        <div class="adm-recent-meta"><i class="fa-regular fa-download"></i> <?php echo number_format($logo['dl_count']); ?> downloads</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Recent Users -->
    <div class="adm-card">
        <div class="adm-card-title">
            <i class="fa-regular fa-user-plus"></i> Recent Registrations
            <a href="users.php" style="margin-left:auto;font-size:.72rem;color:var(--adm-accent);text-decoration:none;">View all →</a>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $avatarColors = ['#e63946','#1d7af3','#2dc653','#f18d35','#8b5cf6','#ec4899','#06b6d4','#f4d03f'];
                    foreach ($recentUsers as $u):
                        $initial  = strtoupper(mb_substr($u['fname'], 0, 1));
                        $color    = $avatarColors[$u['id'] % count($avatarColors)];
                        $isActive = $u['active'] == 1;
                        $isVerified = $u['verified'] == 1;
                    ?>
                    <tr>
                        <td style="color:var(--adm-muted);font-size:.78rem;">#<?php echo $u['id']; ?></td>
                        <td>
                            <div class="adm-user-cell">
                                <div class="adm-avatar" style="background:<?php echo $color; ?>20;color:<?php echo $color; ?>;border:1.5px solid <?php echo $color; ?>40;">
                                    <?php echo $initial; ?>
                                </div>
                                <div>
                                    <div class="adm-user-name"><?php echo htmlspecialchars($u['fname']); ?></div>
                                    <div class="adm-user-username">@<?php echo htmlspecialchars($u['username']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="color:var(--adm-muted);font-size:.82rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td style="color:var(--adm-muted);font-size:.78rem;"><?php echo date('d M Y', strtotime($u['created'])); ?></td>
                        <td>
                            <?php if (!$isActive): ?>
                                <span class="adm-badge adm-badge-banned"><i class="fa-solid fa-ban"></i> Banned</span>
                            <?php elseif (!$isVerified): ?>
                                <span class="adm-badge adm-badge-pending"><i class="fa-regular fa-clock"></i> Unverified</span>
                            <?php else: ?>
                                <span class="adm-badge adm-badge-active"><i class="fa-solid fa-circle-check"></i> Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="adm-actions">
                                <a href="edit-user.php?id=<?php echo $u['username']; ?>" class="adm-btn"><i class="fa-regular fa-pen"></i> Edit</a>
                                <a href="users.php?action=ban&id=<?php echo $u['id']; ?>" class="adm-btn adm-btn-ban" onclick="return confirm('Ban this user?')"><i class="fa-solid fa-ban"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once('includes/footer.php'); ?>