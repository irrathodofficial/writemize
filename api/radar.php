<?php
declare(strict_types=1);

/**
 * api/radar.php
 *
 * Procedural MVP implementation for the Radar Agent.
 * Generated via OpenAI Codex with manual API provider integration.
 */

require_once '../db_config.php';

/**
 * --------------------------------------------------------------------------
 * AI Content Idea Generator
 * --------------------------------------------------------------------------
 * Connects to OpenAI API to generate ideas based on Scout's data.
 */
function generateContentIdeas(string $niche, array$keywords): array
{
    // 1. Fetch API Key
    $apiKey = getenv('OPENAI_API_KEY');
    if (!$apiKey) {
        throw new RuntimeException('OpenAI API Key not found in environment.');
    }

    // Convert keywords array to a comma-separated string
    $keywordsString = implode(', ',$keywords);

    // 2. Precision System Prompt
    $systemPrompt = "Act as an expert SEO strategist. 
    Business Niche: {$niche}. 
    Target Keywords: {$keywordsString}. 
    Generate exactly 5 highly engaging, unique, and SEO-optimized blog post ideas. 
    Return strictly a JSON array of objects. Each object must have these exact keys: 'title', 'focus_keyword', and 'hook'. Do not include markdown formatting like ```json.";

    $payload = [
        'model' => 'gpt-5.6-turbo',
        'messages' => [
            ['role' => 'user', 'content' => $systemPrompt]
        ],
        'temperature' => 0.7
    ];

    // 3. Smart SSL Check for Localhost
    $isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] === 'localhost';

    // 4. Execute cURL Request to OpenAI
    $ch = curl_init('[https://api.openai.com/v1/chat/completions](https://api.openai.com/v1/chat/completions)');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_SSL_VERIFYPEER => !$isLocalhost,
        CURLOPT_TIMEOUT => 60
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new RuntimeException('OpenAI API Request failed: ' . $curlError);
    }
    if (!$response) return [];

    $data = json_decode($response, true);
    $aiContent = $data['choices'][0]['message']['content'] ?? '[]';
    
    // 5. Clean up accidental markdown from AI
    $aiContent = preg_replace('/```json\s*/', '', $aiContent);$aiContent = preg_replace('/```/', '', $aiContent);

    // 6. Return as PHP Array
    $ideas = json_decode(trim($aiContent), true);
    return is_array($ideas) ? $ideas : [];
}

try {

    /*
    |--------------------------------------------------------------------------
    | Create table if it does not exist
    |--------------------------------------------------------------------------
    */

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS content_ideas (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            business_profile_id INT UNSIGNED NOT NULL,
            title VARCHAR(255) NOT NULL,
            focus_keyword VARCHAR(255) NOT NULL,
            hook TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (business_profile_id)
        ) ENGINE=InnoDB
        DEFAULT CHARSET=utf8mb4
        COLLATE=utf8mb4_unicode_ci;
    ");

    /*
    |--------------------------------------------------------------------------
    | Ensure processed column exists
    |--------------------------------------------------------------------------
    */

    $column = $pdo->query("
        SHOW COLUMNS
        FROM business_profiles
        LIKE 'processed'
    ");

    if ($column->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE business_profiles
            ADD COLUMN processed TINYINT(1)
            NOT NULL
            DEFAULT 0
        ");
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch latest unprocessed profile
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT *
        FROM business_profiles
        WHERE processed = 0
        ORDER BY id DESC
        LIMIT 1
    ");

    $stmt->execute();
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$profile) {
        exit("No pending business profile found.\n");
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare data
    |--------------------------------------------------------------------------
    */

    $businessProfileId = (int)$profile['id'];
    $niche = (string)$profile['niche'];
    $keywords = [];

    if (!empty($profile['keywords'])) {
        $decoded = json_decode($profile['keywords'], true);
        if (is_array($decoded)) {
            $keywords = $decoded;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Generate content ideas
    |--------------------------------------------------------------------------
    */

    $ideas = generateContentIdeas($niche, $keywords);

    if (!is_array($ideas) || empty($ideas)) {
        throw new RuntimeException('generateContentIdeas() returned empty or invalid data.');
    }

    /*
    |--------------------------------------------------------------------------
    | Prepare insert statement
    |--------------------------------------------------------------------------
    */

    $insert = $pdo->prepare("
        INSERT INTO content_ideas
        (
            business_profile_id,
            title,
            focus_keyword,
            hook
        )
        VALUES
        (
            :business_profile_id,
            :title,
            :focus_keyword,
            :hook
        )
    ");

    /*
    |--------------------------------------------------------------------------
    | Insert ideas
    |--------------------------------------------------------------------------
    */

    $inserted = 0;

    foreach ($ideas as $idea) {
        if (!is_array($idea)) {
            continue;
        }

        $title = trim((string)($idea['title'] ?? ''));
        $focusKeyword = trim((string)($idea['focus_keyword'] ?? ''));
        $hook = trim((string)($idea['hook'] ?? ''));

        if ($title === '' || $focusKeyword === '' || $hook === '') {
            continue;
        }

        $insert->execute([
            ':business_profile_id' => $businessProfileId,
            ':title' => $title,
            ':focus_keyword' => $focusKeyword,
            ':hook' => $hook
        ]);

        $inserted++;
    }

    /*
    |--------------------------------------------------------------------------
    | Mark profile as processed
    |--------------------------------------------------------------------------
    */

    $update = $pdo->prepare("
        UPDATE business_profiles
        SET processed = 1
        WHERE id = :id
    ");

    $update->execute([
        ':id' => $businessProfileId
    ]);

    echo "Radar completed successfully.\n";
    echo "Inserted ideas: {$inserted}\n";

} catch (Throwable $e) {
    http_response_code(500);
    echo "Radar failed.\n";
    echo $e->getMessage();
}
?>