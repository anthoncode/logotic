<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Error Logs';
require_once('../system/config-admin.php');

// ── Acciones ──
if (isset($_GET['action'])) {
    // Marcar como resuelto
    if ($_GET['action'] === 'resolve' && isset($_GET['id'])) {
        $DB_con->prepare("UPDATE " . PFX . "error_logs SET resolved = 1 WHERE id = :id")
               ->execute([':id' => (int)$_GET['id']]);
        header('Location: error-logs.php?msg=' . urlencode('Error marked as resolved'));
        exit;
    }
    // Reabrir
    if ($_GET['action'] === 'reopen' && isset($_GET['id'])) {
        $DB_con->prepare("UPDATE " . PFX . "error_logs SET resolved = 0 WHERE id = :id")
               ->execute([':id' => (int)$_GET['id']]);
        header('Location: error-logs.php?msg=' . urlencode('Error reopened'));
        exit;
    }
    // Borrar uno
    if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $DB_con->prepare("DELETE FROM " . PFX . "error_logs WHERE id = :id")
               ->execute([':id' => (int)$_GET['id']]);
        header('Location: error-logs.php?msg=' . urlencode('Error deleted'));
        exit;
    }
    // Limpiar todos los resueltos
    if ($_GET['action'] === 'clear_resolved') {
        $DB_con->query("DELETE FROM " . PFX . "error_logs WHERE resolved = 1");
        header('Location: error-logs.php?msg=' . urlencode('Resolved errors cleared'));
        exit;
    }
    // Limpiar todo
    if ($_GET['action'] === 'clear_all') {
        $DB_con->query("DELETE FROM " . PFX . "error_logs");
        header('Location: error-logs.php?msg=' . urlencode('All logs cleared'));
        exit;
    }
}

// ── Filtro ──
$filter = $_GET['filter'] ?? 'unresolved';
$where = '1=1';
if ($filter === 'unresolved') $where = 'resolved = 0';
elseif ($filter === 'resolved') $where = 'resolved = 1';
elseif ($filter === 'error') $where = "level IN ('error','fatal')";
elseif ($filter === 'warning') $where = "level = 'warning'";

// ── Stats ──
$totalErrors    = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "error_logs")->fetchColumn();
$unresolved     = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "error_logs WHERE resolved = 0")->fetchColumn();
$fatalCount     = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "error_logs WHERE level IN ('error','fatal') AND resolved = 0")->fetchColumn();
$totalOccur     = $DB_con->query("SELECT COALESCE(SUM(count),0) FROM " . PFX . "error_logs")->fetchColumn();

// ── Datos ──
$logs = $DB_con->query("SELECT * FROM " . PFX . "error_logs WHERE $where ORDER BY last_seen DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['msg'])) $success = urldecode($_GET['msg']);

require_once('includes/header1.php');
?>

<style>
.el-badge { padding:.15rem .5rem; border-radius:6px; font-size:.68rem; font-weight:700; text-transform:uppercase; }
.el-badge.error, .el-badge.fatal { background:rgba(255,77,77,.15); color:var(--adm-danger); }
.el-badge.warning { background:rgba(244,208,63,.15); color:var(--adm-warning); }
.el-badge.notice, .el-badge.deprecated { background:rgba(6,182,212,.15); color:#06b6d4; }
.el-msg { font-family:monospace; font-size:.82rem; color:var(--adm-text); word-break:break-word; }
.el-meta { font-size:.72rem; color:var(--adm-muted); margin-top:.3rem; }
.el-count { display:inline-block; min-width:24px; text-align:center; background:rgba(255,255,255,.06); border-radius:99px; padding:.1rem .4rem; font-size:.72rem; font-weight:700; }
.el-row.resolved { opacity:.5; }
</style>

<div class="adm-wrap">

    <div class="adm-page-header">
        <div class="adm-page-icon"><i class="fa-regular fa-bug" style="color:var(--adm-accent);"></i></div>
        <div>
            <h1 class="adm-page-title">Error Logs</h1>
            <p class="adm-page-sub">Application errors and warnings</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            <a href="?action=clear_resolved" class="adm-topbar-btn"
               onclick="return confirm('Delete all resolved errors?')">
                <i class="fa-regular fa-broom"></i> Clear resolved
            </a>
            <a href="?action=clear_all" class="adm-topbar-btn" style="color:var(--adm-danger);"
               onclick="return confirm('Delete ALL error logs? This cannot be undone.')">
                <i class="fa-regular fa-trash"></i> Clear all
            </a>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="adm-alert adm-alert-success" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <div class="adm-stats">
        <div class="adm-stat">
            <div class="adm-stat-num"><?php echo number_format($totalErrors); ?></div>
            <div class="adm-stat-label">Unique Errors</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-num" style="color:var(--adm-warning)"><?php echo number_format($unresolved); ?></div>
            <div class="adm-stat-label">Unresolved</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-num" style="color:var(--adm-danger)"><?php echo number_format($fatalCount); ?></div>
            <div class="adm-stat-label">Critical</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-num"><?php echo number_format($totalOccur); ?></div>
            <div class="adm-stat-label">Total Occurrences</div>
        </div>
    </div>

    <div class="adm-toolbar">
        <div class="adm-filter">
            <a class="adm-chip <?php echo $filter === 'unresolved' ? 'active' : ''; ?>" href="?filter=unresolved">Unresolved</a>
            <a class="adm-chip <?php echo $filter === 'error' ? 'active' : ''; ?>" href="?filter=error">Errors</a>
            <a class="adm-chip <?php echo $filter === 'warning' ? 'active' : ''; ?>" href="?filter=warning">Warnings</a>
            <a class="adm-chip <?php echo $filter === 'resolved' ? 'active' : ''; ?>" href="?filter=resolved">Resolved</a>
            <a class="adm-chip <?php echo $filter === 'all' ? 'active' : ''; ?>" href="?filter=all">All</a>
        </div>
    </div>

    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Level</th>
                    <th>Message</th>
                    <th style="text-align:center;">Count</th>
                    <th>Last Seen</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:3rem;color:var(--adm-muted);">
                        <i class="fa-regular fa-circle-check" style="font-size:2rem;display:block;margin-bottom:.5rem;color:var(--adm-success);"></i>
                        No errors here. Everything's running clean.
                    </td></tr>
                <?php else: foreach ($logs as $log): ?>
                    <tr class="el-row <?php echo $log['resolved'] ? 'resolved' : ''; ?>">
                        <td><span class="el-badge <?php echo $log['level']; ?>"><?php echo $log['level']; ?></span></td>
                        <td>
                            <div class="el-msg"><?php echo htmlspecialchars(mb_strimwidth($log['message'], 0, 140, '…')); ?></div>
                            <div class="el-meta">
                                <i class="fa-regular fa-file-code"></i>
                                <?php echo htmlspecialchars(basename($log['file'] ?? '')); ?>:<?php echo (int)$log['line']; ?>
                                <?php if (!empty($log['url'])): ?>
                                    · <i class="fa-regular fa-link"></i> <?php echo htmlspecialchars($log['url']); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="text-align:center;"><span class="el-count"><?php echo (int)$log['count']; ?></span></td>
                        <td style="font-size:.76rem;color:var(--adm-muted);">
                            <?php echo date('d M H:i', strtotime($log['last_seen'])); ?>
                        </td>
                        <td>
                            <div class="adm-actions">
                                <?php if (!$log['resolved']): ?>
                                    <a href="?action=resolve&id=<?php echo $log['id']; ?>" class="adm-btn adm-btn-unban" title="Mark resolved">
                                        <i class="fa-regular fa-check"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?action=reopen&id=<?php echo $log['id']; ?>" class="adm-btn" title="Reopen">
                                        <i class="fa-regular fa-rotate-left"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?action=delete&id=<?php echo $log['id']; ?>" class="adm-btn adm-btn-del"
                                   onclick="return confirm('Delete this error?')" title="Delete">
                                    <i class="fa-regular fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once('includes/footer.php'); ?>