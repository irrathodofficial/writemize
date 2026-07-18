const agents = ["scout", "radar", "quill", "warden", "pulse", "publisher"];
const form = document.getElementById("pipelineForm");
const launchBtn = document.getElementById("launchBtn");
const activateBtn = document.getElementById("activateBtn");
const terminalBody = document.getElementById("terminalBody");
const runState = document.getElementById("runState");
let activeAgent = null;
let heartbeatTimer = null;
let heartbeatIndex = 0;
const agentDetails = {
    scout: {
        icon: "fa-solid fa-binoculars",
        title: "Scout Agent",
        stage: "Business Intelligence",
        summary: "Scout reads the business website, extracts positioning, audience, tone, niche, and the content strategy that all later agents use.",
        inputs: ["Business name", "Website URL", "Readable website text", "Saved business profile"],
        outputs: ["Business niche", "Audience profile", "Brand tone", "Content strategy prompt", "Stored Scout context in MySQL"]
    },
    radar: {
        icon: "fa-solid fa-satellite-dish",
        title: "Radar Agent",
        stage: "Trend and SEO Research",
        summary: "Radar turns Scout context into search-driven topic opportunities by analyzing intent, keyword angle, competitor-style positioning, and viral relevance.",
        inputs: ["Scout business context", "Niche and audience", "Content strategy", "Existing topic history"],
        outputs: ["SEO topic", "Focus keyword", "Search intent", "Related keywords", "Competitor angles"]
    },
    quill: {
        icon: "fa-solid fa-feather-pointed",
        title: "Quill Agent",
        stage: "Article and Image Creation",
        summary: "Quill writes the blog in clean semantic HTML and prepares the DALL-E featured image brief using the selected topic and brand context.",
        inputs: ["Radar topic", "Focus keyword", "Scout tone", "Audience and business strategy"],
        outputs: ["SEO article draft", "Meta description", "Featured image prompt", "Generated image saved locally"]
    },
    warden: {
        icon: "fa-solid fa-shield-halved",
        title: "Warden Agent",
        stage: "Quality Control",
        summary: "Warden audits the article for structure, readability, metadata, keyword usage, and readiness before the post can move forward.",
        inputs: ["Quill article HTML", "Focus keyword", "Meta description", "Article structure"],
        outputs: ["SEO score", "Word count", "Reading time", "Approved or review-ready status"]
    },
    pulse: {
        icon: "fa-solid fa-wave-square",
        title: "Pulse Agent",
        stage: "Publishing Rhythm",
        summary: "Pulse prepares the schedule and keeps the daily publishing cadence aligned with the business posting time.",
        inputs: ["Publish time", "Business schedule", "Approved article", "Daily cron rules"],
        outputs: ["Scheduled timestamp", "Daily cadence status", "Cron-ready publishing handoff"]
    },
    publisher: {
        icon: "fa-solid fa-paper-plane",
        title: "Publisher Agent",
        stage: "Final Blog Handoff",
        summary: "Publisher creates the public blog URL, final status, and stores the finished post so it appears in Preview and All Blogs.",
        inputs: ["Approved article", "Scheduled data", "Slug", "Featured image URL"],
        outputs: ["Public blog URL", "Saved blog post", "Preview page", "All Blogs library entry"]
    }
};

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

function startHeartbeat() {
    stopHeartbeat();
    heartbeatTimer = setInterval(() => {
        if (!activeAgent || runState.textContent !== "Running") return;

        const messages = {
            scout: ["Scout is reading the website content.", "Scout is extracting brand context and audience signals."],
            radar: ["Radar is analyzing topic angles and search intent.", "Radar is matching keyword opportunities to the business."],
            quill: ["Quill is drafting the website-specific article with OpenAI.", "Quill is preparing the featured image brief and article structure."],
            warden: ["Warden is auditing readability, metadata, and SEO quality.", "Warden is calculating score, word count, and content readiness."],
            pulse: ["Pulse is preparing schedule and daily publishing rhythm.", "Pulse is aligning the post with the saved publish time."],
            publisher: ["Publisher is creating the final blog URL and handoff.", "Publisher is saving the article into the blog archive."]
        };
        const list = messages[activeAgent] || ["Writemize agents are still working."];
        logLine(list[heartbeatIndex % list.length]);
        heartbeatIndex += 1;
    }, 14000);
}

function stopHeartbeat() {
    if (heartbeatTimer) {
        clearInterval(heartbeatTimer);
        heartbeatTimer = null;
    }
    heartbeatIndex = 0;
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

function fillList(element, items) {
    element.innerHTML = "";
    items.forEach((item) => {
        const li = document.createElement("li");
        li.textContent = item;
        element.appendChild(li);
    });
}

function openAgentModal(agent) {
    const detail = agentDetails[agent];
    const modal = document.getElementById("agentModal");
    if (!detail || !modal) return;

    document.getElementById("agentModalIcon").innerHTML = `<i class="${detail.icon}"></i>`;
    document.getElementById("agentModalStage").textContent = detail.stage;
    document.getElementById("agentModalTitle").textContent = detail.title;
    document.getElementById("agentModalSummary").textContent = detail.summary;
    fillList(document.getElementById("agentModalInputs"), detail.inputs);
    fillList(document.getElementById("agentModalOutputs"), detail.outputs);

    modal.classList.add("open");
    modal.setAttribute("aria-hidden", "false");
    document.body.classList.add("modal-open");
}

function closeAgentModal() {
    const modal = document.getElementById("agentModal");
    if (!modal) return;
    modal.classList.remove("open");
    modal.setAttribute("aria-hidden", "true");
    document.body.classList.remove("modal-open");
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
        if (event.state === "Running") {
            activeAgent = event.agent;
        }
        if (event.state === "Done" || event.state === "Error") {
            activeAgent = null;
        }
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

function scrollToBlogPreview() {
    const preview = document.getElementById("preview");
    if (!preview) return;

    preview.scrollIntoView({ behavior: "smooth", block: "start" });

    let userMoved = false;
    const markMoved = () => {
        userMoved = true;
    };
    const options = { once: true, passive: true };

    window.addEventListener("wheel", markMoved, options);
    window.addEventListener("touchstart", markMoved, options);
    window.addEventListener("keydown", markMoved, { once: true });

    setTimeout(() => {
        window.removeEventListener("wheel", markMoved);
        window.removeEventListener("touchstart", markMoved);
        window.removeEventListener("keydown", markMoved);

        if (!userMoved) {
            preview.scrollIntoView({ behavior: "smooth", block: "start" });
        }
    }, 4500);
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
    activeAgent = "scout";
    startHeartbeat();

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
        scrollToBlogPreview();
        runState.textContent = "Complete";
        stopHeartbeat();
        logLine("Dashboard preview updated.");
        await loadRecentPosts();
    } catch (error) {
        stopHeartbeat();
        if (!agents.some((agent) => document.getElementById(`state-${agent}`).textContent === "Error")) {
            setAgent("scout", "Error", 100);
        }
        activeAgent = null;
        runState.textContent = "Error";
        logLine(error.message || "Pipeline failed.");
    } finally {
        stopHeartbeat();
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

document.querySelectorAll(".agent-card[data-agent]").forEach((card) => {
    card.addEventListener("click", () => openAgentModal(card.dataset.agent));
    card.addEventListener("keydown", (event) => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openAgentModal(card.dataset.agent);
        }
    });
});

document.querySelectorAll("[data-close-agent-modal]").forEach((element) => {
    element.addEventListener("click", closeAgentModal);
});

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
        closeAgentModal();
    }
});

updateClock();
setInterval(updateClock, 1000);
loadRecentPosts();
