<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = "Pending Approval";
require_once '../system/config-admin.php';

// ── Limpieza oportunista: borrar rechazados con más de 30 días ──
// (se ejecuta al cargar la página; borra registro + archivo SVG)
try {
    $oldRejected = $DB_con->query("
        SELECT id, icon_img FROM " . PFX . "products
        WHERE status = 'rejected' AND modified < (CURDATE() - INTERVAL 30 DAY)
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($oldRejected as $old) {
        $fp = '../system/assets/uploads/vector-files/' . $old['icon_img'];
        if (!empty($old['icon_img']) && file_exists($fp)) {
            @unlink($fp);
        }
        $DB_con->prepare("DELETE FROM " . PFX . "products WHERE id = :id")->execute([':id' => $old['id']]);
    }
} catch (Throwable $e) {
    // Si falla la limpieza, no romper la página
}

// Aprobar logo (status approved → active 1)
if (isset($_GET['action']) && $_GET['action'] === 'approve' && isset($_GET['id'])) {
    $pid = (int)$_GET['id'];
    $DB_con->prepare("UPDATE " . PFX . "products SET status = 'approved', active = 1, modified = CURDATE() WHERE id = :id")->execute([':id' => $pid]);
    header('Location: pending.php?msg=Logo approved and published');
    exit;
}

// Rechazar logo (status rejected → active 0). NO borra; guarda fecha en modified.
if (isset($_GET['action']) && $_GET['action'] === 'reject' && isset($_GET['id'])) {
    $pid = (int)$_GET['id'];
    $DB_con->prepare("UPDATE " . PFX . "products SET status = 'rejected', active = 0, modified = CURDATE() WHERE id = :id")->execute([':id' => $pid]);
    header('Location: pending.php?msg=Logo rejected (kept for 30 days, then auto-deleted)');
    exit;
}

// Bloquear al usuario que subió el logo
if (isset($_GET['action']) && $_GET['action'] === 'block_user' && isset($_GET['user'])) {
    $blockUid = (int)$_GET['user'];
    if ($blockUid > 0) {
        $DB_con->prepare("UPDATE " . PFX . "users SET active = 0 WHERE id = :id")->execute([':id' => $blockUid]);
        // Rechazar sus logos pendientes
        $DB_con->prepare("UPDATE " . PFX . "products SET status = 'rejected', active = 0, modified = CURDATE() WHERE submit_user_id = :id AND status = 'pending'")->execute([':id' => $blockUid]);
    }
    header('Location: pending.php?msg=User blocked and their pending logos rejected');
    exit;
}

// Aprobar todos los pendientes
if (isset($_GET['action']) && $_GET['action'] === 'approve_all') {
    $DB_con->query("UPDATE " . PFX . "products SET status = 'approved', active = 1, modified = CURDATE() WHERE status = 'pending'");
    header('Location: pending.php?msg=All pending logos approved');
    exit;
}

// Stats
$totalPending  = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE status = 'pending'")->fetchColumn();
$totalActive   = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE status = 'approved' AND active = 1")->fetchColumn();
$fromUsers     = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE status = 'pending' AND submit_user_id > 0")->fetchColumn();
$todayPending  = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE status = 'pending' AND DATE(created) = CURDATE()")->fetchColumn();

if (isset($_GET['msg'])) $success = $_GET['msg'];

require_once 'includes/header1.php';
?>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-clock" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">Pending Approval</h1>
            <p class="adm-page-sub">Review and approve logos submitted by users</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            <?php if ($totalPending > 0): ?>
            <a href="pending.php?action=approve_all" class="adm-save"
               style="margin-top:0;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;background:rgba(45,198,83,.15);color:var(--adm-success);border:1px solid rgba(45,198,83,.3);"
               onclick="return confirm('Approve ALL <?php echo $totalPending; ?> pending logos?')">
                <i class="fa-regular fa-circle-check"></i> Approve All
            </a>
            <?php endif; ?>
            <a href="all-logos.php" class="adm-topbar-btn">
                <i class="fa-regular fa-images"></i> All Logos
            </a>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="adm-alert adm-alert-success" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="adm-stats">
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-clock"></i>
            <div class="adm-stat-num" style="color:var(--adm-warning);"><?php echo number_format($totalPending); ?></div>
            <div class="adm-stat-label">Pending Approval</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-user-check"></i>
            <div class="adm-stat-num" style="color:var(--adm-info);"><?php echo number_format($fromUsers); ?></div>
            <div class="adm-stat-label">From Users</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-calendar-day"></i>
            <div class="adm-stat-num"><?php echo number_format($todayPending); ?></div>
            <div class="adm-stat-label">Submitted Today</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-circle-check"></i>
            <div class="adm-stat-num" style="color:var(--adm-success);"><?php echo number_format($totalActive); ?></div>
            <div class="adm-stat-label">Already Published</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="adm-toolbar">
        <input type="text" id="adm-search" class="adm-search"
               placeholder="🔍 Search pending logos by name, tags or slug...">
        <span id="adm-total" style="font-size:.78rem;color:var(--adm-muted);margin-left:auto;"></span>
    </div>

    <!-- Table -->
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Submitted by</th>
                    <th>Category</th>
                    <th>Tags</th>
                    <th>Stats</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="adm-tbody">
                <tr>
                    <td colspan="7" style="text-align:center;padding:2rem;color:var(--adm-muted);">
                        <i class="fa-regular fa-spinner fa-spin"></i> Loading...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="adm-pagination" id="adm-pagination"></div>

</div>

<style>
.adm-btn-approve {
    color: var(--adm-success) !important;
    border-color: rgba(45,198,83,.3) !important;
}
.adm-btn-approve:hover {
    background: var(--adm-success) !important;
    color: #0d0f1c !important;
}
</style>

<script>
let currentSearch = '';
let currentPage   = 1;

function loadPending() {
    $('#adm-tbody').html(`
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--adm-muted);">
            <i class="fa-regular fa-spinner fa-spin"></i> Loading...
        </td></tr>
    `);

    $.ajax({
        url: '<?php echo $setting['website_url']; ?>/admin/ajax-pending-table.php',
        type: 'POST',
        data: { search: currentSearch, page: currentPage },
        success: function(res) {
            const data = JSON.parse(res);
            $('#adm-tbody').html(data.tbody ||
                '<tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--adm-muted);"><i class="fa-regular fa-circle-check" style="font-size:2rem;display:block;margin-bottom:.75rem;color:var(--adm-success);"></i>No pending logos — all caught up!</td></tr>');
            $('#adm-pagination').html(data.pagination);
            $('#adm-total').text(data.total.toLocaleString() + ' pending');
        },
        error: function() {
            $('#adm-tbody').html('<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--adm-danger);">Error loading logos.</td></tr>');
        }
    });
}

$(document).ready(function() {
    loadPending();

    // Búsqueda con debounce
    let searchTimer;
    $('#adm-search').on('input', function() {
        clearTimeout(searchTimer);
        const val = $(this).val();
        searchTimer = setTimeout(function() {
            currentSearch = val;
            currentPage = 1;
            loadPending();
        }, 350);
    });

    // Paginación (delegada)
    $('#adm-pagination').on('click', '.adm-page-btn', function(e) {
        e.preventDefault();
        const p = $(this).data('page');
        if (p) {
            currentPage = parseInt(p, 10);
            loadPending();
            $('html,body').animate({ scrollTop: 0 }, 200);
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>