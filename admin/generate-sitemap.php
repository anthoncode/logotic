<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Generate Sitemap";
require_once('../system/config-admin.php');
require_once('../system/gateways.php');
require_once('includes/header1.php');

$allProd = $product->getAllProducts();

$xml = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

foreach ($allProd as $row) {
    $cleanDate = date('c', time());
    $urlId = $row['id'];
    $urlSlug = $row['slug_lg'];

    $xml .= '
    <url>
        <loc>' . $setting['website_url'] . '/item/' . $urlId . '/' . $urlSlug . '/</loc>
        <lastmod>' . $cleanDate . '</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.8</priority>
    </url>';
}

$xml .= '
</urlset>';

$path = dirname(__DIR__) . '/sitemap.xml';
$success = file_put_contents($path, $xml) !== false;
$total = count($allProd);
?>

<div class="content">
    <div class="module box-shadow" style="max-width:500px; margin: 40px auto; padding: 30px; text-align:center;">
        <?php if ($success): ?>
            <i class="fa-solid fa-circle-check" style="font-size:3rem; color:#28a745;"></i>
            <h3 style="margin-top:15px;">Sitemap generated!</h3>
            <p><?php echo $total; ?> URLs written to <code>sitemap.xml</code></p>
            <p style="color:#888; font-size:.85rem;">
                <?php echo date('d M Y H:i:s'); ?>
            </p>
        <?php else: ?>
            <i class="fa-solid fa-circle-xmark" style="font-size:3rem; color:#dc3545;"></i>
            <h3 style="margin-top:15px;">Error generating sitemap</h3>
            <p>Check write permissions on <code>sitemap.xml</code></p>
        <?php endif; ?>
        <a href="<?php echo $setting['website_url']; ?>/admin/index.php"
           class="btn btn-outline-primary mt-3">
            ← Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>