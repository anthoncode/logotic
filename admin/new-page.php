<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-admin.php');

$editId = (int)($_GET['id'] ?? 0);
$isEdit = $editId > 0;
$page   = null;

if ($isEdit) {
    $stmt = $DB_con->prepare("SELECT * FROM " . PFX . "custompages WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $page = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$page) { header('Location: all-pages.php'); exit; }
    $pageTitle = 'Edit Page';
} else {
    $pageTitle = 'New Page';
}

$v = [
    'title'      => $page['title']      ?? '',
    'slug'       => $page['slug_page']  ?? '',
    'content'    => $page['content']    ?? '',
    'excerpt'    => $page['excerpt']    ?? '',
    'cover_img'  => $page['cover_img']  ?? '',
    'level'      => $page['level']      ?? 0,
    'active'     => $page['active']     ?? 1,
    'meta_title' => $page['meta_title'] ?? '',
    'meta_desc'  => $page['meta_desc']  ?? '',
];

require_once('includes/header1.php');
?>

<script src="https://cdn.jsdelivr.net/npm/tinymce@5/tinymce.min.js" referrerpolicy="origin"></script>

<style>
.page-editor-wrap { display:grid; grid-template-columns:1fr 300px; gap:1rem; align-items:start; }
.slug-preview { font-size:.72rem; color:var(--adm-muted); margin-top:.3rem; display:flex; align-items:center; gap:.3rem; flex-wrap:wrap; }
.slug-preview span { color:var(--adm-accent); font-weight:600; }
.cover-upload-zone { border:2px dashed var(--adm-border); border-radius:10px; padding:1.5rem; text-align:center; cursor:pointer; transition:all .2s; position:relative; overflow:hidden; }
.cover-upload-zone:hover { border-color:var(--adm-accent); background:rgba(212,255,0,.03); }
.cover-upload-zone input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.cover-upload-zone img { max-width:100%; border-radius:8px; display:none; margin-top:.75rem; }
.cover-upload-zone.has-image .cover-placeholder { display:none; }
.cover-upload-zone.has-image img { display:block; margin-top:0; }
.char-counter { font-size:.7rem; color:var(--adm-muted); float:right; }
.char-counter.warn { color:var(--adm-warning); }
.char-counter.over { color:var(--adm-danger); }
.tox .tox-toolbar, .tox .tox-toolbar__primary { background:#13152a !important; }
.tox-tinymce { border-color:var(--adm-border) !important; border-radius:10px !important; }
.save-status { font-size:.75rem; color:var(--adm-muted); text-align:center; margin-top:.5rem; min-height:1rem; }
.save-status.saved { color:var(--adm-success); }
.save-status.saving { color:var(--adm-warning); }
.status-toggle { display:flex; gap:.5rem; }
.status-btn { flex:1; padding:.5rem; border-radius:8px; border:1px solid var(--adm-border); background:transparent; color:var(--adm-muted); font-size:.8rem; cursor:pointer; text-align:center; transition:all .2s; }
.status-btn.active-draft { background:rgba(244,208,63,.1); border-color:rgba(244,208,63,.4); color:var(--adm-warning); font-weight:700; }
.status-btn.active-published { background:rgba(45,198,83,.1); border-color:rgba(45,198,83,.4); color:var(--adm-success); font-weight:700; }
</style>

<div class="adm-wrap">

    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-file-lines" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title" id="pageTitleH1"><?php echo $isEdit ? 'Edit Page' : 'New Page'; ?></h1>
            <p class="adm-page-sub">Create static pages like About, Terms, Privacy</p>
        </div>
        <div style="margin-left:auto;">
            <a href="all-pages.php" class="adm-topbar-btn">
                <i class="fa-regular fa-list"></i> All Pages
            </a>
        </div>
    </div>

    <div id="alertBox"></div>

    <form id="pageForm" enctype="multipart/form-data" onsubmit="return false;">
        <input type="hidden" name="page_id" id="pageId" value="<?php echo $isEdit ? $editId : ''; ?>">
        <input type="hidden" name="active" id="activeInput" value="<?php echo $v['active']; ?>">

        <div class="page-editor-wrap">

            <!-- Principal -->
            <div>
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-field" style="margin-bottom:.5rem;">
                        <input class="adm-input" id="title" name="title" type="text"
                               placeholder="Page title..."
                               value="<?php echo htmlspecialchars($v['title']); ?>"
                               maxlength="250"
                               style="font-size:1.3rem;font-weight:700;padding:.75rem 1rem;border:none;background:transparent;border-bottom:1px solid var(--adm-border);border-radius:0;">
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem 1rem 0;">
                        <span style="font-size:.75rem;color:var(--adm-muted);">URL:</span>
                        <input class="adm-input" id="slug" name="slug" type="text"
                               placeholder="url-slug"
                               value="<?php echo htmlspecialchars($v['slug']); ?>"
                               style="font-size:.78rem;padding:.3rem .7rem;flex:1;">
                    </div>
                    <div class="slug-preview" style="padding:.3rem 1rem .75rem;">
                        <i class="fa-regular fa-link" style="font-size:.7rem;"></i>
                        <?php echo $setting['website_url']; ?>/page/<span id="slug-display"><?php echo htmlspecialchars($v['slug'] ?: 'url-slug'); ?></span>/
                    </div>
                </div>

                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-file-lines"></i> Content *</div>
                    <textarea id="pageContent" name="content"><?php echo htmlspecialchars($v['content']); ?></textarea>
                </div>

                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title">
                        <i class="fa-regular fa-align-left"></i> Excerpt
                        <span class="char-counter" id="excerpt-counter">0 / 500</span>
                    </div>
                    <textarea class="adm-input" id="excerpt" name="excerpt"
                              placeholder="Short summary (optional)..."
                              maxlength="500" rows="2"><?php echo htmlspecialchars($v['excerpt']); ?></textarea>
                </div>

                <div class="adm-card">
                    <div class="adm-card-title"><i class="fa-regular fa-magnifying-glass"></i> SEO</div>
                    <div class="adm-field">
                        <label class="adm-label">Meta Title</label>
                        <input class="adm-input" id="meta_title" name="meta_title" type="text"
                               placeholder="Leave empty to use page title"
                               value="<?php echo htmlspecialchars($v['meta_title']); ?>" maxlength="255">
                    </div>
                    <div class="adm-field" style="margin-bottom:0;">
                        <label class="adm-label">Meta Description</label>
                        <textarea class="adm-input" id="meta_desc" name="meta_desc"
                                  placeholder="Description for search results" maxlength="255" rows="2"><?php echo htmlspecialchars($v['meta_desc']); ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Lateral -->
            <div>
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-rocket"></i> Publish</div>
                    <div class="adm-field">
                        <label class="adm-label">Status</label>
                        <div class="status-toggle">
                            <button type="button" class="status-btn <?php echo $v['active'] == 0 ? 'active-draft' : ''; ?>" id="btnDraft" onclick="setActive(0)">
                                <i class="fa-regular fa-floppy-disk"></i> Draft
                            </button>
                            <button type="button" class="status-btn <?php echo $v['active'] == 1 ? 'active-published' : ''; ?>" id="btnPublish" onclick="setActive(1)">
                                <i class="fa-regular fa-globe"></i> Published
                            </button>
                        </div>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">Access</label>
                        <select class="adm-input" name="level" id="level">
                            <option value="0" <?php echo $v['level'] == 0 ? 'selected' : ''; ?>>Visible to all</option>
                            <option value="1" <?php echo $v['level'] == 1 ? 'selected' : ''; ?>>Logged in users only</option>
                        </select>
                    </div>
                    <button class="adm-save" type="button" id="submitBtn" onclick="savePage()"
                            style="width:100%;justify-content:center;display:flex;margin-top:.5rem;">
                        <i class="fa-regular fa-floppy-disk"></i>
                        <span id="submitBtnText"><?php echo $isEdit ? 'Update Page' : 'Save Page'; ?></span>
                    </button>
                    <div class="save-status" id="saveStatus"></div>
                    <?php if ($isEdit): ?>
                    <a href="<?php echo $setting['website_url']; ?>/page/<?php echo htmlspecialchars($v['slug']); ?>/"
                       target="_blank" style="display:block;text-align:center;margin-top:.5rem;font-size:.78rem;color:var(--adm-accent);text-decoration:none;">
                        <i class="fa-regular fa-external-link"></i> View page
                    </a>
                    <?php endif; ?>
                </div>

                <div class="adm-card">
                    <div class="adm-card-title"><i class="fa-regular fa-image"></i> Cover Image <span style="font-weight:400;color:var(--adm-muted);font-size:.75rem;">(optional)</span></div>
                    <div class="cover-upload-zone <?php echo $v['cover_img'] ? 'has-image' : ''; ?>" id="coverZone">
                        <input type="file" name="cover_img" id="coverInput" accept=".jpg,.jpeg,.png,.webp">
                        <div class="cover-placeholder">
                            <i class="fa-regular fa-image" style="font-size:2rem;color:var(--adm-muted);display:block;margin-bottom:.5rem;"></i>
                            <div style="font-size:.8rem;color:var(--adm-muted);">Click or drag to upload</div>
                            <div style="font-size:.7rem;color:var(--adm-muted);margin-top:.25rem;">JPG, PNG, WebP · max 3MB</div>
                        </div>
                        <img id="coverPreview"
                             src="<?php echo $v['cover_img'] ? $setting['website_url'] . '/system/assets/uploads/pages/' . $v['cover_img'] : ''; ?>"
                             alt="Cover">
                    </div>
                    <?php if ($v['cover_img']): ?>
                    <button type="button" id="removeCover" style="margin-top:.5rem;font-size:.75rem;color:var(--adm-danger);background:none;border:none;cursor:pointer;padding:0;">
                        <i class="fa-regular fa-trash"></i> Remove image
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
tinymce.init({
    selector: '#pageContent',
    skin: 'oxide-dark',
    content_css: 'dark',
    height: 500,
    menubar: true,
    branding: false,
    plugins: ['advlist','autolink','lists','link','image','charmap','preview','searchreplace','visualblocks','code','fullscreen','insertdatetime','media','table','paste','wordcount','anchor','emoticons','hr','quickbars'],
    toolbar: ['undo redo | formatselect | bold italic underline | forecolor backcolor','alignleft aligncenter alignright | bullist numlist outdent indent | blockquote hr','link image media table | code fullscreen preview'],
    toolbar_mode: 'sliding',
    quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
    quickbars_insert_toolbar: false,
    automatic_uploads: true,
    images_upload_url: '<?php echo $setting['website_url']; ?>/admin/ajax-upload-post-img.php',
    images_upload_credentials: true,
    content_style: `body{font-family:-apple-system,sans-serif;font-size:15px;line-height:1.7;color:#f0f2ff;background:#0d0f1c;padding:1.5rem 2rem;} h1,h2,h3{color:#fff;} a{color:#d4ff00;} blockquote{border-left:3px solid #d4ff00;padding-left:1rem;color:#8b8fa8;} img{max-width:100%;border-radius:8px;}`,
    setup: function(editor) { editor.on('change keyup', function() { editor.save(); }); }
});

var titleInput = document.getElementById('title');
var slugInput  = document.getElementById('slug');
var slugManual = <?php echo $isEdit ? 'true' : 'false'; ?>;

function generateSlug(str) {
    return str.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'')
        .replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-').replace(/-+/g,'-').substring(0,100);
}

titleInput.addEventListener('input', function() {
    if (!slugManual) {
        var s = generateSlug(this.value);
        slugInput.value = s;
        updateSlugDisplay(s);
    }
});
slugInput.addEventListener('keydown', function() { slugManual = true; });
slugInput.addEventListener('input', function() {
    var clean = this.value.toLowerCase().replace(/[^a-z0-9\-\s]/g,'').replace(/\s+/g,'-');
    if (clean !== this.value) { var p = this.selectionStart; this.value = clean; this.setSelectionRange(p,p); }
    updateSlugDisplay(this.value);
});
slugInput.addEventListener('blur', function() {
    if (this.value.trim() === '') { slugManual = false; var s = generateSlug(titleInput.value); this.value = s; updateSlugDisplay(s); }
});
function updateSlugDisplay(s) {
    document.getElementById('slug-display').textContent = s || 'url-slug';
}

document.getElementById('excerpt').addEventListener('input', function() {
    var el = document.getElementById('excerpt-counter');
    el.textContent = this.value.length + ' / 500';
    el.className = 'char-counter' + (this.value.length > 500 ? ' over' : (this.value.length > 400 ? ' warn' : ''));
});

function setActive(val) {
    document.getElementById('activeInput').value = val;
    document.getElementById('btnDraft').className = 'status-btn' + (val == 0 ? ' active-draft' : '');
    document.getElementById('btnPublish').className = 'status-btn' + (val == 1 ? ' active-published' : '');
}

document.getElementById('coverInput').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { showAlert('error','Only JPG, PNG or WebP.'); this.value=''; return; }
    if (file.size > 3*1024*1024) { showAlert('error','Image too large. Max 3MB.'); this.value=''; return; }
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('coverPreview').src = e.target.result;
        document.getElementById('coverZone').classList.add('has-image');
    };
    reader.readAsDataURL(file);
});
document.getElementById('removeCover')?.addEventListener('click', function() {
    document.getElementById('coverInput').value = '';
    document.getElementById('coverPreview').src = '';
    document.getElementById('coverZone').classList.remove('has-image');
});

function savePage() {
    tinymce.triggerSave();
    var title = titleInput.value.trim();
    var slug = slugInput.value.trim();
    var content = document.getElementById('pageContent').value.trim();

    if (!title) { showAlert('error','Title is required.'); titleInput.focus(); return; }
    if (!slug) { showAlert('error','Slug is required.'); return; }
    if (!content) { showAlert('error','Content is required.'); return; }

    var btn = document.getElementById('submitBtn');
    var status = document.getElementById('saveStatus');
    btn.disabled = true;
    btn.querySelector('span').textContent = 'Saving...';
    status.className = 'save-status saving';
    status.textContent = 'Saving...';

    var formData = new FormData(document.getElementById('pageForm'));
    formData.set('content', content);

    fetch('<?php echo $setting['website_url']; ?>/admin/ajax-save-page.php', {
        method: 'POST', credentials: 'same-origin', body: formData
    })
    .then(function(r){ return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        if (data.success) {
            document.getElementById('pageId').value = data.id;
            btn.querySelector('span').textContent = 'Update Page';
            document.getElementById('pageTitleH1').textContent = 'Edit Page';
            if (window.history.pushState) window.history.pushState({}, '', 'new-page.php?id=' + data.id);
            status.className = 'save-status saved';
            status.innerHTML = '<i class="fa-solid fa-circle-check"></i> Saved ' + new Date().toLocaleTimeString();
            showAlert('success', data.message);
        } else {
            btn.querySelector('span').textContent = document.getElementById('pageId').value ? 'Update Page' : 'Save Page';
            status.textContent = '';
            showAlert('error', data.message || 'Failed to save.');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.querySelector('span').textContent = 'Save Page';
        status.textContent = '';
        showAlert('error', 'Connection error.');
    });
}

function showAlert(type, msg) {
    var box = document.getElementById('alertBox');
    var cls = type === 'success' ? 'adm-alert-success' : 'adm-alert-error';
    var icon = type === 'success' ? 'circle-check' : 'circle-xmark';
    box.innerHTML = '<div class="adm-alert ' + cls + '" style="margin-bottom:1rem;"><i class="fa-solid fa-' + icon + '"></i> ' + msg + '</div>';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (type === 'success') setTimeout(function(){ box.innerHTML = ''; }, 4000);
}
</script>

<?php require_once('includes/footer.php'); ?>