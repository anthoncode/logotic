<?php

$pageTitle = "Add Logo";
require_once '../system/config-admin.php';

$category = $product->get_categories();
//$category1 = $product->get_subcategories();
require_once 'includes/header1.php';
?>
<!-- <script type="text/javascript" src="<?php echo $setting['website_url']; ?>/admin/css/tinymce.min.js"></script>
-->
<script src="//cdnjs.cloudflare.com/ajax/libs/tinymce/4.6.5/tinymce.min.js"></script>
<div class="content">

  <nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/products.php">All Logos</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-product.php">Add Logo</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/best-selling-products.php">Top</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/products.php">All logos</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <form id="my-awesome-dropzone" class="form-horizontal dropzone">
       <div class="form-group"> <label>Category:</label>
          <div class="input-group mb-3">
            <select class="custom-select" name="cat_id" id="cat_id" required>
              <option value="">Select Category...</option>
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
              <option value="">Select Subcategory...</option>

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

        <style>
          .list-group {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-direction: column;
            flex-direction: column;
            padding-left: 0;
            margin-bottom: 0;
            border-radius: .25rem;
          }
        </style>

       <script>
        //Disabling autoDiscover
        Dropzone.autoDiscover = false; 

        $(function() {
            //Dropzone class
            var myDropzone = new Dropzone(".dropzone", {
                url: "<?php echo $setting['website_url']; ?>/admin/ajax-upload.php",
                paramName: "file",
                maxFilesize: 1024,
                maxFiles: 200,
                autoProcessQueue: true,
                acceptedFiles: "image/svg+xml",
                dataType: "json",

                init: function(){
                  this.on('sending', function(file, formData, xhr){
                     toastr.options = {
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": false,
                            "progressBar": true,
                            "positionClass": "toast-top-left",
                            "preventDuplicates": false,
                            "onclick": null,
                            "showDuration": "500",
                            "hideDuration": "500",
                            "timeOut": "5000",
                            "extendedTimeOut": "1000",
                            "showEasing": "swing",
                            "hideEasing": "linear",
                            "showMethod": "fadeIn",
                            "hideMethod": "fadeOut"
                        }                    
                        Command: toastr["success"](file.name)       
                  },);

                  this.on('error', function(file, errormessage, xhr){
                     toastr.options = {
                           "closeButton": true,
                           "debug": false,
                           "newestOnTop": false,
                           "progressBar": true,
                           "positionClass": "toast-top-left",
                           "preventDuplicates": false,
                           "onclick": null,
                           "showDuration": "500",
                           "hideDuration": "500",
                           "timeOut": "5000",
                           "extendedTimeOut": "1000",
                           "showEasing": "swing",
                           "hideEasing": "linear",
                           "showMethod": "fadeIn",
                           "hideMethod": "fadeOut"
                     }
                        Command: toastr["error"](file.name)
                  },);

                },
                success: function(response, data, file) {
                    
                    function uppercase(str)//Pone en mayúscula cada palabra
                    {
                      var array1 = str.split(' ');
                      var newarray1 = [];
                        
                      for(var x = 0; x < array1.length; x++){
                          newarray1.push(array1[x].charAt(0).toUpperCase()+array1[x].slice(1));
                      }
                      return newarray1.join(' ');
                    }
                    
                    //alert(name);
                    //convierte en objeto el texto array - {"id":"1"} a objeto 
                    json = JSON.parse(data); 
                    //console.log(json.id);
                    //console.log(json.file.name);

                    var title_vect = response.name;
                    var iddd = response.id;
                    remove_ext = title_vect.split("/").slice(-1).join().split(".").shift();
                    clean_str = remove_ext.replace(/[&\/\-\#,+()$~_%.'":*?<>@{}]/g, " ");
                    finalTitle = uppercase(clean_str);

                    var name_in = $("input[name='name_lg']").val();
                    $txt = '<form id="'+json.id+'" class="form_logo" ><li class="list-group-item d-flex justify-content-between align-items-center"><div class="form-group col-md-1 mx-sm-3 mb-2"><img style="border-radius:5px" width="50" height="50" src="'+response.dataURL+'"></div><div class="form-group col-md-4 mx-sm-3 mb-2"><input class="name form-control mt-2" name="name_val '+json.id+'" maxlength="99" required value="'+finalTitle+'"></div><div class="form-group col-md-4 mx-sm-3 mb-2"><input class="form-control mt-2" name="tags_val '+json.id+'" placeholder="Tags" required></div><a id="'+json.id+'" onclick="upload_logo(this.id);" type="submit" class="btn-login btn btn-success btn-block text-white ml-1 mt-0 quick-post-rename"><i class="fas fa-pen"></i> </a></div></li></form>';


                    //$miscript =  $.get("css/script.js"); ;
                    $('#logo-list').append($txt);
                    //$('#logo-list').append($txt, $miscript);
                    return;
                },
            });
        });


        </script>

        <script>
          function upload_logo(clicked_id) { //clicked_id es una variable que captura el id
            event.preventDefault()//evita redirigir después del get
            //alert(clicked_id)
            var id = clicked_id;
            var name = $("input[name='name_val "+clicked_id+"']").val(); //obtiene el atributo de name
            //var description = $("input[name='desc_val "+clicked_id+"']").val();
            var tags = $("input[name='tags_val "+clicked_id+"']").val();
            $.post("ajax-update-logo.php", { id: id, name: name, tags: tags},
            function(data) {
              //$('#results').html(data);
              if(data == 'error') {
                  toastr["error"]("Error")
                  toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toast-bottom-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                  }

              } else {
                  //toastr["success"]("Success")
                  Command: toastr["success"](name)
                  toastr.options = {
                  "closeButton": true,
                  "debug": false,
                  "newestOnTop": false,
                  "progressBar": true,
                  "positionClass": "toast-bottom-right",
                  "preventDuplicates": false,
                  "onclick": null,
                  "showDuration": "300",
                  "hideDuration": "1000",
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showEasing": "swing",
                  "hideEasing": "linear",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
                }
              }
            });
            return
          }
        </script>
    </form>

    <!-- formulario para actualizar -->
    <div class="col-md-12 mt-5">
        <ul class="list-group" id="logo-list"></ul>
    </div>
    <div id="results">
    <br>

    <script>
      $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
      });
    </script>

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