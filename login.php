<?php
declare(strict_types=1);

session_start();

require_once 'includes/db_config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $pdo->prepare("
            SELECT *
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {

            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];

            header("Location: dashboard/index.php");
            exit;

        } else {

            $error = "Invalid email or password.";

        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login • Writemize</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>

body{

background:#f1f5f9;

}

.login-bg{

background:linear-gradient(
135deg,
#2563EB,
#14B8A6,
#10E8A0
);

}

</style>

</head>

<body>

<div class="min-h-screen grid lg:grid-cols-2">

<!-- Left -->

<div class="hidden lg:flex login-bg items-center justify-center p-16">

<div class="text-white max-w-md">

<img
src="assets/images/logo.png"
class="h-20 mb-10"
alt="Writemize Logo">

<h1 class="text-5xl font-bold leading-tight">

Autonomous AI Content Team

</h1>

<p class="mt-6 text-lg opacity-90">

Build once.

Publish forever.

Let Scout, Radar, Quill, Warden and Pulse handle your workflow.

</p>

</div>

</div>

<!-- Right -->

<div class="flex items-center justify-center p-8">

<div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-10">

<div class="text-center">

<img
src="assets/images/logo.png"
class="h-16 mx-auto mb-6"
alt="Logo">

<h2 class="text-3xl font-bold">

Welcome Back

</h2>

<p class="text-gray-500 mt-2">

Sign in to continue

</p>

</div>

<?php if($error): ?>

<div class="mt-6 bg-red-100 border border-red-200 text-red-700 p-3 rounded-xl">

<?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>

<form
method="POST"
class="mt-8 space-y-5">

<div>

<label class="block font-semibold mb-2">

Email Address

</label>

<input
type="email"
name="email"
required
class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

</div>

<div>

<label class="block font-semibold mb-2">

Password

</label>

<input
type="password"
name="password"
required
class="w-full border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none">

</div>

<div class="flex justify-between items-center">

<label class="flex items-center gap-2">

<input
type="checkbox"
name="remember">

<span class="text-sm">

Remember Me

</span>

</label>

<a
href="#"
class="text-blue-600 text-sm">

Forgot Password?

</a>

</div>

<button
type="submit"
class="w-full rounded-xl py-3 text-white font-semibold bg-gradient-to-r from-blue-600 via-cyan-500 to-emerald-400 hover:opacity-90 transition">

Login

</button>

</form>

<div class="text-center mt-8">

Don't have an account?

<a
href="register.php"
class="text-blue-600 font-semibold">

Create Account

</a>

</div>

</div>

</div>

</div>

</body>

</html>