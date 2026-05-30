<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once('system/config-global.php');

$metaRobots = "<meta name='robots' content='index, follow' />\n";
$pageTitle = "Recently Added Logos - Latest SVG & PNG";
$pageMeta = "Browse the latest logos added to Logotic. Free SVG and PNG downloads with transparent background.";

require_once('system/assets/header.php');
?>

<main role="main">
  <header class="bg-dark text-light text-left mb-3 mt-0 p-4 rounded-0 box-shadow">
    <div class="overlay rounded-0 box-shadow"></div>
    <div class="container">
      <h1 class="mb-1 font-weight-light p-15">
        <i class="fa-regular fa-clock" style="color:var(--accent);"></i> Recently Added Logos
      </h1>
      <p style="color:var(--text-secondary);font-size:.9rem;margin:0;">The latest logos added to our collection</p>
    </div>
  </header>
  <br>

  <div class="container">
    <div class="row p-15">
      <?php require_once 'system/assets/sidebar.php'; ?>

      <div class="col-lg-9">
        <div id="dynamic-posts3"></div>
        <div id="ajax-loader-recent" style="display:none;">
          <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif"?>" width="30px" style="display: block; margin: 0px auto;">
        </div>
        <div style="text-align: center; margin: 1.5rem 0;">
          <button id="load-more-recent" class="btn-upload" style="display:none; margin: 0 auto;">
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

    load_recent(page_num);

    $(document).on('click', '#load-more-recent', function() {
      if (loading || no_more) return;
      page_num++;
      load_recent(page_num);
    });

    function load_recent(page_num) {
      loading = true;
      $('#load-more-recent').hide();
      $('#ajax-loader-recent').show();

      $.ajax({
        url: "<?php echo $setting['website_url']; ?>/recent-logos.php",
        type: "post",
        data: { page_num: page_num }
      }).done(function(data) {
        loading = false;
        $('#ajax-loader-recent').hide();

        if ($.trim(data) === '') {
          no_more = true;
          $('#load-more-recent').hide();
        } else {
          $("#dynamic-posts3").append(data);
          $('#load-more-recent').show();
        }
      }).fail(function() {
        loading = false;
        $('#ajax-loader-recent').hide();
        $('#load-more-recent').show();
      });
    }
  });
</script>

<?php require_once('system/assets/footer.php'); ?>