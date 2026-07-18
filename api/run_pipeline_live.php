<?php
declare(strict_types=1);

use Writemize\Agents\Pipeline;

@set_time_limit(0);
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');
ignore_user_abort(true);

require_once __DIR__ . '/../includes/auth.php';

function stream_event(array $payload): void
{
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
    if (ob_get_level() > 0) {
        @ob_flush();
    }
    flush();
}

try {
    $user = require_login();
    $config = require WRITEMIZE_ROOT . '/includes/config.php';
    $input = read_json_body();
    $input['user_id'] = (int) $user['id'];

    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    header('Content-Type: application/x-ndjson; charset=utf-8');
    header('Cache-Control: no-cache');
    header('X-Accel-Buffering: no');

    $pipeline = new Pipeline($pdo, $config);
    $result = $pipeline->run($input, 'stream_event');

    if (!empty($result['error'])) {
        stream_event([
            'type' => 'final',
            'success' => false,
            'error' => $result['error'],
            'completed_agents' => $result['completed_agents'],
            'failed_agent' => $result['failed_agent'],
            'run_id' => $result['run_id'],
        ]);
        exit;
    }

    stream_event([
        'type' => 'final',
        'success' => true,
        'article' => $result['article'],
        'completed_agents' => $result['completed_agents'],
        'failed_agent' => null,
        'run_id' => $result['run_id'],
        'post_id' => $result['post_id'],
        'openai_configured' => $result['openai_configured'],
    ]);
} catch (Throwable $exception) {
    while (ob_get_level() > 0) {
        @ob_end_clean();
    }

    if (!headers_sent()) {
        header('Content-Type: application/x-ndjson; charset=utf-8');
    }

    stream_event([
        'type' => 'final',
        'success' => false,
        'error' => $exception->getMessage(),
        'failed_agent' => 'scout',
        'completed_agents' => [],
    ]);
}
