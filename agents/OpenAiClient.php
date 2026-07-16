<?php
declare(strict_types=1);

namespace Writemize\Agents;

final class OpenAiClient
{
    private string $apiKey;
    private string $textModel;
    private string $imageModel;
    private bool $sslVerify;
    private string $caBundle;

    public function __construct(
        string $apiKey,
        string $textModel,
        string $imageModel,
        bool $sslVerify = true,
        string $caBundle = ''
    ) {
        $this->apiKey = $apiKey;
        $this->textModel = $textModel;
        $this->imageModel = $imageModel;
        $this->sslVerify = $sslVerify;
        $this->caBundle = $caBundle;
    }

    public function configured(): bool
    {
        return $this->apiKey !== '' && !str_contains($this->apiKey, 'replace-me');
    }

    public function json(string $systemPrompt, string $userPrompt, array $fallback): array
    {
        if (!$this->configured() || !function_exists('curl_init')) {
            return $fallback;
        }

        $payload = [
            'model' => $this->textModel,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.65,
            'response_format' => ['type' => 'json_object'],
        ];

        $response = $this->postJson('https://api.openai.com/v1/chat/completions', $payload, 120);
        $content = (string) ($response['choices'][0]['message']['content'] ?? '');
        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : $fallback;
    }

    public function image(string $prompt): ?string
    {
        if (!$this->configured() || !function_exists('curl_init')) {
            return null;
        }

        $payload = [
            'model' => $this->imageModel,
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'quality' => 'standard',
        ];

        $response = $this->postJson('https://api.openai.com/v1/images/generations', $payload, 120);
        $url = (string) ($response['data'][0]['url'] ?? '');

        return $url !== '' ? $url : null;
    }

    private function postJson(string $url, array $payload, int $timeout): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => $this->sslVerify,
            CURLOPT_SSL_VERIFYHOST => $this->sslVerify ? 2 : 0,
        ]);

        if ($this->sslVerify && $this->caBundle !== '' && is_file($this->caBundle)) {
            curl_setopt($ch, CURLOPT_CAINFO, $this->caBundle);
        }

        $raw = curl_exec($ch);
        $error = curl_error($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false || $error !== '') {
            throw new \RuntimeException('OpenAI request failed: ' . $error);
        }

        $decoded = json_decode((string) $raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('OpenAI returned invalid JSON.');
        }

        if ($status < 200 || $status >= 300 || isset($decoded['error'])) {
            throw new \RuntimeException((string) ($decoded['error']['message'] ?? 'OpenAI API request failed.'));
        }

        return $decoded;
    }
}
