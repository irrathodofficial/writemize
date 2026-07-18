<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class RadarAgent
{
    private OpenAiClient $client;

    public function __construct(OpenAiClient $client)
    {
        $this->client = $client;
    }

    public function run(array $context, array &$logs): array
    {
        $logs[] = 'Radar Agent: scoring topic opportunities and search intent.';

        $fallback = [
            'topic' => 'How AI Blogging Dashboards Help Teams Publish Better Content Faster',
            'focus_keyword' => 'AI blogging dashboard',
            'search_intent' => 'commercial education',
            'angle' => 'show how an autonomous agent workflow improves research, drafting, imagery, optimization, and publishing',
            'related_keywords' => ['AI content workflow', 'automated blog writing', 'SEO content automation'],
            'competitor_angles' => ['automation speed', 'SEO quality control', 'daily publishing consistency'],
        ];

        return $this->client->json(
            'You are Radar Agent, an SEO and trend research specialist. Return valid JSON only. Do not include markdown fences, prose, or explanations.',
            'Using this business context, act like an SEO strategist and competitor analyst. Choose one viral, SEO-friendly blog topic for this business. Include keys: topic, focus_keyword, search_intent, angle, related_keywords, competitor_angles. Context: ' . json_encode($context),
            $fallback
        );
    }
}
