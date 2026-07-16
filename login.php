<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_redirect_if_logged_in();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(clean_text($_POST['email'] ?? '', 190));
    $password = (string) ($_POST['password'] ?? '');

    $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if (is_array($user) && password_verify($password, (string) $user['password_hash'])) {
        login_user((int) $user['id']);
        header('Location: dashboard/index.php');
        exit;
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Writemize</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="auth-body">
    <main class="auth-card">
        <img src="assets/images/logo.png" alt="Writemize">
        <h1>Login to Writemize</h1>
        <?php if ($error !== ''): ?><p class="auth-error"><?= e($error) ?></p><?php endif; ?>
        <form method="post">
            <label>Email<input name="email" type="email" required></label>
            <label>Password<input name="password" type="password" required></label>
            <button type="submit">Login</button>
        </form>
        <p>New here? <a href="register.php">Create account</a></p>
    </main>
</body>
</html>
