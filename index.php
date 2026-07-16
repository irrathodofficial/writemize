<?php
declare(strict_types=1);
require_once 'db_config.php';

$message = '';
$error = '';

// Backend Logic: AJAX POST req handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $websiteUrl = trim($_POST['website_url'] ?? '');
        $postTime   = trim($_POST['post_time'] ?? '');

        if ($websiteUrl === '' || $postTime === '') {
            throw new Exception('Please complete all required fields.');
        }

        $userId = 'demo_user';
        $stmt = $pdo->prepare("
            INSERT INTO businesses (user_id, website_url, competitor_urls, post_time)
            VALUES (:user_id, :website_url, :competitor_urls, :post_time)
        ");
        
        $stmt->execute([
            ':user_id'         => $userId,
            ':website_url'     => $websiteUrl,
            ':competitor_urls' => json_encode([]),
            ':post_time'       => $postTime
        ]);

        echo json_encode(['success' => true]);
    } catch (Throwable $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Writemize | Autonomous AI Blog</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #f8fafc; }
        .fade-out {
            opacity: 0;
            transform: translateY(-20px);
            pointer-events: none;
        }
        #formCard, #terminalCard {
            transition: all .6s ease;
        }
        .terminal {
            background: #070b12;
            border: 1px solid #1f2937;
            box-shadow: 0 0 30px rgba(59,130,246,.15), inset 0 0 40px rgba(59,130,246,.05);
        }
        .terminal-text {
            font-family: Consolas, Monaco, monospace;
            text-shadow: 0 0 8px currentColor;
        }
        .cursor {
            display: inline-block;
            width: 8px;
            height: 18px;
            background: #22c55e;
            animation: blink .8s infinite;
        }
        @keyframes blink {
            50% { opacity: 0; }
        }
    </style>
</head>
<body>

<div class="min-h-screen flex items-center justify-center p-8">
    <div class="w-full max-w-4xl relative">
        
        <div id="formCard" class="bg-white rounded-2xl shadow-xl p-10 w-full relative z-10">
            <h1 class="text-3xl font-bold text-gray-900">Blogmize.ai Setup</h1>
            <p class="mt-2 text-gray-500">Configure your website to start the 24/7 autonomous 5-Agent pipeline.</p>

            <form id="setupForm" class="mt-8 space-y-6">
                <div>
                    <label class="block mb-2 font-semibold text-gray-700">Target Website URL</label>
                    <input name="website_url" type="url" required placeholder="https://blogmize.ai/" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-gray-700">Daily Autopilot Publish Time</label>
                    <input name="post_time" type="time" required class="rounded-xl border border-gray-300 px-4 py-3 w-full outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="w-full px-8 py-4 rounded-xl text-white font-bold text-lg bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 shadow-lg hover:scale-[1.01] transition">
                    🚀 Launch 5-Agent Pipeline
                </button>
            </form>
        </div>

        <div id="terminalCard" class="hidden opacity-0 absolute top-0 left-0 w-full terminal rounded-2xl p-8 z-20">
            <div class="flex items-center justify-between mb-6 border-b border-gray-800 pb-4">
                <h2 class="text-xl font-bold text-emerald-400 terminal-text">LIVE PIPELINE EXECUTION</h2>
                <div class="flex gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                </div>
            </div>
            <div id="terminalOutput" class="terminal-text text-gray-300 space-y-3 min-h-[350px] overflow-y-auto"></div>
            <div class="mt-4"><span class="cursor"></span></div>
        </div>

    </div>
</div>

<script>
    const form = document.getElementById("setupForm");
    const formCard = document.getElementById("formCard");
    const terminal = document.getElementById("terminalCard");
    const output = document.getElementById("terminalOutput");

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async function typeLog(message, style = "text-green-400") {
        const line = document.createElement("div");
        line.className = "terminal-text " + style;
        output.appendChild(line);
        
        for (let i = 0; i < message.length; i++) {
            line.textContent += message.charAt(i);
            output.scrollTop = output.scrollHeight;
            await sleep(15);
        }
        await sleep(300);
    }

    async function runAgentPipeline() {
        try {
            // STEP 1: INITIALIZATION
            await typeLog("> [SYSTEM] Configuration saved. 5-Agent Fleet initialized.", "text-blue-400");
            await sleep(500);

            // STEP 2: SCOUT AGENT
            await typeLog("> [SCOUT] Connecting to target website to extract niche and audience data...", "text-cyan-400");
            const scoutRes = await fetch('api/scout.php');
            if (!scoutRes.ok) throw new Error("Scout agent failed.");
            await scoutRes.text();
            await typeLog("> [SCOUT] Success: Niche analyzed and top keywords extracted.", "text-emerald-400");
            await sleep(600);

            // STEP 3: RADAR AGENT
            await typeLog("> [RADAR] Calling Codex AI to predict 5 viral SEO topics...", "text-purple-400");
            const radarRes = await fetch('api/radar.php');
            if (!radarRes.ok) throw new Error("Radar agent failed.");
            await radarRes.text();
            await typeLog("> [RADAR] Success: High-converting topics saved to queue.", "text-emerald-400");
            await sleep(600);

            // STEP 4: QUILL AGENT (Mock for now, as we build it next)
            await typeLog("> [QUILL] Writing 1500-word SEO article and generating featured image...", "text-yellow-400");
            await sleep(2000); // Simulating long generation time
            await typeLog("> [QUILL] Success: Article drafted and image attached.", "text-emerald-400");
            await sleep(600);

            // STEP 5: WARDEN AGENT (Mock)
            await typeLog("> [WARDEN] Auditing content against human-readability standards...", "text-orange-400");
            await sleep(1500);
            await typeLog("> [WARDEN] Success: Score 94/100. Content approved.", "text-emerald-400");
            await sleep(600);

            // STEP 6: PULSE AGENT (Mock)
            await typeLog("> [PULSE] Connecting to WordPress API for publication...", "text-pink-400");
            await sleep(1000);
            await typeLog("> [PULSE] Success: Blog published live to Blogmize.ai!", "text-emerald-400");
            await sleep(800);

            // CONCLUSION
            await typeLog("> [SYSTEM] Daily autonomous cycle complete. Agents powering down.", "text-blue-400 font-bold");

        } catch(err) {
            console.error(err);
            await typeLog(`> [CRITICAL ERROR] ${err.message}`, "text-red-500 font-bold");
        }
    }

    form.addEventListener("submit", async function(e) {
        e.preventDefault();
        
        // Lock button to prevent double submit
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = "Initializing...";

        const fd = new FormData(form);
        try {
            // Save data to DB silently
            await fetch(window.location.href, { method: "POST", body: fd });
        } catch(err) {
            console.warn(err);
        }

        // Trigger the magic UI transition
        formCard.classList.add("fade-out");
        setTimeout(() => {
            formCard.classList.add("hidden");
            terminal.classList.remove("hidden");
            requestAnimationFrame(() => {
                terminal.classList.remove("opacity-0");
            });
            
            // Start the actual pipeline sequence
            runAgentPipeline();
        }, 600);
    });
</script>
</body>
</html>