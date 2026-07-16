<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class PublisherAgent
{
    public function run(array $article, array $input, array &$logs): array
    {
        $logs[] = 'Publisher Agent: preparing public blog page, slug, and final URL.';

        $title = (string) ($article['title'] ?? 'Writemize Blog Post');
        $slug = \slugify($title);

        $article['slug'] = $slug;
        $article['publish_url'] = rtrim((string) env('APP_URL', 'http://localhost/Writemize'), '/') . '/post.php?slug=' . rawurlencode($slug);
        $article['status'] = 'published-preview';

        return $article;
    }
}
