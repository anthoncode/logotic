<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Analytics';
require_once('../system/config-admin.php');

// ══════════════════════════════════════════════════════════
// Helper: calcular % de cambio entre dos valores
// ══════════════════════════════════════════════════════════
function pctChange($current, $previous) {
    if ($previous == 0) return $current > 0 ? 100 : 0;
    return round((($current - $previous) / $previous) * 100, 1);
}

// ══════════════════════════════════════════════════════════
// TARJETAS DE RESUMEN (con comparativa mes actual vs anterior)
// ══════════════════════════════════════════════════════════

// Descargas: total, hoy, este mes, mes anterior
$totalDownloads = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "downloads")->fetchColumn();
$downloadsToday = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "downloads WHERE DATE(date_created) = CURDATE()")->fetchColumn();

$dlThisMonth = (int)$DB_con->query("
    SELECT COUNT(*) FROM " . PFX . "downloads
    WHERE date_created >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
")->fetchColumn();
$dlLastMonth = (int)$DB_con->query("
    SELECT COUNT(*) FROM " . PFX . "downloads
    WHERE date_created >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
      AND date_created <  DATE_FORMAT(CURDATE(), '%Y-%m-01')
")->fetchColumn();
$dlChange = pctChange($dlThisMonth, $dlLastMonth);

// Usuarios: total, este mes, mes anterior
$totalUsers = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "users")->fetchColumn();
$usersThisMonth = (int)$DB_con->query("
    SELECT COUNT(*) FROM " . PFX . "users
    WHERE created >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
")->fetchColumn();
$usersLastMonth = (int)$DB_con->query("
    SELECT COUNT(*) FROM " . PFX . "users
    WHERE created >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
      AND created <  DATE_FORMAT(CURDATE(), '%Y-%m-01')
")->fetchColumn();
$usersChange = pctChange($usersThisMonth, $usersLastMonth);

// Logo #1 del mes
$topLogoMonth = $DB_con->query("
    SELECT p.name, p.slug_lg, p.id, COUNT(*) AS dls
    FROM " . PFX . "downloads d
    INNER JOIN " . PFX . "products p ON d.products_id = p.id
    WHERE d.date_created >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
      AND p.active = 1 AND p.status = 'approved'
    GROUP BY p.id
    ORDER BY dls DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

// ══════════════════════════════════════════════════════════
// LINE CHART: descargas en el tiempo (los 4 periodos de una vez)
// ══════════════════════════════════════════════════════════

// DÍA — últimas 24 horas, por hora
$dayRows = $DB_con->query("
    SELECT HOUR(date_created) AS h, COUNT(*) AS c
    FROM " . PFX . "downloads
    WHERE date_created >= NOW() - INTERVAL 24 HOUR
    GROUP BY HOUR(date_created)
")->fetchAll(PDO::FETCH_KEY_PAIR);
$dayLabels = []; $dayData = [];
for ($i = 23; $i >= 0; $i--) {
    $hour = (int)date('G', strtotime("-$i hours"));
    $dayLabels[] = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':00';
    $dayData[]   = (int)($dayRows[$hour] ?? 0);
}

// SEMANA — últimos 7 días
$weekRows = $DB_con->query("
    SELECT DATE(date_created) AS d, COUNT(*) AS c
    FROM " . PFX . "downloads
    WHERE date_created >= CURDATE() - INTERVAL 6 DAY
    GROUP BY DATE(date_created)
")->fetchAll(PDO::FETCH_KEY_PAIR);
$weekLabels = []; $weekData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $weekLabels[] = date('D', strtotime($date));
    $weekData[]   = (int)($weekRows[$date] ?? 0);
}

// MES — últimos 30 días
$monthRows = $DB_con->query("
    SELECT DATE(date_created) AS d, COUNT(*) AS c
    FROM " . PFX . "downloads
    WHERE date_created >= CURDATE() - INTERVAL 29 DAY
    GROUP BY DATE(date_created)
")->fetchAll(PDO::FETCH_KEY_PAIR);
$monthLabels = []; $monthData = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $monthLabels[] = date('j M', strtotime($date));
    $monthData[]   = (int)($monthRows[$date] ?? 0);
}

// AÑO — últimos 12 meses
$yearRows = $DB_con->query("
    SELECT DATE_FORMAT(date_created, '%Y-%m') AS m, COUNT(*) AS c
    FROM " . PFX . "downloads
    WHERE date_created >= CURDATE() - INTERVAL 11 MONTH
    GROUP BY DATE_FORMAT(date_created, '%Y-%m')
")->fetchAll(PDO::FETCH_KEY_PAIR);
$yearLabels = []; $yearData = [];
for ($i = 11; $i >= 0; $i--) {
    $ym = date('Y-m', strtotime("-$i months"));
    $yearLabels[] = date('M Y', strtotime($ym . '-01'));
    $yearData[]   = (int)($yearRows[$ym] ?? 0);
}

$dlChartData = [
    'day'   => ['labels' => $dayLabels,   'data' => $dayData],
    'week'  => ['labels' => $weekLabels,  'data' => $weekData],
    'month' => ['labels' => $monthLabels, 'data' => $monthData],
    'year'  => ['labels' => $yearLabels,  'data' => $yearData],
];

require_once('includes/header1.php');
?>

<style>
.an-change { font-size:.72rem; font-weight:700; display:inline-flex; align-items:center; gap:.25rem; margin-top:.3rem; }
.an-change.up   { color:var(--adm-success); }
.an-change.down { color:var(--adm-danger); }
.an-change.flat { color:var(--adm-muted); }
.an-card { background:var(--adm-card); border:1px solid var(--adm-border); border-radius:14px; padding:1.25rem; margin-bottom:1rem; }
.an-card-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:1rem; }
.an-card-title { font-size:.9rem; font-weight:700; color:var(--adm-text); display:flex; align-items:center; gap:.5rem; }
.an-range { display:flex; gap:.25rem; }
.an-range-btn { background:transparent; border:1px solid var(--adm-border); color:var(--adm-muted); border-radius:8px; padding:.3rem .7rem; font-size:.72rem; cursor:pointer; transition:all .15s; }
.an-range-btn:hover { border-color:var(--adm-accent); color:var(--adm-text); }
.an-range-btn.active { background:var(--adm-accent); color:#0d0f1c; border-color:var(--adm-accent); font-weight:700; }
.an-chart-box { position:relative; height:300px; }
.an-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
@media (max-width:900px){ .an-grid-2 { grid-template-columns:1fr; } }
.an-loading { display:flex; align-items:center; justify-content:center; gap:.5rem; height:100%; color:var(--adm-muted); font-size:.85rem; }
.an-trend-row { display:flex; align-items:center; gap:.75rem; padding:.6rem 0; border-bottom:1px solid var(--adm-border); }
.an-trend-row:last-child { border-bottom:none; }
.an-trend-rank { width:22px; height:22px; border-radius:6px; background:rgba(212,255,0,.1); color:var(--adm-accent); font-size:.72rem; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.an-trend-name { flex:1; min-width:0; font-size:.82rem; color:var(--adm-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.an-trend-meta { font-size:.72rem; color:var(--adm-muted); }
.an-trend-badge { font-size:.7rem; font-weight:700; color:var(--adm-success); background:rgba(45,198,83,.12); border-radius:99px; padding:.15rem .5rem; flex-shrink:0; }
.an-nr-row { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.55rem 0; border-bottom:1px solid var(--adm-border); }
.an-nr-row:last-child { border-bottom:none; }
.an-nr-term { font-size:.83rem; color:var(--adm-text); font-family:monospace; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.an-nr-count { font-size:.72rem; color:var(--adm-warning); background:rgba(244,208,63,.12); border-radius:99px; padding:.15rem .55rem; flex-shrink:0; font-weight:700; }
.an-dem-row { padding:.6rem 0; border-bottom:1px solid var(--adm-border); }
.an-dem-row:last-child { border-bottom:none; }
.an-dem-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:.35rem; }
.an-dem-cat { font-size:.82rem; color:var(--adm-text); font-weight:600; }
.an-dem-ratio { font-size:.72rem; font-weight:700; color:var(--adm-accent); }
.an-dem-meta { font-size:.7rem; color:var(--adm-muted); }
.an-dem-bar { height:5px; background:rgba(255,255,255,.06); border-radius:99px; margin-top:.35rem; overflow:hidden; }
.an-dem-bar-fill { height:100%; background:var(--adm-accent); border-radius:99px; }
.an-lite-row { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.5rem 0; border-bottom:1px solid var(--adm-border); }
.an-lite-row:last-child { border-bottom:none; }
.an-lite-name { font-size:.82rem; color:var(--adm-text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.an-lite-name a { color:var(--adm-text); text-decoration:none; }
.an-lite-name a:hover { color:var(--adm-accent); }
.an-lite-meta { font-size:.7rem; color:var(--adm-muted); flex-shrink:0; }
.an-lite-badge { font-size:.7rem; font-weight:700; color:var(--adm-danger); background:rgba(255,77,77,.12); border-radius:99px; padding:.15rem .5rem; flex-shrink:0; }
.an-worst-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:.75rem; }
.an-worst-item { background:rgba(255,255,255,.02); border:1px solid var(--adm-border); border-radius:10px; padding:.75rem; }
.an-worst-cat { font-size:.83rem; font-weight:600; color:var(--adm-text); margin-bottom:.25rem; }
.an-worst-ratio { font-size:1.1rem; font-weight:800; color:var(--adm-danger); }
.an-worst-meta { font-size:.7rem; color:var(--adm-muted); margin-top:.15rem; }
</style>

<div class="adm-wrap">

    <!-- Page Header -->
    <div style="display:flex; align-items:center; gap:.75rem; margin-bottom:1.5rem;">
        <div style="background:rgba(212,255,0,.1); border:1px solid rgba(212,255,0,.2); border-radius:10px; width:38px; height:38px; display:flex; align-items:center; justify-content:center;">
            <i class="fa-regular fa-chart-line" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 style="font-size:1.1rem; font-weight:700; color:var(--adm-text); margin:0; line-height:1;">Analytics</h1>
            <p style="font-size:.75rem; color:var(--adm-muted); margin:0;">Download trends, content performance and growth</p>
        </div>
    </div>

    <!-- Leyenda de abreviaciones -->
    <div style="background:rgba(255,255,255,.02); border:1px solid var(--adm-border); border-radius:10px; padding:.7rem 1rem; margin-bottom:1.25rem; font-size:.72rem; color:var(--adm-muted); display:flex; flex-wrap:wrap; gap:1.25rem;">
        <span><i class="fa-regular fa-circle-info" style="color:var(--adm-accent);"></i> <strong style="color:var(--adm-text);">dl</strong> = downloads</span>
        <span><strong style="color:var(--adm-text);">dl/logo</strong> = average downloads per logo (demand vs content)</span>
        <span><strong style="color:var(--adm-text);">×</strong> (trending) = times above its normal weekly average</span>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="adm-stats">
        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-download"></i>
            <div class="adm-stat-num"><?php echo number_format($totalDownloads); ?></div>
            <div class="adm-stat-label">Total Downloads</div>
            <?php
            $cls = $dlChange > 0 ? 'up' : ($dlChange < 0 ? 'down' : 'flat');
            $arrow = $dlChange > 0 ? 'fa-arrow-up' : ($dlChange < 0 ? 'fa-arrow-down' : 'fa-minus');
            ?>
            <div class="an-change <?php echo $cls; ?>">
                <i class="fa-solid <?php echo $arrow; ?>"></i> <?php echo abs($dlChange); ?>% vs last month
            </div>
        </div>

        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-calendar-day"></i>
            <div class="adm-stat-num"><?php echo number_format($downloadsToday); ?></div>
            <div class="adm-stat-label">Downloads Today</div>
            <div class="an-change flat"><?php echo number_format($dlThisMonth); ?> this month</div>
        </div>

        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-users"></i>
            <div class="adm-stat-num"><?php echo number_format($totalUsers); ?></div>
            <div class="adm-stat-label">Total Users</div>
            <?php
            $ucls = $usersChange > 0 ? 'up' : ($usersChange < 0 ? 'down' : 'flat');
            $uarrow = $usersChange > 0 ? 'fa-arrow-up' : ($usersChange < 0 ? 'fa-arrow-down' : 'fa-minus');
            ?>
            <div class="an-change <?php echo $ucls; ?>">
                <i class="fa-solid <?php echo $uarrow; ?>"></i> <?php echo abs($usersChange); ?>% vs last month
            </div>
        </div>

        <div class="adm-stat" style="cursor:default;">
            <i class="adm-stat-icon fa-solid fa-trophy"></i>
            <div class="adm-stat-num" style="font-size:1rem; line-height:1.3; margin-top:.4rem;">
                <?php echo $topLogoMonth ? htmlspecialchars($topLogoMonth['name']) : '—'; ?>
            </div>
            <div class="adm-stat-label">Top Logo This Month</div>
            <?php if ($topLogoMonth): ?>
                <div class="an-change flat"><?php echo number_format($topLogoMonth['dls']); ?> downloads</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- LINE CHART: descargas en el tiempo -->
    <div class="an-card">
        <div class="an-card-head">
            <div class="an-card-title"><i class="fa-regular fa-chart-line"></i> Downloads Over Time</div>
            <div class="an-range">
                <button class="an-range-btn" data-range="day">Day</button>
                <button class="an-range-btn active" data-range="week">Week</button>
                <button class="an-range-btn" data-range="month">Month</button>
                <button class="an-range-btn" data-range="year">Year</button>
            </div>
        </div>
        <div class="an-chart-box">
            <canvas id="downloadsChart"></canvas>
        </div>
    </div>

    <!-- Top Logos + Categorías (cargan por AJAX) -->
    <div class="an-grid-2">
        <div class="an-card">
            <div class="an-card-head">
                <div class="an-card-title"><i class="fa-regular fa-ranking-star"></i> Top Downloaded Logos</div>
                <div class="an-range" id="topRange">
                    <button class="an-range-btn active" data-period="all">All</button>
                    <button class="an-range-btn" data-period="day">Day</button>
                    <button class="an-range-btn" data-period="week">Week</button>
                    <button class="an-range-btn" data-period="month">Month</button>
                    <button class="an-range-btn" data-period="year">Year</button>
                </div>
            </div>
            <div id="topLogosBox" class="an-chart-box">
                <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
                <canvas id="topLogosChart" style="display:none;"></canvas>
            </div>
        </div>
        <div class="an-card">
            <div class="an-card-head">
                <div class="an-card-title"><i class="fa-regular fa-chart-pie"></i> Downloads by Subcategory</div>
                <div class="an-range" id="catRange">
                    <button class="an-range-btn active" data-period="all">All</button>
                    <button class="an-range-btn" data-period="day">Day</button>
                    <button class="an-range-btn" data-period="week">Week</button>
                    <button class="an-range-btn" data-period="month">Month</button>
                    <button class="an-range-btn" data-period="year">Year</button>
                </div>
            </div>
            <div id="categoriesBox" class="an-chart-box">
                <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
                <canvas id="categoriesChart" style="display:none;"></canvas>
            </div>
        </div>
    </div>

    <!-- Nuevos usuarios + Logos en tendencia -->
    <div class="an-grid-2">
        <div class="an-card">
            <div class="an-card-head">
                <div class="an-card-title"><i class="fa-regular fa-user-plus"></i> New Users (30 days)</div>
            </div>
            <div id="newUsersBox" class="an-chart-box">
                <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
                <canvas id="newUsersChart" style="display:none;"></canvas>
            </div>
        </div>
        <div class="an-card">
            <div class="an-card-head">
                <div class="an-card-title"><i class="fa-solid fa-arrow-trend-up"></i> Trending Logos</div>
                <span style="font-size:.7rem;color:var(--adm-muted);">last 7 days</span>
            </div>
            <div id="trendingBox" class="an-chart-box" style="overflow-y:auto;">
                <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
            </div>
        </div>
    </div>

    <!-- Búsquedas sin resultados + Demanda de categorías -->
    <div class="an-grid-2">
        <div class="an-card">
            <div class="an-card-head">
                <div class="an-card-title"><i class="fa-regular fa-magnifying-glass-minus"></i> Searches With No Results</div>
                <span style="font-size:.7rem;color:var(--adm-muted);">what users can't find</span>
            </div>
            <div id="noResultsBox" class="an-chart-box" style="overflow-y:auto;">
                <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
            </div>
        </div>
        <div class="an-card">
            <div class="an-card-head">
                <div class="an-card-title"><i class="fa-regular fa-lightbulb"></i> Subcategory Opportunities</div>
                <span style="font-size:.7rem;color:var(--adm-muted);">high demand, add more logos here</span>
            </div>
            <div id="catDemandBox" class="an-chart-box" style="overflow-y:auto;">
                <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
            </div>
        </div>
    </div>

    <!-- Logos sin descargas (0) + con pocas (1-4) -->
    <div class="an-grid-2">
        <div class="an-card">
            <div class="an-card-head">
                <div class="an-card-title"><i class="fa-regular fa-circle-xmark"></i> Logos With Zero Downloads</div>
                <span style="font-size:.7rem;color:var(--adm-muted);">never downloaded</span>
            </div>
            <div id="zeroBox" class="an-chart-box" style="overflow-y:auto;">
                <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
            </div>
        </div>
        <div class="an-card">
            <div class="an-card-head">
                <div class="an-card-title"><i class="fa-regular fa-arrow-down-short-wide"></i> Logos With Few Downloads</div>
                <span style="font-size:.7rem;color:var(--adm-muted);">1–4 downloads</span>
            </div>
            <div id="fewBox" class="an-chart-box" style="overflow-y:auto;">
                <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
            </div>
        </div>
    </div>

    <!-- Subcategorías con peor rendimiento -->
    <div class="an-card">
        <div class="an-card-head">
            <div class="an-card-title"><i class="fa-regular fa-triangle-exclamation"></i> Underperforming Subcategories</div>
            <span style="font-size:.7rem;color:var(--adm-muted);">lowest downloads per logo (min. 3 logos)</span>
        </div>
        <div id="worstBox" style="min-height:80px;">
            <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
        </div>
    </div>

    <!-- Cantidad de logos por subcategoría (inventario) -->
    <div class="an-card">
        <div class="an-card-head">
            <div class="an-card-title"><i class="fa-regular fa-layer-group"></i> Logos per Subcategory</div>
            <div class="an-range" id="invRange">
                <button class="an-range-btn active" data-filter="least">Least 15</button>
                <button class="an-range-btn" data-filter="most">Most 15</button>
            </div>
        </div>
        <div id="invBox" class="an-chart-box" style="height:420px;">
            <div class="an-loading"><i class="fa-regular fa-spinner fa-spin"></i> Loading...</div>
            <canvas id="invChart" style="display:none;"></canvas>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    var accent  = '#d4ff00';
    var gridCol = 'rgba(255,255,255,.05)';

    // Datos de los 4 periodos (inyectados desde PHP)
    var dlData = <?php echo json_encode($dlChartData); ?>;

    // ── Line chart de descargas ──
    var dlCtx = document.getElementById('downloadsChart').getContext('2d');
    var gradient = dlCtx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(212,255,0,.25)');
    gradient.addColorStop(1, 'rgba(212,255,0,0)');

    var downloadsChart = new Chart(dlCtx, {
        type: 'line',
        data: {
            labels: dlData.week.labels,
            datasets: [{
                label: 'Downloads',
                data: dlData.week.data,
                borderColor: accent,
                backgroundColor: gradient,
                borderWidth: 2,
                fill: true,
                tension: .35,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: accent,
                pointHoverBorderColor: '#0d0f1c',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#13152a',
                    borderColor: 'rgba(255,255,255,.1)',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: accent,
                    padding: 10,
                    displayColors: false,
                    callbacks: { label: function(ctx) { return ctx.parsed.y + ' downloads'; } }
                }
            },
            scales: {
                x: { grid: { color: gridCol, drawBorder: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
                y: { grid: { color: gridCol, drawBorder: false }, beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    // Selector de periodo
    document.querySelectorAll('.an-range-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.an-range-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var range = btn.getAttribute('data-range');
            downloadsChart.data.labels = dlData[range].labels;
            downloadsChart.data.datasets[0].data = dlData[range].data;
            downloadsChart.update();
        });
    });

    // ══════════════════════════════════════════════
    // Carga por AJAX de los charts secundarios
    // ══════════════════════════════════════════════
    var ajaxBase = '<?php echo $setting['website_url']; ?>/admin/ajax-analytics.php';

    // Paleta para el doughnut
    var catColors = ['#d4ff00','#06b6d4','#f4d03f','#2dc653','#ff6b6b','#a78bfa','#fb923c','#38bdf8'];

    // ── TOP LOGOS (bar horizontal, con periodo) ──
    var topChart = null;
    function loadTopLogos(period) {
        var box = document.getElementById('topLogosBox');
        var cv  = document.getElementById('topLogosChart');
        fetch(ajaxBase + '?block=top_logos&period=' + period, { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                var ld = box.querySelector('.an-loading');
                if (!d.success || !d.data.length) {
                    if (topChart) { topChart.destroy(); topChart = null; }
                    cv.style.display = 'none';
                    if (ld) { ld.style.display = 'flex'; ld.textContent = 'No data for this period'; }
                    return;
                }
                if (ld) ld.style.display = 'none';
                cv.style.display = 'block';
                if (topChart) {
                    topChart.data.labels = d.labels;
                    topChart.data.datasets[0].data = d.data;
                    topChart.update();
                } else {
                    topChart = new Chart(cv.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: d.labels,
                            datasets: [{
                                label: 'Downloads',
                                data: d.data,
                                backgroundColor: accent,
                                borderRadius: 4,
                                barThickness: 'flex',
                                maxBarThickness: 18
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#13152a', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1,
                                    titleColor: '#fff', bodyColor: accent, padding: 10, displayColors: false,
                                    callbacks: { label: function(ctx){ return ctx.parsed.x + ' downloads'; } }
                                }
                            },
                            scales: {
                                x: { grid: { color: gridCol, drawBorder: false }, beginAtZero: true, ticks: { precision: 0 } },
                                y: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 11 } } }
                            }
                        }
                    });
                }
            });
    }
    loadTopLogos('all');

    document.querySelectorAll('#topRange .an-range-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('#topRange .an-range-btn').forEach(function(b){ b.classList.remove('active'); });
            btn.classList.add('active');
            loadTopLogos(btn.getAttribute('data-period'));
        });
    });

    // ── CATEGORÍAS/SUBCATEGORÍAS (doughnut, con periodo) ──
    var catChart = null;
    function loadCategories(period) {
        var box = document.getElementById('categoriesBox');
        var cv  = document.getElementById('categoriesChart');
        fetch(ajaxBase + '?block=categories&period=' + period, { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (!d.success || !d.data.length) {
                    if (catChart) { catChart.destroy(); catChart = null; }
                    cv.style.display = 'none';
                    var ld = box.querySelector('.an-loading');
                    if (ld) { ld.style.display = 'flex'; ld.textContent = 'No data for this period'; }
                    return;
                }
                var ld = box.querySelector('.an-loading');
                if (ld) ld.style.display = 'none';
                cv.style.display = 'block';

                if (catChart) {
                    // Actualizar el chart existente
                    catChart.data.labels = d.labels;
                    catChart.data.datasets[0].data = d.data;
                    catChart.update();
                } else {
                    catChart = new Chart(cv.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: d.labels,
                            datasets: [{
                                data: d.data,
                                backgroundColor: catColors,
                                borderColor: '#13152a',
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '62%',
                            plugins: {
                                legend: {
                                    position: 'right',
                                    labels: { color: '#8b8fa8', font: { size: 11 }, padding: 8, boxWidth: 12, boxHeight: 12 }
                                },
                                tooltip: {
                                    backgroundColor: '#13152a', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1,
                                    titleColor: '#fff', bodyColor: '#fff', padding: 10,
                                    callbacks: { label: function(ctx){ return ctx.label + ': ' + ctx.parsed.toLocaleString() + ' downloads'; } }
                                }
                            }
                        }
                    });
                }
            });
    }
    loadCategories('year'); // periodo inicial

    // Selector de periodo del doughnut
    document.querySelectorAll('#catRange .an-range-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('#catRange .an-range-btn').forEach(function(b){ b.classList.remove('active'); });
            btn.classList.add('active');
            loadCategories(btn.getAttribute('data-period'));
        });
    });

    // ── NUEVOS USUARIOS (line pequeño) ──
    fetch(ajaxBase + '?block=new_users', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            if (!d.success || !d.data.length) {
                document.getElementById('newUsersBox').innerHTML = '<div class="an-loading">No data yet</div>';
                return;
            }
            document.querySelector('#newUsersBox .an-loading').style.display = 'none';
            var cv = document.getElementById('newUsersChart');
            cv.style.display = 'block';
            var gr = cv.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gr.addColorStop(0, 'rgba(6,182,212,.25)');
            gr.addColorStop(1, 'rgba(6,182,212,0)');
            new Chart(cv.getContext('2d'), {
                type: 'line',
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: 'New users',
                        data: d.data,
                        borderColor: '#06b6d4',
                        backgroundColor: gr,
                        borderWidth: 2, fill: true, tension: .35,
                        pointRadius: 0, pointHoverRadius: 5,
                        pointHoverBackgroundColor: '#06b6d4', pointHoverBorderColor: '#0d0f1c', pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#13152a', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1,
                            titleColor: '#fff', bodyColor: '#06b6d4', padding: 10, displayColors: false,
                            callbacks: { label: function(ctx){ return ctx.parsed.y + ' new users'; } }
                        }
                    },
                    scales: {
                        x: { grid: { color: gridCol, drawBorder: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
                        y: { grid: { color: gridCol, drawBorder: false }, beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        });

    // ── LOGOS EN TENDENCIA (lista) ──
    fetch(ajaxBase + '?block=trending', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            var box = document.getElementById('trendingBox');
            if (!d.success || !d.items.length) {
                box.innerHTML = '<div class="an-loading">No trending logos yet</div>';
                return;
            }
            var html = '';
            d.items.forEach(function(it, i){
                var url = '<?php echo $setting['website_url']; ?>/item/' + it.id + '/' + it.slug + '/';
                html += '<div class="an-trend-row">' +
                    '<div class="an-trend-rank">' + (i+1) + '</div>' +
                    '<div class="an-trend-name" title="' + it.name + '">' + it.name +
                        '<div class="an-trend-meta">' + it.recent + ' downloads this week</div>' +
                    '</div>' +
                    '<span class="an-trend-badge"><i class="fa-solid fa-arrow-trend-up"></i> ' + it.factor + '×</span>' +
                '</div>';
            });
            box.innerHTML = html;
        });

    // ── BÚSQUEDAS SIN RESULTADOS (lista) ──
    fetch(ajaxBase + '?block=no_results', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            var box = document.getElementById('noResultsBox');
            if (!d.success || !d.items.length) {
                box.innerHTML = '<div class="an-loading">No failed searches — good!</div>';
                return;
            }
            var html = '';
            d.items.forEach(function(it){
                html += '<div class="an-nr-row">' +
                    '<span class="an-nr-term" title="' + it.term + '">' + it.term + '</span>' +
                    '<span class="an-nr-count">' + it.count + '×</span>' +
                '</div>';
            });
            box.innerHTML = html;
        });

    // ── DEMANDA DE CATEGORÍAS (lista con barra) ──
    fetch(ajaxBase + '?block=cat_demand', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            var box = document.getElementById('catDemandBox');
            if (!d.success || !d.items.length) {
                box.innerHTML = '<div class="an-loading">No data yet</div>';
                return;
            }
            // El ratio máximo para escalar las barras
            var maxRatio = Math.max.apply(null, d.items.map(function(x){ return x.ratio; })) || 1;
            var html = '';
            d.items.forEach(function(it){
                var pct = Math.round((it.ratio / maxRatio) * 100);
                html += '<div class="an-dem-row">' +
                    '<div class="an-dem-head">' +
                        '<span class="an-dem-cat">' + it.cat + '</span>' +
                        '<span class="an-dem-ratio">' + it.ratio + ' dl/logo</span>' +
                    '</div>' +
                    '<div class="an-dem-meta">' + it.dls.toLocaleString() + ' downloads · ' + it.logos + ' logos</div>' +
                    '<div class="an-dem-bar"><div class="an-dem-bar-fill" style="width:' + pct + '%;"></div></div>' +
                '</div>';
            });
            box.innerHTML = html;
        });

    // ── LOGOS SIN DESCARGAS (0) ──
    fetch(ajaxBase + '?block=zero_downloads', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            var box = document.getElementById('zeroBox');
            if (!d.success || !d.items.length) {
                box.innerHTML = '<div class="an-loading">Every logo has downloads 🎉</div>';
                return;
            }
            var html = '';
            d.items.forEach(function(it){
                var url = '<?php echo $setting['website_url']; ?>/item/' + it.id + '/' + it.slug + '/';
                html += '<div class="an-lite-row">' +
                    '<span class="an-lite-name"><a href="' + url + '" target="_blank" title="' + it.name + '">' + it.name + '</a></span>' +
                    '<span class="an-lite-meta">' + it.date + '</span>' +
                '</div>';
            });
            box.innerHTML = html;
        });

    // ── LOGOS CON POCAS DESCARGAS (1-4) ──
    fetch(ajaxBase + '?block=few_downloads', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            var box = document.getElementById('fewBox');
            if (!d.success || !d.items.length) {
                box.innerHTML = '<div class="an-loading">No data yet</div>';
                return;
            }
            var html = '';
            d.items.forEach(function(it){
                var url = '<?php echo $setting['website_url']; ?>/item/' + it.id + '/' + it.slug + '/';
                html += '<div class="an-lite-row">' +
                    '<span class="an-lite-name"><a href="' + url + '" target="_blank" title="' + it.name + '">' + it.name + '</a></span>' +
                    '<span class="an-lite-badge">' + it.dls + ' dl</span>' +
                '</div>';
            });
            box.innerHTML = html;
        });

    // ── SUBCATEGORÍAS CON PEOR RATIO ──
    fetch(ajaxBase + '?block=worst_subcat', { credentials: 'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(d){
            var box = document.getElementById('worstBox');
            if (!d.success || !d.items.length) {
                box.innerHTML = '<div class="an-loading">No data yet</div>';
                return;
            }
            var html = '<div class="an-worst-grid">';
            d.items.forEach(function(it){
                html += '<div class="an-worst-item">' +
                    '<div class="an-worst-cat">' + it.cat + '</div>' +
                    '<div class="an-worst-ratio">' + it.ratio + '</div>' +
                    '<div class="an-worst-meta">dl/logo · ' + it.dls.toLocaleString() + ' dls · ' + it.logos + ' logos</div>' +
                '</div>';
            });
            html += '</div>';
            box.innerHTML = html;
        });

    // ── LOGOS POR SUBCATEGORÍA (bar horizontal, filtro all/least) ──
    var invChart = null;
    function loadInventory(filter) {
        var box = document.getElementById('invBox');
        var cv  = document.getElementById('invChart');
        fetch(ajaxBase + '?block=logos_per_subcat&filter=' + filter, { credentials: 'same-origin' })
            .then(function(r){ return r.json(); })
            .then(function(d){
                var ld = box.querySelector('.an-loading');
                if (!d.success || !d.data.length) {
                    if (invChart) { invChart.destroy(); invChart = null; }
                    cv.style.display = 'none';
                    if (ld) { ld.style.display = 'flex'; ld.textContent = 'No data yet'; }
                    return;
                }
                if (ld) ld.style.display = 'none';
                cv.style.display = 'block';
                if (invChart) {
                    invChart.data.labels = d.labels;
                    invChart.data.datasets[0].data = d.data;
                    invChart.update();
                } else {
                    invChart = new Chart(cv.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: d.labels,
                            datasets: [{
                                label: 'Logos',
                                data: d.data,
                                backgroundColor: '#06b6d4',
                                borderRadius: 4,
                                barThickness: 'flex',
                                maxBarThickness: 16
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#13152a', borderColor: 'rgba(255,255,255,.1)', borderWidth: 1,
                                    titleColor: '#fff', bodyColor: '#06b6d4', padding: 10, displayColors: false,
                                    callbacks: { label: function(ctx){ return ctx.parsed.x + ' logos'; } }
                                }
                            },
                            scales: {
                                x: { grid: { color: gridCol, drawBorder: false }, beginAtZero: true, ticks: { precision: 0 } },
                                y: { grid: { display: false, drawBorder: false }, ticks: { font: { size: 11 } } }
                            }
                        }
                    });
                }
            });
    }
    loadInventory('least');

    document.querySelectorAll('#invRange .an-range-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('#invRange .an-range-btn').forEach(function(b){ b.classList.remove('active'); });
            btn.classList.add('active');
            loadInventory(btn.getAttribute('data-filter'));
        });
    });
</script>

<?php require_once('includes/footer.php'); ?>