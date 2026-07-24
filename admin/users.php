<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = "User Management";
require_once('../system/config-admin.php');

// Acciones
if (isset($_GET['action']) && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];
    switch ($_GET['action']) {
        case 'ban':
            $DB_con->prepare("UPDATE " . PFX . "users SET active = 0 WHERE id = :id")->execute([':id' => $uid]);
            header('Location: users.php?msg=User banned successfully');
            exit;
        case 'unban':
            $DB_con->prepare("UPDATE " . PFX . "users SET active = 1 WHERE id = :id")->execute([':id' => $uid]);
            header('Location: users.php?msg=User unbanned successfully');
            exit;
        case 'delete':
            $DB_con->prepare("DELETE FROM " . PFX . "users WHERE id = :id")->execute([':id' => $uid]);
            header('Location: users.php?msg=User deleted successfully');
            exit;
    }
}

// Eliminar en masa
if (isset($_POST['bulk_delete']) && !empty($_POST['selected_users'])) {
    $ids = array_map('intval', $_POST['selected_users']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $DB_con->prepare("DELETE FROM " . PFX . "users WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    header('Location: users.php?msg=' . count($ids) . ' users deleted');
    exit;
}

// Stats
$totalUsers  = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users")->fetchColumn();
$activeUsers = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE active = 1")->fetchColumn();
$bannedUsers = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE active = 0")->fetchColumn();
$unverified = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE active = 1 AND verified = 0")->fetchColumn();

if (isset($_GET['msg'])) $success = $_GET['msg'];

require_once('includes/header1.php');
?>


<div class="adm-wrap">

    <div class="adm-wrap">

        <!-- Page Header -->
        <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem;">
            <div style="background:rgba(212,255,0,.1); border:1px solid rgba(212,255,0,.2); border-radius:10px; width:38px; height:38px; display:flex; align-items:center; justify-content:center;">
                <i class="fa-regular fa-users" style="color:var(--adm-accent);"></i>
            </div>
            <div>
                <h1 style="font-size:1.1rem; font-weight:700; color:var(--adm-text); margin:0; line-height:1;">User Management</h1>
                <p style="font-size:.75rem; color:var(--adm-muted); margin:0;">Manage registered users, ban accounts and clean up bots</p>
            </div>
        </div>

        <!-- Stats -->
        <div class="adm-stats">
            <div class="adm-stat">
                <div class="adm-stat-num"><?php echo number_format($totalUsers); ?></div>
                <div class="adm-stat-label">Total Users</div>
            </div>
            <div class="adm-stat">
                <div class="adm-stat-num" style="color:var(--adm-success)"><?php echo number_format($activeUsers); ?></div>
                <div class="adm-stat-label">Active</div>
            </div>
            <div class="adm-stat">
                <div class="adm-stat-num" style="color:var(--adm-danger)"><?php echo number_format($bannedUsers); ?></div>
                <div class="adm-stat-label">Banned</div>
            </div>
            <div class="adm-stat">
                <div class="adm-stat-num" style="color:var(--adm-warning)"><?php echo number_format($unverified); ?></div>
                <div class="adm-stat-label">Unverified</div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="adm-toolbar">
            <input type="text" id="adm-search" class="adm-search" placeholder="🔍 Search by name, username or email...">
            <div class="adm-filter">
                <a class="adm-chip active" data-filter="all">All</a>
                <a class="adm-chip" data-filter="active">Active</a>
                <a class="adm-chip" data-filter="banned">Banned</a>
                <a class="adm-chip" data-filter="unverified">Unverified</a>
            </div>
            <span id="adm-total" style="font-size:.78rem;color:var(--adm-muted);"></span>
        </div>

        <!-- Bulk + Table -->
        <form method="POST" action="users.php" id="bulkForm">
            <div class="adm-toolbar" style="margin-bottom:.75rem;">
                <button type="submit" name="bulk_delete" class="adm-btn-danger"
                    onclick="return confirm('Delete selected users? This cannot be undone.')">
                    <i class="fa-regular fa-trash"></i> Delete selected
                </button>
            </div>

            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="adm-check" id="selectAll"></th>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th>Verified</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adm-tbody">

                        <tr>
                            <td colspan="9" style="text-align:center;padding:2rem;color:var(--adm-muted);">
                                <i class="fa-regular fa-spinner fa-spin"></i> Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Pagination -->
        <div class="adm-pagination" id="adm-pagination"></div>

    </div>

    <script>
        let currentSearch = '';
        let currentFilter = 'all';
        let currentPage = 1;

        function loadUsers() {
            $('#adm-tbody').html('<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--adm-muted);"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</td></tr>');

            $.ajax({
                url: '<?php echo $setting['website_url']; ?>/admin/users-table.php',
                type: 'POST',
                data: {
                    search: currentSearch,
                    filter: currentFilter,
                    page: currentPage
                },
                success: function(res) {
                    const data = JSON.parse(res);
                    $('#adm-tbody').html(data.tbody ||
                        '<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--adm-muted);">No users found</td></tr>');
                    $('#adm-pagination').html(data.pagination);
                    $('#adm-total').text(data.total + ' users found');
                    $('#selectAll').prop('checked', false);

                    // Rebind paginación
                    $('#adm-pagination .adm-page-btn').off('click').on('click', function(e) {
                        e.preventDefault();
                        currentPage = parseInt($(this).data('page'));
                        loadUsers();
                    });
                }
            });
        }

        // Búsqueda debounce
        let searchTimer;
        $('#adm-search').on('input', function() {
            clearTimeout(searchTimer);
            currentSearch = this.value;
            currentPage = 1;
            searchTimer = setTimeout(loadUsers, 400);
        });

        // Filtros
        $('.adm-chip').on('click', function() {
            $('.adm-chip').removeClass('active');
            $(this).addClass('active');
            currentFilter = $(this).data('filter');
            currentPage = 1;
            loadUsers();
        });

        // Select all
        $('#selectAll').on('change', function() {
            $('.user-check').prop('checked', this.checked);
        });

        loadUsers();
    </script>

    <?php require_once('includes/footer.php'); ?>