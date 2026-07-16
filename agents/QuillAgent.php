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
        $image = $this->client->image($prompt);

        if (isset($image['url'])) {
            $remoteUrl = (string) $image['url'];
            $localUrl = $this->storeGeneratedImageFromUrl($remoteUrl, (string) ($article['title'] ?? 'writemize-blog'), $logs);
            if ($localUrl !== null) {
                $article['image_url'] = $localUrl;
                $article['image_source_url'] = $remoteUrl;
                return $article;
            }

            throw new \RuntimeException('DALL-E image was generated, but Writemize could not save it locally.');
        }

        if (isset($image['b64_json'])) {
            $bytes = base64_decode((string) $image['b64_json'], true);
            if (!is_string($bytes) || $bytes === '') {
                throw new \RuntimeException('DALL-E returned image data, but it could not be decoded.');
            }

            $localUrl = $this->storeGeneratedImageBytes($bytes, (string) ($article['title'] ?? 'writemize-blog'), $logs);
            if ($localUrl !== null) {
                $article['image_url'] = $localUrl;
                return $article;
            }

            throw new \RuntimeException('DALL-E image was generated, but Writemize could not save it locally.');
        }

        throw new \RuntimeException('DALL-E did not return a featured image.');
    }

    private function storeGeneratedImageFromUrl(string $remoteUrl, string $title, array &$logs): ?string
    {
        $context = stream_context_create([
            'http' => ['timeout' => 45, 'user_agent' => 'WritemizeBot/1.0'],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);
        $bytes = @file_get_contents($remoteUrl, false, $context);

        if (!is_string($bytes) || $bytes === '') {
            $logs[] = 'Quill Agent: DALL-E image URL received, but local image download failed.';
            return null;
        }

        return $this->storeGeneratedImageBytes($bytes, $title, $logs);
    }

    private function storeGeneratedImageBytes(string $bytes, string $title, array &$logs): ?string
    {
        $directory = \WRITEMIZE_ROOT . '/assets/images/blogimages';
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            $logs[] = 'Quill Agent: could not create assets/images/blogimages folder.';
            return null;
        }

        $filename = \slugify($title) . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.png';
        $path = $directory . '/' . $filename;

        if (file_put_contents($path, $bytes) === false) {
            $logs[] = 'Quill Agent: generated image could not be saved locally.';
            return null;
        }

        $logs[] = 'Quill Agent: image saved to assets/images/blogimages/' . $filename;

        return rtrim((string) \env('APP_URL', 'http://localhost/Writemize'), '/') . '/assets/images/blogimages/' . $filename;
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
