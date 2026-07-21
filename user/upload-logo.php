<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'My Logos';
$pg = '4';

require_once('../system/config-user.php');

$uid = $crypt->decrypt($_SESSION['uid'], 'USER');

$currpage = max(1, (int)($_GET['page'] ?? 1));
$maxres   = 18;
$start    = ($currpage - 1) * $maxres;

$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "products WHERE submit_user_id = :uid");
$countStmt->execute([':uid' => $uid]);
$total = $countStmt->fetchColumn();
$pages = ceil($total / $maxres);

$stmt = $DB_con->prepare("
    SELECT id, name, slug_lg, icon_img, views, active
    FROM " . PFX . "products
    WHERE submit_user_id = :uid
    ORDER BY id DESC
    LIMIT :start, :maxres
");
$stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':maxres', $maxres, PDO::PARAM_INT);
$stmt->execute();
$logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once('includes/header.php');
?>

<div class="user-panel-head">
    <h1>My Logos</h1>
    <p><?php echo number_format($total); ?> logo<?php echo $total !== 1 ? 's' : ''; ?> published</p>
</div>

<style>
.logo-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:1rem; }
.mylogo-card {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:var(--radius-card); padding:1rem; text-align:center;
    text-decoration:none; transition:var(--transition); position:relative;
}
.mylogo-card:hover { border-color:rgba(212,255,0,.3); transform:translateY(-3px); }
.mylogo-img {
    width:100%; aspect-ratio:1; background:#fff;
    border-radius:10px; padding:12px; object-fit:contain; margin-bottom:.6rem;
}
.mylogo-name {
    font-size:.8rem; font-weight:600; color:var(--text-primary);
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.mylogo-meta {
    font-size:.7rem; color:var(--text-muted); margin-top:.3rem;
    display:flex; align-items:center; justify-content:center; gap:.75rem;
}
.mylogo-status {
    position:absolute; top:.5rem; left:.5rem;
    font-size:.62rem; font-weight:700; text-transform:uppercase;
    border-radius:6px; padding:2px 7px;
}
.status-active { background:rgba(45,198,83,.15); color:#2dc653; }
.status-pending { background:rgba(244,208,63,.15); color:#f4d03f; }

.up-empty { text-align:center; padding:4rem 2rem; color:var(--text-muted); background:var(--bg-card); border:1px solid var(--border); border-radius:var(--radius-card); }
.up-empty i { font-size:3rem; color:var(--border); display:block; margin-bottom:1rem; }
.up-empty a { color:var(--accent); text-decoration:none; }
.up-pagination { display:flex; justify-content:center; gap:.4rem; margin-top:2rem; }
.up-page-btn { background:var(--bg-card); border:1px solid var(--border); color:var(--text-muted); border-radius:8px; padding:.5rem 1rem; font-size:.82rem; text-decoration:none; transition:var(--transition); }
.up-page-btn:hover, .up-page-btn.active { background:var(--accent); color:#0d0f1c; border-color:var(--accent); font-weight:700; }
</style>

<?php if (empty($logos)): ?>
    <div class="up-empty">
        <i class="fa-regular fa-images"></i>
        <p>You haven't published any logos yet.</p>
        <a href="<?php echo $setting['website_url']; ?>/user/upload-logo.php">Upload your first logo →</a>
    </div>
<?php else: ?>
    <div class="logo-grid">
        <?php foreach ($logos as $l): ?>
            <a href="<?php echo $setting['website_url'] . '/item/' . $l['id'] . '/' . $l['slug_lg'] . '/'; ?>"
               class="mylogo-card" title="<?php echo htmlspecialchars($l['name']); ?>">
                <span class="mylogo-status <?php echo $l['active'] == 1 ? 'status-active' : 'status-pending'; ?>">
                    <?php echo $l['active'] == 1 ? 'Live' : 'Pending'; ?>
                </span>
                <img class="mylogo-img"
                     src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $l['icon_img']; ?>"
                     alt="<?php echo htmlspecialchars($l['name']); ?>">
                <div class="mylogo-name"><?php echo htmlspecialchars($l['name']); ?></div>
                <div class="mylogo-meta">
                    <span><i class="fa-regular fa-eye"></i> <?php echo number_format($l['views']); ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
    <div class="up-pagination">
        <?php if ($currpage > 1): ?><a href="?page=<?php echo $currpage-1; ?>" class="up-page-btn">←</a><?php endif; ?>
        <?php for ($i=1;$i<=$pages;$i++): ?>
            <a href="?page=<?php echo $i; ?>" class="up-page-btn <?php echo $i==$currpage?'active':''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($currpage < $pages): ?><a href="?page=<?php echo $currpage+1; ?>" class="up-page-btn">→</a><?php endif; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once('includes/footer.php'); ?>