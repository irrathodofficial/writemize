<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class PublisherAgent
{
    public function run(array $article, array $input, array &$logs): array
    {
        $logs[] = 'Publisher Agent: preparing schedule, slug, and publish URL.';

        $title = (string) ($article['title'] ?? 'Writemize Blog Post');
        $slug = \slugify($title);
        $publishDate = new \DateTimeImmutable('today');
        $publishTime = \clean_text($input['publish_time'] ?? '09:00', 20);

        if (preg_match('/^\d{2}:\d{2}$/', $publishTime) === 1) {
            $publishDate = $publishDate->setTime((int) substr($publishTime, 0, 2), (int) substr($publishTime, 3, 2));
        }

        $article['slug'] = $slug;
        $article['scheduled_for'] = $publishDate->format('Y-m-d H:i:s');
        $article['publish_url'] = rtrim((string) env('APP_URL', 'http://localhost/Writemize'), '/') . '/post.php?slug=' . rawurlencode($slug);
        $article['status'] = 'scheduled';

        return $article;
    }
}
