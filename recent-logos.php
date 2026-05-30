<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once 'system/config-global.php';

if (isset($_POST['page_num'])) {
    $newp = $product->recentLogos($_POST['page_num']);

    if (empty($newp)) {
        exit();
    }

    $now = new DateTime();
?>
<div class="container-logo">
    <?php foreach ($newp as $row) {
        $urlLocal = $setting['website_url'];
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
        $download = $product->downloadCount($row['id']);

        // Calcular si es nuevo (últimos 7 días)
        $created = new DateTime($row['created']);
        $diff = $now->diff($created)->days;
        $isNew = $diff <= 7;
    ?>
        <div class="logo-row mb-3">
            <div class="cont-img" style="position:relative;">
                <a href="<?php echo $urlLocal .'/item/'. $urlId .'/'. $urlSlug .'/'; ?>">
                    <img class="card-logotic-logo" style="background:#fff;" width="100" height="100"
                         title="<?php echo $row['name']; ?>"
                         src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img']; ?>"
                         alt="<?php echo $row['name']; ?>">

                    <!-- Badge NEW o días -->
                    <div style="
                        position:absolute;top:6px;left:6px;
                        background:<?php echo $isNew ? 'var(--accent)' : 'rgba(0,0,0,.6)'; ?>;
                        color:<?php echo $isNew ? '#0d0f1c' : '#fff'; ?>;
                        font-size:.65rem;font-weight:800;
                        border-radius:99px;padding:2px 7px;
                        min-width:22px;text-align:center;">
                        <?php echo $isNew ? 'NEW' : $diff . 'd ago'; ?>
                    </div>

                    <div class="badge-download-pill">
                        <span class="fa-regular fa-download"></span>
                        <span><?php echo $product->formatCount($download['doCount']); ?></span>
                    </div>

                    <?php if ($row['featured'] == 1) { ?>
                        <a class="badge-star-circle" href="#" title="Featured">
                            <span class="circle circ-yellow">
                                <i class="fa-regular fa-star"></i>
                            </span>
                        </a>
                    <?php } ?>
                </a>
            </div>
        </div>
    <?php } ?>
</div>
<?php } ?>