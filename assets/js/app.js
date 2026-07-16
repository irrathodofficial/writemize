const agents = ["scout", "radar", "quill", "warden", "pulse", "publisher"];
const form = document.getElementById("pipelineForm");
const launchBtn = document.getElementById("launchBtn");
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
    label.textContent = state;
    bar.style.width = `${pct}%`;
}

function resetAgents() {
    agents.forEach((agent) => setAgent(agent, "Waiting", 0));
}

async function animateAgents() {
    for (const agent of agents) {
        setAgent(agent, "Running", 35);
        await new Promise((resolve) => setTimeout(resolve, 320));
        setAgent(agent, "Running", 75);
        await new Promise((resolve) => setTimeout(resolve, 320));
        setAgent(agent, "Done", 100);
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
    launchBtn.textContent = "Running";
    runState.textContent = "Running";
    logLine("Launching Writemize pipeline.");

    const animation = animateAgents();

    try {
        const response = await fetch("../api/run_pipeline.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "Accept": "application/json" },
            body: JSON.stringify(Object.fromEntries(new FormData(form)))
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || "Pipeline failed.");
        }

        for (const line of data.logs || []) {
            logLine(line);
        }

        await animation;
        renderArticle(data.article || {});
        runState.textContent = data.openai_configured ? "Complete" : "Complete (local fallback)";
        logLine("Dashboard preview updated.");
        await loadRecentPosts();
    } catch (error) {
        agents.forEach((agent) => setAgent(agent, "Stopped", 0));
        runState.textContent = "Error";
        logLine(error.message || "Pipeline failed.");
    } finally {
        launchBtn.disabled = false;
        launchBtn.textContent = "Run Pipeline";
    }
});

updateClock();
setInterval(updateClock, 1000);
loadRecentPosts();
