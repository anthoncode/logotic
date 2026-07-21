<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once('../system/config-admin.php');

$search = $_POST['search'] ?? '';
$page   = max(1, (int)($_POST['page'] ?? 1));
$maxres = 20;
$start  = ($page - 1) * $maxres;

// Los pendientes son logos con active = 0
$where  = "active = 0";
$params = [];

if ($search) {
    $where .= " AND (name LIKE :search OR tags LIKE :search OR slug_lg LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "products WHERE $where");
$countStmt->execute($params);
$num   = $countStmt->fetchColumn();
$pages = ceil($num / $maxres);

$params[':start']  = $start;
$params[':maxres'] = $maxres;
$stmt = $DB_con->prepare("SELECT * FROM " . PFX . "products WHERE $where ORDER BY id DESC LIMIT :start, :maxres");
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tbody = '';
foreach ($logos as $p) {
    $download = $product->downloadCount($p['id']);
    $dlCount  = $download['doCount'] ?? 0;

    $name    = htmlspecialchars($p['name'] ?? '');
    $tags    = htmlspecialchars($p['tags'] ?? '');
    $website = htmlspecialchars($p['website'] ?? '');
    $tagsShort = mb_strlen($tags) > 45 ? mb_substr($tags, 0, 45) . '...' : $tags;

    // Quién lo subió
    $submitter = '';
    if (!empty($p['submit_user_id'])) {
        $uStmt = $DB_con->prepare("SELECT fname, username FROM " . PFX . "users WHERE id = :id");
        $uStmt->execute([':id' => $p['submit_user_id']]);
        $u = $uStmt->fetch(PDO::FETCH_ASSOC);
        $submitter = $u ? htmlspecialchars($u['fname'] . ' (@' . $u['username'] . ')') : 'User #' . $p['submit_user_id'];
    } else {
        $submitter = 'Admin';
    }

    $tbody .= "
    <tr id='row-{$p['id']}' class='id_table'>
        <td>
            <div class='adm-user-cell'>
                <img src='{$setting['website_url']}/system/assets/uploads/vector-files/{$p['icon_img']}'
                     width='42' height='42'
                     style='border-radius:8px;background:#fff;object-fit:contain;padding:2px;flex-shrink:0;'
                     alt='{$name}'>
                <div style='min-width:0;'>
                    <div class='adm-user-name'>
                        <span class='editValue name'>{$name}</span>
                        <input class='adm-input editInput name' type='text' name='name' value='{$name}'
                               style='display:none;font-size:.82rem;padding:.3rem .6rem;margin-top:.2rem;'>
                    </div>
                    <div class='adm-user-username' style='font-size:.7rem;'>{$p['slug_lg']}</div>
                </div>
            </div>
        </td>
        <td style='max-width:150px;'>
            <span class='editValue tags'
                  style='font-size:.75rem;color:var(--adm-muted);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;'
                  title='{$tags}'>{$tagsShort}</span>
            <input class='adm-input editInput tags' type='text' name='tags' value='{$tags}'
                   style='display:none;font-size:.82rem;padding:.3rem .6rem;'>
        </td>
        <td style='font-size:.75rem;color:var(--adm-muted);'>{$submitter}</td>
        <td style='color:var(--adm-muted);font-size:.75rem;white-space:nowrap;'>" . date('d M Y', strtotime($p['created'])) . "</td>
        <td>
            <div class='adm-actions'>
                <button class='adm-btn adm-btn-approve' onclick='approveLogo({$p['id']})' title='Approve & publish'>
                    <i class='fa-regular fa-circle-check'></i>
                </button>
                <button class='adm-btn editbutton' title='Quick edit'>
                    <i class='fa-regular fa-pen'></i>
                </button>
                <button class='adm-btn adm-btn-unban savebutton' style='display:none;' title='Save'>
                    <i class='fa-regular fa-floppy-disk'></i>
                </button>
                <a href='edit-product.php?id={$p['id']}' class='adm-btn' title='Full edit'>
                    <i class='fa-regular fa-file-pen'></i>
                </a>
                <a href='{$setting['website_url']}/item.php?id={$p['id']}' class='adm-btn' target='_blank' title='Preview'>
                    <i class='fa-regular fa-eye'></i>
                </a>
                <button class='adm-btn adm-btn-del' onclick='rejectLogo({$p['id']})' title='Reject & delete'>
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