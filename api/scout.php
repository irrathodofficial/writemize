<?php
// api/scout.php

declare(strict_types=1);

// Load database connection
require_once '../db_config.php';

// ---------------------------------------------------------
// SMART SSL CHECK: Disable SSL verify ONLY for localhost
// ---------------------------------------------------------
$isLocalhost = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['SERVER_NAME'] === 'localhost';
$verifySsl = !$isLocalhost; // false on localhost, true on live server

// 1. Fetch pending business
$stmt = $pdo->query("SELECT * FROM businesses ORDER BY id ASC LIMIT 1");
$business = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$business) {
    exit('No businesses found in the database.');
}

$url = $business['website_url'];

// 2. Scrape HTML and extract text
// Context for cURL to bypass local SSL if needed
$contextOptions = [
    "ssl" => [
        "verify_peer" => $verifySsl,
        "verify_peer_name" => $verifySsl,
    ],
];
$context = stream_context_create($contextOptions);

$html = @file_get_contents($url, false, $context);

if ($html === false) {
    exit('Unable to fetch website content. Please check the URL.');
}

$text = strip_tags($html);
$text = preg_replace('/\s+/', ' ', $text);
$text = trim($text);
$text = substr($text, 0, 2000); // Limit tokens for the AI model

// 3. Call OpenAI (GPT-5.6) to analyze the text
$openAiApiKey = getenv('OPENAI_API_KEY'); 
if (!$openAiApiKey) {
    exit('OpenAI API Key not set in db_config.php');
}

$systemPrompt = "Act as an expert brand strategist. Analyze the following text from a business website. Determine their niche, brand tone, target audience, 5 core SEO keywords, and a short summary. Return strictly a JSON object with the exact keys: niche, tone, audience, keywords (an array of strings), and summary. Do not include any markdown formatting or extra text.";

$openAiPayload = [
    'model' => 'gpt-5.6-turbo',
    'messages' => [
        ['role' => 'system', 'content' => $systemPrompt],
        ['role' => 'user', 'content' => $text]
    ],
    'temperature' => 0.7,
    'response_format' => ['type' => 'json_object']
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
    CURLOPT_SSL_VERIFYPEER => $verifySsl // Dynamic SSL Check
]);

$openAiResponse = curl_exec($chOpenAi);

if (curl_errno($chOpenAi)) {
    $error = curl_error($chOpenAi);
    curl_close($chOpenAi);
    exit('OpenAI request failed: ' . $error);
}
curl_close($chOpenAi);

$aiData = json_decode($openAiResponse, true);

// Check if OpenAI returned an API error (like invalid key or quota exceeded)
if (isset($aiData['error'])) {
    exit('OpenAI API Error: ' . $aiData['error']['message']);
}

if (!isset($aiData['choices'][0]['message']['content'])) {
    exit('Invalid response format from OpenAI.');
}

// Extract parsed JSON from AI
$aiContent = json_decode($aiData['choices'][0]['message']['content'], true);

$niche = $aiContent['niche'] ?? 'Unknown Niche';
$tone = $aiContent['tone'] ?? 'Professional';
$audience = $aiContent['audience'] ?? 'General Audience';
$keywordsArray = $aiContent['keywords'] ?? ['business', 'services'];
$summary = $aiContent['summary'] ?? 'No summary generated.';

// 4. Create search query for SerpAPI using the AI-generated keywords
$searchQuery = implode(' ', array_slice($keywordsArray, 0, 3)); 

$serpApiKey = getenv('SERPAPI_KEY'); 
if (!$serpApiKey) {
    exit('SerpAPI Key not set in db_config.php');
}

$endpoint = 'https://serpapi.com/search.json?' . http_build_query([
    'engine' => 'google',
    'q' => $searchQuery,
    'api_key' => $serpApiKey
]);

$chSerp = curl_init();
curl_setopt_array($chSerp, [
    CURLOPT_URL => $endpoint,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 60,
    CURLOPT_SSL_VERIFYPEER => $verifySsl // Dynamic SSL Check
]);

$response = curl_exec($chSerp);

if (curl_errno($chSerp)) {
    $error = curl_error($chSerp);
    curl_close($chSerp);
    exit('SerpAPI request failed: ' . $error);
}
curl_close($chSerp);

$data = json_decode($response, true);
$urls = [];

if (!empty($data['organic_results'])) {
    foreach ($data['organic_results'] as $result) {
        if (!empty($result['link'])) {
            $urls[] = $result['link'];
        }
        if (count($urls) >= 3) {
            break;
        }
    }
}

// Fallback if less than 3 URLs found
while (count($urls) < 3) {
    $urls[] = 'Not found';
}

// 5. Save everything securely to the database
$keywordsJson = json_encode($keywordsArray);
$competitorsJson = json_encode($urls);

$insert = $pdo->prepare("
    INSERT INTO business_profiles 
    (business_id, niche, tone, audience, keywords, competitors, summary) 
    VALUES 
    (:business_id, :niche, :tone, :audience, :keywords, :competitors, :summary)
");

$insert->execute([
    ':business_id' => $business['id'],
    ':niche' => $niche,
    ':tone' => $tone,
    ':audience' => $audience,
    ':keywords' => $keywordsJson,
    ':competitors' => $competitorsJson,
    ':summary' => $summary
]);

echo "Scout Agent completed successfully! Business Profile created.";
?>