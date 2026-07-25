<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ob_start(); //para que redireccione header location

error_reporting(E_ALL);
require_once 'system/config-global.php';
$id         = $_GET['id']; //id y nombre de slug enviado por get
$details    = $product->details($id);
if (is_array($details)) {
  $str_name   = $details['slug_lg']; //url amigable de archivos
}


$po = $id;
$views = explode("/", $po);
$viewsOne = $views[1];
$productid = intval($views[0]); // ✅ solo el número

// ¿Este logo ya está en los favoritos del usuario?
$isSaved = false;
if ($user->is_loggedin()) {
  $uidReal = $crypt->decrypt($_SESSION['uid'], 'USER');
  $chkFav = $DB_con->prepare("SELECT w_id FROM " . PFX . "wishlists WHERE user_id = :uid AND product_id = :pid");
  $chkFav->execute([':uid' => $uidReal, ':pid' => $productid]);
  $isSaved = (bool)$chkFav->fetchColumn();
}

if (is_array($details)) { // is_array solo para php7 o superior, necesita verificar array
  $idItem = $details['id'];
  $slugItem = $details['slug_lg'];
  $slug_item = $setting['website_url'] . '/item/' . $idItem . '/' . $slugItem;
}

//echo $slug_item;

if (isset($details['name'])) {
  $link = $details['id'] . '/' . $details['slug_lg']; // id/slug
  if ($id != $link) {
    echo $slug_item;
    header("Location: $slug_item");
    //die();
  }
  /*  else{
    echo "NO son iguales";
  }*/
}


//$productid = $id;
if (isset($_GET['id'])) {
  //$id         = $_GET['id'];
  $details    = $product->details($id);
  $author     = $product->getAuthor($id);
  $download   = $product->downloadCount($id);

  //$allreviews = $product->getReviews($id);

  if (isset($details['cat_id'])) {
    if ($details['cat_id'] != '0') {
      $cat  = $product->catdetails($details['cat_id']);
      $cat1 = $cat['name'];
      $cat2 = $cat['id'];
    } else {
      $cat1 = $l['no_category'];
    }
  }

  if ($details) {
    //$query = $DB_con->prepare("UPDATE " . PFX . "products SET views = views + 1 WHERE id = ?");
    //$query->execute(array($id));

  } else {
    display_post_not_found($id);
    exit();
  }

  $metaRobots = "<meta name='robots' content='index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1' />
";
  $pageTitle = $details['name'] . ' Logo Free Download [SVG - PNG]';

  $metaDescItem = 'Download <strong>' . $details['name'] . '</strong> logo in vector SVG and transparent PNG formats for free to use in personal and commercial projects. Downloading vector logos with transparent backgrounds is incredibly simple at Logotic. All you need to do is browse through our collection, select the logo that suits your needs, and with a single click, initiate the download. This hassle-free process ensures that you can effortlessly access the resources you require for your projects, saving you time and effort while maintaining the quality and transparency you desire.';


  //$pageContent = strip_tags($details['short_desc']); // striptag limpia sintaxis html
  $pageContent = $metaDescItem;

  $canonical = $setting['website_url'] . '/item/' . $details['id'] . '/' . $slugItem . '/';

  require_once 'system/assets/header.php';

  if (isset($_POST['addtowishlist'])) {
    $addtw = $wishlist->add($_SESSION['uid'], $id);
  }

  /*share button*/
  $n_url = $details['name'];
  $urlShare = $setting['website_url'] . '/item/' . $id . '/';
  //$slugPic = $setting['website_url'] . '/system/assets/uploads/products/' . $details['preview_img'];


?>


  <!-- ─── DOWNLOAD TOAST ─── -->
  <div id="downloadToast">
    <div class="toast-ring-wrap">
      <svg viewBox="0 0 46 46">
        <circle class="ring-track" cx="23" cy="23" r="20" />
        <circle class="ring-progress" cx="23" cy="23" r="20" id="ringProgress" />
      </svg>
      <div class="toast-icon-center">
        <i class="fa-solid fa-arrow-down" id="toastIcon"></i>
      </div>
    </div>
    <div class="toast-body">
      <div class="toast-title" id="toastTitle">Downloading</div>
      <div class="toast-percent" id="toastPercent">0%</div>
      <div class="toast-sub" id="toastSub">Preparing file…</div>
    </div>
    <button class="toast-close" id="toastClose" title="Cerrar"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <div class="container py-5">

    <!-- ═══════════════════════════════════════
             ROW 1: Image + Info side by side
        ════════════════════════════════════════ -->
    <div class="row justify-content-center align-items-start g-4 mb-4">

      <!-- Image Column -->
      <div class="col-lg-6 col-md-12">
        <div class="image-container">

          <div class="action-buttons">

            <!-- Share -->
            <div class="share-wrapper" id="shareWrapper">
              <button class="action-btn btn-share" id="shareBtn" title="Compartir">
                <i class="fa-solid fa-share-nodes"></i>
              </button>
              <div class="social-crescent">
                <button class="social-btn reddit" onclick="shareOn('reddit')"> <i class="fa-brands fa-reddit-alien"></i></button>
                <button class="social-btn twitter" onclick="shareOn('twitter')"> <i class="fa-brands fa-x-twitter"></i></button>
                <button class="social-btn pinterest" onclick="shareOn('pinterest')"><i class="fa-brands fa-pinterest-p"></i></button>
              </div>
            </div>

            <!-- Copy link -->
            <button class="action-btn btn-copy-link" id="copyLinkBtn" title="Copiar enlace">
              <i class="fa-solid fa-link"></i>
              <span class="copy-toast-inline">¡Enlace copiado!</span>
            </button>

            <!-- Bookmark / Save -->
            <button class="action-btn btn-bookmark <?php echo $isSaved ? 'saved' : ''; ?>" id="bookmarkBtn"
              title="<?php echo $isSaved ? 'Quitar de favoritos' : 'Guardar en favoritos'; ?>">
              <i class="<?php echo $isSaved ? 'fa-solid' : 'fa-regular'; ?> fa-bookmark"></i>
            </button>

          </div>

          <div class="image-wrapper">
            <img id="output" class="logo-render card-img-top" alt="<?php echo $details['name']; ?> transparent PNG and vector free" title="Download <?php echo $details['name'] ?>">
          </div>
        </div>
      </div>

      <!-- Info Column -->
      <div class="col-lg-6 col-md-12">
        <div class="info-section px-lg-4">
          <h1 class="nft-title"><?php echo $details['name'] . " logo PNG and Vector"; ?></h1>

          <div class="stats">
            <div class="stat-item">
              <i class="fa-regular fa-eye"></i>
              <span class="stat-value"><?php echo number_format($details['views']); ?></span>
            </div>
            <div class="stat-item" id="downloadCount">
              <i class="fa-solid fa-download"></i>
              <span class="stat-value" id="dlCountVal"> <?php $downloaded = $download['doCount'];
                                                        echo $product->formatCount($download['doCount']); ?></span>
            </div>
          </div>


          <p class="description"><?php echo $details['description']; ?></p>
          <p class="description" id="itemDescription"><?php echo $pageContent; ?></p>


          <div class="owner-card">
            <?php
            $isSystemUpload = ($details['submit_user_id'] == 0);
            $uploaderName   = $isSystemUpload ? $setting['site_name'] : ($author['username'] ?? 'User');
            $uploaderPhoto  = $isSystemUpload ? '' : ($author['profile'] ?? '');
            // Considerar "sin foto" si está vacía o es la imagen por defecto
            $hasPhoto = !$isSystemUpload
              && !empty($uploaderPhoto)
              && strpos($uploaderPhoto, 'default') === false;
            ?>

            <?php if ($isSystemUpload): ?>
              <!-- Subido por el sitio: mostrar logo/favicon o inicial del sitio -->
              <div class="owner-avatar owner-avatar-brand">
                <?php if (!empty($setting['site_favicon'])): ?>
                  <img src="<?php echo $setting['website_url'] . '/system/assets/uploads/img/' . $setting['site_favicon']; ?>"
                    alt="<?php echo htmlspecialchars($setting['site_name']); ?>">
                <?php else: ?>
                  <?php echo strtoupper(mb_substr($setting['site_name'], 0, 1)); ?>
                <?php endif; ?>
              </div>
            <?php elseif ($hasPhoto): ?>
              <!-- Usuario con foto -->
              <div class="owner-avatar">
                <img src="<?php echo htmlspecialchars($uploaderPhoto); ?>"
                  alt="<?php echo htmlspecialchars($uploaderName); ?>">
              </div>
            <?php else: ?>
              <!-- Usuario sin foto: inicial -->
              <div class="owner-avatar owner-avatar-letter">
                <?php echo strtoupper(mb_substr($uploaderName, 0, 1)); ?>
              </div>
            <?php endif; ?>

            <div class="owner-info">
              <div class="owner-label">Uploaded by</div>
              <div class="owner-name"><?php echo htmlspecialchars($uploaderName); ?></div>
            </div>
          </div>

          <div class="download-buttons">
            <button class="btn-download btn-svg" onclick="simulateDownload('SVG', '<?php echo $setting['website_url'] . '/system/download.php?pid=' . $productid ?>')">
              <span>SVG</span>
            </button>
            <button class="btn-download btn-png" onclick="simulateDownload('PNG')"><span>PNG</span></button>
          </div>

          <div class="url-display">
            <div class="url-main-row">
              <span class="url-cdn-badge"><i class="fa-solid fa-bolt"></i> CDN</span>
              <span class="url-px-label">px:</span>
              <div class="url-input-wrap">
                <button class="url-stepper" id="sizeDown" title="Decrease"><i class="fa-solid fa-minus"></i></button>
                <input type="number" id="sizeInput" class="url-size-input" min="10" max="1000" value="50">
                <button class="url-stepper" id="sizeUp" title="Increase"><i class="fa-solid fa-plus"></i></button>
              </div>
              <div class="url-code-wrap">
                <code class="url-text" id="urlText"></code>
              </div>
              <button class="url-copy-btn" id="urlCopyBtn" title="Copiar">
                <i class="fa-regular fa-copy"></i>
              </button>
            </div>
          </div>

          <div class="tags-section">
            <span class="tags-label">Tags:</span>
            <div class="tags">
              <?php
              if ($details['tags']) {
                $links = array();
                $tags_key = explode(',', $details['tags']);
                $count_tags = count($tags_key);
                //echo $count_tags;
                for ($i = 0; $i < $count_tags; $i++) {
                  $links[] = "<a class='tag' href=" . $setting['website_url'] . '/tags/' .  product::formatName($tags_key[$i]) . "> $tags_key[$i]</a>";
                }
                echo implode(" ", $links);
              }
              ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════
             ROW 2: Tabs full width below both cols
        ════════════════════════════════════════ -->
    <div class="row justify-content-center">
      <div class="col-lg-12 col-md-12">
        <div class="bottom-tabs-section">

          <div class="custom-tabs">
            <!-- <button class="custom-tab active" data-tab="bid-history">Related logos</button> -->
            <button class="custom-tab active" data-tab="info">Info</button>
          </div>

          <!-- Bid History Tab -->
          <!-- <div class="tab-content" id="bid-history-content">
                        <div class="bid-history-wrap">
                            <div class="bid-item">
                                <div class="bid-avatar">👤</div>
                                <div class="bid-info">
                                    <div class="bid-user">Mason Woodward</div>
                                    <div class="bid-action">place a bid</div>
                                </div>
                                <div class="bid-time">8 hours ago</div>
                            </div>
                            <div class="bid-item">
                                <div class="bid-avatar">👨</div>
                                <div class="bid-info">
                                    <div class="bid-user">Mason Woodward</div>
                                    <div class="bid-action">bid accepted at 06/10/2021, 3:20 AM</div>
                                </div>
                            </div>
                            <div class="bid-item">
                                <div class="bid-avatar">🙋</div>
                                <div class="bid-info">
                                    <div class="bid-user">Mason Woodward</div>
                                    <div class="bid-action">place a bid</div>
                                </div>
                                <div class="bid-time">8 hours ago</div>
                            </div>
                        </div>
                    </div> -->

          <!-- Info Tab -->
          <div class="tab-content" id="info-content"> <!-- style="display:none;" -->
            <div class="info-grid">
              <div class="info-row">
                <div class="info-label"> <?php echo 'Downloaded'; ?></div>
                <div class="info-value"><?php echo $downloaded = $product->formatCount($download['doCount']); ?></div>


              </div>
              <div class="info-row">
                <div class="info-label"><?php echo $l['category']; ?></div>
                <div class="info-value"><a href="<?php echo $setting['website_url'] . "/category/" . $cat2 . "/" . $cate . "/" ?>" title=""><?php echo $cat1; ?> </a></div>
              </div>
              <div class="info-row">
                <div class="info-label"><?php echo 'File'; ?></div>
                <div class="info-value"><?php
                                        //$setting['website_url'] . '/system/download.php?pid=' . $productid
                                        $file = $setting['website_url'] . '/system/assets/uploads/' . $productid . $details['icon_img'];
                                        $extension = pathinfo($file, PATHINFO_EXTENSION);
                                        $format = strtoupper($extension);
                                        echo "<span class='badge badge-success'>.$format</span>";
                                        ?></div>
              </div>
              <div class="info-row">
                <div class="info-label"><?php echo $l['first_release']; ?></div>
                <div class="info-value"><?php
                                        $created = $details['created'];
                                        $date    = new DateTime($created);
                                        echo $date->format('j F Y'); ?></div>
              </div>
              <div class="info-row">
                <div class="info-label"><?php echo 'Uploader'; ?></div>
                <div class="info-value"> <?php if ($details['submit_user_id'] == 0) {
                                            echo "Logotic";
                                          } else {
                                            $uploader = $author['username'];
                                            echo $uploader;
                                          } ?></div>
              </div>
              <div class="info-row">
                <div class="info-label"><?php echo $l['last_updated']; ?></div>
                <div class="info-value"><?php

                                        $modified = $details['modified'];
                                        $date     = new DateTime($modified);
                                        echo $date->format('j F Y'); ?></div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div><!-- /container-fluid -->






  <main role="main">
    <?php /*if ($user->is_loggedin()) {
      if ($purchases->is_purchased($_SESSION['uid'], $id)) {
        echo '<div class="alert alert-danger mb-0" role="alert">' . $l['youalreadypurch'] . '</div>';
      }
    }*/ ?>
    <?php echo ($details['active'] == '2' ? '<div class="alert alert-danger mb-0" role="alert">' . $l['item_paused'] . '</div>' : ''); ?>



    </div>

    <div id="svg"></div>
    <?php
    $svg_icon_url = $setting['website_url'] . '/system/assets/uploads/vector-files/' . $details['icon_img'];
    //Use file_get_contents() to retrieve the content of the svg
    $svg_icon_content = file_get_contents($svg_icon_url); ?>

    <!-- src="<?php //echo $setting['website_url']; 
              ?>/system/assets/uploads/products/<?php //echo $details['preview_img']; 
                                                ?>"
     -->


    <!--Similar Products-->
    <div class="container mt-5">

      <h2 class="similar-title">Similar Logos</h2>

      <div class="container-logo p-15">
        <?php
        $links = array();
        $tags_key = explode(',', $details['tags']);
        $count_tags = count($tags_key);
        $i = 0;
        $links[] = $tags_key[$i];
        $linksTag = implode(" ", $links);
        $allTags = str_replace(',', '|', $details['tags']); //quita las comas por una barra para la consulta REGEXP

        ?>
        <?php
        $similar = $product->getSimilarProducts($allTags); //antes $linksTag
        foreach ($similar as $row) {
          $str = Product::formatName($row['name']);
          $urlLocal = $setting['website_url'];
          $urlId = $row['id'];
          $urlSlug = $row['slug_lg'];
        ?>






          <div class="logo-row mb-3">
            <div class="cont-img">
              <a href="<?php echo $urlLocal . '/item/' . $urlId . '/' . $urlSlug . '/' ?>">
                <img class="card-logotic-logo" style="background:#fff;" width="100" height="100" title="<?php echo $row['name'] ?>" src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $row['icon_img'] ?>" alt="<?php echo $row['name'] ?>">
                <!-- <?php //if ($row['views_off'] == '0') {
                      ?>
          <div class="post-view">
            <span class="ps-icon fa-light fa-eye"></span>
            <span><?php //echo $row['views']; 
                  ?></span>      
          </div>
          <?php //} else { }
          ?> -->

                <div class="badge-download-pill">
                  <span class="fa-regular fa-download"></span>
                  <span>
                    <?php $ip_item = $row['id'];
                    $download  = $product->downloadCount($ip_item);
                    echo $product->formatCount($download['doCount']);
                    ?>
                  </span>
                </div>

                <?php if ($row['featured'] == 1) { ?>

                  <a class="badge-star-circle" href="#" title="Featured">
                    <span class="circle circ-yellow">
                      <i class="fa-regular fa-star"></i>
                    </span>
                  </a>

                <?php } else if ($row['views'] > 999) { ?>

                  <a class="badge-star-circle" href="#" title="Trending +1000">
                    <span class="circle circ-green">
                      <i class="fa-solid fa-arrow-trend-up"></i>
                    </span>
                  </a>

                <?php } ?>
              </a>
            </div>
          </div>

        <?php } ?>
      </div>
    </div>
    <!-- End Similar Product -->

  </main>


  <script type="text/javascript">
    // 1. Prepare SVG String
    // Extracted from: https://upload.wikimedia.org/wikipedia/commons/f/fd/Ghostscript_Tiger.svg

    let InitialSVGString = `<?php echo $svg_icon_content  ?>`;
    document.getElementById("svg").value = InitialSVGString;

    //$(document).ready(function()
    document.addEventListener('DOMContentLoaded', function()
      //document.addEventListener('DOMContentLoaded', async() =>
      //document.getElementById("generate").addEventListener("click", function()
      {
        //alert('document is ready. I can sleep now');
        // 1. Convert SVG String to DataURL
        let dataURL = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(document.getElementById("svg").value);

        let newWidth = 1000;
        let newHeight = 1000;

        //let mimetype = document.getElementById("output-format").value;
        let mimetype = "image/png";

        GenerateImageBlobFromSVG(dataURL, newWidth, newHeight, mimetype).then(function(blob) {
          let fileURL = window.URL.createObjectURL(blob);
          document.getElementById("output").src = fileURL;



          /*- genera un enlace pero no funciona en firefox
          const pngImage = document.getElementById('output');
          pngImage.src = fileURL;

          // Proporcionar un enlace para descargar la imagen PNG
          const downloadLink = document.createElement('a');
          downloadLink.href = fileURL;
          downloadLink.download = 'generated.png';
          downloadLink.innerText = 'Descargar PNG';
          document.body.appendChild(downloadLink);-*/



        }).catch(function(err) {
          //alert("An error ocurred 5000");
          //en caso de no cargar el rederizado mostrar el svg (original)
          //en firefox algunos archivos no rederizan a png
          var image = document.getElementById("output");
          image.src = `<?php echo $svg_icon_url ?>`;
          //esconde boton si no renderiza en png
          document.getElementById("btn-logo-png").style.display = "none";

        });

      }, false);


    /**
     * Helper function to generate a Blob image of a custom size from a SVGDataURL.
     * 
     * @param SVGDataURL 
     * @param newWidth 
     * @param mimeType 
     * @param quality 
     * @returns Promise
     */
    function GenerateImageBlobFromSVG(SVGDataURL, newWidth, newHeight, mimeType, quality) {
      quality = quality || 1;

      return new Promise(function(resolve, reject) {
        // 1. Create an abstract canvas
        let canvas = document.createElement('canvas');
        let ctx = canvas.getContext("2d");

        // 2. Create an image element to load the SVG
        let img = new Image();

        // 3. Manipulate
        img.onload = function() {
          // Declare initial dimensions of the image
          let originalWidth = img.width;
          let originalHeight = img.height;

          // Declare the new width of the image
          // And calculate the new height to preserve the aspect ratio
          img.width = newWidth;
          img.height = (originalHeight / originalWidth) * newWidth;

          // Set the dimensions of the canvas to the new dimensions of the image
          canvas.width = img.width;
          canvas.height = img.height;

          // Render image in Canvas
          ctx.drawImage(img, 0, 0, img.width, img.height);
          //ctx.drawImage(img, 0, 0, img.width, img.height);

          // Export the canvas to blob
          // You may modify this to export it as a base64 data URL
          canvas.toBlob(function(blob) {
            resolve(blob);
          }, mimeType, quality);
        };

        document.getElementById('btn-logo-png').onclick = function() {
          canvas.toBlob(function(blob) {
            saveAs(blob, "<?php echo $str_name . "-" . strtolower($setting['site_name']); ?>.png");
          });
        }


        // Load the DataURL of the SVG
        img.src = SVGDataURL;

      });
    }
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.0/FileSaver.min.js"></script>

  <script>
    window.onload = function(id) { //contador de visitas
      $.ajax({
        url: "<?php echo $setting['website_url']; ?>/admin/ajax-views-logo.php", //url de paginaci車n infinita
        type: "post",
        data: {
          id: <?php echo $idItem; ?>
        },
        beforeSend: function() {
          return;
        }
      })
    }
  </script>

  <script>
    < script >
      (function() {
        var svgBaseUrl = '<?php echo $setting['website_url'] . '/system/assets/uploads/vector-files/' . $details['icon_img']; ?>';
        var logoName = '<?php echo htmlspecialchars($details['name'], ENT_QUOTES); ?>';

        var sizeInput = document.getElementById('sizeInput');
        var urlText = document.getElementById('urlText');
        var sizeUp = document.getElementById('sizeUp');
        var sizeDown = document.getElementById('sizeDown');
        var urlCopyBtn = document.getElementById('urlCopyBtn');

        function getSize() {
          var size = parseInt(sizeInput.value) || 50;
          if (size < 10) size = 10;
          if (size > 1000) size = 1000;
          return size;
        }

        // Solo la URL con parámetros
        function buildUrl() {
          var size = getSize();
          return svgBaseUrl + '?width=' + size + '&height=' + size;
        }

        // Tag <img> completo con alt (para copiar)
        function buildImgTag() {
          var size = getSize();
          return '<img src="' + buildUrl() + '" alt="' + logoName + '" width="' + size + '" height="' + size + '">';
        }

        function updateUrl() {
          urlText.textContent = buildUrl();
        }

        sizeInput.addEventListener('input', updateUrl);

        sizeUp.addEventListener('click', function() {
          var v = parseInt(sizeInput.value) || 50;
          if (v < 1000) {
            sizeInput.value = v + 10;
            updateUrl();
          }
        });
        sizeDown.addEventListener('click', function() {
          var v = parseInt(sizeInput.value) || 50;
          if (v > 10) {
            sizeInput.value = v - 10;
            updateUrl();
          }
        });

        // Copia el tag <img> completo con alt
        urlCopyBtn.addEventListener('click', function() {
          navigator.clipboard.writeText(buildImgTag()).then(function() {
            var icon = urlCopyBtn.querySelector('i');
            var original = icon.className;
            icon.className = 'fa-regular fa-check';
            urlCopyBtn.style.color = 'var(--accent)';
            setTimeout(function() {
              icon.className = original;
              urlCopyBtn.style.color = '';
            }, 1500);
          });
        });

        updateUrl();
      })();
  </script>
<script>
// ── Botón de favoritos ──
(function() {
    var btn = document.getElementById('bookmarkBtn');
    if (!btn) return;

    var isLoggedIn = <?php echo $user->is_loggedin() ? 'true' : 'false'; ?>;
    var productId  = <?php echo (int)$productid; ?>;
    var loginUrl   = '<?php echo $setting['website_url']; ?>/user/login.php';
    var currentUrl = window.location.href;

    btn.addEventListener('click', function(e) {
        e.preventDefault();

        if (!isLoggedIn) {
            window.location.href = loginUrl + '?redirect=' + encodeURIComponent(currentUrl);
            return;
        }

        btn.disabled = true;

        fetch('<?php echo $setting['website_url']; ?>/ajax-wishlist.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=toggle&product_id=' + productId
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            if (!data.success) {
                if (data.reason === 'auth') {
                    window.location.href = loginUrl + '?redirect=' + encodeURIComponent(currentUrl);
                }
                return;
            }

            var icon = btn.querySelector('i');
            if (data.saved) {
                btn.classList.add('saved');
                icon.className = 'fa-solid fa-bookmark';
                btn.title = 'Quitar de favoritos';
                showFavToast('Saved to favorites', true);
            } else {
                btn.classList.remove('saved');
                icon.className = 'fa-regular fa-bookmark';
                btn.title = 'Guardar en favoritos';
                showFavToast('Removed from favorites', false);
            }

            if (window.updateFavCount) window.updateFavCount(data.count, data.saved);
        })
        .catch(function() { btn.disabled = false; });
    });
})();

function showFavToast(message, saved) {
    var t = document.getElementById('favToast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'favToast';
        t.className = 'fav-toast';
        document.body.appendChild(t);
    }
    t.innerHTML = '<i class="fa-solid ' + (saved ? 'fa-bookmark' : 'fa-circle-check') + '"></i> ' + message;
    t.classList.add('show');
    clearTimeout(window._favToastTimer);
    window._favToastTimer = setTimeout(function() { t.classList.remove('show'); }, 2500);
}
</script>

  <?php require_once 'system/assets/footer.php'; ?>

<?php
} else {
  header('Location:index.php');
}

function display_post_not_found($id)
{

  echo "<h1 class='no-item'> Product with ID: $id does not exist! </h1>";
  //header("Location: 404.php");
}
