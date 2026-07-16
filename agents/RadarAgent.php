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
        ];

        return $this->client->json(
            'You are Radar Agent, an SEO and trend research specialist. Return only JSON.',
            'Using this business context, choose one high-opportunity blog topic. Include keys: topic, focus_keyword, search_intent, angle, related_keywords. Context: ' . json_encode($context),
            $fallback
        );
    }
}
