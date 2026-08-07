<div class="col-md-3 mb-3 hide-tablet-down">
  <?php if (($setting['show_ads'] ?? 0) == 1 && !empty($setting['ads_1'])): ?>
    <div class="sidebar-widget mb-3">
      <div class="ad-slot">
        <?php echo $setting['ads_1']; ?>
      </div>
    </div>
  <?php endif; ?>
  <!-- 🔍 Búsqueda rápida -->
  <div class="sidebar-widget mb-3">
    <div class="sidebar-title">
      <i class="fa-regular fa-search"></i> Quick Search
    </div>
    <form action="<?php echo $setting['website_url']; ?>/search.php" method="GET">
      <div class="sidebar-search-wrap">
        <input type="text" name="key" placeholder="Search logos..." class="sidebar-search-input" minlength="3">
        <button type="submit" class="sidebar-search-btn"><i class="fa-regular fa-arrow-right"></i></button>
      </div>
    </form>
  </div>

  <!-- 📁 Categorías principales -->
  <div class="sidebar-widget mb-3">
    <div class="sidebar-title">
      <i class="fa-solid fa-folders"></i> Categories
    </div>
    <div class="sidebar-cat-list">
      <?php
      $categories = $product->get_categories();
      $count = 0;
      foreach ($categories as $cat) {
        if ($count >= 8) break;
        $cate = Product::formatName($cat['name']);
      ?>
        <a href="<?php echo $setting['website_url'] . '/category/' . $cat['id'] . '/' . $cate; ?>/" class="sidebar-cat-item">
          <i class="fa-solid fa-folder"></i>
          <span><?php echo $cat['name']; ?></span>
          <i class="fa-regular fa-chevron-right ms-auto"></i>
        </a>
      <?php $count++;
      } ?>
      <a href="<?php echo $setting['website_url']; ?>/categories/" class="sidebar-see-all">
        See all categories <i class="fa-regular fa-arrow-right"></i>
      </a>
    </div>
  </div>

  <!-- 🔥 Más descargados -->
  <div class="sidebar-widget mb-3">
    <div class="sidebar-title">
      <i class="fa-solid fa-fire"></i> Most Downloaded
    </div>
    <div class="sidebar-logo-list">
      <?php
      $popular = $product->getPopularProducts();
      $i = 0;
      foreach ($popular as $row) {
        if ($i >= 5) break;
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
        $download = $product->downloadCount($row['id']);
      ?>
        <a href="<?php echo $setting['website_url'] . '/item/' . $urlId . '/' . $urlSlug . '/'; ?>" class="sidebar-logo-item">
          <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img']; ?>" width="40" height="40" alt="<?php echo $row['name']; ?>">
          <div class="sidebar-logo-info">
            <span class="sidebar-logo-name"><?php echo $row['name']; ?></span>
            <span class="sidebar-logo-meta"><i class="fa-regular fa-download"></i> <?php echo $product->formatCount($download['doCount']); ?></span>
          </div>
        </a>
      <?php $i++;
      } ?>
    </div>
  </div>

  <!-- 🆕 Recién agregados -->
  <div class="sidebar-widget mb-3">
    <div class="sidebar-title">
      <i class="fa-regular fa-clock"></i> Recently Added
    </div>
    <div class="sidebar-logo-list">
      <?php
      $recent = $product->getNewProducts();
      $j = 0;
      foreach ($recent as $row) {
        if ($j >= 5) break;
        $urlId = $row['id'];
        $urlSlug = $row['slug_lg'];
      ?>
        <a href="<?php echo $setting['website_url'] . '/item/' . $urlId . '/' . $urlSlug . '/'; ?>" class="sidebar-logo-item">
          <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img']; ?>" width="40" height="40" alt="<?php echo $row['name']; ?>">
          <div class="sidebar-logo-info">
            <span class="sidebar-logo-name"><?php echo $row['name']; ?></span>
            <span class="sidebar-logo-meta sidebar-new-badge">NEW</span>
          </div>
        </a>
      <?php $j++;
      } ?>
    </div>
  </div>

  <!-- 🎨 Buscar por color -->
  <div class="sidebar-widget mb-3">
    <div class="sidebar-title">
      <i class="fa-solid fa-palette"></i> Browse by Color
    </div>
    <div class="sidebar-colors">
      <a href="<?php echo $setting['website_url']; ?>/color/red/" class="color-dot" style="background:#e63946" title="Red"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/blue/" class="color-dot" style="background:#1d7af3" title="Blue"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/green/" class="color-dot" style="background:#2dc653" title="Green"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/yellow/" class="color-dot" style="background:#f4d03f" title="Yellow"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/orange/" class="color-dot" style="background:#f18d35" title="Orange"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/black/" class="color-dot" style="background:#111" title="Black"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/white/" class="color-dot" style="background:#fff; border:1px solid rgba(255,255,255,.2)" title="White"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/purple/" class="color-dot" style="background:#8b5cf6" title="Purple"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/pink/" class="color-dot" style="background:#ec4899" title="Pink"></a>
      <a href="<?php echo $setting['website_url']; ?>/color/cyan/" class="color-dot" style="background:#06b6d4" title="Cyan"></a>
    </div>
  </div>

  <?php if ($setting['show_ads'] == 1) { ?>
    <div class="mb-3"><?php echo $setting['ads_2']; ?></div>
  <?php } ?>

</div>