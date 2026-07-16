<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class OptimizerAgent
{
    public function run(array $article, array $topic, array &$logs): array
    {
        $logs[] = 'Optimizer Agent: auditing SEO metadata, readability, and structure.';

        $html = (string) ($article['html'] ?? '');
        $wordCount = str_word_count(strip_tags($html));
        $keyword = (string) ($article['focus_keyword'] ?? $topic['focus_keyword'] ?? '');
        $keywordHits = $keyword !== '' ? substr_count(strtolower(strip_tags($html)), strtolower($keyword)) : 0;
        $score = 76;

        if (($article['meta_description'] ?? '') !== '') {
            $score += 8;
        }
        if ($wordCount >= 450) {
            $score += 8;
        }
        if ($keywordHits > 0) {
            $score += 5;
        }
        if (str_contains($html, '<h2')) {
            $score += 3;
        }

        $article['seo_score'] = min(100, $score);
        $article['word_count'] = $wordCount;
        $article['reading_time'] = \estimate_reading_time($html);
        $article['status'] = $article['seo_score'] >= 90 ? 'optimized' : 'ready';

        return $article;
    }
}
