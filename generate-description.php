<?php
header('Content-Type: text/plain');

$groq_api_key = "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"; // your Groq key

$title = $_POST['title'] ?? '';

if (empty($title)) {
    http_response_code(400);
    echo "Error: Missing title.";
    exit;
}

$prompt = "Write a natural, human-like caption (20-50 words) based on this title: \"$title\".
Do not mention AI. Do not mention image or photo.";

$payload = [
    "model" => "llama-3.1-8b-instant",
    "messages" => [
        [
            "role" => "user",
            "content" => $prompt
        ]
    ],
    "temperature" => 0.7
];

$ch = curl_init("https://api.groq.com/openai/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $groq_api_key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);

if (curl_errno($ch)) {
    http_response_code(500);
    echo "cURL Error: " . curl_error($ch);
    curl_close($ch);
    exit;
}
curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['choices'][0]['message']['content'])) {
    echo "Error: Invalid response\n$response";
    exit;
}

echo trim($data['choices'][0]['message']['content']);
