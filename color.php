<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


require_once('system/config-global.php');
$color = rtrim($_GET['id'], '/');
if (isset($_GET['id'])) {
    $color = $_GET['id'];

    $colorLabels = [
        'red'    => 'Red',
        'blue'   => 'Blue',
        'green'  => 'Green',
        'yellow' => 'Yellow',
        'orange' => 'Orange',
        'black'  => 'Black',
        'white'  => 'White',
        'purple' => 'Purple',
        'pink'   => 'Pink',
        'cyan'   => 'Cyan',
    ];

    $colorLabel = $colorLabels[$color] ?? ucfirst($color);

    $metaRobots = "<meta name='robots' content='noindex, nofollow' />\n";
    $pageTitle = $colorLabel . " Logos - PNG & SVG";
    $pageMeta = "Download " . $colorLabel . " logos in SVG and PNG format with transparent background";

    require_once('system/assets/header.php');
?>

<main role="main">
  <header class="bg-dark text-light text-left mb-3 mt-0 p-4 rounded-0 box-shadow">
    <div class="overlay rounded-0 box-shadow"></div>
    <div class="container">
      <h1 class="mb-1 font-weight-light p-15">
        <span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:<?php
          $dots = [
            'red'=>'#e63946','blue'=>'#1d7af3','green'=>'#2dc653',
            'yellow'=>'#f4d03f','orange'=>'#f18d35','black'=>'#111',
            'white'=>'#fff','purple'=>'#8b5cf6','pink'=>'#ec4899','cyan'=>'#06b6d4'
          ];
          echo $dots[$color] ?? '#888';
        ?>;vertical-align:middle;margin-right:8px;border:2px solid rgba(255,255,255,.2);"></span>
        <?php echo $colorLabel; ?> Logos
      </h1>
    </div>
  </header>
  <br>

  <div class="container">
    <div class="row p-15">
      <?php require_once 'system/assets/sidebar.php'; ?>

      <div class="col-lg-9">
        <div id="dynamic-posts3"></div>
        <div id="ajax-loader-color" style="display:none;">
          <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif"?>" width="30px" style="display: block; margin: 0px auto;">
        </div>
        <div style="text-align: center; margin: 1.5rem 0;">
          <button id="load-more-color" class="btn-upload" style="display:none; margin: 0 auto;">
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

    load_page_color(page_num);

    $(document).on('click', '#load-more-color', function() {
      if (loading || no_more) return;
      page_num++;
      load_page_color(page_num);
    });

    function load_page_color(page_num) {
      loading = true;
      $('#load-more-color').hide();
      $('#ajax-loader-color').show();

      $.ajax({
        url: "<?php echo $setting['website_url']; ?>/color-logos.php?id=<?php echo $color; ?>",
        type: "post",
        data: { page_num: page_num }
      }).done(function(data) {
        loading = false;
        $('#ajax-loader-color').hide();

        if ($.trim(data) === '') {
          no_more = true;
          $('#load-more-color').hide();
        } else {
          $("#dynamic-posts3").append(data);
          $('#load-more-color').show();
        }
      }).fail(function() {
        loading = false;
        $('#ajax-loader-color').hide();
        $('#load-more-color').show();
      });
    }
  });
</script>

<?php
    require_once('system/assets/footer.php');
} else {
    header('Location:index.php');
}
?>