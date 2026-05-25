<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once('system/config-global.php');
require("system/classes/class.search.php");
function clean($string)
{
  return trim(strip_tags(htmlspecialchars($string, ENT_QUOTES, 'UTF-8')));
}
$s = new Search($DB_con);
?>

<?php
$keyword = null;
$count = 0;
//if ($s->get("key")) {
  $keyword = clean($_GET['key']);
  $found   = $s->searchLogos($_POST['page_num'], $keyword);
  $count   = count($found);

if (isset($_POST['page_num'])): ?>
      <div class="container-logo">
        <?php
        if ($s->get("key") && $count > 0) {
            foreach ($found as $row) {
            $str = Product::formatName($row['name']);
            $urlLocal = $setting['website_url'];
            $urlId = $row['id'];
            $urlSlug = $row['slug_lg'];
        ?>

        <div class="logo-row mb-3">
          <div class="cont-img">
            <a href="<?php echo $urlLocal .'/item/'. $urlId .'/'. $urlSlug .'/'?>">
              <img class="card-logotic-logo" width="100" height="100" title="<?php echo $row['name'] ?>" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img'] ?>" alt="<?php echo $row['name'] ?>">

              <?php if ($row['views_off'] == '0') { ?>
              <div class="post-view">
                  <span class="ps-icon fa-light fa-eye"></span>
                  <span><?php echo $row['views']; ?></span>      
              </div>
              <?php } ?>
              <div class="post-download">
                  <span class="ps-icon fa-light fa-arrow-down-to-bracket"></span>
                  <span>
                    <?php $ip_item = $row['id'];
                      $download   = $product->downloadCount($ip_item);
                      echo $download['doCount']; ?>
                  </span>     
              </div>

              <?php if ($row['featured'] == 1) { ?>
              <div class="bb-badge-list">
                <a class="bb-badge badge trending" href="#" title="Featured">
                  <span class="circle circ-yellow">
                    <i class="fa-regular fa-star"></i>
                  </span>
                </a>
              </div>
               <?php } else if ($row['views'] > 999){ ?>
              <div class="bb-badge-list">
                <a class="bb-badge badge trending" href="#" title="Trending">
                  <span class="circle circ-green">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                  </span>
                </a>
              </div>
              <?php } ?>
            </a>
          </div>
        </div>

        <?php 
              }
                } /*else {
                  echo 'No items found';
                }*/
          ?>
      </div>
<?php endif ?>

