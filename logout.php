<?php
// Start the session
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Delete the cookie
setcookie('ae_username', '', time() - 3600, '/'); // Set the cookie's expiration time in the past to delete it

// Redirect to the login page or any other appropriate page
header("Location: index.php");
exit;
?>
