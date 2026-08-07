<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_time_limit(300); // 5 min para procesar miles de SVGs

$pageTitle = "Extract Colors";
require_once('../system/config-admin.php');

// Mapa de colores base
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


function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    if (!ctype_xdigit($hex)) return [0, 0, 0];
    if (strlen($hex) === 3) {
        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    }
    if (strlen($hex) !== 6) return [0, 0, 0];
    return [
        hexdec(substr($hex, 0, 2)),
        hexdec(substr($hex, 2, 2)),
        hexdec(substr($hex, 4, 2))
    ];
}

function colorDistance($hex1, $hex2) {
    [$r1,$g1,$b1] = hexToRgb($hex1);
    [$r2,$g2,$b2] = hexToRgb($hex2);
    return sqrt(pow($r1-$r2,2) + pow($g1-$g2,2) + pow($b1-$b2,2));
}

function getNearestBase($hex, $colorMap) {
    $minDist = PHP_INT_MAX;
    $nearest = 'black';
    foreach ($colorMap as $name => $colors) {
        foreach ($colors as $c) {
            $dist = colorDistance($hex, $c);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $name;
            }
        }
    }
    return $nearest;
}

function extractDominantColor($svgPath) {
    if (!file_exists($svgPath)) return null;

    $content = file_get_contents($svgPath);
    if (!$content) return null;

    preg_match_all('/(fill|stroke)=["\']([^"\']+)["\']/', $content, $matches);
    preg_match_all('/(fill|stroke):\s*([#a-zA-Z][^;"\'\s]+)/', $content, $styleMatches);

    $colors = array_merge($matches[2], $styleMatches[2]);
    $freq = [];

    foreach ($colors as $color) {
        $color = strtolower(trim($color));
        if ($color === 'none' || $color === 'transparent' || $color === 'inherit' || $color === 'currentcolor') continue;

        // Convertir colores nombrados comunes
        $namedColors = [
            'black' => '#000000', 'white' => '#ffffff', 'red' => '#ff0000',
            'blue' => '#0000ff', 'green' => '#008000', 'yellow' => '#ffff00',
            'orange' => '#ffa500', 'purple' => '#800080', 'pink' => '#ffc0cb',
        ];
        if (isset($namedColors[$color])) {
            $color = $namedColors[$color];
        }

        // Solo procesar colores hex
        if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $color)) continue;
        // Ignorar blanco y negro puro (suelen ser fondos o contornos)
        if (in_array($color, ['#fff', '#ffffff', '#000', '#000000'])) continue;

        $freq[$color] = ($freq[$color] ?? 0) + 1;
    }

    if (empty($freq)) return 'black';
    arsort($freq);
    return array_key_first($freq);
}

// Obtener logos sin color procesado
$stmt = $DB_con->prepare("SELECT id, icon_img FROM " . PFX . "products WHERE dominant_color IS NULL AND status = 'approved' LIMIT 2000");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$svgDir = $_SERVER['DOCUMENT_ROOT'] . '/logotic/system/assets/uploads/vector-files/';

$processed = 0;
$errors = 0;
$results = [];

foreach ($products as $p) {
    $path = $svgDir . $p['icon_img'];
    $hex = extractDominantColor($path);

    if ($hex) {
        $base = getNearestBase($hex, $colorMap);
        $stmt2 = $DB_con->prepare("UPDATE " . PFX . "products SET dominant_color = :color WHERE id = :id");
        $stmt2->execute([':color' => $base, ':id' => $p['id']]);
        $results[] = ['id' => $p['id'], 'file' => $p['icon_img'], 'color' => $base, 'hex' => $hex];
        $processed++;
    } else {
        $errors++;
    }
}

// Contar pendientes
$stmtPending = $DB_con->prepare("SELECT COUNT(*) as total FROM " . PFX . "products WHERE dominant_color IS NULL AND status = 'approved'");
$stmtPending->execute();
$pending = $stmtPending->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html>
<head><title>Extract Colors</title></head>
<body style="background:#0d0f1c;color:#f0f2ff;font-family:Poppins,sans-serif;padding:2rem;">
<h2 style="color:#d4ff00;">🎨 Color Extractor</h2>
<p>Processed: <strong><?php echo $processed; ?></strong> logos</p>
<p>Errors: <strong style="color:#ff6b6b;"><?php echo $errors; ?></strong></p>
<p>Still pending: <strong style="color:#f18d35;"><?php echo $pending; ?></strong></p>
<?php if ($pending > 0): ?>
<a href="extract-colors.php" style="background:#d4ff00;color:#0d0f1c;padding:.5rem 1.2rem;border-radius:99px;font-weight:700;text-decoration:none;">
    Process next 500 →
</a>
<?php else: ?>
<p style="color:#2dc653;">✅ All logos processed!</p>
<?php endif; ?>

<table style="margin-top:1.5rem;border-collapse:collapse;width:100%;">
<tr style="color:#8b8fa8;font-size:.8rem;border-bottom:1px solid rgba(255,255,255,.1);">
    <th style="padding:.5rem;text-align:left;">ID</th>
    <th style="padding:.5rem;text-align:left;">File</th>
    <th style="padding:.5rem;text-align:left;">Hex</th>
    <th style="padding:.5rem;text-align:left;">Base Color</th>
</tr>
<?php foreach (array_slice($results, 0, 50) as $r): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.05);font-size:.8rem;">
    <td style="padding:.4rem;"><?php echo $r['id']; ?></td>
    <td style="padding:.4rem;"><?php echo $r['file']; ?></td>
    <td style="padding:.4rem;"><span style="background:<?php echo $r['hex']; ?>;width:16px;height:16px;display:inline-block;border-radius:4px;vertical-align:middle;"></span> <?php echo $r['hex']; ?></td>
    <td style="padding:.4rem;"><?php echo $r['color']; ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>