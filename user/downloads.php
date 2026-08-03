<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'My Downloads';
$pg = '2';

require_once('../system/config-user.php');

$uid = $crypt->decrypt($_SESSION['uid'], 'USER');

// Paginación
$currpage = max(1, (int)($_GET['page'] ?? 1));
$maxres   = 18;
$start    = ($currpage - 1) * $maxres;

// Total (logos únicos descargados, no repeticiones)
$countStmt = $DB_con->prepare("SELECT COUNT(DISTINCT products_id) FROM " . PFX . "downloads WHERE user_id = :uid");
$countStmt->execute([':uid' => $uid]);
$total = $countStmt->fetchColumn();
$pages = ceil($total / $maxres);

// Descargas agrupadas por logo (una fila por logo, con cuántas veces y la última fecha)
$stmt = $DB_con->prepare("
    SELECT p.id, p.name, p.slug_lg, p.icon_img,
           COUNT(*) AS dl_times,
           MAX(d.date_created) AS last_dl
    FROM " . PFX . "downloads d
    INNER JOIN " . PFX . "products p ON d.products_id = p.id
    WHERE d.user_id = :uid
    GROUP BY p.id
    ORDER BY last_dl DESC
    LIMIT :start, :maxres
");
$stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':maxres', $maxres, PDO::PARAM_INT);
$stmt->execute();
$downloads = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once('includes/header.php');
?>

<div class="user-panel-head">
    <h1>My Downloads</h1>
    <p><?php echo number_format($total); ?> unique logo<?php echo $total !== 1 ? 's' : ''; ?> downloaded</p>
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
    text-decoration: none;
    transition: var(--transition);
    position: relative;
}

.logo-card:hover {
    border-color: rgba(212,255,0,.3);
    transform: translateY(-3px);
}

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

.logo-card-date {
    font-size: .68rem;
    color: var(--text-muted);
    margin-top: .2rem;
}

.logo-card-redl {
    position: absolute;
    top: .5rem; right: .5rem;
    width: 28px; height: 28px;
    background: rgba(13,15,28,.8);
    border: 1px solid var(--border);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--accent);
    font-size: .75rem;
    opacity: 0;
    transition: opacity .2s;
}

.logo-card:hover .logo-card-redl { opacity: 1; }

/* Badge con cuántas veces se descargó */
.logo-card-times {
    position: absolute;
    top: .5rem; left: .5rem;
    background: rgba(13,15,28,.8);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 3px 8px;
    color: var(--accent);
    font-size: .68rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 4px;
    z-index: 2;
}

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

.up-pagination {
    display: flex;
    justify-content: center;
    gap: .4rem;
    margin-top: 2rem;
}

.up-page-btn {
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-muted);
    border-radius: 8px;
    padding: .5rem 1rem;
    font-size: .82rem;
    text-decoration: none;
    transition: var(--transition);
}

.up-page-btn:hover, .up-page-btn.active {
    background: var(--accent);
    color: #0d0f1c;
    border-color: var(--accent);
    font-weight: 700;
}
</style>

<?php if (empty($downloads)): ?>
    <div class="up-empty">
        <i class="fa-regular fa-download"></i>
        <p>You haven't downloaded any logos yet.</p>
        <a href="<?php echo $setting['website_url']; ?>">Browse the collection →</a>
    </div>
<?php else: ?>
    <div class="logo-grid">
        <?php foreach ($downloads as $d): ?>
            <a href="<?php echo $setting['website_url'] . '/item/' . $d['id'] . '/' . $d['slug_lg'] . '/'; ?>"
               class="logo-card" title="<?php echo htmlspecialchars($d['name']); ?>">
                <?php if ($d['dl_times'] > 1): ?>
                    <div class="logo-card-times"><i class="fa-regular fa-download"></i> <?php echo $d['dl_times']; ?>×</div>
                <?php endif; ?>
                <div class="logo-card-redl"><i class="fa-regular fa-arrow-down-to-line"></i></div>
                <img class="logo-card-img"
                     src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $d['icon_img']; ?>"
                     alt="<?php echo htmlspecialchars($d['name']); ?>">
                <div class="logo-card-name"><?php echo htmlspecialchars($d['name']); ?></div>
                <div class="logo-card-date">
                    <?php echo $d['last_dl'] ? date('d M Y', strtotime($d['last_dl'])) : ''; ?>
                </div>
            </a>
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