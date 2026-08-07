<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    require_once('../system/config-admin.php');

    // ── Helpers para color dominante del SVG (mismo método que extract-colors.php) ──
    function svgHexToRgb($hex) {
        $hex = ltrim($hex, '#');
        if (!ctype_xdigit($hex)) return [0, 0, 0];
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        if (strlen($hex) !== 6) return [0, 0, 0];
        return [hexdec(substr($hex,0,2)), hexdec(substr($hex,2,2)), hexdec(substr($hex,4,2))];
    }
    function svgColorDistance($h1, $h2) {
        [$r1,$g1,$b1] = svgHexToRgb($h1);
        [$r2,$g2,$b2] = svgHexToRgb($h2);
        return sqrt(pow($r1-$r2,2) + pow($g1-$g2,2) + pow($b1-$b2,2));
    }
    function svgNearestBase($hex, $colorMap) {
        $nearest = 'black'; $minDist = PHP_INT_MAX;
        foreach ($colorMap as $base => $shades) {
            foreach ($shades as $c) {
                $dist = svgColorDistance($hex, $c);
                if ($dist < $minDist) { $minDist = $dist; $nearest = $base; }
            }
        }
        return $nearest;
    }
    function svgDominantHex($content) {
        if (!$content) return null;
        preg_match_all('/(fill|stroke)=["\']([^"\']+)["\']/', $content, $matches);
        preg_match_all('/(fill|stroke):\s*([#a-zA-Z][^;"\'\s]+)/', $content, $styleMatches);
        $colors = array_merge($matches[2], $styleMatches[2]);
        $freq = [];
        foreach ($colors as $color) {
            $color = strtolower(trim($color));
            if (in_array($color, ['none','transparent','inherit','currentcolor'])) continue;
            $named = ['black'=>'#000000','white'=>'#ffffff','red'=>'#ff0000','blue'=>'#0000ff','green'=>'#008000','yellow'=>'#ffff00','orange'=>'#ffa500','purple'=>'#800080','pink'=>'#ffc0cb'];
            if (isset($named[$color])) $color = $named[$color];
            if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $color)) continue;
            if (in_array($color, ['#fff','#ffffff','#000','#000000'])) continue;
            $freq[$color] = ($freq[$color] ?? 0) + 1;
        }
        if (empty($freq)) return null;
        arsort($freq);
        return array_key_first($freq);
    }
    function svgDominantColor($content) {
        $colorMap = [
            'red'    => ['#e63946','#ff0000','#cc0000','#ff3333','#dc143c','#b22222','#ff4500','#ff6347','#cd5c5c'],
            'blue'   => ['#1d7af3','#0000ff','#0066cc','#1e90ff','#4169e1','#00008b','#add8e6','#87ceeb','#6495ed'],
            'green'  => ['#2dc653','#008000','#00ff00','#228b22','#32cd32','#90ee90','#006400','#3cb371','#66bb6a'],
            'yellow' => ['#f4d03f','#ffff00','#ffd700','#ffa500','#ffeb3b','#ffc107','#ff9800'],
            'orange' => ['#f18d35','#ff6600','#ff8c00','#ff7043','#ff5722'],
            'purple' => ['#8b5cf6','#800080','#9b59b6','#6a0dad','#9c27b0','#673ab7','#7b1fa2'],
            'pink'   => ['#ec4899','#ff69b4','#ff1493','#db7093','#e91e63','#f06292'],
            'cyan'   => ['#06b6d4','#00bcd4','#00ffff','#40e0d0','#00ced1','#20b2aa'],
            'black'  => ['#000000','#111111','#1a1a1a','#222222','#333333','#0d0d0d'],
            'white'  => ['#ffffff','#f0f0f0','#fafafa','#eeeeee','#e0e0e0'],
        ];
        $hex = svgDominantHex($content);
        return $hex ? svgNearestBase($hex, $colorMap) : null;
    }

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo '<span class="text-danger">Invalid logo ID</span>';
        exit;
    }

    $productDetails = $product->details($id);
    if (!$productDetails) {
        echo '<span class="text-danger">Logo not found</span>';
        exit;
    }

    // Validar que llegó un archivo
    if (empty($_FILES['iconimgfile']['name'])) {
        echo '<span class="text-danger">Select a vector file</span>';
        exit;
    }

    $file = $_FILES['iconimgfile'];

    // Validar tipo (SVG)
    if ($file['type'] !== 'image/svg+xml') {
        echo '<span class="text-danger">Please select an SVG file</span>';
        exit;
    }

    // Validar tamaño (máx 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo '<span class="text-danger">File too large (max 5MB)</span>';
        exit;
    }

    $tmp_name = $file['tmp_name'];

    // ── Leer contenido del nuevo SVG (para hash + color) ──
    $svgContent = file_get_contents($tmp_name);
    if ($svgContent === false || stripos($svgContent, '<svg') === false) {
        echo '<span class="text-danger">The file does not appear to be a valid SVG</span>';
        exit;
    }

    // ── Nuevo hash y color del archivo de reemplazo ──
    $newHash  = hash('sha256', $svgContent);
    $newColor = svgDominantColor($svgContent);

    // ── Construir el nuevo nombre de archivo (nombre nuevo = evita caché) ──
    $vect_name  = Product::getFileExtension($file['name']);
    $clean_name = Product::formatName($vect_name[0]);
    $slug_vect  = $clean_name . '-' . time() . '-' . 'logotic';
    $ext        = end($vect_name);

    // Respetar la lógica de doble nombre según categoría (brand vs tmpl)
    $catDetails = $product->catdetails($productDetails['cat_id']);
    $catid      = $catDetails['id'] ?? 0;
    $newFileName = ($catid == 1)
        ? $slug_vect . '-brand.' . $ext
        : $slug_vect . '-tmpl.' . $ext;

    $destDir  = '../system/assets/uploads/vector-files/';
    $destPath = $destDir . $newFileName;

    // ── Mover el nuevo archivo ──
    if (!move_uploaded_file($tmp_name, $destPath)) {
        echo '<span class="text-danger">Could not save the new file</span>';
        exit;
    }

    // ── Borrar el archivo viejo (ruta correcta, sin dejar basura) ──
    $oldFile = $productDetails['icon_img'];
    if (!empty($oldFile) && $oldFile !== $newFileName) {
        $oldPath = $destDir . $oldFile;
        if (file_exists($oldPath)) {
            @unlink($oldPath);
        }
    }

    // ── Actualizar la base: nombre, hash y color juntos ──
    try {
        $upd = $DB_con->prepare("
            UPDATE " . PFX . "products
            SET icon_img = :icon, file_hash = :hash, dominant_color = :color, modified = :mod
            WHERE id = :id
        ");
        $upd->execute([
            ':icon'  => $newFileName,
            ':hash'  => $newHash,
            ':color' => $newColor,
            ':mod'   => date('Y-m-d'),
            ':id'    => $id,
        ]);

        echo '<span class="text-success">Logo file replaced successfully</span>';
    } catch (Throwable $e) {
        // Si falla la base, quitar el archivo nuevo que acabamos de mover (no dejar basura)
        if (file_exists($destPath)) @unlink($destPath);
        echo '<span class="text-danger">Database error</span>';
    }

} else {
    header('location: ../index.php');
}