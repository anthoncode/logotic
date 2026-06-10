<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Categories';
require_once('../system/config-admin.php');

// ── Acciones POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Agregar categoría
    if (isset($_POST['action']) && $_POST['action'] === 'add_cat') {
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $product->addcat($name);
        } else {
            $error = 'Category name is required';
        }
        header('Location: add-category.php?msg=Category added&tab=cat');
        exit;
    }

    // Agregar subcategoría
    if (isset($_POST['action']) && $_POST['action'] === 'add_scat') {
        $name  = trim($_POST['subname'] ?? '');
        $catid = (int)($_POST['cat_id'] ?? 0);
        if ($name && $catid) {
            $product->addscat($name, $catid);
        } else {
            $error = 'All fields are required';
        }
        header('Location: add-category.php?msg=Subcategory added&tab=scat');
        exit;
    }

    // Editar categoría inline
    if (isset($_POST['action']) && $_POST['action'] === 'edit_cat') {
        $id   = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        if ($id && $name) {
            $stmt = $DB_con->prepare("UPDATE " . PFX . "categories SET name = :name WHERE id = :id");
            $stmt->execute([':name' => $name, ':id' => $id]);
            echo json_encode(['success' => true, 'name' => $name]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Invalid data']);
        }
        exit;
    }

    // Editar subcategoría inline
    if (isset($_POST['action']) && $_POST['action'] === 'edit_scat') {
        $id    = (int)$_POST['id'];
        $name  = trim($_POST['name'] ?? '');
        $catid = (int)($_POST['cat_id'] ?? 0);
        if ($id && $name) {
            $stmt = $DB_con->prepare("UPDATE " . PFX . "subcat SET name = :name, cat_id = :cat_id WHERE id = :id");
            $stmt->execute([':name' => $name, ':cat_id' => $catid, ':id' => $id]);
            echo json_encode(['success' => true, 'name' => $name]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Invalid data']);
        }
        exit;
    }
}

// ── Acciones GET ──
if (isset($_GET['action'])) {
    if ($_GET['action'] === 'delete_cat' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $DB_con->prepare("DELETE FROM " . PFX . "categories WHERE id = :id")->execute([':id' => $id]);
        header('Location: add-category.php?msg=Category deleted&tab=cat');
        exit;
    }
    if ($_GET['action'] === 'delete_scat' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $DB_con->prepare("DELETE FROM " . PFX . "subcat WHERE id = :id")->execute([':id' => $id]);
        header('Location: add-category.php?msg=Subcategory deleted&tab=scat');
        exit;
    }
}

// ── Datos ──
$categories   = $product->get_categories();
$subcategories = $product->get_scategories();
$totalCat  = count($categories);
$totalScat = count($subcategories);
$activeTab = $_GET['tab'] ?? 'cat';

if (isset($_GET['msg'])) $success = $_GET['msg'];

require_once('includes/header1.php');
?>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-folder" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">Categories</h1>
            <p class="adm-page-sub">Manage categories and subcategories</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;align-items:center;">
            <div class="adm-stat" style="cursor:default;padding:.6rem 1rem;flex-direction:row;gap:.5rem;align-items:center;">
                <span class="adm-stat-num" style="font-size:1.2rem;"><?php echo $totalCat; ?></span>
                <span class="adm-stat-label" style="margin:0;">Categories</span>
            </div>
            <div class="adm-stat" style="cursor:default;padding:.6rem 1rem;flex-direction:row;gap:.5rem;align-items:center;">
                <span class="adm-stat-num" style="font-size:1.2rem;"><?php echo $totalScat; ?></span>
                <span class="adm-stat-label" style="margin:0;">Subcategories</span>
            </div>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="adm-alert adm-alert-success" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="adm-alert adm-alert-error" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-xmark"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="adm-tabs">
        <a class="adm-tab <?php echo $activeTab === 'cat' ? 'active' : ''; ?>" href="?tab=cat">
            <i class="fa-regular fa-folder"></i> Categories
        </a>
        <a class="adm-tab <?php echo $activeTab === 'scat' ? 'active' : ''; ?>" href="?tab=scat">
            <i class="fa-regular fa-folder-open"></i> Subcategories
        </a>
    </div>

    <!-- ── CATEGORIES TAB ── -->
    <?php if ($activeTab === 'cat'): ?>
    <div style="display:grid;grid-template-columns:300px 1fr;gap:1rem;align-items:start;">

        <!-- Form agregar -->
        <div class="adm-card">
            <div class="adm-card-title"><i class="fa-regular fa-plus"></i> Add Category</div>
            <form action="add-category.php" method="POST">
                <input type="hidden" name="action" value="add_cat">
                <div class="adm-field">
                    <label class="adm-label">Category Name *</label>
                    <input class="adm-input" type="text" name="name" required
                           placeholder="e.g. Brand Logos" maxlength="100" autofocus>
                </div>
                <button class="adm-save" type="submit" style="margin-top:.75rem;width:100%;justify-content:center;display:flex;">
                    <i class="fa-regular fa-plus"></i> Add Category
                </button>
            </form>
        </div>

        <!-- Tabla categorías -->
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th style="text-align:center;">Subcats</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat):
                        $scatCount = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "subcat WHERE cat_id = :id");
                        $scatCount->execute([':id' => $cat['id']]);
                        $scCount = $scatCount->fetchColumn();
                    ?>
                    <tr id="cat-row-<?php echo $cat['id']; ?>">
                        <td style="color:var(--adm-muted);font-size:.78rem;">#<?php echo $cat['id']; ?></td>
                        <td>
                            <span class="cat-view"><?php echo htmlspecialchars($cat['name']); ?></span>
                            <input class="adm-input cat-edit" type="text"
                                   value="<?php echo htmlspecialchars($cat['name']); ?>"
                                   style="display:none;font-size:.82rem;padding:.3rem .6rem;">
                        </td>
                        <td style="text-align:center;">
                            <span class="adm-badge" style="background:rgba(212,255,0,.1);color:var(--adm-accent);">
                                <?php echo $scCount; ?>
                            </span>
                        </td>
                        <td>
                            <div class="adm-actions">
                                <button class="adm-btn cat-edit-btn" data-id="<?php echo $cat['id']; ?>" title="Edit">
                                    <i class="fa-regular fa-pen"></i>
                                </button>
                                <button class="adm-btn adm-btn-unban cat-save-btn" data-id="<?php echo $cat['id']; ?>"
                                        style="display:none;" title="Save">
                                    <i class="fa-regular fa-floppy-disk"></i>
                                </button>
                                <button class="adm-btn cat-cancel-btn" data-id="<?php echo $cat['id']; ?>"
                                        style="display:none;" title="Cancel">
                                    <i class="fa-regular fa-xmark"></i>
                                </button>
                                <a href="?action=delete_cat&id=<?php echo $cat['id']; ?>&tab=cat"
                                   class="adm-btn adm-btn-del"
                                   onclick="return confirm('Delete category \'<?php echo htmlspecialchars($cat['name']); ?>\'? This will not delete its subcategories.')"
                                   title="Delete">
                                    <i class="fa-regular fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── SUBCATEGORIES TAB ── -->
    <?php elseif ($activeTab === 'scat'): ?>
    <div style="display:grid;grid-template-columns:300px 1fr;gap:1rem;align-items:start;">

        <!-- Form agregar subcategoría -->
        <div class="adm-card">
            <div class="adm-card-title"><i class="fa-regular fa-plus"></i> Add Subcategory</div>
            <form action="add-category.php" method="POST">
                <input type="hidden" name="action" value="add_scat">
                <div class="adm-field">
                    <label class="adm-label">Parent Category *</label>
                    <select class="adm-input" name="cat_id" required>
                        <option value="">Select category...</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="adm-field">
                    <label class="adm-label">Subcategory Name *</label>
                    <input class="adm-input" type="text" name="subname" required
                           placeholder="e.g. Technology" maxlength="100">
                </div>
                <button class="adm-save" type="submit" style="margin-top:.75rem;width:100%;justify-content:center;display:flex;">
                    <i class="fa-regular fa-plus"></i> Add Subcategory
                </button>
            </form>
        </div>

        <!-- Tabla subcategorías -->
        <div>
            <!-- Filtro por categoría padre -->
            <div class="adm-toolbar" style="margin-bottom:.75rem;">
                <div class="adm-filter" id="catFilter">
                    <a class="adm-chip active" data-cat="all">All</a>
                    <?php foreach ($categories as $cat): ?>
                        <a class="adm-chip" data-cat="<?php echo $cat['id']; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
                <span id="scatCount" style="font-size:.78rem;color:var(--adm-muted);margin-left:auto;">
                    <?php echo $totalScat; ?> subcategories
                </span>
            </div>

            <div class="adm-table-wrap">
                <table class="adm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Parent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="scatTableBody">
                        <?php foreach ($subcategories as $scat):
                            $catname = $product->catdetails($scat['cat_id']);
                        ?>
                        <tr id="scat-row-<?php echo $scat['id']; ?>" data-cat="<?php echo $scat['cat_id']; ?>">
                            <td style="color:var(--adm-muted);font-size:.78rem;">#<?php echo $scat['id']; ?></td>
                            <td>
                                <span class="scat-view"><?php echo htmlspecialchars($scat['name']); ?></span>
                                <input class="adm-input scat-edit-name" type="text"
                                       value="<?php echo htmlspecialchars($scat['name']); ?>"
                                       style="display:none;font-size:.82rem;padding:.3rem .6rem;">
                            </td>
                            <td>
                                <span class="scat-view-parent" style="font-size:.78rem;color:var(--adm-muted);">
                                    <?php echo htmlspecialchars($catname['name'] ?? ''); ?>
                                </span>
                                <select class="adm-input scat-edit-parent" style="display:none;font-size:.82rem;padding:.3rem .6rem;">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"
                                            <?php echo $cat['id'] == $scat['cat_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <div class="adm-actions">
                                    <button class="adm-btn scat-edit-btn" data-id="<?php echo $scat['id']; ?>" title="Edit">
                                        <i class="fa-regular fa-pen"></i>
                                    </button>
                                    <button class="adm-btn adm-btn-unban scat-save-btn" data-id="<?php echo $scat['id']; ?>"
                                            style="display:none;" title="Save">
                                        <i class="fa-regular fa-floppy-disk"></i>
                                    </button>
                                    <button class="adm-btn scat-cancel-btn" data-id="<?php echo $scat['id']; ?>"
                                            style="display:none;" title="Cancel">
                                        <i class="fa-regular fa-xmark"></i>
                                    </button>
                                    <a href="?action=delete_scat&id=<?php echo $scat['id']; ?>&tab=scat"
                                       class="adm-btn adm-btn-del"
                                       onclick="return confirm('Delete subcategory \'<?php echo htmlspecialchars($scat['name']); ?>\'?')"
                                       title="Delete">
                                        <i class="fa-regular fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
// ── Inline edit CATEGORÍAS ──
$(document).on('click', '.cat-edit-btn', function() {
    var id  = $(this).data('id');
    var row = $('#cat-row-' + id);
    row.find('.cat-view').hide();
    row.find('.cat-edit').show().focus();
    row.find('.cat-edit-btn').hide();
    row.find('.cat-save-btn, .cat-cancel-btn').show();
});

$(document).on('click', '.cat-cancel-btn', function() {
    var id  = $(this).data('id');
    var row = $('#cat-row-' + id);
    row.find('.cat-edit').hide();
    row.find('.cat-view').show();
    row.find('.cat-save-btn, .cat-cancel-btn').hide();
    row.find('.cat-edit-btn').show();
});

$(document).on('click', '.cat-save-btn', function() {
    var id   = $(this).data('id');
    var row  = $('#cat-row-' + id);
    var btn  = $(this);
    var name = row.find('.cat-edit').val().trim();

    if (!name) { toastr["warning"]("Name cannot be empty", "Required"); return; }

    btn.html('<i class="fa-regular fa-spinner fa-spin"></i>').prop('disabled', true);

    $.ajax({
        url: 'add-category.php',
        method: 'POST',
        data: { action: 'edit_cat', id: id, name: name },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                row.find('.cat-view').text(res.name).show();
                row.find('.cat-edit').val(res.name).hide();
                row.find('.cat-save-btn, .cat-cancel-btn').hide();
                row.find('.cat-edit-btn').show();
                toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "2000" };
                toastr["success"](res.name, "Category updated");
            } else {
                toastr["error"](res.msg, "Error");
            }
            btn.html('<i class="fa-regular fa-floppy-disk"></i>').prop('disabled', false);
        }
    });
});

// ── Inline edit SUBCATEGORÍAS ──
$(document).on('click', '.scat-edit-btn', function() {
    var id  = $(this).data('id');
    var row = $('#scat-row-' + id);
    row.find('.scat-view, .scat-view-parent').hide();
    row.find('.scat-edit-name, .scat-edit-parent').show();
    row.find('.scat-edit-name').focus();
    row.find('.scat-edit-btn').hide();
    row.find('.scat-save-btn, .scat-cancel-btn').show();
});

$(document).on('click', '.scat-cancel-btn', function() {
    var id  = $(this).data('id');
    var row = $('#scat-row-' + id);
    row.find('.scat-edit-name, .scat-edit-parent').hide();
    row.find('.scat-view, .scat-view-parent').show();
    row.find('.scat-save-btn, .scat-cancel-btn').hide();
    row.find('.scat-edit-btn').show();
});

$(document).on('click', '.scat-save-btn', function() {
    var id    = $(this).data('id');
    var row   = $('#scat-row-' + id);
    var btn   = $(this);
    var name  = row.find('.scat-edit-name').val().trim();
    var catId = row.find('.scat-edit-parent').val();
    var catName = row.find('.scat-edit-parent option:selected').text();

    if (!name) { toastr["warning"]("Name cannot be empty", "Required"); return; }

    btn.html('<i class="fa-regular fa-spinner fa-spin"></i>').prop('disabled', true);

    $.ajax({
        url: 'add-category.php',
        method: 'POST',
        data: { action: 'edit_scat', id: id, name: name, cat_id: catId },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                row.find('.scat-view').text(res.name).show();
                row.find('.scat-view-parent').text(catName).show();
                row.find('.scat-edit-name').val(res.name).hide();
                row.find('.scat-edit-parent').hide();
                row.find('.scat-save-btn, .scat-cancel-btn').hide();
                row.find('.scat-edit-btn').show();
                row.attr('data-cat', catId);
                toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "2000" };
                toastr["success"](res.name, "Subcategory updated");
            } else {
                toastr["error"](res.msg, "Error");
            }
            btn.html('<i class="fa-regular fa-floppy-disk"></i>').prop('disabled', false);
        }
    });
});

// ── Filtro por categoría padre ──
$('#catFilter .adm-chip').on('click', function() {
    $('#catFilter .adm-chip').removeClass('active');
    $(this).addClass('active');
    var cat = $(this).data('cat');
    var rows = $('#scatTableBody tr');
    var visible = 0;

    rows.each(function() {
        if (cat === 'all' || $(this).data('cat') == cat) {
            $(this).show();
            visible++;
        } else {
            $(this).hide();
        }
    });
    $('#scatCount').text(visible + ' subcategories');
});
</script>

<?php require_once('includes/footer.php'); ?>