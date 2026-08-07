<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

$pageTitle = "Admin Dashboard";
require_once('../system/config-admin.php');
require_once('../system/gateways.php');

$customer = new Customer($DB_con);

// Stats principales
$totalUsers    = $customer->countAll();
$totalLogos    = $product->countAll();
$totalDownload = $product->countDownload();
$bannedUsers   = $customer->countBanned();

// Stats adicionales
$activeUsers   = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE active = 1")->fetchColumn();
$unverified    = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE verified = 0")->fetchColumn();
$newUsersToday = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "users WHERE DATE(created) = CURDATE()")->fetchColumn();
$newLogosToday = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE DATE(created) = CURDATE() AND status = 'approved'")->fetchColumn();
$pendingLogos  = $DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE status = 'pending'")->fetchColumn();
$totalViews    = $DB_con->query("SELECT SUM(views) FROM " . PFX . "products")->fetchColumn() ?? 0;

// Logos recientes
$recentLogos = $DB_con->query("SELECT * FROM " . PFX . "products WHERE status = 'approved' ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);

// Usuarios recientes
$recentUsers = $DB_con->query("SELECT * FROM " . PFX . "users ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Top downloads
$topDownloads = $DB_con->query("
    SELECT p.*, COUNT(d.id) as dl_count
    FROM " . PFX . "products p
    INNER JOIN " . PFX . "downloads d ON p.id = d.products_id
    WHERE p.status = 'approved'
    GROUP BY p.id
    ORDER BY dl_count DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Sitemap info
$sitemapPath = $_SERVER['DOCUMENT_ROOT'] . '/logotic/sitemap.xml';

// ═══════════════════════════════════════════
// DATOS PARA GRÁFICOS
// ═══════════════════════════════════════════

// 1. Descargas por DÍA (últimos 30 días)
$dlByDay = $DB_con->query("
    SELECT DATE(date_created) as d, COUNT(*) as total
    FROM " . PFX . "downloads
    WHERE date_created >= (CURDATE() - INTERVAL 29 DAY)
    GROUP BY DATE(date_created)
    ORDER BY d ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Rellenar días sin descargas con 0 (para que la línea sea continua)
$dayLabels = [];
$dayData   = [];
$dayMap = [];
foreach ($dlByDay as $row) { $dayMap[$row['d']] = (int)$row['total']; }
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dayLabels[] = date('d M', strtotime($date));
    $dayData[]   = $dayMap[$date] ?? 0;
}

// 2. Descargas por SEMANA (últimas 12 semanas)
$dlByWeek = $DB_con->query("
    SELECT YEARWEEK(date_created, 1) as yw, MIN(DATE(date_created)) as week_start, COUNT(*) as total
    FROM " . PFX . "downloads
    WHERE date_created >= (CURDATE() - INTERVAL 12 WEEK)
    GROUP BY YEARWEEK(date_created, 1)
    ORDER BY yw ASC
")->fetchAll(PDO::FETCH_ASSOC);
$weekLabels = [];
$weekData   = [];
foreach ($dlByWeek as $row) {
    $weekLabels[] = date('d M', strtotime($row['week_start']));
    $weekData[]   = (int)$row['total'];
}

// 3. Descargas por MES (últimos 12 meses)
$dlByMonth = $DB_con->query("
    SELECT DATE_FORMAT(date_created, '%Y-%m') as ym, COUNT(*) as total
    FROM " . PFX . "downloads
    WHERE date_created >= (CURDATE() - INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(date_created, '%Y-%m')
    ORDER BY ym ASC
")->fetchAll(PDO::FETCH_ASSOC);
$monthLabels = [];
$monthData   = [];
foreach ($dlByMonth as $row) {
    $monthLabels[] = date('M Y', strtotime($row['ym'] . '-01'));
    $monthData[]   = (int)$row['total'];
}

// 4. Top 10 logos más descargados (para el gráfico de barras)
$topChart = $DB_con->query("
    SELECT p.name, COUNT(d.id) as dl_count
    FROM " . PFX . "products p
    INNER JOIN " . PFX . "downloads d ON p.id = d.products_id
    WHERE p.status = 'approved'
    GROUP BY p.id
    ORDER BY dl_count DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
$topLabels = [];
$topData   = [];
foreach ($topChart as $row) {
    $topLabels[] = $row['name'];
    $topData[]   = (int)$row['dl_count'];
}

require_once('includes/header1.php');
?>

<div class="adm-wrap">

    <!-- Page Header -->
    <div class="adm-page-header">
        <div class="adm-page-icon">
            <i class="fa-solid fa-gauge-high" style="color:var(--adm-accent);"></i>
        </div>
        <div>
            <h1 class="adm-page-title">Dashboard</h1>
            <p class="adm-page-sub">Welcome back — <?php echo date('l, d F Y'); ?></p>
        </div>
    </div>

    <!-- Stats principales -->
    <div class="adm-stats">
        <a href="users.php" class="adm-stat">
            <i class="adm-stat-icon fa-solid fa-users"></i>
            <div class="adm-stat-num"><?php echo number_format($totalUsers); ?></div>
            <div class="adm-stat-label">Total Users</div>
            <div class="adm-stat-link"><i class="fa-regular fa-arrow-right"></i> <?php echo $newUsersToday; ?> today</div>
        </a>
        <a href="products.php" class="adm-stat">
            <i class="adm-stat-icon fa-solid fa-image"></i>
            <div class="adm-stat-num"><?php echo number_format($totalLogos); ?></div>
            <div class="adm-stat-label">Total Logos</div>
            <div class="adm-stat-link"><i class="fa-regular fa-arrow-right"></i> <?php echo $newLogosToday; ?> today</div>
        </a>
        <a href="analytics.php" class="adm-stat">
            <i class="adm-stat-icon fa-solid fa-chart-bar"></i>
            <div class="adm-stat-num"><?php echo number_format($totalDownload); ?></div>
            <div class="adm-stat-label">Analytics</div>
            <div class="adm-stat-link"><i class="fa-regular fa-arrow-right"></i> View ranking</div>
        </a>
        <a href="pending.php" class="adm-stat">
            <i class="adm-stat-icon fa-solid fa-clock"></i>
            <div class="adm-stat-num" style="color:<?php echo $pendingLogos > 0 ? 'var(--adm-warning)' : 'var(--adm-accent)'; ?>">
                <?php echo number_format($pendingLogos); ?>
            </div>
            <div class="adm-stat-label">Pending Approval</div>
            <div class="adm-stat-link" style="color:<?php echo $pendingLogos > 0 ? 'var(--adm-warning)' : 'var(--adm-accent)'; ?>">
                <i class="fa-regular fa-arrow-right"></i> Review now
            </div>
        </a>
    </div>

    <!-- Stats secundarias -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem;">
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.3rem;color:var(--adm-success);"><?php echo number_format($activeUsers); ?></div>
            <div class="adm-stat-label">Active Users</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.3rem;color:var(--adm-danger);"><?php echo number_format($bannedUsers); ?></div>
            <div class="adm-stat-label">Banned Users</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.3rem;color:var(--adm-warning);"><?php echo number_format($unverified); ?></div>
            <div class="adm-stat-label">Unverified</div>
        </div>
        <div class="adm-stat" style="cursor:default;">
            <div class="adm-stat-num" style="font-size:1.3rem;color:var(--adm-info);"><?php echo number_format($totalViews); ?></div>
            <div class="adm-stat-label">Total Views</div>
        </div>
    </div>

    <!-- ═══ GRÁFICOS ═══ -->
    <div style="display:grid;grid-template-columns:1.6fr 1fr;gap:1rem;margin-bottom:1.5rem;">

        <!-- Descargas en el tiempo -->
        <div class="adm-card">
            <div class="adm-card-title" style="display:flex;align-items:center;">
                <i class="fa-solid fa-chart-line"></i> Downloads Over Time
                <div style="margin-left:auto;display:flex;gap:.35rem;" id="dlRangeBtns">
                    <button class="dl-range-btn active" data-range="day">Day</button>
                    <button class="dl-range-btn" data-range="week">Week</button>
                    <button class="dl-range-btn" data-range="month">Month</button>
                </div>
            </div>
            <div style="height:280px;position:relative;">
                <canvas id="downloadsChart"></canvas>
            </div>
        </div>

        <!-- Top logos -->
        <div class="adm-card">
            <div class="adm-card-title">
                <i class="fa-solid fa-ranking-star"></i> Top 10 Downloaded
            </div>
            <div style="height:280px;position:relative;">
                <canvas id="topLogosChart"></canvas>
            </div>
        </div>

    </div>

    <style>
    .dl-range-btn {
        background: rgba(255,255,255,.04);
        border: 1px solid var(--adm-border);
        color: var(--adm-muted);
        border-radius: 7px;
        padding: .25rem .7rem;
        font-size: .72rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s;
    }
    .dl-range-btn:hover { color: var(--adm-text); }
    .dl-range-btn.active {
        background: var(--adm-accent);
        color: #0d0f1c;
        border-color: var(--adm-accent);
    }
    </style>

    <!-- Quick Links -->
    <div class="adm-quick-links">
        <a href="all-logos.php" class="adm-quick-link">
            <i class="fa-regular fa-images"></i>
            <span>Logos</span>
            <small>Manage all logos</small>
        </a>
        <a href="users.php" class="adm-quick-link">
            <i class="fa-regular fa-users"></i>
            <span>Users</span>
            <small>Manage users</small>
        </a>
        <a href="pending.php" class="adm-quick-link">
            <i class="fa-regular fa-clock"></i>
            <span>Pending</span>
            <small><?php echo $pendingLogos; ?> waiting</small>
        </a>
        <a href="all-posts.php" class="adm-quick-link">
            <i class="fa-regular fa-rss"></i>
            <span>Blog</span>
            <small>Manage blog posts</small>
        </a>
        <a href="generate-sitemap.php" class="adm-quick-link">
            <i class="fa-regular fa-sitemap"></i>
            <span>Sitemap</span>
            <small><?php echo file_exists($sitemapPath) ? 'Last: ' . date('d M', filemtime($sitemapPath)) : 'Not generated'; ?></small>
        </a>
        <a href="settings.php" class="adm-quick-link">
            <i class="fa-regular fa-gear"></i>
            <span>Settings</span>
            <small>Site configuration</small>
        </a>
    </div>

    <!-- Grid: Recent Logos + Top Downloads + Recent Users -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">

        <!-- Recent Logos -->
        <div class="adm-card">
            <div class="adm-card-title">
                <i class="fa-regular fa-clock"></i> Recently Added Logos
                <a href="products.php" style="margin-left:auto;font-size:.72rem;color:var(--adm-accent);text-decoration:none;">View all →</a>
            </div>
            <div class="adm-recent-list">
                <?php foreach ($recentLogos as $logo): ?>
                <a href="edit-product.php?id=<?php echo $logo['id']; ?>" class="adm-recent-item" style="text-decoration:none;">
                    <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $logo['icon_img']; ?>" alt="<?php echo $logo['name']; ?>">
                    <div>
                        <div class="adm-recent-name"><?php echo htmlspecialchars($logo['name']); ?></div>
                        <div class="adm-recent-meta"><?php echo date('d M Y', strtotime($logo['created'])); ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Downloads -->
        <div class="adm-card">
            <div class="adm-card-title">
                <i class="fa-solid fa-chart-bar"></i> Analytics
                <a href="analytics.php" style="margin-left:auto;font-size:.72rem;color:var(--adm-accent);text-decoration:none;">View all →</a>
            </div>
            <div class="adm-recent-list">
                <?php foreach ($topDownloads as $i => $logo): ?>
                <a href="edit-product.php?id=<?php echo $logo['id']; ?>" class="adm-recent-item" style="text-decoration:none;">
                    <div style="width:24px;text-align:center;font-size:.8rem;font-weight:800;color:<?php echo $i < 3 ? 'var(--adm-warning)' : 'var(--adm-muted)'; ?>;">
                        #<?php echo $i + 1; ?>
                    </div>
                    <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $logo['icon_img']; ?>" alt="<?php echo $logo['name']; ?>">
                    <div>
                        <div class="adm-recent-name"><?php echo htmlspecialchars($logo['name']); ?></div>
                        <div class="adm-recent-meta"><i class="fa-regular fa-download"></i> <?php echo number_format($logo['dl_count']); ?> downloads</div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Recent Users -->
    <div class="adm-card">
        <div class="adm-card-title">
            <i class="fa-regular fa-user-plus"></i> Recent Registrations
            <a href="users.php" style="margin-left:auto;font-size:.72rem;color:var(--adm-accent);text-decoration:none;">View all →</a>
        </div>
        <div class="adm-table-wrap">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $avatarColors = ['#e63946','#1d7af3','#2dc653','#f18d35','#8b5cf6','#ec4899','#06b6d4','#f4d03f'];
                    foreach ($recentUsers as $u):
                        $initial  = strtoupper(mb_substr($u['fname'], 0, 1));
                        $color    = $avatarColors[$u['id'] % count($avatarColors)];
                        $isActive = $u['active'] == 1;
                        $isVerified = $u['verified'] == 1;
                    ?>
                    <tr>
                        <td style="color:var(--adm-muted);font-size:.78rem;">#<?php echo $u['id']; ?></td>
                        <td>
                            <div class="adm-user-cell">
                                <div class="adm-avatar" style="background:<?php echo $color; ?>20;color:<?php echo $color; ?>;border:1.5px solid <?php echo $color; ?>40;">
                                    <?php echo $initial; ?>
                                </div>
                                <div>
                                    <div class="adm-user-name"><?php echo htmlspecialchars($u['fname']); ?></div>
                                    <div class="adm-user-username">@<?php echo htmlspecialchars($u['username']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="color:var(--adm-muted);font-size:.82rem;"><?php echo htmlspecialchars($u['email']); ?></td>
                        <td style="color:var(--adm-muted);font-size:.78rem;"><?php echo date('d M Y', strtotime($u['created'])); ?></td>
                        <td>
                            <?php if (!$isActive): ?>
                                <span class="adm-badge adm-badge-banned"><i class="fa-solid fa-ban"></i> Banned</span>
                            <?php elseif (!$isVerified): ?>
                                <span class="adm-badge adm-badge-pending"><i class="fa-regular fa-clock"></i> Unverified</span>
                            <?php else: ?>
                                <span class="adm-badge adm-badge-active"><i class="fa-solid fa-circle-check"></i> Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="adm-actions">
                                <a href="edit-user.php?id=<?php echo $u['username']; ?>" class="adm-btn"><i class="fa-regular fa-pen"></i> Edit</a>
                                <a href="users.php?action=ban&id=<?php echo $u['id']; ?>" class="adm-btn adm-btn-ban" onclick="return confirm('Ban this user?')"><i class="fa-solid fa-ban"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function() {
    // Colores del tema
    var accent   = '#d4ff00';
    var textMut  = '#8b8fa3';
    var gridCol  = 'rgba(255,255,255,.06)';
    var accentBg = 'rgba(212,255,0,.12)';

    Chart.defaults.color = textMut;
    Chart.defaults.font.family = "'Poppins', sans-serif";
    Chart.defaults.font.size = 11;

    // Datos de descargas (día/semana/mes)
    var dlData = {
        day:   { labels: <?php echo json_encode($dayLabels); ?>,   data: <?php echo json_encode($dayData); ?> },
        week:  { labels: <?php echo json_encode($weekLabels); ?>,  data: <?php echo json_encode($weekData); ?> },
        month: { labels: <?php echo json_encode($monthLabels); ?>, data: <?php echo json_encode($monthData); ?> }
    };

    // ── Gráfico de descargas en el tiempo ──
    var dlCtx = document.getElementById('downloadsChart').getContext('2d');
    var gradient = dlCtx.createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(212,255,0,.25)');
    gradient.addColorStop(1, 'rgba(212,255,0,0)');

    var downloadsChart = new Chart(dlCtx, {
        type: 'line',
        data: {
            labels: dlData.day.labels,
            datasets: [{
                label: 'Downloads',
                data: dlData.day.data,
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
                    callbacks: {
                        label: function(ctx) { return ctx.parsed.y + ' downloads'; }
                    }
                }
            },
            scales: {
                x: { grid: { color: gridCol, drawBorder: false }, ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 8 } },
                y: { grid: { color: gridCol, drawBorder: false }, beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    // Cambiar rango día/semana/mes
    document.querySelectorAll('.dl-range-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.dl-range-btn').forEach(function(b) { b.classList.remove('active'); });
            btn.classList.add('active');
            var range = btn.getAttribute('data-range');
            downloadsChart.data.labels = dlData[range].labels;
            downloadsChart.data.datasets[0].data = dlData[range].data;
            downloadsChart.update();
        });
    });

    // ── Gráfico de top logos (barras horizontales) ──
    var topCtx = document.getElementById('topLogosChart').getContext('2d');
    new Chart(topCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($topLabels); ?>,
            datasets: [{
                label: 'Downloads',
                data: <?php echo json_encode($topData); ?>,
                backgroundColor: accentBg,
                borderColor: accent,
                borderWidth: 1.5,
                borderRadius: 5,
                barThickness: 'flex',
                maxBarThickness: 22
            }]
        },
        options: {
            indexAxis: 'y',
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
                    displayColors: false
                }
            },
            scales: {
                x: { grid: { color: gridCol, drawBorder: false }, beginAtZero: true, ticks: { precision: 0 } },
                y: { grid: { display: false, drawBorder: false }, ticks: { autoSkip: false } }
            }
        }
    });
})();
</script>

<?php require_once('includes/footer.php'); ?>