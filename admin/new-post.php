<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'New Post';
require_once('../system/config-admin.php');

$errors  = [];
$success = '';

// ── POST CATEGORIES ──
$postCats = $DB_con->query("SELECT * FROM " . PFX . "post_categories WHERE active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// ── SERVER VALIDATION & SAVE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_post') {

    // Sanitización
    $title        = htmlspecialchars(strip_tags(trim($_POST['title']        ?? '')));
    $slug         = htmlspecialchars(strip_tags(trim($_POST['slug']         ?? '')));
    $content      = trim($_POST['content']     ?? '');
    $excerpt      = htmlspecialchars(strip_tags(trim($_POST['excerpt']      ?? '')));
    $cat_id       = (int)($_POST['category_id'] ?? 0);
    $status       = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $meta_title   = htmlspecialchars(strip_tags(trim($_POST['meta_title']   ?? '')));
    $meta_desc    = htmlspecialchars(strip_tags(trim($_POST['meta_desc']    ?? '')));
    $meta_keywords = htmlspecialchars(strip_tags(trim($_POST['meta_keywords'] ?? '')));
    $author       = htmlspecialchars(strip_tags(trim($_POST['author']       ?? 'Admin')));

    // Validaciones
    if (empty($title))   $errors[] = 'Title is required.';
    elseif (mb_strlen($title) > 255) $errors[] = 'Title max 255 characters.';

    if (empty($slug))    $errors[] = 'URL slug is required.';
    elseif (!preg_match('/^[a-z0-9\-]+$/', $slug)) $errors[] = 'Slug can only contain lowercase letters, numbers and hyphens.';
    else {
        // Unicidad del slug
        $chk = $DB_con->prepare("SELECT id FROM " . PFX . "posts WHERE slug = :slug");
        $chk->execute([':slug' => $slug]);
        if ($chk->fetchColumn()) $errors[] = 'This URL slug is already in use. Choose a different one.';
    }

    if (empty($content)) $errors[] = 'Content is required.';
    if (mb_strlen($excerpt) > 500) $errors[] = 'Excerpt max 500 characters.';
    if (mb_strlen($meta_title) > 255) $errors[] = 'Meta title max 255 characters.';
    if (mb_strlen($meta_desc)  > 255) $errors[] = 'Meta description max 255 characters.';

    // Cover image
    $cover_img = '';
    if (!empty($_FILES['cover_img']['name'])) {
        $file      = $_FILES['cover_img'];
        $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts  = ['jpg','jpeg','png','webp'];
        $allowedMimes = ['image/jpeg','image/png','image/webp'];
        $maxSize      = 3 * 1024 * 1024; // 3MB

        if (!in_array($ext, $allowedExts)) {
            $errors[] = 'Cover image must be JPG, PNG or WebP.';
        } elseif (!in_array($file['type'], $allowedMimes)) {
            $errors[] = 'Invalid image format.';
        } elseif ($file['size'] > $maxSize) {
            $errors[] = 'Cover image max size is 3MB.';
        } else {
            $cover_img = 'post-cover-' . time() . '-' . uniqid() . '.' . $ext;
            $dest = '../system/assets/uploads/blog/covers/' . $cover_img;
            if (!move_uploaded_file($file['tmp_name'], $dest)) {
                $errors[] = 'Failed to upload cover image. Check directory permissions.';
                $cover_img = '';
            }
        }
    }

    if (empty($errors)) {
        $stmt = $DB_con->prepare("
            INSERT INTO " . PFX . "posts
            (title, slug, content, excerpt, cover_img, author, category_id, status, meta_title, meta_desc, meta_keywords)
            VALUES
            (:title, :slug, :content, :excerpt, :cover_img, :author, :category_id, :status, :meta_title, :meta_desc, :meta_keywords)
        ");
        $stmt->execute([
            ':title'        => $title,
            ':slug'         => $slug,
            ':content'      => $content,
            ':excerpt'      => $excerpt,
            ':cover_img'    => $cover_img,
            ':author'       => $author,
            ':category_id'  => $cat_id ?: null,
            ':status'       => $status,
            ':meta_title'   => $meta_title ?: $title,
            ':meta_desc'    => $meta_desc,
            ':meta_keywords' => $meta_keywords,
        ]);
        $newId = $DB_con->lastInsertId();
        header('Location: all-posts.php?msg=Post+created+successfully');
        exit;
    }
}

require_once('includes/header1.php');
?>

<!-- TinyMCE 5 Dark -->
<script src="https://cdn.jsdelivr.net/npm/tinymce@5/tinymce.min.js" referrerpolicy="origin"></script>

<style>
/* ── Post editor layout ── */
.post-editor-wrap {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 1rem;
    align-items: start;
}

.adm-input.is-error { border-color: var(--adm-danger) !important; }
.adm-input.is-ok    { border-color: var(--adm-success) !important; }

.field-hint { font-size:.72rem; margin-top:.2rem; display:block; min-height:1rem; }
.field-hint.error { color: var(--adm-danger); }
.field-hint.ok    { color: var(--adm-success); }

/* Slug preview */
.slug-preview {
    font-size: .72rem;
    color: var(--adm-muted);
    margin-top: .3rem;
    display: flex;
    align-items: center;
    gap: .3rem;
    flex-wrap: wrap;
}
.slug-preview span { color: var(--adm-accent); font-weight: 600; }

/* Cover upload */
.cover-upload-zone {
    border: 2px dashed var(--adm-border);
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all .2s;
    position: relative;
    overflow: hidden;
}
.cover-upload-zone:hover { border-color: var(--adm-accent); background: rgba(212,255,0,.03); }
.cover-upload-zone input[type="file"] { position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%; }
.cover-upload-zone img { max-width:100%;border-radius:8px;display:none;margin-top:.75rem; }

/* SEO preview */
.seo-preview {
    background: rgba(255,255,255,.03);
    border: 1px solid var(--adm-border);
    border-radius: 10px;
    padding: 1rem;
    margin-top: .75rem;
}
.seo-preview-title { font-size:.9rem; color:#4d9eff; font-weight:600; margin-bottom:.2rem; }
.seo-preview-url   { font-size:.75rem; color:#2dc653; margin-bottom:.3rem; }
.seo-preview-desc  { font-size:.78rem; color:var(--adm-muted); line-height:1.4; }

/* Status toggle */
.status-toggle {
    display: flex;
    gap: .5rem;
}
.status-btn {
    flex: 1;
    padding: .5rem;
    border-radius: 8px;
    border: 1px solid var(--adm-border);
    background: transparent;
    color: var(--adm-muted);
    font-size: .8rem;
    cursor: pointer;
    text-align: center;
    transition: all .2s;
}
.status-btn.active-draft {
    background: rgba(244,208,63,.1);
    border-color: rgba(244,208,63,.4);
    color: var(--adm-warning);
    font-weight: 700;
}
.status-btn.active-published {
    background: rgba(45,198,83,.1);
    border-color: rgba(45,198,83,.4);
    color: var(--adm-success);
    font-weight: 700;
}

/* Char counter */
.char-counter { font-size:.7rem; color:var(--adm-muted); float:right; }
.char-counter.warn { color: var(--adm-warning); }
.char-counter.over { color: var(--adm-danger); }

/* TinyMCE dark override */
.tox .tox-toolbar, .tox .tox-toolbar__primary { background: #13152a !important; }
.tox-tinymce { border-color: var(--adm-border) !important; border-radius: 10px !important; }
</style>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-pen-to-square" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">New Post</h1>
            <p class="adm-page-sub">Create a new article or blog post</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            <a href="all-posts.php" class="adm-topbar-btn">
                <i class="fa-regular fa-list"></i> All Posts
            </a>
        </div>
    </div>

    <!-- Errores -->
    <?php if (!empty($errors)): ?>
        <div class="adm-alert adm-alert-error" style="margin-bottom:1rem;">
            <i class="fa-solid fa-circle-xmark"></i>
            <ul style="margin:.3rem 0 0 1rem;padding:0;">
                <?php foreach ($errors as $e): ?>
                    <li><?php echo $e; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="new-post.php" method="POST" enctype="multipart/form-data" id="postForm" novalidate>
        <input type="hidden" name="action" value="save_post">
        <input type="hidden" name="status" id="statusInput" value="draft">

        <div class="post-editor-wrap">

            <!-- ── COLUMNA PRINCIPAL ── -->
            <div>

                <!-- Título -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-field" style="margin-bottom:.5rem;">
                        <input class="adm-input" id="title" name="title" type="text"
                               placeholder="Post title..."
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                               maxlength="255" required
                               style="font-size:1.3rem;font-weight:700;padding:.75rem 1rem;border:none;background:transparent;border-bottom:1px solid var(--adm-border);border-radius:0;">
                        <span class="field-hint" id="title-hint"></span>
                    </div>
                    <!-- Slug -->
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.5rem 1rem 0;">
                        <span style="font-size:.75rem;color:var(--adm-muted);">URL:</span>
                        <input class="adm-input" id="slug" name="slug" type="text"
                               placeholder="url-friendly-slug"
                               value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>"
                               pattern="[a-z0-9\-]+"
                               style="font-size:.78rem;padding:.3rem .7rem;flex:1;">
                        <span class="field-hint" id="slug-hint" style="margin:0;"></span>
                    </div>
                    <div class="slug-preview" style="padding:.3rem 1rem .75rem;">
                        <i class="fa-regular fa-link" style="font-size:.7rem;"></i>
                        <?php echo $setting['website_url']; ?>/blog/<span id="slug-display">your-post-slug</span>/
                    </div>
                </div>

                <!-- Editor TinyMCE -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title">
                        <i class="fa-regular fa-file-lines"></i> Content *
                    </div>
                    <textarea id="postContent" name="content"><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                    <span class="field-hint" id="content-hint" style="margin-top:.5rem;"></span>
                </div>

                <!-- Excerpt -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title">
                        <i class="fa-regular fa-align-left"></i> Excerpt
                        <span class="char-counter" id="excerpt-counter">0 / 500</span>
                    </div>
                    <textarea class="adm-input" id="excerpt" name="excerpt"
                              placeholder="Short description shown in post listings (optional)..."
                              maxlength="500"
                              rows="3"><?php echo htmlspecialchars($_POST['excerpt'] ?? ''); ?></textarea>
                    <span class="field-hint" id="excerpt-hint"></span>
                </div>

                <!-- SEO -->
                <div class="adm-card">
                    <div class="adm-card-title">
                        <i class="fa-regular fa-magnifying-glass"></i> SEO Settings
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">
                            Meta Title
                            <span class="char-counter" id="metatitle-counter">0 / 255</span>
                        </label>
                        <input class="adm-input" id="meta_title" name="meta_title" type="text"
                               placeholder="Leave empty to use post title"
                               value="<?php echo htmlspecialchars($_POST['meta_title'] ?? ''); ?>"
                               maxlength="255">
                        <span class="field-hint" id="metatitle-hint"></span>
                    </div>
                    <div class="adm-field">
                        <label class="adm-label">
                            Meta Description
                            <span class="char-counter" id="metadesc-counter">0 / 255</span>
                        </label>
                        <textarea class="adm-input" id="meta_desc" name="meta_desc"
                                  placeholder="Description shown in Google search results (max 155 chars recommended)"
                                  maxlength="255" rows="2"><?php echo htmlspecialchars($_POST['meta_desc'] ?? ''); ?></textarea>
                        <span class="field-hint" id="metadesc-hint"></span>
                    </div>
                    <div class="adm-field" style="margin-bottom:0;">
                        <label class="adm-label">Meta Keywords</label>
                        <input class="adm-input" name="meta_keywords" type="text"
                               placeholder="logo, design, branding (comma separated)"
                               value="<?php echo htmlspecialchars($_POST['meta_keywords'] ?? ''); ?>"
                               maxlength="255">
                    </div>

                    <!-- SEO Preview -->
                    <div class="seo-preview">
                        <div style="font-size:.7rem;color:var(--adm-muted);margin-bottom:.5rem;text-transform:uppercase;letter-spacing:.08em;">Google Preview</div>
                        <div class="seo-preview-title" id="prev-title">Post title will appear here</div>
                        <div class="seo-preview-url" id="prev-url"><?php echo $setting['website_url']; ?>/blog/your-post-slug/</div>
                        <div class="seo-preview-desc" id="prev-desc">Meta description will appear here...</div>
                    </div>
                </div>

            </div>

            <!-- ── COLUMNA LATERAL ── -->
            <div>

                <!-- Publicar -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-rocket"></i> Publish</div>

                    <div class="adm-field">
                        <label class="adm-label">Status</label>
                        <div class="status-toggle">
                            <button type="button" class="status-btn active-draft" id="btnDraft"
                                    onclick="setStatus('draft')">
                                <i class="fa-regular fa-floppy-disk"></i> Draft
                            </button>
                            <button type="button" class="status-btn" id="btnPublish"
                                    onclick="setStatus('published')">
                                <i class="fa-regular fa-globe"></i> Publish
                            </button>
                        </div>
                    </div>

                    <div class="adm-field">
                        <label class="adm-label">Author</label>
                        <input class="adm-input" name="author" type="text"
                               value="<?php echo htmlspecialchars($details['fname'] ?? 'Admin'); ?>"
                               maxlength="100">
                    </div>

                    <button class="adm-save" type="submit" id="submitBtn"
                            style="width:100%;justify-content:center;display:flex;margin-top:.5rem;">
                        <i class="fa-regular fa-floppy-disk"></i> Save as Draft
                    </button>
                </div>

                <!-- Categoría -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-folder"></i> Category</div>
                    <div class="adm-field" style="margin-bottom:0;">
                        <select class="adm-input" name="category_id">
                            <option value="">Uncategorized</option>
                            <?php foreach ($postCats as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Cover Image -->
                <div class="adm-card" style="margin-bottom:1rem;">
                    <div class="adm-card-title"><i class="fa-regular fa-image"></i> Cover Image</div>
                    <div class="cover-upload-zone" id="coverZone">
                        <input type="file" name="cover_img" id="coverInput"
                               accept=".jpg,.jpeg,.png,.webp">
                        <i class="fa-regular fa-image" style="font-size:2rem;color:var(--adm-muted);display:block;margin-bottom:.5rem;"></i>
                        <div style="font-size:.8rem;color:var(--adm-muted);">Click or drag to upload</div>
                        <div style="font-size:.7rem;color:var(--adm-muted);margin-top:.25rem;">JPG, PNG, WebP · max 3MB</div>
                        <img id="coverPreview" src="" alt="Cover preview">
                    </div>
                    <span class="field-hint" id="cover-hint" style="margin-top:.3rem;"></span>
                    <button type="button" id="removeCover"
                            style="display:none;margin-top:.5rem;font-size:.75rem;color:var(--adm-danger);background:none;border:none;cursor:pointer;padding:0;">
                        <i class="fa-regular fa-trash"></i> Remove image
                    </button>
                </div>

                <!-- Post info -->
                <div class="adm-card">
                    <div class="adm-card-title"><i class="fa-regular fa-circle-info"></i> Info</div>
                    <div style="font-size:.75rem;color:var(--adm-muted);display:flex;flex-direction:column;gap:.4rem;">
                        <div><i class="fa-regular fa-calendar"></i> Created: <strong style="color:var(--adm-text);"><?php echo date('d M Y'); ?></strong></div>
                        <div><i class="fa-regular fa-clock"></i> Time: <strong style="color:var(--adm-text);"><?php echo date('H:i'); ?></strong></div>
                        <div><i class="fa-regular fa-user"></i> Author: <strong style="color:var(--adm-text);"><?php echo htmlspecialchars($details['fname'] ?? 'Admin'); ?></strong></div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
// ── TinyMCE 5 Dark ──
tinymce.init({
    selector: '#postContent',
    skin: 'oxide-dark',
    content_css: 'dark',
    height: 500,
    menubar: true,
    branding: false,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'paste', 'wordcount', 'anchor',
        'emoticons', 'hr', 'nonbreaking', 'pagebreak', 'quickbars'
    ],
    toolbar: [
        'undo redo | formatselect | bold italic underline strikethrough | forecolor backcolor',
        'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | blockquote hr',
        'link image media table | code fullscreen preview | emoticons charmap'
    ],
    toolbar_mode: 'sliding',
    quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
    quickbars_insert_toolbar: false,
    image_advtab: true,
    automatic_uploads: true,
    images_upload_url: '<?php echo $setting['website_url']; ?>/admin/ajax-upload-post-img.php',
    images_upload_credentials: true,
    file_picker_types: 'image',
    content_style: `
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-size: 15px;
            line-height: 1.7;
            color: #f0f2ff;
            background: #0d0f1c;
            padding: 1.5rem 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        h1,h2,h3,h4 { color: #ffffff; margin-top: 1.5em; }
        a { color: #d4ff00; }
        blockquote { border-left: 3px solid #d4ff00; padding-left: 1rem; color: #8b8fa8; margin: 1rem 0; }
        code { background: rgba(212,255,0,.1); padding: 2px 6px; border-radius: 4px; font-size: .9em; }
        img { max-width: 100%; border-radius: 8px; }
    `,
    setup: function(editor) {
        editor.on('change', function() {
            editor.save();
            validateContent();
        });
    }
});

// ── Auto-generar slug desde título ──
var titleInput = document.getElementById('title');
var slugInput  = document.getElementById('slug');
var slugManual = false;

function generateSlug(str) {
    return str
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // quitar acentos
        .replace(/[^a-z0-9\s\-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .substring(0, 100);
}

titleInput.addEventListener('input', function() {
    if (!slugManual) {
        var s = generateSlug(this.value);
        slugInput.value = s;
        updateSlugDisplay(s);
    }
    updateSEOPreview();
    validateTitle();
});

slugInput.addEventListener('input', function() {
    slugManual = true;
    var clean = this.value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
    this.value = clean;
    updateSlugDisplay(clean);
    validateSlug();
});

function updateSlugDisplay(s) {
    document.getElementById('slug-display').textContent = s || 'your-post-slug';
    document.getElementById('prev-url').textContent =
        '<?php echo $setting['website_url']; ?>/blog/' + (s || 'your-post-slug') + '/';
}

// ── SEO Preview ──
function updateSEOPreview() {
    var t = document.getElementById('meta_title').value || titleInput.value;
    var d = document.getElementById('meta_desc').value;
    document.getElementById('prev-title').textContent = t || 'Post title will appear here';
    document.getElementById('prev-desc').textContent  = d || 'Meta description will appear here...';
}

document.getElementById('meta_title').addEventListener('input', function() {
    updateSEOPreview();
    updateCounter('metatitle-counter', this.value.length, 255, 60);
});
document.getElementById('meta_desc').addEventListener('input', function() {
    updateSEOPreview();
    updateCounter('metadesc-counter', this.value.length, 255, 155);
});
document.getElementById('excerpt').addEventListener('input', function() {
    updateCounter('excerpt-counter', this.value.length, 500, 400);
});

function updateCounter(id, len, max, warn) {
    var el = document.getElementById(id);
    el.textContent = len + ' / ' + max;
    el.className = 'char-counter' + (len > max ? ' over' : (len > warn ? ' warn' : ''));
}

// ── Status toggle ──
function setStatus(status) {
    document.getElementById('statusInput').value = status;
    var btn = document.getElementById('submitBtn');
    var btnD = document.getElementById('btnDraft');
    var btnP = document.getElementById('btnPublish');

    if (status === 'draft') {
        btnD.className = 'status-btn active-draft';
        btnP.className = 'status-btn';
        btn.innerHTML  = '<i class="fa-regular fa-floppy-disk"></i> Save as Draft';
    } else {
        btnD.className = 'status-btn';
        btnP.className = 'status-btn active-published';
        btn.innerHTML  = '<i class="fa-regular fa-globe"></i> Publish Now';
    }
}

// ── Cover image preview ──
document.getElementById('coverInput').addEventListener('change', function() {
    var file = this.files[0];
    var hint = document.getElementById('cover-hint');
    if (!file) return;

    // Validación client-side
    var maxSize   = 3 * 1024 * 1024;
    var allowedTypes = ['image/jpeg','image/png','image/webp'];
    if (!allowedTypes.includes(file.type)) {
        hint.className   = 'field-hint error';
        hint.textContent = 'Only JPG, PNG or WebP allowed.';
        this.value = '';
        return;
    }
    if (file.size > maxSize) {
        hint.className   = 'field-hint error';
        hint.textContent = 'Image too large. Max 3MB.';
        this.value = '';
        return;
    }

    hint.textContent = '';
    var reader = new FileReader();
    reader.onload = function(e) {
        var preview = document.getElementById('coverPreview');
        preview.src   = e.target.result;
        preview.style.display = 'block';
        document.getElementById('removeCover').style.display = 'block';
        document.getElementById('coverZone').querySelector('i').style.display = 'none';
        document.getElementById('coverZone').querySelectorAll('div').forEach(function(d) { d.style.display = 'none'; });
    };
    reader.readAsDataURL(file);
});

document.getElementById('removeCover').addEventListener('click', function() {
    document.getElementById('coverInput').value = '';
    var preview = document.getElementById('coverPreview');
    preview.src   = '';
    preview.style.display = 'none';
    this.style.display = 'none';
    document.getElementById('coverZone').querySelector('i').style.display = 'block';
    document.getElementById('coverZone').querySelectorAll('div').forEach(function(d) { d.style.display = 'block'; });
    document.getElementById('cover-hint').textContent = '';
});

// ── Client validations ──
function validateTitle() {
    var v    = titleInput.value.trim();
    var hint = document.getElementById('title-hint');
    if (!v) return setFieldState(titleInput, hint, 'error', 'Title is required.');
    if (v.length > 255) return setFieldState(titleInput, hint, 'error', 'Max 255 characters.');
    setFieldState(titleInput, hint, 'ok', '');
}

function validateSlug() {
    var v    = slugInput.value.trim();
    var hint = document.getElementById('slug-hint');
    if (!v) return setFieldState(slugInput, hint, 'error', 'Required.');
    if (!/^[a-z0-9\-]+$/.test(v)) return setFieldState(slugInput, hint, 'error', 'Only lowercase, numbers, hyphens.');
    setFieldState(slugInput, hint, 'ok', '');
}

function validateContent() {
    var content = tinymce.get('postContent').getContent();
    var hint    = document.getElementById('content-hint');
    if (!content.trim()) {
        hint.className   = 'field-hint error';
        hint.textContent = 'Content is required.';
    } else {
        hint.className   = 'field-hint ok';
        hint.textContent = '';
    }
}

document.getElementById('postForm').addEventListener('submit', function(e) {
    tinymce.triggerSave();
    var valid = true;

    if (!titleInput.value.trim()) { validateTitle(); valid = false; }
    if (!slugInput.value.trim())  { validateSlug();  valid = false; }

    var content = tinymce.get('postContent').getContent();
    if (!content.trim()) { validateContent(); valid = false; }

    if (!valid) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});

titleInput.addEventListener('blur', validateTitle);
slugInput.addEventListener('blur',  validateSlug);

function setFieldState(input, hint, state, msg) {
    input.classList.remove('is-error','is-ok');
    hint.classList.remove('error','ok');
    if (state === 'error') {
        input.classList.add('is-error');
        hint.classList.add('error');
    } else {
        input.classList.add('is-ok');
        hint.classList.add('ok');
    }
    hint.textContent = msg;
}

// Init counters y preview
updateSEOPreview();
</script>

<?php require_once('includes/footer.php'); ?>