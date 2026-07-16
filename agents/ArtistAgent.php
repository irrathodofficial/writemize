<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class ArtistAgent
{
    private OpenAiClient $client;

    public function __construct(OpenAiClient $client)
    {
        $this->client = $client;
    }

    public function run(array $article, array &$logs): array
    {
        $logs[] = 'Artist Agent: creating featured image direction with DALL-E.';

        $title = (string) ($article['title'] ?? 'AI blogging dashboard');
        $prompt = 'Editorial featured image for a business blog post titled "' . $title . '". Modern AI dashboard, content workflow, premium SaaS aesthetic, no text, no logos.';

        return [
            'image_prompt' => $prompt,
            'image_url' => $this->client->image($prompt),
        ];
    }
}
