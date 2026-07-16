<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
auth_redirect_if_logged_in();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = clean_text($_POST['name'] ?? '', 190);
    $email = strtolower(clean_text($_POST['email'] ?? '', 190));
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Enter your name, valid email, and password with at least 8 characters.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ]);
            login_user((int) $pdo->lastInsertId());
            header('Location: dashboard/index.php');
            exit;
        } catch (PDOException $exception) {
            $error = 'This email is already registered. Please login instead.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Writemize</title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="auth-body">
    <main class="auth-card">
        <img src="assets/images/logo.png" alt="Writemize">
        <h1>Create your Writemize account</h1>
        <?php if ($error !== ''): ?><p class="auth-error"><?= e($error) ?></p><?php endif; ?>
        <form method="post">
            <label>Name<input name="name" type="text" required></label>
            <label>Email<input name="email" type="email" required></label>
            <label>Password<input name="password" type="password" minlength="8" required></label>
            <button type="submit">Create Account</button>
        </form>
        <p>Already registered? <a href="login.php">Login</a></p>
    </main>
</body>
</html>
