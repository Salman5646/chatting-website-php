<?php
session_start();

header('Content-Type: application/json'); // ✅ Always send JSON

if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$cur = $_SESSION['username'];
$target = $_POST["user"] ?? $_GET["user"] ?? null;

if (!$target) {
    http_response_code(400);
    echo json_encode(["error" => "No target specified"]);
    exit;
}

$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
if (!$con) {
    http_response_code(500);
    echo json_encode(["error" => "DB connection error"]);
    exit;
}

// Fetch messages
$query = "SELECT * FROM message WHERE 
          (`user`='$target' AND `from`='$cur') OR 
          (`user`='$cur' AND `from`='$target')
          ORDER BY time ASC";

$result = mysqli_query($con, $query);
if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "DB query error"]);
    exit;
}

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        "from" => $row["from"],
        "user" => $row["user"],
        "content" => $row["content"],
        "type" => $row["type"],
        "time" => $row["time"]
    ];
}

echo json_encode($messages);
?>