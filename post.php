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

// Read time (≈200 palabras por minuto)
$wordCount = str_word_count(strip_tags($post['content']));
$readTime  = max(1, ceil($wordCount / 200));

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

// SEO
$metaRobots = "<meta name='robots' content='index, follow' />\n";
$canonical  = $setting['website_url'] . '/blog/' . $post['slug'] . '/';
$pageTitle  = $post['meta_title'] ?: $post['title'];
$pageMeta   = $post['meta_desc']  ?: $post['excerpt'];

require_once('system/assets/header.php');
?>


<div class="article-wrap">

    <!-- Header -->
    <div class="article-container">
        <div class="article-header">
            <?php if ($post['cat_name']): ?>
                <a href="<?php echo $setting['website_url']; ?>/blog/category/<?php echo $post['cat_slug']; ?>/" class="article-cat">
                    <?php echo htmlspecialchars($post['cat_name']); ?>
                </a>
            <?php endif; ?>

            <h1 class="article-title"><?php echo htmlspecialchars($post['title']); ?></h1>

            <?php if ($post['excerpt']): ?>
                <p class="article-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
            <?php endif; ?>

            <div class="article-meta">
                <div class="article-meta-item">
                    <div class="article-meta-label">Date</div>
                    <div class="article-meta-value"><?php echo date('M j, Y', strtotime($post['created'])); ?></div>
                </div>
                <div class="article-meta-item">
                    <div class="article-meta-label">Author</div>
                    <div class="article-meta-value"><?php echo htmlspecialchars($post['author']); ?></div>
                </div>
                <div class="article-meta-item">
                    <div class="article-meta-label">Read</div>
                    <div class="article-meta-value"><?php echo $readTime; ?> Min</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cover -->
    <?php if ($post['cover_img']): ?>
    <div class="article-cover">
        <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/blog/covers/<?php echo $post['cover_img']; ?>"
             alt="<?php echo htmlspecialchars($post['title']); ?>">
    </div>
    <?php endif; ?>

    <!-- Body -->
    <div class="article-body-wrap">
        <!-- Redes flotantes para compartir -->
        <div class="article-share-float">
            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode($canonical); ?>"
               class="share-float-btn" target="_blank" rel="noopener" title="Share on Twitter">
                <i class="fa-brands fa-x-twitter"></i>
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($canonical); ?>"
               class="share-float-btn" target="_blank" rel="noopener" title="Share on LinkedIn">
                <i class="fa-brands fa-linkedin-in"></i>
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical); ?>"
               class="share-float-btn" target="_blank" rel="noopener" title="Share on Facebook">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
            <button class="share-float-btn" onclick="copyUrl()" title="Copy link" id="copyFloat">
                <i class="fa-regular fa-link"></i>
            </button>
        </div>

        <!-- Contenido -->
        <div class="article-content">
            <?php echo $post['content']; ?>
        </div>
    </div>

    <!-- Share bottom -->
    <div class="article-share-bottom">
        <span style="font-size:.8rem;color:var(--text-muted);font-weight:600;">Share this article:</span>
        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode($canonical); ?>"
           class="share-btn-inline" target="_blank" rel="noopener">
            <i class="fa-brands fa-x-twitter"></i> Twitter
        </a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($canonical); ?>"
           class="share-btn-inline" target="_blank" rel="noopener">
            <i class="fa-brands fa-linkedin-in"></i> LinkedIn
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical); ?>"
           class="share-btn-inline" target="_blank" rel="noopener">
            <i class="fa-brands fa-facebook-f"></i> Facebook
        </a>
        <button class="share-btn-inline" onclick="copyUrl()" id="copyBtn">
            <i class="fa-regular fa-link"></i> Copy link
        </button>
    </div>

    <!-- Related -->
    <?php if (!empty($related)): ?>
    <div class="article-related">
        <div class="article-related-title">Related Articles</div>
        <div class="related-grid">
            <?php foreach ($related as $r): ?>
            <a href="<?php echo $setting['website_url']; ?>/blog/<?php echo $r['slug']; ?>/" class="related-card">
                <?php if ($r['cover_img']): ?>
                    <img src="<?php echo $setting['website_url']; ?>/system/assets/uploads/blog/covers/<?php echo $r['cover_img']; ?>"
                         alt="<?php echo htmlspecialchars($r['title']); ?>" loading="lazy">
                <?php else: ?>
                    <div style="width:100%;aspect-ratio:16/10;background:rgba(212,255,0,.04);display:flex;align-items:center;justify-content:center;">
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

</div>

<script>
function copyUrl() {
    navigator.clipboard.writeText('<?php echo $canonical; ?>').then(function() {
        ['copyBtn','copyFloat'].forEach(function(id) {
            var btn = document.getElementById(id);
            if (!btn) return;
            var original = btn.innerHTML;
            btn.innerHTML = '<i class="fa-regular fa-check"></i>' + (id === 'copyBtn' ? ' Copied!' : '');
            btn.style.color = 'var(--accent)';
            btn.style.borderColor = 'var(--accent)';
            setTimeout(function() {
                btn.innerHTML = original;
                btn.style.color = '';
                btn.style.borderColor = '';
            }, 2000);
        });
    });
}
</script>

<?php require_once('system/assets/footer.php'); ?>