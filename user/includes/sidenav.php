<aside class="user-info-wrapper">
  <div class="user-cover" style="background-image: url();">
    <a href="<?php echo $setting['website_url']; ?>/user/account.php" class="info-label"><i class="icon-medal"></i>Edit Account</a>
  </div>
  <div class="user-info">
    <div class="user-avatar"><a href="<?php echo $setting['website_url'] . '/profile/' . $userDetails['username']; ?>/"><img src="<?php echo $userDetails['profile'] ?>" alt="User"></a></div>
    <div class="user-data">
      <h4 class="mb-1">Hi, <?php echo $userDetails['fname']; ?></h4><span class="">Joined: <strong><?php $modified = $userDetails['created'];
                                                                                                  $date = new DateTime($modified);
                                                                                                  echo $date->format('j F Y'); ?></strong></span>
    </div>
  </div>
</aside>
<div class="list-group sidenav_radius">
  <a href="<?php echo $setting['website_url']; ?>/user/" class="list-group-item list-group-item-action <?php echo $pg == '1' ? 'active' : NULL ?>">Overview</a>
  <!-- <a href="<?php echo $setting['website_url']; ?>/user/downloads.php" class="list-group-item list-group-item-action <?php echo $pg == '2' ? 'active' : NULL ?>">Downloads</a> -->
  <!-- <a href="<?php echo $setting['website_url']; ?>/user/transactions.php" class="list-group-item list-group-item-action <?php echo $pg == '4' ? 'active' : NULL ?>">Transactions</a> -->
 <!--  <a href="<?php echo $setting['website_url']; ?>/user/favorites.php" class="list-group-item list-group-item-action list-group-item d-flex justify-content-between align-items-center <?php echo $pg == '5' ? 'active' : NULL ?>">Favorites <span class="badge badge-primary badge-pill"><?php echo $wishcount; ?></span></a> -->
  <a href="<?php echo $setting['website_url']; ?>/user/account.php" class="list-group-item list-group-item-action <?php echo $pg == '6' ? 'active' : NULL ?>">My Account</a>
</div>