<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = "All Categories";

require_once '../system/config-admin.php';

if (isset($_REQUEST['path'])) {

    $file = $_REQUEST['path'];
    if (!@fopen($file, 'r') && (!@file_exists($file))) {
        echo 'File not found, Check file path';
        print_r(error_get_last());
        exit;
    } else {
        echo 'success';
        exit;
    }
}


/*  $productDetails = $product->details($_REQUEST['id']);
  $error          = ($product->error ? $product->error : false);

  if (empty($error)) {
    unset($error);
  }*/

$search      = null;
$search      = (isset($_GET['search'])) ? $_GET['search'] : null;

$currpage    = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres      = 20;
$num         = $product->countAll($search);
$pages       = $num / $maxres;
$pages       = ceil($pages);
$start       = ($currpage - 1) * $maxres;
$last        = $start + $maxres - 1;
$allProducts = $product->getLogoList($start, $maxres, $search);

//$downd_itm = $product->downloadCount($_REQUEST['id']);
$category = $product->get_categories();

require_once 'includes/header1.php';
?>

<div class="content">
  <nav class="navbar navbar-expand-lg navbar-dark text-white rounded bg-primary box-shadow">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
      <ul class="navbar-nav">
        <li class="nav-item active">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/all-logos.php">All logos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/add-product.php">Add Logo</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/best-selling-all-category.php">Top</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="my-3 p-3 bg-white rounded box-shadow">
    <?php if ($num > 0) { ?>
      <div id="response" class="alert text-center" style="display:none;">
              <button type="button" class="close" id="clearMsg"><span aria-hidden="true">&times;</span></button>
              <span id="message"></span>
      </div>

      <ul class="float-left" style="padding-left: 0;">
        <form action="all-category.php" method="GET" class="form-inline my-2 my-lg-0">
          <div class="input-group mb-0">
            <input type="text" placeholder="Search" name="search" class="form-control form-control-sm header-search" value="<?php echo $search; ?>">
            <div class="input-group-append">
              <button class="btn header-searchcustombtn btn-sm" type="submit"><i class="fa-regular fa-magnifying-glass"></i></button>
            </div>
            </div>
        </form>
      </ul>
      <!-- <div id="res" class="res"></div> -->
      <ul class="pagination float-right">
        <!-- ?search='. $search .'&page  busca palabra clave y número de página-->
        <?php
        $back = (($currpage == 1) ? '#' : 'all-category.php?search='. $search .'&page=' . ($currpage - 1));
        $next = (($currpage == $pages) ? 'all-category.php?search='. $search .'&page=' . $currpage : 'all-category.php?search='. $search .'&page=' . ($currpage + 1));
        ?>
        <li class="page-item">
          <a class="page-link">
          <?php echo $num; ?> elements
          </a>
        </li>
        <!-- <<< -->
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == 1) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Previous" href="<?php echo $back; ?>" tabindex="-1"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
        </li>
        <li class="page-item">
          <a class="page-link">
          <?php echo "".$currpage." of ".$pages.""?>
          <a>
        </li>
        <!-- >>> -->
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == $pages) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Next" href="<?php echo $next; ?>"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
        </li>
      </ul>
      <table class="table table-hover table-striped">
        <thead>
          <tr>
            <th>Name</th>
            <th>SVG</th>
            <th>Category</th>
            <th>Sub category</th>

          </tr>
        </thead>
        <tbody>
          <?php
              foreach ($allProducts as $productss) {
              $ip_item  = $productss['id'];
              $download = $product->downloadCount($ip_item);
          ?>
            <tr id="<?php echo $productss['id']; ?>" class="id_table">
              <td>
                <span class="editValue name"><?php echo $productss['name']; ?></span>
                <input class="form-control editInput name" type="text" name="name" value="<?php echo $productss['name']; ?> " style="display:none;">
              </td>

              <style>
                .first {
                    width: 60%;
                }
                .overflow-tag {
                    position: relative;
                }
                .overflow-tag:before {
                    content: ' ';
                    visibility: hidden;
                }
                .overflow-tag span {
                    position: absolute;
                    left: 0;
                    right: 0;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
              </style>

              <td><img itemprop="image" class="pull-left thumb-lg m-r-md rounded mr-3" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $productss['icon_img']; ?>" width="50" height="50"></td>

              <td data-title="">
                  <?php $idd = $productss['id'];?>
                  <select class="custom-select" name="cat_id" id="cat_id<?php echo $idd ?>" required>
                    <option value="<?php echo $productss['cat_id']; ?>" >Change Category...</option>
                    <?php foreach ($category as $cat) {
                      $selected = $productss['cat_id'];
                    ?>
                      <option <?php if ($selected == $cat['id']){echo "selected";}; ?> value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                    <?php } ?>
                  </select>
              </td>

              <td>
                <select class="custom-select" name="subcat" id="subcat<?php echo $idd; ?>">
                  <option value="<?php echo $productss['subc_id']; ?>">Select Subcategory...</option>
                </select>
                <script type="text/javascript">

                 $(function() {

                  $("#cat_id<?php echo $idd; ?>").bind("change", function() {
                    $.ajax({
                      type: "POST",
                      url: "<?php echo $setting['website_url']; ?>/admin/ajax-min-cat.php",
                      data: "cat_id=" + $("#cat_id<?php echo $idd; ?>").val() + "&id=" + <?php echo $idd; ?>,
                      success: function(html) {
                        $("#subcat<?php echo $idd; ?>").html(html);
                        toastr["success"]("Success")
                        toastr.options = {
                              "closeButton": false,
                              "debug": false,
                              "newestOnTop": false,
                              "progressBar": false,
                              "positionClass": "toast-bottom-left",
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
                  });

                });

                  $(function() {

                   $("#subcat<?php echo $idd; ?>").bind("change", function() {
                     //alert("cuac");
                     $.ajax({
                       type: "POST",
                       url: "ajax-min-cat.php",
                       data: "subc_id=" + $("#subcat<?php echo $idd; ?>").val() + "&id=" + <?php echo $idd; ?>,

                       success: function(html) {
                         toastr["success"]("Success")
                          toastr.options = {
                              "closeButton": false,
                              "debug": false,
                              "newestOnTop": false,
                              "progressBar": false,
                              "positionClass": "toast-bottom-left",
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
                         //$("#subcat<?php echo $idd; ?>").html(html);
                       }
                     });
                   });

                 });

                /*
                 $("select[id='cat_id<?php echo $idd ?>']").on("change", () => {
                   sendCate();
                 });

                 $("select[id='subcat<?php echo $idd; ?>']").on("change", () => {
                   sendSubcate();
                 });

                 function sendCate(){
                  alert("hola");
                  $.ajax({
                      type: "GET",
                      url: "ajax-category.php",
                      data: "cat_id=" + $("#cat_id<?php echo $idd; ?>").val(),
                      success: function(html) {
                        $("#subcat<?php echo $idd; ?>").html(html);
                      }
                    });
                 }

                 function sendSubcate(){
                  alert("chau");
                  $.ajax({
                      type: "GET",
                      url: "ajax-category.php",
                      data: "cat_id=" + $("#cat_id<?php echo $idd; ?>").val(),
                      success: function(html) {
                        $("#subcat<?php echo $idd; ?>").html(html);
                      }
                    });
                 }
                */
                </script>

              </td>

            </tr>
          <?php }?>
        </tbody>
      </table>
  </div>

  <br>
  <ul class="pagination float-right">
        <!-- ?search='. $search .'&page  busca palabra clave y número de página-->
        <?php
        $back = (($currpage == 1) ? '#' : 'all-category.php?search='. $search .'&page=' . ($currpage - 1));
        $next = (($currpage == $pages) ? 'all-category.php?search='. $search .'&page=' . $currpage : 'all-category.php?search='. $search .'&page=' . ($currpage + 1));
        ?>
        <li class="page-item">
          <a class="page-link">
          <?php echo $num; ?> elements
          </a>
        </li>
        <!-- <<< -->
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == 1) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Previous" href="<?php echo $back; ?>" tabindex="-1"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
        </li>
        <li class="page-item">
          <a class="page-link">
          <?php echo "".$currpage." of ".$pages.""?>
          <a>
        </li>
        <!-- >>> -->
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == $pages) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Next" href="<?php echo $next; ?>"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
        </li>
      </ul>
</div>
</div>



<script type="text/javascript">
  $(document).ready(function(){
    //fetch table data
    //fetch();
    //clicking edit button
    $(document).on('click', '.editbutton', function(){
      var row = $(this).closest('.id_table');
      //hide values
          row.find('.editValue').hide();
          //show edit input
          row.find('.editInput').show();
          //show save button
          row.find('.savebutton').show();
          //hide edit button
          $(this).hide();
    });


    //save
    $(document).on('click', '.savebutton', function(){
      var row = $(this).closest('.id_table');
      //hide textbox
      row.find('.editInput').hide();
      //show value
      row.find('.editValue').show();
      //show edit button
      row.find('.editbutton').show();
      //hide save button
      $(this).hide();
      var id = row.attr('id');
      var form = row.find('.editInput').serializeArray();
      form.push({ name:'id', value:id });
      $.ajax({
        method: 'POST',
        url: '<?php echo $setting['website_url']; ?>/admin/edit-list.php',
        data: form,
        dataType: 'json',

        success: function(response){
        if(response.error){
          //$('#response').show().removeClass('alert-sucess').addClass('alert-danger');
          //$('#message').html(response.message);

          toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-top-right",
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
             Command: toastr["error"](response.message)
        }
          else{
            //$('#response').show().removeClass('alert-danger').addClass('alert-success');
            //$('#message').html(response.message);

            toastr.options = {
                   "closeButton": true,
                   "debug": false,
                   "newestOnTop": false,
                   "progressBar": true,
                   "positionClass": "toast-top-right",
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
               Command: toastr["success"](response.message)


            //populate table with updated row
            row.find('.editValue.name').html(response.member.name);
            row.find('.editValue.description').html(response.member.description);
            row.find('.editValue.tags').html(response.member.tags);

            row.find('.editInput.name').val(response.member.name);
            row.find('.editInput.description').val(response.member.description);
            row.find('.editInput.tags').val(response.member.tags);
          }
        }
      });
    });
    //clear msg
    /*$('#clearMsg').click(function(){
      $('#response').hide();
    });*/

    
  });

</script>


<script type="text/javascript">
    var active = '';
    var id = 0;
    $('.onoffswitch-small-checkbox').click(function() {
        if($(this).prop('checked')) {
            active = '1';
            id = $(this).parent().attr("id");
        } else {
            active = '0';
            id = $(this).parent().attr("id");
        }

        if((active != '' || active != null) && (id !='')) {
            $.ajax({
                type: 'POST',
                url: "ajax-active.php",
                data: "id=" + id + "&active=" + active,
                dataType: "html",
                success: function(data) {

                    if(data == 'error') {
                        toastr["error"]("Error")
                        toastr.options = {
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": false,
                            "progressBar": false,
                            "positionClass": "toast-top-right",
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

                    } else {
                        toastr["success"]("Success")
                        toastr.options = {
                            "closeButton": true,
                            "debug": false,
                            "newestOnTop": false,
                            "progressBar": false,
                            "positionClass": "toast-top-right",
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
                    }
                }
            });
        }
    });
</script>

<?php
} else {
    echo "<div class='alert'>No Products added yet</div>";
}
require_once 'includes/footer.php';
?>