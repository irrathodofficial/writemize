const agents = ["scout", "radar", "quill", "warden", "pulse", "publisher"];
const form = document.getElementById("pipelineForm");
const launchBtn = document.getElementById("launchBtn");
const activateBtn = document.getElementById("activateBtn");
const terminalBody = document.getElementById("terminalBody");
const runState = document.getElementById("runState");

function updateClock() {
    const now = new Date();
    document.getElementById("clock").textContent = now.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit"
    });
}

function logLine(message) {
    const p = document.createElement("p");
    p.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
    terminalBody.appendChild(p);
    terminalBody.scrollTop = terminalBody.scrollHeight;
}

function setAgent(agent, state, pct) {
    const card = document.getElementById(`agent-${agent}`);
    const label = document.getElementById(`state-${agent}`);
    const bar = document.getElementById(`bar-${agent}`);

    card.classList.toggle("active", state === "Running");
    card.classList.toggle("done", state === "Done");
    card.classList.toggle("error", state === "Error");
    label.textContent = state;
    bar.style.width = `${pct}%`;
}

function resetAgents() {
    agents.forEach((agent) => setAgent(agent, "Waiting", 0));
}

function formPayload() {
    return JSON.stringify(Object.fromEntries(new FormData(form)));
}

function updateBusinessMemory(business) {
    return business;
}

function applyPipelineState(completedAgents, failedAgent) {
    const completed = Array.isArray(completedAgents) ? completedAgents : [];
    let failedSeen = false;

    agents.forEach((agent) => {
        if (completed.includes(agent)) {
            setAgent(agent, "Done", 100);
            return;
        }

        if (failedAgent === agent) {
            setAgent(agent, "Error", 100);
            failedSeen = true;
            return;
        }

        setAgent(agent, failedSeen ? "Waiting" : "Waiting", 0);
    });
}

function handleLiveEvent(event) {
    if (event.type === "log" && event.message) {
        logLine(event.message);
        return;
    }

    if (event.type === "agent" && event.agent) {
        setAgent(event.agent, event.state || "Running", Number(event.pct || 35));
    }
}

function renderArticle(article) {
    document.getElementById("articleTitle").textContent = article.title || "Generated Blog Article";
    document.getElementById("metaDescription").textContent = article.meta_description || "";
    document.getElementById("seoBadge").textContent = `SEO ${article.seo_score || "--"}`;
    document.getElementById("wordCount").textContent = article.word_count || 0;
    document.getElementById("readingTime").textContent = article.reading_time || "--";
    document.getElementById("postStatus").textContent = article.status || "ready";
    document.getElementById("articleHtml").innerHTML = article.html || "<p>No article returned.</p>";

    const image = document.getElementById("imagePreview");
    if (article.image_url) {
        image.style.backgroundImage = `url("${article.image_url}")`;
        image.textContent = "";
    } else {
        image.style.backgroundImage = "";
        image.textContent = article.image_prompt ? "DALL-E prompt prepared" : "Featured image fallback";
    }

    const link = document.getElementById("publishUrl");
    link.textContent = article.publish_url || "Publish URL pending";
    if (article.publish_url) {
        link.href = article.publish_url;
        link.removeAttribute("aria-disabled");
        link.classList.remove("is-pending");
    }
}

async function loadRecentPosts() {
    const holder = document.getElementById("recentPosts");

    try {
        const response = await fetch("../api/recent_posts.php");
        const data = await response.json();
        const posts = data.posts || [];

        if (posts.length === 0) {
            holder.innerHTML = "<p>No runs yet.</p>";
            return;
        }

        holder.innerHTML = posts.map((post) => `
            <a class="recent-item" href="${escapeHtml(post.publish_url || "#")}">
                <strong>${escapeHtml(post.title)}</strong>
                <small>${escapeHtml(post.status)} | SEO ${Number(post.seo_score || 0)} | ${escapeHtml(post.scheduled_for || post.created_at || "")}</small>
            </a>
        `).join("");
    } catch (error) {
        holder.innerHTML = "<p>Recent runs unavailable until MySQL is connected.</p>";
    }
}

function escapeHtml(value) {
    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

form.addEventListener("submit", async (event) => {
    event.preventDefault();

    resetAgents();
    terminalBody.innerHTML = "";
    launchBtn.disabled = true;
    activateBtn.disabled = true;
    launchBtn.textContent = "Running";
    runState.textContent = "Running";
    logLine("Launching Writemize pipeline.");

    setAgent("scout", "Running", 20);

    try {
        const response = await fetch("../api/run_pipeline_live.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json" },
            body: formPayload()
        });

        if (!response.body) {
            throw new Error("Live pipeline stream is unavailable in this browser.");
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = "";
        let finalEvent = null;

        while (true) {
            const { value, done } = await reader.read();
            if (done) break;

            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split("\n");
            buffer = lines.pop() || "";

            for (const line of lines) {
                if (!line.trim()) continue;
                const eventData = JSON.parse(line);
                handleLiveEvent(eventData);
                if (eventData.type === "final") {
                    finalEvent = eventData;
                }
            }
        }

        if (buffer.trim()) {
            const eventData = JSON.parse(buffer);
            handleLiveEvent(eventData);
            if (eventData.type === "final") {
                finalEvent = eventData;
            }
        }

        const data = finalEvent || { success: false, error: "Pipeline ended without a final response." };
        applyPipelineState(data.completed_agents || [], data.failed_agent || null);

        if (!data.success) {
            throw new Error(data.error || "Pipeline failed.");
        }

        renderArticle(data.article || {});
        runState.textContent = data.openai_configured ? "Complete" : "Complete (local fallback)";
        logLine("Dashboard preview updated.");
        await loadRecentPosts();
    } catch (error) {
        if (!agents.some((agent) => document.getElementById(`state-${agent}`).textContent === "Error")) {
            setAgent("scout", "Error", 100);
        }
        runState.textContent = "Error";
        logLine(error.message || "Pipeline failed.");
    } finally {
        launchBtn.disabled = false;
        activateBtn.disabled = false;
        launchBtn.textContent = "Run AI Agent";
    }
});

activateBtn.addEventListener("click", async () => {
    resetAgents();
    terminalBody.innerHTML = "";
    activateBtn.disabled = true;
    launchBtn.disabled = true;
    activateBtn.textContent = "Activating";
    runState.textContent = "Activating";
    setAgent("scout", "Running", 45);
    logLine("Saving business URL, daily time, and activating Scout Agent.");

    try {
        const response = await fetch("../api/activate_agent.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json" },
            body: formPayload()
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || "Activation failed.");
        }

        for (const line of data.logs || []) {
            logLine(line);
        }

        setAgent("scout", "Done", 100);
        updateBusinessMemory(data.business);
        runState.textContent = "Activated";
        logLine("AI Agent is activated. Run AI Agent now, or cron will run it at the saved daily time.");
    } catch (error) {
        setAgent("scout", "Error", 100);
        runState.textContent = "Error";
        logLine(error.message || "Activation failed.");
    } finally {
        activateBtn.disabled = false;
        launchBtn.disabled = false;
        activateBtn.textContent = "AI Agent Activate";
    }
});

updateClock();
setInterval(updateClock, 1000);
loadRecentPosts();
