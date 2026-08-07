<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once('../system/config-admin.php');

if (!$auth->is_loggedin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$block = $_GET['block'] ?? '';

// ══════════════════════════════════════════════════════════
// TOP LOGOS — bar horizontal (top 12, con filtro de periodo)
// ══════════════════════════════════════════════════════════
if ($block === 'top_logos') {
    $period = $_GET['period'] ?? 'all';
    $dateFilter = '';
    switch ($period) {
        case 'day':   $dateFilter = " AND d.date_created >= CURDATE() - INTERVAL 1 DAY";   break;
        case 'week':  $dateFilter = " AND d.date_created >= CURDATE() - INTERVAL 7 DAY";   break;
        case 'month': $dateFilter = " AND d.date_created >= CURDATE() - INTERVAL 30 DAY";  break;
        case 'year':  $dateFilter = " AND d.date_created >= CURDATE() - INTERVAL 365 DAY"; break;
        // 'all' → sin filtro
    }

    $rows = $DB_con->query("
        SELECT p.name, COUNT(*) AS dls
        FROM " . PFX . "downloads d
        INNER JOIN " . PFX . "products p ON d.products_id = p.id
        WHERE p.status = 'approved'" . $dateFilter . "
        GROUP BY p.id
        ORDER BY dls DESC
        LIMIT 12
    ")->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $data   = [];
    foreach ($rows as $r) {
        $labels[] = mb_strlen($r['name']) > 24 ? mb_substr($r['name'], 0, 24) . '…' : $r['name'];
        $data[]   = (int)$r['dls'];
    }

    echo json_encode(['success' => true, 'labels' => $labels, 'data' => $data]);
    exit;
}

// ══════════════════════════════════════════════════════════
// SUBCATEGORÍAS — doughnut (top 10, con filtro de periodo)
// ══════════════════════════════════════════════════════════
if ($block === 'categories') {
    $period = $_GET['period'] ?? 'all';
    $dateFilter = '';
    switch ($period) {
        case 'day':   $dateFilter = " AND d.date_created >= CURDATE() - INTERVAL 1 DAY";   break;
        case 'week':  $dateFilter = " AND d.date_created >= CURDATE() - INTERVAL 7 DAY";   break;
        case 'month': $dateFilter = " AND d.date_created >= CURDATE() - INTERVAL 30 DAY";  break;
        case 'year':  $dateFilter = " AND d.date_created >= CURDATE() - INTERVAL 365 DAY"; break;
        // 'all' → sin filtro
    }

    $rows = $DB_con->query("
        SELECT s.name AS sub_name, COUNT(*) AS dls
        FROM " . PFX . "downloads d
        INNER JOIN " . PFX . "products p ON d.products_id = p.id
        INNER JOIN " . PFX . "subcat s ON p.subc_id = s.id
        WHERE p.status = 'approved'" . $dateFilter . "
        GROUP BY s.id
        ORDER BY dls DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $data   = [];
    foreach ($rows as $r) {
        $labels[] = $r['sub_name'];
        $data[]   = (int)$r['dls'];
    }

    echo json_encode(['success' => true, 'labels' => $labels, 'data' => $data]);
    exit;
}

// ══════════════════════════════════════════════════════════
// NUEVOS USUARIOS — line (últimos 30 días)
// ══════════════════════════════════════════════════════════
if ($block === 'new_users') {
    $rows = $DB_con->query("
        SELECT DATE(created) AS d, COUNT(*) AS c
        FROM " . PFX . "users
        WHERE created >= CURDATE() - INTERVAL 29 DAY
        GROUP BY DATE(created)
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $labels = [];
    $data   = [];
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('j M', strtotime($date));
        $data[]   = (int)($rows[$date] ?? 0);
    }

    echo json_encode(['success' => true, 'labels' => $labels, 'data' => $data]);
    exit;
}

// ══════════════════════════════════════════════════════════
// LOGOS EN TENDENCIA — descargas últimos 7 días vs promedio histórico
// ══════════════════════════════════════════════════════════
if ($block === 'trending') {
    // Descargas de los últimos 7 días por logo
    $recent = $DB_con->query("
        SELECT p.id, p.name, p.slug_lg, COUNT(*) AS recent_dls
        FROM " . PFX . "downloads d
        INNER JOIN " . PFX . "products p ON d.products_id = p.id
        WHERE d.date_created >= CURDATE() - INTERVAL 7 DAY
          AND p.status = 'approved'
        GROUP BY p.id
        HAVING recent_dls >= 3
        ORDER BY recent_dls DESC
        LIMIT 40
    ")->fetchAll(PDO::FETCH_ASSOC);

    $trending = [];
    foreach ($recent as $r) {
        // Total histórico del logo
        $stmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "downloads WHERE products_id = :id");
        $stmt->execute([':id' => $r['id']]);
        $total = (int)$stmt->fetchColumn();

        // Promedio semanal histórico estimado: total repartido en semanas desde su creación
        $stmtC = $DB_con->prepare("SELECT MIN(date_created) FROM " . PFX . "downloads WHERE products_id = :id");
        $stmtC->execute([':id' => $r['id']]);
        $firstDl = $stmtC->fetchColumn();

        $weeks = 1;
        if ($firstDl) {
            $days = (time() - strtotime($firstDl)) / 86400;
            $weeks = max(1, $days / 7);
        }
        $avgWeekly = $total / $weeks;

        // Factor de tendencia: descargas recientes vs promedio semanal
        $factor = $avgWeekly > 0 ? ($r['recent_dls'] / $avgWeekly) : 0;

        $trending[] = [
            'name'    => $r['name'],
            'slug'    => $r['slug_lg'],
            'id'      => $r['id'],
            'recent'  => (int)$r['recent_dls'],
            'factor'  => round($factor, 1),
        ];
    }

    // Ordenar por factor de tendencia (los que más suben respecto a su promedio)
    usort($trending, function($a, $b){ return $b['factor'] <=> $a['factor']; });
    $trending = array_slice($trending, 0, 10);

    echo json_encode(['success' => true, 'items' => $trending]);
    exit;
}

// ══════════════════════════════════════════════════════════
// BÚSQUEDAS SIN RESULTADOS — lista (lo que la gente busca y no encuentra)
// ══════════════════════════════════════════════════════════
if ($block === 'no_results') {
    $rows = $DB_con->query("
        SELECT term, search_count, last_searched
        FROM " . PFX . "search_logs
        WHERE results_count = 0
        ORDER BY search_count DESC, last_searched DESC
        LIMIT 15
    ")->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $r) {
        $items[] = [
            'term'  => $r['term'],
            'count' => (int)$r['search_count'],
        ];
    }

    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

// ══════════════════════════════════════════════════════════
// SUBCATEGORÍAS: DEMANDA vs CONTENIDO — top 5 (histórico)
// (descargas por subcategoría vs cantidad de logos en ella)
// ══════════════════════════════════════════════════════════
if ($block === 'cat_demand') {
    // Descargas por subcategoría
    $dlRows = $DB_con->query("
        SELECT s.id, s.name AS sub_name, COUNT(*) AS dls
        FROM " . PFX . "downloads d
        INNER JOIN " . PFX . "products p ON d.products_id = p.id
        INNER JOIN " . PFX . "subcat s ON p.subc_id = s.id
        WHERE p.status = 'approved'
        GROUP BY s.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Cantidad de logos por subcategoría
    $countRows = $DB_con->query("
        SELECT subc_id, COUNT(*) AS logos
        FROM " . PFX . "products
        WHERE status = 'approved'
        GROUP BY subc_id
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    $items = [];
    foreach ($dlRows as $r) {
        $logos = (int)($countRows[$r['id']] ?? 0);
        $dls   = (int)$r['dls'];
        $ratio = $logos > 0 ? round($dls / $logos, 1) : 0;
        $items[] = [
            'cat'   => $r['sub_name'],
            'dls'   => $dls,
            'logos' => $logos,
            'ratio' => $ratio,
        ];
    }

    // Ordenar por ratio (mayor demanda por logo = mejor oportunidad)
    usort($items, function($a, $b){ return $b['ratio'] <=> $a['ratio']; });
    $items = array_slice($items, 0, 5);

    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

// ══════════════════════════════════════════════════════════
// LOGOS SIN DESCARGAS (0) — publicados que nunca se descargaron
// ══════════════════════════════════════════════════════════
if ($block === 'zero_downloads') {
    $rows = $DB_con->query("
        SELECT p.id, p.name, p.slug_lg, p.created
        FROM " . PFX . "products p
        LEFT JOIN " . PFX . "downloads d ON d.products_id = p.id
        WHERE p.status = 'approved'
        GROUP BY p.id
        HAVING COUNT(d.id) = 0
        ORDER BY p.created DESC
        LIMIT 15
    ")->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $r) {
        $items[] = [
            'name' => $r['name'],
            'slug' => $r['slug_lg'],
            'id'   => $r['id'],
            'date' => $r['created'] ? date('j M Y', strtotime($r['created'])) : '',
        ];
    }
    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

// ══════════════════════════════════════════════════════════
// LOGOS CON POCAS DESCARGAS (1-4)
// ══════════════════════════════════════════════════════════
if ($block === 'few_downloads') {
    $rows = $DB_con->query("
        SELECT p.id, p.name, p.slug_lg, COUNT(d.id) AS dls
        FROM " . PFX . "products p
        INNER JOIN " . PFX . "downloads d ON d.products_id = p.id
        WHERE p.status = 'approved'
        GROUP BY p.id
        HAVING dls BETWEEN 1 AND 4
        ORDER BY dls ASC, p.created DESC
        LIMIT 15
    ")->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($rows as $r) {
        $items[] = [
            'name' => $r['name'],
            'slug' => $r['slug_lg'],
            'id'   => $r['id'],
            'dls'  => (int)$r['dls'],
        ];
    }
    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

// ══════════════════════════════════════════════════════════
// SUBCATEGORÍAS CON PEOR RATIO — menor descargas/logo (mín. 3 logos)
// ══════════════════════════════════════════════════════════
if ($block === 'worst_subcat') {
    // Descargas por subcategoría
    $dlRows = $DB_con->query("
        SELECT p.subc_id, COUNT(d.id) AS dls
        FROM " . PFX . "products p
        LEFT JOIN " . PFX . "downloads d ON d.products_id = p.id
        WHERE p.status = 'approved'
        GROUP BY p.subc_id
    ")->fetchAll(PDO::FETCH_KEY_PAIR);

    // Logos por subcategoría + nombre
    $subRows = $DB_con->query("
        SELECT s.id, s.name, COUNT(p.id) AS logos
        FROM " . PFX . "subcat s
        INNER JOIN " . PFX . "products p ON p.subc_id = s.id
        WHERE p.status = 'approved'
        GROUP BY s.id
        HAVING logos >= 3
    ")->fetchAll(PDO::FETCH_ASSOC);

    $items = [];
    foreach ($subRows as $r) {
        $dls   = (int)($dlRows[$r['id']] ?? 0);
        $logos = (int)$r['logos'];
        $ratio = $logos > 0 ? round($dls / $logos, 1) : 0;
        $items[] = [
            'cat'   => $r['name'],
            'dls'   => $dls,
            'logos' => $logos,
            'ratio' => $ratio,
        ];
    }

    // Ordenar por ratio ASCENDENTE (los peores primero)
    usort($items, function($a, $b){ return $a['ratio'] <=> $b['ratio']; });
    $items = array_slice($items, 0, 8);

    echo json_encode(['success' => true, 'items' => $items]);
    exit;
}

// ══════════════════════════════════════════════════════════
// LOGOS POR SUBCATEGORÍA — bar horizontal (inventario de contenido)
// filtro: all (todas) / least (las 10 con menos logos)
// ══════════════════════════════════════════════════════════
if ($block === 'logos_per_subcat') {
    $filter = $_GET['filter'] ?? 'least';
    // least → las 15 con menos logos (ASC) ; most → las 15 con más (DESC)
    $order = $filter === 'most' ? 'DESC' : 'ASC';

    $rows = $DB_con->query("
        SELECT s.name AS sub_name, COUNT(p.id) AS logos
        FROM " . PFX . "subcat s
        INNER JOIN " . PFX . "products p ON p.subc_id = s.id
        WHERE p.status = 'approved'
        GROUP BY s.id
        ORDER BY logos " . $order . "
        LIMIT 15
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Para 'most', invertir para que el gráfico quede ascendente visualmente
    if ($filter === 'most') {
        $rows = array_reverse($rows);
    }

    $labels = [];
    $data   = [];
    foreach ($rows as $r) {
        $labels[] = mb_strlen($r['sub_name']) > 24 ? mb_substr($r['sub_name'], 0, 24) . '…' : $r['sub_name'];
        $data[]   = (int)$r['logos'];
    }

    echo json_encode(['success' => true, 'labels' => $labels, 'data' => $data]);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Unknown block']);