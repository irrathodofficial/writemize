<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

$user = require_login();
$stmt = $pdo->prepare('SELECT bp.title, bp.focus_keyword, bp.seo_score, bp.status, bp.publish_url, bp.scheduled_for, bp.created_at FROM blog_posts bp INNER JOIN businesses b ON b.id = bp.business_id WHERE b.user_id = :user_id ORDER BY bp.id DESC LIMIT 8');
$stmt->execute([':user_id' => (int) $user['id']]);

json_response([
    'success' => true,
    'posts' => $stmt->fetchAll(),
]);
