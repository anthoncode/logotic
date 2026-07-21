<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'All Pages';
require_once('../system/config-admin.php');

// Eliminar página
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $uid = (int)$_GET['id'];
    $stmt = $DB_con->prepare("SELECT cover_img FROM " . PFX . "custompages WHERE id = :id");
    $stmt->execute([':id' => $uid]);
    $pg = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($pg) {
        if (!empty($pg['cover_img'])) {
            $path = '../system/assets/uploads/pages/' . $pg['cover_img'];
            if (file_exists($path)) unlink($path);
        }
        $DB_con->prepare("DELETE FROM " . PFX . "custompages WHERE id = :id")->execute([':id' => $uid]);
    }
    header('Location: all-pages.php?msg=Page deleted successfully');
    exit;
}

// Stats
$totalPages     = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "custompages")->fetchColumn();
$publishedPages = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "custompages WHERE active = 1")->fetchColumn();
$draftPages     = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "custompages WHERE active = 0")->fetchColumn();
$restrictedPages = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "custompages WHERE level = 1")->fetchColumn();

if (isset($_GET['msg'])) $success = urldecode($_GET['msg']);

require_once('includes/header1.php');
?>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-file-lines" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">All Pages</h1>
            <p class="adm-page-sub">Manage static pages like About, Terms, Privacy</p>
        </div>
        <div style="margin-left:auto;">
            <a href="new-page.php" class="adm-save"
               style="margin-top:0;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
                <i class="fa-regular fa-plus"></i> New Page
            </a>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="adm-alert adm-alert-success" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="adm-stats">
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-file-lines"></i>
            <div class="adm-stat-num"><?php echo number_format($totalPages); ?></div>
            <div class="adm-stat-label">Total Pages</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-globe"></i>
            <div class="adm-stat-num" style="color:var(--adm-success);"><?php echo number_format($publishedPages); ?></div>
            <div class="adm-stat-label">Published</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-floppy-disk"></i>
            <div class="adm-stat-num" style="color:var(--adm-warning);"><?php echo number_format($draftPages); ?></div>
            <div class="adm-stat-label">Drafts</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-lock"></i>
            <div class="adm-stat-num" style="color:var(--adm-info);"><?php echo number_format($restrictedPages); ?></div>
            <div class="adm-stat-label">Members Only</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="adm-toolbar">
        <input type="text" id="adm-search" class="adm-search"
               placeholder="🔍 Search by title or slug...">
        <div class="adm-filter">
            <a class="adm-chip active" data-filter="all">All</a>
            <a class="adm-chip" data-filter="published">
                <i class="fa-solid fa-globe" style="font-size:.7rem;"></i> Published
            </a>
            <a class="adm-chip" data-filter="draft">
                <i class="fa-regular fa-floppy-disk" style="font-size:.7rem;"></i> Drafts
            </a>
        </div>
        <span id="adm-total" style="font-size:.78rem;color:var(--adm-muted);margin-left:auto;"></span>
    </div>

    <!-- Table -->
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Access</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="adm-tbody">
                <tr>
                    <td colspan="5" style="text-align:center;padding:2rem;color:var(--adm-muted);">
                        <i class="fa-regular fa-spinner fa-spin"></i> Loading...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="adm-pagination" id="adm-pagination"></div>

</div>

<script>
let currentSearch = '';
let currentFilter = 'all';
let currentPage   = 1;

function loadPages() {
    $('#adm-tbody').html(`
        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--adm-muted);">
            <i class="fa-regular fa-spinner fa-spin"></i> Loading...
        </td></tr>
    `);

    $.ajax({
        url: '<?php echo $setting['website_url']; ?>/admin/pages-table.php',
        type: 'POST',
        data: { search: currentSearch, filter: currentFilter, page: currentPage },
        success: function(res) {
            const data = JSON.parse(res);
            $('#adm-tbody').html(data.tbody ||
                '<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--adm-muted);">No pages found</td></tr>');
            $('#adm-pagination').html(data.pagination);
            $('#adm-total').text(data.total.toLocaleString() + ' pages found');

            $('#adm-pagination .adm-page-btn').off('click').on('click', function(e) {
                e.preventDefault();
                const p = $(this).data('page');
                if (p) { currentPage = parseInt(p); loadPages(); }
            });
        }
    });
}

let searchTimer;
$('#adm-search').on('input', function() {
    clearTimeout(searchTimer);
    currentSearch = this.value;
    currentPage   = 1;
    searchTimer   = setTimeout(loadPages, 400);
});

$('.adm-chip').on('click', function() {
    $('.adm-chip').removeClass('active');
    $(this).addClass('active');
    currentFilter = $(this).data('filter');
    currentPage   = 1;
    loadPages();
});

function deletePage(id) {
    if (!confirm('Delete this page permanently? Cover image will also be removed.')) return;
    window.location.href = 'all-pages.php?action=delete&id=' + id;
}

loadPages();
</script>

<?php require_once('includes/footer.php'); ?>