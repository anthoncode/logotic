<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ob_start();//para que redireccione header location
require_once('system/config-global.php');


if (isset($_GET['id'])) {
  $id = $_GET['id'];

  $catpro = $product->getCProducts(null, $_GET['id']);
  $catname = $product->catdetails($_GET['id']);//obtiene el array de categoria
  
  $metaRobots = "<meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
";
  $pageTitle = "Download " . $catname['name'] . " PNG and SVG";
  $pageMeta = "Download " . $catname['name'] . ", they are high quality vector images, thousands of new designs editable for personal and commercial use";
  require_once('system/assets/header.php');


  //redireccionar a categor¨ªa
  if(is_array($catname)){
  $idcat = $catname['id'];
  $nmcat = Product::formatName($catname['name']);
  $slug_item = $setting['website_url'] . '/category/' . $idcat . '/' . $nmcat ."/";
  }

  if (isset($catname['name'])) {
    $link = $idcat . '/' . $nmcat."/"; // id/slug
    $idd = $id . "/";
    if ($idd != $link) {
      header("Location: $slug_item");
      //echo $slug_item. "</br>";
      //echo $idd. "</br>";
      //echo $link. "</br>";
    }
  }


  if ($catpro) {
  } else {
    display_post_not_found($id);
    exit();
  }
?>
  <main role="main">
    <!-- Masthead -->
    <header class="bg-light text-dark text-left mb-3 mt-0 p-4 rounded-0 box-shadow">

      <div class="overlay rounded-0 box-shadow"></div>
      <div class="container">
        <h1 class="mb-1 font-weight-light p-15"><?php echo $catname['name']; ?></h1>
      </div>
    </header>
    <br>
    <div class="container">
      <div class="row p-15">

        <?php require_once 'system/assets/sidebar.php'; ?>

        <!-- /.col-lg-3 -->
        <script type="text/javascript">
          $(document).ready(function() {
              var page_num = 1;
              load_page_3(page_num, false);

              var lastScrollTop = 0;
              $(window).scroll(function(event){
                 var st = $(this).scrollTop();
                 if (st > lastScrollTop){
                     // downscroll code
                      page_num++;
                      load_page_3(page_num, false)
                 } else {
                    // upscroll code
                 }
                 lastScrollTop = st;
              });

          });

          function load_page_3(page_num, loading) {
              if (loading == false) {
                  loading = true;
                  $.ajax({
                      url: "<?php echo $setting['website_url']; ?>" + "/cat-logos.php?id=" + "<?php echo $id; ?>", //url de paginaci¨®n infinita
                      type: "post",
                      data: {
                          page_num: page_num
                      },
                      beforeSend: function() {
                          $('#ajax-loader-cat').show();
                          //alert(window.location.href + 'logo-post.php');
                          return;
                      }
                  }).done(function(data) {
                      $('#ajax-loader-cat').hide();
                      loading = false;
                      $("#dynamic-posts3").append(data);
                      //alert($("#dynamic-posts2").append(data));
                  }).fail(function(jqXHR, ajaxOptions, thrownError) {
                      $('#ajax-loader-cat').hide();
                  });
              }
          }
        </script>
        
        <div class="col-lg-9">
          <div id="dynamic-posts3"></div>
          <div id="ajax-loader-cat">
            <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif"?>" width="30px" style="display: block; margin: 0px auto;">
          </div>
        </div>
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

function display_post_not_found($id)
{
  global $l;
  echo $l['noitemsincategory'];
}
