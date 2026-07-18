#  Writemize

**Autonomous AI blogging dashboard built for OpenAI Build Week.**

Writemize turns a business website URL into a daily, SEO-ready publishing workflow. A user registers, adds their business name, website URL, and preferred publishing time, then Writemize runs a coordinated agent pipeline that researches the business, finds fresh SEO topics, writes structured blog posts, generates featured images, audits quality, schedules the post, and creates a public preview URL.

** Live Demo:** https://writemize.online

---

##  Project Summary

Most businesses know they should publish useful blog content, but the process is slow: research topics, understand the brand, write the article, create visuals, check SEO, schedule publishing, and repeat it again tomorrow.

Writemize solves this with a fully autonomous AI content team:

- ** Scout** learns the business from its website.
- ** Radar** finds fresh, non-duplicate, SEO-friendly topic opportunities.
- ** Quill** writes the article and creates the featured image prompt.
- ** Warden** checks readability, structure, metadata, and SEO quality.
- ** Pulse** prepares the publishing schedule.
- ** Publisher** creates the final public blog URL and dashboard handoff.

The result is a powerful dashboard where businesses can run the AI agents instantly for demos, or configure a daily posting time for seamless, cron-based automation.

---

##  Core Features

- **Secure Authentication:** User registration and login flow.
- **Persistent Storage:** MySQL-backed business profile storage.
- **Configuration:** Saved business website URL and daily publishing time.
- **Autonomous Workflow:** Multi-agent blog generation pipeline working in sync.
- **Transparency:** Live agent log with real-time progress updates on the dashboard.
- **Anti-Duplication:** Fresh topic generation using previous blog history to avoid duplicate topics.
- **Advanced Text Generation:** GPT-5.6-powered topic research and article generation.
- **Visuals:** AI featured image generation using gpt-image-1.5.
- **Local Asset Management:** Generated images saved locally in assets/images/blogimages/.
- **Quality Control:** SEO score, word count, reading time, and post status tracking.
- **Preview:** Public blog view page for generated content.
- **Content Library:** 'All Blogs' library with featured image tiles.
- **Management:** Edit and delete options for generated posts.
- **Automation:** Daily cron runner for scheduled publishing.
- **Scalability:** Placeholder pages designed for future website integration and social auto-posting.

---

##  Technology Stack

- **Backend:** PHP 8.3
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript (Vanilla)
- **AI Text Model:** GPT-5.6
- **AI Image Model:** gpt-image-1.5
- **Local Environment:** WAMP / Apache / MySQL
- **Automation:** PHP cron script
- **Build Assistant:** OpenAI Codex

---

##  Agent Architecture

### Scout Agent
Scout receives the business name and website URL. It fetches readable website content, extracts brand context, and stores memory for future runs:
- Business niche
- Brand tone
- Audience
- Website excerpt
- Content strategy
- Last scouted URL and timestamp

*This context is stored in MySQL and reused by later agents to ensure brand consistency.*

### Radar Agent
Radar uses GPT-5.6 to act as an SEO strategist. It receives Scout context plus recent blog history, then creates a fresh topic. Radar specifically avoids repeating previous post titles and uses a unique run seed so each run can produce a radically different angle.

Radar outputs:
- Blog topic
- Focus keyword
- Search intent
- Topic angle
- Related keywords
- Competitor-style angles

### Quill Agent
Quill uses GPT-5.6 to write a complete website-specific SEO article in semantic HTML. It is strictly instructed not to reuse the same title, intro, section order, examples, or conclusion from recent posts.

Quill outputs:
- Title
- Meta description
- Focus keyword
- Structured article HTML
- Featured image prompt

### Artist / Image Generation
The image generation step uses the configured OpenAI image model (OPENAI_IMAGE_MODEL=gpt-image-1.5). Generated image files are downloaded and saved into assets/images/blogimages/. The saved local image URL is then attached to the respective blog post in the database.

### Warden Agent
Warden acts as the final editor and audits the article for:
- Meta description presence
- Word count
- Focus keyword usage
- Heading structure
- Readability status

It calculates the SEO score, reading time, word count, and marks the post as review-ready or approved.

### Pulse Agent
Pulse applies the business publishing schedule and prepares the post for the saved daily publishing time.

### Publisher Agent
Publisher handles the final deployment. It creates:
- URL Slug
- Public blog URL
- Final post status
- Dashboard/archive handoff

---

##  How We Used GPT-5.6

GPT-5.6 powers the reasoning-heavy content steps in Writemize:
- **Scout** uses GPT-5.6 to process raw website text and intelligently extract the brand's core context (niche, tone, and target audience).
- **Radar** uses GPT-5.6 to interpret this business context and choose fresh SEO topic opportunities.
- **Quill** uses GPT-5.6 to write structured, production-ready SEO blog posts highly tailored to the specific business, and simultaneously generates a highly contextual featured image prompt for the visual agent.
- **Warden** uses GPT-5.6 to act as the final editorial auditor, deeply analyzing the generated article for SEO quality, readability, and structural compliance before approving it for publishing.
- GPT-5.6 receives recent post history so it can reason about avoiding duplicate content and producing a new angle for every single run.
- Configuration is handled dynamically through the `.env` file (`OPENAI_MODEL=gpt-5.6`).

*Note: Because GPT-5 family models support only default temperature in our tested API setup, Writemize automatically avoids sending unsupported temperature parameters for GPT-5 models to ensure stability.*

---

##  How We Used Codex

OpenAI Codex was used as the primary engineering assistant and co-pilot during the Build Week development process.

Codex actively helped build and iterate on:
- PHP project structure and architectural design.
- MySQL schema and relationship mapping.
- Secure Authentication flow.
- Agent orchestration classes and data passing.
- Live NDJSON streaming API for real-time frontend updates.
- Dashboard UI and visual agent visualization.
- 'All Blogs' CRUD interface.
- Public blog view page rendering.
- Cron automation scripts.
- OpenAI API integration (Text and Image).
- Advanced error handling for SSL, unsupported model parameters, timeouts, and streaming output.
- README and hackathon-ready documentation.

The project was developed through an iterative Codex coding session where Codex implemented changes, debugged API/runtime errors, and refined the UX based on live browser feedback.

---

##  Installation & Setup

### Requirements
- PHP 8.3
- MySQL 8 or compatible MariaDB
- Apache/Nginx or WAMP/XAMPP
- PHP extensions: PDO MySQL, cURL, JSON, OpenSSL
- OpenAI API key

### 1. Clone or copy the project
Place the project in your web server root.
Example for WAMP: C:\wamp64\www\Writemize

### 2. Create the database
Create a MySQL database:
CREATE DATABASE writemize CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

### 3. Import the schema
Import the schema located at database/schema.sql.

Using phpMyAdmin:
1. Open phpMyAdmin.
2. Select the writemize database.
3. Go to Import.
4. Choose database/schema.sql.
5. Run the import.

Using MySQL CLI:
mysql -u root -p writemize < database/schema.sql

### 4. Configure environment variables
Create or update the .env file in the project root:

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
OPENAI_SSL_VERIFY=false

* Warning: Do not commit your real .env file or API key to version control.*

### 5. Start the app
Open your local server URL: http://localhost/Writemize/
Register a user, log in, and open the dashboard to begin.

---

##  Running the Agent Pipeline

From the dashboard:
1. Enter the business name.
2. Enter the website URL.
3. Set a daily publish time.
4. Click AI Agent Activate to save the business memory and run the Scout agent.
5. Click Run AI Agent to trigger the full pipeline instantly.

*The live agent log will dynamically update and show each stage as it executes in the background.*

---

##  Daily Cron Setup

Writemize includes a scheduled automation script: cron/daily_publish.php
This script checks businesses where daily posting is enabled and runs the pipeline when the saved publish time is due.

Example Windows Task Scheduler command:
C:\wamp64\bin\php\php8.3.0\php.exe C:\wamp64\www\Writemize\cron\daily_publish.php

Example Linux cron:
* * * * * /usr/bin/php /var/www/html/Writemize/cron/daily_publish.php

---

##  Database Tables

### users
Stores registered users and authentication credentials.

### businesses
Stores the business profile, website URL, publish time, daily automation state, and Scout memory.
*Important fields: website_url, publish_time, daily_posting_enabled, last_daily_run_date, scout_context, niche, tone, audience, content_strategy*

### blog_runs
Stores pipeline run status, real-time logs, generated topic, SEO score, image URL, and publish URL.

### blog_posts
Stores the final generated blog content:
*Title, Slug, Meta description, Focus keyword, HTML article, Featured image URL, SEO score, Word count, Reading time, Status, Public URL, Schedule time.*

---

##  Important Project Paths

agents/ (OpenAiClient.php, Pipeline.php, ScoutAgent.php, RadarAgent.php, QuillAgent.php, WardenAgent.php, PulseAgent.php, PublisherAgent.php)
api/ (activate_agent.php, run_pipeline.php, run_pipeline_live.php, recent_posts.php)
cron/ (daily_publish.php)
dashboard/ (index.php, blogs.php, blogedit.php, websiteintegration.php, socialautoposting.php)
assets/images/ (logo.png, agentstanding.png, blogimages/)
database/ (schema.sql)

---

##  Demo Flow

1. Open the landing page.
2. Register or log in.
3. Add a business website URL.
4. Click AI Agent Activate.
5. Click Run AI Agent.
6. Watch the live agent log execute steps:
   - Scout learns the business.
   - Radar chooses a fresh topic.
   - Quill writes the blog.
   - Image generation creates a featured image.
   - Warden scores SEO.
   - Pulse schedules.
   - Publisher creates the URL.
7. View the generated article.
8. Open All Blogs to see the archive with edit/delete actions.

---

##  Security Notes

- Keep .env private and secure.
- Do not commit OpenAI API keys to GitHub.
- Generated public blog pages render trusted AI HTML generated by the app. For production deployment, add stricter HTML sanitization and moderation protocols.
- OPENAI_SSL_VERIFY=false is useful for bypassing local WAMP SSL certificate issues. Use proper CA verification in production environments.

---

##  Future Improvements

- WordPress,Shopify publishing integration.
- LinkedIn/Facebook social auto posting.
- XML Sitemap submission integration.
- User-selectable content strategies (e.g., Aggressive SEO vs. Thought Leadership).
- Real-time trend APIs for Radar topic generation.
- Editorial approval workflow before publishing.
- Multi-business support per user account.
- Analytics dashboard to track generated post traffic.

---

##  Hackathon Statement

Writemize was built for OpenAI Build Week as an autonomous AI blogging dashboard. The core idea is to demonstrate a real-world multi-agent workflow, not just a single prompt wrapper. Each agent has a separate job, persistent business memory, live execution logs, database-backed outputs, and a clear publishing handoff.

Built by Ishwar Rathod with OpenAI GPT-5.6, OpenAI image generation, and Codex.
