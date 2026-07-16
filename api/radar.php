<?php
declare(strict_types=1);

// Load database connection
require_once '../db_config.php';

// ---------------------------------------------------------
// SMART SSL CHECK: Disable SSL verify ONLY for localhost
// ---------------------------------------------------------
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] === 'localhost';
$verifySsl = !$isLocalhost;

// 1. Fetch the latest business profile analyzed by Scout
$stmt = $pdo->query("SELECT * FROM business_profiles ORDER BY id DESC LIMIT 1");
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    exit(json_encode(['status' => 'error', 'message' => 'No business profile found. Please run Scout Agent first.']));
}

$niche = $profile['niche'];
$keywordsJson = $profile['keywords'];
$businessId = $profile['id'];

// 2. Set up OpenAI API Call
$openAiApiKey = getenv('OPENAI_API_KEY'); 
if (!$openAiApiKey) {
    exit(json_encode(['status' => 'error', 'message' => 'OpenAI API Key not found in db_config.php']));
}

// Precision Prompt for Radar Agent
$systemPrompt = "Act as an expert SEO strategist. 
Business Niche: {$niche}. 
Target Keywords: {$keywordsJson}. 
Generate exactly 5 highly engaging, unique, and SEO-optimized blog post ideas. 
Return strictly a JSON array of objects. Each object must have these exact keys: 'title', 'focus_keyword', and 'hook'. Do not include markdown formatting, backticks, or any other text.";

$openAiPayload = [
    'model' => 'gpt-5.6-turbo',
    'messages' => [
        ['role' => 'user', 'content' => $systemPrompt]
    ],
    'temperature' => 0.7,
];

$chOpenAi = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($chOpenAi, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($openAiPayload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openAiApiKey
    ],
    CURLOPT_TIMEOUT => 60,
    CURLOPT_SSL_VERIFYPEER => $verifySsl
]);

$openAiResponse = curl_exec($chOpenAi);

if (curl_errno($chOpenAi)) {
    $error = curl_error($chOpenAi);
    curl_close($chOpenAi);
    exit(json_encode(['status' => 'error', 'message' => 'OpenAI API request failed: ' . $error]));
}
curl_close($chOpenAi);

$aiData = json_decode($openAiResponse, true);

if (isset($aiData['error'])) {
    exit(json_encode(['status' => 'error', 'message' => 'OpenAI API Error: ' . $aiData['error']['message']]));
}

$aiContent = $aiData['choices'][0]['message']['content'] ?? '[]';

// Clean up markdown if the AI mistakenly adds it (e.g., ```json ... ```)
$aiContent = preg_replace('/```json\s*/', '', $aiContent);$aiContent = preg_replace('/```/', '', $aiContent);

$ideas = json_decode(trim($aiContent), true);

if (!is_array($ideas) || empty($ideas)) {
    exit(json_encode(['status' => 'error', 'message' => 'Failed to parse AI response into valid JSON array.']));
}

// 3. Ensure the content_ideas table exists
$pdo->exec("
    CREATE TABLE IF NOT EXISTS content_ideas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        business_profile_id INT,
        title VARCHAR(255),
        focus_keyword VARCHAR(100),
        hook TEXT,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// 4. Save the generated ideas into the database
$insertStmt = $pdo->prepare("
    INSERT INTO content_ideas (business_profile_id, title, focus_keyword, hook) 
    VALUES (:profile_id, :title, :focus_keyword, :hook)
");

foreach ($ideas as $idea) {
    $insertStmt->execute([
        ':profile_id' => $businessId,
        ':title' => $idea['title'] ?? 'Untitled Idea',
        ':focus_keyword' => $idea['focus_keyword'] ?? '',
        ':hook' => $idea['hook'] ?? ''
    ]);
}

// Return success for the frontend terminal
echo json_encode([
    'status' => 'success', 
    'message' => 'Radar Agent completed successfully! 5 high-converting content ideas generated and securely saved.'
]);
?>