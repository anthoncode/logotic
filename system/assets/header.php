<!doctype html>
<html lang="en">

<head>
  <title><?php echo $pageTitle . " | " . $setting['site_name'] ?></title>
  <?php if (isset($pageContent)) {
    $meta_desc = mb_substr($pageContent, 0, 141, "UTF-8");
  } ?>
  <?php if ($metaRobots) {
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
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" /> -->

  <link rel="stylesheet" href="<?php echo $setting['website_url']; ?>/system/assets/css/logotic.css">


  <link rel="icon" type="image/x-icon" href="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>">
  <link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/all.min.css">
  <!-- <link rel="stylesheet" type="text/css" href="<?php echo $setting['website_url']; ?>/system/assets/css/regular.min.css"> -->
  <!-- <link rel="stylesheet" type="text/css" href="<?php //echo $setting['website_url']; 
                                                    ?>/system/assets/css/styles.css"> -->
  <script src="<?php echo $setting['website_url']; ?>/system/assets/js/jquery.min.js"></script>
  <!-- <script type="text/javascript" src="<?php //echo $setting['website_url']; 
                                            ?>/system/assets/js/bootstrap.bundle.min.js"></script> -->


  <!-- Open Graph para Facebook -->
  <meta property="og:title" content="<?php echo $setting['site_name']; ?>" />
  <meta property="og:type" content="website" />
  <?php if (isset($canonical)) {
    echo '<meta property="og:url" content="' . $canonical . '" />' . "\n";
  } else {
    echo '<meta property="og:url" content="' . $setting['website_url'] . '" />' . "\n";
  } ?>
  <meta property="og:image" content="<?php echo $setting['website_url']; ?>/system/assets/uploads/img/logotic.jpg" />
  <meta property="og:description" content="<?php echo $setting['description']; ?>" />
  <meta property="og:site_name" content="<?php echo $setting['site_name']; ?>" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary">
  <meta name="twitter:title" content="<?php echo $setting['site_name']; ?>">
  <meta name="twitter:description" content="<?php echo $setting['description']; ?>">
  <meta name="twitter:creator" content="<?php echo $setting['author']; ?>">
  <meta name="twitter:image" content="<?php echo $setting['website_url']; ?>/system/assets/uploads/img/logotic.jpg">

  <!-- Schema.org para Google+ -->
  <meta itemprop="name" content="<?php echo $setting['site_name']; ?>">
  <meta itemprop="description" content="<?php echo $setting['description']; ?>">
  <meta itemprop="image" content="<?php echo $setting['website_url']; ?>/system/assets/uploads/img/logotic.jpg">

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
  <?php if (!empty($setting['global_message'])) { ?>
    <div class="alert box-shadow text-center alert-<?php echo $setting['alert_type']; ?> alert-dismissible fade show mb-0 rounded-0" role="alert">
      <?php echo $setting['global_message']; ?>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  <?php } else { ?>
  <?php } ?>

  <div class="bg-mesh"></div>
  <?php if ($setting['notification_header'] == 1) { ?>
    <li class="nav-item">
      <a class="nav-link" href="<?php echo $setting['url_msg']; ?>" target="_blank">
        <div class="not-msg">
          <?php echo $setting['notification_msg']; ?> <span class="badge badge-warning">NEW</span>
        </div>
      </a>
    </li>
  <?php } ?>



  <nav class="navbar navbar-expand-lg" style="background-color: <?php echo $setting['bg_color']; ?>;">
    <div class="container-fluid px-lg-4">

      <a class="navbar-brand" href="<?php echo $setting['website_url']; ?>">
        <?php if (empty($setting['site_logo'])) { ?>
          <?php echo $setting['site_name']; ?>
        <?php } else { ?>
          <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/" . $setting['site_logo']; ?>">
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

        </ul>

        <form action="<?php echo $setting['website_url']; ?>/search.php" method="GET" class="form-inline my-2 my-lg-0">
          <div class="nav-search me-3 d-none d-lg-block">
            <i class="bi bi-search"></i>
            <input maxlength="20" name="key" minlength="3" pattern=".{3,}" required="" type="text"
              placeholder="Quick search…" value="<?php if (isset($_GET['key'])) {
                                                    echo $_GET['key'];
                                                  } ?>" />
          </div>
        </form>

        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-upload" onclick="showToast('Upload feature coming soon!')">
            <i class="bi bi-cloud-upload-fill"></i> Upload logo
          </button>

          <div class="user-dropdown-wrap">
            <div class="btn-user"><i class="bi bi-person-fill"></i></div>
            <?php if ($setting['login'] == 1) { ?>
              <?php if ($user->is_loggedin()) { ?>
                <div class="user-dropdown-panel">
                  <div class="udp-header">
                    <div class="udp-avatar">L</div>
                    <div>
                      <div class="udp-name"><?php echo $userDetails['fname']; ?></div>
                      <div class="udp-email">jane@example.com</div>
                    </div>
                  </div>
                  <a href="<?php echo $setting['website_url']; ?>/user/" class="udp-item"><i class="bi bi-grid-1x2-fill"></i> Overview</a>
                  <a href="<?php echo $setting['website_url']; ?>/user/downloads.php" class="udp-item"><i class="bi bi-download"></i> Downloads</a>
                  <a href="<?php echo $setting['website_url']; ?>/user/news.php" class="udp-item"><i class="bi bi-bell-fill"></i> Notifications</a>
                  <div class="udp-divider"></div>
                  <a href="<?php echo $setting['website_url']; ?>/user/login.php?logout" class="udp-item udp-signout"><i class="bi bi-box-arrow-right"></i> Sign out</a>
                </div>
              <?php } else { ?>
                <a href="<?php echo $setting['website_url']; ?>/user/" type="button" class="btn-login me-1">
                  <i class="bi bi-box-arrow-in-right"></i><span>Login</span>
                </a>
          </div>
        <?php } ?>
      <?php } ?>
        </div>
      </div>
    </div>
    </div>
  </nav>

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
  </script>