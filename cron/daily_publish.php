<?php
declare(strict_types=1);

use Writemize\Agents\Pipeline;

require_once __DIR__ . '/../includes/db_config.php';

$config = require WRITEMIZE_ROOT . '/includes/config.php';
$now = new DateTimeImmutable('now');
$today = $now->format('Y-m-d');
$time = $now->format('H:i:s');

$stmt = $pdo->prepare("
    SELECT *
    FROM businesses
    WHERE daily_posting_enabled = 1
      AND website_url <> ''
      AND publish_time IS NOT NULL
      AND publish_time <= :current_time
      AND (last_daily_run_date IS NULL OR last_daily_run_date < :today)
    ORDER BY publish_time ASC
    LIMIT 10
");
$stmt->execute([
    ':current_time' => $time,
    ':today' => $today,
]);

$businesses = $stmt->fetchAll();
$pipeline = new Pipeline($pdo, $config);
$results = [];

foreach ($businesses as $business) {
    try {
        $result = $pipeline->run([
            'business_id' => (int) $business['id'],
            'user_id' => (int) ($business['user_id'] ?? 0),
            'business_name' => (string) ($business['name'] ?? $business['business_name'] ?? 'Writemize Business'),
            'website_url' => (string) $business['website_url'],
            'publish_time' => substr((string) $business['publish_time'], 0, 5),
        ]);

        $update = $pdo->prepare('UPDATE businesses SET last_daily_run_date = :today WHERE id = :id');
        $update->execute([
            ':today' => $today,
            ':id' => (int) $business['id'],
        ]);

        $results[] = [
            'business_id' => (int) $business['id'],
            'status' => 'published',
            'post_id' => $result['post_id'],
            'publish_url' => $result['article']['publish_url'] ?? null,
        ];
    } catch (Throwable $exception) {
        $results[] = [
            'business_id' => (int) $business['id'],
            'status' => 'failed',
            'error' => $exception->getMessage(),
        ];
    }
}

if (PHP_SAPI === 'cli') {
    echo json_encode([
        'checked_at' => $now->format(DateTimeInterface::ATOM),
        'due_count' => count($businesses),
        'results' => $results,
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
    exit;
}

json_response([
    'success' => true,
    'checked_at' => $now->format(DateTimeInterface::ATOM),
    'due_count' => count($businesses),
    'results' => $results,
]);
