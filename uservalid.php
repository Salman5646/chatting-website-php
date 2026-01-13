<?php
    // Sanitize the username input
    $con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
    $username = mysqli_real_escape_string($con, $_GET["username"]);
    
    // Query to check if the username exists
    $query = "SELECT * FROM account WHERE username='$username'";
    $result = mysqli_query($con, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        echo "Username not available"; // Username exists
    } else {
        echo "Available"; // Username is available
    }

?>
