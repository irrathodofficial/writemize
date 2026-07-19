# Writemize

Autonomous AI blogging dashboard built for OpenAI Build Week.

Writemize turns a business website URL into a daily, SEO-ready publishing workflow. A user registers, adds their business name, website URL, and preferred publishing time, then Writemize runs a coordinated agent pipeline that researches the business, finds fresh SEO topics, writes structured blog posts, generates featured images, audits quality, schedules the post, and creates a public preview URL.

## Project Summary

Most businesses know they should publish useful blog content, but the process is slow: research topics, understand the brand, write the article, create visuals, check SEO, schedule publishing, and repeat it again tomorrow.

Writemize solves this with an autonomous AI content team:

- **Scout** learns the business from its website.
- **Radar** finds fresh, non-duplicate, SEO-friendly topic opportunities.
- **Quill** writes the article and creates the featured image prompt.
- **Warden** checks readability, structure, metadata, and SEO quality.
- **Pulse** prepares the publishing schedule.
- **Publisher** creates the final public blog URL and dashboard handoff.

The result is a dashboard where businesses can run the AI agents instantly for demos, or configure a daily posting time for cron-based automation.

## Core Features

- User registration and login
- MySQL-backed business profile storage
- Saved business website URL and daily publishing time
- Autonomous multi-agent blog generation pipeline
- Live agent log with real-time progress updates
- Fresh topic generation using previous blog history to avoid duplicate topics
- GPT-5.6-powered topic research and article generation
- AI featured image generation using `gpt-image-2`
- Generated images saved locally in `assets/images/blogimages/`
- SEO score, word count, reading time, and post status
- Public blog view page
- All Blogs library with featured image tiles
- Edit and delete options for generated posts
- Daily cron runner for scheduled publishing
- Placeholder pages for website integration and social auto posting

## Technology Stack

- **Backend:** PHP 8.3
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **AI Text Model:** GPT-5.6
- **AI Image Model:** `gpt-image-2`
- **Local Environment:** WAMP / Apache / MySQL
- **Automation:** PHP cron script
- **Build Assistant:** OpenAI Codex

## Agent Architecture

### Scout Agent

Scout receives the business name and website URL. It fetches readable website content, extracts brand context, and stores memory for future runs:

- Business niche
- Brand tone
- Audience
- Website excerpt
- Content strategy
- Last scouted URL and timestamp

This context is stored in MySQL and reused by later agents.

### Radar Agent

Radar uses GPT-5.6 to act as an SEO strategist. It receives Scout context plus recent blog history, then creates a fresh topic.

Radar specifically avoids repeating previous post titles and uses a unique run seed so each run can produce a different angle.

Radar outputs:

- Blog topic
- Focus keyword
- Search intent
- Topic angle
- Related keywords
- Competitor-style angles

### Quill Agent

Quill uses GPT-5.6 to write a complete website-specific SEO article in semantic HTML.

Quill outputs:

- Title
- Meta description
- Focus keyword
- Structured article HTML
- Featured image prompt

It is instructed not to reuse the same title, intro, section order, examples, or conclusion from recent posts.

### Artist / Image Generation

The image generation step uses the configured OpenAI image model:

```env
OPENAI_IMAGE_MODEL=gpt-image-2
```

Generated image files are downloaded and saved into:

```text
assets/images/blogimages/
```

The saved local image URL is attached to the blog post.

### Warden Agent

Warden audits the article for:

- Meta description presence
- Word count
- Focus keyword usage
- Heading structure
- Readability status

It calculates SEO score, reading time, word count, and marks the post as review-ready or approved.

### Pulse Agent

Pulse applies the business publishing schedule and prepares the post for the saved daily publishing time.

### Publisher Agent

Publisher creates:

- Slug
- Public blog URL
- Final post status
- Dashboard/archive handoff

## How We Used GPT-5.6

GPT-5.6 powers the reasoning-heavy content steps in Writemize:

- Radar uses GPT-5.6 to interpret business context and choose fresh SEO topic opportunities.
- Quill uses GPT-5.6 to write structured, website-specific blog posts instead of generic demo content.
- GPT-5.6 receives recent post history so it can avoid duplicate content and produce a new angle for every run.
- GPT-5.6 is configured through `.env`:

```env
OPENAI_MODEL=gpt-5.6
```

Because GPT-5 family models support only default temperature in our tested API setup, Writemize automatically avoids sending unsupported temperature parameters for GPT-5 models.

## How We Used Codex

OpenAI Codex was used as the primary engineering assistant during the Build Week development process.

Codex helped build and iterate on:

- PHP project structure
- MySQL schema
- Authentication flow
- Agent orchestration classes
- Live NDJSON streaming API
- Dashboard UI and agent visualization
- All Blogs CRUD interface
- Public blog view page
- Cron automation
- OpenAI API integration
- Error handling for SSL, unsupported model parameters, timeouts, and streaming output
- README and hackathon-ready documentation

The project was developed through an iterative Codex coding session where Codex implemented changes, debugged API/runtime errors, and refined the UX based on live browser feedback.

## Installation

### Requirements

- PHP 8.3
- MySQL 8 or compatible MariaDB
- Apache/Nginx or WAMP/XAMPP
- PHP extensions:
  - PDO MySQL
  - cURL
  - JSON
  - OpenSSL
- OpenAI API key

### 1. Clone or copy the project

Place the project in your web server root.

Example for WAMP:

```text
C:\wamp64\www\Writemize
```

### 2. Create the database

Create a MySQL database:

```sql
CREATE DATABASE writemize CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import the schema

Import:

```text
database/schema.sql
```

Using phpMyAdmin:

1. Open phpMyAdmin.
2. Select the `writemize` database.
3. Go to Import.
4. Choose `database/schema.sql`.
5. Run the import.

Using MySQL CLI:

```bash
mysql -u root -p writemize < database/schema.sql
```

### 4. Configure environment variables

Create or update `.env` in the project root:

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
OPENAI_IMAGE_MODEL=gpt-image-2
OPENAI_SSL_VERIFY=false
```

Do not commit your real `.env` file or API key.

### 5. Start the app

Open:

```text
http://localhost/Writemize/
```

Register a user, log in, then open the dashboard.

## Running the Agent Pipeline

From the dashboard:

1. Enter business name.
2. Enter website URL.
3. Set daily publish time.
4. Click **AI Agent Activate** to save business memory.
5. Click **Run AI Agent** to run the pipeline instantly.

The live agent log will show each stage as it runs.

## Daily Cron Setup

Writemize includes:

```text
cron/daily_publish.php
```

This script checks businesses where daily posting is enabled and runs the pipeline when the saved publish time is due.

Example Windows Task Scheduler command:

```text
C:\wamp64\bin\php\php8.3.0\php.exe C:\wamp64\www\Writemize\cron\daily_publish.php
```

Run it every minute or every five minutes depending on your demo setup.

Example Linux cron:

```cron
* * * * * /usr/bin/php /var/www/html/Writemize/cron/daily_publish.php
```

## Database Tables

### `users`

Stores registered users.

### `businesses`

Stores business profile, website URL, publish time, daily automation state, and Scout memory.

Important fields:

- `website_url`
- `publish_time`
- `daily_posting_enabled`
- `last_daily_run_date`
- `scout_context`
- `niche`
- `tone`
- `audience`
- `content_strategy`

### `blog_runs`

Stores pipeline run status, logs, topic, SEO score, image URL, and publish URL.

### `blog_posts`

Stores generated blog content:

- Title
- Slug
- Meta description
- Focus keyword
- HTML article
- Featured image URL
- SEO score
- Word count
- Reading time
- Status
- Public URL
- Schedule time

## Important Project Paths

```text
agents/
  OpenAiClient.php
  Pipeline.php
  ScoutAgent.php
  RadarAgent.php
  QuillAgent.php
  WardenAgent.php
  PulseAgent.php
  PublisherAgent.php

api/
  activate_agent.php
  run_pipeline.php
  run_pipeline_live.php
  recent_posts.php

cron/
  daily_publish.php

dashboard/
  index.php
  blogs.php
  blogedit.php
  websiteintegration.php
  socialautoposting.php

assets/images/
  logo.png
  agentstanding.png
  blogimages/

database/
  schema.sql
```

## Demo Flow

1. Open the landing page.
2. Register or log in.
3. Add a business website URL.
4. Click **AI Agent Activate**.
5. Click **Run AI Agent**.
6. Watch the live agent log:
   - Scout learns the business.
   - Radar chooses a fresh topic.
   - Quill writes the blog.
   - Image generation creates a featured image.
   - Warden scores SEO.
   - Pulse schedules.
   - Publisher creates the URL.
7. View the generated article.
8. Open **All Blogs** to see the archive with edit/delete actions.

## Security Notes

- Keep `.env` private.
- Do not commit OpenAI API keys.
- Generated public blog pages render trusted AI HTML generated by the app. For production, add stricter HTML sanitization and moderation.
- `OPENAI_SSL_VERIFY=false` is useful for local WAMP SSL certificate issues. Use proper CA verification in production.

## Future Improvements

- WordPress publishing integration
- LinkedIn/social auto posting
- Sitemap submission
- User-selectable content strategy
- Real-time trend APIs
- Editorial approval workflow
- Multi-business support per account
- Analytics dashboard

## Hackathon Statement

Writemize was built for OpenAI Build Week as an autonomous AI blogging dashboard. The core idea is to demonstrate a real multi-agent workflow, not just a single prompt: each agent has a separate job, persistent business memory, live execution logs, database-backed outputs, and a clear publishing handoff.

Built by Ishwar Rathod with OpenAI GPT-5.6, OpenAI image generation, and Codex.
