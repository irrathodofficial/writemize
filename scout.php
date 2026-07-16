<?php
declare(strict_types=1);

require_once 'db_config.php';

$stmt = $pdo->query("
    SELECT
        bp.*,
        b.website_url
    FROM business_profiles bp
    INNER JOIN businesses b
        ON bp.business_id = b.id
    ORDER BY bp.id DESC
    LIMIT 1
");

$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$keywords = [];

if ($profile && !empty($profile['keywords'])) {
    $keywords = json_decode($profile['keywords'], true) ?: [];
}

$competitors = [];

if ($profile && !empty($profile['competitors'])) {
    $competitors = json_decode($profile['competitors'], true) ?: [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Scout • Writemize</title>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-gray-50">

<div class="flex min-h-screen">

<?php include 'includes/sidebar.php'; ?>

<main class="flex-1 ml-64 p-10">

<div class="max-w-6xl mx-auto">

<h1 class="text-3xl font-bold text-gray-900 mb-8">
Scout Analysis
</h1>

<?php if(!$profile): ?>

<div class="bg-white rounded-xl shadow-lg p-10 text-center">

<h2 class="text-xl font-semibold text-gray-700">
No business profile available.
</h2>

<p class="text-gray-500 mt-2">
Run Scout to generate your first business analysis.
</p>

</div>

<?php else: ?>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

<div class="bg-white rounded-xl shadow-lg p-6">

<h2 class="font-bold text-lg mb-4">
Business
</h2>

<p class="text-gray-700">
<?= htmlspecialchars($profile['website_url']) ?>
</p>

</div>

<div class="bg-white rounded-xl shadow-lg p-6">

<h2 class="font-bold text-lg mb-4">
Niche
</h2>

<p>
<?= htmlspecialchars($profile['niche']) ?>
</p>

</div>

<div class="bg-white rounded-xl shadow-lg p-6">

<h2 class="font-bold text-lg mb-4">
Brand Tone
</h2>

<p>
<?= htmlspecialchars($profile['tone']) ?>
</p>

</div>

<div class="bg-white rounded-xl shadow-lg p-6">

<h2 class="font-bold text-lg mb-4">
Target Audience
</h2>

<p>
<?= htmlspecialchars($profile['audience']) ?>
</p>

</div>

<div class="bg-white rounded-xl shadow-lg p-6 lg:col-span-2">

<h2 class="font-bold text-lg mb-4">
SEO Keywords
</h2>

<div class="flex flex-wrap gap-2">

<?php foreach($keywords as $keyword): ?>

<span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm">
<?= htmlspecialchars($keyword) ?>
</span>

<?php endforeach; ?>

</div>

</div>

<div class="bg-white rounded-xl shadow-lg p-6 lg:col-span-2">

<h2 class="font-bold text-lg mb-4">
Competitors
</h2>

<ul class="space-y-3">

<?php foreach($competitors as $url): ?>

<li>

<a
href="<?= htmlspecialchars($url) ?>"
target="_blank"
class="text-blue-600 hover:underline">

<?= htmlspecialchars($url) ?>

</a>

</li>

<?php endforeach; ?>

</ul>

</div>

<div class="bg-white rounded-xl shadow-lg p-6 lg:col-span-2">

<h2 class="font-bold text-lg mb-4">
Summary
</h2>

<p class="leading-7 text-gray-700">

<?= nl2br(htmlspecialchars($profile['summary'])) ?>

</p>

</div>

</div>

<?php endif; ?>

</div>

</main>

</div>

</body>
</html>