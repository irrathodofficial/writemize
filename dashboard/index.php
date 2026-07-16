<?php
declare(strict_types=1);

require_once '../includes/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard | Writemize</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>

:root{

--blue:#2563EB;
--cyan:#14B8A6;
--green:#10E8A0;

}

body{

background:#f8fafc;

}

.sidebar{

width:280px;

background:#111827;

position:fixed;

top:0;

left:0;

bottom:0;

color:white;

}

.content{

margin-left:280px;

}

.card{

background:white;

border-radius:16px;

box-shadow:0 10px 30px rgba(0,0,0,.06);

}

.active{

background:#1f2937;

border-left:4px solid var(--green);

}

</style>

</head>

<body>

<!-- Sidebar -->

<aside class="sidebar">

<div class="p-8 border-b border-gray-700">

<img
src="../assets/images/logo.png"
class="h-12 w-auto object-contain"
alt="Writemize Logo">

<h2 class="mt-4 text-xl font-bold">

Writemize

</h2>

<p class="text-gray-400 text-sm">

Business Dashboard

</p>

</div>

<nav class="mt-6">

<a href="index.php"
class="flex items-center gap-3 px-6 py-4 active">

🏠

<span>Dashboard</span>

</a>

<a href="#"
class="flex items-center gap-3 px-6 py-4 hover:bg-gray-800">

🏢

<span>Business Setup</span>

</a>

<a href="#"
class="flex items-center gap-3 px-6 py-4 hover:bg-gray-800">

📊

<span>Reports</span>

</a>

<a href="#"
class="flex items-center gap-3 px-6 py-4 hover:bg-gray-800">

📁

<span>Projects</span>

</a>

<a href="../logout.php"
class="flex items-center gap-3 px-6 py-4 hover:bg-red-600 mt-8">

🚪

<span>Logout</span>

</a>

</nav>

</aside>

<!-- Main -->

<div class="content">

<!-- Topbar -->

<header class="bg-white shadow-sm px-10 py-6 flex justify-between items-center">

<div>

<h1 class="text-3xl font-bold">

Welcome,
<?= e($userName) ?>

</h1>

<p class="text-gray-500">

<?= e($companyName) ?>

</p>

</div>

<div class="flex gap-4">

<div class="bg-gray-100 rounded-xl px-5 py-3">

<?= e($userEmail) ?>

</div>

</div>

</header>

<!-- Dashboard -->

<div class="p-10">

<!-- Analytics -->

<div class="grid grid-cols-4 gap-6">

<div class="card p-6">

<h3 class="text-gray-500">

Businesses

</h3>

<div class="text-4xl font-bold mt-3">

1

</div>

</div>

<div class="card p-6">

<h3 class="text-gray-500">

Projects

</h3>

<div class="text-4xl font-bold mt-3">

0

</div>

</div>

<div class="card p-6">

<h3 class="text-gray-500">

Tasks

</h3>

<div class="text-4xl font-bold mt-3">

0

</div>

</div>

<div class="card p-6">

<h3 class="text-gray-500">

Status

</h3>

<div class="text-2xl font-bold text-green-600 mt-3">

Ready

</div>

</div>

</div>

<!-- Business Configuration -->

<div class="card mt-8 p-8">

<h2 class="text-2xl font-bold mb-8">

Business Configuration

</h2>

<form class="grid grid-cols-2 gap-6">

<div>

<label class="block mb-2 font-semibold">

Website URL

</label>

<input
type="url"
class="w-full border rounded-xl p-3"
placeholder="https://example.com">

</div>

<div>

<label class="block mb-2 font-semibold">

Business Name

</label>

<input
type="text"
class="w-full border rounded-xl p-3">

</div>

<div>

<label class="block mb-2 font-semibold">

Business Category

</label>

<input
type="text"
class="w-full border rounded-xl p-3">

</div>

<div>

<label class="block mb-2 font-semibold">

Target Country

</label>

<input
type="text"
class="w-full border rounded-xl p-3">

</div>

<div>

<label class="block mb-2 font-semibold">

Language

</label>

<select class="w-full border rounded-xl p-3">

<option>English</option>

<option>Hindi</option>

</select>

</div>

<div>

<label class="block mb-2 font-semibold">

Timezone

</label>

<select class="w-full border rounded-xl p-3">

<option>Asia/Kolkata</option>

<option>UTC</option>

</select>

</div>

<div>

<label class="block mb-2 font-semibold">

Daily Time

</label>

<input
type="time"
class="w-full border rounded-xl p-3">

</div>

<div class="flex items-end">

<button
class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl">

Save Configuration

</button>

</div>

</form>

</div>

</div>

</div>

</body>
</html>