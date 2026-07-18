<?php
declare(strict_types=1);

use Writemize\Agents\Pipeline;

@set_time_limit(0);
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');

require_once __DIR__ . '/../includes/auth.php';

try {
    $user = require_login();
    $config = require WRITEMIZE_ROOT . '/includes/config.php';
    $input = read_json_body();
    $input['user_id'] = (int) $user['id'];
    $pipeline = new Pipeline($pdo, $config);
    $result = $pipeline->run($input);

    if (!empty($result['error'])) {
        json_response([
            'success' => false,
            'error' => $result['error'],
            'logs' => $result['logs'],
            'completed_agents' => $result['completed_agents'],
            'failed_agent' => $result['failed_agent'],
            'run_id' => $result['run_id'],
            'openai_configured' => $result['openai_configured'],
        ], 502);
    }

    json_response([
        'success' => true,
        'logs' => $result['logs'],
        'completed_agents' => $result['completed_agents'],
        'failed_agent' => $result['failed_agent'],
        'article' => $result['article'],
        'run_id' => $result['run_id'],
        'post_id' => $result['post_id'],
        'openai_configured' => $result['openai_configured'],
    ]);
} catch (InvalidArgumentException $exception) {
    json_response(['success' => false, 'error' => $exception->getMessage()], 422);
} catch (Throwable $exception) {
    json_response(['success' => false, 'error' => $exception->getMessage()], 500);
}
