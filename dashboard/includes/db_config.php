<?php
declare(strict_types=1);

/**
 * Writemize Database Configuration
 * Uses environment variables.
 */

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'writemize';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

$dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    exit('Database connection failed.');
}

// API Credentials Setup
$openAiKey = '';
$serpApiKey = '';

// Check if our secret local file exists and load it
if (file_exists(__DIR__ . '/.env.php')) {
    require_once __DIR__ . '/.env.php';
}

// Set environment variables so Scout agent can read them
putenv("OPENAI_API_KEY=" . $openAiKey);
putenv("SERPAPI_KEY=" . $serpApiKey);