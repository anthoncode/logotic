<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ob_start(); //para que redireccione header location
require_once('system/config-global.php');


if (isset($_GET['id'])) {
  $id = $_GET['id'];

  $catpro = $product->getCSProducts(null, $_GET['id']);
  $scatname = $product->scatdetails($_GET['id']);
  $catname = $product->catdetails($scatname['cat_id']);

  $metaRobots = "<meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
";
  $pageTitle = "Download " . $scatname['name'] . " " . $catname['name'];
  $pageMeta = "Get " . $scatname['name'] . " logos in PNG and SVG, they are high quality images with a transparent background";
  require_once('system/assets/header.php');

  //redireccionar a subcategor��a
  if (is_array($scatname)) {
    $idsubcat = $scatname['id'];
    $nmsubcat = Product::formatName($scatname['name']);
    $slug_item_sub = $setting['website_url'] . '/subcat/' . $idsubcat . '/' . $nmsubcat . "/";
  }

  if (isset($scatname['name'])) {
    $linkSub = $idsubcat . '/' . $nmsubcat . "/"; // id/slug
    $iddSub = $id . "/";
    if ($iddSub != $linkSub) {
      header("Location: $slug_item_sub");
      //header("Location: http://www.google.es");
      echo $slug_item_sub . "</br>";
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
        <h1 class="mb-1 font-weight-light p-15"><?php echo $scatname['name']; ?></h1>
      </div>
    </header>
    <br>

    <div class="container">
      <div class="row p-15">
        <?php require_once 'system/assets/sidebar.php'; ?>


        <!-- /.col-lg-3 -->
        <!-- <div class="col-lg-9"> -->

        <script type="text/javascript">
          $(document).ready(function() {
            var page_num = 1;
            var loading = false;
            var no_more = false;

            load_page_2(page_num);

            $(document).on('click', '#load-more-sub', function() {
              if (loading || no_more) return;
              page_num++;
              load_page_2(page_num);
            });

            function load_page_2(page_num) {
              loading = true;
              $('#load-more-sub').hide();
              $('#ajax-loader-sub').show();

              $.ajax({
                url: "<?php echo $setting['website_url']; ?>/subcat-logos.php?id=<?php echo $id; ?>",
                type: "post",
                data: {
                  page_num: page_num
                }
              }).done(function(data) {
                loading = false;
                $('#ajax-loader-sub').hide();

                if ($.trim(data) === '') {
                  no_more = true;
                  $('#load-more-sub').hide();
                } else {
                  $("#dynamic-posts3").append(data);
                  $('#load-more-sub').show();
                }
              }).fail(function() {
                loading = false;
                $('#ajax-loader-sub').hide();
                $('#load-more-sub').show();
              });
            }
          });
        </script>

        <div class="col-md-9">
          <div id="dynamic-posts3"></div>
          <div id="ajax-loader-sub" style="display:none;">
            <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif" ?>" width="30px" style="display: block; margin: 0px auto;">
          </div>
          <div style="text-align: center; margin: 1.5rem 0;">
            <button id="load-more-sub" class="btn-upload" style="display:none; margin: 0 auto;">
              <i class="fa-regular fa-arrow-down"></i> Load more
            </button>
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
  echo "<h2 class='no-item'> " . $l['noitemsinsubcategory'] . " </h2>";
  /*echo "<div class='go-back'><a href=''> Go back</a></div>";
    echo $setting['website_url'];*/
}
