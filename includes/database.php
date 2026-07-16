<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    /** @var array{database: array{host: string, port: int, name: string, user: string, pass: string}} $config */
    $config = require __DIR__ . '/config.php';
    $db = $config['database'];
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $db['name']);

    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function ensure_schema(PDO $pdo): void
{
    $schema = file_get_contents(WRITEMIZE_ROOT . '/database/schema.sql');
    if ($schema === false) {
        throw new RuntimeException('Unable to read database schema.');
    }

    $pdo->exec($schema);
    ensure_column($pdo, 'businesses', 'user_id', 'INT UNSIGNED NULL');
    ensure_column($pdo, 'businesses', 'name', "VARCHAR(190) NOT NULL DEFAULT 'Writemize Business'");
    ensure_column($pdo, 'businesses', 'updated_at', 'TIMESTAMP NULL DEFAULT NULL');
    ensure_column($pdo, 'businesses', 'daily_posting_enabled', 'TINYINT(1) NOT NULL DEFAULT 1');
    ensure_column($pdo, 'businesses', 'last_daily_run_date', 'DATE NULL');
    ensure_column($pdo, 'businesses', 'scout_context', 'JSON NULL');
    ensure_column($pdo, 'businesses', 'niche', 'VARCHAR(190) NULL');
    ensure_column($pdo, 'businesses', 'tone', 'VARCHAR(190) NULL');
    ensure_column($pdo, 'businesses', 'audience', 'VARCHAR(255) NULL');
    ensure_column($pdo, 'businesses', 'content_strategy', 'TEXT NULL');
    ensure_column($pdo, 'businesses', 'last_scouted_url', 'VARCHAR(2048) NULL');
    ensure_column($pdo, 'businesses', 'last_scouted_at', 'DATETIME NULL');
    ensure_column($pdo, 'blog_runs', 'logs', 'JSON NULL');
    ensure_column($pdo, 'blog_posts', 'scheduled_for', 'DATETIME NULL');
}

function ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    if (preg_match('/^[a-zA-Z0-9_]+$/', $table) !== 1 || preg_match('/^[a-zA-Z0-9_]+$/', $column) !== 1) {
        throw new InvalidArgumentException('Invalid schema identifier.');
    }

    $stmt = $pdo->query('SHOW COLUMNS FROM `' . $table . '` LIKE ' . $pdo->quote($column));

    if ($stmt->fetch() === false) {
        $pdo->exec('ALTER TABLE `' . $table . '` ADD COLUMN `' . $column . '` ' . $definition);
    }
}
