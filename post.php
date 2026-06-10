<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once('system/config-global.php');

$slug = $_GET['slug'] ?? null;
if (!$slug) { header('Location: ' . $setting['website_url'] . '/blog/'); exit; }

// Obtener post
$stmt = $DB_con->prepare("
    SELECT p.*, pc.name as cat_name, pc.slug as cat_slug
    FROM " . PFX . "posts p
    LEFT JOIN " . PFX . "post_categories pc ON p.category_id = pc.id
    WHERE p.slug = :slug AND p.status = 'published'
");
$stmt->execute([':slug' => $slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) { header('Location: ' . $setting['website_url'] . '/blog/'); exit; }

// Incrementar vistas
$DB_con->prepare("UPDATE " . PFX . "posts SET views = views + 1 WHERE id = :id")
       ->execute([':id' => $post['id']]);

// Posts relacionados (misma categoría)
$related = [];
if ($post['category_id']) {
    $relStmt = $DB_con->prepare("
        SELECT id, title, slug, cover_img, created, excerpt
        FROM " . PFX . "posts
        WHERE status = 'published'
          AND category_id = :cat_id
          AND id != :id
        ORDER BY created DESC
        LIMIT 3
    ");
    $relStmt->execute([':cat_id' => $post['category_id'], ':id' => $post['id']]);
    $related = $relStmt->fetchAll(PDO::FETCH_ASSOC);
}

// Categorías sidebar
$postCats = $DB_con->query("
    SELECT pc.*, COUNT(p.id) as post_count
    FROM " . PFX . "post_categories pc
    LEFT JOIN " . PFX . "posts p ON p.category_id = pc.id AND p.status = 'published'
    WHERE pc.active = 1
    GROUP BY pc.id
    ORDER BY pc.name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// SEO
$metaRobots = "<meta name='robots' content='index, follow' />\n";
$canonical  = $setting['website_url'] . '/blog/' . $post['slug'] . '/';
$pageTitle  = $post['meta_title'] ?: $post['title'];
$pageMeta   = $post['meta_desc']  ?: $post['excerpt'];

require_once('system/assets/header.php');
?>

<style>
.post-wrap { padding: 2rem 0 4rem; }

/* Breadcrumb */
.post-breadcrumb {
    font-size: .78rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: .4rem;
    flex-wrap: wrap;
}
.post-breadcrumb a { color: var(--accent); text-decoration: none; }
.post-breadcrumb a:hover { text-decoration: underline; }

/* Article header */
.post-header { margin-bottom: 2rem; }

.post-cat-pill {
    display: inline-block;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #0d0f1c;
    background: var(--accent);
    border-radius: 99px;
    padding: 3px 12px;
    text-decoration: none;
    margin-bottom: 1rem;
}

.post-title {
    font-size: 2.2rem;
    font-weight: 800;
    color: var(--text-primary);
    line-height: 1.25;
    margin: 0 0 1rem;
}

.post-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: .8rem;
    color: var(--text-muted);
    flex-wrap: wrap;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.post-meta i { color: var(--accent); }

/* Cover */
.post-cover {
    width: 100%;
    border-radius: var(--radius-card);
    aspect-ratio: 16/7;
    object-fit: cover;
    margin-bottom: 2rem;
    border: 1px solid var(--border);
}

/* Content */
.post-content {
    font-size: 1rem;
    line-height: 1.8;
    color: var(--text-secondary);
}

.post-content h2 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); margin: 2rem 0 .75rem; }
.post-content h3 { font-size: 1.2rem; font-weight: 700; color: var(--text-primary); margin: 1.5rem 0 .5rem; }
.post-content p  { margin-bottom: 1.25rem; }
.post-content img { max-width: 100%; border-radius: 8px; margin: 1rem 0; }
.post-content a  { color: var(--accent); }
.post-content a:hover { text-decoration: underline; }
.post-content blockquote {
    border-left: 3px solid var(--accent);
    margin: 1.5rem 0;
    padding: .75rem 1.25rem;
    background: rgba(212,255,0,.04);
    border-radius: 0 8px 8px 0;
    color: var(--text-muted);
    font-style: italic;
}
.post-content code {
    background: rgba(212,255,0,.08);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: .9em;
    color: var(--accent);
}
.post-content pre {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 1rem;
    overflow-x: auto;
    margin: 1.25rem 0;
}
.post-content table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.25rem 0;
    font-size: .9rem;
}
.post-content table th,
.post-content table td {
    padding: .6rem 1rem;
    border: 1px solid var(--border);
    text-align: left;
}
.post-content table th { background: rgba(212,255,0,.06); color: var(--text-primary); font-weight: 600; }

/* Share */
.post-share {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: 1.25rem 0;
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    margin: 2rem 0;
    flex-wrap: wrap;
}

.share-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    font-size: .78rem;
    font-weight: 600;
    padding: .4rem 1rem;
    border-radius: 99px;
    text-decoration: none;
    transition: var(--transition);
    border: 1px solid var(--border);
    color: var(--text-muted);
}
.share-btn:hover { color: var(--text-primary); border-color: var(--text-primary); }
.share-btn.twitter:hover { color: #1da1f2; border-color: #1da1f2; }
.share-btn.linkedin:hover { color: #0a66c2; border-color: #0a66c2; }

/* Related posts */
.related-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1rem;
}

.related-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-card);
    overflow: hidden;
    text-decoration: none;
    transition: var(--transition);
}

.related-card:hover { border-color: rgba(212,255,0,.3); transform: translateY(-2px); }

.related-card img {
    width: 100%;
    aspect-ratio: 16/9;
    object-fit: cover;
}

.related-card-body { padding: .85rem; }
.related-card-title {
    font-size: .85rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: .4rem;
}
.related-card-date { font-size: .7rem; color: var(--text-muted); }

@media (max-width: 768px) {
    .post-title { font-size: 1.5rem; }
    .post-layout { grid-template-columns: 1fr !important; }
    .related-grid { grid-template-columns: 1fr; }
}
</style>

<div class="post-wrap">
    <div class="container">
        <div class="post-layout" style="display:grid;grid-template-columns:1fr 280px;gap:2rem;align-items:start;">

            <!-- ── ARTÍCULO ── -->
            <article>

                <!-- Breadcrumb -->
                <nav class="post-breadcrumb">
                    <a href="<?php echo $setting['website_url']; ?>">Home</a>
                    <i class="fa-regular fa-chevron-right" style="font-size:.6rem;"></i>
                    <a href="<?php echo $setting['website_url']; ?>/blog/">Blog</a>
                    <?php if ($post['cat_name']): ?>
                        <i class="fa-regular fa-chevron-right" style="font-size:.6rem;"></i>
                        <a href="<?php echo $setting['website_url']; ?>/blog/category/<?php echo $post['cat_slug']; ?>/">
                            <?php echo htmlspecialchars($post['cat_name']); ?>
                        </a>
                    <?php endif; ?>
                    <i class="fa-regular fa-chevron-right" style="font-size:.6rem;"></i>
                    <span><?php echo htmlspecialchars($post['title']); ?></span>
                </nav>

                <!-- Header -->
                <div class="post-header">
                    <?php if ($post['cat_name']): ?>
                        <a href="<?php echo $setting['website_url']; ?>/blog/category/<?php echo $post['cat_slug']; ?>/"
                           class="post-cat-pill">
                            <?php echo htmlspecialchars($post['cat_name']); ?>
                        </a>
                    <?php endif; ?>

                    <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>

                    <div class="post-meta">
                        <span><i class="fa-regular fa-user"></i> <?php echo htmlspecialchars($post['author']); ?></span>
                        <span><i class="fa-regular fa-calendar"></i> <?php echo date('d F Y', strtotime($post['created'])); ?></span>
                        <span><i class="fa-regular fa-eye"></i> <?php echo number_format($post['views']); ?> views</span>
                        <?php if ($post['modified'] != $post['created']): ?>
                            <span><i class="fa-regular fa-pen"></i> Updated <?php echo date('d M Y', strtotime($post['modified'])); ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cover -->
                <?php if ($post['cover_img']): ?>
                    <img class="post-cover"
                         src="<?php echo $setting['website_url']; ?>/system/assets/uploads/blog/covers/<?php echo $post['cover_img']; ?>"
                         alt="<?php echo htmlspecialchars($post['title']); ?>">
                <?php endif; ?>

                <!-- Content -->
                <div class="post-content">
                    <?php echo $post['content']; ?>
                </div>

                <!-- Share -->
                <div class="post-share">
                    <span style="font-size:.78rem;font-weight:600;color:var(--text-muted);">Share:</span>
                    <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode($canonical); ?>"
                       class="share-btn twitter" target="_blank" rel="noopener">
                        <i class="fa-brands fa-x-twitter"></i> Twitter
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($canonical); ?>"
                       class="share-btn linkedin" target="_blank" rel="noopener">
                        <i class="fa-brands fa-linkedin"></i> LinkedIn
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical); ?>"
                       class="share-btn" target="_blank" rel="noopener">
                        <i class="fa-brands fa-facebook"></i> Facebook
                    </a>
                    <button class="share-btn" onclick="copyUrl()" id="copyBtn">
                        <i class="fa-regular fa-link"></i> Copy link
                    </button>
                </div>

                <!-- Related posts -->
                <?php if (!empty($related)): ?>
                <div style="margin-top:2rem;">
                    <h3 style="font-size:1.1rem;font-weight:700;color:var(--text-primary);margin-bottom:1rem;">
                        Related Posts
                    </h3>
                    <div class="related-grid">
                        <?php foreach ($related as $r):
                            $relUrl = $setting['website_url'] . '/blog/' . $r['slug'] . '/';
                        ?>
                        <a href="<?php echo $relUrl; ?>" class="related-card">
                            <?php if ($r['cover_img']): ?>
                                <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/blog/covers/<?php echo $r['cover_img']; ?>"
                                     alt="<?php echo htmlspecialchars($r['title']); ?>"
                                     loading="lazy">
                            <?php else: ?>
                                <div style="width:100%;aspect-ratio:16/9;background:rgba(212,255,0,.04);
                                            display:flex;align-items:center;justify-content:center;">
                                    <i class="fa-regular fa-image" style="font-size:1.5rem;color:var(--border);"></i>
                                </div>
                            <?php endif; ?>
                            <div class="related-card-body">
                                <div class="related-card-title"><?php echo htmlspecialchars($r['title']); ?></div>
                                <div class="related-card-date"><?php echo date('d M Y', strtotime($r['created'])); ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

            </article>

            <!-- ── SIDEBAR ── -->
            <aside class="blog-sidebar" style="position:sticky;top:80px;">

                <!-- About -->
                <div class="blog-sidebar-widget">
                    <div class="blog-sidebar-title"><i class="fa-regular fa-circle-info"></i> About</div>
                    <div style="padding:1rem;font-size:.8rem;color:var(--text-muted);line-height:1.5;">
                        <?php echo htmlspecialchars($setting['description'] ?? ''); ?>
                        <a href="<?php echo $setting['website_url']; ?>"
                           style="color:var(--accent);display:block;margin-top:.5rem;">
                            Explore logos →
                        </a>
                    </div>
                </div>

                <!-- Categorías -->
                <div class="blog-sidebar-widget">
                    <div class="blog-sidebar-title"><i class="fa-regular fa-folder"></i> Categories</div>
                    <a href="<?php echo $setting['website_url']; ?>/blog/" class="blog-cat-item">
                        All Posts
                    </a>
                    <?php foreach ($postCats as $cat): ?>
                        <a href="<?php echo $setting['website_url']; ?>/blog/category/<?php echo $cat['slug']; ?>/"
                           class="blog-cat-item <?php echo ($post['category_id'] == $cat['id']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                            <span class="blog-cat-count"><?php echo $cat['post_count']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Logos populares -->
                <div class="blog-sidebar-widget">
                    <div class="blog-sidebar-title"><i class="fa-solid fa-fire"></i> Popular Logos</div>
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

<script>
function copyUrl() {
    navigator.clipboard.writeText('<?php echo $canonical; ?>').then(function() {
        var btn = document.getElementById('copyBtn');
        btn.innerHTML = '<i class="fa-regular fa-check"></i> Copied!';
        btn.style.color = 'var(--accent)';
        btn.style.borderColor = 'var(--accent)';
        setTimeout(function() {
            btn.innerHTML = '<i class="fa-regular fa-link"></i> Copy link';
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 2000);
    });
}
</script>

<?php require_once('system/assets/footer.php'); ?>