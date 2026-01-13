<?php
header("Content-Type: text/plain");

$api_key = "YOUR_KEY_HERE";

$prompt = "Say hi in one sentence.";

$data = [
    "model" => "meta-llama/llama-3.1-8b-instruct",
    "messages" => [["role" => "user", "content" => $prompt]],
    "max_tokens" => 50,
    "temperature" => 0.7
];

$ch = curl_init("https://api.zenmux.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $api_key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
if (!$response) {
    echo "CURL Error: " . curl_error($ch);
    exit;
}
curl_close($ch);

echo $response;
?>
