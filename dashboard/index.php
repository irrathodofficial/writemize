<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
$user = require_login();
$config = require WRITEMIZE_ROOT . '/includes/config.php';

$stmt = $pdo->prepare('SELECT * FROM businesses WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
$stmt->execute([':user_id' => (int) $user['id']]);
$business = $stmt->fetch() ?: [];
$businessName = (string) ($business['name'] ?? $user['name']);
$websiteUrl = (string) ($business['website_url'] ?? 'https://example.com');
$publishTime = substr((string) ($business['publish_time'] ?? '09:00'), 0, 5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($config['app']['name']) ?> | Autonomous AI Blogging</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <a class="brand" href="index.php" aria-label="Writemize dashboard">
                <img class="brand-logo" src="../assets/images/logo.png" alt="Writemize">
            </a>

            <div class="sidebar-workflow" aria-hidden="true">
                <span class="workflow-node workflow-left workflow-1"><i class="fa-solid fa-binoculars"></i>Scout</span>
                <span class="workflow-node workflow-right workflow-2"><i class="fa-solid fa-satellite-dish"></i>Radar</span>
                <span class="workflow-node workflow-left workflow-3"><i class="fa-solid fa-feather-pointed"></i>Quill</span>
                <span class="workflow-node workflow-right workflow-4"><i class="fa-solid fa-shield-halved"></i>Warden</span>
                <span class="workflow-node workflow-left workflow-5"><i class="fa-solid fa-wave-square"></i>Pulse</span>
                <span class="workflow-node workflow-right workflow-6"><i class="fa-solid fa-paper-plane"></i>Publisher</span>
            </div>

            <nav class="nav" aria-label="Dashboard">
                <a href="#mission" class="active"><i class="fa-solid fa-gauge-high"></i>Mission Control</a>
                <a href="#agents"><i class="fa-solid fa-robot"></i>Agents</a>
                <a href="#preview"><i class="fa-solid fa-eye"></i>Preview</a>
                <a href="#recent"><i class="fa-solid fa-clock-rotate-left"></i>Recent Runs</a>
                <a href="blogs.php"><i class="fa-solid fa-newspaper"></i>All Blogs</a>
                <a href="websiteintegration.php"><i class="fa-solid fa-globe"></i>Website Integration</a>
                <a href="socialautoposting.php"><i class="fa-solid fa-share-nodes"></i>Social Auto Posting</a>
                <a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</a>
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
                    <p class="eyebrow">Scout -> Radar -> Quill -> Warden -> Pulse -> Publisher</p>
                    <h1>Autonomous AI Blogging</h1>
                    <p class="dashboard-welcome">Welcome, <?= e($user['name']) ?>. Set your daily time once; cron will run the agents for you.</p>
                </div>
                <div class="clock" id="clock">--:--</div>
            </header>

            <section id="mission" class="mission">
                <form id="pipelineForm" class="control-panel">
                    <div class="field wide">
                        <label for="businessName">Business name</label>
                        <input id="businessName" name="business_name" type="text" value="<?= e($businessName) ?>" autocomplete="organization">
                    </div>
                    <div class="field wide">
                        <label for="websiteUrl">Website URL</label>
                        <input id="websiteUrl" name="website_url" type="url" value="<?= e($websiteUrl) ?>" required>
                    </div>
                    <div class="field">
                        <label for="publishTime">Publish time</label>
                        <input id="publishTime" name="publish_time" type="time" value="<?= e($publishTime ?: '09:00') ?>">
                    </div>
                    <div class="action-row wide">
                        <button id="activateBtn" type="button">AI Agent Activate</button>
                        <button id="launchBtn" type="submit">Run AI Agent</button>
                    </div>
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
                    ['scout', 'Scout', 'fa-solid fa-binoculars', 'Business context and website intelligence'],
                    ['radar', 'Radar', 'fa-solid fa-satellite-dish', 'Trend, topic, keyword, and intent research'],
                    ['quill', 'Quill', 'fa-solid fa-feather-pointed', 'SEO article drafting and DALL-E image brief'],
                    ['warden', 'Warden', 'fa-solid fa-shield-halved', 'Readability, structure, and SEO quality control'],
                    ['pulse', 'Pulse', 'fa-solid fa-wave-square', 'Publishing rhythm and schedule preparation'],
                    ['publisher', 'Publisher', 'fa-solid fa-paper-plane', 'Final public blog URL and handoff'],
                ];
                foreach ($agents as [$key, $name, $icon, $task]):
                ?>
                    <article class="agent-card" id="agent-<?= e($key) ?>" role="button" tabindex="0" data-agent="<?= e($key) ?>" aria-label="Open <?= e($name) ?> agent details">
                        <div class="agent-icon"><i class="<?= e($icon) ?>"></i></div>
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
                        <p>Your Quill, Warden, Pulse, and Publisher output will render here after the pipeline completes.</p>
                    </div>
                    <a id="publishUrl" class="publish-link is-pending" href="#" aria-disabled="true">Publish URL will appear here after Publisher finishes</a>
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

    <div class="agent-modal" id="agentModal" aria-hidden="true">
        <div class="agent-modal-backdrop" data-close-agent-modal></div>
        <section class="agent-modal-panel" role="dialog" aria-modal="true" aria-labelledby="agentModalTitle">
            <button class="agent-modal-close" type="button" data-close-agent-modal aria-label="Close agent details">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="agent-modal-icon" id="agentModalIcon"></div>
            <p class="eyebrow" id="agentModalStage">Agent Detail</p>
            <h2 id="agentModalTitle">Agent</h2>
            <p id="agentModalSummary" class="agent-modal-summary"></p>
            <div class="agent-modal-grid">
                <article>
                    <strong><i class="fa-solid fa-circle-nodes"></i> Inputs</strong>
                    <ul id="agentModalInputs"></ul>
                </article>
                <article>
                    <strong><i class="fa-solid fa-wand-magic-sparkles"></i> Outputs</strong>
                    <ul id="agentModalOutputs"></ul>
                </article>
            </div>
        </section>
    </div>

    <script src="../assets/js/app.js"></script>
</body>
</html>
