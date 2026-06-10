<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = "All Logos";
require_once '../system/config-admin.php';

// Eliminar logo
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $uid  = (int)$_GET['id'];
    $stmt = $DB_con->prepare("SELECT icon_img FROM " . PFX . "products WHERE id = :id");
    $stmt->execute([':id' => $uid]);
    $logo = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($logo) {
        $filePath = '../system/assets/uploads/vector-files/' . $logo['icon_img'];
        if (file_exists($filePath)) unlink($filePath);
        $DB_con->prepare("DELETE FROM " . PFX . "products WHERE id = :id")->execute([':id' => $uid]);
    }
    header('Location: all-logos.php?msg=Logo deleted successfully');
    exit;
}

// Stats
$totalLogos     = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products")->fetchColumn();
$activeLogos    = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE active = 1")->fetchColumn();
$inactiveLogos  = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE active = 0")->fetchColumn();
$featuredLogos  = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE featured = 1")->fetchColumn();
$totalViews     = $DB_con->query("SELECT SUM(views) FROM " . PFX . "products WHERE active = 1")->fetchColumn() ?? 0;
$totalDownloads = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "downloads")->fetchColumn();

if (isset($_GET['msg'])) $success = $_GET['msg'];

require_once 'includes/header1.php';
?>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-images" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">All Logos</h1>
            <p class="adm-page-sub">Manage, search and edit your logo collection</p>
        </div>
        <div style="margin-left:auto;">
            <a href="add-product.php" class="adm-save"
               style="margin-top:0;text-decoration:none;display:inline-flex;align-items:center;gap:.4rem;">
                <i class="fa-regular fa-plus"></i> Add Logo
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
            <i class="adm-stat-icon fa-solid fa-images"></i>
            <div class="adm-stat-num"><?php echo number_format($totalLogos); ?></div>
            <div class="adm-stat-label">Total Logos</div>
            <div class="adm-stat-link">
                <span style="color:var(--adm-success);"><?php echo number_format($activeLogos); ?> active</span>
                &nbsp;·&nbsp;
                <span style="color:var(--adm-danger);"><?php echo number_format($inactiveLogos); ?> inactive</span>
            </div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-star"></i>
            <div class="adm-stat-num" style="color:var(--adm-warning);"><?php echo number_format($featuredLogos); ?></div>
            <div class="adm-stat-label">Featured</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-eye"></i>
            <div class="adm-stat-num" style="color:var(--adm-info);"><?php echo number_format($totalViews); ?></div>
            <div class="adm-stat-label">Total Views</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-download"></i>
            <div class="adm-stat-num" style="color:var(--adm-warning);"><?php echo number_format($totalDownloads); ?></div>
            <div class="adm-stat-label">Total Downloads</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="adm-toolbar">
        <input type="text" id="adm-search" class="adm-search"
               placeholder="🔍 Search by name, tags or slug...">
        <div class="adm-filter">
            <a class="adm-chip active" data-filter="all">All</a>
            <a class="adm-chip" data-filter="active">Active</a>
            <a class="adm-chip" data-filter="inactive">Inactive</a>
            <a class="adm-chip" data-filter="featured">
                <i class="fa-solid fa-star" style="font-size:.7rem;"></i> Featured
            </a>
        </div>
        <span id="adm-total" style="font-size:.78rem;color:var(--adm-muted);margin-left:auto;"></span>
    </div>

    <!-- Table -->
    <div class="adm-table-wrap">
        <table class="adm-table">
            <thead>
                <tr>
                    <th>Logo</th>
                    <th>Tags</th>
                    <th>Website</th>
                    <th style="text-align:center;"><i class="fa-solid fa-eye"></i></th>
                    <th style="text-align:center;"><i class="fa-solid fa-download"></i></th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="adm-tbody">
                <tr>
                    <td colspan="8" style="text-align:center;padding:2rem;color:var(--adm-muted);">
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

function loadLogos() {
    $('#adm-tbody').html(`
        <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--adm-muted);">
            <i class="fa-regular fa-spinner fa-spin"></i> Loading...
        </td></tr>
    `);

    $.ajax({
        url: '<?php echo $setting['website_url']; ?>/admin/ajax-logos-table.php',
        type: 'POST',
        data: { search: currentSearch, filter: currentFilter, page: currentPage },
        success: function(res) {
            const data = JSON.parse(res);
            $('#adm-tbody').html(data.tbody ||
                '<tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--adm-muted);">No logos found</td></tr>');
            $('#adm-pagination').html(data.pagination);
            $('#adm-total').text(data.total.toLocaleString() + ' logos found');

            // Rebind paginación
            $('#adm-pagination .adm-page-btn').off('click').on('click', function(e) {
                e.preventDefault();
                const p = $(this).data('page');
                if (p) { currentPage = parseInt(p); loadLogos(); }
            });
        }
    });
}

// Búsqueda debounce
let searchTimer;
$('#adm-search').on('input', function() {
    clearTimeout(searchTimer);
    currentSearch = this.value;
    currentPage   = 1;
    searchTimer   = setTimeout(loadLogos, 400);
});

// Filtros
$('.adm-chip').on('click', function() {
    $('.adm-chip').removeClass('active');
    $(this).addClass('active');
    currentFilter = $(this).data('filter');
    currentPage   = 1;
    loadLogos();
});

// ── Inline edit — delegación de eventos para filas cargadas por AJAX ──
$(document).on('click', '.editbutton', function() {
    var row = $(this).closest('.id_table');
    row.find('.editValue').hide();
    row.find('.editInput').show().first().focus();
    row.find('.savebutton').show();
    $(this).hide();
});

$(document).on('click', '.savebutton', function() {
    var row  = $(this).closest('.id_table');
    var id   = row.attr('id').replace('row-', '');
    var btn  = $(this);
    var form = row.find('.editInput').serializeArray();
    form.push({ name: 'id', value: id });

    btn.html('<i class="fa-regular fa-spinner fa-spin"></i>').prop('disabled', true);

    $.ajax({
        method: 'POST',
        url: '<?php echo $setting['website_url']; ?>/admin/edit-list.php',
        data: form,
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "4000" };
                toastr["error"](response.message, "Error");
                btn.html('<i class="fa-regular fa-floppy-disk"></i>').prop('disabled', false);
            } else {
                // Actualizar valores visibles
                row.find('.editValue.name').text(response.member.name);
                row.find('.editValue.tags').text(response.member.tags);
                row.find('.editValue.website').text(response.member.website || '');
                // Actualizar inputs
                row.find('.editInput.name').val(response.member.name);
                row.find('.editInput.tags').val(response.member.tags);
                row.find('.editInput.website').val(response.member.website || '');
                // Restaurar estado visual
                row.find('.editInput').hide();
                row.find('.editValue').show();
                row.find('.editbutton').show();
                btn.html('<i class="fa-regular fa-floppy-disk"></i>').prop('disabled', false).hide();

                toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "2000" };
                toastr["success"](response.member.name, "Saved!");
            }
        },
        error: function() {
            toastr["error"]("Connection error — try again", "Error");
            btn.html('<i class="fa-regular fa-floppy-disk"></i>').prop('disabled', false);
        }
    });
});

// ── Eliminar logo ──
function deleteLogo(id) {
    if (!confirm('Delete this logo permanently? The SVG file will also be removed.')) return;
    window.location.href = 'all-logos.php?action=delete&id=' + id;
}

// Carga inicial
loadLogos();
</script>

<?php require_once 'includes/footer.php'; ?>