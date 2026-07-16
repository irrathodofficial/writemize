<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class WardenAgent
{
    public function run(array $article, array $topic, array &$logs): array
    {
        $logs[] = 'Warden Agent: auditing SEO metadata, readability, and article structure.';

        $html = (string) ($article['html'] ?? '');
        $wordCount = str_word_count(strip_tags($html));
        $keyword = (string) ($article['focus_keyword'] ?? $topic['focus_keyword'] ?? '');
        $keywordHits = $keyword !== '' ? substr_count(strtolower(strip_tags($html)), strtolower($keyword)) : 0;
        $score = 78;

        if (($article['meta_description'] ?? '') !== '') {
            $score += 8;
        }
        if ($wordCount >= 450) {
            $score += 7;
        }
        if ($keywordHits > 0) {
            $score += 5;
        }
        if (str_contains($html, '<h2')) {
            $score += 2;
        }

        $article['seo_score'] = min(100, $score);
        $article['word_count'] = $wordCount;
        $article['reading_time'] = \estimate_reading_time($html);
        $article['status'] = $article['seo_score'] >= 90 ? 'warden-approved' : 'review-ready';

        return $article;
    }
}
