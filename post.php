<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/db_config.php';

$slug = clean_text($_GET['slug'] ?? '', 255);
$stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE slug = :slug LIMIT 1');
$stmt->execute([':slug' => $slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($post['title'] ?? 'Post not found') ?> | Writemize</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="post-body">
    <main class="post-shell">
        <?php if (!$post): ?>
            <h1>Post not found</h1>
            <p>The requested Writemize post does not exist.</p>
        <?php else: ?>
            <?php if (!empty($post['image_url'])): ?>
                <img class="post-image" src="<?= e($post['image_url']) ?>" alt="">
            <?php endif; ?>
            <p class="eyebrow"><?= e($post['focus_keyword']) ?> | SEO <?= (int) $post['seo_score'] ?></p>
            <?= $post['html'] ?>
        <?php endif; ?>
    </main>
</body>
</html>
