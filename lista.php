<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('display_errors', true);
error_reporting(E_ALL);

require_once('system/config-global.php');
  require_once('system/assets/header.php');

if (isset($_GET['id'])) {
  $id = $_GET['id'];

  $catpro = $product->getCSProducts(null, $_GET['id']);
  //$scatname = $product->scatdetails($_GET['id']);
  //$catname = $product->catdetails($scatname['cat_id']);

  //$pageTitle = $scatname['name'];


  if ($catpro) {
  } else {
    display_post_not_found($id);
    exit();
  }

?>
  <main role="main">

    <div class="container">
      <div class="row p-15">
        <!-- /.col-lg-3 -->
        <!-- <div class="col-lg-9"> -->

        <script type="text/javascript">
        	$(document).ready(function() {
        	    var page_num = 1;
        	    load_page_2(page_num, false);

        	    $(window).scroll(function() {
        	        if ($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
        	            page_num++;
        	            load_page_2(page_num, false)
        	        }

        	    });

        	});

        	function load_page_2(page_num, loading) {
        	    if (loading == false) {
        	        loading = true;
        	        $.ajax({
        	            url: 'logo-post.php', //url de paginación infinita
        	            type: "post",
        	            data: {
        	                page_num: page_num
        	            },
        	            beforeSend: function() {
        	                $('#ajax-loader').show();
        	                //alert(window.location.href + 'logo-post.php');
        	                return;
        	            }
        	        }).done(function(data) {
        	            $('#ajax-loader').hide();
        	            loading = false;
        	            $("#dynamic-posts3").append(data);
        	            //alert($("#dynamic-posts2").append(data));
        	        }).fail(function(jqXHR, ajaxOptions, thrownError) {
        	            $('#ajax-loader').hide();
        	        });

        	    }

        	}
        </script>

	<div id="dynamic-posts3"></div>
        <div id="ajax-loader0">
          <p>Please wait..!</p>
      </div>

    	<!-- </div>  --> 
        <!-- /.col-lg-9 -->
      </div>
    </div>


  </main>
  <?php

  require_once('system/assets/footer.php');
  ?>
<?php
} else {
  header('Location:index.php');
}


