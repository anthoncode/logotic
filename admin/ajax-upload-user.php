<?php
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  error_reporting(E_ALL);
  ini_set('display_errors', 0);
  require_once('../system/config-user.php');

  // ── Helpers para color dominante del SVG ──
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
  function svgDominantColor($content) {
      if (!$content) return null;
      preg_match_all('/(fill|stroke)=["\']([^"\']+)["\']/', $content, $m);
      preg_match_all('/(fill|stroke):\s*([#a-zA-Z][^;"\'\s]+)/', $content, $sm);
      $colors = array_merge($m[2], $sm[2]);
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
      $hex = array_key_first($freq);
      $colorMap = [
          'red'=>['#e63946','#ff0000','#cc0000','#ff3333','#dc143c','#b22222','#ff4500','#ff6347','#cd5c5c'],
          'blue'=>['#1d7af3','#0000ff','#0066cc','#1e90ff','#4169e1','#00008b','#add8e6','#87ceeb','#6495ed'],
          'green'=>['#2dc653','#008000','#00ff00','#228b22','#32cd32','#90ee90','#006400','#3cb371','#66bb6a'],
          'yellow'=>['#f4d03f','#ffff00','#ffd700','#ffa500','#ffeb3b','#ffc107','#ff9800'],
          'orange'=>['#f18d35','#ff6600','#ff8c00','#ff7043','#ff5722'],
          'purple'=>['#8b5cf6','#800080','#9b59b6','#6a0dad','#9c27b0','#673ab7','#7b1fa2'],
          'pink'=>['#ec4899','#ff69b4','#ff1493','#db7093','#e91e63','#f06292'],
          'cyan'=>['#06b6d4','#00bcd4','#00ffff','#40e0d0','#00ced1','#20b2aa'],
          'black'=>['#000000','#111111','#1a1a1a','#222222','#333333','#0d0d0d'],
          'white'=>['#ffffff','#f0f0f0','#fafafa','#eeeeee','#e0e0e0'],
      ];
      return $hex ? svgNearestBase($hex, $colorMap) : null;
  }

  //$productDetails = $product->details($_REQUEST['id']);
  $output = array('error' => false);
  $id_login = $crypt->decrypt($_SESSION['uid'], 'USER'); //id de user desencriptado
  //echo $id_login;
  $eemail = $setting['mail_admin'];
  $eemail_user = $userDetails['email']; //correo del usuario
  


  //recorre todos los archivos cargados
  if (isset($_FILES['file'])) {

    $vector = $_FILES['file']['name']; //título del archivo vector
    $tmp_name   = $_FILES['file']['tmp_name'];
    $vect_name = Product::getFileExtension($vector);  //explode, separa nombre de extensión (nom - png)
    $clean_name = Product::formatName($vect_name[0]);
    $slug_vect = $clean_name . '-' . time() . '-' . 'logotic'; //slug (nombre + tiempo + logotic)
    $join_name_vec = $slug_vect . '-brand' . '.' . end($vect_name); //une slug con extensión 1[png]
    $imgn = $join_name_vec; //slug completo

    $template_vect = $slug_vect . '-tmpl' . '.' . end($vect_name);

    $new_image_name1 = $imgn;

    $categ = $_POST['cat_id'];
    if ($categ == 1) {
      //brand logo
      move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $new_image_name1 . '');
    }else{
      //logo template
      move_uploaded_file($tmp_name, '../system/assets/uploads/vector-files/' . $template_vect . '');
    }
    

    /*obtiene el nombre del archivo*/
    $origen_name = $_FILES['file']['name'];
    $filename = pathinfo($origen_name, PATHINFO_FILENAME);
    $clean_file = preg_replace("/[^a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ.\']/", " ", $filename);
    $strlower = mb_strtolower($clean_file,'UTF-8');
    $final_name = ucfirst($strlower);

    $id_admin = $id_login; //admin
    $filename_tl = $final_name;
    $slug = Product::formatName($filename_tl);//slug del item
    $cat_id = $_POST['cat_id'];
    $scat_id = (isset($_POST['subcat']) ? $_POST['subcat'] : null);
    $date = date("Y-m-d");

    // ── Calcular hash y color del SVG guardado ──
    $savedSvg = ($categ == 1) ? $new_image_name1 : $template_vect;
    $svgPath  = '../system/assets/uploads/vector-files/' . $savedSvg;
    $file_hash = null;
    $dominant_color = null;
    if (file_exists($svgPath)) {
        $svgContent = file_get_contents($svgPath);
        if ($svgContent !== false) {
            $file_hash = hash('sha256', $svgContent);
            $dominant_color = svgDominantColor($svgContent);
        }
    }

    $sql_upload = $DB_con->prepare("INSERT INTO " . PFX . "products (submit_user_id, slug_lg, name, cat_id, subc_id, icon_img, created, modified, status, file_hash, dominant_color) VALUES (:id_admin, :slug, :name2, :cat_id, :scat_id, :icon_img, :created, :modified, 'pending', :file_hash, :dominant_color)");

    $sql_upload->bindparam(":id_admin", $id_admin);
    $sql_upload->bindparam(":slug", $slug);
    $sql_upload->bindparam(":name2", $filename_tl);
    //$sql_upload->bindparam(":description", $description);
    $sql_upload->bindparam(":cat_id", $cat_id);
    $sql_upload->bindparam(":scat_id", $scat_id);

    if ($categ == 1) {
      $sql_upload->bindparam(":icon_img", $new_image_name1);
    }else{
      $sql_upload->bindparam(":icon_img", $template_vect);
    }

    //$sql_upload->bindparam(":tags", $tags);
    $sql_upload->bindparam(":created", $date);
    $sql_upload->bindparam(":modified", $date);
    $sql_upload->bindparam(":file_hash", $file_hash);
    $sql_upload->bindparam(":dominant_color", $dominant_color);


    if ($sql_upload->execute()) {

      $post_data = $_POST;
      $post_file = $_FILES;
      $id['id'] = $DB_con->lastInsertId();
      $data = array_merge($id, $post_file, $post_data );
      //sleep(3);
      echo json_encode($data);

      //agregar un update
      //obtener ultimo lastid de insert
      //actualizar gen_id
    } else {
      echo "Error: " . $sql_upload->error;
      //$output['error'] = true;
      //$output['message'] = 'Cannot update member';
    }

  //}
}
$result = $user->newLogo($eemail, $eemail_user); //tarda en enviar después del resto de la carga, tratar de usar js en la siguiente actualización


} else {
  header('location: ../index.php');
}