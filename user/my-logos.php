<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'My Logos';
$pg = '4';

require_once('../system/config-user.php');
$uid = $crypt->decrypt($_SESSION['uid'], 'USER');

// Paginación
$maxres = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$start = ($page - 1) * $maxres;

// Total
$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "products WHERE submit_user_id = :uid");
$countStmt->execute([':uid' => $uid]);
$total = (int)$countStmt->fetchColumn();
$pages = ceil($total / $maxres);

// Logos del usuario
$stmt = $DB_con->prepare("
    SELECT id, name, slug_lg, icon_img, views, active, status, created
    FROM " . PFX . "products
    WHERE submit_user_id = :uid
    ORDER BY created DESC, id DESC
    LIMIT :start, :maxres
");
$stmt->bindValue(':uid', $uid, PDO::PARAM_INT);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':maxres', $maxres, PDO::PARAM_INT);
$stmt->execute();
$logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contadores de estado (para las stats de arriba)
$statStmt = $DB_con->prepare("
    SELECT
        SUM(CASE WHEN status='pending'  THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) AS rejected
    FROM " . PFX . "products WHERE submit_user_id = :uid
");
$statStmt->execute([':uid' => $uid]);
$counts = $statStmt->fetch(PDO::FETCH_ASSOC);

require_once('includes/header.php');
?>

<div class="user-panel-head">
    <h1>My Logos</h1>
    <p><?php echo number_format($total); ?> logo<?php echo $total !== 1 ? 's' : ''; ?> submitted</p>
</div>

<style>
.ml-stats { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-bottom: 1.5rem; }
.ml-stat { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-card); padding: 1rem 1.25rem; }
.ml-stat-num { font-size: 1.5rem; font-weight: 800; }
.ml-stat-label { font-size: .78rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .05em; }

.ml-table-wrap { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-card); overflow: hidden; }
.ml-table { width: 100%; border-collapse: collapse; }
.ml-table th { text-align: left; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: var(--text-muted); padding: .85rem 1rem; border-bottom: 1px solid var(--border); font-weight: 700; }
.ml-table td { padding: .85rem 1rem; border-bottom: 1px solid var(--border); font-size: .85rem; color: var(--text-primary); }
.ml-table tr:last-child td { border-bottom: none; }

.ml-thumb { width: 44px; height: 44px; background: #fff; border-radius: 8px; padding: 5px; object-fit: contain; }
.ml-metric { display: inline-flex; align-items: center; gap: .3rem; color: var(--text-secondary); font-size: .82rem; }
.ml-metric i { color: var(--text-muted); font-size: .8rem; }

.ml-badge { display: inline-flex; align-items: center; gap: .35rem; font-size: .72rem; font-weight: 700; padding: .25rem .6rem; border-radius: 99px; }
.ml-badge.approved { background: rgba(45,198,83,.12); color: #2dc653; }
.ml-badge.pending  { background: rgba(244,208,63,.12); color: #f4d03f; }
.ml-badge.rejected { background: rgba(255,77,77,.12); color: #ff4d4d; }
.ml-badge.inactive { background: rgba(139,143,163,.15); color: #8b8fa3; }

.ml-empty { text-align: center; padding: 3rem 1rem; color: var(--text-muted); }
.ml-empty i { font-size: 2.2rem; color: var(--accent); display: block; margin-bottom: .75rem; }

.ml-pagination { display: flex; gap: .4rem; justify-content: center; margin-top: 1.5rem; }
.ml-pagination a, .ml-pagination span { padding: .4rem .75rem; border-radius: 8px; font-size: .82rem; border: 1px solid var(--border); color: var(--text-secondary); text-decoration: none; }
.ml-pagination .active { background: var(--accent); color: #0d0f1c; border-color: var(--accent); font-weight: 700; }

@media (max-width: 600px) {
    .ml-stats { grid-template-columns: 1fr; }
    .ml-hide-sm { display: none; }
}
</style>

<div class="ml-stats">
    <div class="ml-stat">
        <div class="ml-stat-num" style="color:#2dc653;"><?php echo (int)$counts['approved']; ?></div>
        <div class="ml-stat-label">Approved</div>
    </div>
    <div class="ml-stat">
        <div class="ml-stat-num" style="color:#f4d03f;"><?php echo (int)$counts['pending']; ?></div>
        <div class="ml-stat-label">Pending</div>
    </div>
    <div class="ml-stat">
        <div class="ml-stat-num" style="color:#ff4d4d;"><?php echo (int)$counts['rejected']; ?></div>
        <div class="ml-stat-label">Rejected</div>
    </div>
</div>

<?php if (empty($logos)): ?>
    <div class="ml-table-wrap">
        <div class="ml-empty">
            <i class="fa-regular fa-cloud-arrow-up"></i>
            <div>You haven't uploaded any logos yet.</div>
            <a href="<?php echo $setting['website_url']; ?>/user/upload-logo.php" style="color:var(--accent);display:inline-block;margin-top:.5rem;">Upload your first logo</a>
        </div>
    </div>
<?php else: ?>
    <div class="ml-table-wrap">
        <table class="ml-table">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Name</th>
                    <th class="ml-hide-sm">Views</th>
                    <th class="ml-hide-sm">Downloads</th>
                    <th class="ml-hide-sm">Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logos as $lg):
                    $dl = $product->downloadCount($lg['id']);
                    $dlCount = $dl['doCount'] ?? 0;

                    // Estado según 'status' (la fuente de verdad)
                    switch ($lg['status']) {
                        case 'approved':
                            $stLabel = 'Approved'; $stClass = 'approved'; $stIcon = 'fa-circle-check';
                            break;
                        case 'rejected':
                            $stLabel = 'Rejected'; $stClass = 'rejected'; $stIcon = 'fa-circle-xmark';
                            break;
                        case 'inactive':
                            $stLabel = 'Inactive'; $stClass = 'inactive'; $stIcon = 'fa-circle-minus';
                            break;
                        default:
                            $stLabel = 'Pending'; $stClass = 'pending'; $stIcon = 'fa-clock';
                    }

                    // Solo enlazar si está aprobado y visible
                    $isLive = ($lg['status'] === 'approved' && $lg['active'] == 1);
                    $itemUrl = $setting['website_url'] . '/item/' . $lg['id'] . '/' . $lg['slug_lg'] . '/';
                ?>
                    <tr>
                        <td>
                            <img class="ml-thumb" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $lg['icon_img']; ?>" alt="<?php echo htmlspecialchars($lg['name']); ?>">
                        </td>
                        <td>
                            <?php if ($isLive): ?>
                                <a href="<?php echo $itemUrl; ?>" style="color:var(--text-primary);text-decoration:none;font-weight:600;"><?php echo htmlspecialchars($lg['name']); ?></a>
                            <?php else: ?>
                                <span style="font-weight:600;"><?php echo htmlspecialchars($lg['name']); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="ml-hide-sm"><span class="ml-metric"><i class="fa-regular fa-eye"></i> <?php echo $product->formatCount($lg['views']); ?></span></td>
                        <td class="ml-hide-sm"><span class="ml-metric"><i class="fa-regular fa-download"></i> <?php echo $product->formatCount($dlCount); ?></span></td>
                        <td class="ml-hide-sm" style="color:var(--text-muted);font-size:.8rem;"><?php echo $lg['created'] ? date('d M Y', strtotime($lg['created'])) : '—'; ?></td>
                        <td><span class="ml-badge <?php echo $stClass; ?>"><i class="fa-solid <?php echo $stIcon; ?>"></i> <?php echo $stLabel; ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
        <div class="ml-pagination">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class="active"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php require_once('includes/footer.php'); ?>