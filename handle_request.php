<?php
session_start();
if(!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$cur = $_SESSION['username'];
$fromUser = $_POST['fromUser'] ?? '';
$action = $_POST['action'] ?? '';

if ($fromUser && in_array($action, ['accept', 'reject'])) {
    if ($action === 'accept') {
        // Update the friend request status to accepted
        $sql = "UPDATE friends SET status='accepted' WHERE user1='$fromUser' AND user2='$cur' AND status='pending'";
    } else {
        // Delete the friend request on rejection
        $sql = "DELETE FROM friends WHERE user1='$fromUser' AND user2='$cur' AND status='pending'";
    }

    if (mysqli_query($con, $sql)) {
        header("Location: friendrequests.php");
        exit();
    } else {
        echo "Error processing request: " . mysqli_error($con);
    }
} else {
    echo "Invalid request.";
}

mysqli_close($con);
?>
