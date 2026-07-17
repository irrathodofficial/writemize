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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="landing-body">
    <header class="landing-nav">
        <a class="landing-brand" href="index.php">
            <img src="assets/images/logo.png" alt="Writemize">
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
                <h1>Writemize</h1>
                <h2>Autonomous AI blogging.</h2>
                <p>Connect a business website once. Writemize studies the brand, discovers daily SEO topics, writes complete blog posts, generates featured images, audits quality, and prepares publishing on schedule.</p>
                <div class="landing-actions">
                    <a href="register.php">Create Account</a>
                    <a href="login.php">Open Dashboard</a>
                </div>
                <div class="landing-metrics" aria-label="Writemize capabilities">
                    <span><strong>6</strong> specialist agents</span>
                    <span><strong>24/7</strong> cron-ready pipeline</span>
                    <span><strong>SEO</strong> article + image</span>
                </div>
            </div>
            <div class="landing-visual" aria-label="Writemize autonomous agent workflow">
                <div class="hero-logo-card">
                   
                    <span></span>
                </div>
                <img class="hero-agent" src="assets/images/agentstanding.png" alt="Writemize AI agent robot">
                <div class="hero-agent-node node-scout"><i class="fa-solid fa-binoculars"></i>Scout</div>
                <div class="hero-agent-node node-radar"><i class="fa-solid fa-satellite-dish"></i>Radar</div>
                <div class="hero-agent-node node-quill"><i class="fa-solid fa-feather-pointed"></i>Quill</div>
                <div class="hero-agent-node node-warden"><i class="fa-solid fa-shield-halved"></i>Warden</div>
                <div class="hero-agent-node node-pulse"><i class="fa-solid fa-wave-square"></i>Pulse</div>
                <div class="hero-agent-node node-publisher"><i class="fa-solid fa-paper-plane"></i>Publisher</div>
                <div class="hero-console">
                    <span>Live Agent Log</span>
                    <p>Business context -> topic -> article -> image -> SEO -> publish URL</p>
                </div>
            </div>
        </section>

        <section id="agents" class="landing-section">
            <div class="section-title">
                <p class="eyebrow">The Agents</p>
                <h2>Six agents built for one blogging mission.</h2>
            </div>
            <div class="landing-grid">
                <article><i class="fa-solid fa-binoculars"></i><strong>Scout</strong><span>Reads the website, extracts positioning, audience, niche, tone, and reusable business context.</span></article>
                <article><i class="fa-solid fa-satellite-dish"></i><strong>Radar</strong><span>Finds topic opportunities, competitor-style angles, focus keywords, and search intent.</span></article>
                <article><i class="fa-solid fa-feather-pointed"></i><strong>Quill</strong><span>Drafts the SEO article in clean HTML and prepares the DALL-E featured image prompt.</span></article>
                <article><i class="fa-solid fa-shield-halved"></i><strong>Warden</strong><span>Checks readability, structure, metadata, word count, and SEO quality before publishing.</span></article>
                <article><i class="fa-solid fa-wave-square"></i><strong>Pulse</strong><span>Prepares publishing rhythm, scheduled time, and daily cron-ready handoff.</span></article>
                <article><i class="fa-solid fa-paper-plane"></i><strong>Publisher</strong><span>Creates the final post record, status, public blog URL, and dashboard archive entry.</span></article>
            </div>
        </section>

        <section id="workflow" class="landing-band">
            <div>
                <p class="eyebrow">Set it once</p>
                <h2>Register, enter your business URL, choose a daily posting time, and let Writemize run the complete content pipeline.</h2>
            </div>
            <a href="register.php">Build with Writemize</a>
        </section>

        <section class="landing-flow" aria-label="Writemize publishing workflow">
            <div class="section-title">
                <p class="eyebrow">Workflow</p>
                <h2>From website URL to publish-ready blog.</h2>
            </div>
            <div class="flow-steps">
                <span>Business URL</span>
                <span>Scout memory</span>
                <span>SEO topic</span>
                <span>Article + image</span>
                <span>Quality score</span>
                <span>Publish URL</span>
            </div>
        </section>
    </main>
</body>
</html>
