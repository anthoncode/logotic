<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once('../system/config-admin.php');

// Autor = admin logueado
$adminDetails = $auth->details($_SESSION['uid']);
$authorName   = $adminDetails['fname'] ?? 'Admin';

// ── Modo edición ──
$editId = (int)($_GET['id'] ?? 0);
$isEdit = $editId > 0;
$post   = null;

if ($isEdit) {
    $stmt = $DB_con->prepare("SELECT * FROM " . PFX . "posts WHERE id = :id");
    $stmt->execute([':id' => $editId]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$post) {
        header('Location: all-posts.php');
        exit;
    }
    $pageTitle = 'Edit Post';
} else {
    $pageTitle = 'New Post';
}

// Categorías activas
$postCats = $DB_con->query("SELECT * FROM " . PFX . "post_categories WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Valores por defecto o del post
$v = [
    'title'         => $post['title']         ?? '',
    'slug'          => $post['slug']          ?? '',
    'content'       => $post['content']       ?? '',
    'excerpt'       => $post['excerpt']       ?? '',
    'cover_img'     => $post['cover_img']      ?? '',
    'category_id'   => $post['category_id']    ?? '',
    'status'        => $post['status']         ?? 'draft',
    'meta_title'    => $post['meta_title']     ?? '',
    'meta_desc'     => $post['meta_desc']      ?? '',
    'meta_keywords' => $post['meta_keywords']  ?? '',
    'author'        => $post['author']         ?? $authorName,
];

require_once('includes/header1.php');
?>

<script src="https://cdn.jsdelivr.net/npm/tinymce@5/tinymce.min.js" referrerpolicy="origin"></script>

<style>
.post-editor-wrap { display:grid; grid-template-columns:1fr 300px; gap:1rem; align-items:start; }
.adm-input.is-error { border-color:var(--adm-danger) !important; }
.field-hint { font-size:.72rem; margin-top:.2rem; display:block; min-height:1rem; }
.field-hint.error { color:var(--adm-danger); }
.field-hint.ok { color:var(--adm-success); }

.slug-preview { font-size:.72rem; color:var(--adm-muted); margin-top:.3rem; display:flex; align-items:center; gap:.3rem; flex-wrap:wrap; }
.slug-preview span { color:var(--adm-accent); font-weight:600; }

.cover-upload-zone { border:2px dashed var(--adm-border); border-radius:10px; padding:1.5rem; text-align:center; cursor:pointer; transition:all .2s; position:relative; overflow:hidden; }
.cover-upload-zone:hover { border-color:var(--adm-accent); background:rgba(212,255,0,.03); }
.cover-upload-zone input[type="file"] { position:absolute; inset:0; opacity:0; cursor:pointer; width:100%; height:100%; }
.cover-upload-zone img { max-width:100%; border-radius:8px; display:none; margin-top:.75rem; }
.cover-upload-zone.has-image .cover-placeholder { display:none; }
.cover-upload-zone.has-image img { display:block; margin-top:0; }

.seo-preview { background:rgba(255,255,255,.03); border:1px solid var(--adm-border); border-radius:10px; padding:1rem; margin-top:.75rem; }
.seo-preview-title { font-size:.9rem; color:#4d9eff; font-weight:600; margin-bottom:.2rem; }
.seo-preview-url { font-size:.75rem; color:#2dc653; margin-bottom:.3rem; }
.seo-preview-desc { font-size:.78rem; color:var(--adm-muted); line-height:1.4; }

.status-toggle { display:flex; gap:.5rem; }
.status-btn { flex:1; padding:.5rem; border-radius:8px; border:1px solid var(--adm-border); background:transparent; color:var(--adm-muted); font-size:.8rem; cursor:pointer; text-align:center; transition:all .2s; }
.status-btn.active-draft { background:rgba(244,208,63,.1); border-color:rgba(244,208,63,.4); color:var(--adm-warning); font-weight:700; }
.status-btn.active-published { background:rgba(45,198,83,.1); border-color:rgba(45,198,83,.4); color:var(--adm-success); font-weight:700; }

.char-counter { font-size:.7rem; color:var(--adm-muted); float:right; }
.char-counter.warn { color:var(--adm-warning); }
.char-counter.over { color:var(--adm-danger); }

.tox .tox-toolbar, .tox .tox-toolbar__primary { background:#13152a !important; }
.tox-tinymce { border-color:var(--adm-border) !important; border-radius:10px !important; }

.save-status { font-size:.75rem; color:var(--adm-muted); text-align:center; margin-top:.5rem; min-height:1rem; }
.save-status.saved { color:var(--adm-success); }
.save-status.saving { color:var(--adm-warning); }
</style>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-pen-to-square" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title" id="pageTitle"><?php echo $isEdit ? 'Edit Post' : 'New Post'; ?></h1>
            <p class="adm-page-sub">Create and manage your blog content</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            <a href="post-categories.php" class="adm-topbar-btn">
                <i class="fa-regular fa-folder"></i> Categories
            </a>
            <a href="all-posts.php" class="adm-topbar-btn">
                <i class="fa-regular fa-list"></i> All Posts
            </a>
        </div>
    </div>

    <div id="alertBox"></div>

    <form id="postForm" enctype="multipart/form-data" onsubmit="return false;">
        <input type="hidden" name="post_id" id="postId" value="<?php echo $isEdit ? $editId : ''; ?>">
        <input type="hidden" name="status" id="statusInput" value="<?php echo htmlspecialchars($v['status']); ?>">

        <div class="post-editor-wrap">

            <!-- COLUMNA PRINCIPAL -->
            <div>
                <!-- Título + slug -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-field" style="margin-bottom:.5rem;">
                        <input class="adm-input" id="title" name="title" type="text"
                               placeholder="Post title..."
                               value="<?php echo htmlspecialchars($v['title']); ?>"
                               maxlength="255"
                               style="font-size:1.3rem;font-weight:700;padding:.75rem 1rem;border:none;background:transparent;border-bottom:1px solid var(--adm-border);border-radius:0;">
                        <span class="field-hint" id="title-hint"></span>
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem 1rem 0;">
                        <span style="font-size:.75rem;color:var(--adm-muted);">URL:</span>
                        <input class="adm-input" id="slug" name="slug" type="text"
                               placeholder="url-friendly-slug"
                               value="<?php echo htmlspecialchars($v['slug']); ?>"
                               style="font-size:.78rem;padding:.3rem .7rem;flex:1;">
                    </div>
                    <div class="slug-preview" style="padding:.3rem 1rem .75rem;">
                        <i class="fa-regular fa-link" style="font-size:.7rem;"></i>
                        <?php echo $setting['website_url']; ?>/blog/<span id="slug-display"><?php echo htmlspecialchars($v['slug'] ?: 'your-post-slug'); ?></span>/
                    </div>
                </div>

                <!-- Editor -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-file-lines"></i> Content *</div>
                    <textarea id="postContent" name="content"><?php echo htmlspecialchars($v['content']); ?></textarea>
                    <span class="field-hint" id="content-hint" style="margin-top:.5rem;"></span>
                </div>

                <!-- Excerpt -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title">
                        <i class="fa-regular fa-align-left"></i> Excerpt
                        <span class="char-counter" id="excerpt-counter">0 / 500</span>
                    </div>
                    <textarea class="adm-input" id="excerpt" name="excerpt"
                              placeholder="Short description shown in listings..."
                              maxlength="500" rows="3"><?php echo htmlspecialchars($v['excerpt']); ?></textarea>
                </div>

                <!-- SEO -->
                <div class="adm-card">
                    <div class="adm-card-title"><i class="fa-regular fa-magnifying-glass"></i> SEO Settings</div>
                    <div class="adm-field">
                        <label class="adm-label">Meta Title <span class="char-counter" id="metatitle-counter">0 / 255</span></label>
                        <input class="adm-input" id="meta_title" name="meta_title" type="text"
                               placeholder="Leave empty to use post title"
                               value="<?php echo htmlspecialchars($v['meta_title']); ?>" maxlength="255">
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">Meta Description <span class="char-counter" id="metadesc-counter">0 / 255</span></label>
                        <textarea class="adm-input" id="meta_desc" name="meta_desc"
                                  placeholder="Description for search results (max 155 recommended)"
                                  maxlength="255" rows="2"><?php echo htmlspecialchars($v['meta_desc']); ?></textarea>
                    </div>
                    <div class="adm-field" style="margin-bottom:0;">
                        <label class="adm-label">Meta Keywords</label>
                        <input class="adm-input" name="meta_keywords" type="text"
                               placeholder="logo, design, branding"
                               value="<?php echo htmlspecialchars($v['meta_keywords']); ?>" maxlength="255">
                    </div>
                    <div class="seo-preview">
                        <div style="font-size:.7rem;color:var(--adm-muted);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.08em;">Google Preview</div>
                        <div class="seo-preview-title" id="prev-title"><?php echo htmlspecialchars($v['meta_title'] ?: $v['title'] ?: 'Post title'); ?></div>
                        <div class="seo-preview-url" id="prev-url"><?php echo $setting['website_url']; ?>/blog/<?php echo htmlspecialchars($v['slug'] ?: 'your-post-slug'); ?>/</div>
                        <div class="seo-preview-desc" id="prev-desc"><?php echo htmlspecialchars($v['meta_desc'] ?: 'Meta description will appear here...'); ?></div>
                    </div>
                </div>
            </div>

            <!-- COLUMNA LATERAL -->
            <div>
                <!-- Publish -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-rocket"></i> Publish</div>
                    <div class="adm-field">
                        <label class="adm-label">Status</label>
                        <div class="status-toggle">
                            <button type="button" class="status-btn <?php echo $v['status'] === 'draft' ? 'active-draft' : ''; ?>" id="btnDraft" onclick="setStatus('draft')">
                                <i class="fa-regular fa-floppy-disk"></i> Draft
                            </button>
                            <button type="button" class="status-btn <?php echo $v['status'] === 'published' ? 'active-published' : ''; ?>" id="btnPublish" onclick="setStatus('published')">
                                <i class="fa-regular fa-globe"></i> Published
                            </button>
                        </div>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">Author</label>
                        <input class="adm-input" type="text" value="<?php echo htmlspecialchars($v['author']); ?>" disabled style="opacity:.7;">
                    </div>
                    <button class="adm-save" type="button" id="submitBtn" onclick="savePost()"
                            style="width:100%;justify-content:center;display:flex;margin-top:.5rem;">
                        <i class="fa-regular fa-floppy-disk"></i>
                        <span id="submitBtnText"><?php echo $isEdit ? 'Update Post' : 'Save Post'; ?></span>
                    </button>
                    <div class="save-status" id="saveStatus"></div>
                    <?php if ($isEdit): ?>
                    <a href="<?php echo $setting['website_url']; ?>/blog/<?php echo htmlspecialchars($v['slug']); ?>/"
                       target="_blank" style="display:block;text-align:center;margin-top:.5rem;font-size:.78rem;color:var(--adm-accent);text-decoration:none;">
                        <i class="fa-regular fa-external-link"></i> View post
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Category -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-folder"></i> Category</div>
                    <div class="adm-field" style="margin-bottom:0;">
                        <?php if (empty($postCats)): ?>
                            <p style="font-size:.78rem;color:var(--adm-muted);margin-bottom:.5rem;">No categories yet.</p>
                            <a href="post-categories.php" class="adm-btn" style="width:100%;justify-content:center;">
                                <i class="fa-regular fa-plus"></i> Create category
                            </a>
                        <?php else: ?>
                        <select class="adm-input" name="category_id" id="category_id">
                            <option value="">Uncategorized</option>
                            <?php foreach ($postCats as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $v['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cover -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-image"></i> Cover Image</div>
                    <div class="cover-upload-zone <?php echo $v['cover_img'] ? 'has-image' : ''; ?>" id="coverZone">
                        <input type="file" name="cover_img" id="coverInput" accept=".jpg,.jpeg,.png,.webp">
                        <div class="cover-placeholder">
                            <i class="fa-regular fa-image" style="font-size:2rem;color:var(--adm-muted);display:block;margin-bottom:.5rem;"></i>
                            <div style="font-size:.8rem;color:var(--adm-muted);">Click or drag to upload</div>
                            <div style="font-size:.7rem;color:var(--adm-muted);margin-top:.25rem;">JPG, PNG, WebP · max 3MB</div>
                        </div>
                        <img id="coverPreview"
                             src="<?php echo $v['cover_img'] ? $setting['website_url'] . '/system/assets/uploads/blog/covers/' . $v['cover_img'] : ''; ?>"
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
// ── TinyMCE ──
tinymce.init({
    selector: '#postContent',
    skin: 'oxide-dark',
    content_css: 'dark',
    height: 500,
    menubar: true,
    branding: false,
    plugins: ['advlist','autolink','lists','link','image','charmap','preview','searchreplace','visualblocks','code','fullscreen','insertdatetime','media','table','paste','wordcount','anchor','emoticons','hr','nonbreaking','quickbars'],
    toolbar: [
        'undo redo | formatselect | bold italic underline strikethrough | forecolor backcolor',
        'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blockquote hr',
        'link image media table | code fullscreen preview | emoticons charmap'
    ],
    toolbar_mode: 'sliding',
    quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
    quickbars_insert_toolbar: false,
    automatic_uploads: true,
    images_upload_url: '<?php echo $setting['website_url']; ?>/admin/ajax-upload-post-img.php',
    images_upload_credentials: true,
    content_style: `body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;font-size:15px;line-height:1.7;color:#f0f2ff;background:#0d0f1c;padding:1.5rem 2rem;max-width:800px;margin:0 auto;} h1,h2,h3,h4{color:#fff;margin-top:1.5em;} a{color:#d4ff00;} blockquote{border-left:3px solid #d4ff00;padding-left:1rem;color:#8b8fa8;} code{background:rgba(212,255,0,.1);padding:2px 6px;border-radius:4px;} img{max-width:100%;border-radius:8px;}`,
    setup: function(editor) {
        editor.on('change keyup', function() { editor.save(); });
    }
});

// ── Auto-slug ──
// ── Auto-slug ──
var titleInput = document.getElementById('title');
var slugInput  = document.getElementById('slug');
var slugManual = <?php echo $isEdit ? 'true' : 'false'; ?>;

function generateSlug(str) {
    return str.toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim().replace(/\s+/g, '-').replace(/-+/g, '-')
        .substring(0, 100);
}

titleInput.addEventListener('input', function() {
    if (!slugManual) {
        var s = generateSlug(this.value);
        slugInput.value = s;
        updateSlugDisplay(s);
    }
    updateSEOPreview();
});

// Solo marca manual si el usuario ESCRIBE directamente en el slug (no por código)
slugInput.addEventListener('keydown', function() {
    slugManual = true;
});

slugInput.addEventListener('input', function() {
    var clean = this.value.toLowerCase().replace(/[^a-z0-9\-\s]/g, '').replace(/\s+/g, '-');
    if (clean !== this.value) {
        var pos = this.selectionStart;
        this.value = clean;
        this.setSelectionRange(pos, pos);
    }
    updateSlugDisplay(this.value);
});

// Si el usuario vacía el slug manualmente, vuelve al modo automático
slugInput.addEventListener('blur', function() {
    if (this.value.trim() === '') {
        slugManual = false;
        var s = generateSlug(titleInput.value);
        this.value = s;
        updateSlugDisplay(s);
    }
});

function updateSlugDisplay(s) {
    document.getElementById('slug-display').textContent = s || 'your-post-slug';
    document.getElementById('prev-url').textContent = '<?php echo $setting['website_url']; ?>/blog/' + (s || 'your-post-slug') + '/';
}

// ── SEO preview ──
function updateSEOPreview() {
    var t = document.getElementById('meta_title').value || titleInput.value;
    var d = document.getElementById('meta_desc').value;
    document.getElementById('prev-title').textContent = t || 'Post title';
    document.getElementById('prev-desc').textContent  = d || 'Meta description will appear here...';
}
document.getElementById('meta_title').addEventListener('input', function() { updateSEOPreview(); updateCounter('metatitle-counter', this.value.length, 255, 60); });
document.getElementById('meta_desc').addEventListener('input', function() { updateSEOPreview(); updateCounter('metadesc-counter', this.value.length, 255, 155); });
document.getElementById('excerpt').addEventListener('input', function() { updateCounter('excerpt-counter', this.value.length, 500, 400); });

function updateCounter(id, len, max, warn) {
    var el = document.getElementById(id);
    el.textContent = len + ' / ' + max;
    el.className = 'char-counter' + (len > max ? ' over' : (len > warn ? ' warn' : ''));
}

// ── Status ──
function setStatus(status) {
    document.getElementById('statusInput').value = status;
    var btnD = document.getElementById('btnDraft');
    var btnP = document.getElementById('btnPublish');
    if (status === 'draft') {
        btnD.className = 'status-btn active-draft';
        btnP.className = 'status-btn';
    } else {
        btnD.className = 'status-btn';
        btnP.className = 'status-btn active-published';
    }
}

// ── Cover preview ──
document.getElementById('coverInput').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;
    var maxSize = 3 * 1024 * 1024;
    if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
        showAlert('error', 'Only JPG, PNG or WebP allowed.');
        this.value = ''; return;
    }
    if (file.size > maxSize) {
        showAlert('error', 'Image too large. Max 3MB.');
        this.value = ''; return;
    }
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('coverPreview');
        preview.src = e.target.result;
        document.getElementById('coverZone').classList.add('has-image');
    };
    reader.readAsDataURL(file);
});

document.getElementById('removeCover')?.addEventListener('click', function() {
    document.getElementById('coverInput').value = '';
    document.getElementById('coverPreview').src = '';
    document.getElementById('coverZone').classList.remove('has-image');
});

// ── Guardar por AJAX ──
function savePost() {
    tinymce.triggerSave();
    var title   = titleInput.value.trim();
    var slug    = slugInput.value.trim();
    var content = document.getElementById('postContent').value.trim();

    // Validación
    if (!title) { showAlert('error', 'Title is required.'); titleInput.focus(); return; }
    if (!slug)  { showAlert('error', 'Slug is required.'); return; }
    if (!content) { showAlert('error', 'Content is required.'); return; }

    var btn = document.getElementById('submitBtn');
    var status = document.getElementById('saveStatus');
    btn.disabled = true;
    btn.querySelector('span').textContent = 'Saving...';
    status.className = 'save-status saving';
    status.textContent = 'Saving your post...';

    var formData = new FormData(document.getElementById('postForm'));
    formData.set('content', content);

    fetch('<?php echo $setting['website_url']; ?>/admin/ajax-save-post.php', {
        method: 'POST',
        credentials: 'same-origin',
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        if (data.success) {
            // Actualizar post_id oculto
            document.getElementById('postId').value = data.id;
            // Cambiar botón a Update
            btn.querySelector('span').textContent = 'Update Post';
            document.getElementById('pageTitle').textContent = 'Edit Post';
            document.title = 'Edit Post';
            // Actualizar URL sin recargar
            if (window.history.pushState) {
                window.history.pushState({}, '', 'new-post.php?id=' + data.id);
            }
            status.className = 'save-status saved';
            status.innerHTML = '<i class="fa-solid fa-circle-check"></i> Saved ' + new Date().toLocaleTimeString();
            showAlert('success', data.message);
        } else {
            btn.querySelector('span').textContent = document.getElementById('postId').value ? 'Update Post' : 'Save Post';
            status.className = 'save-status';
            status.textContent = '';
            showAlert('error', data.message || 'Failed to save.');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.querySelector('span').textContent = 'Save Post';
        status.textContent = '';
        showAlert('error', 'Connection error. Try again.');
    });
}

function showAlert(type, msg) {
    var box = document.getElementById('alertBox');
    var cls = type === 'success' ? 'adm-alert-success' : 'adm-alert-error';
    var icon = type === 'success' ? 'circle-check' : 'circle-xmark';
    box.innerHTML = '<div class="adm-alert ' + cls + '" style="margin-bottom:1rem;"><i class="fa-solid fa-' + icon + '"></i> ' + msg + '</div>';
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (type === 'success') {
        setTimeout(function() { box.innerHTML = ''; }, 4000);
    }
}

updateSEOPreview();
</script>

<?php require_once('includes/footer.php'); ?>