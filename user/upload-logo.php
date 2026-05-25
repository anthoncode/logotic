<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Upload';
$pg = '5';
require_once('../system/config-user.php');

$category = $product->get_categories();
$setting = $settings->get_all(); //para mostrar correo
$sales = $purchases->all('user_id', $crypt->decrypt($_SESSION['uid'], 'USER'));
require_once('includes/header.php');
if (isset($_GET['type']) && isset($_GET['msg'])) {
    echo  "<div class=\"alert mt-3 " . $_GET['type'] . "\" style=\"display:block;\">" . $_GET['msg'] . "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button></div>";
}

//$eemail = $userDetails['email']; //correo del usuario
//$eemail = $setting['mail_admin']; //correo del admin
//$result = $user->newLogo($eemail);
?>

<script src="//cdnjs.cloudflare.com/ajax/libs/tinymce/4.6.5/tinymce.min.js"></script>
<div class="content">
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
                url: "<?php echo $setting['website_url']; ?>/admin/ajax-category-user.php",
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
                url: "<?php echo $setting['website_url']; ?>/admin/ajax-upload-user.php",
                paramName: "file",
                maxFilesize: 1024,
                maxFiles: 50,
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

                  /*this.on('queuecomplete', function(file){
                            alert("Se subieron todo los archivos");
                            <?php //$result = $user->newLogo($eemail);?>
                        },);*/

                },
                success: function(response, data, file) {
                    //alert(name);
                    //convierte en objeto el texto array - {"id":"1"} a objeto 
                    json = JSON.parse(data); 
                    //console.log(json.id);
                    //console.log(json.file.name);

                    var title_vect = response.name;
                    var iddd = response.id;
                    remove_ext = title_vect.split("/").slice(-1).join().split(".").shift();
                    clean_str = remove_ext.replace(/[&\/\-\#,+()$~_%.'":*?<>@{}]/g, " ");
                    finalTitle = clean_str[0].toUpperCase() +  clean_str.slice(1);

                    var name_in = $("input[name='name_lg']").val();
                    $txt = '<h5 class="mb-4">You can rename uploaded files:</h5><form id="'+json.id+'" class="form_logo" ><li class="list-group-item d-flex justify-content-between align-items-center"><div class="form-group col-md-1 mx-sm-3 mb-2"><img style="border-radius:5px" width="50" height="50" src="'+response.dataURL+'"></div><div class="form-group col-md-4 mx-sm-3 mb-2"><input class="name form-control mt-2" name="name_val '+json.id+'" maxlength="99" required value="'+finalTitle+'"></div><div class="form-group col-md-4 mx-sm-3 mb-2"><input class="form-control mt-2" name="tags_val '+json.id+'" placeholder="Tags" required></div><a id="'+json.id+'" onclick="upload_logo(this.id);" type="submit" class="btn-login btn btn-success btn-block text-white ml-1 mt-0 quick-post-rename"><i class="fas fa-pen"></i> </a></div></li></form>';


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
            $.post("<?php echo $setting['website_url']; ?>/admin/ajax-update-logo-user.php", { id: id, name: name, tags: tags},
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
    </div>
    <br>
    </div>
    </div>

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