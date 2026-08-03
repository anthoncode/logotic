<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Upload Logo';
$pg = '5';

require_once('../system/config-user.php');
$uid = $crypt->decrypt($_SESSION['uid'], 'USER');
$category = $product->get_categories();

require_once('includes/header.php');
?>

<div class="user-panel-head">
    <h1>Upload a Logo</h1>
    <p>Share a logo with the community — it will be reviewed before publishing</p>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>
<script>Dropzone.autoDiscover = false;</script>

<style>
.ul-step { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-card); padding: 1.5rem; margin-bottom: 1.25rem; position: relative; }
.ul-step-num { position: absolute; top: -12px; left: 20px; width: 26px; height: 26px; background: var(--accent); color: #0d0f1c; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .82rem; font-weight: 800; }
.ul-step-title { font-size: .82rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-muted); margin: 0 0 1rem; display: flex; align-items: center; gap: .5rem; }
.ul-step-title i { color: var(--accent); }
.ul-step.disabled { opacity: .5; pointer-events: none; }

.ul-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .ul-grid-2 { grid-template-columns: 1fr; } }

.ul-label { display: block; font-size: .8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: .4rem; }
.ul-input, .ul-select { width: 100%; background: rgba(255,255,255,.04); border: 1px solid var(--border); border-radius: var(--radius-btn); color: var(--text-primary); font-size: .88rem; padding: .65rem .85rem; font-family: 'Poppins', sans-serif; }
.ul-input:focus, .ul-select:focus { outline: none; border-color: var(--accent); }

/* Mensaje arriba */
.ul-msg { border-radius: 10px; padding: .85rem 1rem; font-size: .85rem; margin-bottom: 1.25rem; display: none; gap: .6rem; align-items: flex-start; }
.ul-msg.show { display: flex; }
.ul-msg.success { background: rgba(45,198,83,.1); border: 1px solid rgba(45,198,83,.3); color: #4ade80; }
.ul-msg.error { background: rgba(255,77,77,.1); border: 1px solid rgba(255,77,77,.3); color: #ff8080; }
.ul-msg i { margin-top: .1rem; }

.ul-info { background: rgba(29,122,243,.08); border: 1px solid rgba(29,122,243,.25); color: #6fb1ff; border-radius: 10px; padding: .85rem 1rem; font-size: .82rem; margin-bottom: 1.25rem; display: flex; gap: .6rem; align-items: flex-start; }
.ul-info i { margin-top: .1rem; }

/* Dropzone */
#user-dropzone { background: rgba(212,255,0,.03) !important; border: 2px dashed rgba(212,255,0,.25) !important; border-radius: 12px !important; min-height: 150px; padding: 1.5rem; cursor: pointer; transition: border-color .2s; }
#user-dropzone:hover, #user-dropzone.dz-drag-hover { border-color: var(--accent) !important; background: rgba(212,255,0,.06) !important; }
#user-dropzone.dz-locked { opacity: .5; pointer-events: none; }
#user-dropzone .dz-message { color: var(--text-muted); margin: 1rem 0; text-align: center; }

.dz-lock-msg { display: none; margin-top: .75rem; font-size: .8rem; color: var(--text-muted); text-align: center; }
.dz-lock-msg.visible { display: block; }

/* Miniatura de carga (estilo add-product) */
#user-dropzone .dz-preview { display: flex !important; align-items: center; gap: .75rem; flex-wrap: wrap; min-height: 0 !important; margin: .5rem 0 0 !important; padding: .5rem .75rem; border: 1px solid var(--border); border-radius: 8px; background: rgba(255,255,255,.03); position: relative; text-align: left; }
#user-dropzone .dz-preview .dz-image { display: block !important; order: 1; width: 40px !important; height: 40px !important; min-width: 40px; border-radius: 6px !important; background: #fff !important; overflow: hidden; flex-shrink: 0; }
#user-dropzone .dz-preview .dz-image img { width: 100%; height: 100%; object-fit: contain; padding: 3px; }
#user-dropzone .dz-preview .dz-details { order: 2; position: static !important; opacity: 1 !important; padding: 0 !important; margin: 0 !important; min-width: 0 !important; max-width: none !important; text-align: left !important; line-height: 1.3 !important; display: flex; flex-direction: column; flex: 1; }
#user-dropzone .dz-preview .dz-filename span { font-size: .78rem; color: var(--text-primary); background: none !important; border: none !important; padding: 0 !important; }
#user-dropzone .dz-preview .dz-size { display: block !important; font-size: .68rem; color: var(--text-muted); margin: 0 !important; }
#user-dropzone .dz-preview .dz-size span { background: none !important; padding: 0 !important; }
#user-dropzone .dz-preview .dz-progress { order: 3; position: static !important; width: 120px !important; height: 4px !important; margin: 0 0 0 auto !important; background: rgba(255,255,255,.1) !important; border-radius: 99px; overflow: hidden; opacity: 1 !important; animation: none !important; transform: none !important; flex-shrink: 0; }
#user-dropzone .dz-preview .dz-upload { display: block; height: 100%; background: var(--accent) !important; transition: width .3s; position: static !important; }
#user-dropzone .dz-preview .dz-success-mark, #user-dropzone .dz-preview .dz-error-mark { display: none !important; }
#user-dropzone .dz-preview::after { order: 4; font-size: .9rem; font-weight: 700; flex-shrink: 0; width: 16px; text-align: center; }
#user-dropzone .dz-preview.dz-success::after { content: '✓'; color: #2dc653; }
#user-dropzone .dz-preview.dz-error::after { content: '✕'; color: #ff4d4d; }
#user-dropzone .dz-preview.dz-success { border-color: rgba(45,198,83,.3); }
#user-dropzone .dz-preview.dz-success .dz-progress { display: none !important; }
#user-dropzone .dz-preview.dz-error { border-color: rgba(255,77,77,.4); background: rgba(255,77,77,.05); }
#user-dropzone .dz-preview .dz-error-message { order: 5; display: none; position: static !important; opacity: 1 !important; background: none !important; color: #ff4d4d !important; font-size: .72rem; padding: 0 !important; margin-top: .3rem; flex-basis: 100%; width: auto !important; }
#user-dropzone .dz-preview .dz-error-message:after { display: none !important; }
#user-dropzone .dz-preview.dz-error .dz-error-message { display: block; }

/* Tarjetas del paso 3 — campos en fila */
.ul-uploaded { display: flex; flex-direction: column; gap: .85rem; margin-top: 1rem; }
.ul-uploaded-item { display: flex; gap: 1rem; background: rgba(255,255,255,.02); border: 1px solid var(--border); border-radius: 12px; padding: .85rem 1rem; align-items: center; flex-wrap: wrap; }
.ul-uploaded-img { width: 56px; height: 56px; background: #fff; border-radius: 8px; padding: 6px; object-fit: contain; flex-shrink: 0; }
.ul-uploaded-item .ul-name { flex: 1 1 180px; min-width: 0; }
.ul-uploaded-item .ul-tags { flex: 2 1 240px; min-width: 0; }
.ul-save-btn { background: var(--accent); color: #0d0f1c; border: none; border-radius: var(--radius-btn); padding: .6rem 1.1rem; font-size: .8rem; font-weight: 700; cursor: pointer; flex-shrink: 0; transition: var(--transition); }
.ul-save-btn:hover { transform: translateY(-1px); }
.ul-saved-badge { font-size: .72rem; color: #2dc653; display: none; align-items: center; gap: .3rem; flex-shrink: 0; }
</style>

<!-- Mensaje arriba (éxito / error) -->
<div class="ul-msg" id="ulMsg"></div>

<div class="ul-info">
    <i class="fa-solid fa-circle-info"></i>
    <div>Every logo you upload is <strong>reviewed by our team</strong> before going live. Track its status in <a href="<?php echo $setting['website_url']; ?>/user/my-logos.php" style="color:var(--accent);">My Logos</a>. Limit: 50 logos per day.</div>
</div>

<!-- PASO 1 -->
<div class="ul-step" id="step1">
    <div class="ul-step-num">1</div>
    <div class="ul-step-title"><i class="fa-regular fa-folder"></i> Choose category</div>
    <div class="ul-grid-2">
        <div>
            <label class="ul-label">Category *</label>
            <select class="ul-select" id="cat_id">
                <option value="">Select category...</option>
                <?php foreach ($category as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="ul-label">Subcategory *</label>
            <select class="ul-select" id="subcat" disabled>
                <option value="">Select subcategory...</option>
            </select>
        </div>
    </div>
</div>

<!-- PASO 2 -->
<div class="ul-step disabled" id="step2">
    <div class="ul-step-num">2</div>
    <div class="ul-step-title"><i class="fa-regular fa-cloud-arrow-up"></i> Upload SVG file</div>
    <form id="user-dropzone">
        <div class="dz-message">
            <i class="fa-regular fa-cloud-arrow-up" style="font-size:2.2rem;color:var(--accent);display:block;margin-bottom:.6rem;"></i>
            <div style="font-size:.92rem;font-weight:600;color:var(--text-primary);">Drop your SVG here or click to browse</div>
            <div style="font-size:.76rem;margin-top:.3rem;">Only .svg files · Max 5MB</div>
        </div>
    </form>
    <div class="dz-lock-msg visible" id="lockMsg"><i class="fa-regular fa-lock"></i> Select a category and subcategory first</div>
</div>

<!-- PASO 3 -->
<div class="ul-step disabled" id="step3">
    <div class="ul-step-num">3</div>
    <div class="ul-step-title"><i class="fa-regular fa-pen"></i> Name & tags</div>
    <div id="uploadedList" class="ul-uploaded">
        <p style="color:var(--text-muted);font-size:.85rem;margin:0;">Your uploaded logo will appear here to add a name and tags.</p>
    </div>
</div>

<script>
$(function() {
    var SITE = "<?php echo $setting['website_url']; ?>";

    function showMsg(type, text) {
        var m = $('#ulMsg');
        m.attr('class', 'ul-msg show ' + type);
        m.html('<i class="fa-solid ' + (type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation') + '"></i><div>' + text + '</div>');
        $('html,body').animate({ scrollTop: 0 }, 300);
    }

    // Paso 1
    $('#cat_id').on('change', function() {
        var cat = $(this).val();
        if (cat) {
            $.get(SITE + '/user/ajax-subcat.php', { cat_id: cat }, function(html) {
                $('#subcat').html('<option value="">Select subcategory...</option>' + html).prop('disabled', false);
                checkReady();
            });
        } else {
            $('#subcat').html('<option value="">Select subcategory...</option>').prop('disabled', true);
            checkReady();
        }
    });
    $('#subcat').on('change', checkReady);

    function checkReady() {
        var cat = $('#cat_id').val();
        var sub = $('#subcat').val();
        if (cat && sub) {
            $('#step2').removeClass('disabled');
            $('#user-dropzone').removeClass('dz-locked');
            $('#lockMsg').removeClass('visible');
        } else {
            $('#step2').addClass('disabled');
            $('#user-dropzone').addClass('dz-locked');
            $('#lockMsg').addClass('visible');
        }
    }

    // Paso 2: Dropzone (deja la miniatura visible con progreso)
    var dz = new Dropzone('#user-dropzone', {
        url: SITE + '/user/ajax-upload-user.php',
        paramName: 'file',
        maxFilesize: 5,
        maxFiles: 50,
        acceptedFiles: '.svg,image/svg+xml',
        dictInvalidFileType: 'Not an SVG file',
        addRemoveLinks: false,
        clickable: true,
        createImageThumbnails: true,
        params: function() {
            return { cat_id: $('#cat_id').val(), subcat: $('#subcat').val() };
        },
        init: function() {
            this.on('addedfile', function(file) {
                if (!$('#cat_id').val() || !$('#subcat').val()) {
                    this.removeFile(file);
                }
            });
            this.on('success', function(file, resp) {
                var data = (typeof resp === 'string') ? JSON.parse(resp) : resp;
                if (data.success) {
                    file.previewElement.classList.add('dz-success');
                    showMsg('success', 'Logo uploaded! It will be reviewed by our team before publishing.');
                    addUploadedCard(data);
                } else {
                    file.previewElement.classList.add('dz-error');
                    var em = file.previewElement.querySelector('.dz-error-message');
                    if (em) em.textContent = data.message || 'Upload failed';
                    showMsg('error', data.message || 'Upload failed');
                }
            });
            this.on('error', function(file, msg) {
                file.previewElement.classList.add('dz-error');
                var text = (typeof msg === 'string') ? msg : (msg.message || 'Upload error');
                var em = file.previewElement.querySelector('.dz-error-message');
                if (em) em.textContent = text;
                showMsg('error', text);
            });
        }
    });

    // Paso 3: tarjeta editable (campos en fila)
    function addUploadedCard(data) {
        $('#step3').removeClass('disabled');
        if ($('#uploadedList p').length) $('#uploadedList').empty();

        var safeName = $('<div>').text(data.name).html();
        var card = $(
            '<div class="ul-uploaded-item" data-id="' + data.id + '">' +
                '<img class="ul-uploaded-img" src="' + data.preview + '" alt="logo">' +
                '<input class="ul-input ul-name" value="' + safeName + '" maxlength="99" placeholder="Logo name">' +
                '<input class="ul-input ul-tags" placeholder="tags, separated, by, commas">' +
                '<button class="ul-save-btn">Save</button>' +
                '<span class="ul-saved-badge"><i class="fa-solid fa-circle-check"></i> Saved</span>' +
            '</div>'
        );
        $('#uploadedList').append(card);

        card.find('.ul-save-btn').on('click', function() {
            var id = card.data('id');
            var name = card.find('.ul-name').val().trim();
            var tags = card.find('.ul-tags').val().trim();
            if (!name) { card.find('.ul-name').focus(); return; }

            var btn = $(this);
            btn.text('Saving...').prop('disabled', true);

            $.post(SITE + '/user/ajax-update-user-logo.php', { id: id, name: name, tags: tags }, function(res) {
                var d = (typeof res === 'string') ? JSON.parse(res) : res;
                if (d.success) {
                    card.find('.ul-saved-badge').css('display', 'flex');
                    btn.text('Save').prop('disabled', false);
                } else {
                    showMsg('error', d.message || 'Could not save');
                    btn.text('Save').prop('disabled', false);
                }
            });
        });
    }
});
</script>

<?php require_once('includes/footer.php'); ?>