<?php
declare(strict_types=1);

$config = require __DIR__ . '/../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($config['app']['name']) ?> | Autonomous AI Blogging Dashboard</title>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <a class="brand" href="index.php" aria-label="Writemize dashboard">
                <span class="brand-mark">W</span>
                <span>
                    <strong>Writemize</strong>
                    <small>Autonomous AI blogging</small>
                </span>
            </a>

            <nav class="nav" aria-label="Dashboard">
                <a href="#mission" class="active">Mission Control</a>
                <a href="#agents">Agents</a>
                <a href="#preview">Preview</a>
                <a href="#recent">Recent Runs</a>
            </nav>

            <div class="system-card">
                <span class="status-dot"></span>
                <div>
                    <strong>System Online</strong>
                    <small><?= e($config['app']['timezone']) ?></small>
                </div>
            </div>
        </aside>

        <main class="main">
            <header class="topbar">
                <div>
                    <p class="eyebrow">Scout -> Radar -> Writer -> Artist -> Optimizer -> Publisher</p>
                    <h1>Autonomous AI Blogging Dashboard</h1>
                </div>
                <div class="clock" id="clock">--:--</div>
            </header>

            <section id="mission" class="mission">
                <form id="pipelineForm" class="control-panel">
                    <div class="field wide">
                        <label for="businessName">Business name</label>
                        <input id="businessName" name="business_name" type="text" value="Writemize" autocomplete="organization">
                    </div>
                    <div class="field wide">
                        <label for="websiteUrl">Website URL</label>
                        <input id="websiteUrl" name="website_url" type="url" value="https://example.com" required>
                    </div>
                    <div class="field">
                        <label for="publishTime">Publish time</label>
                        <input id="publishTime" name="publish_time" type="time" value="09:00">
                    </div>
                    <button id="launchBtn" type="submit">Run Pipeline</button>
                </form>

                <div class="terminal" aria-live="polite">
                    <div class="terminal-head">
                        <strong>Live Agent Log</strong>
                        <span id="runState">Idle</span>
                    </div>
                    <div id="terminalBody" class="terminal-body">
                        <p>Ready. Add a business URL and launch the autonomous blog run.</p>
                    </div>
                </div>
            </section>

            <section id="agents" class="agent-grid" aria-label="Agent progress">
                <?php
                $agents = [
                    ['scout', 'Scout', 'Business context and website intelligence'],
                    ['radar', 'Radar', 'Trend, topic, keyword, and intent research'],
                    ['writer', 'Writer', 'SEO article drafting in clean HTML'],
                    ['artist', 'Artist', 'DALL-E featured image generation'],
                    ['optimizer', 'Optimizer', 'Metadata, readability, and SEO scoring'],
                    ['publisher', 'Publisher', 'Scheduling, URL, and final handoff'],
                ];
                foreach ($agents as [$key, $name, $task]):
                ?>
                    <article class="agent-card" id="agent-<?= e($key) ?>">
                        <div class="agent-top">
                            <span><?= e($name) ?></span>
                            <small id="state-<?= e($key) ?>">Waiting</small>
                        </div>
                        <p><?= e($task) ?></p>
                        <div class="progress"><span id="bar-<?= e($key) ?>"></span></div>
                    </article>
                <?php endforeach; ?>
            </section>

            <section class="workspace">
                <article id="preview" class="preview-panel">
                    <div class="panel-head">
                        <div>
                            <p class="eyebrow">Generated Blog</p>
                            <h2 id="articleTitle">Waiting for first run</h2>
                        </div>
                        <span id="seoBadge">SEO --</span>
                    </div>
                    <div id="imagePreview" class="image-preview">Featured image preview</div>
                    <p id="metaDescription" class="meta">The generated meta description will appear here.</p>
                    <div class="metrics">
                        <span><strong id="wordCount">0</strong> words</span>
                        <span><strong id="readingTime">--</strong> read</span>
                        <span><strong id="postStatus">Idle</strong></span>
                    </div>
                    <div id="articleHtml" class="article-html">
                        <p>Your Writer, Artist, Optimizer, and Publisher output will render here after the pipeline completes.</p>
                    </div>
                    <a id="publishUrl" class="publish-link" href="#" aria-disabled="true">Publish URL pending</a>
                </article>

                <aside id="recent" class="recent-panel">
                    <div class="panel-head">
                        <div>
                            <p class="eyebrow">Archive</p>
                            <h2>Recent Runs</h2>
                        </div>
                    </div>
                    <div id="recentPosts" class="recent-list">
                        <p>No posts loaded yet.</p>
                    </div>
                </aside>
            </section>
        </main>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>
