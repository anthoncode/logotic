<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Search Results';
$metaRobots = "<meta name='robots' content='noindex, nofollow' />";
require_once('system/config-global.php');
require_once('system/assets/header.php');
require("system/classes/class.search.php");
function clean($string)
{
  return trim(strip_tags(htmlspecialchars($string, ENT_QUOTES, 'UTF-8')));
}
$s = new Search($DB_con);
?>
<main role="main">

  <?php
  $keyword = null;
  $count = 0;
  if ($s->get("key")) {
    $keyword = strip_tags(trim($_GET['key']));
    $keyword = substr($keyword, 0, 20);
    $found   = $s->search($keyword);
    $count   = count($found);
  }
  ?>
  <!-- Masthead -->
  <header class="masthead text-white text-center mb-3 mt-0 rounded-0 box-shadow">
    <div class="overlay rounded-0 box-shadow"></div>
    <div class="container">
      <div class="row">
        <div class="col-xl-9 mx-auto">
          <h1 class="mb-1 font-weight-bold"><?php echo $count; ?> <?php echo $l['results_for'] ?> [<?php echo $keyword; ?>]</h1>
        </div>
      </div>
    </div>
  </header>
  <br>

  <div class="container">
    <div class="row p-15">
      <div class="col-lg-3 mb-3">
        <div class="card box-shadow">
          <div class="card-header font-weight-bold bg-light">
            <?php echo $l['all_category'] ?>
          </div>

          <div class="list-group bg-light">
            <?php
            $category = $product->get_categories();
            foreach ($category as $cat) {
            ?>
              <a href="<?php echo $setting['website_url']; ?>/category/<?php echo $cat['id']; ?>/" class="list-group-item list-group-item-action">
                <span class="ml-2 font-weight-bold"><i class="fa-solid fa-folders"></i> <?php echo $cat['name']; ?></span>
              </a>
            <?php } ?>
          </div>
        </div>
      </div>
      <!-- /.col-lg-3 -->
      <script type="text/javascript">
               $(document).ready(function() {
                   var page_num = 1;
                   load_page_5(page_num, false);

                   var lastScrollTop = 0;
                   $(window).scroll(function(event){
                      var st = $(this).scrollTop();
                      if (st > lastScrollTop){
                          // downscroll code
                           page_num++;
                           load_page_5(page_num, false)
                      } else {
                         // upscroll code
                      }
                      lastScrollTop = st;
                   });

               });

               function load_page_5(page_num, loading) {
                   if (loading == false) {
                       loading = true;
                       $.ajax({
                           url: "<?php echo $setting['website_url']; ?>" + "/search-logo.php?key=" + "<?php echo $keyword; ?>", //url de paginación infinita
                           type: "post",
                           data: {
                               page_num: page_num
                           },
                           beforeSend: function() {
                               $('#ajax-loader-se').show();
                               //alert(window.location.href + 'logo-post.php');
                               return;
                           }
                       }).done(function(data) {
                           $('#ajax-loader-se').hide();
                           loading = false;
                           $("#dynamic-posts3").append(data);

                       }).fail(function(jqXHR, ajaxOptions, thrownError) {
                           $('#ajax-loader-se').hide();
                       });
                   }
               }
             </script>
             
             <div class="col-lg-9">
               <div id="dynamic-posts3"></div>
               <div id="ajax-loader-se">
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