<?php
// ═══════════════════════════════════════════════════════════
// og-image.php — Genera imágenes PNG para vistas previas
// (Open Graph / Twitter). Cachea el resultado y NO cuenta
// como descarga ni aplica límites.
// ═══════════════════════════════════════════════════════════
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/system/config-global.php';

// No indexar
header('X-Robots-Tag: noindex, nofollow', true);

// ── Sanitizar ──
$pid  = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
$size = isset($_GET['size']) ? intval($_GET['size']) : 600;
$size = max(200, min(1200, $size));   // rango razonable para vistas previas

if ($pid <= 0) {
    http_response_code(400);
    exit;
}

// ── Buscar el logo ──
$stmt = $DB_con->prepare("SELECT icon_img, active FROM " . PFX . "products WHERE id = :id");
$stmt->execute([':id' => $pid]);
$logo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$logo || $logo['active'] == 0) {
    http_response_code(404);
    exit;
}

// ── Validar ruta del SVG ──
$baseDir = realpath(__DIR__ . '/system/assets/uploads/vector-files');
$svgPath = realpath($baseDir . '/' . $logo['icon_img']);
if ($svgPath === false || strpos($svgPath, $baseDir) !== 0) {
    http_response_code(404);
    exit;
}

// ── Caché ──
$cacheDir = __DIR__ . '/system/assets/uploads/og-cache';
if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0755, true); }
$cacheFile = $cacheDir . '/og_' . $pid . '_' . $size . '.png';

// Si el caché existe y es más nuevo que el SVG, servirlo directo
if (file_exists($cacheFile) && filemtime($cacheFile) >= filemtime($svgPath)) {
    header('Content-Type: image/png');
    header('Content-Length: ' . filesize($cacheFile));
    header('Cache-Control: public, max-age=604800');   // 7 días
    readfile($cacheFile);
    exit;
}

// ── Generar el PNG ──
$pngData = null;

// Método 1: binario de ImageMagick (aislado, no crashea)
$magickBin = null;
foreach (['/usr/local/bin/magick', '/usr/local/bin/convert', '/opt/homebrew/bin/magick', 'magick', 'convert'] as $bin) {
    if (@is_executable($bin) || in_array($bin, ['magick', 'convert'])) { $magickBin = $bin; break; }
}

if ($magickBin) {
    $tmpPng = tempnam(sys_get_temp_dir(), 'og_') . '.png';
    // Fondo blanco (mejor para vistas previas en redes que transparente)
    $cmd = escapeshellarg($magickBin) . ' -background white -density 200 '
         . escapeshellarg($svgPath)
         . ' -resize ' . (int)$size . 'x' . (int)$size . ' '
         . ' -gravity center -extent ' . (int)$size . 'x' . (int)$size . ' '
         . escapeshellarg($tmpPng) . ' 2>&1';
    shell_exec($cmd);
    if (file_exists($tmpPng) && filesize($tmpPng) > 0) {
        $pngData = file_get_contents($tmpPng);
        @unlink($tmpPng);
    } else {
        @unlink($tmpPng);
    }
}

// Método 2 (fallback): extensión Imagick
if ($pngData === null && extension_loaded('imagick')) {
    try {
        $im = new Imagick();
        $im->setBackgroundColor(new ImagickPixel('white'));
        $im->setResolution(200, 200);
        $im->readImage($svgPath);
        $im->setImageFormat('png');
        $im->resizeImage((int)$size, (int)$size, Imagick::FILTER_LANCZOS, 1, true);
        // Centrar en un lienzo cuadrado
        $im->setImageBackgroundColor(new ImagickPixel('white'));
        $im->extentImage((int)$size, (int)$size,
            ((int)$size - $im->getImageWidth()) / 2 * -1,
            ((int)$size - $im->getImageHeight()) / 2 * -1);
        $pngData = $im->getImageBlob();
        $im->clear();
        $im->destroy();
    } catch (Throwable $e) {
        error_log('OG image failed for pid ' . $pid . ': ' . $e->getMessage());
    }
}

// ── Servir ──
if ($pngData !== null && strlen($pngData) > 0) {
    // Guardar en caché
    @file_put_contents($cacheFile, $pngData);

    header('Content-Type: image/png');
    header('Content-Length: ' . strlen($pngData));
    header('Cache-Control: public, max-age=604800');
    echo $pngData;
    exit;
}

// Si falla, servir el logo del sitio como respaldo
$fallback = __DIR__ . '/system/assets/uploads/img/logotic.jpg';
if (file_exists($fallback)) {
    header('Content-Type: image/jpeg');
    readfile($fallback);
}
exit;