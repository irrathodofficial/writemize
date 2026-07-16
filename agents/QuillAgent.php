<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class QuillAgent
{
    private OpenAiClient $client;

    public function __construct(OpenAiClient $client)
    {
        $this->client = $client;
    }

    public function run(array $context, array $topic, array &$logs): array
    {
        $logs[] = 'Quill Agent: drafting the blog and preparing the featured image brief.';

        $title = (string) ($topic['topic'] ?? 'How AI Blogging Dashboards Help Teams Publish Better Content Faster');
        $keyword = (string) ($topic['focus_keyword'] ?? 'AI blogging dashboard');
        $html = $this->fallbackHtml($title, $keyword, (string) ($context['business_name'] ?? 'Writemize'));

        $fallback = [
            'title' => $title,
            'meta_description' => 'Learn how an autonomous AI blogging workflow coordinates research, writing, images, SEO checks, and publishing.',
            'focus_keyword' => $keyword,
            'image_prompt' => $this->imagePrompt($title),
            'html' => $html,
        ];

        $article = $this->client->json(
            'You are Quill Agent, Writemize\'s expert blog writer. Return only JSON with title, meta_description, focus_keyword, image_prompt, html.',
            'Business context: ' . json_encode($context) . "\nTopic: " . json_encode($topic) . "\nWrite a polished SEO blog post in semantic HTML. Also create a DALL-E image_prompt. No scripts, no markdown fences.",
            $fallback
        );

        $article['html'] = $this->sanitizeHtml((string) ($article['html'] ?? $html));
        $article['image_prompt'] = (string) ($article['image_prompt'] ?? $this->imagePrompt((string) ($article['title'] ?? $title)));

        return $article;
    }

    public function createImage(array $article, array &$logs): array
    {
        $logs[] = 'Quill Agent: sending featured image brief to DALL-E.';
        $prompt = (string) ($article['image_prompt'] ?? $this->imagePrompt((string) ($article['title'] ?? 'AI blogging dashboard')));
        $article['image_url'] = $this->client->image($prompt);

        return $article;
    }

    private function imagePrompt(string $title): string
    {
        return 'Premium editorial featured image for a Writemize blog titled "' . $title . '". Blue, cyan, and green AI content dashboard aesthetic, clean SaaS style, no text, no logos.';
    }

    private function fallbackHtml(string $title, string $keyword, string $businessName): string
    {
        return '<article><header><h1>' . \e($title) . '</h1><p><strong>' . \e($businessName) . '</strong> can turn content from an occasional task into a repeatable growth system by coordinating research, writing, image generation, optimization, scheduling, and publishing in one workflow.</p></header><section><h2>Why autonomous blogging matters</h2><p>Modern teams need useful content, but the work usually stalls between topic research, first drafts, edits, imagery, and scheduling. A focused ' . \e($keyword) . ' gives every stage a dedicated agent with a clear job.</p></section><section><h2>The Writemize agent workflow</h2><ul><li><strong>Scout</strong> understands the business, audience, and brand tone.</li><li><strong>Radar</strong> chooses a search-driven topic angle.</li><li><strong>Quill</strong> drafts the article and featured image brief.</li><li><strong>Warden</strong> audits structure, readability, and SEO quality.</li><li><strong>Pulse</strong> prepares the publishing cadence and schedule.</li><li><strong>Publisher</strong> creates the final public blog handoff.</li></ul></section><section><h2>What better execution looks like</h2><p>The goal is not to flood a site with generic posts. The goal is a reliable editorial system that keeps business context, search intent, and quality checks in the same loop. That makes content faster to create and easier to trust.</p></section><section><h2>Practical takeaways</h2><p>Start with a clear business profile, define the publishing cadence, review early outputs, and let the agents compound what they learn. Over time, the dashboard becomes a daily operating rhythm for organic growth.</p></section></article>';
    }

    private function sanitizeHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/\son[a-z]+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/is', '', $html) ?? $html;

        return trim($html);
    }
}
