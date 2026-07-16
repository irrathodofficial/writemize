<?php
declare(strict_types=1);

require_once '../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard | Writemize</title>
<style>
:root {
    --bg: #0F172A;
    --sidebar: #111827;
    --card: #1E293B;
    --primary: #2563EB;
    --accent: #14B8A6;
    --success: #10E8A0;
    --warning: #FBBF24;
    --error: #EF4444;
    --muted: #94A3B8;
    --line: rgba(148, 163, 184, .18);
    --text: #E5E7EB;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    min-height: 100vh;
    background: var(--bg);
    color: var(--text);
    font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

button,
input {
    font: inherit;
}

.app {
    min-height: 100vh;
    display: grid;
    grid-template-columns: 290px minmax(0, 1fr);
}

.sidebar {
    position: sticky;
    top: 0;
    height: 100vh;
    background: var(--sidebar);
    border-right: 1px solid var(--line);
    padding: 24px 18px;
    overflow-y: auto;
}

.brand {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 0 8px 24px;
    border-bottom: 1px solid var(--line);
}

.brand img {
    width: 48px;
    height: 48px;
    object-fit: contain;
}

.brand-mark {
    width: 48px;
    height: 48px;
    display: grid;
    place-items: center;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    font-weight: 900;
    color: white;
    box-shadow: 0 0 28px rgba(20, 184, 166, .28);
}

.brand h1,
.section h2,
.preview-title,
.metric strong {
    margin: 0;
}

.brand p,
.section p {
    margin: 4px 0 0;
    color: var(--muted);
    font-size: 13px;
}

.nav {
    padding: 20px 0;
    border-bottom: 1px solid var(--line);
}

.nav-item {
    min-height: 46px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    color: #CBD5E1;
    text-decoration: none;
    border-radius: 8px;
    padding: 11px 12px;
    margin-bottom: 6px;
    transition: background .25s ease, transform .25s ease, color .25s ease;
}

.nav-item:hover,
.nav-item.active {
    background: rgba(37, 99, 235, .16);
    color: white;
}

.nav-item:hover {
    transform: translateX(3px);
}

.nav-left {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 0;
}

.icon {
    width: 26px;
    height: 26px;
    display: grid;
    place-items: center;
    border-radius: 8px;
    background: rgba(148, 163, 184, .1);
    color: var(--accent);
    animation: iconPulse 2.5s ease-in-out infinite;
    flex: 0 0 auto;
}

.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: #CBD5E1;
    font-size: 12px;
    white-space: nowrap;
}

.dot {
    width: 9px;
    height: 9px;
    border-radius: 999px;
    background: var(--muted);
    box-shadow: 0 0 0 rgba(148, 163, 184, 0);
}

.dot.running {
    background: var(--success);
    animation: glow 1.2s ease-in-out infinite;
}

.dot.waiting {
    background: var(--warning);
}

.dot.complete {
    background: var(--primary);
}

.dot.error {
    background: var(--error);
}

.agent-status {
    padding: 20px 0;
    border-bottom: 1px solid var(--line);
}

.status-title,
.legend-title {
    margin: 0 0 14px;
    font-size: 14px;
    color: white;
}

.status-list {
    display: grid;
    gap: 10px;
    color: var(--muted);
    font-size: 14px;
}

.status-row {
    display: flex;
    align-items: center;
    gap: 9px;
    transition: color .25s ease, transform .25s ease;
}

.status-row.done {
    color: var(--success);
    transform: translateX(2px);
}

.check {
    width: 19px;
    height: 19px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(148, 163, 184, .35);
    border-radius: 50%;
    font-size: 12px;
}

.status-row.done .check {
    border-color: var(--success);
    color: #061D17;
    background: var(--success);
    animation: pop .42s ease;
}

.legend {
    padding-top: 20px;
}

.legend-grid {
    display: grid;
    gap: 10px;
}

.main {
    min-width: 0;
    padding: 26px;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 22px;
}

.eyebrow {
    color: var(--accent);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.topbar h2 {
    margin: 4px 0;
    font-size: clamp(26px, 4vw, 42px);
    letter-spacing: 0;
}

.user-chip {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: rgba(30, 41, 59, .7);
}

.avatar {
    width: 40px;
    height: 40px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    font-weight: 900;
}

.layout {
    display: grid;
    grid-template-columns: minmax(0, 1.2fr) minmax(320px, .8fr);
    gap: 22px;
    align-items: start;
}

.stack {
    display: grid;
    gap: 22px;
}

.card {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 8px;
    box-shadow: 0 20px 50px rgba(2, 6, 23, .28);
    animation: slideIn .55s ease both;
}

.section {
    padding: 22px;
}

.business-setup-container {
    position: relative;
    z-index: 8000;
    pointer-events: auto;
}

.section-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.clock {
    color: white;
    font-family: "SFMono-Regular", Consolas, monospace;
    font-size: 13px;
    padding: 8px 10px;
    border-radius: 8px;
    background: rgba(15, 23, 42, .72);
}

.setup-form {
    position: relative;
    z-index: 9000;
    display: grid;
    grid-template-columns: 1fr 190px auto;
    gap: 12px;
    pointer-events: auto;
}

.field {
    position: relative;
    z-index: 9000;
    min-width: 0;
    pointer-events: auto;
}

.field label {
    display: block;
    margin-bottom: 7px;
    color: #CBD5E1;
    font-size: 13px;
    font-weight: 700;
}

.field input {
    width: 100%;
    min-height: 48px;
    color: white;
    color-scheme: dark;
    background: #0B1220;
    border: 1px solid rgba(148, 163, 184, .26);
    border-radius: 8px;
    outline: none;
    padding: 0 14px;
    transition: border .2s ease, box-shadow .2s ease;
}

.field input[type="time"] {
    cursor: pointer;
}

#publishTime {
    position: relative;
    z-index: 9999 !important;
    pointer-events: auto !important;
}

.field input[type="time"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
    filter: invert(1);
    opacity: .85;
    pointer-events: auto !important;
}

.field input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 4px rgba(20, 184, 166, .12);
}

.launch {
    align-self: end;
    min-height: 48px;
    border: 0;
    border-radius: 8px;
    padding: 0 18px;
    color: white;
    font-weight: 800;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    cursor: pointer;
    transition: transform .2s ease, filter .2s ease;
    white-space: nowrap;
}

.launch:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
}

.launch:disabled {
    cursor: wait;
    opacity: .72;
    transform: none;
}

.terminal {
    min-height: 330px;
    overflow: hidden;
    background: #060B14;
}

.terminal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid rgba(148, 163, 184, .14);
}

.terminal-title {
    font-weight: 800;
}

.terminal-dots {
    display: flex;
    gap: 7px;
}

.terminal-dots span {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--error);
}

.terminal-dots span:nth-child(2) {
    background: var(--warning);
}

.terminal-dots span:nth-child(3) {
    background: var(--success);
}

.terminal-body {
    height: 276px;
    overflow: hidden;
    padding: 18px;
    color: #D1FAE5;
    font-family: "SFMono-Regular", Consolas, monospace;
    font-size: 13px;
    line-height: 1.8;
}

.terminal-line {
    opacity: 0;
    transform: translateY(8px);
    animation: fadeUp .25s ease forwards;
}

.terminal-line .time {
    color: #60A5FA;
}

.terminal-line .ok {
    color: var(--success);
}

.cursor {
    display: inline-block;
    width: 8px;
    height: 15px;
    margin-left: 4px;
    background: var(--success);
    animation: blink .9s steps(2, start) infinite;
    vertical-align: -2px;
}

.agent-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.agent-card {
    padding: 16px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: rgba(15, 23, 42, .58);
    transition: box-shadow .3s ease, border .3s ease, transform .3s ease;
}

.agent-card.running {
    border-color: rgba(16, 232, 160, .52);
    box-shadow: 0 0 30px rgba(16, 232, 160, .16);
    transform: translateY(-2px);
}

.agent-name {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 9px;
    font-weight: 800;
}

.agent-task {
    min-height: 19px;
    color: var(--muted);
    font-size: 13px;
    margin-bottom: 12px;
}

.progress {
    height: 11px;
    overflow: hidden;
    border-radius: 999px;
    background: rgba(148, 163, 184, .16);
}

.progress span {
    display: block;
    width: 0%;
    height: 100%;
    border-radius: inherit;
    background: linear-gradient(90deg, var(--primary), var(--accent), var(--success));
    transition: width .8s ease;
    position: relative;
}

.progress span::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .35), transparent);
    animation: shimmer 1.5s linear infinite;
}

.progress-meta {
    display: flex;
    justify-content: space-between;
    margin-top: 9px;
    color: var(--muted);
    font-size: 12px;
}

.preview {
    position: sticky;
    top: 26px;
}

.image-box {
    min-height: 210px;
    display: grid;
    place-items: center;
    overflow: hidden;
    border-radius: 8px;
    border: 1px solid rgba(20, 184, 166, .28);
    background:
        linear-gradient(135deg, rgba(37, 99, 235, .34), rgba(20, 184, 166, .18)),
        radial-gradient(circle at 70% 35%, rgba(16, 232, 160, .32), transparent 34%),
        #0B1220;
    color: rgba(255, 255, 255, .72);
    font-weight: 900;
    text-transform: uppercase;
}

.preview-title {
    margin-top: 18px;
    font-size: 24px;
    line-height: 1.25;
}

.meta-desc {
    color: var(--muted);
    line-height: 1.6;
}

.metrics {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin: 18px 0;
}

.metric {
    padding: 14px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: rgba(15, 23, 42, .56);
}

.metric span {
    display: block;
    color: var(--muted);
    font-size: 12px;
    margin-bottom: 5px;
}

.metric strong {
    display: block;
    color: white;
    font-size: 20px;
}

.article {
    max-height: 540px;
    overflow: auto;
    color: #CBD5E1;
    line-height: 1.7;
    border-top: 1px solid var(--line);
    padding-top: 16px;
}

.article :is(h1, h2, h3) {
    color: white;
    line-height: 1.25;
    margin: 18px 0 10px;
}

.article h1 {
    font-size: 24px;
}

.article h2 {
    font-size: 20px;
}

.article h3 {
    font-size: 17px;
}

.article p,
.article ul,
.article ol {
    margin: 0 0 14px;
}

.article a {
    color: var(--accent);
}

.preview-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 18px;
}

.publish-url {
    min-width: 0;
    color: var(--accent);
    font-size: 13px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.view-btn {
    border: 1px solid rgba(20, 184, 166, .45);
    color: white;
    background: rgba(20, 184, 166, .16);
    border-radius: 8px;
    padding: 10px 14px;
    text-decoration: none;
    font-weight: 800;
    white-space: nowrap;
}

@keyframes glow {
    0%, 100% { box-shadow: 0 0 0 rgba(16, 232, 160, .15), 0 0 10px rgba(16, 232, 160, .45); }
    50% { box-shadow: 0 0 0 8px rgba(16, 232, 160, .08), 0 0 22px rgba(16, 232, 160, .72); }
}

@keyframes iconPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.07); }
}

@keyframes pop {
    0% { transform: scale(.5); }
    70% { transform: scale(1.18); }
    100% { transform: scale(1); }
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeUp {
    to { opacity: 1; transform: translateY(0); }
}

@keyframes blink {
    50% { opacity: 0; }
}

@keyframes shimmer {
    from { transform: translateX(-100%); }
    to { transform: translateX(100%); }
}

@media (max-width: 1180px) {
    .layout {
        grid-template-columns: 1fr;
    }

    .preview {
        position: static;
    }
}

@media (max-width: 860px) {
    .app {
        grid-template-columns: 1fr;
    }

    .sidebar {
        position: relative;
        height: auto;
    }

    .topbar,
    .section-head,
    .preview-actions {
        align-items: flex-start;
        flex-direction: column;
    }

    .setup-form,
    .agent-grid {
        grid-template-columns: 1fr;
    }

    .user-chip {
        width: 100%;
    }
}
</style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <?php if (file_exists(__DIR__ . '/../assets/images/logo.png')): ?>
                <img src="../assets/images/logo.png" alt="Writemize logo">
            <?php else: ?>
                <div class="brand-mark">W</div>
            <?php endif; ?>
            <div>
                <h1>Writemize</h1>
                <p>Autonomous blog engine</p>
            </div>
        </div>

        <nav class="nav" aria-label="Main sidebar">
            <a class="nav-item active" href="index.php">
                <span class="nav-left"><span class="icon">H</span>Dashboard</span>
            </a>
            <a class="nav-item" href="#business-setup">
                <span class="nav-left"><span class="icon">B</span>Business Setup</span>
            </a>
            <a class="nav-item" href="#activity">
                <span class="nav-left"><span class="icon">S</span>Scout</span>
                <span class="status-pill"><span id="dot-scout" class="dot running"></span><span id="label-scout">Running</span></span>
            </a>
            <a class="nav-item" href="#activity">
                <span class="nav-left"><span class="icon">R</span>Radar</span>
                <span class="status-pill"><span id="dot-radar" class="dot waiting"></span><span id="label-radar">Waiting</span></span>
            </a>
            <a class="nav-item" href="#preview">
                <span class="nav-left"><span class="icon">Q</span>Quill</span>
                <span class="status-pill"><span id="dot-quill" class="dot waiting"></span><span id="label-quill">Waiting</span></span>
            </a>
            <a class="nav-item" href="#preview">
                <span class="nav-left"><span class="icon">W</span>Warden</span>
                <span class="status-pill"><span id="dot-warden" class="dot waiting"></span><span id="label-warden">Waiting</span></span>
            </a>
            <a class="nav-item" href="#preview">
                <span class="nav-left"><span class="icon">P</span>Pulse</span>
                <span class="status-pill"><span id="dot-pulse" class="dot waiting"></span><span id="label-pulse">Waiting</span></span>
            </a>
        </nav>

        <div class="agent-status">
            <h3 class="status-title">Agent Status</h3>
            <div class="status-list">
                <div id="side-scout" class="status-row"><span class="check">o</span>Scout Pending</div>
                <div id="side-radar" class="status-row"><span class="check">o</span>Radar Pending</div>
                <div id="side-quill" class="status-row"><span class="check">o</span>Quill Pending</div>
                <div id="side-warden" class="status-row"><span class="check">o</span>Warden Pending</div>
                <div id="side-pulse" class="status-row"><span class="check">o</span>Pulse Pending</div>
            </div>
        </div>

        <div class="legend">
            <h3 class="legend-title">Status Guide</h3>
            <div class="legend-grid">
                <span class="status-pill"><span class="dot running"></span>Running</span>
                <span class="status-pill"><span class="dot waiting"></span>Waiting</span>
                <span class="status-pill"><span class="dot complete"></span>Complete</span>
                <span class="status-pill"><span class="dot error"></span>Error</span>
            </div>
        </div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div>
                <div class="eyebrow">Business Setup</div>
                <h2>AI Blog Generation Pipeline</h2>
                <p>Welcome, <?= e($userName) ?>. Scout, Radar, Quill, Warden, and Pulse are ready.</p>
            </div>
            <div class="user-chip">
                <div class="avatar"><?= e(strtoupper(substr($userName, 0, 1))) ?></div>
                <div>
                    <strong><?= e($companyName ?: 'Writemize Business') ?></strong>
                    <p><?= e($userEmail) ?></p>
                </div>
            </div>
        </header>

        <div class="layout">
            <div class="stack">
                <section id="business-setup" class="card section business-setup-container">
                    <div class="section-head">
                        <div>
                            <h2>Business Setup</h2>
                            <p>Enter the source site and publish time, then launch the full agent pipeline.</p>
                        </div>
                        <div id="liveClock" class="clock">--:--:--</div>
                    </div>

                    <form id="pipelineForm" class="setup-form">
                        <div class="field">
                            <label for="websiteUrl">Website URL</label>
                            <input id="websiteUrl" type="url" value="https://example.com" placeholder="https://yourbusiness.com">
                        </div>
                        <div class="field">
                            <label for="publishTime">Publish Time</label>
                            <input id="publishTime" name="publish_time" type="time" value="10:02" step="60">
                        </div>
                        <button id="launchBtn" class="launch" type="submit">Launch Pipeline</button>
                    </form>
                </section>

                <section id="activity" class="card terminal">
                    <div class="terminal-header">
                        <div>
                            <div class="terminal-title">Writemize AI Terminal</div>
                            <p>Live Agent Activity</p>
                        </div>
                        <div class="terminal-dots"><span></span><span></span><span></span></div>
                    </div>
                    <div id="terminalBody" class="terminal-body" aria-live="polite"></div>
                </section>

                <section class="card section">
                    <div class="section-head">
                        <div>
                            <h2>Agent Progress</h2>
                            <p>Each agent updates live as the article moves from research to publishing.</p>
                        </div>
                    </div>
                    <div class="agent-grid">
                        <div id="card-scout" class="agent-card running">
                            <div class="agent-name">Scout <span id="pct-scout">0%</span></div>
                            <div id="task-scout" class="agent-task">Reading website</div>
                            <div class="progress"><span id="bar-scout"></span></div>
                            <div class="progress-meta"><span id="state-scout">Running</span><span>Website intelligence</span></div>
                        </div>
                        <div id="card-radar" class="agent-card">
                            <div class="agent-name">Radar <span id="pct-radar">0%</span></div>
                            <div id="task-radar" class="agent-task">Waiting</div>
                            <div class="progress"><span id="bar-radar"></span></div>
                            <div class="progress-meta"><span id="state-radar">Waiting</span><span>Topic research</span></div>
                        </div>
                        <div id="card-quill" class="agent-card">
                            <div class="agent-name">Quill <span id="pct-quill">0%</span></div>
                            <div id="task-quill" class="agent-task">Waiting</div>
                            <div class="progress"><span id="bar-quill"></span></div>
                            <div class="progress-meta"><span id="state-quill">Waiting</span><span>Article writing</span></div>
                        </div>
                    </div>
                </section>
            </div>

            <aside id="preview" class="card section preview">
                <div class="section-head">
                    <div>
                        <h2>Live Generated Blog Preview</h2>
                        <p id="previewStatus">Waiting for Scout to finish source reading.</p>
                    </div>
                    <span class="status-pill"><span id="publishDot" class="dot waiting"></span><span id="publishState">Draft</span></span>
                </div>

                <div id="featuredImage" class="image-box">Featured Image</div>
                <h3 id="seoTitle" class="preview-title">How AI is Transforming Digital Marketing</h3>
                <p id="metaDescription" class="meta-desc">Meta description will be generated after Radar identifies the strongest topic angle.</p>

                <div class="metrics">
                    <div class="metric"><span>SEO Score</span><strong id="seoScore">--/100</strong></div>
                    <div class="metric"><span>Word Count</span><strong id="wordCount">0</strong></div>
                    <div class="metric"><span>Reading Time</span><strong id="readingTime">--</strong></div>
                    <div class="metric"><span>Status</span><strong id="blogStatus">Queued</strong></div>
                </div>

                <div id="articlePreview" class="article">
                    The 1500 word blog preview will stream here as Quill writes, Warden reviews SEO, and Pulse prepares the publish URL.
                </div>

                <div class="preview-actions">
                    <div id="publishUrl" class="publish-url">Publish URL pending</div>
                    <a class="view-btn" href="#" aria-disabled="true">View Blog</a>
                </div>
            </aside>
        </div>
    </main>
</div>

<script>
const agents = ["scout", "radar", "quill", "warden", "pulse"];
const terminal = document.getElementById("terminalBody");
const launchBtn = document.getElementById("launchBtn");
const form = document.getElementById("pipelineForm");
const companyName = <?= json_encode($companyName ?: 'Writemize Business') ?>;
let progressTimer = null;

function pad(value) {
    return String(value).padStart(2, "0");
}

function timeStamp() {
    const now = new Date();
    return `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
}

function setClock() {
    document.getElementById("liveClock").textContent = timeStamp();
}

async function addTerminalLine(text, ok) {
    const line = document.createElement("div");
    const time = document.createElement("span");
    const message = document.createElement("span");
    const cursor = document.createElement("span");
    line.className = "terminal-line";
    time.className = "time";
    time.textContent = `[${timeStamp()}] `;
    message.className = ok ? "ok" : "";
    cursor.className = "cursor";
    line.appendChild(time);
    line.appendChild(message);
    line.appendChild(cursor);
    terminal.appendChild(line);

    const output = `${ok ? "OK" : ">"} ${text}`;
    for (let index = 0; index < output.length; index += 1) {
        message.textContent += output[index];
        terminal.scrollTop = terminal.scrollHeight;
        await new Promise((resolve) => setTimeout(resolve, 16));
    }
    cursor.remove();
}

function setAgentStatus(agent, status) {
    const dot = document.getElementById(`dot-${agent}`);
    const label = document.getElementById(`label-${agent}`);
    if (!dot || !label) return;
    dot.className = `dot ${status}`;
    label.textContent = status.charAt(0).toUpperCase() + status.slice(1);
}

function setAgentProgress(agent, pct, task) {
    const bar = document.getElementById(`bar-${agent}`);
    const pctEl = document.getElementById(`pct-${agent}`);
    const taskEl = document.getElementById(`task-${agent}`);
    const stateEl = document.getElementById(`state-${agent}`);
    const card = document.getElementById(`card-${agent}`);
    if (bar) bar.style.width = `${pct}%`;
    if (pctEl) pctEl.textContent = `${pct}%`;
    if (taskEl) taskEl.textContent = task;
    if (stateEl) stateEl.textContent = pct >= 100 ? "Completed" : "Running";
    if (card) card.classList.toggle("running", pct > 0 && pct < 100);
}

function completeSide(agent) {
    const row = document.getElementById(`side-${agent}`);
    if (!row) return;
    row.classList.add("done");
    row.querySelector(".check").textContent = "OK";
    row.lastChild.textContent = `${agent.charAt(0).toUpperCase() + agent.slice(1)} Complete`;
}

function activateAgent(agent) {
    agents.forEach((name) => {
        const card = document.getElementById(`card-${name}`);
        if (card && name !== agent) card.classList.remove("running");
    });
    setAgentStatus(agent, "running");
}

function finishAgent(agent) {
    setAgentStatus(agent, "complete");
    completeSide(agent);
    const nextIndex = agents.indexOf(agent) + 1;
    if (agents[nextIndex]) setAgentStatus(agents[nextIndex], "running");
}

function updateArticlePreview(article) {
    document.getElementById("seoTitle").textContent = article.title || "Generated Blog Article";
    document.getElementById("metaDescription").textContent = article.meta_description || "SEO meta description generated by Writemize.";
    document.getElementById("seoScore").textContent = `${article.seo_score || 92}/100`;
    document.getElementById("wordCount").textContent = article.word_count || "Generated";
    document.getElementById("readingTime").textContent = article.reading_time || "Ready";
    document.getElementById("blogStatus").textContent = article.status || "Generated";
    document.getElementById("publishUrl").textContent = article.publish_url || "Preview generated";
    document.getElementById("articlePreview").innerHTML = article.html || "<p>No article HTML returned.</p>";
    const featuredImage = document.getElementById("featuredImage");
    featuredImage.textContent = article.image_url ? "" : (article.focus_keyword || "Generated Article");
    featuredImage.style.backgroundImage = article.image_url ? `url("${article.image_url}")` : "";
    featuredImage.style.backgroundSize = "cover";
    featuredImage.style.backgroundPosition = "center";
    document.getElementById("previewStatus").textContent = "Actual OpenAI-generated HTML article loaded.";
    document.getElementById("publishDot").className = "dot complete";
    document.getElementById("publishState").textContent = "Complete";
}

function resetPipeline() {
    if (progressTimer) {
        clearInterval(progressTimer);
        progressTimer = null;
    }
    terminal.innerHTML = "";
    agents.forEach((agent, index) => {
        setAgentStatus(agent, index === 0 ? "running" : "waiting");
        setAgentProgress(agent, 0, index === 0 ? "Reading website" : "Waiting");
        const side = document.getElementById(`side-${agent}`);
        if (side) {
            side.classList.remove("done");
            side.querySelector(".check").textContent = "o";
            side.lastChild.textContent = `${agent.charAt(0).toUpperCase() + agent.slice(1)} Pending`;
        }
    });
    document.getElementById("seoScore").textContent = "--/100";
    document.getElementById("wordCount").textContent = "0";
    document.getElementById("readingTime").textContent = "--";
    document.getElementById("blogStatus").textContent = "Queued";
    document.getElementById("publishUrl").textContent = "Publish URL pending";
    document.getElementById("publishDot").className = "dot waiting";
    document.getElementById("publishState").textContent = "Draft";
    document.getElementById("previewStatus").textContent = "Waiting for Scout to finish source reading.";
    document.getElementById("metaDescription").textContent = "Meta description will be generated after Radar identifies the strongest topic angle.";
    document.getElementById("articlePreview").textContent = "The 1500 word blog preview will stream here as Quill writes, Warden reviews SEO, and Pulse prepares the publish URL.";
    const featuredImage = document.getElementById("featuredImage");
    featuredImage.textContent = "Featured Image";
    featuredImage.style.backgroundImage = "";
}

function startRealProgressAnimation() {
    const states = [
        { agent: "scout", pct: 35, task: "Sending business inputs to backend" },
        { agent: "scout", pct: 78, task: "Reading website context" },
        { agent: "radar", pct: 30, task: "Preparing SEO strategy" },
        { agent: "radar", pct: 70, task: "Choosing search intent angle" },
        { agent: "quill", pct: 28, task: "Waiting for OpenAI article generation" },
        { agent: "quill", pct: 64, task: "Generating long-form HTML" },
        { agent: "warden", pct: 42, task: "Validating response structure" },
        { agent: "pulse", pct: 45, task: "Preparing preview data" }
    ];
    let index = 0;

    progressTimer = setInterval(() => {
        const state = states[Math.min(index, states.length - 1)];
        activateAgent(state.agent);
        setAgentProgress(state.agent, state.pct, state.task);
        index += 1;
    }, 1200);
}

function completeAllAgents() {
    if (progressTimer) {
        clearInterval(progressTimer);
        progressTimer = null;
    }

    agents.forEach((agent) => {
        setAgentProgress(agent, 100, "Completed");
        finishAgent(agent);
    });
}

function markPipelineError(message) {
    if (progressTimer) {
        clearInterval(progressTimer);
        progressTimer = null;
    }

    const activeAgent = agents.find((agent) => {
        const label = document.getElementById(`label-${agent}`);
        return label && label.textContent === "Running";
    }) || "scout";

    setAgentStatus(activeAgent, "error");
    document.getElementById("blogStatus").textContent = "Error";
    document.getElementById("previewStatus").textContent = message;
    document.getElementById("publishDot").className = "dot error";
    document.getElementById("publishState").textContent = "Error";
}

async function runPipeline() {
    resetPipeline();
    launchBtn.disabled = true;
    launchBtn.textContent = "Pipeline Running";
    document.getElementById("blogStatus").textContent = "Running";
    document.getElementById("previewStatus").textContent = "Calling backend pipeline endpoint.";

    await addTerminalLine("Connecting to api/run_pipeline.php", false);
    startRealProgressAnimation();

    try {
        const response = await fetch("../api/run_pipeline.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                website_url: document.getElementById("websiteUrl").value,
                publish_time: document.getElementById("publishTime").value,
                business_name: companyName,
                company_name: companyName
            })
        });

        const rawText = await response.text();
        let data;

        try {
            data = JSON.parse(rawText);
        } catch (error) {
            throw new Error(rawText || "Backend returned an invalid JSON response.");
        }

        if (Array.isArray(data.logs)) {
            for (const log of data.logs) {
                await addTerminalLine(log, data.success);
            }
        }

        if (!response.ok || !data.success) {
            throw new Error(data.error || "Pipeline failed.");
        }

        completeAllAgents();
        updateArticlePreview(data.article || {});
        await addTerminalLine("Actual generated HTML rendered in preview.", true);
    } catch (error) {
        const message = error instanceof Error ? error.message : "Pipeline failed.";
        markPipelineError(message);
        await addTerminalLine(message, false);
    } finally {
        launchBtn.disabled = false;
        launchBtn.textContent = "Launch Pipeline";
    }
}

form.addEventListener("submit", function(event) {
    event.preventDefault();
    runPipeline();
});

const publishTimeInput = document.getElementById("publishTime");
["click", "focus"].forEach((eventName) => {
    publishTimeInput.addEventListener(eventName, function() {
        if (typeof publishTimeInput.showPicker === "function") {
            try {
                publishTimeInput.showPicker();
            } catch (error) {
                publishTimeInput.focus();
            }
        }
    });
});

setClock();
setInterval(setClock, 1000);
</script>
</body>
</html>
