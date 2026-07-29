<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);


$pageTitle = 'Free Vector Logos, Icons and Templates';
$metaRobots = "<meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />";

// ── Open Graph de la home ──
$ogTitle = $setting['site_name'] . ' - Download Free Vector Logos in SVG & PNG';
$ogDesc  = 'Browse and download thousands of brand logos and templates in SVG and PNG. Free, high quality, transparent background.';
$ogUrl   = $setting['website_url'] . '/';
$ogType  = 'website';
// Imagen: usa la genérica del sitio (asegúrate de que exista y sea 1200×630)
$ogImage = $setting['website_url'] . '/system/assets/uploads/img/logotic.jpg';

require_once 'system/config-global.php';
require_once 'system/assets/header.php';

$featuredp = $product->getFeaturedProducts();
$newp      = $product->getNewProducts();
$popp      = $product->getPopularProducts();
//$allProd   = $product->getAllProducts();

//$free      = $product->getFreeProducts();
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
        <button class="btn-search" onclick="handleSearch()"><i class="bi bi-search me-1 fa-regular fa-facebook"></i><?php echo $l['search'] ?></button>
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
      <span onclick="selectSuggestion('Twitter')">Twitter</span>
      <span onclick="selectSuggestion('Reddit')">Reddit</span>
      <span onclick="selectSuggestion('Samsung')">Samsung</span>
      <span onclick="selectSuggestion('Google')">Google</span>
      <span onclick="selectSuggestion('Amazon')">Amazon</span>
      <span onclick="selectSuggestion('ChatGPT')">ChatGPT</span>
    </p>
    <div class="stats-bar">
      <div class="stat-item">
        <div class="stat-num" id="stat-0">0<span class="stat-suffix">+</span></div>
        <div class="stat-label">Logos available</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat-1">0<span class="stat-suffix">+</span></div>
        <div class="stat-label">Brands covered</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat-2">0<span class="stat-suffix">%</span></div>
        <div class="stat-label">High-res quality</div>
      </div>
      <div class="stat-item">
        <div class="stat-num" id="stat-3">0<span class="stat-suffix">k+</span></div>
        <div class="stat-label">Downloads/month</div>
      </div>
    </div>
  </div>
</section>

<main class="main-wrapper">
  <div class="container">
    <div class="filter-chips" id="filterChips">
      <div class="chip active" data-cat="all">All</div>
      <div class="chip" data-cat="tech">Tech</div>
      <div class="chip" data-cat="social">Social Media</div>
      <div class="chip" data-cat="finance">Finance</div>
      <div class="chip" data-cat="gaming">Gaming</div>
      <div class="chip" data-cat="food">Food &amp; Drink</div>
      <div class="chip" data-cat="crypto">Crypto</div>
      <div class="chip" data-cat="media">Media</div>
    </div>

    <div class="section-header">
      <h2><span class="section-dot"></span> Featured Items</h2>
      <a href="#" class="btn-see-all">See all <i class="bi bi-arrow-right"></i></a>
    </div>



    <div class="container-logo" id="">

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

    <div class="section-header">
      <h2><span class="section-dot"></span> <?php echo $l['popular_items'] ?></h2>
      <a href="#" class="btn-see-all">See all <i class="bi bi-arrow-right"></i></a>
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


    <div class="section-divider"></div>

    <div class="section-header">
      <h2><span class="section-dot" style="background:#ff6b35;box-shadow:0 0 8px rgba(255,107,53,.6)"></span> Browse Categories</h2>
    </div>
    <div class="row g-3" id="categoryGrid"></div>
  </div>
</main>





<?php
require_once 'system/assets/footer.php';
?>