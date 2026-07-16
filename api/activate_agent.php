<?php
declare(strict_types=1);

use Writemize\Agents\ScoutAgent;

require_once __DIR__ . '/../includes/auth.php';

try {
    $user = require_login();
    $input = read_json_body();

    $businessName = clean_text($input['business_name'] ?? $user['name'], 160);
    $websiteUrl = clean_text($input['website_url'] ?? '', 2048);
    $publishTime = clean_text($input['publish_time'] ?? '09:00', 20);

    if ($websiteUrl === '' || filter_var($websiteUrl, FILTER_VALIDATE_URL) === false) {
        json_response(['success' => false, 'error' => 'Please enter a valid business website URL.'], 422);
    }

    $time = preg_match('/^\d{2}:\d{2}$/', $publishTime) === 1 ? $publishTime . ':00' : null;
    $logs = ['AI Agent Activate: saving business settings.'];

    $find = $pdo->prepare('SELECT id FROM businesses WHERE user_id = :user_id ORDER BY id DESC LIMIT 1');
    $find->execute([':user_id' => (int) $user['id']]);
    $businessId = (int) ($find->fetchColumn() ?: 0);

    if ($businessId > 0) {
        $stmt = $pdo->prepare('UPDATE businesses SET name = :name, website_url = :website_url, publish_time = :publish_time, daily_posting_enabled = 1, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            ':name' => $businessName,
            ':website_url' => $websiteUrl,
            ':publish_time' => $time,
            ':id' => $businessId,
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO businesses (user_id, name, website_url, publish_time, daily_posting_enabled) VALUES (:user_id, :name, :website_url, :publish_time, 1)');
        $stmt->execute([
            ':user_id' => (int) $user['id'],
            ':name' => $businessName,
            ':website_url' => $websiteUrl,
            ':publish_time' => $time,
        ]);
        $businessId = (int) $pdo->lastInsertId();
    }

    $context = (new ScoutAgent())->run([
        'business_name' => $businessName,
        'website_url' => $websiteUrl,
    ], $logs);

    $update = $pdo->prepare('UPDATE businesses SET scout_context = :scout_context, niche = :niche, tone = :tone, audience = :audience, content_strategy = :content_strategy, last_scouted_url = :last_scouted_url, last_scouted_at = CURRENT_TIMESTAMP WHERE id = :id');
    $update->execute([
        ':scout_context' => json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ':niche' => clean_text($context['niche'] ?? '', 190),
        ':tone' => clean_text($context['tone'] ?? '', 190),
        ':audience' => clean_text($context['audience'] ?? '', 255),
        ':content_strategy' => (string) ($context['content_strategy'] ?? ''),
        ':last_scouted_url' => $websiteUrl,
        ':id' => $businessId,
    ]);

    $logs[] = 'Scout Agent: business context stored for daily and instant runs.';

    json_response([
        'success' => true,
        'business_id' => $businessId,
        'logs' => $logs,
        'business' => [
            'name' => $businessName,
            'website_url' => $websiteUrl,
            'publish_time' => substr((string) $time, 0, 5),
            'niche' => $context['niche'] ?? '',
            'tone' => $context['tone'] ?? '',
            'audience' => $context['audience'] ?? '',
            'content_strategy' => $context['content_strategy'] ?? '',
        ],
    ]);
} catch (Throwable $exception) {
    json_response(['success' => false, 'error' => $exception->getMessage()], 500);
}
