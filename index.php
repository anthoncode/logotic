<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once 'system/config-global.php';

$pageTitle = 'Free Vector Logos, Icons and Templates';
$metaRobots = "<meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />";

// ── Open Graph de la home ──
$ogTitle = $setting['site_name'] . ' - Download Free Vector Logos in SVG & PNG';
$ogDesc  = 'Browse and download thousands of brand logos and templates in SVG and PNG. Free, high quality, transparent background.';
$ogUrl   = $setting['website_url'] . '/';
$ogType  = 'website';
$ogImage = !empty($setting['og_cover'])
    ? $setting['website_url'] . '/system/assets/uploads/og/' . $setting['og_cover']
    : $setting['website_url'] . '/system/assets/uploads/img/logotic.jpg';

require_once 'system/assets/header.php';

$featuredp = $product->getFeaturedProducts();
$newp      = $product->getNewProducts();
$popp      = $product->getPopularProducts();

// ── Datos para el hero ──
// Reutilizar los populares que ya cargamos (evita otro JOIN pesado a downloads)
$weekTop = array_slice($popp, 0, 6);

// ── Stats reales para el hero ──
$statLogos     = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE status = 'approved'")->fetchColumn();
$statBrands    = (int)$DB_con->query("SELECT COUNT(DISTINCT cat_id) FROM " . PFX . "products WHERE status = 'approved'")->fetchColumn();
$statDownloads = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "downloads WHERE date_created >= (CURDATE() - INTERVAL 30 DAY)")->fetchColumn();

// ── Subcategorías fijas para los chips ──
$topCats = [
    ['id' => 60, 'name' => 'Social Media'],
    ['id' => 54, 'name' => 'Software'],
    ['id' => 51, 'name' => 'Flag'],
    ['id' => 27, 'name' => 'Sports'],
    ['id' => 29, 'name' => 'Transport'],
    ['id' => 26, 'name' => 'Shopping'],
];
?>

<section class="hero">
  <div class="container">
    <div class="hero-icon mx-auto"><i class="bi bi-lightning-fill" style="color:#0d0f1c"><?php if (!empty($setting['site_favicon'])): ?>
          <img src="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>"
            alt="<?php echo htmlspecialchars($setting['site_name']); ?>"
            style="width:100%; height:100%; object-fit:contain; padding:6px;">
        <?php else: ?>
          <?php echo strtoupper(mb_substr($setting['site_name'], 0, 1)); ?>
        <?php endif; ?></i></div>
    <h1><?php echo $setting['homepage_header']; ?> <br><span class="gradient-text">Brand Logos Instantly</span></h1>
    <p><?php echo $setting['homepage_subheader']; ?></p>
    <div class="hero-search position-relative">
      <i class="fa-regular fa-search"></i>
      <form action="search.php" method="GET" class="">
        <input type="text" id="heroSearch" placeholder="<?php echo $l['searchplaceholder'] ?> — e.g. Google, Apple, Nike…" autocomplete="off" name="key" minlength="3" pattern=".{3,}" required="" />
        <button class="btn-search" onclick="handleSearch()"><i class="fa-regular fa-magnifying-glass me-1"></i><?php echo $l['search'] ?></button>
      </form>
      <div class="search-suggestions" id="searchSuggestions">
        <div class="suggestion-item" onclick="selectSuggestion('Google')">
          <div class="suggestion-icon">🔍</div>Google
        </div>
        <div class="suggestion-item" onclick="selectSuggestion('Apple')">
          <div class="suggestion-icon">🍎</div>Apple
        </div>
        <div class="suggestion-item" onclick="selectSuggestion('Nike')">
          <div class="suggestion-icon">👟</div>Nike
        </div>
        <div class="suggestion-item" onclick="selectSuggestion('Twitter / X')">
          <div class="suggestion-icon">🐦</div>Twitter / X
        </div>
        <div class="suggestion-item" onclick="selectSuggestion('Discord')">
          <div class="suggestion-icon">💬</div>Discord
        </div>
      </div>
    </div>
    <p class="hero-hint mt-3">Popular:
      <?php foreach ($weekTop as $wt): ?>
        <a href="<?php echo $setting['website_url'] . '/item/' . $wt['id'] . '/' . $wt['slug_lg'] . '/'; ?>"><?php echo htmlspecialchars(strtok($wt['name'], ' ')); ?></a>
      <?php endforeach; ?>
    </p>
    <div class="stats-bar">
      <div class="stat-item">
        <div class="stat-num" id="stat-0" data-target="<?php echo $statLogos; ?>">0<span class="stat-suffix">+</span></div>
        <div class="stat-label">Logos available</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat-1" data-target="<?php echo $statBrands; ?>">0<span class="stat-suffix">+</span></div>
        <div class="stat-label">Categories</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat-2" data-target="100">0<span class="stat-suffix">%</span></div>
        <div class="stat-label">High-res quality</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat-3" data-target="<?php echo $statDownloads; ?>">0<span class="stat-suffix">+</span></div>
        <div class="stat-label">Downloads/month</div>
      </div>
    </div>
  </div>
</section>

<main class="main-wrapper">
  <div class="container">
    <div class="filter-chips" id="filterChips">
      <div class="chip active" data-cat="all">All</div>
      <?php foreach ($topCats as $tc): ?>
        <div class="chip" data-cat="<?php echo $tc['id']; ?>"><?php echo htmlspecialchars($tc['name']); ?></div>
      <?php endforeach; ?>
    </div>

    <div class="section-header">
      <h2><span class="section-dot"></span> <span id="featuredTitle">Featured Items</span></h2>
      <a href="<?php echo $setting['website_url']; ?>/most-downloaded/" class="btn-see-all">See all <i class="bi bi-arrow-right"></i></a>
    </div>



    <div class="container-logo" id="featuredGrid">

      <!-- featured items-->

      <?php
      foreach ($featuredp as $row) {
        $str = Product::formatName($row['name']);
        $urlLocal = $setting['website_url'];
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
      ?>

        <div class="logo-row mb-3">
          <div class="cont-img">
            <a href="<?php echo $urlLocal . '/item/' . $urlId . '/' . $urlSlug . '/' ?>">
              <img class="card-logotic-logo" style="background:#fff;" width="100" height="100" title="<?php echo $row['name'] ?>" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img'] ?>" alt="<?php echo $row['name'] ?>">
              <!-- <?php //if ($row['views_off'] == '0') {
                    ?>
          <div class="post-view">
            <span class="ps-icon fa-light fa-eye"></span>
            <span><?php //echo $row['views']; 
                  ?></span>      
          </div>
          <?php //} else { }
          ?> -->

              <div class="badge-download-pill">
                <span class="fa-regular fa-download"></span>
                <span>
                  <?php $ip_item = $row['id'];
                  $download  = $product->downloadCount($ip_item);
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

    <div class="section-divider"></div>

    <?php if (($setting['show_ads'] ?? 0) == 1 && !empty($setting['ads_1'])): ?>
    <div class="ad-slot">
        <?php echo $setting['ads_1']; ?>
    </div>
    <?php endif; ?>

    <div class="section-header">
      <h2><span class="section-dot"></span> <?php echo $l['popular_items'] ?></h2>
      <a href="<?php echo $setting['website_url']; ?>/recently-added/" class="btn-see-all">See all <i class="bi bi-arrow-right"></i></a>
    </div>
    <!-- <div id="popularGrid"></div> -->
    <div class="container-logo">
      <?php
      foreach ($popp as $row) {
        $str = Product::formatName($row['name']);
        $urlLocal = $setting['website_url'];
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
      ?>

        <div class="logo-row mb-3">
          <div class="cont-img">
            <a href="<?php echo $urlLocal . '/item/' . $urlId . '/' . $urlSlug . '/' ?>">
              <img class="card-logotic-logo" style="background:#fff;" width="100" height="100" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img'] ?>" alt="<?php echo $row['name'] ?>">


              <div class="badge-download-pill">
                <span class="fa-regular fa-download"></span>
                <span>
                  <?php $ip_item = $row['id'];
                  $download   = $product->downloadCount($ip_item);
                  echo $product->formatCount($download['doCount']); ?>
                </span>
              </div>


              <a class="badge-fire-circle" href="#" title="Trending">
                <span class="circle circ-yellow">
                  <i class="fa-solid fa-fire"></i>
                </span>
              </a>


            </a>
          </div>
        </div>


      <?php } ?>
    </div>

    <div class="section-header">
      <h2><span class="section-dot"></span> <?php echo $l['new_items'] ?></h2>
      <a href="#" class="btn-see-all">See all <i class="bi bi-arrow-right"></i></a>
    </div>


    <script>
      window.SITE_URL = "<?php echo $setting['website_url']; ?>";
    </script>


    <div id="dynamic-posts2"></div>
    <div id="ajax-loader" style="display:none;">
      <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif" ?>" width="30px" style="display: block; margin: 0px auto;">
    </div>
    <div style="text-align: center; margin: 1.5rem 0;">
      <button id="load-more" class="btn-upload" style="margin: 0 auto;">
        <i class="fa-regular fa-arrow-down"></i> Load more
      </button>
    </div>

    <div class="row g-3" id="categoryGrid"></div>
  </div>
</main>





<script>
// Filtrado de chips por categoría
(function() {
    var chips = document.querySelectorAll('#filterChips .chip');
    var grid = document.getElementById('featuredGrid');
    var title = document.getElementById('featuredTitle');
    if (!chips.length || !grid) return;

    var originalHTML = grid.innerHTML;       // guardar el "All" original
    var originalTitle = title ? title.textContent : '';
    var SITE = "<?php echo $setting['website_url']; ?>";

    chips.forEach(function(chip) {
        chip.addEventListener('click', function() {
            chips.forEach(function(c) { c.classList.remove('active'); });
            chip.classList.add('active');

            var cat = chip.getAttribute('data-cat');

            // "All" → restaurar el contenido original
            if (cat === 'all') {
                grid.innerHTML = originalHTML;
                if (title) title.textContent = originalTitle;
                return;
            }

            // Subcategoría → cargar sus logos destacados
            if (title) title.textContent = chip.textContent;
            grid.style.opacity = '.4';

            fetch(SITE + '/filter-subcategory.php?cat=' + encodeURIComponent(cat))
                .then(function(res) { return res.text(); })
                .then(function(html) {
                    grid.innerHTML = html;
                    grid.style.opacity = '1';
                })
                .catch(function() {
                    grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:2rem;color:var(--text-muted);">Could not load this category.</div>';
                    grid.style.opacity = '1';
                });
        });
    });
})();
</script>

<script>
// Animación de los contadores del hero
(function() {
    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-target'), 10) || 0;
        var suffix = el.querySelector('.stat-suffix');
        var suffixText = suffix ? suffix.outerHTML : '';
        var duration = 1500;
        var start = 0;
        var startTime = null;

        function fmt(n) {
            return n >= 1000 ? (n / 1000).toFixed(n >= 10000 ? 0 : 1) + 'k' : n;
        }

        function step(ts) {
            if (!startTime) startTime = ts;
            var progress = Math.min((ts - startTime) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);  // ease-out
            var current = Math.floor(eased * target);
            el.innerHTML = fmt(current) + suffixText;
            if (progress < 1) requestAnimationFrame(step);
            else el.innerHTML = fmt(target) + suffixText;
        }
        requestAnimationFrame(step);
    }

    // Disparar cuando el stats-bar entra en pantalla
    var statsBar = document.querySelector('.stats-bar');
    if (statsBar) {
        var fired = false;
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting && !fired) {
                    fired = true;
                    document.querySelectorAll('.stat-num').forEach(animateCounter);
                }
            });
        }, { threshold: 0.4 });
        observer.observe(statsBar);
    }
})();
</script>

<?php
require_once 'system/assets/footer.php';
?>