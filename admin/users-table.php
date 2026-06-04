<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once('../system/config-admin.php');

$search  = $_POST['search'] ?? '';
$filter  = $_POST['filter'] ?? 'all';
$page    = max(1, (int)($_POST['page'] ?? 1));
$maxres  = 25;
$start   = ($page - 1) * $maxres;

$where  = '1=1';
$params = [];

if ($search) {
    $where .= " AND (fname LIKE :search OR username LIKE :search OR email LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}
if ($filter === 'active') {
    $where .= " AND active = 1 AND verified = 1";
}
if ($filter === 'banned') {
    $where .= " AND active = 0";
}
if ($filter === 'unverified') {
    $where .= " AND verified = 0";
}

$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "users WHERE $where");
$countStmt->execute($params);
$num   = $countStmt->fetchColumn();
$pages = ceil($num / $maxres);

$params[':start']  = $start;
$params[':maxres'] = $maxres;
$stmt = $DB_con->prepare("SELECT * FROM " . PFX . "users WHERE $where ORDER BY id DESC LIMIT :start, :maxres");
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$allUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$avatarColors = ['#e63946', '#1d7af3', '#2dc653', '#f18d35', '#8b5cf6', '#ec4899', '#06b6d4', '#f4d03f'];

// Devolver JSON con el HTML de la tabla y la paginación
$tbody = '';
foreach ($allUsers as $u) {
    $initial   = strtoupper(mb_substr($u['fname'], 0, 1));
    $color     = $avatarColors[$u['id'] % count($avatarColors)];
    $isActive  = $u['active'] == 1;
    $isVerified = $u['verified'] == 1;
    $isMod     = $u['moderator'] == 1;
    $daysAgo   = (new DateTime())->diff(new DateTime($u['created']))->days;
    $isNew     = $daysAgo <= 7;

    $statusBadge = !$isActive
        ? '<span class="adm-badge adm-badge-banned"><i class="fa-solid fa-ban"></i> Banned</span>'
        : (!$isVerified
            ? '<span class="adm-badge adm-badge-pending"><i class="fa-regular fa-clock"></i> Unverified</span>'
            : '<span class="adm-badge adm-badge-active"><i class="fa-solid fa-circle-check"></i> Active</span>');

    $roleBadge = $isMod
        ? '<span class="adm-badge" style="background:rgba(139,92,246,.15);color:#8b5cf6;"><i class="fa-solid fa-shield"></i> Mod</span>'
        : '<span style="color:var(--adm-muted);font-size:.78rem;">User</span>';

    $banBtn = $isActive
        ? '<a href="users.php?action=ban&id=' . $u['id'] . '" class="adm-btn adm-btn-ban" onclick="return confirm(\'Ban this user?\')"><i class="fa-solid fa-ban"></i> Ban</a>'
        : '<a href="users.php?action=unban&id=' . $u['id'] . '" class="adm-btn adm-btn-unban"><i class="fa-regular fa-circle-check"></i> Unban</a>';

    $newBadge = $isNew ? '<span class="adm-badge" style="background:rgba(212,255,0,.15);color:#d4ff00;font-size:.65rem;">NEW</span>' : '';

    $tbody .= "
    <tr>
        <td><input type='checkbox' class='adm-check user-check' name='selected_users[]' value='{$u['id']}'></td>
        <td style='color:var(--adm-muted);font-size:.78rem;'>#{$u['id']}</td>
        <td>
            <div class='adm-user-cell'>
                <div class='adm-avatar' style='background:{$color}20;color:{$color};border:1.5px solid {$color}40;'>{$initial}</div>
                <div>
                    <div class='adm-user-name'>" . htmlspecialchars($u['fname']) . " {$newBadge}</div>
                    <div class='adm-user-username'>@" . htmlspecialchars($u['username']) . "</div>
                </div>
            </div>
        </td>
        <td style='color:var(--adm-muted);'>" . htmlspecialchars($u['email']) . "</td>
        <td style='color:var(--adm-muted);font-size:.78rem;'>" . date('d M Y', strtotime($u['created'])) . "<div style='font-size:.7rem;'>{$daysAgo}d ago</div></td>
        <td>{$statusBadge}</td>
        <td>{$roleBadge}</td>
        <td>
            <div class='adm-actions'>
                <a href='edit-user.php?id={$u['username']}' class='adm-btn'><i class='fa-regular fa-pen'></i> Edit</a>
                {$banBtn}
                <a href='users.php?action=delete&id={$u['id']}' class='adm-btn adm-btn-del' onclick=\"return confirm('Permanently delete this user?')\"><i class='fa-regular fa-trash'></i></a>
            </div>
        </td>
    </tr>";
}

// Paginación con ellipsis
$paginationHtml = '';
if ($pages > 1) {
    $range = 2; // páginas a cada lado de la actual

    // Botón anterior
    if ($page > 1) {
        $paginationHtml .= "<a href='#' class='adm-page-btn' data-page='" . ($page - 1) . "'>←</a>";
    }

    for ($i = 1; $i <= $pages; $i++) {
        // Mostrar: primera, última, y las cercanas a la actual
        $showPage = $i == 1 || $i == $pages || ($i >= $page - $range && $i <= $page + $range);

        if ($showPage) {
            $active = $i == $page ? 'active' : '';
            $paginationHtml .= "<a href='#' class='adm-page-btn {$active}' data-page='{$i}'>{$i}</a>";
        } else {
            // Ellipsis — solo una vez entre grupos
            if ($i == 2 || $i == $pages - 1) {
                $paginationHtml .= "<span class='adm-page-btn' style='cursor:default;border:none;color:var(--adm-muted);'>...</span>";
            }
        }
    }

    // Botón siguiente
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
