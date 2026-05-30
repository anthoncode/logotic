<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once 'system/config-global.php';

if (isset($_POST['offset'])) {
    $newp = $product->popularLogos($_POST['offset']);

    if (empty($newp)) {
        exit();
    }
?>
<div class="container-logo">
    <?php foreach ($newp as $index => $row) {
        $urlLocal = $setting['website_url'];
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
        $download = $product->downloadCount($row['id']);
        $rank = (int)$_POST['offset'] + $index + 1;
    ?>
        <div class="logo-row mb-3">
            <div class="cont-img" style="position:relative;">
                <a href="<?php echo $urlLocal .'/item/'. $urlId .'/'. $urlSlug .'/'; ?>">
                    <img class="card-logotic-logo" style="background:#fff;" width="100" height="100"
                         title="<?php echo $row['name']; ?>"
                         src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img']; ?>"
                         alt="<?php echo $row['name']; ?>">

                    <div class="badge-rank" style="
                        position:absolute;top:6px;left:6px;
                        background:<?php echo $rank <= 3 ? '#f4d03f' : 'rgba(0,0,0,.6)'; ?>;
                        color:<?php echo $rank <= 3 ? '#0d0f1c' : '#fff'; ?>;
                        font-size:.65rem;font-weight:800;
                        border-radius:99px;padding:2px 7px;
                        min-width:22px;text-align:center;">
                        #<?php echo $rank; ?>
                    </div>

                    <div class="badge-download-pill">
                        <span class="fa-regular fa-download"></span>
                        <span><?php echo $product->formatCount($download['doCount']); ?></span>
                    </div>
                </a>
            </div>
        </div>
    <?php } ?>
</div>
<?php } ?>