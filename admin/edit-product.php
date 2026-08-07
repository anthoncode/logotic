<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = "Edit Logo";
require_once '../system/config-admin.php';

if (!isset($_REQUEST['id'])) {
    require_once 'includes/header1.php';
    echo '<div class="adm-wrap"><div class="adm-alert adm-alert-error">Invalid request — no logo ID.</div></div>';
    require_once 'includes/footer.php';
    exit;
}

$productDetails = $product->details($_REQUEST['id']);
if (!$productDetails) {
    require_once 'includes/header1.php';
    echo '<div class="adm-wrap"><div class="adm-alert adm-alert-error">Logo not found.</div></div>';
    require_once 'includes/footer.php';
    exit;
}

$category = $product->get_categories();

// Subcategorías actuales para preseleccionar
$currentSubcats = $product->dispsubcategories($productDetails['cat_id']);

require_once 'includes/header1.php';
?>

<style>
.ep2-grid { display:grid; grid-template-columns:1fr 340px; gap:1rem; align-items:start; }
.ep2-toggle-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:.75rem 0; border-bottom:1px solid var(--adm-border);
}
.ep2-toggle-row:last-child { border-bottom:none; }
.ep2-toggle-label { font-size:.83rem; color:var(--adm-text); }
.ep2-toggle-label small { display:block; font-size:.7rem; color:var(--adm-muted); margin-top:.1rem; }
.ep2-toggle-label.danger { color:var(--adm-danger); }
.ep2-preview {
    width:100%; aspect-ratio:1; background:#fff; border-radius:12px;
    padding:1.5rem; display:flex; align-items:center; justify-content:center; margin-bottom:1rem;
}
.ep2-preview img { max-width:100%; max-height:100%; object-fit:contain; }
.ep2-dropzone {
    border:2px dashed var(--adm-border); border-radius:10px; padding:1.25rem;
    text-align:center; cursor:pointer; transition:all .2s; position:relative;
}
.ep2-dropzone:hover { border-color:var(--adm-accent); background:rgba(212,255,0,.03); }
.ep2-dropzone input[type=file] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.ep2-progress { height:6px; background:var(--adm-border); border-radius:99px; overflow:hidden; margin-top:.75rem; display:none; }
.ep2-progress-bar { height:100%; background:var(--adm-accent); width:0; border-radius:99px; transition:width .2s; }
.ep2-msg { font-size:.8rem; margin-top:.5rem; min-height:1rem; }
.ep2-msg .text-success { color:var(--adm-success); }
.ep2-msg .text-danger { color:var(--adm-danger); }
</style>

<div class="adm-wrap">

    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-file-pen" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">Edit Logo</h1>
            <p class="adm-page-sub"><?php echo htmlspecialchars($productDetails['name']); ?></p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            <a href="<?php echo $setting['website_url']; ?>/item.php?id=<?php echo $productDetails['id']; ?>" target="_blank" class="adm-topbar-btn">
                <i class="fa-regular fa-eye"></i> Preview
            </a>
            <a href="all-logos.php" class="adm-topbar-btn">
                <i class="fa-regular fa-list"></i> All Logos
            </a>
        </div>
    </div>

    <div class="ep2-grid">

        <!-- Columna principal: datos -->
        <div>
            <form id="editForm">
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-pen"></i> Logo Details</div>

                    <div class="adm-field">
                        <label class="adm-label">Name *</label>
                        <input class="adm-input" type="text" name="name" maxlength="70"
                               value="<?php echo htmlspecialchars($productDetails['name']); ?>" required>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label">Description</label>
                        <textarea class="adm-input" name="description" rows="4"
                                  style="resize:vertical;"><?php echo htmlspecialchars($productDetails['description']); ?></textarea>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                        <div class="adm-field">
                            <label class="adm-label">Category *</label>
                            <select class="adm-input" name="cat_id" id="cat_id" required>
                                <?php foreach ($category as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"
                                        <?php echo $productDetails['cat_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="adm-field">
                            <label class="adm-label">Subcategory</label>
                            <select class="adm-input" name="subcat" id="subcat">
                                <option value="">Select subcategory...</option>
                                <?php foreach ($currentSubcats as $sc): ?>
                                    <option value="<?php echo $sc['id']; ?>"
                                        <?php echo $productDetails['subc_id'] == $sc['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sc['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label">Website</label>
                        <input class="adm-input" type="url" name="website"
                               value="<?php echo htmlspecialchars($productDetails['website']); ?>"
                               placeholder="https://...">
                    </div>

                    <div class="adm-field" style="margin-bottom:0;">
                        <label class="adm-label">Tags</label>
                        <input class="adm-input" type="text" name="tags"
                               value="<?php echo htmlspecialchars($productDetails['tags']); ?>"
                               placeholder="comma, separated, tags">
                        <span style="font-size:.7rem;color:var(--adm-muted);margin-top:.3rem;display:block;">Separate tags with commas</span>
                    </div>

                    <input type="hidden" name="id" value="<?php echo $productDetails['id']; ?>">
                </div>

                <button class="adm-save" type="submit" id="saveBtn" style="width:100%;justify-content:center;display:flex;">
                    <i class="fa-regular fa-floppy-disk"></i> Update Logo
                </button>
                <div class="ep2-msg" id="dataMsg" style="text-align:center;"></div>
            </form>
        </div>

        <!-- Columna lateral -->
        <div>
            <!-- Preview + reemplazo SVG -->
            <div class="adm-card" style="margin-bottom:1rem;">
                <div class="adm-card-title"><i class="fa-regular fa-image"></i> Vector File</div>
                <div class="ep2-preview">
                    <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $productDetails['icon_img']; ?>"
                         alt="<?php echo htmlspecialchars($productDetails['name']); ?>">
                </div>
                <form id="fileForm">
                    <div class="ep2-dropzone">
                        <input type="file" name="iconimgfile" id="svgInput" accept="image/svg+xml">
                        <i class="fa-regular fa-cloud-arrow-up" style="font-size:1.5rem;color:var(--adm-muted);display:block;margin-bottom:.4rem;"></i>
                        <div style="font-size:.8rem;color:var(--adm-muted);" id="fileLabel">Replace SVG file</div>
                        <div style="font-size:.68rem;color:var(--adm-muted);margin-top:.2rem;">SVG only · max 5MB</div>
                    </div>
                    <div class="ep2-progress"><div class="ep2-progress-bar"></div></div>
                    <input type="hidden" name="id" value="<?php echo $productDetails['id']; ?>">
                    <button class="adm-save" type="submit" style="width:100%;justify-content:center;display:flex;margin-top:.75rem;background:var(--adm-success);color:#0d0f1c;">
                        <i class="fa-regular fa-arrows-rotate"></i> Replace File
                    </button>
                    <div class="ep2-msg" id="fileMsg" style="text-align:center;"></div>
                </form>
            </div>

            <!-- Status + toggles -->
            <div class="adm-card">
                <div class="adm-card-title"><i class="fa-regular fa-sliders"></i> Settings</div>

                <div class="adm-field">
                    <label class="adm-label">Status</label>
                    <select class="adm-input" name="status" id="statusSelect" form="editForm">
                        <?php $curStatus = $productDetails['status'] ?? 'approved'; ?>
                        <option value="approved" <?php echo $curStatus === 'approved' ? 'selected' : ''; ?>>Approved (visible on site)</option>
                        <option value="pending"  <?php echo $curStatus === 'pending'  ? 'selected' : ''; ?>>Pending (awaiting review)</option>
                        <option value="rejected" <?php echo $curStatus === 'rejected' ? 'selected' : ''; ?>>Rejected (hidden, auto-deleted in 30 days)</option>
                        <option value="inactive" <?php echo $curStatus === 'inactive' ? 'selected' : ''; ?>>Inactive (hidden on purpose)</option>
                    </select>
                </div>

                <div class="ep2-toggle-row">
                    <div class="ep2-toggle-label">Featured
                        <small>Show in featured sections</small>
                    </div>
                    <label class="adm-switch">
                        <input type="checkbox" name="featured" form="editForm" <?php echo $productDetails['featured'] == 1 ? 'checked' : ''; ?>>
                        <span class="adm-slider"></span>
                    </label>
                </div>

                <div class="ep2-toggle-row">
                    <div class="ep2-toggle-label danger">Disable Downloads
                        <small>Users can't download this logo</small>
                    </div>
                    <label class="adm-switch">
                        <input type="checkbox" name="download_off" form="editForm" <?php echo $productDetails['download_off'] == 1 ? 'checked' : ''; ?>>
                        <span class="adm-slider"></span>
                    </label>
                </div>

                <div class="ep2-toggle-row">
                    <div class="ep2-toggle-label danger">Hide View Counter
                        <small>Don't show view count</small>
                    </div>
                    <label class="adm-switch">
                        <input type="checkbox" name="views_off" form="editForm" <?php echo $productDetails['views_off'] == 1 ? 'checked' : ''; ?>>
                        <span class="adm-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cargar subcategorías al cambiar categoría
$('#cat_id').on('change', function() {
    $.ajax({
        type: 'GET',
        url: 'ajax-category.php',
        data: 'cat_id=' + $(this).val(),
        success: function(html) { $('#subcat').html(html); }
    });
});

// Guardar datos
$('#editForm').on('submit', function(e) {
    e.preventDefault();
    var btn = $('#saveBtn');
    btn.prop('disabled', true).html('<i class="fa-regular fa-spinner fa-spin"></i> Saving...');
    $.ajax({
        url: '<?php echo $setting['website_url']; ?>/admin/ajax-update.php',
        type: 'POST',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function(response) {
            $('#dataMsg').html(response);
            btn.prop('disabled', false).html('<i class="fa-regular fa-floppy-disk"></i> Update Logo');
        },
        error: function() {
            $('#dataMsg').html('<span class="text-danger">Connection error</span>');
            btn.prop('disabled', false).html('<i class="fa-regular fa-floppy-disk"></i> Update Logo');
        }
    });
});

// Mostrar nombre del archivo elegido
$('#svgInput').on('change', function() {
    var name = this.files[0] ? this.files[0].name : 'Replace SVG file';
    $('#fileLabel').text(name);
});

// Reemplazar archivo SVG con progreso
$('#fileForm').on('submit', function(e) {
    e.preventDefault();
    if (!$('#svgInput')[0].files[0]) {
        $('#fileMsg').html('<span class="text-danger">Select a file first</span>');
        return;
    }
    $('.ep2-progress').show();
    $.ajax({
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    var pct = parseInt((evt.loaded / evt.total) * 100);
                    $('.ep2-progress-bar').css('width', pct + '%');
                }
            }, false);
            return xhr;
        },
        url: '<?php echo $setting['website_url']; ?>/admin/ajax-update-files.php',
        type: 'POST',
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        beforeSend: function() { $('#fileMsg').html('Uploading...'); },
        success: function(response) {
            $('#fileMsg').html(response);
            // Recargar el preview tras 1s
            setTimeout(function() { location.reload(); }, 1200);
        },
        error: function() {
            $('#fileMsg').html('<span class="text-danger">Upload failed</span>');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>