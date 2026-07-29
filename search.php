<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$pageTitle = 'Search Results';
$metaRobots = "<meta name='robots' content='noindex, nofollow' />";
require_once('system/config-global.php');
require_once('system/assets/header.php');
require("system/classes/class.search.php");
function clean($string)
{
  return trim(strip_tags(htmlspecialchars($string, ENT_QUOTES, 'UTF-8')));
}
$s = new Search($DB_con);
?>
<main role="main">

  <?php
  $keyword = null;
  $count = 0;
  if ($s->get("key")) {
    $keyword = strip_tags(trim($_GET['key']));
    $keyword = substr($keyword, 0, 20);
    $found   = $s->search($keyword);
    $count   = count($found);

    // ── Registrar la búsqueda (una vez por sesión y término) ──
    $termClean = strtolower(trim($keyword));

    if (mb_strlen($termClean) >= 3) {
        // Clave única de esta búsqueda en la sesión
        if (!isset($_SESSION['searched_terms'])) {
            $_SESSION['searched_terms'] = [];
        }

        // Solo contar si NO se ha registrado este término en esta sesión
        // en los últimos 30 minutos (evita refrescos y repeticiones)
        $termKey = md5($termClean);
        $now = time();
        $alreadyCounted = isset($_SESSION['searched_terms'][$termKey])
                          && ($now - $_SESSION['searched_terms'][$termKey]) < 1800; // 30 min

        if (!$alreadyCounted) {
            $_SESSION['searched_terms'][$termKey] = $now;  // marcar como contado

            try {
                $chkS = $DB_con->prepare("SELECT id FROM " . PFX . "search_logs WHERE term = :t LIMIT 1");
                $chkS->execute([':t' => $termClean]);
                $sid = $chkS->fetchColumn();

                if ($sid) {
                    $DB_con->prepare("UPDATE " . PFX . "search_logs
                        SET search_count = search_count + 1,
                            results_count = :rc,
                            last_searched = NOW()
                        WHERE id = :id")
                        ->execute([':rc' => $count, ':id' => $sid]);
                } else {
                    $DB_con->prepare("INSERT INTO " . PFX . "search_logs
                        (term, results_count, search_count, status, first_searched, last_searched)
                        VALUES (:t, :rc, 1, 'pending', NOW(), NOW())")
                        ->execute([':t' => $termClean, ':rc' => $count]);
                }
            } catch (Throwable $e) {
                // No romper la búsqueda si falla el registro
            }
        } else {
            // Aunque no cuente, actualiza el results_count por si cambió
            try {
                $DB_con->prepare("UPDATE " . PFX . "search_logs
                    SET results_count = :rc, last_searched = NOW()
                    WHERE term = :t")
                    ->execute([':rc' => $count, ':t' => $termClean]);
            } catch (Throwable $e) {}
        }
    }
  }
  ?>
  <!-- Masthead -->
  <header class="bg-dark text-light text-left mb-3 mt-0 p-4 rounded-0 box-shadow">
      <div class="overlay rounded-0 box-shadow"></div>
      <div class="container">
        <h1 class="mb-1 font-weight-bold"><?php echo $count; ?> <?php echo $l['results_for'] ?> [<?php echo $keyword; ?>]</h1>
      </div>
    </header>
    <br>

  <div class="container">
    <div class="row p-15">
      <?php require_once 'system/assets/sidebar.php'; ?>

      <div class="col-lg-9">
        <div id="dynamic-posts3"></div>
        <div id="ajax-loader-search" style="display:none;">
          <img src="<?php echo $setting['website_url'] . "/system/assets/uploads/img/loader.gif" ?>" width="30px" style="display: block; margin: 0px auto;">
        </div>
        <div style="text-align: center; margin: 1.5rem 0;">
          <button id="load-search" class="btn-upload" style="display:none; margin: 0 auto;">
            <i class="fa-regular fa-arrow-down"></i> Load more
          </button>
        </div>
      </div>
    </div>
  </div>
</main>

<script type="text/javascript">
  $(document).ready(function() {
    var page_num = 1;
    var loading = false;
    var no_more = false;

    var offset = 0;

    load_popular(page_num);

    $(document).on('click', '#load-search', function() {
      if (loading || no_more) return;
      page_num++;
      load_popular(page_num);
    });



    function load_popular(page_num) {
      loading = true;
      $('#load-search').hide();
      $('#ajax-loader-search').show();

      $.ajax({
        url: "<?php echo $setting['website_url']; ?>" + "/search-logo.php?key=" + "<?php echo $keyword; ?>",
        type: "post",
        data: {
          page_num: page_num,
          offset: offset
        }
      }).done(function(data) {
        loading = false;
        $('#ajax-loader-search').hide();

        if ($.trim(data) === '') {
          no_more = true;
          $('#load-search').hide();
        } else {
          $("#dynamic-posts3").append(data);
          offset += 24;
          if (offset >= 100) {
            no_more = true;
            $('#load-search').hide();
          } else {
            $('#load-search').show();
          }
        }
      }).fail(function() {
        loading = false;
        $('#ajax-loader-search').hide();
        $('#load-search').show();
      });
    }
  });
</script>

<?php require_once('system/assets/footer.php'); ?>