<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db_config.php';

$stmt = $pdo->query('SELECT title, focus_keyword, seo_score, status, publish_url, scheduled_for, created_at FROM blog_posts ORDER BY id DESC LIMIT 8');

json_response([
    'success' => true,
    'posts' => $stmt->fetchAll(),
]);
