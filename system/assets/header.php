<!doctype html>
<html lang="en">

<head>
  <title><?php echo $pageTitle . " | " . $setting['site_name'] ?></title>
  <?php if (isset($pageContent)) {
    $meta_desc = mb_substr($pageContent, 0, 141, "UTF-8");
  } ?>
  <?php if (isset($metaRobots)) {
    echo $metaRobots;
  } ?>
  <?php if (isset($canonical)) {
    echo '<link rel="canonical" href="' . $canonical . '" />' . "\n";
  } ?>
  <meta name="description" content="<?php if (isset($pageMeta)) {
                                      echo $pageMeta;
                                    } elseif (empty($meta_desc)) {
                                      echo $setting['description'];
                                    } else {
                                      echo $meta_desc;
                                    } ?>" />
  <meta name="keywords" content="<?php echo $setting['keywords']; ?>">
  <meta name="author" content="<?php echo $setting['author']; ?>" />
  <meta name="copyright" content="<?php echo $setting['site_name']; ?>" />
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet" />

  <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/system/assets/css/logotic.css">


  <link rel="icon" type="image/x-icon" href="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>">
  <link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/all.min.css">
  <!-- <link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/regular.min.css"> -->
  <!-- <link rel="stylesheet" type="text/css" href="<?php //echo $setting['website_url']; 
                                                    ?>/system/assets/css/styles.css"> -->
  <script src="<?php echo $setting['website_url']; ?>/system/assets/js/jquery.min.js"></script>
  <!-- <script type="text/javascript" src="<?php //echo $setting['website_url']; 
                                            ?>/system/assets/js/bootstrap.bundle.min.js"></script> -->

<?php
  // Valores por defecto (si la página no define los suyos)
  $ogTitle = $ogTitle ?? ($pageTitle . " | " . $setting['site_name']);
  $ogDesc  = $ogDesc  ?? (isset($pageMeta) ? $pageMeta : $setting['description']);
  $ogImage = $ogImage ?? ($setting['website_url'] . '/system/assets/uploads/img/logotic.jpg');
  $ogUrl   = $ogUrl   ?? (isset($canonical) ? $canonical : $setting['website_url']);
  $ogType  = $ogType  ?? 'website';
  ?>

  <!-- Open Graph -->
  <meta property="og:title" content="<?php echo htmlspecialchars($ogTitle); ?>" />
  <meta property="og:type" content="<?php echo $ogType; ?>" />
  <meta property="og:url" content="<?php echo htmlspecialchars($ogUrl); ?>" />
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>" />
  <meta property="og:description" content="<?php echo htmlspecialchars($ogDesc); ?>" />
  <meta property="og:site_name" content="<?php echo $setting['site_name']; ?>" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?php echo htmlspecialchars($ogTitle); ?>">
  <meta name="twitter:description" content="<?php echo htmlspecialchars($ogDesc); ?>">
  <meta name="twitter:creator" content="<?php echo $setting['author']; ?>">
  <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>">

  <!-- Schema.org -->
  <meta itemprop="name" content="<?php echo htmlspecialchars($ogTitle); ?>">
  <meta itemprop="description" content="<?php echo htmlspecialchars($ogDesc); ?>">
  <meta itemprop="image" content="<?php echo htmlspecialchars($ogImage); ?>">

  <script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
  <script>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    OneSignalDeferred.push(async function(OneSignal) {
      await OneSignal.init({
        appId: "c49c9b26-955c-41f1-b40e-1068fe0fc8e4",
      });
    });
  </script>

  <script>
    $(document).ready(function() {
      $('[data-toggle="tooltip"]').tooltip();
    });
  </script>

  <?php echo $setting['code_header']; ?>

  <?php if (isset($schemaJsonLd)) echo $schemaJsonLd; ?>

  <script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "Organization",
      "name": "<?php echo $setting['site_name']; ?>",
      "url": "<?php echo $setting['website_url']; ?>",
      "logo": "<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_logo']; ?>",
      "sameAs": ["https://www.facebook.com/logotic.me"]
    }
  </script>
  <script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "WebSite",
      "name": "<?php echo $setting['site_name']; ?>",
      "url": "<?php echo $setting['website_url']; ?>",
      "potentialaction": {
        "@type": "SearchAction",
        "target": "https://logotic.me/search.php?key={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
  </script>

</head>

<body>

  <?php if (($setting['notification_header'] ?? 0) == 1 && !empty($setting['global_message'])) { ?>
    <div class="promo-bar promo-<?php echo $setting['alert_type'] ?? 'info'; ?>" id="promoBar">
      <div class="promo-bar-inner">
        <span class="promo-bar-icon"><i class="fa-solid fa-bolt"></i></span>
        <span class="promo-bar-text"><?php echo $setting['global_message']; ?></span>
        <?php if (!empty($setting['global_btn_text']) && !empty($setting['global_btn_link'])): ?>
          <a href="<?php echo htmlspecialchars($setting['global_btn_link']); ?>"
            class="promo-bar-btn"
            <?php echo (strpos($setting['global_btn_link'], 'http') === 0) ? 'target="_blank" rel="noopener"' : ''; ?>>
            <?php echo htmlspecialchars($setting['global_btn_text']); ?>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
        <?php endif; ?>
      </div>
      <button class="promo-bar-close" onclick="closePromoBar()" aria-label="Close">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    
    <!-- Justo después del </div> del promoBar en el header -->
<script>
(function() {
    if (document.cookie.indexOf('promoBarClosed=1') !== -1) {
        var bar = document.getElementById('promoBar');
        if (bar) bar.style.display = 'none';
    }
})();
</script><!-- Justo después del </div> del promoBar en el header -->

  <?php } ?>

  <div class="bg-mesh"></div>



  <nav class="navbar navbar-expand-lg">
    <div class="container-fluid px-lg-4">

      <a class="navbar-brand" href="<?php echo $setting['website_url']; ?>">
        <?php if (empty($setting['site_logo'])) { ?>
          <?php echo $setting['site_name']; ?>
        <?php } else { ?>
          <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_logo']; ?>" style="height:30px;width:auto;">
        <?php } ?>
      </a>

      <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navMain">
        <ul class="navbar-nav me-auto ms-3 gap-1">

          <!-- Mega Menu Categories -->
          <li class="nav-item mega-item" id="megaToggle">
            <a class="nav-link" href="#">
              Categories <i class="fa-regular fa-chevron-down" style="font-size:.7rem;"></i>
            </a>
          </li>

          <!-- Links fijos -->
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $setting['website_url']; ?>/most-downloaded/">
              <i class="fa-solid fa-fire" style="color:#f18d35;font-size:.8rem;"></i> Most Downloaded
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $setting['website_url']; ?>/recently-added/">
              <i class="fa-regular fa-clock" style="font-size:.8rem;"></i> Recently Added
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="<?php echo $setting['website_url']; ?>/blog/">
              <i class="fa-regular fa-newspaper" style="font-size:.8rem;"></i> Blog
            </a>
          </li>

        </ul>

        <form action="<?php echo $setting['website_url']; ?>/search.php" method="GET" class="form-inline my-2 my-lg-0">
          <div class="nav-search me-3 d-none d-lg-block">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input maxlength="20" name="key" minlength="3" pattern=".{3,}" required="" type="text"
              placeholder="Quick search…" value="<?php if (isset($_GET['key'])) {
                                                    echo $_GET['key'];
                                                  } ?>" />
          </div>
        </form>

        <div class="d-flex align-items-center gap-2">
          <?php if ($user->is_loggedin()): ?>
            <a href="<?php echo $setting['website_url']; ?>/user/upload-logo.php" class="btn btn-upload">
              <i class="fa-solid fa-cloud-arrow-up"></i> Upload logo
            </a>
          <?php else: ?>
            <a href="<?php echo $setting['website_url']; ?>/user/login.php?redirect=<?php echo urlencode($setting['website_url'] . '/user/upload-logo.php'); ?>" class="btn btn-upload">
              <i class="fa-solid fa-cloud-arrow-up"></i> Upload logo
            </a>
          <?php endif; ?>

          <?php if ($setting['login'] == 1): ?>
            <?php if ($user->is_loggedin()): ?>
              <!-- Usuario logueado: avatar + dropdown -->
              <div class="user-dropdown-wrap">
                <div class="btn-user">
                  <?php if (!empty($userDetails['profile']) && $userDetails['profile'] !== '../system/assets/uploads/user-img/default.png'): ?>
                    <img src="<?php echo $userDetails['profile']; ?>" alt="<?php echo htmlspecialchars($userDetails['fname']); ?>">
                  <?php else: ?>
                    <?php echo strtoupper(mb_substr($userDetails['fname'] ?? 'U', 0, 1)); ?>
                  <?php endif; ?>
                </div>
                <div class="user-dropdown-panel">
                  <div class="udp-header">
                    <div class="udp-avatar">
                      <?php echo strtoupper(mb_substr($userDetails['fname'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div>
                      <div class="udp-name"><?php echo htmlspecialchars($userDetails['fname']); ?></div>
                      <div class="udp-email"><?php echo htmlspecialchars($userDetails['email']); ?></div>
                    </div>
                  </div>
                  <a href="<?php echo $setting['website_url']; ?>/user/" class="udp-item"><i class="bi bi-grid-1x2-fill"></i> Overview</a>
                  <a href="<?php echo $setting['website_url']; ?>/user/downloads.php" class="udp-item"><i class="bi bi-download"></i> Downloads</a>
                  <a href="<?php echo $setting['website_url']; ?>/user/favorites.php" class="udp-item"><i class="bi bi-heart"></i> Favorites</a>
                  <div class="udp-divider"></div>
                  <a href="<?php echo $setting['website_url']; ?>/user/login.php?logout" class="udp-item udp-signout"><i class="bi bi-box-arrow-right"></i> Sign out</a>
                </div>
              </div>
            <?php else: ?>
              <!-- Usuario NO logueado: solo botón de login -->
              <a href="<?php echo $setting['website_url']; ?>/user/login.php" class="btn-login me-1">
                <i class="fa-solid fa-right-to-bracket"></i><span>Login</span>
              </a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
    </div>
  </nav>

  <!-- ══ Barra simplificada (iPad vertical + móviles ≤768px) ══ -->
  <div class="mobile-simple-bar">
    <a class="msb-logo" href="<?php echo $setting['website_url']; ?>">
      <?php if (empty($setting['site_logo'])): ?>
        <?php echo $setting['site_name']; ?>
      <?php else: ?>
        <img src="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_logo']; ?>" alt="<?php echo htmlspecialchars($setting['site_name']); ?>">
      <?php endif; ?>
    </a>

    <form class="msb-search" action="<?php echo $setting['website_url']; ?>/search.php" method="GET">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="key" maxlength="20" minlength="3" pattern=".{3,}" required
             placeholder="Search logos…"
             value="<?php echo isset($_GET['key']) ? htmlspecialchars($_GET['key']) : ''; ?>">
    </form>

    <div class="msb-actions">
      <?php if ($user->is_loggedin()): ?>
        <a href="<?php echo $setting['website_url']; ?>/user/upload-logo.php" class="msb-icon-btn" title="Upload logo" aria-label="Upload logo">
          <i class="fa-solid fa-cloud-arrow-up"></i>
        </a>
      <?php else: ?>
        <a href="<?php echo $setting['website_url']; ?>/user/login.php?redirect=<?php echo urlencode($setting['website_url'] . '/user/upload-logo.php'); ?>" class="msb-icon-btn" title="Upload logo" aria-label="Upload logo">
          <i class="fa-solid fa-cloud-arrow-up"></i>
        </a>
      <?php endif; ?>

      <?php if ($setting['login'] == 1): ?>
        <?php if ($user->is_loggedin()): ?>
          <div class="user-dropdown-wrap msb-user">
            <div class="btn-user">
              <?php if (!empty($userDetails['profile']) && $userDetails['profile'] !== '../system/assets/uploads/user-img/default.png'): ?>
                <img src="<?php echo $userDetails['profile']; ?>" alt="<?php echo htmlspecialchars($userDetails['fname']); ?>">
              <?php else: ?>
                <?php echo strtoupper(mb_substr($userDetails['fname'] ?? 'U', 0, 1)); ?>
              <?php endif; ?>
            </div>
            <div class="user-dropdown-panel">
              <div class="udp-header">
                <div class="udp-avatar"><?php echo strtoupper(mb_substr($userDetails['fname'] ?? 'U', 0, 1)); ?></div>
                <div>
                  <div class="udp-name"><?php echo htmlspecialchars($userDetails['fname']); ?></div>
                  <div class="udp-email"><?php echo htmlspecialchars($userDetails['email']); ?></div>
                </div>
              </div>
              <a href="<?php echo $setting['website_url']; ?>/user/" class="udp-item"><i class="bi bi-grid-1x2-fill"></i> Overview</a>
              <a href="<?php echo $setting['website_url']; ?>/user/downloads.php" class="udp-item"><i class="bi bi-download"></i> Downloads</a>
              <a href="<?php echo $setting['website_url']; ?>/user/favorites.php" class="udp-item"><i class="bi bi-heart"></i> Favorites</a>
              <div class="udp-divider"></div>
              <a href="<?php echo $setting['website_url']; ?>/user/login.php?logout" class="udp-item udp-signout"><i class="bi bi-box-arrow-right"></i> Sign out</a>
            </div>
          </div>
        <?php else: ?>
          <a href="<?php echo $setting['website_url']; ?>/user/login.php" class="msb-icon-btn" title="Login" aria-label="Login">
            <i class="fa-solid fa-right-to-bracket"></i>
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Mega Menu Panel -->
  <div class="mega-menu" id="megaMenu">
    <div class="container-fluid px-lg-4">
      <div class="mega-menu-grid">

        <?php
        $categories = $product->get_categories();
        $firstCat = true;
        foreach ($categories as $cat):
          $cate = Product::formatName($cat['name']);
          $subcats = $product->dispsubcategories($cat['id']);
          if (empty($subcats)) continue;
        ?>
          <div class="mega-col">
            <a href="<?php echo $setting['website_url'] . '/category/' . $cat['id'] . '/' . $cate; ?>/"
              class="mega-col-title">
              <?php echo $cat['name']; ?>
            </a>
            <!-- Wrap con columnas si es la primera categoría (Brand Logos) -->
            <?php if ($firstCat): ?>
              <div class="mega-links-wrap">
              <?php endif; ?>
              <?php foreach ($subcats as $scat):
                $subcatt = Product::formatName($scat['name']);
              ?>
                <a href="<?php echo $setting['website_url'] . '/subcat/' . $scat['id'] . '/' . $subcatt; ?>/"
                  class="mega-link">
                  <?php echo $scat['name']; ?>
                </a>
              <?php endforeach; ?>
              <?php if ($firstCat): ?>
              </div>
            <?php $firstCat = false;
              endif; ?>
          </div>
        <?php endforeach; ?>

        <!-- Columna Quick Access -->
        <div class="mega-col mega-col-featured">
          <div class="mega-col-title" style="cursor:default;">Quick Access</div>
          <a href="<?php echo $setting['website_url']; ?>/most-downloaded/" class="mega-featured-card">
            <span class="mega-featured-icon"><i class="fa-solid fa-fire"></i></span>
            <div>
              <div class="mega-featured-name">Most Downloaded</div>
              <div class="mega-featured-sub">Top 100 logos by downloads</div>
            </div>
          </a>
          <a href="<?php echo $setting['website_url']; ?>/recently-added/" class="mega-featured-card">
            <span class="mega-featured-icon"><i class="fa-regular fa-clock"></i></span>
            <div>
              <div class="mega-featured-name">Recently Added</div>
              <div class="mega-featured-sub">Latest logos in the collection</div>
            </div>
          </a>
          <a href="<?php echo $setting['website_url']; ?>/color/red/" class="mega-featured-card">
            <span class="mega-featured-icon"><i class="fa-solid fa-palette"></i></span>
            <div>
              <div class="mega-featured-name">Browse by Color</div>
              <div class="mega-featured-sub">Find logos by dominant color</div>
            </div>
          </a>
        </div>

      </div>
    </div>
  </div>
  <div class="mega-overlay" id="megaOverlay"></div>


  <script>
    const megaToggle = document.getElementById('megaToggle');
    const megaMenu = document.getElementById('megaMenu');
    const megaOverlay = document.getElementById('megaOverlay');

    megaToggle.addEventListener('click', function(e) {
      e.preventDefault();
      megaMenu.classList.toggle('open');
      megaOverlay.classList.toggle('open');
    });

    megaOverlay.addEventListener('click', function() {
      megaMenu.classList.remove('open');
      megaOverlay.classList.remove('open');
    });

    // Cierra al hacer clic en cualquier link del mega menu
    document.querySelectorAll('.mega-link, .mega-col-title, .mega-featured-card').forEach(function(el) {
      el.addEventListener('click', function() {
        megaMenu.classList.remove('open');
        megaOverlay.classList.remove('open');
      });
    });

    // ── Dropdown de usuario por CLIC (para móvil/táctil) ──
    // En la barra simplificada, el hover no funciona; se abre/cierra al tocar el avatar.
    document.querySelectorAll('.msb-user .btn-user').forEach(function(btn) {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        var wrap = btn.closest('.user-dropdown-wrap');
        wrap.classList.toggle('open');
      });
    });
    // Cerrar el dropdown al tocar fuera
    document.addEventListener('click', function(e) {
      document.querySelectorAll('.msb-user.open').forEach(function(wrap) {
        if (!wrap.contains(e.target)) {
          wrap.classList.remove('open');
        }
      });
    });
  </script>

  <?php
  // Google One Tap — disponible en todo el sitio
  $oneTapEnabled = ($setting['google_oauth_enabled'] ?? '0') == '1'
    && !empty($setting['google_client_id'])
    && !$user->is_loggedin(); // solo si NO ha iniciado sesión
  ?>
  <?php if ($oneTapEnabled): ?>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <div id="g_id_onload"
      data-client_id="<?php echo htmlspecialchars($setting['google_client_id']); ?>"
      data-callback="handleOneTap"
      data-auto_prompt="true"
      data-cancel_on_tap_outside="false">
    </div>
    <script>
      function handleOneTap(response) {
        fetch('<?php echo $setting['website_url']; ?>/user/google-onetap.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
              credential: response.credential
            })
          })
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (data.success) {
              window.location.reload();
            } else {
              console.error('One Tap failed:', data.error);
            }
          })
          .catch(function(err) {
            console.error('One Tap error:', err);
          });
      }
    </script>
  <?php endif; ?>