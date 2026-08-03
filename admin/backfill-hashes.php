<?php
// ═══════════════════════════════════════════════════════════
// backfill-hashes.php — ARCHIVO TEMPORAL
// Rellena la columna file_hash de los logos existentes, por tandas.
// BORRAR este archivo cuando termines de usarlo.
// Colócalo en: admin/backfill-hashes.php
// ═══════════════════════════════════════════════════════════
ini_set('display_errors', 0);
error_reporting(E_ALL);
set_time_limit(120);

require_once('../system/config-admin.php');

$batchSize = 200;   // cuántos procesa por tanda
$vectorDir = __DIR__ . '/../system/assets/uploads/vector-files/';

// Procesar tanda si se pidió
$processed = 0;
$failed = 0;
$failedList = [];

if (isset($_POST['run'])) {
    // Traer logos SIN hash todavía
    $stmt = $DB_con->prepare("
        SELECT id, icon_img FROM " . PFX . "products
        WHERE (file_hash IS NULL OR file_hash = '')
        LIMIT :lim
    ");
    $stmt->bindValue(':lim', $batchSize, PDO::PARAM_INT);
    $stmt->execute();
    $logos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $upd = $DB_con->prepare("UPDATE " . PFX . "products SET file_hash = :hash WHERE id = :id");

    foreach ($logos as $lg) {
        $path = $vectorDir . $lg['icon_img'];
        if (empty($lg['icon_img']) || !file_exists($path)) {
            // Archivo faltante: marcar con un hash especial para no reintentarlo infinitamente
            $upd->execute([':hash' => 'MISSING-FILE', ':id' => $lg['id']]);
            $failed++;
            $failedList[] = $lg['id'] . ' (' . ($lg['icon_img'] ?: 'sin archivo') . ')';
            continue;
        }
        $content = file_get_contents($path);
        if ($content === false) {
            $upd->execute([':hash' => 'MISSING-FILE', ':id' => $lg['id']]);
            $failed++;
            continue;
        }
        $hash = hash('sha256', $content);
        $upd->execute([':hash' => $hash, ':id' => $lg['id']]);
        $processed++;
    }
}

// Contar cuántos faltan por procesar
$remaining = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE file_hash IS NULL OR file_hash = ''")->fetchColumn();
$totalDone = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "products WHERE file_hash IS NOT NULL AND file_hash != ''")->fetchColumn();
$totalAll  = (int)$DB_con->query("SELECT COUNT(*) FROM " . PFX . "products")->fetchColumn();
$pct = $totalAll > 0 ? round(($totalDone / $totalAll) * 100) : 100;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Backfill Hashes</title>
    <style>
        body { font-family: -apple-system, sans-serif; background: #0d0f1c; color: #e8eaf0; padding: 3rem 1.5rem; max-width: 640px; margin: 0 auto; }
        h1 { font-size: 1.4rem; }
        .box { background: #13152a; border: 1px solid #2a2d47; border-radius: 14px; padding: 1.5rem; margin: 1.5rem 0; }
        .bar-bg { background: rgba(255,255,255,.08); border-radius: 99px; height: 14px; overflow: hidden; margin: 1rem 0; }
        .bar { background: #d4ff00; height: 100%; transition: width .3s; width: <?php echo $pct; ?>%; }
        .stat { display: flex; justify-content: space-between; padding: .4rem 0; border-bottom: 1px solid #2a2d47; font-size: .9rem; }
        .stat:last-child { border: none; }
        .num { font-weight: 700; }
        button { background: #d4ff00; color: #0d0f1c; border: none; border-radius: 10px; padding: .8rem 1.6rem; font-size: 1rem; font-weight: 700; cursor: pointer; }
        button:disabled { opacity: .4; cursor: default; }
        .done { color: #2dc653; font-weight: 700; }
        .warn { background: rgba(244,208,63,.1); border: 1px solid rgba(244,208,63,.3); color: #f4d03f; border-radius: 10px; padding: 1rem; font-size: .85rem; margin-top: 1rem; }
        .muted { color: #8b8fa3; font-size: .82rem; }
        code { background: rgba(255,255,255,.08); padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>🔑 Backfill Logo Hashes</h1>
    <p class="muted">Temporary tool. Fills the <code>file_hash</code> column for existing logos, <?php echo $batchSize; ?> at a time. Delete this file when done.</p>

    <div class="box">
        <div class="bar-bg"><div class="bar"></div></div>
        <div class="stat"><span>Progress</span><span class="num"><?php echo $pct; ?>%</span></div>
        <div class="stat"><span>Total logos</span><span class="num"><?php echo number_format($totalAll); ?></span></div>
        <div class="stat"><span>With hash</span><span class="num" style="color:#2dc653;"><?php echo number_format($totalDone); ?></span></div>
        <div class="stat"><span>Remaining</span><span class="num" style="color:#f4d03f;"><?php echo number_format($remaining); ?></span></div>
    </div>

    <?php if (isset($_POST['run'])): ?>
        <div class="box">
            <strong>Last batch:</strong>
            <div class="stat"><span>Processed OK</span><span class="num" style="color:#2dc653;"><?php echo $processed; ?></span></div>
            <div class="stat"><span>Missing files</span><span class="num" style="color:#ff6b6b;"><?php echo $failed; ?></span></div>
            <?php if (!empty($failedList)): ?>
                <div class="warn">
                    <strong>Logos with missing files</strong> (marked as MISSING-FILE, won't be reprocessed):<br>
                    <?php echo implode('<br>', array_slice($failedList, 0, 20)); ?>
                    <?php if (count($failedList) > 20) echo '<br>...and ' . (count($failedList) - 20) . ' more'; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($remaining > 0): ?>
        <form method="post">
            <button type="submit" name="run" value="1">
                Process next <?php echo min($batchSize, $remaining); ?> logos →
            </button>
        </form>
        <p class="muted" style="margin-top:1rem;">Click repeatedly until it reaches 0 remaining. Each click processes one batch.</p>
    <?php else: ?>
        <div class="box">
            <p class="done">✓ All done! Every logo has a hash.</p>
            <p class="muted">You can now <strong>delete this file</strong> (<code>admin/backfill-hashes.php</code>). Duplicate detection is active for new uploads.</p>
        </div>
    <?php endif; ?>
</body>
</html>