<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'My Favorites';
$pg = '3';

require_once('../system/config-user.php');

$uid = $crypt->decrypt($_SESSION['uid'], 'USER');

// Quitar de favoritos
if (isset($_GET['remove'])) {
    $removeId = (int)$_GET['remove'];
    $del = $DB_con->prepare("DELETE FROM " . PFX . "wishlists WHERE user_id = :uid AND product_id = :pid");
    $del->execute([':uid' => $uid, ':pid' => $removeId]);
    header('Location: favorites.php?msg=removed');
    exit;
}

// Paginación
$currpage = max(1, (int)($_GET['page'] ?? 1));
$maxres   = 18;
$start    = ($currpage - 1) * $maxres;

// Total
$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE user_id = :uid");
$countStmt->execute([':uid' => $uid]);
$total = $countStmt->fetchColumn();
$pages = ceil($total / $maxres);

// Favoritos
$stmt = $DB_con->prepare("
    SELECT p.id, p.name, p.slug_lg, p.icon_img, w.w_id
    FROM " . PFX . "wishlists w
    INNER JOIN " . PFX . "products p ON w.product_id = p.id
    WHERE w.user_id = :uid
    ORDER BY w.w_id DESC
    LIMIT :start, :maxres
");
$stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':maxres', $maxres, PDO::PARAM_INT);
$stmt->execute();
$favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once('includes/header.php');
?>

<div class="user-panel-head">
    <h1>My Favorites</h1>
    <p><?php echo number_format($total); ?> logo<?php echo $total !== 1 ? 's' : ''; ?> saved</p>
</div>

<style>
.logo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 1rem;
}
.logo-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 1rem;
    text-align: center;
    transition: var(--transition);
    position: relative;
}
.logo-card:hover { border-color: rgba(212,255,0,.3); transform: translateY(-3px); }
.logo-card-link { text-decoration: none; display: block; }
.logo-card-img {
    width: 100%;
    aspect-ratio: 1;
    background: #fff;
    border-radius: 10px;
    padding: 12px;
    object-fit: contain;
    margin-bottom: .6rem;
}
.logo-card-name {
    font-size: .8rem;
    font-weight: 600;
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.fav-remove {
    position: absolute;
    top: .5rem; right: .5rem;
    width: 28px; height: 28px;
    background: rgba(13,15,28,.85);
    border: 1px solid var(--border);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ff4d4d;
    font-size: .75rem;
    text-decoration: none;
    opacity: 0;
    transition: all .2s;
    z-index: 2;
}
.logo-card:hover .fav-remove { opacity: 1; }
.fav-remove:hover { background: #ff4d4d; color: #fff; }

.up-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
}
.up-empty i { font-size: 3rem; color: var(--border); display: block; margin-bottom: 1rem; }
.up-empty a { color: var(--accent); text-decoration: none; }

.up-pagination { display: flex; justify-content: center; gap: .4rem; margin-top: 2rem; }
.up-page-btn {
    background: var(--bg-card); border: 1px solid var(--border);
    color: var(--text-muted); border-radius: 8px;
    padding: .5rem 1rem; font-size: .82rem;
    text-decoration: none; transition: var(--transition);
}
.up-page-btn:hover, .up-page-btn.active {
    background: var(--accent); color: #0d0f1c;
    border-color: var(--accent); font-weight: 700;
}

.up-toast {
    background: rgba(45,198,83,.1);
    border: 1px solid rgba(45,198,83,.3);
    color: #2dc653;
    border-radius: 10px;
    padding: .75rem 1rem;
    font-size: .82rem;
    margin-bottom: 1rem;
}
</style>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'removed'): ?>
    <div class="up-toast">
        <i class="fa-solid fa-circle-check"></i> Logo removed from your favorites.
    </div>
<?php endif; ?>

<?php if (empty($favorites)): ?>
    <div class="up-empty">
        <i class="fa-regular fa-heart"></i>
        <p>You haven't saved any favorites yet.</p>
        <a href="<?php echo $setting['website_url']; ?>">Discover logos to save →</a>
    </div>
<?php else: ?>
    <div class="logo-grid">
        <?php foreach ($favorites as $f): ?>
            <div class="logo-card">
                <a href="favorites.php?remove=<?php echo $f['id']; ?>"
                   class="fav-remove"
                   title="Remove from favorites"
                   onclick="return confirm('Remove this logo from favorites?')">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <a href="<?php echo $setting['website_url'] . '/item/' . $f['id'] . '/' . $f['slug_lg'] . '/'; ?>"
                   class="logo-card-link" title="<?php echo htmlspecialchars($f['name']); ?>">
                    <img class="logo-card-img"
                         src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $f['icon_img']; ?>"
                         alt="<?php echo htmlspecialchars($f['name']); ?>">
                    <div class="logo-card-name"><?php echo htmlspecialchars($f['name']); ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
    <div class="up-pagination">
        <?php if ($currpage > 1): ?>
            <a href="?page=<?php echo $currpage - 1; ?>" class="up-page-btn">←</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="up-page-btn <?php echo $i == $currpage ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
        <?php if ($currpage < $pages): ?>
            <a href="?page=<?php echo $currpage + 1; ?>" class="up-page-btn">→</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once('includes/footer.php'); ?>