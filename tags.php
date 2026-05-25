<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('system/config-global.php');

if (isset($_GET['id'])) {
  $tag_item = $_GET['id'];
  $tag_w_slash = str_replace('/', ' ', $tag_item); //quita el "/" al final
  $tags = product::formatName($tag_w_slash); //tag con con guion y sanitizado
  $tags_free = str_replace('-', ' ', $tags); //tag SIN guion en espacios
  //$tags = $_GET['tags'];
  //$details = $pages->details($id);

  $tagPro = $product->getTProducts(null, $_GET['id']);
  $catname = $product->tagdetailsCate();
  //$catname = $product->catdetails($_GET['id']);
  //echo $tags;
  //$pageTitle = $tagPro['tags'];
  $metaRobots = "<meta name='robots' content='noindex, nofollow' />
";
  $UperTag = strtoupper($tags_free);
  $pageTitle = $UperTag . " logo versions and variants" . " - PNG & SVG";
  $pageMeta = "Multiple " .$UperTag. " logo designs, download old and new versions of " .$UperTag. " icons and logos with transparent background";
  require_once('system/assets/header.php');
  if ($tag_w_slash) {

  } else {
    display_post_not_found($tags);
    exit();
  }
?>

  <main role="main">

    <!-- Masthead -->
    <header class="bg-light text-dark text-left mb-3 mt-0 p-4 rounded-0 box-shadow">

      <div class="overlay rounded-0 box-shadow"></div>
      <div class="container">
        <h1 class="mb-1 font-weight-light p-15"><?php echo $tags_free; ?></h1>
      </div>
    </header>
    <br>


    <div class="container">
      <div class="row p-15">
        <?php require_once 'system/assets/sidebar.php'; ?>
        

        <script type="text/javascript">
          $(document).ready(function() {
              var page_num = 1;
              load_page_4(page_num, false);

              var lastScrollTop = 0;
              $(window).scroll(function(event){
                 var st = $(this).scrollTop();
                 if (st > lastScrollTop){
                     // downscroll code
                      page_num++;
                      load_page_4(page_num, false)
                 } else {
                    // upscroll code
                 }
                 lastScrollTop = st;
              });

          });

          function load_page_4(page_num, loading) {
              if (loading == false) {
                  loading = true;
                  $.ajax({
                      url: "<?php echo $setting['website_url']; ?>" + "/tag-logos.php?id=" + "<?php echo $tags; ?>", //url de paginación infinita
                      type: "post",
                      data: {
                          page_num: page_num
                      },
                      beforeSend: function() {
                          $('#ajax-loader-tag').show();
                          //alert(window.location.href + 'logo-post.php');
                          return;
                      }
                  }).done(function(data) {
                      $('#ajax-loader-tag').hide();
                      loading = false;
                      $("#dynamic-posts3").append(data);
                      //alert('http://localhost/digiclass/tag-logos.php?id=' + '<?php echo $tags;?>');
                  }).fail(function(jqXHR, ajaxOptions, thrownError) {
                      $('#ajax-loader-tag').hide();
                  });
              }
          }
        </script>
        
        <div class="col-lg-9">
          <div id="dynamic-posts3"></div>
          <div id="ajax-loader-tag">
            <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif"?>" width="30px" style="display: block; margin: 0px auto;">
          </div>
        </div>


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
  echo "<h2 class='no-item'> " . $l['notitemtags'] . " </h2>";
}
