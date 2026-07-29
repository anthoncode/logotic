<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'system/config-global.php';

$pid = 2914;
$stmt = $DB_con->prepare("SELECT icon_img FROM " . PFX . "products WHERE id = :id");
$stmt->execute([':id' => $pid]);
$logo = $stmt->fetch(PDO::FETCH_ASSOC);

$svgPath = __DIR__ . '/system/assets/uploads/vector-files/' . $logo['icon_img'];

echo "Archivo: " . $svgPath . "<br>";
echo "Existe: " . (file_exists($svgPath) ? 'SÍ' : 'NO') . "<br>";
echo "Tamaño archivo: " . filesize($svgPath) . " bytes<br><br>";

try {
    $im = new Imagick();
    $im->setBackgroundColor(new ImagickPixel('transparent'));
    $im->readImage($svgPath);  // leer directo del archivo, no blob
    echo "Ancho original: " . $im->getImageWidth() . "<br>";
    echo "Alto original: " . $im->getImageHeight() . "<br>";
    $im->setImageFormat('png32');
    $im->resizeImage(256, 0, Imagick::FILTER_LANCZOS, 1);
    echo "Ancho tras resize: " . $im->getImageWidth() . "<br>";
    echo "Alto tras resize: " . $im->getImageHeight() . "<br>";
    echo "Tamaño del blob PNG: " . strlen($im->getImageBlob()) . " bytes<br>";
    echo "<br>Si el blob tiene bytes, Imagick funciona.<br>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}

