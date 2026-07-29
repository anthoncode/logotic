<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

http_response_code(404);

$pageTitle  = 'Page Not Found';
$metaRobots = "<meta name='robots' content='noindex, follow' />";
require_once('system/config-global.php');
require_once('system/assets/header.php');
?>

<main role="main">
  <section class="nf-wrap">

    <!-- Vectores decorativos de fondo -->
    <div class="nf-shapes" aria-hidden="true">
      <span class="nf-shape nf-shape-1"></span>
      <span class="nf-shape nf-shape-2"></span>
      <span class="nf-shape nf-shape-3"></span>
      <span class="nf-shape nf-shape-4"></span>
    </div>

    <div class="nf-inner">

      <!-- 404 grande con estética de vectores -->
      <div class="nf-code">
        <span class="nf-digit">4</span>
        <span class="nf-zero">
          <svg viewBox="0 0 120 120" class="nf-zero-svg" xmlns="http://www.w3.org/2000/svg">
            <circle cx="60" cy="60" r="52" fill="none" stroke="var(--accent)" stroke-width="6" stroke-dasharray="10 8" />
            <circle cx="60" cy="60" r="30" fill="none" stroke="rgba(255,255,255,.18)" stroke-width="3" />
            <circle cx="60" cy="60" r="5" fill="var(--accent)" />
          </svg>
        </span>
        <span class="nf-digit">4</span>
      </div>

      <h1 class="nf-title">This logo got lost in the vectors</h1>
      <p class="nf-sub">The page you're looking for doesn't exist — but the logo you need might. Try a search.</p>

      <!-- Buscador central -->
      <form action="<?php echo $setting['website_url']; ?>/search.php" method="GET" class="nf-search-form">
        <div class="nf-search-wrap">
          <span class="nf-search-icon"><i class="fa-regular fa-magnifying-glass"></i></span>
          <input type="text" name="key" class="nf-search-input" placeholder="Search for a logo..." minlength="3" autofocus>
          <button type="submit" class="nf-search-btn">
            <i class="fa-regular fa-arrow-right"></i>
          </button>
        </div>
      </form>

      <!-- Acciones -->
      <div class="nf-actions">
        <a href="<?php echo $setting['website_url']; ?>/" class="nf-btn nf-btn-primary">
          <i class="fa-regular fa-house"></i> Go home
        </a>
        <a href="<?php echo $setting['website_url']; ?>/category/1/" class="nf-btn nf-btn-ghost">
          <i class="fa-regular fa-shapes"></i> Browse logos
        </a>
      </div>

    </div>
  </section>
</main>

<style>
.nf-wrap {
    position: relative;
    min-height: 72vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    overflow: hidden;
}

/* Vectores decorativos de fondo */
.nf-shapes { position: absolute; inset: 0; pointer-events: none; z-index: 0; }
.nf-shape {
    position: absolute;
    border-radius: 30%;
    opacity: .06;
    filter: blur(2px);
}
.nf-shape-1 { width: 180px; height: 180px; background: var(--accent); top: 8%; left: 10%; border-radius: 50%; animation: nfFloat 9s ease-in-out infinite; }
.nf-shape-2 { width: 120px; height: 120px; border: 8px solid var(--accent); background: transparent; bottom: 12%; right: 14%; border-radius: 24px; animation: nfFloat 11s ease-in-out infinite reverse; }
.nf-shape-3 { width: 90px;  height: 90px;  background: #fff; opacity: .04; top: 20%; right: 22%; border-radius: 50%; animation: nfFloat 7s ease-in-out infinite; }
.nf-shape-4 { width: 140px; height: 140px; border: 6px dashed var(--accent); background: transparent; bottom: 18%; left: 16%; border-radius: 50%; animation: nfFloat 13s ease-in-out infinite; }

@keyframes nfFloat {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50%      { transform: translateY(-22px) rotate(8deg); }
}

.nf-inner {
    position: relative;
    z-index: 1;
    text-align: center;
    max-width: 620px;
    width: 100%;
}

/* 404 grande */
.nf-code {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    margin-bottom: 1.5rem;
}
.nf-digit {
    font-size: clamp(6rem, 18vw, 11rem);
    font-weight: 800;
    line-height: 1;
    color: var(--text-primary);
    letter-spacing: -.03em;
}
.nf-zero {
    width: clamp(6rem, 18vw, 11rem);
    height: clamp(6rem, 18vw, 11rem);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.nf-zero-svg {
    width: 100%;
    height: 100%;
    animation: nfSpin 18s linear infinite;
}
@keyframes nfSpin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}

.nf-title {
    font-size: clamp(1.3rem, 3.5vw, 1.9rem);
    font-weight: 700;
    color: var(--text-primary);
    margin: 0 0 .6rem;
}
.nf-sub {
    font-size: .95rem;
    color: var(--text-secondary);
    margin: 0 auto 2rem;
    max-width: 440px;
    line-height: 1.6;
}

/* Buscador central */
.nf-search-form { margin-bottom: 1.75rem; }
.nf-search-wrap {
    display: flex;
    align-items: center;
    max-width: 460px;
    margin: 0 auto;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 99px;
    padding: .35rem .35rem .35rem 1.25rem;
    transition: var(--transition);
}
.nf-search-wrap:focus-within {
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(212,255,0,.08);
}
.nf-search-icon { color: var(--text-muted); font-size: .95rem; flex-shrink: 0; }
.nf-search-input {
    flex: 1 1 0;
    min-width: 0;
    width: 0;
    background: transparent;
    border: none;
    outline: none;
    color: var(--text-primary);
    font-size: .95rem;
    padding: .7rem .85rem;
    font-family: 'Poppins', sans-serif;
}
.nf-search-input::placeholder { color: var(--text-muted); }
.nf-search-btn {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--accent);
    color: #0d0f1c;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 1rem;
    transition: var(--transition);
}
.nf-search-btn:hover { transform: scale(1.06); background: #bfe600; }

/* Acciones */
.nf-actions {
    display: flex;
    gap: .75rem;
    justify-content: center;
    flex-wrap: wrap;
}
.nf-btn {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .7rem 1.4rem;
    border-radius: 99px;
    font-size: .88rem;
    font-weight: 600;
    text-decoration: none;
    transition: var(--transition);
}
.nf-btn-primary {
    background: var(--accent);
    color: #0d0f1c;
}
.nf-btn-primary:hover { transform: translateY(-2px); background: #bfe600; }
.nf-btn-ghost {
    background: transparent;
    color: var(--text-primary);
    border: 1px solid var(--border);
}
.nf-btn-ghost:hover { border-color: var(--accent); color: var(--accent); }

@media (max-width: 500px) {
    .nf-code { gap: .2rem; }
    .nf-actions { flex-direction: column; }
    .nf-btn { width: 100%; justify-content: center; }
}
</style>

<?php require_once('system/assets/footer.php'); ?>