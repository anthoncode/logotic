<?php
$uid = $crypt->decrypt($_SESSION['uid'], 'USER');

// Métricas para badges
$dlCount = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "downloads WHERE user_id = :uid");
$dlCount->execute([':uid' => $uid]);
$totalDownloads = $dlCount->fetchColumn();

$favCount = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "wishlists WHERE user_id = :uid");
$favCount->execute([':uid' => $uid]);
$totalFavs = $favCount->fetchColumn();

$upCount = $product->countUpload($uid);

// ¿Es contribuidor? (tiene uploads)
$isContributor = $upCount > 0;
?>

<div class="usidenav">

    <!-- Perfil header -->
    <div class="usidenav-profile">
        <div class="usidenav-avatar">
            <?php if (!empty($userDetails['profile']) && $userDetails['profile'] !== '../system/assets/uploads/user-img/default.png'): ?>
                <img src="<?php echo $userDetails['profile']; ?>" alt="<?php echo htmlspecialchars($userDetails['fname']); ?>">
            <?php else: ?>
                <div class="usidenav-avatar-letter">
                    <?php echo strtoupper(mb_substr($userDetails['fname'] ?? 'U', 0, 1)); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="usidenav-name"><?php echo htmlspecialchars($userDetails['fname']); ?></div>
        <div class="usidenav-username">@<?php echo htmlspecialchars($userDetails['username']); ?></div>
        <?php if ($isContributor): ?>
            <div class="usidenav-badge">
                <i class="fa-solid fa-award"></i> Contributor
            </div>
        <?php endif; ?>
    </div>

    <!-- Nav links -->
    <nav class="usidenav-nav">
        <a href="<?php echo $setting['website_url']; ?>/user/"
           class="usidenav-item <?php echo $pg == '1' ? 'active' : ''; ?>">
            <i class="fa-regular fa-grid-2"></i> Overview
        </a>
        <a href="<?php echo $setting['website_url']; ?>/user/downloads.php"
           class="usidenav-item <?php echo $pg == '2' ? 'active' : ''; ?>">
            <i class="fa-regular fa-download"></i> My Downloads
            <?php if ($totalDownloads > 0): ?>
                <span class="usidenav-count"><?php echo $totalDownloads; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo $setting['website_url']; ?>/user/favorites.php"
           class="usidenav-item <?php echo $pg == '3' ? 'active' : ''; ?>">
            <i class="fa-regular fa-heart"></i> My Favorites
            <?php if ($totalFavs > 0): ?>
                <span class="usidenav-count"><?php echo $totalFavs; ?></span>
            <?php endif; ?>
        </a>
        <a href="<?php echo $setting['website_url']; ?>/user/my-logos.php"
           class="usidenav-item <?php echo $pg == '4' ? 'active' : ''; ?>">
            <i class="fa-regular fa-images"></i> My Logos
            <?php if ($upCount > 0): ?>
                <span class="usidenav-count"><?php echo $upCount; ?></span>
            <?php endif; ?>
        </a>

        <div class="usidenav-divider"></div>

        <a href="<?php echo $setting['website_url']; ?>/user/upload-logo.php"
           class="usidenav-item <?php echo $pg == '5' ? 'active' : ''; ?>">
            <i class="fa-regular fa-cloud-arrow-up"></i> Upload Logo
        </a>
        <a href="<?php echo $setting['website_url']; ?>/user/edit-profile.php"
           class="usidenav-item <?php echo $pg == '6' ? 'active' : ''; ?>">
            <i class="fa-regular fa-user-pen"></i> Edit Profile
        </a>
        <a href="<?php echo $setting['website_url']; ?>/user/change-password.php"
           class="usidenav-item <?php echo $pg == '7' ? 'active' : ''; ?>">
            <i class="fa-regular fa-lock"></i> Change Password
        </a>

        <div class="usidenav-divider"></div>

        <a href="<?php echo $setting['website_url']; ?>/user/login.php?logout"
           class="usidenav-item usidenav-signout">
            <i class="fa-regular fa-arrow-right-from-bracket"></i> Sign Out
        </a>
    </nav>
</div>