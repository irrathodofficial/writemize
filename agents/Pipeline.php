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
            (string) ($config['openai']['model'] ?? 'gpt-4.1-mini'),
            (string) ($config['openai']['image_model'] ?? 'dall-e-3'),
            (bool) ($config['openai']['ssl_verify'] ?? true),
            (string) ($config['openai']['ca_bundle'] ?? '')
        );
    }

    public function run(array $input): array
    {
        $logs = ['Pipeline: request accepted.'];
        $businessName = \clean_text($input['business_name'] ?? 'Writemize Business', 160);
        $websiteUrl = \clean_text($input['website_url'] ?? '', 2048);

        if ($websiteUrl === '' || filter_var($websiteUrl, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Please enter a valid website URL.');
        }

        $businessId = $this->upsertBusiness($businessName, $websiteUrl, \clean_text($input['publish_time'] ?? '09:00', 20));
        $runId = $this->createRun($businessId);

        try {
            $context = (new ScoutAgent())->run($input, $logs);
            $topic = (new RadarAgent($this->client))->run($context, $logs);
            $article = (new WriterAgent($this->client))->run($context, $topic, $logs);
            $art = (new ArtistAgent($this->client))->run($article, $logs);
            $article = array_merge($article, $art);
            $article = (new OptimizerAgent())->run($article, $topic, $logs);
            $article = (new PublisherAgent())->run($article, $input, $logs);

            $postId = $this->savePost($runId, $businessId, $article);
            $this->completeRun($runId, $article, $logs);
            $logs[] = 'Pipeline: completed successfully.';

            return [
                'run_id' => $runId,
                'post_id' => $postId,
                'logs' => $logs,
                'article' => $article,
                'openai_configured' => $this->client->configured(),
            ];
        } catch (Throwable $exception) {
            $logs[] = 'Pipeline error: ' . $exception->getMessage();
            $this->failRun($runId, $logs);
            throw $exception;
        }
    }

    private function upsertBusiness(string $name, string $websiteUrl, string $publishTime): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO businesses (name, website_url, publish_time) VALUES (:name, :website_url, :publish_time)');
        $stmt->execute([
            ':name' => $name !== '' ? $name : 'Writemize Business',
            ':website_url' => $websiteUrl,
            ':publish_time' => preg_match('/^\d{2}:\d{2}$/', $publishTime) === 1 ? $publishTime . ':00' : null,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function createRun(int $businessId): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO blog_runs (business_id, status) VALUES (:business_id, :status)');
        $stmt->execute([':business_id' => $businessId, ':status' => 'running']);

        return (int) $this->pdo->lastInsertId();
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
