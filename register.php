<?php
declare(strict_types=1);

session_start();
require_once 'includes/db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['full_name'] ?? '');
    $company = trim($_POST['company_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (
        $name === '' ||
        $email === '' ||
        $password === '' ||
        $confirm === ''
    ) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {

        $check = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = ?
        ");

        $check->execute([$email]);

        if ($check->fetch()) {

            $error = "Email already registered.";

        } else {

            $hash = password_hash($password, PASSWORD_DEFAULT);

            $insert = $pdo->prepare("
                INSERT INTO users
                (
                    full_name,
                    email,
                    password,
                    company_name
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");

            $insert->execute([
                $name,
                $email,
                $hash,
                $company
            ]);

            $_SESSION['user_id'] = $pdo->lastInsertId();

            header("Location: dashboard/index.php");
            exit;

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>Create Account • Writemize</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-slate-100">

<div class="min-h-screen flex items-center justify-center p-8">

<div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-10">

<div class="text-center">

<img
src="assets/images/logo.png"
class="h-16 mx-auto mb-5"
alt="Writemize Logo">

<h1 class="text-3xl font-bold">

Create your account

</h1>

<p class="text-gray-500 mt-2">

Start your autonomous content team.

</p>

</div>

<?php if($error): ?>

<div class="mt-6 bg-red-100 text-red-700 p-3 rounded-lg">

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>

<form
method="post"
class="mt-8 space-y-5">

<div>

<label class="font-semibold">

Full Name

</label>

<input
type="text"
name="full_name"
required
class="w-full border rounded-xl p-3 mt-2">

</div>

<div>

<label class="font-semibold">

Company Name

</label>

<input
type="text"
name="company_name"
class="w-full border rounded-xl p-3 mt-2">

</div>

<div>

<label class="font-semibold">

Email

</label>

<input
type="email"
name="email"
required
class="w-full border rounded-xl p-3 mt-2">

</div>

<div>

<label class="font-semibold">

Password

</label>

<input
type="password"
name="password"
required
class="w-full border rounded-xl p-3 mt-2">

</div>

<div>

<label class="font-semibold">

Confirm Password

</label>

<input
type="password"
name="confirm_password"
required
class="w-full border rounded-xl p-3 mt-2">

</div>

<button
class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-3 font-semibold">

Create Account

</button>

</form>

<p class="text-center mt-6">

Already have an account?

<a
href="login.php"
class="text-blue-600 font-semibold">

Login

</a>

</p>

</div>

</div>

</body>

</html>