<?php
// El panel de usuario no debe indexarse
$metaRobots = "<meta name='robots' content='noindex, nofollow' />\n";

// Reutiliza el header del sitio (navbar, mega menu, meta, CSS, One Tap)
require_once($_SERVER['DOCUMENT_ROOT'] . '/logotic/system/assets/header.php');
?>

<!-- ── Panel de usuario ── -->
<style>
.user-panel {
    padding: 2rem 0 4rem;
}

.user-panel-head {
    margin-bottom: 1.5rem;
}

.user-panel-head h1 {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0;
}

.user-panel-head p {
    color: var(--text-muted);
    font-size: .88rem;
    margin: .25rem 0 0;
}

.user-panel-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1.5rem;
    align-items: start;
}

/* Cards reutilizables del panel */
.up-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 1.5rem;
}

.up-card-title {
    font-size: .8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--text-muted);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: .5rem;
}

.up-card-title i { color: var(--accent); }

@media (max-width: 900px) {
    .user-panel-layout { grid-template-columns: 1fr; }
    .usidenav { position: static; }
}
</style>

<div class="user-panel">
    <div class="container">
        <div class="user-panel-layout">
            <!-- Columna izquierda: sidenav -->
            <div>
                <?php require_once('includes/sidenav.php'); ?>
            </div>

            <!-- Columna derecha: contenido -->
            <div class="user-panel-content">