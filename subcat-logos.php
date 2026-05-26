<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
require_once 'system/config-global.php';

$id = $_GET['id'];

if (isset($_POST['page_num'])) {
  $newp = $product->subcateLogos($_POST['page_num'], null, $_GET['id']);

  if (empty($newp)) {
    exit();
  }
?>
  <div class="container-logo">
    <?php foreach ($newp as $row) {
      $str = Product::formatName($row['name']);
      $urlLocal = $setting['website_url'];
      $urlId = $row['id'];
      $urlSlug = $row['slug_lg'];
    ?>
      <div class="logo-row mb-3">
        <div class="cont-img">
          <a href="<?php echo $urlLocal . '/item/' . $urlId . '/' . $urlSlug . '/' ?>">
            <img class="card-logotic-logo" style="background:#fff;" width="100" height="100" title="<?php echo $row['name'] ?>" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img'] ?>" alt="<?php echo $row['name'] ?>">

            <div class="badge-download-pill">
              <span class="fa-regular fa-download"></span>
              <span>
                <?php $ip_item = $row['id'];
                $download = $product->downloadCount($ip_item);
                echo $product->formatCount($download['doCount']); ?>
              </span>
            </div>

            <?php if ($row['featured'] == 1) { ?>
              <a class="badge-star-circle" href="#" title="Featured">
                <span class="circle circ-yellow">
                  <i class="fa-regular fa-star"></i>
                </span>
              </a>
            <?php } else if ($row['views'] > 999) { ?>
              <a class="badge-star-circle" href="#" title="Trending +1000">
                <span class="circle circ-green">
                  <i class="fa-solid fa-arrow-trend-up"></i>
                </span>
              </a>
            <?php } ?>
          </a>
        </div>
      </div>
    <?php } ?>
  </div>
<?php } ?>