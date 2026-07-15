<?php
declare(strict_types=1);

require_once 'db_config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userId = 'demo_user';

    $websiteUrl = trim($_POST['website_url'] ?? '');
    $postTime   = trim($_POST['post_time'] ?? '');

    if ($websiteUrl === '' || $postTime === '') {

        $error = 'Please complete all required fields.';

    } else {

        try {

            $stmt = $pdo->prepare("
                INSERT INTO businesses
                (
                    user_id,
                    website_url,
                    competitor_urls,
                    post_time
                )
                VALUES
                (
                    :user_id,
                    :website_url,
                    :competitor_urls,
                    :post_time
                )
            ");

            $stmt->execute([
                ':user_id'         => $userId,
                ':website_url'     => $websiteUrl,
                ':competitor_urls' => json_encode([]),
                ':post_time'       => $postTime
            ]);

            $message = 'Configuration saved successfully.';

        } catch (PDOException $e) {

            $error = 'Unable to save configuration.';

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Writemize Dashboard</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>

        :root{
            --writemize-blue:#2563EB;
            --writemize-cyan:#14B8A6;
            --writemize-green:#10E8A0;
        }

        .gradient-btn{
            background:linear-gradient(
                90deg,
                var(--writemize-blue),
                var(--writemize-cyan),
                var(--writemize-green)
            );
        }

    </style>

</head>

<body class="bg-gray-50">

<div class="flex min-h-screen">

    <?php include 'includes/sidebar.php'; ?>

    <main class="flex-1 ml-64 bg-gray-50">

        <div class="max-w-3xl mx-auto px-8 py-12">

            <div class="bg-white rounded-xl shadow-lg p-8">

                <h1 class="text-3xl font-bold text-gray-900 mb-8">
                    Business Setup
                </h1>

                <?php if (!empty($message)): ?>
                    <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-700">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">

                    <div>

                        <label
                            for="website_url"
                            class="block text-sm font-semibold text-gray-700 mb-2">
                            Website URL
                        </label>

                        <input
                            id="website_url"
                            name="website_url"
                            type="url"
                            required
                            placeholder="https://example.com"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">

                    </div>

                    <div>

                        <label
                            for="post_time"
                            class="block text-sm font-semibold text-gray-700 mb-2">
                            Daily Post Time
                        </label>

                        <input
                            id="post_time"
                            name="post_time"
                            type="time"
                            required
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition">

                    </div>

                    <div>

                        <button
                            type="submit"
                            class="gradient-btn inline-flex items-center justify-center rounded-lg px-8 py-3 text-white font-semibold shadow-md hover:opacity-95 transition">
                            Save Configuration
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </main>

</div>

</body>

</html>