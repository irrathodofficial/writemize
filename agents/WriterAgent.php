<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class WriterAgent
{
    private OpenAiClient $client;

    public function __construct(OpenAiClient $client)
    {
        $this->client = $client;
    }

    public function run(array $context, array $topic, array &$logs): array
    {
        $logs[] = 'Writer Agent: drafting SEO-ready article HTML.';

        $title = (string) ($topic['topic'] ?? 'How AI Blogging Dashboards Help Teams Publish Better Content Faster');
        $keyword = (string) ($topic['focus_keyword'] ?? 'AI blogging dashboard');
        $html = $this->fallbackHtml($title, $keyword, (string) ($context['business_name'] ?? 'Writemize'));

        $fallback = [
            'title' => $title,
            'meta_description' => 'Learn how an autonomous AI blogging dashboard coordinates research, writing, images, SEO checks, and publishing.',
            'focus_keyword' => $keyword,
            'html' => $html,
        ];

        $article = $this->client->json(
            'You are Writer Agent. Write a polished, practical, SEO-friendly blog post. Return only JSON with title, meta_description, focus_keyword, html.',
            'Business context: ' . json_encode($context) . "\nTopic: " . json_encode($topic) . "\nThe html must use article, header, section, h1, h2, h3, p, ul, li, and strong tags. No scripts.",
            $fallback
        );

        $article['html'] = $this->sanitizeHtml((string) ($article['html'] ?? $html));

        return $article;
    }

    private function fallbackHtml(string $title, string $keyword, string $businessName): string
    {
        return '<article><header><h1>' . \e($title) . '</h1><p><strong>' . \e($businessName) . '</strong> can turn content from an occasional campaign into a repeatable growth system by coordinating research, writing, image generation, optimization, and publishing in one workflow.</p></header><section><h2>Why autonomous blogging matters</h2><p>Modern teams need useful content, but the work usually stalls between topic research, first drafts, edits, imagery, and scheduling. An ' . \e($keyword) . ' removes those handoffs by giving every stage a dedicated agent with a clear job.</p></section><section><h2>The six-agent workflow</h2><ul><li><strong>Scout</strong> understands the business, audience, and tone.</li><li><strong>Radar</strong> chooses a search-driven topic angle.</li><li><strong>Writer</strong> drafts the article in structured HTML.</li><li><strong>Artist</strong> creates a featured image concept.</li><li><strong>Optimizer</strong> checks metadata, readability, and keyword coverage.</li><li><strong>Publisher</strong> schedules and prepares the publish URL.</li></ul></section><section><h2>What better execution looks like</h2><p>The goal is not to flood a site with generic posts. The goal is a reliable editorial system that keeps brand context, search intent, and quality controls in the same loop. That makes content faster to create and easier to trust.</p></section><section><h2>Practical takeaways</h2><p>Start with a clear business profile, define the publishing cadence, review early outputs, and let the agents compound what they learn. Over time, the dashboard becomes a daily operating rhythm for organic growth.</p></section></article>';
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/\son[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $html) ?? $html;

        return trim($html);
    }
}
