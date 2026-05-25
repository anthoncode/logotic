<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
require_once 'system/config-global.php';
?>

<?php 
$tags = $_GET['id'];
$tags_free = str_replace('-', ' ', $tags);

if (isset($_POST['page_num'])): ?>
    <?php $newp = $product->tagLogos($_POST['page_num'], $tags_free) ?>
      <div class="container-logo">
        <?php
        foreach ($newp as $row) {
        $str = Product::formatName($row['name']);
        $urlLocal = $setting['website_url'];
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg']; 
        ?>
        <div class="logo-row mb-3">
              <div class="cont-img">
                <a href="<?php echo $urlLocal .'/item/'. $urlId .'/'. $urlSlug .'/'?>">
                  <img class="card-logotic-logo" width="100" height="100" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img'] ?>" alt="<?php echo $row['name'] ?>" title="<?php echo $row['name'] ?>">
                  <?php if ($row['views_off'] == '0') {?>
                  <div class="post-view">
                    <span class="ps-icon fa-light fa-eye"></span>
                    <span><?php echo $row['views']; ?></span>      
                  </div>
                  <?php } else { }?>
                  <div class="post-download">
                    <span class="ps-icon fa-light fa-arrow-down-to-bracket"></span>
                    <span>
                      <?php $ip_item = $row['id'];
                          $download  = $product->downloadCount($ip_item);
                          echo $download['doCount'];
                      ?>
                    </span>     
                  </div>
                </a>
              </div>
            </div>
    <?php } ?>
</div>
<?php endif ?>

