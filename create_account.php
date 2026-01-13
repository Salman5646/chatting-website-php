<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $email = trim($_POST["email"]);
    $fullname = strtoupper(trim($_POST["fullname"]));

    // Optional fields
    $phone = !empty($_POST["phone"]) ? trim($_POST["phone"]) : NULL;
    $bio = !empty($_POST["bio"]) ? trim($_POST["bio"]) : NULL;
    $gender = !empty($_POST["gender"]) ? trim($_POST["gender"]) : NULL;
    $dob = !empty($_POST["dob"]) ? trim($_POST["dob"]) : NULL;

    $targetFile = NULL;
    if (isset($_FILES["img"]) && $_FILES["img"]["error"] == 0) {
        $allowedTypes = ['image/jpeg','image/png','image/gif'];
        if (in_array($_FILES["img"]["type"], $allowedTypes)) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $targetFile = $targetDir . time() . '_' . basename($_FILES["img"]["name"]);
            if (!move_uploaded_file($_FILES["img"]["tmp_name"], $targetFile)) {
                $error = "Failed to upload profile picture.";
            }
        } else {
            $error = "Invalid image format. Only JPG, PNG, GIF allowed.";
        }
    }

    if (!isset($error)) {
        $con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
        if (!$con) die("Connection failed: " . mysqli_connect_error());

        mysqli_query($con, "SET time_zone = '+05:30'");

        $stmt = $con->prepare("INSERT INTO account
            (username,password,email,phone,fullname,bio,gender,dob,profile_picture,created_at)
            VALUES (?,?,?,?,?,?,?,?,?,NOW())");

   $stmt->bind_param(
    "sssssssss",
    $username,
    $password,
    $email,
    $phone,
    $fullname,
    $bio,
    $gender,
    $dob,
    $targetFile
);


        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            header("Location: index.php");
            exit();
        } else {
            $error = "Unable to create account. Possibly username/email exists.";
        }

        $stmt->close();
        mysqli_close($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Account</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="index.css"/>
  <link rel="icon" type="image/x-icon" href="/tablogo.png"/>
  <style>
    .S9gUrf-YoZ4jf, .S9gUrf-YoZ4jf * { margin: 0 auto!important; }
.create-account-container {
  margin-top: 5%;
  background: white;
  border-radius: 12px;
  
}
 
    .form-text {
  color: #d32f2f; /* Material red */
}

.error {
  border-color: #d32f2f !important;
}
input[type="text"],
input[type="email"],
input[type="password"],
input[type="file"],
input[type="number"],
button.btn {
  border-radius: 6px !important;
  transition: all 0.2s ease-in-out;
}

input:focus,
button:focus {
  box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  outline: none;
}
#g_id_onload, .g_id_signin {
  display: flex;
  justify-content: center;
  margin-top: 20px;
}

    .position-relative { position: relative; }
    .password-toggle {
      background-color: white; border: none; position: absolute;
      top: 50%; right: 10px; transform: translateY(-50%);
      cursor: pointer; z-index: 1;
    }
    body {
  background: linear-gradient(135deg, #e0f7fa, #fce4ec);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

  </style>
</head>
<body>
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6 col-sm-8 col-10" style="background-color: white;margin:30px auto;border:10px solid white;border-radius:10px!important">
      <div class="create-account-container">
        <div class="text-center mb-4">
            <h2>Create Account</h2>
        </div>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form id="createAccountForm" style="padding-bottom: 10px;" enctype="multipart/form-data" action="" method="post">
          <div class="form-group">
            <label for="inputUsername" class="form-label">Username</label>
            <input type="text" id="inputUsername" placeholder="Enter username" name="username" class="form-control" required>
            <div id="usernameHelpBlock" class="form-text"></div>
            <div id="usernameValidBlock" class="form-text"></div>
          </div>
          <div class="form-group">
            <label for="fullname">Full Name</label>
            <input type="text" onchange="cap()" class="form-control" id="fullname" name="fullname" placeholder="Enter full name" required>
            <div id="fullnameHelpBlock" class="form-text"></div>
          </div>
          <div class="form-group position-relative">
            <label for="inputPassword" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" name="password" id="inputPassword" class="form-control" required>
              <span class="input-group-append">
                <button class="input-group-text password-toggle" onclick="togglePasswordVisibility()" type="button"><i id="eyeIcon" class="fas fa-eye"></i></button>
              </span>
            </div>
            <div id="passwordHelpBlock" class="form-text"></div>
          </div>
          <div class="form-group">
            <label for="file" class="form-label">Choose your profile picture</label>
            <input type="file" id="file" name="img" class="form-control" required onchange="previewProfileImage()">
            <img id="profilePreview" style="display:none; width:100%; margin-top:10px; border-radius:8px;" />
          </div>
          <div class="form-group">
            <label for="inputPhone" class="form-label">Phone no</label>
            <input type="number" id="inputPhone" name="phone" placeholder="Enter phone no" class="form-control" required>
            <div id="phoneHelpBlock" class="form-text"></div>
          </div>
          <div class="form-group">
            <label for="inputEmail" class="form-label">Email</label>
            <input type="email" id="inputEmail" placeholder="Enter email" name="email" class="form-control" required>
            <div id="emailHelpBlock" class="form-text"></div>
          </div>
             <div class="form-group">
    <label>Bio</label>
    <textarea name="bio" class="form-control"></textarea>
  </div>
  <div class="form-group">
    <label>Gender</label>
    <select name="gender" class="form-control">
      <option value="">Select</option>
      <option value="Male">Male</option>
      <option value="Female">Female</option>
      <option value="Other">Other</option>
    </select>
  </div>
  <div class="form-group">
    <label>Date of Birth</label>
    <input type="date" name="dob" class="form-control">
  </div>
          <center><h6 style="padding:10px">Already Have an Account? <a href="login.html">Login here</a></h6></center>
          <button type="submit" class="btn btn-primary btn-block">Create Account</button>
        </form>
        <div style="text-align:center; margin:20px 0;">
          <div id="g_id_onload"
               data-client_id="78541695364-o7uo5akjgbtim5gdf31vdikojndcfqmc.apps.googleusercontent.com"
               data-callback="handleCredentialResponse"
               data-auto_prompt="false">
          </div>
          <div class="g_id_signin"
               data-type="standard"
               data-size="large"
               data-theme="outline"
               data-text="sign_in_with"
               data-shape="rectangular"
               data-logo_alignment="left">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

<script>
function togglePasswordVisibility() {
  var passwordField = document.getElementById("inputPassword");
  var eyeIcon = document.getElementById("eyeIcon");
  if (passwordField.type === "password") {
    passwordField.type = "text";
    eyeIcon.className = "fas fa-eye-slash";
  } else {
    passwordField.type = "password";
    eyeIcon.className = "fas fa-eye";
  }
}
function cap() {
  document.getElementById('fullname').value = document.getElementById('fullname').value.toUpperCase();
}
function previewProfileImage() {
  const fileInput = document.getElementById('file');
  const preview = document.getElementById('profilePreview');
  const file = fileInput.files[0];
  if (file && file.type.startsWith('image/')) {
    const reader = new FileReader();
    reader.onload = function(e) {
      preview.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(file);
  } else {
    preview.src = '';
    preview.style.display = 'none';
  }
}
document.getElementById('createAccountForm').addEventListener('submit', function(event) {
  event.preventDefault();
  if (validateForm()) checkUsernameAvailability();
});
function validateForm() {
    // Clear previous errors
    document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    document.querySelectorAll('.form-text').forEach(el => el.textContent = '');

    // Username: letters, numbers, underscores, 4-20 chars
    const username = document.getElementById('inputUsername').value.trim();
    const usernameRegex = /^[a-zA-Z0-9_.]{4,20}$/;
    if (!usernameRegex.test(username)) {
        document.getElementById('inputUsername').classList.add('error');
        document.getElementById('usernameHelpBlock').textContent = 'Username must be 4-20 characters long and contain only letters, digits, dots and underscores.';
        return false;
    }

    // Password: min 8 chars, at least 1 letter and 1 number
    const password = document.getElementById('inputPassword').value;
    const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwordRegex.test(password)) {
        document.getElementById('inputPassword').classList.add('error');
        document.getElementById('passwordHelpBlock').textContent = 'Password must be at least 8 characters long and include letters and numbers.';
        return false;
    }

    // Fullname: letters, spaces, hyphens, min 6 chars
    const fullname = document.getElementById('fullname').value.trim();
    const fullnameRegex = /^[A-Za-z\s\-]{6,}$/;
    if (!fullnameRegex.test(fullname)) {
        document.getElementById('fullname').classList.add('error');
        document.getElementById('fullnameHelpBlock').textContent = 'Full name must be at least 6 letters and contain only letters, spaces, or hyphens.';
        return false;
    }

    // Email: standard email format
    const email = document.getElementById('inputEmail').value.trim();
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailRegex.test(email)) {
        document.getElementById('inputEmail').classList.add('error');
        document.getElementById('emailHelpBlock').textContent = 'Please enter a valid email address.';
        return false;
    }

    // Phone: optional, but if provided must be 10 digits
    const phone = document.getElementById('inputPhone').value.trim();
    const phoneRegex = /^\d{10}$/;
    if (phone && !phoneRegex.test(phone)) {
        document.getElementById('inputPhone').classList.add('error');
        document.getElementById('phoneHelpBlock').textContent = 'Phone must be 10 digits.';
        return false;
    }

    // Bio: optional, max 500 characters
    const bio = document.querySelector('textarea[name="bio"]').value.trim();
    const bioRegex = /^[\s\S]{0,500}$/;
    if (!bioRegex.test(bio)) {
        alert('Bio can be at most 500 characters.');
        return false;
    }

    // Gender: optional, only Male, Female, Other
    const gender = document.querySelector('select[name="gender"]').value;
    const genderRegex = /^(Male|Female|Other)?$/;
    if (!genderRegex.test(gender)) {
        alert('Invalid gender selected.');
        return false;
    }

    // DOB: optional, format YYYY-MM-DD
    const dob = document.querySelector('input[name="dob"]').value;
    const dobRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (dob && !dobRegex.test(dob)) {
        alert('Invalid date of birth format.');
        return false;
    }

    return true; // All validations passed
}

// Attach validation to form submit
document.getElementById('createAccountForm').addEventListener('submit', function(event) {
    event.preventDefault();
    if (validateForm()) {
        // Optionally check username availability via AJAX before submit
        checkUsernameAvailability();
    }
});

function checkUsernameAvailability() {
var xhr = new XMLHttpRequest();
var username = document.getElementById('inputUsername').value;
xhr.open('GET', 'uservalid.php?username=' + encodeURIComponent(username), true);
xhr.onreadystatechange = function() {
if (xhr.readyState === XMLHttpRequest.DONE) {
if (xhr.status === 200) {
if (xhr.responseText === "Username not available") {
document.getElementById('inputUsername').classList.add('error');
document.getElementById('usernameValidBlock').textContent = 'Username not available';
} else if (xhr.responseText === "Available") {
document.getElementById('inputUsername').classList.remove('error');
document.getElementById('usernameValidBlock').textContent = '';
document.getElementById('createAccountForm').submit();
}
} else {
console.error('Error occurred while fetching the message:', xhr.status);
}
}
};
xhr.send();
}
</script>

<script src="https://accounts.google.com/gsi/client" async defer></script> 
<script> function handleCredentialResponse(response) { fetch('google-login.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'id_token=' + encodeURIComponent(response.credential) }) .then(res => res.text()) .then(data => { if (data.includes('success')) { window.location.href = 'index.php'; } }) .catch(err => { console.error('Google login error:', err); }); } 
</script> 
</body> 
</html> 
