<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../system/config-admin.php');

// ── Helpers para color dominante del SVG (mismo método que extract-colors.php) ──
function svgHexToRgb($hex)
{
    $hex = ltrim($hex, '#');
    if (!ctype_xdigit($hex)) return [0, 0, 0];
    if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    if (strlen($hex) !== 6) return [0, 0, 0];
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}
function svgColorDistance($h1, $h2)
{
    [$r1, $g1, $b1] = svgHexToRgb($h1);
    [$r2, $g2, $b2] = svgHexToRgb($h2);
    return sqrt(pow($r1 - $r2, 2) + pow($g1 - $g2, 2) + pow($b1 - $b2, 2));
}
function svgNearestBase($hex, $colorMap)
{
    $nearest = 'black';
    $minDist = PHP_INT_MAX;
    foreach ($colorMap as $base => $shades) {
        foreach ($shades as $c) {
            $dist = svgColorDistance($hex, $c);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearest = $base;
            }
        }
    }
    return $nearest;
}
function svgDominantHex($content)
{
    if (!$content) return null;
    preg_match_all('/(fill|stroke)=["\']([^"\']+)["\']/', $content, $matches);
    preg_match_all('/(fill|stroke):\s*([#a-zA-Z][^;"\'\s]+)/', $content, $styleMatches);
    $colors = array_merge($matches[2], $styleMatches[2]);
    $freq = [];
    foreach ($colors as $color) {
        $color = strtolower(trim($color));
        if (in_array($color, ['none', 'transparent', 'inherit', 'currentcolor'])) continue;
        $named = ['black' => '#000000', 'white' => '#ffffff', 'red' => '#ff0000', 'blue' => '#0000ff', 'green' => '#008000', 'yellow' => '#ffff00', 'orange' => '#ffa500', 'purple' => '#800080', 'pink' => '#ffc0cb'];
        if (isset($named[$color])) $color = $named[$color];
        if (!preg_match('/^#[0-9a-fA-F]{3,6}$/', $color)) continue;
        if (in_array($color, ['#fff', '#ffffff', '#000', '#000000'])) continue;
        $freq[$color] = ($freq[$color] ?? 0) + 1;
    }
    if (empty($freq)) return null;
    arsort($freq);
    return array_key_first($freq);
}

$output = array('error' => false);

if (isset($_FILES['file'])) {

    $vector    = $_FILES['file']['name'];
    $tmp_name  = $_FILES['file']['tmp_name'];
    $vect_name = Product::getFileExtension($vector);
    $clean_name = Product::formatName($vect_name[0]);
    $slug_vect  = $clean_name . '-' . time() . '-' . 'logotic';
    $join_name_vec = $slug_vect . '-brand' . '.' . end($vect_name);
    $imgn = $join_name_vec;
    $template_vect = $slug_vect . '-tmpl' . '.' . end($vect_name);
    $new_image_name1 = $imgn;

    $categ = $_POST['cat_id'] ?? '';

    // ── Calcular el nombre final ANTES de mover (para validar) ──
    $origen_name = $_FILES['file']['name'];
    $filename    = pathinfo($origen_name, PATHINFO_FILENAME);
    $clean_file  = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", " ", $filename);
    $strlower    = mb_strtolower($clean_file, 'UTF-8');
    $final_name  = ucwords($strlower);
    $filename_tl = $final_name;

    // ── VALIDACIÓN DE DUPLICADOS (antes de mover el archivo o insertar) ──

    // 1) Hash del archivo temporal (sin moverlo aún)
    $uploadHash = hash_file('sha256', $tmp_name);

    // 1a) ¿Existe ya ese contenido exacto? (hash duplicado → bloquea)
    $dupHash = $DB_con->prepare("SELECT name FROM " . PFX . "products WHERE file_hash = :h LIMIT 1");
    $dupHash->execute([':h' => $uploadHash]);
    $existingByHash = $dupHash->fetchColumn();
    if ($existingByHash !== false) {
        echo json_encode([
            'error'   => true,
            'skipped' => true,
            'reason'  => 'duplicate_file',
            'message' => 'Duplicate file — already uploaded as "' . $existingByHash . '"'
        ]);
        exit;
    }

    // 1b) ¿Existe ya ese nombre? (case-insensitive por la colación utf8_general_ci → bloquea)
    $dupName = $DB_con->prepare("SELECT name FROM " . PFX . "products WHERE name = :n LIMIT 1");
    $dupName->execute([':n' => $filename_tl]);
    if ($dupName->fetchColumn() !== false) {
        echo json_encode([
            'error'   => true,
            'skipped' => true,
            'reason'  => 'duplicate_name',
            'message' => 'A logo named "' . $filename_tl . '" already exists'
        ]);
        exit;
    }

    // ── Pasó la validación: ahora sí mover el archivo ──
    if ($categ == 1) {
        move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $new_image_name1);
    } else {
        move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $template_vect);
    }

    $id_admin    = 0;
    $slug        = Product::formatName($filename_tl) . "-logo";
    $cat_id      = $_POST['cat_id'] ?? null;
    $scat_id     = !empty($_POST['subcat']) ? $_POST['subcat'] : null;
    $date        = date("Y-m-d");

    $tags = ''; // vacío por ahora, se actualiza después con ajax-update-logo.php

    // ── Calcular file_hash y dominant_color del SVG guardado ──
    $file_hash = $uploadHash; // ya calculado antes para la validación
    $dominant_color = null;
    $savedSvg = ($categ == 1) ? $new_image_name1 : $template_vect;
    $svgPath  = '../system/assets/uploads/vector-files/' . $savedSvg;

    if (file_exists($svgPath)) {
        $svgContent = file_get_contents($svgPath);
        if ($svgContent !== false) {
            // Color dominante (mismo método que extract-colors.php)
            $colorMap = [
                'red'    => ['#e63946', '#ff0000', '#cc0000', '#ff3333', '#dc143c', '#b22222', '#ff4500', '#ff6347', '#cd5c5c'],
                'blue'   => ['#1d7af3', '#0000ff', '#0066cc', '#1e90ff', '#4169e1', '#00008b', '#add8e6', '#87ceeb', '#6495ed'],
                'green'  => ['#2dc653', '#008000', '#00ff00', '#228b22', '#32cd32', '#90ee90', '#006400', '#3cb371', '#66bb6a'],
                'yellow' => ['#f4d03f', '#ffff00', '#ffd700', '#ffa500', '#ffeb3b', '#ffc107', '#ff9800'],
                'orange' => ['#f18d35', '#ff6600', '#ff8c00', '#ff7043', '#ff5722'],
                'purple' => ['#8b5cf6', '#800080', '#9b59b6', '#6a0dad', '#9c27b0', '#673ab7', '#7b1fa2'],
                'pink'   => ['#ec4899', '#ff69b4', '#ff1493', '#db7093', '#e91e63', '#f06292'],
                'cyan'   => ['#06b6d4', '#00bcd4', '#00ffff', '#40e0d0', '#00ced1', '#20b2aa'],
                'black'  => ['#000000', '#111111', '#1a1a1a', '#222222', '#333333', '#0d0d0d'],
                'white'  => ['#ffffff', '#f0f0f0', '#fafafa', '#eeeeee', '#e0e0e0'],
            ];
            $hex = svgDominantHex($svgContent);
            if ($hex) {
                $dominant_color = svgNearestBase($hex, $colorMap);
            }
        }
    }

    $sql_upload = $DB_con->prepare("INSERT INTO " . PFX . "products (submit_user_id, slug_lg, name, cat_id, subc_id, icon_img, tags, created, modified, status, file_hash, dominant_color) VALUES (:id_admin, :slug, :name2, :cat_id, :scat_id, :icon_img, :tags, :created, :modified, 'approved', :file_hash, :dominant_color)");

    $sql_upload->bindParam(":id_admin", $id_admin);
    $sql_upload->bindParam(":slug",     $slug);
    $sql_upload->bindParam(":name2",    $filename_tl);
    $sql_upload->bindParam(":cat_id",   $cat_id);
    $sql_upload->bindParam(":scat_id",  $scat_id);
    $icon_img = ($categ == 1) ? $new_image_name1 : $template_vect;
    $sql_upload->bindParam(":icon_img", $icon_img);
    $sql_upload->bindParam(":tags",     $tags);
    $sql_upload->bindParam(":created",  $date);
    $sql_upload->bindParam(":modified", $date);
    $sql_upload->bindParam(":file_hash", $file_hash);
    $sql_upload->bindParam(":dominant_color", $dominant_color);

    if ($sql_upload->execute()) {
        $id['id'] = $DB_con->lastInsertId();
        $id['preview'] = $setting['website_url'] . '/system/assets/uploads/vector-files/' . $savedSvg;  // ← LÍNEA NUEVA
        $data = array_merge($id, $_POST);
        echo json_encode($data);
    } else {
        echo json_encode(['error' => true, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['error' => true, 'message' => 'No file received']);
}
