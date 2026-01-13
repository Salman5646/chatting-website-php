<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set headers
header("Access-Control-Allow-Origin: https://chat.byethost17.com");
header("Cross-Origin-Opener-Policy: same-origin-allow-popups");
header("Content-Type: application/json");

session_start();

// Database connection
$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");

if (mysqli_connect_errno()) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed"]);
    exit;
}

// Check for ID token
if (!isset($_POST['id_token'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID token missing"]);
    exit;
}

$id_token = $_POST['id_token'];
$client_id = "78541695364-o7uo5akjgbtim5gdf31vdikojndcfqmc.apps.googleusercontent.com";

// Verify ID token from Google
$verify_url = "https://oauth2.googleapis.com/tokeninfo?id_token=" . urlencode($id_token);
$response = @file_get_contents($verify_url);

if ($response === false) {
    http_response_code(502);
    echo json_encode(["error" => "Failed to contact Google"]);
    exit;
}

$data = json_decode($response, true);

// Validate response
if (!is_array($data) || !isset($data['aud']) || $data['aud'] !== $client_id) {
    http_response_code(401);
    echo json_encode(["error" => "Invalid ID token"]);
    exit;
}

// Extract Google data
$email = $data['email'];
$fullname = $data['name'];
$google_id = $data['sub'];
$picture = $data['picture'];
$username = explode("@", $email)[0];

// Check if user exists
$sql = "SELECT * FROM account WHERE email = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Insert if new user
if ($result->num_rows === 0) {
    $insert_sql = "INSERT INTO account (username, password, email, fullname, profile_picture, google_id)
                   VALUES (?, NULL, ?, ?, ?, ?)";

    $insert_stmt = $con->prepare($insert_sql);
    $insert_stmt->bind_param("sssss", $username, $email, $fullname, $picture, $google_id);
    $insert_stmt->execute();
}

// Set session
$_SESSION['username'] = $username;

// Response
echo json_encode(["status" => "success", "username" => $username]);

?>
