<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
require_once('system/config-global.php');

$period = $_POST['period'] ?? 'month';
$offset = isset($_POST['offset']) ? max(0, intval($_POST['offset'])) : 0;
$perPage = 24;

// No pasar de 100 en total
if ($offset >= 100) {
    exit();
}

// Dias segun el periodo
$daysMap = [
    'year'  => 365,
    'month' => 30,
    'day'   => 1,
];
$days = $daysMap[$period] ?? 30;

// Logos mas descargados en el periodo
$stmt = $DB_con->prepare("
    SELECT p.id, p.name, p.slug_lg, p.icon_img, p.featured, p.views,
           COUNT(d.id) AS period_dl
    FROM " . PFX . "products p
    INNER JOIN " . PFX . "downloads d
        ON p.id = d.products_id
        AND d.date_created >= (CURDATE() - INTERVAL :days DAY)
    WHERE p.status = 'approved'
    GROUP BY p.id
    ORDER BY period_dl DESC, p.views DESC
    LIMIT $offset, $perPage
");
$stmt->bindValue(':days', $days, PDO::PARAM_INT);
$stmt->execute();
$newp = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($newp)) {
    exit();
}
?>
<div class="container-logo">
    <?php foreach ($newp as $index => $row) {
        $urlLocal = $setting['website_url'];
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
        $rank = $offset + $index + 1;
    ?>
        <div class="logo-row mb-3">
            <div class="cont-img" style="position:relative;">
                <a href="<?php echo $urlLocal . '/item/' . $urlId . '/' . $urlSlug . '/'; ?>">
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
                        <span><?php echo $product->formatCount($row['period_dl']); ?></span>
                    </div>
                </a>
            </div>
        </div>
    <?php } ?>
</div>