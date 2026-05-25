<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('./system/config-global.php');
if (isset($_GET['id'])) {
  $id = $_GET['id'];
}
?>


<div class="col-md-3 mb-3">
  <div class="card box-shadow mb-3">
    <div class="card-header font-weight-bold bg-light">
      <?php echo $l['all_category'] ?>
    </div>

    <div class="list-group bg-light">
      <?php
        $category = $product->get_categories();
        foreach ($category as $cat) {
        $cate = Product::formatName($cat['name']);
      ?>
        <a href="<?php echo $setting['website_url'] ."/category/". $cat['id'] ."/". $cate; ?>/" class="list-group-item list-group-item-action">
          <span class="ml-2 font-weight-bold"><i class="fa-solid fa-folders"></i> <?php echo $cat['name']; ?></span>
        </a>
      <?php } ?>
    </div>
  </div>

  <?php if ($setting['show_ads'] == 1) { ?>
    <div style="width: auto; height: auto !important;">
      <?php echo $setting['ads_2']; ?>
    </div>
  <?php } ?>

  <div class="card box-shadow mb-3">
    <div class="card-header font-weight-bold bg-light">
      <?php echo $l['current_category'] ?>
    </div>

    <div class="list-group bg-light">
      <a href="<?php echo $setting['website_url']; ?>/category/<?php echo $catname['id']; ?>/" class="list-group-item list-group-item-action active">
        <span class="ml-2 font-weight-bold"><i class="fa-solid fa-folders"></i> <?php echo $catname['name']; ?></span>
      </a>
      <?php $subcat = $product->dispsubcategories($catname['id']);
      foreach ($subcat as $scat) {
      $subcatt = Product::formatName($scat['name']);
      ?>
        <a href="<?php echo $setting['website_url'] ."/subcat/". $scat['id'] ."/". $subcatt; ?>/" class="list-group-item list-group-item-action"><span class="ml-3"><i class="fa-solid fa-folder-open"></i> <?php echo $scat['name']; ?></span></a>
      <?php } ?>
    </div>
  </div>
  
  <?php if ($setting['show_ads'] == 1) { ?>
    <div style="height: auto !important;">
      <?php echo $setting['ads_2']; ?>
    </div>
  <?php } ?>
  
  
</div>
