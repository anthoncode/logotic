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

<style>
/* ── Blog Layout ── */
.blog-wrap { padding: 2rem 0 4rem; }

.blog-hero {
    background: var(--bg-card);
    border-bottom: 1px solid var(--border);
    padding: 2.5rem 0;
    margin-bottom: 2.5rem;
}

.blog-hero h1 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 .5rem;
}

.blog-hero p {
    color: var(--text-muted);
    font-size: .95rem;
    margin: 0;
}

.blog-grid {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

/* Post card */
.post-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    overflow: hidden;
    text-decoration: none;
    display: flex;
    flex-direction: column;
    transition: var(--transition);
}

.post-card:hover {
    border-color: rgba(212,255,0,.3);
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(0,0,0,.3);
}

.post-card-cover {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
    background: rgba(255,255,255,.04);
    display: flex;
    align-items: center;
    justify-content: center;
}

.post-card-cover img { width:100%; height:100%; object-fit:cover; }

.post-card-cover-placeholder {
    width: 100%;
    aspect-ratio: 16/9;
    background: rgba(212,255,0,.04);
    display: flex;
    align-items: center;
    justify-content: center;
    border-bottom: 1px solid var(--border);
}

.post-card-body { padding: 1.25rem; flex: 1; display: flex; flex-direction: column; gap: .5rem; }

.post-card-cat {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--accent);
}

.post-card-title {
    font-size: 1rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.post-card-excerpt {
    font-size: .82rem;
    color: var(--text-muted);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

.post-card-meta {
    display: flex;
    align-items: center;
    gap: .75rem;
    font-size: .72rem;
    color: var(--text-muted);
    margin-top: auto;
    padding-top: .75rem;
    border-top: 1px solid var(--border);
}

.post-card-meta i { color: var(--accent); }

/* Sidebar */
.blog-sidebar { display: flex; flex-direction: column; gap: 1rem; }

.blog-sidebar-widget {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    overflow: hidden;
}

.blog-sidebar-title {
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--accent);
    padding: .9rem 1rem;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    gap: .5rem;
}

.blog-cat-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: .55rem 1rem;
    color: var(--text-secondary);
    text-decoration: none;
    font-size: .83rem;
    border-bottom: 1px solid var(--border);
    transition: var(--transition);
}

.blog-cat-item:last-child { border-bottom: none; }
.blog-cat-item:hover { background: rgba(212,255,0,.05); color: var(--text-primary); }
.blog-cat-item.active { color: var(--accent); font-weight: 600; }

.blog-cat-count {
    background: rgba(212,255,0,.1);
    color: var(--accent);
    font-size: .68rem;
    font-weight: 700;
    border-radius: 99px;
    padding: 1px 8px;
}

.blog-recent-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .6rem 1rem;
    text-decoration: none;
    border-bottom: 1px solid var(--border);
    transition: var(--transition);
}

.blog-recent-item:last-child { border-bottom: none; }
.blog-recent-item:hover { background: rgba(212,255,0,.05); }

.blog-recent-item img {
    width: 48px; height: 36px;
    border-radius: 6px;
    object-fit: cover;
    flex-shrink: 0;
    background: rgba(255,255,255,.04);
}

.blog-recent-title {
    font-size: .78rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.blog-recent-date { font-size: .68rem; color: var(--text-muted); margin-top: .15rem; }

/* Pagination */
.blog-pagination {
    display: flex;
    justify-content: center;
    gap: .4rem;
    margin-top: 2rem;
}

.blog-page-btn {
    background: var(--bg-card);
    border: 1px solid var(--border);
    color: var(--text-muted);
    border-radius: 8px;
    padding: .5rem 1rem;
    font-size: .82rem;
    text-decoration: none;
    transition: var(--transition);
}

.blog-page-btn:hover, .blog-page-btn.active {
    background: var(--accent);
    color: #0d0f1c;
    border-color: var(--accent);
    font-weight: 700;
}

.blog-empty {
    text-align: center;
    padding: 4rem 2rem;
    color: var(--text-muted);
}

.blog-empty i { font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--border); }

@media (max-width: 768px) {
    .blog-grid { grid-template-columns: 1fr; }
    .blog-layout { grid-template-columns: 1fr; }
}
</style>

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