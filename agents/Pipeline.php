<?php
declare(strict_types=1);

namespace Writemize\Agents;

use PDO;
use Throwable;

final class Pipeline
{
    private OpenAiClient $client;
    private PDO $pdo;

    public function __construct(PDO $pdo, array $config)
    {
        $this->pdo = $pdo;
        $this->client = new OpenAiClient(
            (string) ($config['openai']['api_key'] ?? ''),
            (string) ($config['openai']['model'] ?? 'gpt-5.6'),
            (string) ($config['openai']['image_model'] ?? 'gpt-image-1.5'),
            (bool) ($config['openai']['ssl_verify'] ?? true),
            (string) ($config['openai']['ca_bundle'] ?? '')
        );
    }

    public function run(array $input, ?callable $emit = null): array
    {
        $logs = ['Pipeline: request accepted.'];
        $completedAgents = [];
        $currentAgent = 'scout';
        $this->emit($emit, ['type' => 'log', 'message' => 'Pipeline: request accepted.']);
        $businessName = \clean_text($input['business_name'] ?? 'Writemize Business', 160);
        $websiteUrl = \clean_text($input['website_url'] ?? '', 2048);
        $userId = (int) ($input['user_id'] ?? 0);
        $requestedBusinessId = (int) ($input['business_id'] ?? 0);

        if ($websiteUrl === '' || filter_var($websiteUrl, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Please enter a valid website URL.');
        }

        $businessId = $this->upsertBusiness($businessName, $websiteUrl, \clean_text($input['publish_time'] ?? '09:00', 20), $userId, $requestedBusinessId);
        $runId = $this->createRun($businessId);

        try {
            $logs[] = 'Scout Agent: started.';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'scout', 'state' => 'Running', 'pct' => 35]);
            $this->emit($emit, ['type' => 'log', 'message' => 'Scout Agent: started.']);
            $this->streamLog($logs, $emit, 'Scout Agent: fetching website content and extracting brand context.');
            $logCursor = count($logs);
            $context = (new ScoutAgent())->run($input, $logs);
            $this->emitNewLogs($emit, $logs, $logCursor);
            $this->streamLog($logs, $emit, 'Scout Agent: storing business niche, tone, audience, and content strategy.');
            $this->storeScoutContext($businessId, $context);
            $completedAgents[] = 'scout';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'scout', 'state' => 'Done', 'pct' => 100]);

            $currentAgent = 'radar';
            $logs[] = 'Radar Agent: started.';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'radar', 'state' => 'Running', 'pct' => 35]);
            $this->emit($emit, ['type' => 'log', 'message' => 'Radar Agent: started.']);
            $this->streamLog($logs, $emit, 'Radar Agent: sending business context to OpenAI for SEO topic research.');
            $logCursor = count($logs);
            $topic = (new RadarAgent($this->client))->run($context, $logs);
            $this->emitNewLogs($emit, $logs, $logCursor);
            $this->streamLog($logs, $emit, 'Radar Agent: selected topic "' . \clean_text($topic['topic'] ?? 'Untitled topic', 120) . '" with focus keyword "' . \clean_text($topic['focus_keyword'] ?? '', 80) . '".');
            $completedAgents[] = 'radar';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'radar', 'state' => 'Done', 'pct' => 100]);

            $currentAgent = 'quill';
            $logs[] = 'Quill Agent: started.';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'quill', 'state' => 'Running', 'pct' => 35]);
            $this->emit($emit, ['type' => 'log', 'message' => 'Quill Agent: started.']);
            $quill = new QuillAgent($this->client);
            $this->streamLog($logs, $emit, 'Quill Agent: asking OpenAI to draft the full website-specific SEO article.');
            $this->emit($emit, ['type' => 'agent', 'agent' => 'quill', 'state' => 'Running', 'pct' => 55]);
            $logCursor = count($logs);
            $article = $quill->run($context, $topic, $logs);
            $this->emitNewLogs($emit, $logs, $logCursor);
            $this->streamLog($logs, $emit, 'Quill Agent: article draft received; preparing DALL-E featured image request.');
            $this->emit($emit, ['type' => 'agent', 'agent' => 'quill', 'state' => 'Running', 'pct' => 75]);
            $logCursor = count($logs);
            $article = $quill->createImage($article, $logs);
            $this->emitNewLogs($emit, $logs, $logCursor);
            $this->streamLog($logs, $emit, 'Quill Agent: featured image ready and attached to article preview.');
            $completedAgents[] = 'quill';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'quill', 'state' => 'Done', 'pct' => 100]);

            $currentAgent = 'warden';
            $logs[] = 'Warden Agent: started.';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'warden', 'state' => 'Running', 'pct' => 35]);
            $this->emit($emit, ['type' => 'log', 'message' => 'Warden Agent: started.']);
            $this->streamLog($logs, $emit, 'Warden Agent: checking metadata, headings, readability, keyword usage, and word count.');
            $logCursor = count($logs);
            $article = (new WardenAgent())->run($article, $topic, $logs);
            $this->emitNewLogs($emit, $logs, $logCursor);
            $this->streamLog($logs, $emit, 'Warden Agent: SEO score calculated at ' . (int) ($article['seo_score'] ?? 0) . '.');
            $completedAgents[] = 'warden';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'warden', 'state' => 'Done', 'pct' => 100]);

            $currentAgent = 'pulse';
            $logs[] = 'Pulse Agent: started.';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'pulse', 'state' => 'Running', 'pct' => 35]);
            $this->emit($emit, ['type' => 'log', 'message' => 'Pulse Agent: started.']);
            $this->streamLog($logs, $emit, 'Pulse Agent: applying daily publish time and preparing schedule.');
            $logCursor = count($logs);
            $article = (new PulseAgent())->run($article, $input, $logs);
            $this->emitNewLogs($emit, $logs, $logCursor);
            $this->streamLog($logs, $emit, 'Pulse Agent: scheduled for ' . \clean_text($article['scheduled_for'] ?? 'pending', 40) . '.');
            $completedAgents[] = 'pulse';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'pulse', 'state' => 'Done', 'pct' => 100]);

            $currentAgent = 'publisher';
            $logs[] = 'Publisher Agent: started.';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'publisher', 'state' => 'Running', 'pct' => 35]);
            $this->emit($emit, ['type' => 'log', 'message' => 'Publisher Agent: started.']);
            $this->streamLog($logs, $emit, 'Publisher Agent: creating slug, public preview URL, and final handoff.');
            $logCursor = count($logs);
            $article = (new PublisherAgent())->run($article, $input, $logs);
            $this->emitNewLogs($emit, $logs, $logCursor);
            $this->streamLog($logs, $emit, 'Publisher Agent: publish URL ready: ' . \clean_text($article['publish_url'] ?? 'pending', 180));
            $completedAgents[] = 'publisher';
            $this->emit($emit, ['type' => 'agent', 'agent' => 'publisher', 'state' => 'Done', 'pct' => 100]);

            $postId = $this->savePost($runId, $businessId, $article);
            $this->completeRun($runId, $article, $logs);
            $logs[] = 'Pipeline: completed successfully.';
            $this->emit($emit, ['type' => 'log', 'message' => 'Pipeline: completed successfully.']);

            return [
                'run_id' => $runId,
                'post_id' => $postId,
                'logs' => $logs,
                'completed_agents' => $completedAgents,
                'failed_agent' => null,
                'article' => $article,
                'openai_configured' => $this->client->configured(),
            ];
        } catch (Throwable $exception) {
            $logs[] = 'Pipeline error: ' . $exception->getMessage();
            $this->failRun($runId, $logs);
            $this->emit($emit, ['type' => 'agent', 'agent' => $currentAgent, 'state' => 'Error', 'pct' => 100]);
            $this->emit($emit, ['type' => 'log', 'message' => $exception->getMessage()]);
            return [
                'run_id' => $runId,
                'post_id' => null,
                'logs' => $logs,
                'completed_agents' => $completedAgents,
                'failed_agent' => $currentAgent,
                'article' => null,
                'openai_configured' => $this->client->configured(),
                'error' => $exception->getMessage(),
            ];
        }
    }

    private function emit(?callable $emit, array $event): void
    {
        if ($emit !== null) {
            $emit($event);
        }
    }

    private function streamLog(array &$logs, ?callable $emit, string $message): void
    {
        $logs[] = $message;
        $this->emit($emit, ['type' => 'log', 'message' => $message]);
    }

    private function emitNewLogs(?callable $emit, array $logs, int $fromIndex): void
    {
        $count = count($logs);
        for ($i = $fromIndex; $i < $count; $i++) {
            $this->emit($emit, ['type' => 'log', 'message' => (string) $logs[$i]]);
        }
    }

    private function upsertBusiness(string $name, string $websiteUrl, string $publishTime, int $userId = 0, int $requestedBusinessId = 0): int
    {
        $existingId = 0;
        if ($requestedBusinessId > 0) {
            $find = $this->pdo->prepare('SELECT id FROM businesses WHERE id = :id LIMIT 1');
            $find->execute([':id' => $requestedBusinessId]);
            $existingId = (int) ($find->fetchColumn() ?: 0);
        }

        if ($userId > 0) {
            $find = $this->pdo->prepare('SELECT id FROM businesses WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
            $find->execute([':user_id' => $userId]);
            $existingId = $existingId > 0 ? $existingId : (int) ($find->fetchColumn() ?: 0);
        }

        $time = preg_match('/^\d{2}:\d{2}$/', $publishTime) === 1 ? $publishTime . ':00' : null;

        if ($existingId > 0) {
            $stmt = $this->pdo->prepare('UPDATE businesses SET name = :name, website_url = :website_url, publish_time = :publish_time, daily_posting_enabled = 1, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
            $stmt->execute([
                ':name' => $name !== '' ? $name : 'Writemize Business',
                ':website_url' => $websiteUrl,
                ':publish_time' => $time,
                ':id' => $existingId,
            ]);

            return $existingId;
        }

        $stmt = $this->pdo->prepare('INSERT INTO businesses (user_id, name, website_url, publish_time, daily_posting_enabled) VALUES (:user_id, :name, :website_url, :publish_time, 1)');
        $stmt->execute([
            ':user_id' => $userId > 0 ? $userId : null,
            ':name' => $name !== '' ? $name : 'Writemize Business',
            ':website_url' => $websiteUrl,
            ':publish_time' => $time,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function createRun(int $businessId): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO blog_runs (business_id, status) VALUES (:business_id, :status)');
        $stmt->execute([':business_id' => $businessId, ':status' => 'running']);

        return (int) $this->pdo->lastInsertId();
    }

    private function storeScoutContext(int $businessId, array $context): void
    {
        $stmt = $this->pdo->prepare('UPDATE businesses SET scout_context = :scout_context, niche = :niche, tone = :tone, audience = :audience, content_strategy = :content_strategy, last_scouted_url = :last_scouted_url, last_scouted_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            ':scout_context' => json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':niche' => \clean_text($context['niche'] ?? '', 190),
            ':tone' => \clean_text($context['tone'] ?? '', 190),
            ':audience' => \clean_text($context['audience'] ?? '', 255),
            ':content_strategy' => (string) ($context['content_strategy'] ?? ''),
            ':last_scouted_url' => \clean_text($context['website_url'] ?? '', 2048),
            ':id' => $businessId,
        ]);
    }

    private function savePost(int $runId, int $businessId, array $article): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO blog_posts (run_id, business_id, title, slug, meta_description, focus_keyword, html, image_url, seo_score, word_count, reading_time, status, publish_url, scheduled_for) VALUES (:run_id, :business_id, :title, :slug, :meta_description, :focus_keyword, :html, :image_url, :seo_score, :word_count, :reading_time, :status, :publish_url, :scheduled_for)');
        $stmt->execute([
            ':run_id' => $runId,
            ':business_id' => $businessId,
            ':title' => \clean_text($article['title'] ?? 'Generated Blog Article', 255),
            ':slug' => $this->uniqueSlug((string) ($article['slug'] ?? 'writemize-post')),
            ':meta_description' => \clean_text($article['meta_description'] ?? '', 320),
            ':focus_keyword' => \clean_text($article['focus_keyword'] ?? '', 190),
            ':html' => (string) ($article['html'] ?? ''),
            ':image_url' => $article['image_url'] ?? null,
            ':seo_score' => (int) ($article['seo_score'] ?? 0),
            ':word_count' => (int) ($article['word_count'] ?? 0),
            ':reading_time' => \clean_text($article['reading_time'] ?? '1 min', 40),
            ':status' => \clean_text($article['status'] ?? 'scheduled', 40),
            ':publish_url' => $article['publish_url'] ?? null,
            ':scheduled_for' => $article['scheduled_for'] ?? null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function uniqueSlug(string $slug): string
    {
        $base = \slugify($slug);
        $candidate = $base;
        $count = 2;
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM blog_posts WHERE slug = :slug');

        while (true) {
            $stmt->execute([':slug' => $candidate]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $candidate;
            }

            $candidate = $base . '-' . $count;
            $count++;
        }
    }

    private function completeRun(int $runId, array $article, array $logs): void
    {
        $stmt = $this->pdo->prepare('UPDATE blog_runs SET status = :status, topic = :topic, focus_keyword = :focus_keyword, seo_score = :seo_score, image_url = :image_url, publish_url = :publish_url, logs = :logs, completed_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            ':status' => 'complete',
            ':topic' => \clean_text($article['title'] ?? '', 255),
            ':focus_keyword' => \clean_text($article['focus_keyword'] ?? '', 190),
            ':seo_score' => (int) ($article['seo_score'] ?? 0),
            ':image_url' => $article['image_url'] ?? null,
            ':publish_url' => $article['publish_url'] ?? null,
            ':logs' => json_encode($logs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':id' => $runId,
        ]);
    }

    private function failRun(int $runId, array $logs): void
    {
        $stmt = $this->pdo->prepare('UPDATE blog_runs SET status = :status, logs = :logs, completed_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            ':status' => 'failed',
            ':logs' => json_encode($logs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            ':id' => $runId,
        ]);
    }
}
