<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Products";

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


$search      = null;
$search      = (isset($_GET['search'])) ? $_GET['search'] : null;
$currpage    = (isset($_GET['page'])) ? $_GET['page'] : 1;
$maxres      = 20;
$num         = $product->countAll($search); // $search? //muestra el total 
$pages       = $num / $maxres; //divide tota entre el max de filas
$pages       = ceil($pages); //devuelve un número entero
$start       = ($currpage - 1) * $maxres;
$last        = $start + $maxres - 1;
$allProducts = $product->getLogoList($start, $maxres, $search);
//$allProducts = $product->getLogoList();

//$downd_itm = $product->downloadCount($_REQUEST['id']);

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
          <a class="nav-link" href="<?php echo $setting['website_url']; ?>/admin/best-selling-all-logos.php">Top</a>
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
      <!-- <div id="res" class="res"></div> -->
      <ul class="float-left" style="padding-left: 0;">
        <form action="all-logos.php" method="GET" class="form-inline my-2 my-lg-0">
          <div class="input-group mb-0">
            <input type="text" placeholder="Search" name="search" class="form-control form-control-sm header-search" value="<?php echo $search; ?>">
            <div class="input-group-append">
              <button class="btn header-searchcustombtn btn-sm" type="submit"><i class="fa-regular fa-magnifying-glass"></i></button>
            </div>
            </div>
        </form>
      </ul>

      <ul class="pagination float-right">
        <?php
        $back = (($currpage == 1) ? '#' : 'all-logos.php?search='. $search .'&page=' . ($currpage - 1));
        $next = (($currpage == $pages) ? 'all-logos.php?search='. $search .'&page=' . $currpage : 'all-logos.php?search='. $search .'&page=' . ($currpage + 1));
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

      <table id="table_id" class="table table-hover table-striped display">
        <thead>
          <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Tags</th>
            <th>Website</th>
            <th>Status</th>
            <th><i class="fa-solid fa-eye"></i></th>
            <th><i class="fa-solid fa-download"></i></th>
            <th><i class="fa-solid fa-file"></i></th>
            <th>Actions</th>
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
              <td>
                <span class="editValue description"><?php echo $productss['description']; ?></span>
                <input class="form-control editInput description" type="text" name="description" value="<?php echo $productss['description']; ?>" style="display:none;">
              </td>
              <td class="overflow-tag first">
                <span class="editValue tags"><?php echo $productss['tags']; ?></span>
                <input class="form-control editInput tags" type="text" name="tags" value="<?php echo $productss['tags']; ?>" style="display:none;">
              </td>
              <td class="overflow-tag first">
                <span class="editValue website"><?php echo $productss['website']; ?></span>
                <input class="form-control editInput website" type="text" name="website" value="<?php echo $productss['website']; ?>" style="display:none;">
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
              
              <!-- <td><?php //echo ($productss['active'] == '1' ? '<span data-toggle="tooltip" data-placement="top" title="Item Active" class="badge badge-success badge-pill">Active</span>' : '<span data-toggle="tooltip" data-placement="top" title="Item Paused" class="badge badge-warning badge-pill">Paused</span>'); ?></td> -->

              <td data-title="">
                  <div class="onoffswitch-small" id="<?php echo $productss['id'];?>">
                      <input type="checkbox" id="myonoffswitch<?php echo $productss['id'];?>" class="onoffswitch-small-checkbox" name="switch-btn"  <?php if ($productss['active'] === '1') {echo "checked='checked'";}?>>
                      
                      <label for="myonoffswitch<?php echo $productss['id'];?>" class="onoffswitch-small-label">
                          <span class="onoffswitch-small-inner"></span>
                          <span class="onoffswitch-small-switch"></span>
                      </label>
                  </div>
              </td>

              <td><?php echo $productss['views']; ?></td>

              <td>
                <?php echo $download['doCount']; ?>
              </td>

              <td><img itemprop="image" class="pull-left thumb-lg m-r-md rounded mr-3" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $productss['icon_img']; ?>" width="50" height="50"></td>
              <td>
                <div class="btn-group btn-group-sm" role="group" aria-label="AActions">
                  <a href="<?php echo $setting['website_url']; ?>/item.php?id=<?php echo $productss['id']; ?>" class="btn btn-info" target="_blank"><i class="fa-solid fa-eye"></i></a>
                 <!--  <a type="submit" id="btn" href="" class="btn btn-primary w-100">Edit</a> -->
                  <button class="btn btn-primary editbutton">
                    <span class="glyphicon glyphicon-edit"></span> <i class="fa-solid fa-pen-to-square"></i>
                  </button>
                  <button class="btn btn-success savebutton" style="display:none;">
                    <span class="glyphicon glyphicon-floppy-disk"></span> <i class="fa-solid fa-floppy-disk"></i>
                  </button>

                  <a href="edit-product.php?id=<?php echo $productss['id']; ?>" class="btn btn-warning"><i class="fa-solid fa-file-pen"></i></a>

                  <button class="btn btn-danger btn-delete-file" data-id='<?php echo $ip_item ?>'><i class="fa-solid fa-trash"></i></a></button>
                </div>
              </td>
            </tr>
          <?php }?>
        </tbody>
      </table>

      <ul class="pagination float-right">
        <?php
        $back = (($currpage == 1) ? '#' : 'all-logos.php?search='. $search .'&page=' . ($currpage - 1));
        $next = (($currpage == $pages) ? 'all-logos.php?search='. $search .'&page=' . $currpage : 'all-logos.php?search='. $search .'&page=' . ($currpage + 1));
        ?>
        <li class="page-item">
          <a class="page-link">
          <?php echo $num; ?> elements
          </a>
        </li>
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == 1) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Previous" href="<?php echo $back; ?>" tabindex="-1"><i class="fa fa-chevron-left" aria-hidden="true"></i></a>
        </li>
        <li class="page-item">
          <a class="page-link">
          <?php echo "".$currpage." of ".$pages.""?>
          <a>
        </li>
        <li class="page-item">
          <a class="page-link" <?php echo ($currpage == $pages) ? "class='disabled'" : ''; ?> data-toggle="tooltip" data-placement="top" title="Next" href="<?php echo $next; ?>"><i class="fa fa-chevron-right" aria-hidden="true"></i></a>
        </li>
      </ul> 
    </div>
  </div>
</div>


<!-- <script type="text/javascript">
  $("#edit-logo").on("submit", (function(e) {

    //tinyMCE.triggerSave();

    e.preventDefault();
    $.ajax({
      url: "<?php //echo $setting['website_url']; ?>/admin/edit-list.php",
      method: "POST",
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
</script> -->


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
            row.find('.editValue.website').html(response.member.website);

            row.find('.editInput.name').val(response.member.name);
            row.find('.editInput.description').val(response.member.description);
            row.find('.editInput.tags').val(response.member.tags);
            row.find('.editInput.website').val(response.member.website);
          }
        }
      });
    });
    //clear msg
    /*$('#clearMsg').click(function(){
      $('#response').hide();
    });*/

    
  });

  /*function fetch(){
    $.ajax({
      method: 'GET',
      url: 'all-logos.php',
      success: function(response){
        $('#tbody').html(response);
      }
    });
  }*/
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