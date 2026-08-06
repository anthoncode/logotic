<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once('../system/config-admin.php');

$search  = $_POST['search'] ?? '';
$filter  = $_POST['filter'] ?? 'all';
$page    = max(1, (int)($_POST['page'] ?? 1));
$maxres  = 20;
$start   = ($page - 1) * $maxres;

$where  = '1=1';
$params = [];

if ($search) {
    $where .= " AND (p.title LIKE :search OR p.author LIKE :search OR p.slug LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($filter === 'published') { $where .= " AND p.status = 'published'"; }
if ($filter === 'draft')     { $where .= " AND p.status = 'draft'"; }

$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "posts p WHERE $where");
$countStmt->execute($params);
$num   = $countStmt->fetchColumn();
$pages = ceil($num / $maxres);

$params[':start']  = $start;
$params[':maxres'] = $maxres;

$stmt = $DB_con->prepare("
    SELECT p.*, pc.name as cat_name
    FROM " . PFX . "posts p
    LEFT JOIN " . PFX . "post_categories pc ON p.category_id = pc.id
    WHERE $where
    ORDER BY p.id DESC
    LIMIT :start, :maxres
");
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tbody = '';
foreach ($posts as $p) {
    $isPublished = $p['status'] === 'published';

    $statusBadge = $isPublished
        ? "<span class='adm-badge adm-badge-active'><i class='fa-solid fa-globe'></i> Published</span>"
        : "<span class='adm-badge adm-badge-pending'><i class='fa-regular fa-floppy-disk'></i> Draft</span>";

    $cover = $p['cover_img']
        ? "<img src='{$setting['website_url']}/system/assets/uploads/blog/covers/{$p['cover_img']}'
                width='60' height='40'
                style='border-radius:6px;object-fit:cover;flex-shrink:0;'
                alt='" . htmlspecialchars($p['title']) . "'>"
        : "<div style='width:60px;height:40px;border-radius:6px;background:rgba(255,255,255,.05);
                       border:1px solid var(--adm-border);display:flex;align-items:center;
                       justify-content:center;flex-shrink:0;'>
               <i class='fa-regular fa-image' style='color:var(--adm-muted);font-size:.8rem;'></i>
           </div>";

    $catBadge = $p['cat_name']
        ? "<span style='font-size:.7rem;background:rgba(212,255,0,.08);color:var(--adm-accent);
                        border-radius:99px;padding:2px 8px;'>{$p['cat_name']}</span>"
        : "<span style='font-size:.7rem;color:var(--adm-muted);'>Uncategorized</span>";

    $excerpt = $p['excerpt']
        ? (mb_strlen($p['excerpt']) > 80 ? mb_substr($p['excerpt'], 0, 80) . '...' : $p['excerpt'])
        : '';

    $tbody .= "
    <tr id='post-row-{$p['id']}'>
        <td>
            <div style='display:flex;align-items:center;gap:.75rem;'>
                {$cover}
                <div style='min-width:0;'>
                    <div style='font-weight:600;font-size:.85rem;color:var(--adm-text);
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px;'
                         title='" . htmlspecialchars($p['title']) . "'>
                        " . htmlspecialchars($p['title']) . "
                    </div>
                    <div style='font-size:.7rem;color:var(--adm-muted);margin-top:.15rem;'>{$p['slug']}</div>
                    " . ($excerpt ? "<div style='font-size:.72rem;color:var(--adm-muted);margin-top:.2rem;
                                               white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:280px;'>{$excerpt}</div>" : '') . "
                </div>
            </div>
        </td>
        <td>{$catBadge}</td>
        <td style='color:var(--adm-muted);font-size:.78rem;'>{$p['author']}</td>
        <td style='color:var(--adm-muted);font-size:.78rem;text-align:center;'>{$p['views']}</td>
        <td>{$statusBadge}</td>
        <td style='color:var(--adm-muted);font-size:.75rem;white-space:nowrap;'>" . date('d M Y', strtotime($p['created'])) . "</td>
        <td>
            <div class='adm-actions'>
                <a href='new-post.php?id={$p['id']}' class='adm-btn' title='Edit'>
                    <i class='fa-regular fa-pen'></i>
                </a>
                <a href='{$setting['website_url']}/blog/{$p['slug']}/' class='adm-btn' target='_blank' title='View'>
                    <i class='fa-regular fa-eye'></i>
                </a>
                <button class='adm-btn adm-btn-del' onclick='deletePost({$p['id']})' title='Delete'>
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