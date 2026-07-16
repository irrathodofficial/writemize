<?php
declare(strict_types=1);

require_once __DIR__ . '/db_config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function current_user(): ?array
{
    global $pdo;

    $id = (int) ($_SESSION['user_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }

    $stmt = $pdo->prepare('SELECT id, name, email, created_at FROM users WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    return is_array($user) ? $user : null;
}

function require_login(): array
{
    $user = current_user();
    if ($user !== null) {
        return $user;
    }

    header('Location: ../login.php');
    exit;
}

function auth_redirect_if_logged_in(): void
{
    if (current_user() !== null) {
        header('Location: dashboard/index.php');
        exit;
    }
}

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}
