<?php
declare(strict_types=1);

$config = require __DIR__ . '/includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writemize | Autonomous AI Blogging Dashboard</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="landing-body">
    <header class="landing-nav">
        <a class="landing-brand" href="index.php">
            <img src="assets/images/logo.png" alt="Writemize">
            <span>Writemize</span>
        </a>
        <nav>
            <a href="#agents">Agents</a>
            <a href="#workflow">Workflow</a>
            <a href="login.php">Login</a>
            <a class="nav-cta" href="register.php">Start Free</a>
        </nav>
    </header>

    <main>
        <section class="landing-hero">
            <div class="landing-copy">
                <p class="eyebrow">Scout -> Radar -> Quill -> Warden -> Pulse -> Publisher</p>
                <h1>Your autonomous AI blogging team.</h1>
                <p>Writemize researches your business, finds daily topic opportunities, writes SEO-ready posts, creates image briefs, audits quality, and publishes on your schedule.</p>
                <div class="landing-actions">
                    <a href="register.php">Create Account</a>
                    <a href="login.php">Open Dashboard</a>
                </div>
            </div>
            <div class="landing-product">
                <img src="assets/images/logo.png" alt="Writemize logo">
                <div class="product-lines">
                    <span>Daily blog pipeline ready</span>
                    <span>SEO score, schedule, publish URL</span>
                    <span>Runs automatically from cron</span>
                </div>
            </div>
        </section>

        <section id="agents" class="landing-section">
            <div class="section-title">
                <p class="eyebrow">The Agents</p>
                <h2>Six roles. One publishing rhythm.</h2>
            </div>
            <div class="landing-grid">
                <article><strong>Scout</strong><span>Reads business context, audience, niche, and brand tone.</span></article>
                <article><strong>Radar</strong><span>Finds search intent, topic angles, and focus keywords.</span></article>
                <article><strong>Quill</strong><span>Writes the article and prepares the DALL-E image brief.</span></article>
                <article><strong>Warden</strong><span>Checks readability, headings, metadata, and SEO quality.</span></article>
                <article><strong>Pulse</strong><span>Controls daily cadence and scheduled publishing time.</span></article>
                <article><strong>Publisher</strong><span>Creates the final blog URL and public article handoff.</span></article>
            </div>
        </section>

        <section id="workflow" class="landing-band">
            <div>
                <p class="eyebrow">Set it once</p>
                <h2>Register, enter your business URL, choose a daily posting time, and let cron run the team.</h2>
            </div>
            <a href="register.php">Build with Writemize</a>
        </section>
    </main>
</body>
</html>
