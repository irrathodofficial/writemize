# Writemize

Autonomous AI blogging dashboard, built for OpenAI Build Week.

Writemize takes a business website URL and turns it into a running content pipeline. Give it a business name, a website, and a publishing time, and it researches the business, finds a fresh topic, writes a full SEO article, generates a featured image, checks the quality, schedules it, and publishes a live public page - on its own, every day.

**Live Demo:** https://writemize.online

---

## Why I built this

Every business owner knows they should be blogging regularly. Almost none of them actually do it, because the day-to-day process is a grind - pick a topic, stay on-brand, write something worth reading, make a matching image, check it's SEO-sound, then do it all again tomorrow. Most people quit after a few posts.

Writemize replaces that entire loop with a small team of AI agents that pass work to each other automatically, with a live log so you can watch exactly what each one is doing.

---

## The agents

- **Scout** - reads the business website and learns its niche, tone, and audience, then stores that as long-term memory
- **Radar** - acts like an SEO strategist, picks a fresh topic every run, and checks it against post history so nothing repeats
- **Quill** - writes the full article in structured HTML, writes the prompt for the featured image, generates the image, and saves it directly to the assets/images/blogimages/ folder.
- **Warden** - audits the draft for SEO score, word count, heading structure, and readability before it's approved
- **Pulse** - applies the business's publishing schedule
- **Publisher** - creates the slug, the public URL, and hands the post off to the archive

Scout's brand memory is stored in MySQL and reused by every later agent, so Radar and Quill stay consistent with the business instead of drifting from post to post.

---

## Core features

- User registration and login
- MySQL-backed business profiles with persistent brand memory
- Configurable website URL and daily publish time per business
- Live agent log with real-time updates on the dashboard
- Anti-duplication check against previous post history
- Article generation with GPT-5.6
- Featured image generation with gpt-image-1.5
- Local image storage in `assets/images/blogimages/`
- SEO score, word count, and reading time tracking on every post
- Public preview page for each generated article
- Content library with edit and delete controls
- Daily cron runner for unattended publishing
- Placeholder pages for future website and social integrations

---

## Tech stack

- **Backend:** PHP 8.3
- **Database:** MySQL
- **Frontend:** HTML, CSS, vanilla JavaScript
- **AI text model:** GPT-5.6
- **AI image model:** gpt-image-1.5
- **Local environment:** WAMP / Apache / MySQL
- **Automation:** PHP cron
- **Build assistant:** OpenAI Codex

---

## How GPT-5.6 is used

GPT-5.6 powers the reasoning-heavy content steps in Writemize:
- **Scout** uses GPT-5.6 to process raw website text and intelligently extract the brand's core context (niche, tone, and target audience).
- **Radar** uses GPT-5.6 to interpret this business context and choose fresh SEO topic opportunities.
- **Quill** uses GPT-5.6 to write structured, production-ready SEO blog posts highly tailored to the specific business, and simultaneously generates a highly contextual featured image prompt for the visual agent.
- **Warden** uses GPT-5.6 to act as the final editorial auditor, deeply analyzing the generated article for SEO quality, readability, and structural compliance before approving it for publishing.
- GPT-5.6 receives recent post history so it can reason about avoiding duplicate content and producing a new angle for every single run.
- Configuration is handled dynamically through the `.env` file (`OPENAI_MODEL=gpt-5.6`).

---

## How Codex was used

Codex was the engineering partner for this build, from the schema to the last bug fix.

It helped with the PHP project structure, the MySQL schema, the authentication flow, the agent orchestration and how data passes between agents, the live NDJSON streaming for the dashboard log, the dashboard UI itself, the blog library CRUD screens, the public preview page, the cron script, and the OpenAI API integration for both text and images. It also helped debug real issues along the way - SSL problems on local WAMP, unsupported model parameters, timeouts, and streaming output. Every piece of Codex-generated code was read and understood before it was committed.

---

## Installation

### Requirements

- PHP 8.3
- MySQL 8 or compatible MariaDB
- Apache/Nginx or WAMP/XAMPP
- PHP extensions: PDO MySQL, cURL, JSON, OpenSSL
- An OpenAI API key

### 1. Get the code

Place the project in your web server root.

```
Example for WAMP: C:\wamp64\www\Writemize
```

### 2. Create the database

```sql
CREATE DATABASE writemize CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import the schema

Using the MySQL CLI:

```bash
mysql -u root -p writemize < database/schema.sql
```

Or with phpMyAdmin: open the `writemize` database, go to Import, and select `database/schema.sql`.

### 4. Configure environment variables

Create a `.env` file in the project root:

```env
APP_NAME=Writemize
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/Writemize
TIMEZONE=Asia/Kolkata
DB_HOST=localhost
DB_PORT=3306
DB_NAME=writemize
DB_USER=root
DB_PASS=
OPENAI_API_KEY=your_openai_api_key_here
OPENAI_MODEL=gpt-5.6
OPENAI_IMAGE_MODEL=gpt-image-1.5
```

Do not commit your real `.env` file or API key to version control.

### 5. Start the app

Open `http://localhost/Writemize/`, register a user, log in, and open the dashboard.

---

## Running the pipeline

1. Enter the business name
2. Enter the website URL
3. Set a daily publish time
4. Click **AI Agent Activate** to save the business memory and run Scout
5. Click **Run AI Agent** to trigger the full pipeline

The live agent log updates as each stage runs in the background.

---

## Daily automation

`cron/daily_publish.php` checks every business with daily posting enabled and runs the pipeline once their configured publish time is due.

Linux cron:

```bash
* * * * * /usr/bin/php /var/www/html/Writemize/cron/daily_publish.php
```

Windows Task Scheduler:

```
C:\wamp64\bin\php\php8.3.0\php.exe C:\wamp64\www\Writemize\cron\daily_publish.php
```

---

## Database tables

| Table | Purpose |
|---|---|
| `users` | Registered users and login credentials |
| `businesses` | Business profile, website URL, publish time, automation state, Scout's memory |
| `blog_runs` | Pipeline run status, live logs, topic, SEO score, image URL, publish URL |
| `blog_posts` | Final content — title, slug, meta description, focus keyword, HTML, image, SEO score, word count, status |

---

## Project structure

```
agents/       OpenAiClient.php, Pipeline.php, ScoutAgent.php, RadarAgent.php, QuillAgent.php, WardenAgent.php, PulseAgent.php, PublisherAgent.php
api/          activate_agent.php, run_pipeline.php, run_pipeline_live.php, recent_posts.php
cron/         daily_publish.php
dashboard/    index.php, blogs.php, blogedit.php, websiteintegration.php, socialautoposting.php
assets/       logo.png, agentstanding.png, images/blogimages/
database/     schema.sql
```

---

## Demo flow

1. Open the landing page
2. Register or log in
3. Add a business and its website URL
4. Click AI Agent Activate
5. Click Run AI Agent
6. Watch the live log: Scout learns the business, Radar picks a topic, Quill writes the article, the image is generated, Warden scores it, Pulse schedules it, Publisher creates the URL
7. View the generated article
8. Open All Blogs to see the archive

---

## Security notes

- `.env` is kept out of version control and must never contain a real key in a public repo
- Local development runs with relaxed SSL verification to work around a WAMP certificate issue - production deployments should use full CA verification
- Generated article HTML is currently rendered as-is; stricter output sanitization is planned before any public production use

---

## What's next

- WordPress and Shopify publishing integration
- LinkedIn and Facebook social auto-posting
- XML sitemap submission
- Selectable content strategies, such as aggressive SEO versus thought leadership
- Real-time trend data feeding into Radar
- A manual approval step before publishing
- Multi-business support per account
- An analytics dashboard for post performance

---

## Built for OpenAI Build Week

Writemize is my attempt at proving a multi-agent workflow can actually replace a tedious, recurring business task, not just wrap one prompt in a nicer UI. Each agent has one job, the business context persists between runs, every step is visible live, and the handoff between agents is explicit end to end.

Built by Ishwar Rathod, using GPT-5.6, OpenAI image generation, and Codex.
