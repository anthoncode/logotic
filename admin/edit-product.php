<?php

$pageTitle = "Edit File";
require_once '../system/config-admin.php';

if (isset($_REQUEST['id'])) {
  $productDetails = $product->details($_REQUEST['id']);
  $error          = ($product->error ? $product->error : false);

  if (empty($error)) {
    unset($error);
  }
} else {
  require_once 'includes/header1.php';
  echo 'Invalid request';
  require_once 'includes/footer.php';
  exit;
}
$category = $product->get_categories();
require_once 'includes/header1.php';
?>
<script src="//cdnjs.cloudflare.com/ajax/libs/tinymce/4.6.5/tinymce.min.js"></script>
<div class="content">

  <nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/products.php">All Products</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-product.php">Add logo</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/best-selling-products.php">Top</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <form id="upload" class="form-horizontal">
      <div class="form-group"> <label>Name:</label>
        <input class="form-control" value="<?php echo $productDetails['name']; ?>" name="name" id="coupon-code" type="text" maxlength="70">
      </div>

      <div class="form-group"> <label>Description:</label> <textarea type="text" class="form-control" name="description" id="coupon-code"><?php echo $productDetails['description']; ?></textarea>
      </div>
      <hr>

      <div class="form-group"> <label>Category:</label>
        <div class="input-group mb-3">

          <select class="custom-select" name="cat_id" id="cat_id" required>
            <option selected value="<?php echo $productDetails['cat_id']; ?>">Change Category...</option>
            <?php foreach ($category as $cat) {
            ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
            <?php } ?>
          </select>
        </div>

      </div>

      <div class="form-group"> <label>Subcategory:</label>
        <div class="input-group mb-3">
          <select class="custom-select" name="subcat" id="subcat">
            <option value="<?php echo $productDetails['subc_id']; ?>">Select Subcategory...</option>

          </select>
        </div>
      </div>

      <script type="text/javascript">
        $(function() {

          $("#cat_id").bind("change", function() {
            $.ajax({
              type: "GET",
              url: "ajax-category.php",
              data: "cat_id=" + $("#cat_id").val(),
              success: function(html) {
                $("#subcat").html(html);
              }
            });
          });


        });
      </script>


      <div class="form-group">
        <label>Status:</label>
        <div class="input-group mb-3">
          <select class="custom-select" name="active" id="inputGroupSelect01">
            <option value="1">Item Status...</option>
            <option value="1">Active</option>
            <option value="2">Paused</option>
          </select>
        </div>

      </div>

      <!-- <div class="form-group"> <label>Live Preview:</label> <input class="form-control" value="<?php echo $productDetails['demo']; ?>" name="demo" id="coupon-code" type="url"></div> -->

      <div class="form-group"> <label>Website:</label> <input class="form-control" value="<?php echo $productDetails['website']; ?>" name="website" id="coupon-code" type="url"></div>
      <hr>

      <div class="form-group"> <label>Tags:</label> <input class="form-control" value="<?php echo $productDetails['tags']; ?>" name="tags" id="skills" data-role="tagsinput" type="text"></div>
      <hr>

      <div class="form-check">
        <input type="checkbox" name="featured" <?php if ($productDetails['featured'] == 1) {
                                                  echo 'checked="checked"';
                                                } ?> id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">Featured Item</label>
      </div>
      <div class="form-check">
        <input type="checkbox" name="icon" <?php if ($productDetails['icon'] == 1) {
                                              echo 'checked="checked"';
                                            } ?> id="exampleCheck1">
        <label class="form-check-label" for="exampleCheck1">icon</label>
      </div>

      <div class="form-check">
        <input type="checkbox" name="download_off" <?php if ($productDetails['download_off'] == 1) {
                                                      echo 'checked="checked"';
                                                    } ?> id="exampleCheck1">
        <label class="form-check-label text-danger" for="exampleCheck1">Disable Downloads</label>
      </div>
      <div class="form-check">
        <input type="checkbox" name="views_off" <?php if ($productDetails['views_off'] == 1) {
                                                  echo 'checked="checked"';
                                                } ?> id="exampleCheck1">
        <label class="form-check-label text-danger" for="exampleCheck1">Hide View Counter</label>
      </div>


      <hr>
      <input class="form-control" type="hidden" name="id" value="<?php echo $productDetails['id']; ?>">
      <button type="submit" id="btn" class="btn btn-primary w-100">Update</button>
      <script type="text/javascript">
        $("#upload").on("submit", (function(e) {

          tinyMCE.triggerSave();

          e.preventDefault();
          $.ajax({
            url: "<?php echo $setting['website_url']; ?>/admin/ajax-update.php",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
              $("#res").html('Updating..Please wait!');
            },
            success: function(response) {
              $("#res").html(response);
            }
          });
        }));
      </script>
      <div id="res"></div>
    </form>
  </div>

  <div class="my-3 p-3 bg-white rounded box-shadow">


    <form name="updatedata" id="upload1" class="form-horizontal">
      <div class="form-group"> <label>Vector File (.SVG):</label>
        <div class="input-group">
          <div class="custom-file">
            <input type="file" name="iconimgfile" class="form-control" id="inputGroupFile04" accept="image/svg+xml">
          </div>
        </div>
      </div>

      <hr>
      <input class="form-control" type="hidden" name="id" value="<?php echo $productDetails['id']; ?>">
      <button type="submit" id="btn" class="btn btn-success w-100">Update File</button>

      <div class="progress mt-3" style="display:none;">
        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;">

        </div>
      </div>

      <script type="text/javascript">
        $("#upload1").on("submit", (function(e) {
          e.preventDefault();

          e.stopImmediatePropagation();
          var formData = new FormData($(this)[0]);
          var file = $('input[type=file]')[0].files[0];
          formData.append('upload_file', file);
          $('.progress').show();

          $.ajax({

            xhr: function() {
              var xhr = new window.XMLHttpRequest();
              xhr.upload.addEventListener("progress", function(evt) {
                if (evt.lengthComputable) {
                  var percentComplete = evt.loaded / evt.total;
                  percentComplete = parseInt(percentComplete * 100);
                  $('.progress-bar').css('width', percentComplete + "%");
                  $('.progress-bar').html(percentComplete + "%");
                  if (percentComplete === 100) {

                  }
                }
              }, false);
              return xhr;
            },

            url: "<?php echo $setting['website_url']; ?>/admin/ajax-update-files.php",
            type: "POST",
            data: new FormData(this),
            contentType: false,
            cache: false,
            processData: false,
            beforeSend: function() {
              $("#res1").html('Updating..Please wait!');
            },
            success: function(response) {
              $("#res1").html(response);
              //window.location = "products.php"; //redirecciona una vez terminada la actualización
            }
          });
        }));
      </script>
      <div id="res1"></div>
    </form>
  </div>
  <script type="text/javascript">
    tinymce.init({
      selector: "textarea",
      themes: "modern",
      branding: false,
      plugins: [
        'advlist autolink lists link image charmap preview',
        'visualblocks code',
        'insertdatetime media contextmenu paste code'
      ],
      toolbar: 'bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image code'
    });
  </script>
  <?php
  require_once 'includes/footer.php';
  ?>