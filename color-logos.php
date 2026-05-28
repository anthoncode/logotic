<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once 'system/config-global.php';


$color = $_GET['id'];
$color = rtrim($_GET['id'], '/');
if (isset($_POST['page_num'])) {
    $newp = $product->getByColor($color, $_POST['page_num']);

    // DEBUG temporal
       // echo "Color: " . $color . " | Page: " . $_POST['page_num'] . " | Results: " . count($newp);
       // var_dump($newp);
       // exit();

    if (empty($newp)) {
        exit();
    }
?>

hols
<div class="container-logo">
    <?php foreach ($newp as $row) {
        $urlLocal = $setting['website_url'];
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
    ?>
        <div class="logo-row mb-3">
            <div class="cont-img">
                <a href="<?php echo $urlLocal .'/item/'. $urlId .'/'. $urlSlug .'/'; ?>">
                    <img class="card-logotic-logo" style="background:#fff;" width="100" height="100"
                         title="<?php echo $row['name']; ?>"
                         src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img']; ?>"
                         alt="<?php echo $row['name']; ?>">

                    <div class="badge-download-pill">
                        <span class="fa-regular fa-download"></span>
                        <span>
                            <?php
                            $download = $product->downloadCount($row['id']);
                            echo $product->formatCount($download['doCount']);
                            ?>
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