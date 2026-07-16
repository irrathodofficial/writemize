<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
$user = require_login();

function delete_local_blog_image(?string $imageUrl): void
{
    if (!$imageUrl) {
        return;
    }

    $path = parse_url($imageUrl, PHP_URL_PATH);
    if (!is_string($path) || $path === '') {
        return;
    }

    $marker = '/assets/images/blogimages/';
    $position = strpos($path, $marker);
    if ($position === false) {
        return;
    }

    $filename = basename($path);
    $target = realpath(WRITEMIZE_ROOT . '/assets/images/blogimages/' . $filename);
    $allowedDir = realpath(WRITEMIZE_ROOT . '/assets/images/blogimages');

    if ($target && $allowedDir && str_starts_with($target, $allowedDir) && is_file($target)) {
        @unlink($target);
    }
}

$notice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $postId = (int) ($_POST['post_id'] ?? 0);
    $stmt = $pdo->prepare('SELECT bp.id, bp.image_url FROM blog_posts bp INNER JOIN businesses b ON b.id = bp.business_id WHERE bp.id = :id AND b.user_id = :user_id LIMIT 1');
    $stmt->execute([':id' => $postId, ':user_id' => (int) $user['id']]);
    $post = $stmt->fetch();

    if ($post) {
        delete_local_blog_image((string) ($post['image_url'] ?? ''));
        $delete = $pdo->prepare('DELETE FROM blog_posts WHERE id = :id');
        $delete->execute([':id' => $postId]);
        $notice = 'Blog deleted successfully.';
    }
}

$stmt = $pdo->prepare('
    SELECT bp.*
    FROM blog_posts bp
    INNER JOIN businesses b ON b.id = bp.business_id
    WHERE b.user_id = :user_id
    ORDER BY bp.id DESC
');
$stmt->execute([':user_id' => (int) $user['id']]);
$posts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Blogs | Writemize</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <a class="brand" href="index.php">
                <img class="brand-logo" src="../assets/images/logo.png" alt="Writemize">
            </a>
            <nav class="nav">
                <a href="index.php"><i class="fa-solid fa-gauge-high"></i>Mission Control</a>
                <a href="blogs.php" class="active"><i class="fa-solid fa-newspaper"></i>All Blogs</a>
                <a href="websiteintegration.php"><i class="fa-solid fa-globe"></i>Website Integration</a>
                <a href="socialautoposting.php"><i class="fa-solid fa-share-nodes"></i>Social Auto Posting</a>
                <a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
            </nav>
        </aside>

        <main class="main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Content Library</p>
                    <h1>All Blogs</h1>
                    <p class="dashboard-welcome">Manage generated posts, featured images, SEO metadata, edits, and deletes.</p>
                </div>
                <a class="topbar-link" href="index.php">Run AI Agent</a>
            </header>

            <?php if ($notice !== ''): ?>
                <div class="notice-card"><?= e($notice) ?></div>
            <?php endif; ?>

            <?php if (count($posts) === 0): ?>
                <section class="empty-library">
                    <p class="eyebrow">No posts yet</p>
                    <h2>Your generated blogs will appear here.</h2>
                    <a href="index.php">Generate your first blog</a>
                </section>
            <?php else: ?>
                <section class="blog-library">
                    <?php foreach ($posts as $post): ?>
                        <article class="blog-tile">
                            <a class="blog-tile-image" href="<?= e($post['publish_url'] ?: '../post.php?slug=' . rawurlencode((string) $post['slug'])) ?>">
                                <?php if (!empty($post['image_url'])): ?>
                                    <img src="<?= e($post['image_url']) ?>" alt="">
                                <?php else: ?>
                                    <img src="../assets/images/logo.png" alt="">
                                <?php endif; ?>
                            </a>
                            <div class="blog-tile-body">
                                <div class="blog-tile-meta">
                                    <span>SEO <?= (int) $post['seo_score'] ?></span>
                                    <span><?= e($post['status']) ?></span>
                                    <span><?= e($post['reading_time']) ?></span>
                                </div>
                                <h2><?= e($post['title']) ?></h2>
                                <p><?= e($post['meta_description']) ?></p>
                                <div class="blog-tile-footer">
                                    <small><?= (int) $post['word_count'] ?> words | <?= e($post['focus_keyword']) ?></small>
                                    <div class="blog-actions">
                                        <a href="<?= e($post['publish_url'] ?: '../post.php?slug=' . rawurlencode((string) $post['slug'])) ?>">View</a>
                                        <a href="blogedit.php?id=<?= (int) $post['id'] ?>">Edit</a>
                                        <form method="post" onsubmit="return confirm('Delete this blog permanently?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="post_id" value="<?= (int) $post['id'] ?>">
                                            <button type="submit">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
