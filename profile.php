<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION["username"])) {
    echo "Session not set. Redirecting...";
    header("Location: login.html");
    exit();
}

$cur = $_SESSION['username'];
$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
// Send Friend Request
if (isset($_POST['send_request'])) {
    $toUser = $_POST['to_user'];
    if ($cur !== $toUser) {
        $createdAt = date('Y-m-d H:i:s');
        $insert_sql = "INSERT INTO friends (user1,user2,status,created_at)
                       VALUES ('$cur','$toUser','pending','$createdAt')";
        mysqli_query($con, $insert_sql);
        $_SESSION['toastMessage'] = "Friend request sent to $toUser.";
        $_SESSION['toastType'] = "info";
        header("Location: profile.php?id=$toUser");
        exit();
    }
}

// Accept Friend Request
if (isset($_POST['accept_request'])) {
    $update_sql = "UPDATE friends SET status='accepted' 
                   WHERE user1='$user' AND user2='$cur' AND status='pending'";
    mysqli_query($con, $update_sql);
    $_SESSION['toastMessage'] = "Friend request accepted.";
    $_SESSION['toastType'] = "success";
    header("Location: profile.php?id=$user");
    exit();
}

// Unfriend
if (isset($_POST['unfriend'])) {
    $user1 = $_POST['user1'];
    $user2 = $_POST['user2'];
    $sql = "DELETE FROM friends WHERE (user1='$user1' AND user2='$user2') OR (user1='$user2' AND user2='$user1')";
    mysqli_query($con, $sql);
    $_SESSION['toastMessage'] = "You have unfriended $user2.";
    $_SESSION['toastType'] = "warning";
    header("Location: profile.php?id=$user2");
    exit();
}


$toastMessage = '';
$toastType = ''; // success, danger, warning, info

// --- UNFRIEND HANDLING ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["unfriend"])) {
    $user1 = $_POST["user1"];
    $user2 = $_POST["user2"];

    // 1. Get images to delete from messages
    $getImagesSql = "SELECT content FROM message 
                     WHERE ((user = '$user1' AND `from` = '$user2') 
                         OR (user = '$user2' AND `from` = '$user1'))
                       AND type = 'image'";

    $imgResult = mysqli_query($con, $getImagesSql);

    if ($imgResult && mysqli_num_rows($imgResult) > 0) {
        while ($imgRow = mysqli_fetch_assoc($imgResult)) {
            $imagePath = $imgRow['content'];
            if (strpos($imagePath, 'messages/') === 0 && file_exists($imagePath)) {
                unlink($imagePath); // Delete the image file
            }
        }
    }

    // 2. Remove friendship (both directions)
    $sql = "DELETE FROM friends 
            WHERE (user1 = '$user1' AND user2 = '$user2') 
               OR (user1 = '$user2' AND user2 = '$user1')";
    mysqli_query($con, $sql);

    // 3. Delete all messages between users
    $deleteMessages = "DELETE FROM message 
                       WHERE (user = '$user1' AND `from` = '$user2') 
                          OR (user = '$user2' AND `from` = '$user1')";
    mysqli_query($con, $deleteMessages);

    // Set toast message (we will pass it via session because of redirect)
    $_SESSION['toastMessage'] = "You have unfriended $user2.";
    $_SESSION['toastType'] = "warning";

    // 4. Redirect back to profile.php with POST
    echo '<form id="postRedirect" method="POST" action="profile.php">
            <input type="hidden" name="id" value="' . htmlspecialchars($user2) . '">
          </form>
          <script>document.getElementById("postRedirect").submit();</script>';
    exit();
}

// --- FRIEND REQUEST HANDLING ---
if (isset($_POST['to_user'])) {
    $toUser = $_POST['to_user'];
    if ($cur !== $toUser) {
        $check_sql = "SELECT * FROM friends WHERE 
                      (user1 = '$cur' AND user2 = '$toUser') OR 
                      (user1 = '$toUser' AND user2 = '$cur')";
        $check_result = mysqli_query($con, $check_sql);
        date_default_timezone_set('Asia/Kolkata');
        $createdAt = date('Y-m-d H:i:s');  // Gives IST time

        if (mysqli_num_rows($check_result) == 0) {
            $insert_sql = "INSERT INTO friends (user1, user2, status, created_at) VALUES ('$cur', '$toUser', 'pending', '$createdAt')";
            if (mysqli_query($con, $insert_sql)) {
                $toastMessage = "Friend request sent to $toUser.";
                $toastType = "info";
            } else {
                $toastMessage = "Failed to send friend request.";
                $toastType = "danger";
            }
        } else {
            $toastMessage = "You are already friends or have a pending request.";
            $toastType = "warning";
        }
    }
}

// Check if toast message is stored in session (e.g., after unfriending redirect)
if (isset($_SESSION['toastMessage'])) {
    $toastMessage = $_SESSION['toastMessage'];
    $toastType = $_SESSION['toastType'] ?? 'info';
    unset($_SESSION['toastMessage'], $_SESSION['toastType']);
}

// Determine user profile to show
$user = $_POST["id"] ?? ($_POST['to_user'] ?? ($_GET['id'] ?? ''));

// Fetch account info
$query = "SELECT * FROM account WHERE username='$user'";
$result = mysqli_query($con, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $name = $row["fullname"];
$email = $row["email"];
$no = $row["phone"];
$path = $row["profile_picture"];
    $bio = $row["bio"];
    $gender = $row["gender"];
    $dob = $row["dob"];
    $created = $row["created_at"];

} else {
    // If no data found, show error toast
    $toastMessage = "No data found for user.";
    $toastType = "danger";
}

// Fetch posts
$sql = "SELECT * FROM posts WHERE user='$user'";
$post_result = mysqli_query($con, $sql);
$posts = [];
if ($post_result) {
    while ($row = mysqli_fetch_assoc($post_result)) {
        $posts[] = $row;
    }
}
$has_posts = count($posts) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>User Profile</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
  <link rel="stylesheet" href="index.css" />
  <link rel="icon" type="image/x-icon" href="/tablogo.png" />
  <style>
    .create-account-container { margin-top: 5%; }
    #img { margin: 0 auto; width: 80%; height: 100%; }
    .form-text { color: red; }
    .error { border-color: red; }
    .position-relative { position: relative; }
    .password-toggle {
      background-color: white; border: none; position: absolute;
      top: 50%; right: 10px; transform: translateY(-50%);
      cursor: pointer; z-index: 1;
    }
    .post-image {
      max-width: 100%;
      height: auto;
      border-radius: 5px;
    }
  </style>
</head>
<body style="background-color: black;">
  <div class="container">
    <a href="index.php" style="position: fixed; top: 2%; left: 0px; z-index: 1050; font-size: 1.2rem; color: white; padding: 8px 12px; border-radius: 5px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
      <i class="fas fa-arrow-left"></i>
    </a>
    <div class="row justify-content-center">
      <div class="col-md-6 col-sm-8 col-10" style="background-color: white; margin: 30px auto; border: 10px solid white; border-radius: 10px!important;">
        <div class="create-account-container">
          <div class="text-center mb-4">
            <h2>User Info</h2>
          </div>

        
            <?php
// Ensure $row contains full account data
$acc_sql = "SELECT * FROM account WHERE username='$user'";
$acc_res = mysqli_query($con, $acc_sql);
$row = mysqli_fetch_assoc($acc_res);
?>

<?php
if (isset($_POST["edit"])) {
    echo '<form id="createAccountForm" enctype="multipart/form-data" action="updateacc.php" method="post" onsubmit="return validateProfileForm()">';
}
?>

<?php
if (isset($_GET['error'])) {
    echo '<p class="text-danger text-center">' . htmlspecialchars($_GET['error']) . '</p>';
}
if (isset($_GET['success'])) {
    echo '<p class="text-success text-center">' . htmlspecialchars($_GET['success']) . '</p>';
}
?>

<!-- PROFILE IMAGE -->
<div class="form-group">
    <label class="form-label">Profile picture</label>
    <img src="<?php echo htmlspecialchars($row['profile_picture']); ?>" class="form-control" style="height:50%;" />

    <input type="hidden" name="old" value="<?php echo htmlspecialchars($row['profile_picture']); ?>">

    <?php if (isset($_POST["edit"])): ?>
        <label for="file" class="form-label mt-2">Choose new profile picture</label>
        <input type="file" accept="image/*" name="img" id="file" class="form-control">
    <?php endif; ?>
</div>

<!-- USERNAME (READONLY) -->
<?php if (isset($_POST["edit"])): ?>
<div class="form-group">
    <label for="inputUsername" class="form-label">Username</label>
    <input type="text" id="inputUsername" name="user" class="form-control"
           value="<?php echo htmlspecialchars($row['username']); ?>" readonly>
</div>
<?php endif; ?>

<!-- FULL NAME -->
<div class="form-group">
    <label>Full Name</label>
    <input type="text" class="form-control" name="fullname"
           value="<?php echo htmlspecialchars($row['fullname']); ?>"
           <?php echo isset($_POST["edit"]) ? "required" : "readonly"; ?>>
</div>

<!-- PHONE -->
<div class="form-group">
    <label>Phone</label>
    <input type="number" class="form-control" name="phone"
           value="<?php echo htmlspecialchars($row['phone']); ?>"
           <?php echo isset($_POST["edit"]) ? "required" : "readonly"; ?>>
</div>

<!-- EMAIL -->
<div class="form-group">
    <label>Email</label>
    <input type="email" class="form-control" name="email"
           value="<?php echo htmlspecialchars($row['email']); ?>"
           <?php echo isset($_POST["edit"]) ? "required" : "readonly"; ?>>
</div>

<!-- BIO -->
<div class="form-group">
    <label>Bio</label>
    <textarea class="form-control" name="bio"
        <?php echo isset($_POST["edit"]) ? "required" : "readonly"; ?>><?php 
        echo htmlspecialchars($row['bio']); ?></textarea>
</div>

<!-- GENDER -->
<div class="form-group">
    <label>Gender</label>

    <?php if (isset($_POST["edit"])): ?>
        <select class="form-control" name="gender" required>
            <option value="">Select</option>
            <option value="Male"   <?php if ($row['gender']=="Male") echo "selected"; ?>>Male</option>
            <option value="Female" <?php if ($row['gender']=="Female") echo "selected"; ?>>Female</option>
            <option value="Other"  <?php if ($row['gender']=="Other") echo "selected"; ?>>Other</option>
        </select>
    <?php else: ?>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['gender']); ?>" readonly>
    <?php endif; ?>
</div>

<!-- DOB -->
<div class="form-group">
    <label>Date of Birth</label>
    <input type="date" class="form-control" name="dob"
           value="<?php echo htmlspecialchars($row['dob']); ?>"
           <?php echo isset($_POST["edit"]) ? "required" : "readonly"; ?>>
</div>

<?php
if (isset($_POST["edit"])) {
    echo '<input type="submit" value="Update Profile" class="btn btn-success btn-block">';
    echo '</form><br>';
} else {
    
    if ($cur !== $user) { // Do not show friend button on own profile
    // Check friendship
    $friendCheck = "SELECT * FROM friends 
                    WHERE (user1='$cur' AND user2='$user') 
                       OR (user1='$user' AND user2='$cur')";
    $friendResult = mysqli_query($con, $friendCheck);
    $friendRow = mysqli_fetch_assoc($friendResult);

    $status = '';
    if ($friendRow) {
        $status = $friendRow['status']; // pending, accepted, blocked, etc.
    }

    // Determine button text and style
    if (!$friendRow) {
        $btnText = "Add Friend";
        $btnClass = "btn-primary btn-block";
        $action = "send_request";
    } elseif ($status === "pending") {
        if ($friendRow['user2'] === $cur) {
            $btnText = "Accept Request";
            $btnClass = "btn-success btn-block";
            $action = "accept_request";
        } else {
            $btnText = "Pending Request";
            $btnClass = "btn-warning btn-block";
            $action = "";
        }
    } elseif ($status === "accepted") {
        $btnText = "Unfriend";
        $btnClass = "btn-danger btn-block";
        $action = "unfriend";
    } else {
        $btnText = "Add Friend";
        $btnClass = "btn-primary btn-block";
        $action = "send_request";
    }
    ?>
    
    <form method="POST" style="margin-bottom: 20px;">
        <input type="hidden" name="to_user" value="<?php echo htmlspecialchars($user); ?>">
        <?php if ($action === 'unfriend'): ?>
            <input type="hidden" name="user1" value="<?php echo htmlspecialchars($cur); ?>">
            <input type="hidden" name="user2" value="<?php echo htmlspecialchars($user); ?>">
            <button type="submit" name="unfriend" class="btn <?php echo $btnClass; ?>">
                <?php echo $btnText; ?>
            </button>
        <?php elseif ($action === 'accept_request'): ?>
            <button type="submit" name="accept_request" class="btn <?php echo $btnClass; ?>">
                <?php echo $btnText; ?>
            </button>
        <?php elseif ($action === 'send_request'): ?>
            <button type="submit" name="send_request" class="btn <?php echo $btnClass; ?>">
                <?php echo $btnText; ?>
            </button>
        <?php else: ?>
            <button type="button" class="btn <?php echo $btnClass; ?>" disabled>
                <?php echo $btnText; ?>
            </button>
        <?php endif; ?>
    </form>
<?php } 
    
}
?>

<!-- POSTS SECTION -->
<button class="btn btn-primary btn-block mb-3" type="button" onclick="togglePosts()">View Posts</button>

<div id="posts-container" style="display:none;">
    <?php if (!$has_posts): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <label class="form-label">Posts</label>
   <?php foreach ($posts as $p): ?>
    <div class="post-item p-3 border-bottom">
        <img src="<?php echo htmlspecialchars($p['image']); ?>" class="mb-2 post-image"
             onerror="this.src='default.jpg'">

        <h5><?php echo htmlspecialchars($p['title']); ?></h5>
        <p><?php echo htmlspecialchars($p['content']); ?></p>

        <small class="text-muted">
            <?php echo htmlspecialchars($p['created_at'] ?? ''); ?>
        </small>
    </div>
<?php endforeach; ?>

    <?php endif; ?>
</div>

            
            

  <!-- Toast Notification HTML -->
  <div aria-live="polite" aria-atomic="true" class="position-fixed bottom-0 end-0 p-3" style="z-index: 1051; right: 1rem; bottom: 1rem;">
    <div id="liveToast" class="toast hide" role="alert" aria-live="assertive" aria-atomic="true" data-delay="5000">
      <div class="toast-header" id="toastHeader">
        <strong class="me-auto" id="toastTitle">Notification</strong>
        <button type="button" class="close ml-2 mb-1" data-dismiss="toast" aria-label="Close">
  <span aria-hidden="true">&times;</span>
</button>

      </div>
      <div class="toast-body" id="toastBody">
        Hello, world! This is a toast message.
      </div>
    </div>
  </div>

  <!-- JS Dependencies for Bootstrap 4 -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    function togglePosts() {
      const container = document.getElementById('posts-container');
      if (container.style.display === 'none' || container.style.display === '') {
        container.style.display = 'block';
      } else {
        container.style.display = 'none';
      }
    }
  </script>

  <?php if (!empty($toastMessage)): ?>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const toastEl = document.getElementById('liveToast');
      const toastTitle = document.getElementById('toastTitle');
      const toastBody = document.getElementById('toastBody');
      const toastHeader = document.getElementById('toastHeader');

      // Set message
      toastBody.textContent = <?php echo json_encode($toastMessage); ?>;

      // Clear previous classes
      toastHeader.className = "toast-header";

      // Set color based on type
      const type = <?php echo json_encode($toastType); ?>;
      if (type === 'success') {
        toastHeader.classList.add('bg-success', 'text-white');
        toastTitle.textContent = 'Success';
      } else if (type === 'danger') {
        toastHeader.classList.add('bg-danger', 'text-white');
        toastTitle.textContent = 'Error';
      } else if (type === 'warning') {
        toastHeader.classList.add('bg-warning');
        toastTitle.textContent = 'Warning';
      } else if (type === 'info') {
        toastHeader.classList.add('bg-info', 'text-white');
        toastTitle.textContent = 'Info';
      } else {
        toastTitle.textContent = 'Notification';
      }

      // Show toast (Bootstrap 4 uses jQuery)
      $('.toast').toast('show');
    });
  </script>
  <?php endif; ?>
            <script>
function validateProfileForm() {

    // --- FULL NAME ---
    const fullname = document.getElementsByName('fullname')[0].value.trim();
    const fullnameRegex = /^[A-Za-z\s\-]{3,}$/;
    if (!fullnameRegex.test(fullname)) {
        alert("Full name must be at least 3 characters and contain only letters or spaces.");
        return false;
    }

    // --- EMAIL ---
    const email = document.getElementsByName('email')[0].value.trim();
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Za-z]{2,}$/;
    if (!emailRegex.test(email)) {
        alert("Please enter a valid email address.");
        return false;
    }

    // --- PHONE ---
    const phone = document.getElementsByName('phone')[0].value.trim();
    const phoneRegex = /^\d{10}$/;
    if (phone && !phoneRegex.test(phone)) {
        alert("Phone number must be exactly 10 digits.");
        return false;
    }

    // --- BIO ---
    const bio = document.getElementsByName('bio')[0].value.trim();
    const bioRegex = /^[\s\S]{0,500}$/;
    if (!bioRegex.test(bio)) {
        alert("Bio must be 500 characters or less.");
        return false;
    }

    // --- GENDER ---
    const gender = document.getElementsByName('gender')[0].value;
    const genderRegex = /^(Male|Female|Other)$/;
    if (!genderRegex.test(gender)) {
        alert("Invalid gender selected.");
        return false;
    }

    // --- DOB ---
    const dob = document.getElementsByName('dob')[0].value.trim();
    const dobRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (dob && !dobRegex.test(dob)) {
        alert("Invalid date of birth format. Use YYYY-MM-DD.");
        return false;
    }

    return true;
}
</script>

</body>
</html>
