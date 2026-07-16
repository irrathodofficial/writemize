<?php
declare(strict_types=1);

/**
 * Writemize application bootstrap.
 *
 * This file is intentionally the first include for every entry point. It loads
 * local environment values, exposes small helpers, and prepares class loading.
 */

$rootPath = dirname(__DIR__);

if (!defined('WRITEMIZE_ROOT')) {
    define('WRITEMIZE_ROOT', $rootPath);
}

if (!defined('WRITEMIZE_STARTED_AT')) {
    define('WRITEMIZE_STARTED_AT', microtime(true));
}

require_once WRITEMIZE_ROOT . '/includes/helpers.php';

$envFile = WRITEMIZE_ROOT . '/.env.php';
if (is_file($envFile)) {
    writemize_load_env_file($envFile);
}

$timezone = env('TIMEZONE', 'UTC');
if (is_string($timezone) && $timezone !== '') {
    date_default_timezone_set($timezone);
}

$app = [
    'name' => env('APP_NAME', 'Writemize'),
    'env' => env('APP_ENV', 'development'),
    'debug' => filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
    'url' => rtrim((string) env('APP_URL', 'http://localhost/Writemize'), '/'),
    'timezone' => date_default_timezone_get(),
];

$openai = [
    'api_key' => (string) env('OPENAI_API_KEY', ''),
    'model' => (string) env('OPENAI_MODEL', 'gpt-4.1-mini'),
    'image_model' => (string) env('OPENAI_IMAGE_MODEL', 'dall-e-3'),
    'ssl_verify' => filter_var(
        env('OPENAI_SSL_VERIFY', $app['env'] === 'production' ? 'true' : 'false'),
        FILTER_VALIDATE_BOOLEAN
    ),
    'ca_bundle' => (string) env('CURL_CA_BUNDLE', ''),
];

$database = [
    'host' => (string) env('DB_HOST', '127.0.0.1'),
    'port' => (int) env('DB_PORT', '3306'),
    'name' => (string) env('DB_NAME', 'writemize'),
    'user' => (string) env('DB_USER', 'root'),
    'pass' => (string) env('DB_PASS', ''),
];

spl_autoload_register(static function (string $class): void {
    $prefix = 'Writemize\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $path = WRITEMIZE_ROOT . '/' . $relative . '.php';

    if (is_file($path)) {
        require_once $path;
        return;
    }

    $agentPath = WRITEMIZE_ROOT . '/agents/' . basename($relative) . '.php';
    if (is_file($agentPath)) {
        require_once $agentPath;
    }
});

return [
    'app' => $app,
    'database' => $database,
    'openai' => $openai,
];
