<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Search Insights';
require_once('../system/config-admin.php');

// ── Acciones ──
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'resolve') {
        $DB_con->prepare("UPDATE " . PFX . "search_logs SET status = 'resolved' WHERE id = :id")->execute([':id' => $id]);
    } elseif ($_GET['action'] === 'ignore') {
        $DB_con->prepare("UPDATE " . PFX . "search_logs SET status = 'ignored' WHERE id = :id")->execute([':id' => $id]);
    } elseif ($_GET['action'] === 'reopen') {
        $DB_con->prepare("UPDATE " . PFX . "search_logs SET status = 'pending' WHERE id = :id")->execute([':id' => $id]);
    } elseif ($_GET['action'] === 'delete') {
        $DB_con->prepare("DELETE FROM " . PFX . "search_logs WHERE id = :id")->execute([':id' => $id]);
    }
    header('Location: search-insights.php?filter=' . urlencode($_GET['filter'] ?? 'noresults'));
    exit;
}

// ── Filtro ──
$filter = $_GET['filter'] ?? 'noresults';
if ($filter === 'noresults')      $where = "results_count = 0 AND status = 'pending'";
elseif ($filter === 'few')        $where = "results_count BETWEEN 1 AND 3 AND status = 'pending'";
elseif ($filter === 'resolved')   $where = "status = 'resolved'";
elseif ($filter === 'ignored')    $where = "status = 'ignored'";
else                              $where = "status = 'pending'";  // all pending

// ── Stats ──
$noResultsTerms = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "search_logs WHERE results_count = 0 AND status = 'pending'")->fetchColumn();
$unmetDemand    = $DB_con->query("SELECT COALESCE(SUM(search_count),0) FROM " . PFX . "search_logs WHERE results_count = 0 AND status = 'pending'")->fetchColumn();
$topTerm        = $DB_con->query("SELECT term FROM " . PFX . "search_logs WHERE results_count = 0 AND status = 'pending' ORDER BY search_count DESC LIMIT 1")->fetchColumn();
$thisWeek       = $DB_con->query("SELECT COALESCE(SUM(search_count),0) FROM " . PFX . "search_logs WHERE results_count = 0 AND last_searched >= (CURDATE() - INTERVAL 7 DAY)")->fetchColumn();

// ── Datos ──
$rows = $DB_con->query("SELECT * FROM " . PFX . "search_logs WHERE $where ORDER BY search_count DESC, last_searched DESC LIMIT 200")->fetchAll(PDO::FETCH_ASSOC);

require_once('includes/header1.php');
?>

<style>
    .si-term {
        font-weight: 600;
        color: var(--adm-text);
        font-size: .9rem;
    }

    .si-count {
        display: inline-block;
        min-width: 28px;
        text-align: center;
        background: rgba(212, 255, 0, .12);
        color: var(--adm-accent);
        border-radius: 99px;
        padding: .15rem .5rem;
        font-size: .75rem;
        font-weight: 800;
    }

    .si-results {
        font-size: .75rem;
    }

    .si-results.zero {
        color: var(--adm-danger);
        font-weight: 700;
    }

    .si-results.few {
        color: var(--adm-warning);
    }

    .si-row.resolved,
    .si-row.ignored {
        opacity: .5;
    }
</style>

<div class="adm-wrap">

    <div class="adm-page-header">
        <div class="adm-page-icon"><i class="fa-regular fa-magnifying-glass-chart" style="color:var(--adm-accent);"></i></div>
        <div>
            <h1 class="adm-page-title">Search Insights</h1>
            <p class="adm-page-sub">What people search for that you don't have yet</p>
        </div>
    </div>

    <div class="adm-stats">
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="color:var(--adm-danger);"><?php echo number_format($noResultsTerms); ?></div>
            <div class="adm-stat-label">Terms With No Results</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num"><?php echo number_format($unmetDemand); ?></div>
            <div class="adm-stat-label">Unmet Demand (searches)</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.1rem;color:var(--adm-accent);"><?php echo $topTerm ? htmlspecialchars($topTerm) : '—'; ?></div>
            <div class="adm-stat-label">Most Wanted</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="color:var(--adm-warning);"><?php echo number_format($thisWeek); ?></div>
            <div class="adm-stat-label">Failed Searches (7d)</div>
        </div>
    </div>

    <div class="adm-toolbar">
        <div class="adm-filter">
            <a class="adm-chip <?php echo $filter === 'noresults' ? 'active' : ''; ?>" href="?filter=noresults">No Results</a>
            <a class="adm-chip <?php echo $filter === 'few' ? 'active' : ''; ?>" href="?filter=few">Few Results (1-3)</a>
            <a class="adm-chip <?php echo $filter === 'resolved' ? 'active' : ''; ?>" href="?filter=resolved">Resolved</a>
            <a class="adm-chip <?php echo $filter === 'ignored' ? 'active' : ''; ?>" href="?filter=ignored">Ignored</a>
        </div>
    </div>

    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Search Term</th>
                    <th style="text-align:center;">Times Searched</th>
                    <th style="text-align:center;">Results</th>
                    <th>Last Searched</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:3rem; color:var(--adm-muted);">
                            <div style="display:flex; flex-direction:column; align-items:center; gap:.5rem;">
                                <i class="fa-regular fa-face-smile" style="font-size:2rem; color:var(--adm-success);"></i>
                                <span>Nothing here. No unmet searches in this view.</span>
                            </div>
                            </td>
                    </tr>
                    <?php else: foreach ($rows as $r):
                        $isZero = $r['results_count'] == 0;
                    ?>
                        <tr class="si-row <?php echo $r['status']; ?>">
                            <td><span class="si-term"><?php echo htmlspecialchars($r['term']); ?></span></td>
                            <td style="text-align:center;"><span class="si-count"><?php echo (int)$r['search_count']; ?></span></td>
                            <td style="text-align:center;">
                                <span class="si-results <?php echo $isZero ? 'zero' : 'few'; ?>">
                                    <?php echo $isZero ? 'None' : (int)$r['results_count']; ?>
                                </span>
                            </td>
                            <td style="font-size:.76rem;color:var(--adm-muted);"><?php echo date('d M H:i', strtotime($r['last_searched'])); ?></td>
                            <td>
                                <div class="adm-actions">
                                    <?php if ($r['status'] === 'pending'): ?>
                                        <a href="?action=resolve&id=<?php echo $r['id']; ?>&filter=<?php echo $filter; ?>" class="adm-btn adm-btn-unban" title="Mark as added">
                                            <i class="fa-regular fa-check"></i>
                                        </a>
                                        <a href="?action=ignore&id=<?php echo $r['id']; ?>&filter=<?php echo $filter; ?>" class="adm-btn" title="Ignore">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=reopen&id=<?php echo $r['id']; ?>&filter=<?php echo $filter; ?>" class="adm-btn" title="Reopen">
                                            <i class="fa-regular fa-rotate-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?action=delete&id=<?php echo $r['id']; ?>&filter=<?php echo $filter; ?>" class="adm-btn adm-btn-del"
                                        onclick="return confirm('Delete this term?')" title="Delete">
                                        <i class="fa-regular fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                <?php endforeach;
                endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once('includes/footer.php'); ?>