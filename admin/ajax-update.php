<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    require_once '../system/config-admin.php';
    /*if (!preg_match('/^([1-9][0-9]*|0)(\.[0-9]{2})?$/', $_POST['price'])) {
        echo '<span class="text-danger">You need to put a price</span>';
        die();
    }*/
    if (empty($_POST['name'])) {
        echo '<span class="text-danger">Product Name is required!</span>';
        die();
    }

    if (empty($_POST['tags'])) {
        echo '<span class="text-danger">Product Tags is required!</span>';
        die();
    }
    if (empty($_POST['cat_id'])) {
        echo '<span class="text-danger">Category is required.</span>';
        die();
    }

    $id          = $_POST['id'];
    $name2       = $_POST['name'];
    $slug        = Product::formatName($_POST['name']) . "-logo";//slug del item
    $description = $_POST['description'];
    $cat_id      = $_POST['cat_id'];
    //$scat_id = $_POST['subcat'];
    $scat_id     = (isset($_POST['subcat']) ? $_POST['subcat'] : null);

    $website     = $_POST['website'];

    $date        = date("Y-m-d");
    $featured    = (isset($_POST['featured'])) ? 1 : 0;

    $views_off   = (isset($_POST['views_off'])) ? 1 : 0;
    $download_off = (isset($_POST['download_off'])) ? 1 : 0;

    $tags        = strtolower($_POST['tags']);
    // Estado del logo: validar que sea uno de los permitidos
    $status = $_POST['status'] ?? 'approved';
    $allowedStatus = ['approved', 'pending', 'rejected', 'inactive'];
    if (!in_array($status, $allowedStatus, true)) {
        $status = 'approved';
    }

    $sql_upload = $DB_con->prepare("UPDATE " . PFX . "products SET name=:name2, slug_lg=:slug, description=:description, cat_id=:cat_id, subc_id=:scat_id, website=:website, tags=:tags, modified=:modified, featured=:featured, status=:status, views_off=:views_off, download_off=:download_off WHERE id=:id");

    $sql_upload->bindparam(":name2", $name2);
    $sql_upload->bindparam(":slug", $slug);
    $sql_upload->bindparam(":description", $description);
    $sql_upload->bindparam(":cat_id", $cat_id);
    $sql_upload->bindparam(":scat_id", $scat_id);

    $sql_upload->bindparam(":website", $website);
    $sql_upload->bindparam(":modified", $date);
    $sql_upload->bindparam(":featured", $featured);
    $sql_upload->bindparam(":status", $status);
    $sql_upload->bindparam(":views_off", $views_off);
    $sql_upload->bindparam(":download_off", $download_off);

    $sql_upload->bindparam(":tags", $tags);
    $sql_upload->bindparam(":id", $id);

    if ($sql_upload->execute()) {
        echo '<span class="text-success">Product Updated!</span>';
    } else {
        echo "Error: " . $sql_upload->error;
    }
} else {
    header('location: ../index.php');
}