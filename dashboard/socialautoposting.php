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
    <title>Social Auto Posting | Writemize</title>
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
                <a href="websiteintegration.php">Website Integration</a>
                <a href="socialautoposting.php" class="active">Social Auto Posting</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </aside>
        <main class="main">
            <section class="settings-page">
                <p class="eyebrow">Social Auto Posting</p>
                <h1>Turn every blog into social distribution</h1>
                <p>This page is ready for LinkedIn auto-posting settings. Later we can add OAuth, company pages, post templates, hashtags, and approval rules.</p>
                <div class="settings-grid">
                    <article><strong>LinkedIn</strong><span>Auto-post blog summaries to a profile or company page.</span></article>
                    <article><strong>Templates</strong><span>Generate short, professional captions from the published blog.</span></article>
                    <article><strong>Queue</strong><span>Control timing, approvals, retries, and post history.</span></article>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
