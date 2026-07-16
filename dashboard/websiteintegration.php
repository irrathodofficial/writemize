<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
$user = require_login();
$config = require WRITEMIZE_ROOT . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Integration | Writemize</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <a class="brand" href="index.php">
                <img class="brand-logo" src="../assets/images/logo.png" alt="Writemize">
                <span><strong>Writemize</strong><small>Autonomous AI blogging</small></span>
            </a>
            <nav class="nav">
                <a href="index.php">Mission Control</a>
                <a href="blogs.php">All Blogs</a>
                <a href="websiteintegration.php" class="active">Website Integration</a>
                <a href="socialautoposting.php">Social Auto Posting</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </aside>
        <main class="main">
            <section class="settings-page">
                <p class="eyebrow">Website Integration</p>
                <h1>Connect your publishing website</h1>
                <p>Use this page for WordPress, custom webhook, sitemap, and publishing API settings. The dashboard already stores the business URL and daily schedule; this area will hold the deeper website connection flow.</p>
                <div class="settings-grid">
                    <article><strong>WordPress</strong><span>Prepare site URL, username, app password, category, and status mapping.</span></article>
                    <article><strong>Webhook</strong><span>Send generated posts to any custom CMS endpoint.</span></article>
                    <article><strong>Sitemap</strong><span>Read existing pages for internal linking and topic coverage.</span></article>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
