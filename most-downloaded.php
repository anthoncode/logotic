<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('system/config-global.php');

$metaRobots = "<meta name='robots' content='index, follow' />\n";
$pageTitle = "Most Downloaded Logos - Top 100 SVG & PNG";
$pageMeta = "The 100 most downloaded logos on Logotic. Free SVG and PNG downloads with transparent background.";

require_once('system/assets/header.php');
?>

<main role="main">
  <header class="bg-dark text-light text-left mb-3 mt-0 p-4 rounded-0 box-shadow">
    <div class="overlay rounded-0 box-shadow"></div>
    <div class="container">
      <h1 class="mb-1 font-weight-light p-15">
        <i class="fa-solid fa-fire" style="color:#f18d35;"></i> Most Downloaded Logos
      </h1>
      <p style="color:var(--text-secondary);font-size:.9rem;margin:0;">Top 100 logos ranked by total downloads</p>
    </div>
  </header>
  <br>

  <div class="container">
    <div class="row p-15">
      <?php require_once 'system/assets/sidebar.php'; ?>

      <div class="col-lg-9">
        <div id="dynamic-posts3"></div>
        <div id="ajax-loader-popular" style="display:none;">
          <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif" ?>" width="30px" style="display: block; margin: 0px auto;">
        </div>
        <div style="text-align: center; margin: 1.5rem 0;">
          <button id="load-more-popular" class="btn-upload" style="display:none; margin: 0 auto;">
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

    var offset = 0;

    load_popular(page_num);

    $(document).on('click', '#load-more-popular', function() {
      if (loading || no_more) return;
      page_num++;
      load_popular(page_num);
    });



    function load_popular(page_num) {
      loading = true;
      $('#load-more-popular').hide();
      $('#ajax-loader-popular').show();

      $.ajax({
        url: "<?php echo $setting['website_url']; ?>/popular-logos.php",
        type: "post",
        data: {
          page_num: page_num,
          offset: offset
        }
      }).done(function(data) {
        loading = false;
        $('#ajax-loader-popular').hide();

        if ($.trim(data) === '') {
          no_more = true;
          $('#load-more-popular').hide();
        } else {
          $("#dynamic-posts3").append(data);
          offset += 24;
          if (offset >= 100) {
            no_more = true;
            $('#load-more-popular').hide();
          } else {
            $('#load-more-popular').show();
          }
        }
      }).fail(function() {
        loading = false;
        $('#ajax-loader-popular').hide();
        $('#load-more-popular').show();
      });
    }
  });
</script>

<?php require_once('system/assets/footer.php'); ?>