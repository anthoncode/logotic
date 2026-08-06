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
            <a class="adm-chip adm-chip-dup" data-filter="duplicates">
                <i class="fa-solid fa-clone" style="font-size:.7rem;"></i> Duplicates
                <span id="dupBadge" class="dup-badge" style="display:none;">0</span>
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

<!-- Modal de confirmación de borrado -->
<div id="delModal" class="del-modal-overlay">
    <div class="del-modal">
        <div class="del-modal-icon"><i class="fa-regular fa-trash-can"></i></div>
        <div class="del-modal-title">Delete logo?</div>
        <div class="del-modal-text">
            You're about to permanently delete <strong id="delModalName">this logo</strong>.
            The SVG file will also be removed. This action cannot be undone.
        </div>
        <div class="del-modal-actions">
            <button type="button" class="del-btn-cancel" id="delCancel">Cancel</button>
            <button type="button" class="del-btn-confirm" id="delConfirm">
                <i class="fa-regular fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<style>
.dup-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    margin-left: .35rem;
    border-radius: 99px;
    background: var(--adm-danger);
    color: #fff;
    font-size: .68rem;
    font-weight: 700;
    line-height: 1;
    animation: dupBlink 1.2s ease-in-out infinite;
}
@keyframes dupBlink {
    0%, 100% { opacity: 1; }
    50%      { opacity: .35; }
}
.del-modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(3px);
    z-index: 9999;
    align-items: center; justify-content: center;
    padding: 1rem;
}
.del-modal-overlay.show { display: flex; }
.del-modal {
    background: var(--adm-card);
    border: 1px solid var(--adm-border);
    border-radius: 16px;
    padding: 1.75rem;
    max-width: 400px; width: 100%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
    animation: delModalIn .18s ease;
}
@keyframes delModalIn { from { opacity: 0; transform: translateY(10px) scale(.97); } to { opacity: 1; transform: none; } }
.del-modal-icon {
    width: 52px; height: 52px; margin: 0 auto 1rem;
    border-radius: 50%;
    background: rgba(255,77,77,.12);
    color: var(--adm-danger);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
}
.del-modal-title { font-size: 1.1rem; font-weight: 700; color: var(--adm-text); margin-bottom: .5rem; }
.del-modal-text { font-size: .85rem; color: var(--adm-muted); line-height: 1.5; margin-bottom: 1.5rem; }
.del-modal-text strong { color: var(--adm-text); }
.del-modal-actions { display: flex; gap: .75rem; }
.del-btn-cancel, .del-btn-confirm {
    flex: 1; padding: .7rem; border-radius: 10px; font-size: .88rem; font-weight: 600;
    cursor: pointer; border: 1px solid transparent; transition: all .15s;
}
.del-btn-cancel { background: transparent; border-color: var(--adm-border); color: var(--adm-text); }
.del-btn-cancel:hover { background: rgba(255,255,255,.05); }
.del-btn-confirm { background: var(--adm-danger); color: #fff; }
.del-btn-confirm:hover { filter: brightness(1.1); }
.del-btn-confirm:disabled { opacity: .6; cursor: default; }
tr.row-removing { opacity: 0; transform: translateX(20px); transition: all .3s ease; }
</style>

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

            // Actualizar el marcador de duplicados
            if (typeof data.dupCount !== 'undefined') {
                const badge = document.getElementById('dupBadge');
                if (data.dupCount > 0) {
                    badge.textContent = data.dupCount;
                    badge.style.display = 'inline-flex';
                } else {
                    badge.style.display = 'none';
                }
            }

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

// ── Eliminar logo (modal oscuro + AJAX) ──
let delTargetId = null;

function deleteLogo(id, name) {
    delTargetId = id;
    document.getElementById('delModalName').textContent = name || 'this logo';
    document.getElementById('delModal').classList.add('show');
}

// Cerrar modal
document.getElementById('delCancel').addEventListener('click', function() {
    document.getElementById('delModal').classList.remove('show');
    delTargetId = null;
});
// Cerrar al hacer clic fuera del modal
document.getElementById('delModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.remove('show');
        delTargetId = null;
    }
});

// Confirmar borrado
document.getElementById('delConfirm').addEventListener('click', function() {
    if (!delTargetId) return;
    const btn = this;
    const id  = delTargetId;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-regular fa-spinner fa-spin"></i> Deleting...';

    $.ajax({
        url: '<?php echo $setting['website_url']; ?>/admin/ajax-delete-logo.php',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(res) {
            document.getElementById('delModal').classList.remove('show');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-regular fa-trash"></i> Delete';

            if (res.success) {
                // Quitar la fila con animación
                const row = document.getElementById('row-' + id);
                if (row) {
                    row.classList.add('row-removing');
                    setTimeout(function() {
                        row.remove();
                        // Si la tabla quedó vacía, recargar (para traer la siguiente página)
                        if ($('#adm-tbody tr').length === 0) {
                            loadLogos();
                        }
                    }, 300);
                }
                if (typeof toastr !== 'undefined') {
                    toastr.success('Logo deleted');
                }
            } else {
                if (typeof toastr !== 'undefined') toastr.error(res.message || 'Could not delete');
                else alert(res.message || 'Could not delete');
            }
            delTargetId = null;
        },
        error: function() {
            document.getElementById('delModal').classList.remove('show');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-regular fa-trash"></i> Delete';
            if (typeof toastr !== 'undefined') toastr.error('Server error');
            else alert('Server error');
            delTargetId = null;
        }
    });
});

// Carga inicial
loadLogos();
</script>

<?php require_once 'includes/footer.php'; ?>