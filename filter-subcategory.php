<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once('system/config-global.php');

$catId = isset($_GET['cat']) ? intval($_GET['cat']) : 0;

if ($catId <= 0) {
    exit();
}

// Logos destacados (featured) de esta subcategoría — rápido, sin JOIN a downloads
$stmt = $DB_con->prepare("
    SELECT id, name, slug_lg, icon_img, featured, views
    FROM " . PFX . "products
    WHERE subc_id = :cat AND active = 1 AND featured = 1
    ORDER BY id DESC
    LIMIT 20
");
$stmt->execute([':cat' => $catId]);
$logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($logos)) {
    echo '<div style="grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-muted);">No logos in this category yet.</div>';
    exit();
}

foreach ($logos as $row) {
    $urlLocal = $setting['website_url'];
    $urlId    = $row['id'];
    $urlSlug  = $row['slug_lg'];
    $download = $product->downloadCount($row['id']);
?>
    <div class="logo-row mb-3">
        <div class="cont-img">
            <a href="<?php echo $urlLocal . '/item/' . $urlId . '/' . $urlSlug . '/'; ?>">
                <img class="card-logotic-logo" style="background:#fff;" width="100" height="100" title="<?php echo htmlspecialchars($row['name']); ?>" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">

                <div class="badge-download-pill">
                    <span class="fa-regular fa-download"></span>
                    <span><?php echo $product->formatCount($download['doCount']); ?></span>
                </div>

                <?php if ($row['featured'] == 1) { ?>
                    <a class="badge-star-circle" href="#" title="Featured">
                        <span class="circle circ-yellow"><i class="fa-regular fa-star"></i></span>
                    </a>
                <?php } else if ($row['views'] > 999) { ?>
                    <a class="badge-star-circle" href="#" title="Trending +1000">
                        <span class="circle circ-green"><i class="fa-solid fa-arrow-trend-up"></i></span>
                    </a>
                <?php } ?>
            </a>
        </div>
    </div>
<?php
}