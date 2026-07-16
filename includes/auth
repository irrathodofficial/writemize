<?php
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Writemize Authentication Middleware
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db_config.php';

/*
|--------------------------------------------------------------------------
| Check Login
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Load Logged-in User
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        email,
        company_name,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $_SESSION['user_id']
]);

$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Invalid Session
|--------------------------------------------------------------------------
*/

if (!$currentUser) {

    session_unset();
    session_destroy();

    header("Location: ../login.php");
    exit;

}

/*
|--------------------------------------------------------------------------
| Session Variables
|--------------------------------------------------------------------------
*/

$userId       = (int)$currentUser['id'];
$userName     = $currentUser['full_name'];
$userEmail    = $currentUser['email'];
$companyName  = $currentUser['company_name'];
$memberSince  = $currentUser['created_at'];

/*
|--------------------------------------------------------------------------
| Helper Function
|--------------------------------------------------------------------------
*/

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>