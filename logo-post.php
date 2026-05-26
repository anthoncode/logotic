<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
require_once 'system/config-global.php';

if (isset($_POST['page_num'])) {
    $newp = $product->posts($_POST['page_num']);

    if (empty($newp)) {
        exit(); // ← detiene el load more
    }
?>
<!-- new items -->
<style>
  .container-logo{
    width: 100%;
    height: auto;
    display: flex;
    flex-direction : row;
    justify-content : space-around;
    flex-flow : wrap;
  }

  @media screen and (max-width:1199px) {
    /*.cont-img{
      background-color: #f34f55;
    }*/
    .logo-row {
      padding-right: 1px;
      padding-left: 1px;
      font-size: 9px;
    }
  }
  @media screen and (max-width:767px) {
    /*.cont-img{
      background-color: #1083ff;
    }*/
    .logo-row {
      padding-right: 2px;
      padding-left: 2px;
      font-size: 9px;
    }
  }
</style>
<div class="container-logo">
    <?php foreach ($newp as $row) {
        $urlLocal = $setting['website_url'];
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
    ?>
        <div class="logo-row mb-3">
            <div class="cont-img">
                <a href="<?php echo $urlLocal .'/item/'. $urlId .'/'. $urlSlug .'/'?>">
                    <img class="card-logotic-logo" style="background:#fff;" width="100" height="100" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img'] ?>" alt="<?php echo $row['name'] ?>" title="<?php echo $row['name'] ?>">

                    <div class="badge-download-pill">
                        <span class="fa-regular fa-download"></span>
                        <span>
                            <?php $ip_item = $row['id'];
                            $download = $product->downloadCount($ip_item);
                            echo $product->formatCount($download['doCount']); ?>
                        </span>
                    </div>

                    <?php if ($row['featured'] == 1) { ?>
                        <a class="bb-badge badge trending" href="#" title="Featured">
                            <span class="circle circ-yellow">
                                <i class="fa-regular fa-star"></i>
                            </span>
                        </a>
                    <?php } else if ($row['views'] > 999) { ?>
                        <a class="bb-badge badge trending" href="#" title="Trending">
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


