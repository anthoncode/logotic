<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('system/config-global.php');

if (isset($_GET['id'])) {
  $tag_item = $_GET['id'];
  $tag_w_slash = str_replace('/', ' ', $tag_item);
  $tags = product::formatName($tag_w_slash);
  $tags_free = str_replace('-', ' ', $tags);

  $tagPro = $product->getTProducts(null, $_GET['id']);

  // ── Si el tag no existe o no tiene logos → 404 real ──
  if (empty($tagPro)) {
    http_response_code(404);
    include '404.php';
    exit;
  }

  $metaRobots = "<meta name='robots' content='noindex, nofollow' />\n";
  $UperTag = strtoupper($tags_free);
  $pageTitle = $UperTag . " logo versions and variants" . " - PNG & SVG";
  $pageMeta = "Multiple " . $UperTag . " logo designs, download old and new versions of " . $UperTag . " icons and logos with transparent background";
  require_once('system/assets/header.php');
  

  if (!$tag_w_slash) {
    display_post_not_found($tags);
    exit();
  }
?>

<main role="main">

  <header class="bg-dark text-light text-left mb-3 mt-0 p-4 rounded-0 box-shadow">
    <div class="overlay rounded-0 box-shadow"></div>
    <div class="container">
      <h1 class="mb-1 font-weight-light p-15"><?php echo $tags_free; ?></h1>
    </div>
  </header>
  <br>

  <div class="container">
    <div class="row p-15">
      <?php require_once 'system/assets/sidebar.php'; ?>

      <div class="col-lg-9">
        <div id="dynamic-posts3"></div>
        <div id="ajax-loader-tag" style="display:none;">
          <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif" ?>" width="30px" style="display: block; margin: 0px auto;">
        </div>
        <div style="text-align: center; margin: 1.5rem 0;">
          <button id="load-more-tag" class="btn-upload" style="display:none; margin: 0 auto;">
            <i class="fa-regular fa-arrow-down"></i> Load more
          </button>
        </div>
      </div>

    </div>
  </div>

</main>

<script type="text/javascript">
  $(document).ready(function() {
    var page_num = 1;
    var loading = false;
    var no_more = false;

    load_page_4(page_num);

    $(document).on('click', '#load-more-tag', function() {
      if (loading || no_more) return;
      page_num++;
      load_page_4(page_num);
    });

    function load_page_4(page_num) {
      loading = true;
      $('#load-more-tag').hide();
      $('#ajax-loader-tag').show();

      $.ajax({
        url: "<?php echo $setting['website_url']; ?>/tag-logos.php?id=<?php echo $tags; ?>",
        type: "post",
        data: { page_num: page_num }
      }).done(function(data) {
        loading = false;
        $('#ajax-loader-tag').hide();

        if ($.trim(data) === '') {
          no_more = true;
          $('#load-more-tag').hide();
        } else {
          $("#dynamic-posts3").append(data);
          $('#load-more-tag').show();
        }
      }).fail(function() {
        loading = false;
        $('#ajax-loader-tag').hide();
        $('#load-more-tag').show();
      });
    }
  });
</script>

<?php
  require_once('system/assets/footer.php');
} else {
  header('Location:index.php');
}

function display_post_not_found($id)
{
  global $l;
  echo "<h2 class='no-item'> " . $l['notitemtags'] . " </h2>";
}
?>