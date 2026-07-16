<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class ScoutAgent
{
    public function run(array $input, array &$logs): array
    {
        $logs[] = 'Scout Agent: reading business URL and extracting positioning.';
        $url = (string) $input['website_url'];
        $text = $this->fetchText($url);

        $host = parse_url($url, PHP_URL_HOST) ?: 'business website';
        $name = \clean_text($input['business_name'] ?? $host, 120);

        return [
            'business_name' => $name !== '' ? $name : $host,
            'website_url' => $url,
            'site_host' => $host,
            'site_excerpt' => $text,
            'niche' => $this->inferNiche($host, $text),
            'tone' => 'clear, expert, practical',
            'audience' => 'buyers researching better ways to grow online',
        ];
    }

    private function fetchText(string $url): string
    {
        $context = stream_context_create([
            'http' => ['timeout' => 8, 'user_agent' => 'WritemizeBot/1.0'],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]);

        $html = @file_get_contents($url, false, $context);
        if (!is_string($html) || $html === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        return \clean_text($text, 4000);
    }

    private function inferNiche(string $host, string $text): string
    {
        if ($text !== '') {
            $sample = strtolower($text);
            foreach (['marketing', 'software', 'health', 'finance', 'real estate', 'education', 'ecommerce'] as $keyword) {
                if (str_contains($sample, $keyword)) {
                    return ucfirst($keyword);
                }
            }
        }

        return str_replace(['www.', '.com', '.in', '.co'], '', strtolower($host));
    }
}
