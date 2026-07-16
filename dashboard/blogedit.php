<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
$user = require_login();

$postId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$notice = '';

$stmt = $pdo->prepare('SELECT bp.* FROM blog_posts bp INNER JOIN businesses b ON b.id = bp.business_id WHERE bp.id = :id AND b.user_id = :user_id LIMIT 1');
$stmt->execute([':id' => $postId, ':user_id' => (int) $user['id']]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
}

if ($post && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = clean_text($_POST['title'] ?? '', 255);
    $metaDescription = clean_text($_POST['meta_description'] ?? '', 320);
    $focusKeyword = clean_text($_POST['focus_keyword'] ?? '', 190);
    $status = clean_text($_POST['status'] ?? 'edited', 40);
    $html = trim((string) ($_POST['html'] ?? ''));
    $imageUrl = clean_text($_POST['image_url'] ?? '', 2048);

    if ($title !== '' && $html !== '') {
        $wordCount = str_word_count(strip_tags($html));
        $readingTime = estimate_reading_time($html);
        $update = $pdo->prepare('UPDATE blog_posts SET title = :title, meta_description = :meta_description, focus_keyword = :focus_keyword, html = :html, image_url = :image_url, word_count = :word_count, reading_time = :reading_time, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $update->execute([
            ':title' => $title,
            ':meta_description' => $metaDescription,
            ':focus_keyword' => $focusKeyword,
            ':html' => $html,
            ':image_url' => $imageUrl !== '' ? $imageUrl : null,
            ':word_count' => $wordCount,
            ':reading_time' => $readingTime,
            ':status' => $status,
            ':id' => $postId,
        ]);
        $notice = 'Blog updated successfully.';

        $stmt->execute([':id' => $postId, ':user_id' => (int) $user['id']]);
        $post = $stmt->fetch();
    } else {
        $notice = 'Title and blog HTML are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog | Writemize</title>
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
            <?php if (!$post): ?>
                <section class="empty-library">
                    <p class="eyebrow">Not found</p>
                    <h1>Blog not found</h1>
                    <a href="blogs.php">Back to All Blogs</a>
                </section>
            <?php else: ?>
                <header class="topbar">
                    <div>
                        <p class="eyebrow">Editor</p>
                        <h1>Edit Blog</h1>
                    </div>
                    <a class="topbar-link" href="blogs.php">Back to All Blogs</a>
                </header>

                <?php if ($notice !== ''): ?>
                    <div class="notice-card"><?= e($notice) ?></div>
                <?php endif; ?>

                <form class="blog-editor" method="post">
                    <input type="hidden" name="id" value="<?= (int) $post['id'] ?>">
                    <label>Title<input name="title" type="text" value="<?= e($post['title']) ?>" required></label>
                    <label>Meta description<textarea name="meta_description" rows="3"><?= e($post['meta_description']) ?></textarea></label>
                    <div class="editor-row">
                        <label>Focus keyword<input name="focus_keyword" type="text" value="<?= e($post['focus_keyword']) ?>"></label>
                        <label>Status<input name="status" type="text" value="<?= e($post['status']) ?>"></label>
                    </div>
                    <label>Featured image URL<input name="image_url" type="url" value="<?= e((string) $post['image_url']) ?>"></label>
                    <label>Blog HTML<textarea class="html-editor" name="html" rows="22" required><?= e($post['html']) ?></textarea></label>
                    <div class="editor-actions">
                        <button type="submit">Save Blog</button>
                        <a href="<?= e($post['publish_url'] ?: '../post.php?slug=' . rawurlencode((string) $post['slug'])) ?>">View Blog</a>
                    </div>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
