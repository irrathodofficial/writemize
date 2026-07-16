<?php
declare(strict_types=1);

use Writemize\Agents\Pipeline;

require_once __DIR__ . '/../includes/auth.php';

try {
    $user = require_login();
    $config = require WRITEMIZE_ROOT . '/includes/config.php';
    $input = read_json_body();
    $input['user_id'] = (int) $user['id'];
    $pipeline = new Pipeline($pdo, $config);
    $result = $pipeline->run($input);

    json_response([
        'success' => true,
        'logs' => $result['logs'],
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
