<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

require_once('system/config-global.php');

$slug = $_GET['slug'] ?? null;
if (!$slug) { header('Location: index.php'); exit; }

// Buscar página por slug
$stmt = $DB_con->prepare("SELECT * FROM " . PFX . "custompages WHERE slug_page = :slug AND active = 1");
$stmt->execute([':slug' => $slug]);
$details = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$details) {
    header("HTTP/1.0 404 Not Found");
    $pageTitle = 'Page not found';
    require_once('system/assets/header.php');
    echo '<div style="max-width:600px;margin:5rem auto;text-align:center;padding:0 1.5rem;">
            <h1 style="font-size:2rem;color:var(--text-primary);">Page not found</h1>
            <p style="color:var(--text-muted);margin-top:.5rem;">The page you\'re looking for doesn\'t exist or was removed.</p>
            <a href="' . $setting['website_url'] . '" style="color:var(--accent);text-decoration:none;margin-top:1rem;display:inline-block;">← Back home</a>
          </div>';
    require_once('system/assets/footer.php');
    exit;
}

// SEO
$metaRobots = "<meta name='robots' content='index, follow' />\n";
$canonical  = $setting['website_url'] . '/page/' . $details['slug_page'] . '/';
$pageTitle  = !empty($details['meta_title']) ? $details['meta_title'] : $details['title'];
$pageMeta   = !empty($details['meta_desc']) ? $details['meta_desc'] : ($details['excerpt'] ?? '');

// Control de acceso: si es nivel 1, requiere login
$requiresLogin = ($details['level'] == 1);
$canView = !$requiresLogin || $user->is_loggedin();

require_once('system/assets/header.php');
?>

<style>
.page-wrap { padding: 3.5rem 0 4rem; }
.page-container { max-width: 760px; margin: 0 auto; padding: 0 1.5rem; }

.page-header { text-align: center; margin-bottom: 2.5rem; }
.page-title {
    font-size: 2.6rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.15;
    letter-spacing: -.02em;
    margin: 0 0 1rem;
}
.page-excerpt {
    font-size: 1.05rem;
    color: var(--text-muted);
    line-height: 1.6;
    max-width: 560px;
    margin: 0 auto 1.5rem;
}
.page-date {
    font-size: .8rem;
    color: var(--text-muted);
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
    display: inline-block;
}

/* Cover */
.page-cover { width: 100%; max-width: 900px; margin: 0 auto 3rem; padding: 0 1.5rem; }
.page-cover img {
    width: 100%;
    border-radius: 24px;
    aspect-ratio: 16/9;
    object-fit: cover;
}

/* Contenido */
.page-content-wrap { max-width: 720px; margin: 0 auto; padding: 0 1.5rem; }
.page-content {
    font-size: 1.05rem;
    line-height: 1.8;
    color: var(--text-secondary);
}
.page-content h2 { font-size: 1.6rem; font-weight: 700; color: var(--text-primary); margin: 2.5rem 0 1rem; }
.page-content h3 { font-size: 1.3rem; font-weight: 700; color: var(--text-primary); margin: 2rem 0 .75rem; }
.page-content p { margin-bottom: 1.5rem; }
.page-content img { max-width: 100%; border-radius: 12px; margin: 1.5rem 0; }
.page-content a { color: var(--accent); }
.page-content a:hover { text-decoration: underline; }
.page-content ul, .page-content ol { margin: 0 0 1.5rem 1.5rem; }
.page-content li { margin-bottom: .5rem; }
.page-content blockquote {
    border-left: 3px solid var(--accent);
    margin: 1.5rem 0;
    padding: .75rem 1.5rem;
    background: rgba(212,255,0,.04);
    border-radius: 0 8px 8px 0;
    color: var(--text-muted);
    font-style: italic;
}
.page-content table { width: 100%; border-collapse: collapse; margin: 1.5rem 0; font-size: .9rem; }
.page-content table th, .page-content table td { padding: .6rem 1rem; border: 1px solid var(--border); text-align: left; }
.page-content table th { background: rgba(212,255,0,.06); color: var(--text-primary); font-weight: 600; }

/* Login gate */
.page-gate {
    max-width: 480px;
    margin: 3rem auto;
    text-align: center;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    padding: 3rem 2rem;
}
.page-gate i { font-size: 2.5rem; color: var(--accent); margin-bottom: 1rem; display: block; }
.page-gate h3 { font-size: 1.2rem; color: var(--text-primary); margin-bottom: .5rem; }
.page-gate p { color: var(--text-muted); font-size: .9rem; margin-bottom: 1.5rem; }
.page-gate a {
    display: inline-block;
    background: var(--accent);
    color: #0d0f1c;
    font-weight: 700;
    font-size: .88rem;
    padding: .65rem 1.5rem;
    border-radius: 99px;
    text-decoration: none;
    transition: var(--transition);
}
.page-gate a:hover { background: #bfe600; }

@media (max-width: 800px) {
    .page-title { font-size: 1.8rem; }
}
</style>

<div class="page-wrap">

    <!-- Header -->
    <div class="page-container">
        <div class="page-header">
            <h1 class="page-title"><?php echo htmlspecialchars($details['title']); ?></h1>
            <?php if (!empty($details['excerpt'])): ?>
                <p class="page-excerpt"><?php echo htmlspecialchars($details['excerpt']); ?></p>
            <?php endif; ?>
            <?php if (!empty($details['indate'])): ?>
                <div class="page-date">
                    <i class="fa-regular fa-calendar" style="margin-right:.3rem;"></i>
                    Last updated <?php echo date('M j, Y', strtotime($details['modified'] ?: $details['indate'])); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cover (opcional) -->
    <?php if (!empty($details['cover_img'])): ?>
    <div class="page-cover">
        <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/pages/<?php echo $details['cover_img']; ?>"
             alt="<?php echo htmlspecialchars($details['title']); ?>">
    </div>
    <?php endif; ?>

    <!-- Contenido -->
    <div class="page-content-wrap">
        <?php if ($canView): ?>
            <div class="page-content">
                <?php echo $details['content']; ?>
            </div>
        <?php else: ?>
            <div class="page-gate">
                <i class="fa-regular fa-lock"></i>
                <h3>Members only</h3>
                <p>You need to be logged in to view this page.</p>
                <a href="<?php echo $setting['website_url']; ?>/user/login.php?redirect=<?php echo urlencode($canonical); ?>">
                    Sign in to continue
                </a>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once('system/assets/footer.php'); ?>