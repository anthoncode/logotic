<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once('../system/config-admin.php');

$search = $_POST['search'] ?? '';
$page   = max(1, (int)($_POST['page'] ?? 1));
$maxres = 20;
$start  = ($page - 1) * $maxres;

// Pendientes = status 'pending' (subidos por usuarios, esperando revisión)
$where  = "p.status = 'pending'";
$params = [];

if ($search) {
    $where .= " AND (p.name LIKE :search OR p.tags LIKE :search OR p.slug_lg LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "products p WHERE $where");
$countStmt->execute($params);
$num   = $countStmt->fetchColumn();
$pages = ceil($num / $maxres);

$params[':start']  = $start;
$params[':maxres'] = $maxres;

// JOIN con categorías y subcategorías para mostrar sus nombres
$sql = "SELECT p.*,
               c.name  AS cat_name,
               sc.name AS subcat_name
        FROM " . PFX . "products p
        LEFT JOIN " . PFX . "categories c ON p.cat_id = c.id
        LEFT JOIN " . PFX . "subcat sc     ON p.subc_id = sc.id
        WHERE $where
        ORDER BY p.created ASC, p.id ASC
        LIMIT :start, :maxres";
$stmt = $DB_con->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tbody = '';
foreach ($logos as $p) {
    $download = $product->downloadCount($p['id']);
    $dlCount  = $download['doCount'] ?? 0;
    $views    = (int)($p['views'] ?? 0);

    $name = htmlspecialchars($p['name'] ?? '');
    $tags = htmlspecialchars($p['tags'] ?? '');
    $tagsShort = mb_strlen($tags) > 40 ? mb_substr($tags, 0, 40) . '...' : $tags;

    $catName = htmlspecialchars($p['cat_name'] ?? '—');
    $subName = htmlspecialchars($p['subcat_name'] ?? '');

    // Quién lo subió
    $submitter = 'Admin';
    $submitterId = 0;
    if (!empty($p['submit_user_id'])) {
        $submitterId = (int)$p['submit_user_id'];
        $uStmt = $DB_con->prepare("SELECT fname, username FROM " . PFX . "users WHERE id = :id");
        $uStmt->execute([':id' => $submitterId]);
        $u = $uStmt->fetch(PDO::FETCH_ASSOC);
        $submitter = $u ? htmlspecialchars($u['fname'] . ' (@' . $u['username'] . ')') : 'User #' . $submitterId;
    }

    // Badges de tags (cada tag separado por coma como chip)
    $tagBadges = '';
    if ($tags !== '') {
        $tagArr = array_slice(array_filter(array_map('trim', explode(',', $p['tags']))), 0, 4);
        foreach ($tagArr as $t) {
            $tagBadges .= "<span style='display:inline-block;background:rgba(255,255,255,.06);border-radius:5px;padding:1px 6px;font-size:.68rem;color:var(--adm-muted);margin:1px;'>" . htmlspecialchars($t) . "</span>";
        }
    } else {
        $tagBadges = "<span style='color:var(--adm-muted);font-size:.72rem;'>—</span>";
    }

    $blockBtn = '';
    if ($submitterId > 0) {
        $blockBtn = "<a href='pending.php?action=block_user&user={$submitterId}' class='adm-btn adm-btn-del' title='Block this user' onclick=\"return confirm('Block this user? Their pending logos will be rejected and they will not be able to sign in.')\"><i class='fa-regular fa-user-slash'></i></a>";
    }

    $tbody .= "
    <tr id='row-{$p['id']}'>
        <td>
            <div class='adm-user-cell'>
                <img src='{$setting['website_url']}/system/assets/uploads/vector-files/{$p['icon_img']}'
                     width='42' height='42'
                     style='border-radius:8px;background:#fff;object-fit:contain;padding:2px;flex-shrink:0;'
                     alt='{$name}'>
                <div style='min-width:0;'>
                    <div class='adm-user-name'>{$name}</div>
                    <div class='adm-user-username' style='font-size:.7rem;'>{$p['slug_lg']}</div>
                </div>
            </div>
        </td>
        <td style='font-size:.75rem;color:var(--adm-muted);'>{$submitter}</td>
        <td style='font-size:.75rem;'>
            <span style='color:var(--adm-text);'>{$catName}</span>" .
            ($subName ? "<br><span style='color:var(--adm-muted);font-size:.7rem;'>{$subName}</span>" : "") . "
        </td>
        <td style='max-width:170px;'>{$tagBadges}</td>
        <td style='white-space:nowrap;font-size:.75rem;color:var(--adm-muted);'>
            <span title='Views'><i class='fa-regular fa-eye'></i> {$views}</span>
            &nbsp;&nbsp;
            <span title='Downloads'><i class='fa-regular fa-download'></i> {$dlCount}</span>
        </td>
        <td style='color:var(--adm-muted);font-size:.75rem;white-space:nowrap;'>" . date('d M Y', strtotime($p['created'])) . "</td>
        <td>
            <div class='adm-actions'>
                <a href='pending.php?action=approve&id={$p['id']}' class='adm-btn adm-btn-approve' title='Approve & publish' onclick=\"return confirm('Approve and publish this logo?')\">
                    <i class='fa-regular fa-circle-check'></i>
                </a>
                <a href='edit-product.php?id={$p['id']}' class='adm-btn' title='Edit before approving'>
                    <i class='fa-regular fa-file-pen'></i>
                </a>
                <a href='{$setting['website_url']}/item.php?id={$p['id']}' class='adm-btn' target='_blank' title='Preview'>
                    <i class='fa-regular fa-eye'></i>
                </a>
                <a href='pending.php?action=reject&id={$p['id']}' class='adm-btn adm-btn-del' title='Reject' onclick=\"return confirm('Reject this logo? The user will see it as rejected.')\">
                    <i class='fa-regular fa-xmark'></i>
                </a>
                {$blockBtn}
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