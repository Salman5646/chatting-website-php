<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!isset($_SESSION["username"])) {
    header("login.html");
}

if (!isset($_POST["target"])) {
    die("Target user not specified.");
}

$target = $_POST["target"];
$from = $_SESSION["username"];
$content = "";
$type = "text";  // default
date_default_timezone_set('Asia/Kolkata');
$time = date("Y-m-d H:i:s");

$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
if (!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Escape strings to prevent SQL injection
$target = mysqli_real_escape_string($con, $target);
$from = mysqli_real_escape_string($con, $from);

// Handle image upload if present
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'messages/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tmpName = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes

if ($_FILES['image']['size'] > $maxSize) {
    die("Image exceeds the 5MB size limit.");
}

    if (in_array($ext, $allowed)) {
        $newFileName = uniqid("img_") . "." . $ext;
        $filePath = $uploadDir . $newFileName;

        if (move_uploaded_file($tmpName, $filePath)) {
            $content = $filePath;
            $type = "image";
        } else {
            die("Image upload failed.");
        }
    } else {
        die("Invalid image format.");
    }
}

// Handle text message if no image uploaded
if (empty($content) && isset($_POST["msg"]) && trim($_POST["msg"]) !== "") {
    $msg = mysqli_real_escape_string($con, $_POST["msg"]);
    $content = $msg;
    $type = "text";
}

// Prevent empty messages
if (empty($content)) {
    die("Message cannot be empty.");
}

$query = "INSERT INTO message (`user`, `content`, `from`, `type`, `time`) 
          VALUES ('$target', '$content', '$from', '$type', '$time')";

$result = mysqli_query($con, $query);

if (!$result) {
    echo "Error: " . mysqli_error($con);
    echo " Unable to send!";
} else {
    echo "
<form id='redirectForm' method='post' action='message.php'>
    <input type='hidden' name='user' value='" . htmlspecialchars($target, ENT_QUOTES) . "'>
</form>
<script>document.getElementById('redirectForm').submit();</script>
";
exit();

}
?>
