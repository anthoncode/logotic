<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'system/config-global.php';

$pid = 2914;
$stmt = $DB_con->prepare("SELECT icon_img FROM " . PFX . "products WHERE id = :id");
$stmt->execute([':id' => $pid]);
$logo = $stmt->fetch(PDO::FETCH_ASSOC);
$svgPath = realpath(__DIR__ . '/system/assets/uploads/vector-files/' . $logo['icon_img']);

echo "SVG: $svgPath<br>";
echo "Imagick disponible: " . (extension_loaded('imagick') ? 'SÍ' : 'NO') . "<br>";

try {
    $im = new Imagick();
    $im->setBackgroundColor(new ImagickPixel('transparent'));
    $im->setResolution(300, 300);
    $im->readImage($svgPath);
    echo "Leído. Dimensiones: " . $im->getImageWidth() . "x" . $im->getImageHeight() . "<br>";
    $im->setImageFormat('png32');
    $im->resizeImage(256, 0, Imagick::FILTER_LANCZOS, 1);
    $blob = $im->getImageBlob();
    echo "PNG generado: " . strlen($blob) . " bytes<br>";
    echo "✓ TODO FUNCIONA";
} catch (Throwable $e) {
    echo "✗ ERROR: " . $e->getMessage();
}