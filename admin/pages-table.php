<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once('../system/config-admin.php');

$search = $_POST['search'] ?? '';
$filter = $_POST['filter'] ?? 'all';
$page   = max(1, (int)($_POST['page'] ?? 1));
$maxres = 15;
$start  = ($page - 1) * $maxres;

$where  = "1=1";
$params = [];

if ($search) {
    $where .= " AND (title LIKE :search OR slug_page LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($filter === 'published') {
    $where .= " AND active = 1";
} elseif ($filter === 'draft') {
    $where .= " AND active = 0";
}

$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "custompages WHERE $where");
$countStmt->execute($params);
$num   = $countStmt->fetchColumn();
$pages = ceil($num / $maxres);

$params[':start']  = $start;
$params[':maxres'] = $maxres;
$stmt = $DB_con->prepare("SELECT * FROM " . PFX . "custompages WHERE $where ORDER BY id DESC LIMIT :start, :maxres");
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tbody = '';
foreach ($rows as $p) {
    $title = htmlspecialchars($p['title'] ?? '');
    $slug  = htmlspecialchars($p['slug_page'] ?? '');

    // Miniatura o icono
    $thumb = !empty($p['cover_img'])
        ? "<img src='{$setting['website_url']}/system/assets/uploads/pages/{$p['cover_img']}' width='42' height='42' style='border-radius:8px;object-fit:cover;flex-shrink:0;' alt='{$title}'>"
        : "<div style='width:42px;height:42px;border-radius:8px;background:rgba(212,255,0,.06);display:flex;align-items:center;justify-content:center;flex-shrink:0;'><i class='fa-regular fa-file-lines' style='color:var(--adm-muted);'></i></div>";

    // Badge de acceso
    $access = $p['level'] == 1
        ? "<span class='adm-badge' style='background:rgba(6,182,212,.12);color:var(--adm-info);'><i class='fa-solid fa-lock' style='font-size:.65rem;'></i> Members</span>"
        : "<span class='adm-badge' style='background:rgba(255,255,255,.06);color:var(--adm-muted);'><i class='fa-solid fa-globe' style='font-size:.65rem;'></i> Public</span>";

    // Badge de estado
    $status = $p['active'] == 1
        ? "<span class='adm-badge' style='background:rgba(45,198,83,.12);color:var(--adm-success);'>Published</span>"
        : "<span class='adm-badge' style='background:rgba(244,208,63,.12);color:var(--adm-warning);'>Draft</span>";

    $date = !empty($p['indate']) ? date('d M Y', strtotime($p['indate'])) : '—';

    $tbody .= "
    <tr id='row-{$p['id']}'>
        <td>
            <div class='adm-user-cell'>
                {$thumb}
                <div style='min-width:0;'>
                    <div class='adm-user-name'>{$title}</div>
                    <div class='adm-user-username' style='font-size:.7rem;'>/page/{$slug}/</div>
                </div>
            </div>
        </td>
        <td>{$access}</td>
        <td>{$status}</td>
        <td style='color:var(--adm-muted);font-size:.78rem;white-space:nowrap;'>{$date}</td>
        <td>
            <div class='adm-actions'>
                <a href='new-page.php?id={$p['id']}' class='adm-btn' title='Edit'>
                    <i class='fa-regular fa-pen'></i>
                </a>
                <a href='{$setting['website_url']}/page/{$slug}/' class='adm-btn' target='_blank' title='View'>
                    <i class='fa-regular fa-eye'></i>
                </a>
                <button class='adm-btn adm-btn-del' onclick='deletePage({$p['id']})' title='Delete'>
                    <i class='fa-regular fa-trash'></i>
                </button>
            </div>
        </td>
    </tr>";
}

// Paginación con ellipsis
$paginationHtml = '';
if ($pages > 1) {
    $range = 2;
    if ($page > 1) {
        $paginationHtml .= "<a href='#' class='adm-page-btn' data-page='" . ($page - 1) . "'>←</a>";
    }
    for ($i = 1; $i <= $pages; $i++) {
        $showPage = $i == 1 || $i == $pages || ($i >= $page - $range && $i <= $page + $range);
        if ($showPage) {
            $active = $i == $page ? 'active' : '';
            $paginationHtml .= "<a href='#' class='adm-page-btn {$active}' data-page='{$i}'>{$i}</a>";
        } else if ($i == 2 || $i == $pages - 1) {
            $paginationHtml .= "<span class='adm-page-btn' style='cursor:default;border:none;color:var(--adm-muted);'>...</span>";
        }
    }
    if ($page < $pages) {
        $paginationHtml .= "<a href='#' class='adm-page-btn' data-page='" . ($page + 1) . "'>→</a>";
    }
}

echo json_encode([
    'tbody'      => $tbody,
    'pagination' => $paginationHtml,
    'total'      => $num,
    'pages'      => $pages,
]);