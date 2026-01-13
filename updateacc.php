<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$con = mysqli_connect(
    "sql209.byethost17.com",
    "b17_40616871",
    "Salman@56",
    "b17_40616871_main"
);

if (!$con) {
    die("Database connection failed");
}

$user = $_POST["user"];
$fullname = $_POST["fullname"];
$email = $_POST["email"];
$phone = $_POST["phone"];
$bio = $_POST["bio"];
$gender = $_POST["gender"];
$dob = $_POST["dob"];
$oldPic = $_POST["old"];

// --------------------------------
// HANDLE IMAGE UPLOAD
// --------------------------------
if (!empty($_FILES["img"]["name"])) {

    $imgName = $_FILES["img"]["name"];
    $tmpName = $_FILES["img"]["tmp_name"];

    $newPath = "profiles/" . time() . "_" . basename($imgName);

    if (move_uploaded_file($tmpName, $newPath)) {
        // delete old image
        if ($oldPic != "" && file_exists($oldPic)) {
            unlink($oldPic);
        }
        $profilePic = $newPath;
    } else {
        $profilePic = $oldPic;
    }

} else {
    $profilePic = $oldPic;
}

// --------------------------------
// UPDATE QUERY
// --------------------------------

$sql = "UPDATE account SET 
        fullname='$fullname',
        email='$email',
        phone='$phone',
        bio='$bio',
        gender='$gender',
        dob='$dob',
        profile_picture='$profilePic'
        WHERE username='$user'";

if (mysqli_query($con, $sql)) {
    header("Location: index.php");
    exit();
} else {
    header("Location: profile.php");
    exit();
}
?>
