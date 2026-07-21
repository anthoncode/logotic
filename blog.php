<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once('system/config-global.php');

$catSlug  = $_GET['cat'] ?? null;
$currpage = max(1, (int)($_GET['page'] ?? 1));
$maxres   = 9;
$start    = ($currpage - 1) * $maxres;

// Categoría activa
$activeCat = null;
if ($catSlug) {
    $stmt = $DB_con->prepare("SELECT * FROM " . PFX . "post_categories WHERE slug = :slug AND active = 1");
    $stmt->execute([':slug' => $catSlug]);
    $activeCat = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Posts
$where  = "p.status = 'published'";
$params = [];
if ($activeCat) {
    $where .= " AND p.category_id = :cat_id";
    $params[':cat_id'] = $activeCat['id'];
}

$countStmt = $DB_con->prepare("SELECT COUNT(*) FROM " . PFX . "posts p WHERE $where");
$countStmt->execute($params);
$totalPosts = $countStmt->fetchColumn();
$pages = ceil($totalPosts / $maxres);

$params[':start']  = $start;
$params[':maxres'] = $maxres;
$stmt = $DB_con->prepare("
    SELECT p.*, pc.name as cat_name, pc.slug as cat_slug
    FROM " . PFX . "posts p
    LEFT JOIN " . PFX . "post_categories pc ON p.category_id = pc.id
    WHERE $where
    ORDER BY p.created DESC
    LIMIT :start, :maxres
");
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorías para sidebar
$postCats = $DB_con->query("
    SELECT pc.*, COUNT(p.id) as post_count
    FROM " . PFX . "post_categories pc
    LEFT JOIN " . PFX . "posts p ON p.category_id = pc.id AND p.status = 'published'
    WHERE pc.active = 1
    GROUP BY pc.id
    ORDER BY pc.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Posts recientes para sidebar
$recentPosts = $DB_con->query("
    SELECT id, title, slug, cover_img, created
    FROM " . PFX . "posts
    WHERE status = 'published'
    ORDER BY created DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$metaRobots = "<meta name='robots' content='index, follow' />\n";
$pageTitle  = $activeCat ? $activeCat['name'] . ' — Blog' : 'Blog — ' . $setting['site_name'];
$pageMeta   = $activeCat
    ? ($activeCat['description'] ?? 'Browse ' . $activeCat['name'] . ' articles')
    : 'Logo design articles, brand identity tips and logo history on ' . $setting['site_name'];

require_once('system/assets/header.php');
?>


<div class="blog-wrap">
    <!-- Hero -->
    <div class="blog-hero">
        <div class="container">
            <?php if ($activeCat): ?>
                <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.4rem;">
                    <a href="<?php echo $setting['website_url']; ?>/blog/" style="color:var(--accent);text-decoration:none;">Blog</a>
                    <i class="fa-regular fa-chevron-right" style="font-size:.6rem;margin:0 .3rem;"></i>
                    <?php echo htmlspecialchars($activeCat['name']); ?>
                </div>
                <h1><?php echo htmlspecialchars($activeCat['name']); ?></h1>
                <?php if ($activeCat['description']): ?>
                    <p><?php echo htmlspecialchars($activeCat['description']); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <h1>Blog <span style="color:var(--accent);">& Articles</span></h1>
                <p>Logo design, brand identity tips and the history behind iconic logos.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="container">
        <div class="blog-layout" style="display:grid;grid-template-columns:1fr 280px;gap:2rem;align-items:start;">

            <!-- Posts grid -->
            <div>
                <?php if (empty($posts)): ?>
                    <div class="blog-empty">
                        <i class="fa-regular fa-newspaper"></i>
                        <p>No posts found<?php echo $activeCat ? ' in this category' : ''; ?>.</p>
                        <?php if ($activeCat): ?>
                            <a href="<?php echo $setting['website_url']; ?>/blog/" style="color:var(--accent);">
                                View all posts →
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="blog-grid">
                        <?php foreach ($posts as $post):
                            $postUrl = $setting['website_url'] . '/blog/' . $post['slug'] . '/';
                        ?>
                        <a href="<?php echo $postUrl; ?>" class="post-card">
                            <?php if ($post['cover_img']): ?>
                                <div class="post-card-cover">
                                    <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/blog/covers/<?php echo $post['cover_img']; ?>"
                                         alt="<?php echo htmlspecialchars($post['title']); ?>"
                                         loading="lazy">
                                </div>
                            <?php else: ?>
                                <div class="post-card-cover-placeholder">
                                    <i class="fa-regular fa-image" style="font-size:2rem;color:var(--border);"></i>
                                </div>
                            <?php endif; ?>

                            <div class="post-card-body">
                                <?php if ($post['cat_name']): ?>
                                    <div class="post-card-cat"><?php echo htmlspecialchars($post['cat_name']); ?></div>
                                <?php endif; ?>
                                <div class="post-card-title"><?php echo htmlspecialchars($post['title']); ?></div>
                                <?php if ($post['excerpt']): ?>
                                    <div class="post-card-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></div>
                                <?php endif; ?>
                                <div class="post-card-meta">
                                    <span><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                                    <span><i class="fa-regular fa-calendar"></i> <?php echo date('d M Y', strtotime($post['created'])); ?></span>
                                    <span><i class="fa-regular fa-eye"></i> <?php echo number_format($post['views']); ?></span>
                                </div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- Paginación -->
                    <?php if ($pages > 1): ?>
                    <div class="blog-pagination">
                        <?php
                        $baseUrl = $setting['website_url'] . '/blog/' . ($activeCat ? 'category/' . $activeCat['slug'] . '/' : '');
                        if ($currpage > 1): ?>
                            <a href="<?php echo $baseUrl; ?>?page=<?php echo $currpage - 1; ?>" class="blog-page-btn">←</a>
                        <?php endif;
                        for ($i = 1; $i <= $pages; $i++): ?>
                            <a href="<?php echo $baseUrl; ?>?page=<?php echo $i; ?>"
                               class="blog-page-btn <?php echo $i == $currpage ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor;
                        if ($currpage < $pages): ?>
                            <a href="<?php echo $baseUrl; ?>?page=<?php echo $currpage + 1; ?>" class="blog-page-btn">→</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <aside class="blog-sidebar">

                <!-- Categorías -->
                <div class="blog-sidebar-widget">
                    <div class="blog-sidebar-title">
                        <i class="fa-regular fa-folder"></i> Categories
                    </div>
                    <a href="<?php echo $setting['website_url']; ?>/blog/"
                       class="blog-cat-item <?php echo !$activeCat ? 'active' : ''; ?>">
                        All Posts
                        <span class="blog-cat-count"><?php echo $totalPosts; ?></span>
                    </a>
                    <?php foreach ($postCats as $cat): ?>
                        <a href="<?php echo $setting['website_url']; ?>/blog/category/<?php echo $cat['slug']; ?>/"
                           class="blog-cat-item <?php echo ($activeCat && $activeCat['id'] == $cat['id']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                            <span class="blog-cat-count"><?php echo $cat['post_count']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Recientes -->
                <div class="blog-sidebar-widget">
                    <div class="blog-sidebar-title">
                        <i class="fa-regular fa-clock"></i> Recent Posts
                    </div>
                    <?php foreach ($recentPosts as $r): ?>
                    <a href="<?php echo $setting['website_url']; ?>/blog/<?php echo $r['slug']; ?>/"
                       class="blog-recent-item">
                        <?php if ($r['cover_img']): ?>
                            <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/blog/covers/<?php echo $r['cover_img']; ?>"
                                 alt="<?php echo htmlspecialchars($r['title']); ?>">
                        <?php else: ?>
                            <div style="width:48px;height:36px;border-radius:6px;background:rgba(255,255,255,.04);
                                        border:1px solid var(--border);flex-shrink:0;display:flex;
                                        align-items:center;justify-content:center;">
                                <i class="fa-regular fa-image" style="color:var(--border);font-size:.75rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="blog-recent-title"><?php echo htmlspecialchars($r['title']); ?></div>
                            <div class="blog-recent-date"><?php echo date('d M Y', strtotime($r['created'])); ?></div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

                <!-- Logos populares -->
                <div class="blog-sidebar-widget">
                    <div class="blog-sidebar-title">
                        <i class="fa-solid fa-fire"></i> Popular Logos
                    </div>
                    <?php
                    $popLogos = $DB_con->query("
                        SELECT p.*, COUNT(d.id) as dl_count
                        FROM " . PFX . "products p
                        INNER JOIN " . PFX . "downloads d ON p.id = d.products_id
                        WHERE p.active = 1
                        GROUP BY p.id
                        ORDER BY dl_count DESC
                        LIMIT 5
                    ")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($popLogos as $lg): ?>
                    <a href="<?php echo $setting['website_url']; ?>/item/<?php echo $lg['id']; ?>/<?php echo $lg['slug_lg']; ?>/"
                       class="blog-recent-item">
                        <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/vector-files/<?php echo $lg['icon_img']; ?>"
                             alt="<?php echo htmlspecialchars($lg['name']); ?>"
                             style="background:#fff;padding:2px;object-fit:contain;">
                        <div>
                            <div class="blog-recent-title"><?php echo htmlspecialchars($lg['name']); ?></div>
                            <div class="blog-recent-date">
                                <i class="fa-regular fa-download" style="color:var(--accent);"></i>
                                <?php echo number_format($lg['dl_count']); ?> downloads
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>

            </aside>
        </div>
    </div>
</div>

<?php require_once('system/assets/footer.php'); ?>