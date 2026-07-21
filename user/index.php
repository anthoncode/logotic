<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Overview';
$pg = '1';

require_once('../system/config-user.php');

$uid = $crypt->decrypt($_SESSION['uid'], 'USER');

// ── Métricas ──
$dlStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "downloads WHERE user_id = :uid");
$dlStmt->execute([':uid' => $uid]);
$totalDownloads = $dlStmt->fetchColumn();

$favStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE user_id = :uid");
$favStmt->execute([':uid' => $uid]);
$totalFavs = $favStmt->fetchColumn();

$totalUploads = $product->countUpload($uid);
$isContributor = $totalUploads > 0;

// Métricas de contribuidor
$contribViews = 0;
$contribDownloads = 0;
if ($isContributor) {
    $vStmt = $DB_con->prepare("SELECT COALESCE(SUM(views),0) FROM " . PFX . "products WHERE submit_user_id = :uid");
    $vStmt->execute([':uid' => $uid]);
    $contribViews = $vStmt->fetchColumn();

    $cdStmt = $DB_con->prepare("
        SELECT COUNT(*) FROM " . PFX . "downloads d
        INNER JOIN " . PFX . "products p ON d.products_id = p.id
        WHERE p.submit_user_id = :uid
    ");
    $cdStmt->execute([':uid' => $uid]);
    $contribDownloads = $cdStmt->fetchColumn();
}

// ── Descargas recientes ──
$recentStmt = $DB_con->prepare("
    SELECT p.id, p.name, p.slug_lg, p.icon_img, d.date_created
    FROM " . PFX . "downloads d
    INNER JOIN " . PFX . "products p ON d.products_id = p.id
    WHERE d.user_id = :uid
    ORDER BY d.date_created DESC
    LIMIT 6
");
$recentStmt->execute([':uid' => $uid]);
$recentDownloads = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// "Miembro desde"
$memberSince = (new DateTime($userDetails['created']))->format('F Y');

require_once('includes/header.php');
?>


<!-- Page head -->
<div class="user-panel-head">
    <h1>Welcome back, <?php echo htmlspecialchars($userDetails['fname']); ?> 👋</h1>
    <p>Member since <?php echo $memberSince; ?> · <?php echo htmlspecialchars($userDetails['email']); ?></p>
</div>

<style>
/* Dashboard stats */
.dash-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.dash-stat {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 1.25rem;
    position: relative;
    overflow: hidden;
    transition: var(--transition);
}

.dash-stat:hover { border-color: rgba(212,255,0,.3); transform: translateY(-2px); }

.dash-stat-icon {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    margin-bottom: .75rem;
}

.dash-stat-num {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1;
}

.dash-stat-label {
    font-size: .78rem;
    color: var(--text-muted);
    margin-top: .3rem;
}

/* Quick actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: .75rem;
    margin-bottom: 1.5rem;
}

.quick-action {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 1.1rem;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: .75rem;
    transition: var(--transition);
}

.quick-action:hover {
    border-color: var(--accent);
    background: rgba(212,255,0,.04);
}

.quick-action-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: rgba(212,255,0,.1);
    color: var(--accent);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.quick-action-text {
    font-size: .85rem;
    font-weight: 600;
    color: var(--text-primary);
}

/* Recent downloads grid */
.recent-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 1rem;
}

.recent-item {
    text-decoration: none;
    text-align: center;
    transition: var(--transition);
}

.recent-item:hover { transform: translateY(-3px); }

.recent-item-img {
    width: 100%;
    aspect-ratio: 1;
    background: #fff;
    border-radius: 12px;
    padding: 10px;
    object-fit: contain;
    border: 1px solid var(--border);
    margin-bottom: .4rem;
}

.recent-item-name {
    font-size: .72rem;
    color: var(--text-secondary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dash-empty {
    text-align: center;
    padding: 2.5rem 1rem;
    color: var(--text-muted);
}

.dash-empty i { font-size: 2.5rem; color: var(--border); display: block; margin-bottom: .75rem; }

.dash-empty a { color: var(--accent); text-decoration: none; }
</style>

<!-- Stats -->
<div class="dash-stats">
    <div class="dash-stat">
        <div class="dash-stat-icon" style="background:rgba(212,255,0,.12);color:var(--accent);">
            <i class="fa-solid fa-download"></i>
        </div>
        <div class="dash-stat-num"><?php echo number_format($totalDownloads); ?></div>
        <div class="dash-stat-label">Logos Downloaded</div>
    </div>

    <div class="dash-stat">
        <div class="dash-stat-icon" style="background:rgba(255,77,77,.12);color:#ff4d4d;">
            <i class="fa-solid fa-heart"></i>
        </div>
        <div class="dash-stat-num"><?php echo number_format($totalFavs); ?></div>
        <div class="dash-stat-label">Favorites Saved</div>
    </div>

    <?php if ($isContributor): ?>
    <div class="dash-stat">
        <div class="dash-stat-icon" style="background:rgba(29,122,243,.12);color:#1d7af3;">
            <i class="fa-solid fa-images"></i>
        </div>
        <div class="dash-stat-num"><?php echo number_format($totalUploads); ?></div>
        <div class="dash-stat-label">Logos Published</div>
    </div>

    <div class="dash-stat">
        <div class="dash-stat-icon" style="background:rgba(45,198,83,.12);color:#2dc653;">
            <i class="fa-solid fa-eye"></i>
        </div>
        <div class="dash-stat-num"><?php echo number_format($contribViews); ?></div>
        <div class="dash-stat-label">Total Views</div>
    </div>

    <div class="dash-stat">
        <div class="dash-stat-icon" style="background:rgba(244,208,63,.12);color:#f4d03f;">
            <i class="fa-solid fa-cloud-arrow-down"></i>
        </div>
        <div class="dash-stat-num"><?php echo number_format($contribDownloads); ?></div>
        <div class="dash-stat-label">Downloads of Your Logos</div>
    </div>
    <?php endif; ?>
</div>

<!-- Quick actions -->
<div class="up-card-title" style="margin-bottom:.75rem;">
    <i class="fa-solid fa-bolt"></i> Quick Actions
</div>
<div class="quick-actions">
    <a href="<?php echo $setting['website_url']; ?>/user/downloads.php" class="quick-action">
        <div class="quick-action-icon"><i class="fa-regular fa-download"></i></div>
        <div class="quick-action-text">My Downloads</div>
    </a>
    <a href="<?php echo $setting['website_url']; ?>/user/favorites.php" class="quick-action">
        <div class="quick-action-icon"><i class="fa-regular fa-heart"></i></div>
        <div class="quick-action-text">My Favorites</div>
    </a>
    <a href="<?php echo $setting['website_url']; ?>/user/upload-logo.php" class="quick-action">
        <div class="quick-action-icon"><i class="fa-regular fa-cloud-arrow-up"></i></div>
        <div class="quick-action-text">Upload Logo</div>
    </a>
    <a href="<?php echo $setting['website_url']; ?>/user/edit-profile.php" class="quick-action">
        <div class="quick-action-icon"><i class="fa-regular fa-user-pen"></i></div>
        <div class="quick-action-text">Edit Profile</div>
    </a>
</div>

<!-- Recent downloads -->
<div class="up-card">
    <div class="up-card-title">
        <i class="fa-regular fa-clock-rotate-left"></i> Recent Downloads
    </div>

    <?php if (empty($recentDownloads)): ?>
        <div class="dash-empty">
            <i class="fa-regular fa-download"></i>
            <p>You haven't downloaded any logos yet.</p>
            <a href="<?php echo $setting['website_url']; ?>">Browse logos →</a>
        </div>
    <?php else: ?>
        <div class="recent-grid">
            <?php foreach ($recentDownloads as $r): ?>
                <a href="<?php echo $setting['website_url'] . '/item/' . $r['id'] . '/' . $r['slug_lg'] . '/'; ?>"
                   class="recent-item" title="<?php echo htmlspecialchars($r['name']); ?>">
                    <img class="recent-item-img"
                         src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $r['icon_img']; ?>"
                         alt="<?php echo htmlspecialchars($r['name']); ?>">
                    <div class="recent-item-name"><?php echo htmlspecialchars($r['name']); ?></div>
                </a>
            <?php endforeach; ?>
        </div>
        <div style="text-align:center;margin-top:1.25rem;">
            <a href="<?php echo $setting['website_url']; ?>/user/downloads.php"
               style="color:var(--accent);text-decoration:none;font-size:.85rem;font-weight:600;">
                View all downloads <i class="fa-regular fa-arrow-right"></i>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once('includes/footer.php'); ?>