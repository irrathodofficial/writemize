<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class RadarAgent
{
    private \Writemize\Agents\OpenAiClient $client;

    public function __construct(\Writemize\Agents\OpenAiClient $client)
    {
        $this->client = $client;
    }

    public function run(array $context, array &$logs): array
    {
        $logs[] = 'Radar Agent: scoring topic opportunities and search intent.';
        $recentPosts = is_array($context['recent_posts'] ?? null) ? $context['recent_posts'] : [];
        $recentTitles = array_values(array_filter(array_map(
            static fn (array $post): string => (string) ($post['title'] ?? ''),
            $recentPosts
        )));
        $runSeed = (string) ($context['run_seed'] ?? microtime(true));
        
        $topicIdeas = [
            'Top AI Blog Automation Trends Businesses Should Watch',
            'How to Build a Daily SEO Content Engine Without Hiring a Large Team',
            'AI Content Operations: From Keyword Research to Publishing Workflow',
            'What Makes an AI Blogging Platform Useful for Growing Businesses',
            'How Automated Blog Publishing Improves Organic Growth Consistency',
        ];
        $idea = $topicIdeas[abs((int) crc32($runSeed)) % count($topicIdeas)];

        $fallback = [
            'topic' => $idea,
            'focus_keyword' => 'AI blog automation',
            'search_intent' => 'commercial education',
            'angle' => 'fresh run seed ' . $runSeed . ' with a non-duplicate competitor-aware topic angle',
            'related_keywords' => ['AI content workflow', 'automated blog writing', 'SEO content automation', 'daily blog automation'],
            'competitor_angles' => ['automation speed', 'SEO quality control', 'daily publishing consistency', 'topic freshness'],
        ];

        return $this->client->json(
            'You are Radar Agent, an SEO and trend research specialist. Return valid JSON only. Do not include markdown fences, prose, or explanations.',
            'Using this business context, act like an SEO strategist and competitor analyst. Choose one highly viral, evergreen, and SEO-friendly blog topic for this business. While you can consider today\'s date (' . date('Y-m-d') . ') for context, DO NOT append the current year to the title. Make the topic evergreen so it remains relevant for years. The topic must be meaningfully different from previous posts and must not reuse these titles: ' . json_encode($recentTitles) . '. Use run_seed "' . $runSeed . '" to vary the angle. Prefer high-intent, comparison, evergreen checklist, how-to, or buyer-problem topics. Include keys: topic, focus_keyword, search_intent, angle, related_keywords, competitor_angles. Context: ' . json_encode($context),
            $fallback
        );
    }
}