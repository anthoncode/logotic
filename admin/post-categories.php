<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Blog Categories';
require_once('../system/config-admin.php');

// Helper para generar slug
function makeSlug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
    $str = preg_replace('/[\s-]+/', '-', $str);
    return trim($str, '-');
}

// ── Acciones POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Agregar categoría
    if (($_POST['action'] ?? '') === 'add_cat') {
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        $slug = makeSlug($name);

        if (empty($name)) {
            $error = 'Category name is required';
        } else {
            // Verificar slug único
            $chk = $DB_con->prepare("SELECT id FROM " . PFX . "post_categories WHERE slug = :slug");
            $chk->execute([':slug' => $slug]);
            if ($chk->fetchColumn()) {
                $slug .= '-' . time();
            }
            $stmt = $DB_con->prepare("
                INSERT INTO " . PFX . "post_categories (name, slug, description, active)
                VALUES (:name, :slug, :desc, 1)
            ");
            $stmt->execute([':name' => $name, ':slug' => $slug, ':desc' => $desc]);
            header('Location: post-categories.php?msg=' . urlencode('Category added'));
            exit;
        }
    }

    // Editar categoría inline
    if (($_POST['action'] ?? '') === 'edit_cat') {
        $id   = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $desc = trim($_POST['description'] ?? '');
        if ($id && $name) {
            $stmt = $DB_con->prepare("
                UPDATE " . PFX . "post_categories
                SET name = :name, description = :desc
                WHERE id = :id
            ");
            $stmt->execute([':name' => $name, ':desc' => $desc, ':id' => $id]);
            echo json_encode(['success' => true, 'name' => htmlspecialchars($name), 'description' => htmlspecialchars($desc)]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'Invalid data']);
        }
        exit;
    }

    // Toggle activo
    if (($_POST['action'] ?? '') === 'toggle_active') {
        $id     = (int)$_POST['id'];
        $active = (int)$_POST['active'];
        $stmt = $DB_con->prepare("UPDATE " . PFX . "post_categories SET active = :active WHERE id = :id");
        $stmt->execute([':active' => $active, ':id' => $id]);
        echo json_encode(['success' => true]);
        exit;
    }
}

// ── Eliminar ──
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    // Verificar que no tenga posts
    $chk = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "posts WHERE category_id = :id");
    $chk->execute([':id' => $id]);
    if ($chk->fetchColumn() > 0) {
        header('Location: post-categories.php?error=' . urlencode('Cannot delete: category has posts assigned'));
        exit;
    }
    $DB_con->prepare("DELETE FROM " . PFX . "post_categories WHERE id = :id")->execute([':id' => $id]);
    header('Location: post-categories.php?msg=' . urlencode('Category deleted'));
    exit;
}

// ── Datos ──
$categories = $DB_con->query("
    SELECT pc.*, COUNT(p.id) as post_count
    FROM " . PFX . "post_categories pc
    LEFT JOIN " . PFX . "posts p ON p.category_id = pc.id
    GROUP BY pc.id
    ORDER BY pc.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

$totalCats = count($categories);

if (isset($_GET['msg']))   $success = urldecode($_GET['msg']);
if (isset($_GET['error'])) $error   = urldecode($_GET['error']);

require_once('includes/header1.php');
?>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-folder-tree" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">Blog Categories</h1>
            <p class="adm-page-sub">Organize your blog posts into categories</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            <a href="all-posts.php" class="adm-topbar-btn">
                <i class="fa-regular fa-newspaper"></i> All Posts
            </a>
            <a href="new-post.php" class="adm-topbar-btn">
                <i class="fa-regular fa-plus"></i> New Post
            </a>
        </div>
    </div>

    <?php if (isset($success)): ?>
        <div class="adm-alert adm-alert-success" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="adm-alert adm-alert-error" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-xmark"></i> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:320px 1fr;gap:1rem;align-items:start;">

        <!-- Form agregar -->
        <div class="adm-card">
            <div class="adm-card-title"><i class="fa-regular fa-plus"></i> Add Category</div>
            <form action="post-categories.php" method="POST" id="addForm">
                <input type="hidden" name="action" value="add_cat">
                <div class="adm-field">
                    <label class="adm-label">Category Name *</label>
                    <input class="adm-input" type="text" name="name" id="catName" required
                           placeholder="e.g. Design Tips" maxlength="100" autofocus>
                </div>
                <div class="adm-field">
                    <label class="adm-label">Slug <span style="font-weight:400;color:var(--adm-muted);">(auto-generated)</span></label>
                    <input class="adm-input" type="text" id="catSlug" disabled
                           placeholder="design-tips"
                           style="opacity:.7;font-size:.82rem;">
                </div>
                <div class="adm-field">
                    <label class="adm-label">Description</label>
                    <textarea class="adm-input" name="description" rows="2"
                              placeholder="Short description (optional)" maxlength="255"></textarea>
                </div>
                <button class="adm-save" type="submit" style="width:100%;justify-content:center;display:flex;">
                    <i class="fa-regular fa-plus"></i> Add Category
                </button>
            </form>
        </div>

        <!-- Tabla -->
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th style="text-align:center;">Posts</th>
                        <th style="text-align:center;">Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--adm-muted);">
                            No categories yet. Create your first one.
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                        <tr id="cat-row-<?php echo $cat['id']; ?>">
                            <td>
                                <span class="cat-view-name" style="font-weight:600;"><?php echo htmlspecialchars($cat['name']); ?></span>
                                <input class="adm-input cat-edit-name" type="text"
                                       value="<?php echo htmlspecialchars($cat['name']); ?>"
                                       style="display:none;font-size:.82rem;padding:.3rem .6rem;">
                                <?php if ($cat['description']): ?>
                                    <div class="cat-view-desc" style="font-size:.72rem;color:var(--adm-muted);margin-top:.2rem;">
                                        <?php echo htmlspecialchars($cat['description']); ?>
                                    </div>
                                <?php endif; ?>
                                <input class="adm-input cat-edit-desc" type="text"
                                       value="<?php echo htmlspecialchars($cat['description'] ?? ''); ?>"
                                       placeholder="Description"
                                       style="display:none;font-size:.78rem;padding:.3rem .6rem;margin-top:.3rem;">
                            </td>
                            <td style="font-size:.75rem;color:var(--adm-muted);"><?php echo htmlspecialchars($cat['slug']); ?></td>
                            <td style="text-align:center;">
                                <span class="adm-badge" style="background:rgba(212,255,0,.1);color:var(--adm-accent);">
                                    <?php echo $cat['post_count']; ?>
                                </span>
                            </td>
                            <td style="text-align:center;">
                                <label class="adm-switch" style="display:inline-block;">
                                    <input type="checkbox" class="cat-toggle" data-id="<?php echo $cat['id']; ?>"
                                           <?php echo $cat['active'] == 1 ? 'checked' : ''; ?>>
                                    <span class="adm-slider"></span>
                                </label>
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
                                    <a href="?action=delete&id=<?php echo $cat['id']; ?>"
                                       class="adm-btn adm-btn-del"
                                       onclick="return confirm('Delete category \'<?php echo htmlspecialchars($cat['name']); ?>\'?')"
                                       title="Delete">
                                        <i class="fa-regular fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// ── Auto-slug en el form de agregar ──
function makeSlug(str) {
    return str.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim().replace(/[\s-]+/g, '-').replace(/^-+|-+$/g, '');
}
document.getElementById('catName').addEventListener('input', function() {
    document.getElementById('catSlug').value = makeSlug(this.value);
});

// ── Inline edit ──
$(document).on('click', '.cat-edit-btn', function() {
    var id = $(this).data('id');
    var row = $('#cat-row-' + id);
    row.find('.cat-view-name, .cat-view-desc').hide();
    row.find('.cat-edit-name, .cat-edit-desc').show();
    row.find('.cat-edit-name').focus();
    row.find('.cat-edit-btn').hide();
    row.find('.cat-save-btn, .cat-cancel-btn').show();
});

$(document).on('click', '.cat-cancel-btn', function() {
    var id = $(this).data('id');
    var row = $('#cat-row-' + id);
    row.find('.cat-edit-name, .cat-edit-desc').hide();
    row.find('.cat-view-name, .cat-view-desc').show();
    row.find('.cat-save-btn, .cat-cancel-btn').hide();
    row.find('.cat-edit-btn').show();
});

$(document).on('click', '.cat-save-btn', function() {
    var id   = $(this).data('id');
    var row  = $('#cat-row-' + id);
    var btn  = $(this);
    var name = row.find('.cat-edit-name').val().trim();
    var desc = row.find('.cat-edit-desc').val().trim();

    if (!name) { toastr["warning"]("Name cannot be empty", "Required"); return; }

    btn.html('<i class="fa-regular fa-spinner fa-spin"></i>').prop('disabled', true);

    $.ajax({
        url: 'post-categories.php',
        method: 'POST',
        data: { action: 'edit_cat', id: id, name: name, description: desc },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                row.find('.cat-view-name').text(res.name).show();
                if (res.description) {
                    if (row.find('.cat-view-desc').length) {
                        row.find('.cat-view-desc').text(res.description).show();
                    }
                }
                row.find('.cat-edit-name, .cat-edit-desc').hide();
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

// ── Toggle activo ──
$(document).on('change', '.cat-toggle', function() {
    var id = $(this).data('id');
    var active = this.checked ? 1 : 0;
    $.ajax({
        url: 'post-categories.php',
        method: 'POST',
        data: { action: 'toggle_active', id: id, active: active },
        dataType: 'json',
        success: function(res) {
            toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "1500" };
            toastr["success"](active ? "Category enabled" : "Category disabled");
        }
    });
});
</script>

<?php require_once('includes/footer.php'); ?>