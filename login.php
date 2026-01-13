<?php
session_start(); // Start the session

$un = $_POST["username"];
$pass = $_POST["password"];

$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");

$query = "SELECT * FROM account WHERE username='$un'";

$result = mysqli_query($con, $query);

if ($row = mysqli_fetch_assoc($result)) {
    // Verify the hashed password
    if (password_verify($pass, $row['password'])) {
        // Set session variables
        $_SESSION['username'] = $un;
        
        header("Location:index.php");
        exit();
    } else {
        header("refresh:2;url=login.html");
        echo "Invalid Credentials! Try Again";
    }
} else {
    header("refresh:2;url=login.html");
    echo "Invalid Credentials! Try Again";
}
?>
