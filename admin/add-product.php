<?php
$pageTitle = "Add Logo";
require_once '../system/config-admin.php';
$category = $product->get_categories();
require_once 'includes/header1.php';
?>

<script src="//cdnjs.cloudflare.com/ajax/libs/tinymce/4.6.5/tinymce.min.js"></script>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-regular fa-plus" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">Add Logo</h1>
            <p class="adm-page-sub">Upload SVG files in bulk — up to 200 at once</p>
        </div>
        <div style="margin-left:auto;display:flex;gap:.5rem;">
            <a href="<?php echo $setting['website_url']; ?>/admin/all-logos.php" class="adm-topbar-btn">
                <i class="fa-regular fa-list"></i> All Logos
            </a>
        </div>
    </div>

    <!-- Category selectors -->
    <div class="adm-card" style="margin-bottom:1rem;">
        <div class="adm-card-title">
            <i class="fa-regular fa-folder"></i> Classification
        </div>
        <div class="adm-grid-2">
            <div class="adm-field">
                <label class="adm-label">Category *</label>
                <select class="adm-input" name="cat_id" id="cat_id" required>
                    <option value="">Select Category...</option>
                    <?php foreach ($category as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="adm-field">
                <label class="adm-label">Subcategory</label>
                <select class="adm-input" name="subcat" id="subcat">
                    <option value="">Select Subcategory...</option>
                </select>
            </div>
        </div>
        <p style="font-size:.75rem;color:var(--adm-muted);margin:0;">
            <i class="fa-regular fa-circle-info"></i> Select the category first — subcategories load automatically.
            The category applies to all logos uploaded in this session.
        </p>
    </div>

    <!-- Dropzone -->
    <div class="adm-card">
        <div class="adm-card-title">
            <i class="fa-regular fa-cloud-arrow-up"></i> Upload SVG Files
            <span style="margin-left:auto;font-size:.72rem;font-weight:400;color:var(--adm-muted);">
                Max 200 files · SVG only · 1GB per file
            </span>
        </div>

        <form id="my-awesome-dropzone" class="dropzone" style="
            background: rgba(212,255,0,.03);
            border: 2px dashed rgba(212,255,0,.25);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            transition: border-color .2s;
            min-height: 160px;
            cursor: pointer;
        ">
            <div class="dz-message" style="color:var(--adm-muted);">
                <i class="fa-regular fa-cloud-arrow-up" style="font-size:2.5rem;color:var(--adm-accent);display:block;margin-bottom:.75rem;"></i>
                <div style="font-size:.95rem;font-weight:600;color:var(--adm-text);">Drop SVG files here or click to browse</div>
                <div style="font-size:.78rem;margin-top:.35rem;">Only .svg files accepted — up to 200 files at once</div>
            </div>
        </form>
        <div class="dz-lock-msg visible">
    <i class="fa-regular fa-lock"></i> Please select a category and subcategory first
</div>
    </div>




    <!-- Logo list (populated dynamically) -->
    <div class="adm-card" id="logo-list-wrap" style="display:none;">
        <div class="adm-card-title">
            <i class="fa-regular fa-list"></i> Uploaded Logos
            <span id="logo-count" style="margin-left:.5rem;font-size:.72rem;font-weight:400;color:var(--adm-muted);"></span>
        </div>

        <div style="
            display:grid;
            grid-template-columns: 60px 1fr 1fr auto;
            gap:.5rem;
            padding:.5rem .75rem;
            border-bottom:1px solid var(--adm-border);
            font-size:.7rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.08em;
            color:var(--adm-muted);
        ">
            <div></div>
            <div>Name</div>
            <div>Tags</div>
            <div>Save</div>
        </div>

        <ul class="list-group" id="logo-list" style="list-style:none;padding:0;margin:0;"></ul>
    </div>

</div>

<style>

  /* ── Dropzone previews ── */
#my-awesome-dropzone .dz-preview {
    display: flex !important;
    align-items: center;
    gap: .75rem;
    flex-wrap: wrap;
    min-height: 0 !important;
    margin: .5rem 0 0 !important;
    padding: .5rem .75rem;
    border: 1px solid var(--adm-border);
    border-radius: 8px;
    background: rgba(255,255,255,.03);
    position: relative;
    text-align: left;
}

/* ── Dropzone bloqueado ── */
#my-awesome-dropzone.dz-locked {
    opacity: .5;
    cursor: not-allowed !important;
    pointer-events: none;
}

.dz-lock-msg {
    text-align: center;
    font-size: .78rem;
    color: var(--adm-warning);
    margin-top: .75rem;
    display: none;
}
.dz-lock-msg.visible { display: block; }

/* ── Dropzone hover ── */
#my-awesome-dropzone:hover,
#my-awesome-dropzone.dz-drag-hover {
    border-color: var(--adm-accent) !important;
    background: rgba(212,255,0,.06) !important;
}

/* Miniatura */
#my-awesome-dropzone .dz-preview .dz-image {
    display: block !important;
    order: 1;
    width: 40px !important;
    height: 40px !important;
    min-width: 40px;
    border-radius: 6px !important;
    background: #fff !important;
    overflow: hidden;
    flex-shrink: 0;
}
#my-awesome-dropzone .dz-preview .dz-image img {
    width: 100%; height: 100%;
    object-fit: contain;
    padding: 3px;
}

/* Nombre y tamaño */
#my-awesome-dropzone .dz-preview .dz-details {
    order: 2;
    position: static !important;
    opacity: 1 !important;
    padding: 0 !important;
    margin: 0 !important;
    min-width: 0 !important;
    max-width: none !important;
    text-align: left !important;
    line-height: 1.3 !important;
    display: flex;
    flex-direction: column;
    flex: 1;
}
#my-awesome-dropzone .dz-preview .dz-filename span,
#my-awesome-dropzone .dz-preview .dz-filename:hover span {
    font-size: .78rem;
    color: var(--adm-text);
    background: none !important;
    border: none !important;
    padding: 0 !important;
}
#my-awesome-dropzone .dz-preview .dz-size {
    display: block !important;
    font-size: .68rem;
    color: var(--adm-muted);
    margin: 0 !important;
}
#my-awesome-dropzone .dz-preview .dz-size span {
    background: none !important;
    padding: 0 !important;
}

/* Barra de progreso */
#my-awesome-dropzone .dz-preview .dz-progress {
    order: 3;
    position: static !important;
    width: 120px !important;
    height: 4px !important;
    margin: 0 0 0 auto !important;
    background: rgba(255,255,255,.1) !important;
    border-radius: 99px;
    overflow: hidden;
    opacity: 1 !important;
    animation: none !important;
    transform: none !important;
    flex-shrink: 0;
}
#my-awesome-dropzone .dz-preview .dz-upload {
    display: block;
    height: 100%;
    background: var(--adm-accent) !important;
    transition: width .3s;
    position: static !important;
    top: auto !important;
    bottom: auto !important;
    left: auto !important;
    right: auto !important;
}

/* Marcas por defecto fuera */
#my-awesome-dropzone .dz-preview .dz-success-mark,
#my-awesome-dropzone .dz-preview .dz-error-mark { display: none !important; }

/* Indicador propio */
#my-awesome-dropzone .dz-preview::after {
    order: 4;
    font-size: .9rem;
    font-weight: 700;
    flex-shrink: 0;
    width: 16px;
    text-align: center;
}
#my-awesome-dropzone .dz-preview.dz-success::after { content: '✓'; color: var(--adm-success); }
#my-awesome-dropzone .dz-preview.dz-error::after   { content: '✕'; color: var(--adm-danger); }

#my-awesome-dropzone .dz-preview.dz-success { border-color: rgba(45,198,83,.3); }
#my-awesome-dropzone .dz-preview.dz-success .dz-progress { display: none !important; }
#my-awesome-dropzone .dz-preview.dz-error {
    border-color: rgba(255,77,77,.4);
    background: rgba(255,77,77,.05);
}

/* Mensaje de error */
#my-awesome-dropzone .dz-preview .dz-error-message {
    order: 5;
    display: none;
    position: static !important;
    opacity: 1 !important;
    background: none !important;
    color: var(--adm-danger) !important;
    font-size: .72rem;
    padding: 0 !important;
    margin-top: .3rem;
    flex-basis: 100%;
    width: auto !important;
    top: auto !important;
    left: auto !important;
}
#my-awesome-dropzone .dz-preview .dz-error-message:after { display: none !important; }
#my-awesome-dropzone .dz-preview.dz-error .dz-error-message { display: block; }

/* Mensaje central de la zona */
#my-awesome-dropzone .dz-message {
    text-align: center !important;
    margin: 0 !important;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

/* ── Logo list rows ── */
.logo-row-item {
    display: grid;
    grid-template-columns: 60px 1fr 1fr auto;
    gap: .5rem;
    align-items: center;
    padding: .6rem .75rem;
    border-bottom: 1px solid var(--adm-border);
    transition: background .15s;
}

.logo-row-item:hover { background: rgba(212,255,0,.03); }
.logo-row-item:last-child { border-bottom: none; }

.logo-row-item img {
    width: 48px; height: 48px;
    border-radius: 8px;
    background: #fff;
    object-fit: contain;
    padding: 3px;
}

.logo-row-item input {
    background: rgba(255,255,255,.04);
    border: 1px solid var(--adm-border);
    border-radius: 8px;
    color: var(--adm-text);
    font-size: .82rem;
    padding: .4rem .75rem;
    width: 100%;
    font-family: inherit;
    outline: none;
    transition: border-color .2s;
}

.logo-row-item input:focus { border-color: var(--adm-accent); }
.logo-row-item input::placeholder { color: var(--adm-muted); }

.logo-save-btn {
    background: rgba(212,255,0,.1);
    border: 1px solid rgba(212,255,0,.3);
    color: var(--adm-accent);
    border-radius: 8px;
    padding: .4rem .85rem;
    font-size: .75rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    white-space: nowrap;
}

.logo-save-btn:hover { background: var(--adm-accent); color: #0d0f1c; }

.logo-save-btn.saved {
    background: rgba(45,198,83,.15);
    border-color: rgba(45,198,83,.3);
    color: var(--adm-success);
    cursor: default;
}

.logo-save-btn.needs-name {
    background: rgba(244,208,63,.15);
    border-color: rgba(244,208,63,.3);
    color: var(--adm-warning);
}

.logo-save-btn.error-state {
    background: rgba(255,77,77,.15);
    border-color: rgba(255,77,77,.3);
    color: var(--adm-danger);
}
</style>

<script>
// ── Ajax subcategories ──
$(function() {
    $("#cat_id").on("change", function() {
        var catVal = $(this).val();
        if (catVal) {
            $.ajax({
                type: "GET",
                url: "ajax-category.php",
                data: "cat_id=" + catVal,
                success: function(html) {

                $("#subcat").html('<option value="">Select subcategory...</option>' + html).prop('disabled', false);
                $("#subcat").val(''); // forzar vacío
                checkDropzone();
                }
            });
        } else {
            $("#subcat").html('<option value="">Select subcategory...</option>').prop('disabled', true);
        }
        checkDropzone();
    });

    $("#subcat").on("change", function() {
        checkDropzone();
    });
});

// ── Verificar si el dropzone debe estar activo ──
function checkDropzone() {
    var cat = $("#cat_id").val();
    var sub = $("#subcat").val();
    var dz  = $("#my-awesome-dropzone");
    var msg = $(".dz-lock-msg");

    if (cat && sub) {
        dz.removeClass("dz-locked");
        msg.removeClass("visible");
    } else if (cat && !sub) {
        dz.addClass("dz-locked");
        msg.text("Please select a subcategory to enable upload").addClass("visible");
    } else {
        dz.addClass("dz-locked");
        msg.text("Please select a category and subcategory first").addClass("visible");
    }
}

// ── Dropzone ──
Dropzone.autoDiscover = false;

$(function() {
    var uploadedCount = 0;
    var skippedCount  = 0;

    // Bloquear al inicio
    $("#my-awesome-dropzone").addClass("dz-locked");

    var myDropzone = new Dropzone("#my-awesome-dropzone", {
        url: "<?php echo $setting['website_url']; ?>/admin/ajax-upload.php",
        paramName: "file",
        maxFilesize: 1024,
        maxFiles: 200,
        autoProcessQueue: true,
        acceptedFiles: ".svg,image/svg+xml",
        dictInvalidFileType: "Not an SVG file — rejected",
        dictFileTooBig: "Too large ({{filesize}}MB). Max {{maxFilesize}}MB",
        dataType: "json",
        headers: { 'X-Requested-With': 'XMLHttpRequest' },

        params: function() {
            return {
                cat_id: $("#cat_id").val(),
                subcat: $("#subcat").val()
            };
        },

        init: function() {
            var dz = this;

            // Bloquear upload si no hay categoría o subcategoría
            dz.on('addedfile', function(file) {
                var cat = $("#cat_id").val();
                var sub = $("#subcat").val();

                if (!cat || !sub) {
                    dz.removeFile(file);
                    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "3000" };
                    if (!cat) {
                        toastr["warning"]("Please select a category first", "Required");
                    } else {
                        toastr["warning"]("Please select a subcategory first", "Required");
                    }
                    return;
                }
                var img = file.previewElement.querySelector('[data-dz-thumbnail]');
                if (img) {
                    var reader = new FileReader();
                    reader.onload = function(e) { img.src = e.target.result; };
                    reader.readAsDataURL(file);
                }
            });

            dz.on('sending', function(file) {
               // toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "2000" };
               // toastr["info"](file.name, "Uploading...");
            });

            dz.on('error', function(file, errormessage) {
                toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "4000" };
                toastr["error"](file.name, "Upload failed");
            });

            // Resumen final cuando termina toda la tanda
            dz.on('queuecomplete', function() {
                if (uploadedCount > 0 || skippedCount > 0) {
                    var msg = uploadedCount + ' uploaded';
                    if (skippedCount > 0) msg += ', ' + skippedCount + ' skipped (duplicates)';
                    toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-top-right", timeOut: "6000" };
                    if (skippedCount > 0) {
                        toastr["info"](msg, "Upload complete");
                    } else {
                        toastr["success"](msg, "Upload complete");
                    }
                }
            });
        },

        success: function(response, data) {
            var json = JSON.parse(data);

            // ── Si el endpoint rechazó por duplicado (hash o nombre) ──
            if (json.error === true || json.skipped === true) {
                skippedCount++;
                if (response.previewElement) {
                    response.previewElement.classList.remove('dz-success');
                    response.previewElement.classList.add('dz-error');
                    // Mostrar el motivo en el preview
                    var errEl = response.previewElement.querySelector('.dz-error-message span');
                    if (errEl) errEl.textContent = json.message || 'Duplicate';
                }
                toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-bottom-right", timeOut: "4000" };
                toastr["warning"](json.message || 'Skipped', "Duplicate skipped");
                return;
            }

            if (response.previewElement) response.previewElement.classList.add('dz-success');
            function uppercase(str) {
                return str.split(' ').map(function(w) {
                    return w.charAt(0).toUpperCase() + w.slice(1);
                }).join(' ');
            }

            var title_vect = response.name;
            var remove_ext = title_vect.split("/").slice(-1).join().split(".").shift();
            var clean_str  = remove_ext.replace(/[&\/\-\#,+()$~_%.'":*?<>@{}]/g, " ");
            var finalTitle = uppercase(clean_str);

            uploadedCount++;
            $('#logo-list-wrap').show();
            $('#logo-count').text(uploadedCount + ' logo' + (uploadedCount !== 1 ? 's' : '') + ' ready to save');

            var row = `
                <li class="logo-row-item" id="row-${json.id}">
                    <img src="${response.dataURL}" alt="${finalTitle}">
                    <input class="name" name="name_val ${json.id}" maxlength="99" value="${finalTitle}" placeholder="Logo name">
                    <input name="tags_val ${json.id}" placeholder="Tags (comma separated)">
                    <button class="logo-save-btn" id="btn-${json.id}" data-label="Save" onclick="upload_logo('${json.id}'); return false;">
                        <i class="fa-regular fa-floppy-disk"></i> Save
                    </button>
                </li>`;

            $('#logo-list').append(row);

            toastr.options = { closeButton: true, progressBar: true, positionClass: "toast-bottom-right", timeOut: "2000" };
            toastr["success"](finalTitle, "Uploaded");
        }
    });
});

// ── Save individual logo ──
function upload_logo(clicked_id) {
    event.preventDefault();

    var name = $("input[name='name_val " + clicked_id + "']").val();
    var tags = $("input[name='tags_val " + clicked_id + "']").val();
    var btn  = $("#btn-" + clicked_id);

    if (!name.trim()) {
        // Aviso inline en el botón, sin toast que tape los campos
        var original = btn.data('label') || 'Save';
        btn.html('<i class="fa-solid fa-triangle-exclamation"></i> Name?').addClass('needs-name');
        setTimeout(function() {
            btn.html('<i class="fa-regular fa-floppy-disk"></i> ' + original).removeClass('needs-name');
        }, 1500);
        return;
    }

    // Detectar si ya se guardó antes (para el texto Saving/Updating)
    var alreadySaved = btn.data('saved') === true;
    var workingText  = alreadySaved ? 'Updating...' : 'Saving...';

    btn.html('<i class="fa-regular fa-spinner fa-spin"></i> ' + workingText).prop('disabled', true);

    $.post("ajax-update-logo.php", { id: clicked_id, name: name, tags: tags },
        function(data) {
            if (data == 'error') {
                btn.html('<i class="fa-solid fa-triangle-exclamation"></i> Error')
                   .removeClass('saved').addClass('error-state').prop('disabled', false);
                setTimeout(function() {
                    var lbl = btn.data('saved') === true ? 'Update' : 'Save';
                    btn.html('<i class="fa-regular fa-floppy-disk"></i> ' + lbl)
                       .removeClass('error-state');
                }, 1800);
            } else {
                // Confirmación inline: mostrar "Saved/Updated ✓" un momento
                var doneText = alreadySaved ? 'Updated' : 'Saved';
                btn.html('<i class="fa-solid fa-circle-check"></i> ' + doneText)
                   .addClass('saved')
                   .prop('disabled', true)
                   .data('saved', true);

                // Luego rehabilitar como "Update" activo (para volver a guardar)
                setTimeout(function() {
                    btn.html('<i class="fa-regular fa-pen-to-square"></i> Update')
                       .removeClass('saved')
                       .prop('disabled', false)
                       .data('label', 'Update');
                }, 1500);
            }
        }
    );
}

// ── TinyMCE ──
tinymce.init({
    selector: "textarea",
    themes: "modern",
    branding: false,
    plugins: ['advlist autolink lists link image charmap preview', 'visualblocks code', 'insertdatetime media contextmenu paste code'],
    toolbar: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code'
});
</script>

<?php require_once 'includes/footer.php'; ?>