<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit;
}
$cur = $_SESSION['username'];

// --- SINGLE DB CONNECTION FOR THE PAGE ---
$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// ---- FETCH POSTS ----
$sql = "SELECT * FROM posts ORDER BY RAND()";
$result = mysqli_query($con, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($con));
}
$posts = [];
while ($r = mysqli_fetch_assoc($result)) {
    $posts[] = $r;
}
$has_posts = count($posts) > 0;

// ---- CHECK IF CURRENT USER PROFILE HAS ANY MISSING FIELDS (pending) ----
$cur_esc = mysqli_real_escape_string($con, $cur);
$check_sql = "SELECT fullname, email, phone, bio, gender, dob, profile_picture FROM account WHERE username = '$cur_esc' LIMIT 1";
$check_result = mysqli_query($con, $check_sql);
$pending = false;
if ($check_result) {
    $rowPending = mysqli_fetch_assoc($check_result);
    foreach ($rowPending as $field => $value) {
        if ($value === NULL || trim((string)$value) === "") {
            $pending = true;
            break;
        }
    }
}

// ---- GET COUNT OF PENDING FRIEND REQUESTS ----
$rq_sql = "SELECT COUNT(*) AS cnt FROM friends WHERE user2 = '$cur_esc' AND status = 'pending'";
$rq_result = mysqli_query($con, $rq_sql);
$pending_requests_count = 0;
if ($rq_result) {
    $rq_row = mysqli_fetch_assoc($rq_result);
    $pending_requests_count = (int)$rq_row['cnt'];
}

// --- HELPER: check accepted friendship (uses escaping) ---
function isFriend($con, $user1, $user2) {
    $u1 = mysqli_real_escape_string($con, $user1);
    $u2 = mysqli_real_escape_string($con, $user2);

    $sql = "
      SELECT 1 FROM friends 
      WHERE ((user1 = '$u1' AND user2 = '$u2') OR (user1 = '$u2' AND user2 = '$u1')) 
        AND status = 'accepted'
      LIMIT 1
    ";
    $res = mysqli_query($con, $sql);
    if (!$res) return false;
    return mysqli_num_rows($res) > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Posts</title>

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link rel="stylesheet" href="index.css">

  <style>
      .img-fluid{
    width: 100%;
    border-radius:0px !important;
    max-height: 450px !important;
      }
      .nav-link:hover { text-decoration: none; transform: none; background-color: lightblue; color: black; border-radius: 10px; } 
      .navbar { position: sticky; top: 0; height: auto !important; z-index: 1000; } 
      .navbar-brand img { margin-left: 2rem; width: 2.5rem; border-radius: 50%; height: auto; } 
      .flex-hero { border: 0.2px solid grey; margin-bottom: 0.5px; justify-content: left; align-items: center; height: 10% !important; display: flex; text-align: center; color: black; } 
      .tab{ width:33%; }
      .tabs { text-align: center; border-bottom: 1px solid black; }
      .tab-link { color: black; }
      .post-item { cursor: pointer; transition: background-color 0.2s ease; background: white; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); margin-bottom: 1rem; padding: 1rem; border-radius: 10px; }
      .post-item:hover { background-color: #f2f2f2; border-radius: 5px; } 
      .post-image { width: 90%; margin: 0 auto; max-height: 300px!important; object-fit: cover; border-radius: 5px; } .
      text-muted, .mb-1 { margin: 1rem; } 
      .active { color: blue !important; font-weight: bold; } 
      .req{ color:white; text-decoration:none; } 
      .small-badge { padding: 3px 6px; vertical-align: top; }
      .offcanvas-body{ overflow-y:auto; } 
      .account-tab { position: relative; display: inline-block; margin-right:0.5rem; } 
      .profile-badge { position: absolute; top: -5px; right: -10px; background: red; color: white; padding: 2px 6px; font-size: 10px; border-radius: 50%; } 
      .mobile-account { position: relative; display: inline-block; } 
      .mobile-profile-badge { position: absolute; top: -4px; right: -8px; background: red; color: white; padding: 2px 6px; font-size: 10px; border-radius: 50%; }
      .nav-tabs {
    display: flex;
    justify-content: center !important;
    text-align: center;
    width: 100%;
}

.nav-tabs .nav-item {
    flex: 1;
    text-align: center;
}

.nav-tabs .nav-link {
    width: 100%;
}

      
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><img src="whatsapp.png" alt="Logo" style="width:30px;height:24px;"></a>

    <button class="btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" style="height:38px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white"><path fill-rule="evenodd" d="M2.5 12.5a.5.5 0 010-1h11a.5.5 0 010 1h-11zm0-5a.5.5 0 010-1h11a.5.5 0 010 1h-11zm0-5a.5.5 0 010-1h11a.5.5 0 010 1h-11z"/></svg>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <!-- My Account (form POST) -->
        <li class="nav-item">
          <form method="post" action="profile.php" style="display:inline;">
            <input type="hidden" name="edit" value="none">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($cur); ?>">
            <div class="account-tab">
              <input class="nav-link" type="submit" value="My Account">
              <?php if ($pending): ?><span class="profile-badge">!</span><?php endif; ?>
            </div>
          </form>
        </li>

        <!-- Friend Requests -->
        <li class="nav-item">
          <a class="nav-link tab-link req" href="friendrequests.php">
            Requests
            <?php if ($pending_requests_count > 0) echo "<span class='badge bg-danger small-badge'>{$pending_requests_count}</span>"; ?>
          </a>
        </li>

        <li class="nav-item"><a class="nav-link" href="addpost.php">Add post</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Offcanvas mobile menu -->
<div class="offcanvas offcanvas-end" id="offcanvasRight" style="width:40%;height:50%;">
  <div class="offcanvas-header"><h5 class="offcanvas-title">Menu</h5><button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button></div>
  <div class="offcanvas-body">
    <ul class="navbar-nav">
      <li class="nav-item mb-2 mobile-account">
        <form method="post" action="profile.php" class="d-inline">
          <input type="hidden" name="edit" value="none">
          <input type="hidden" name="id" value="<?php echo htmlspecialchars($cur); ?>">
          <input class="nav-link" style="color:black" type="submit" value="My Account">
          <?php if ($pending): ?><span class="mobile-profile-badge">!</span><?php endif; ?>
        </form>
      </li>
      <li class="nav-item mb-2"><a class="nav-link" style="color:black" href="friendrequests.php">Requests <?php if ($pending_requests_count>0) echo "<span class='badge bg-danger small-badge'>{$pending_requests_count}</span>"; ?></a></li>
      <li class="nav-item mb-2"><a class="nav-link" style="color:black" href="addpost.php">Add post</a></li>
      <li class="nav-item mb-2"><a class="nav-link" style="color:black" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a></li>
    </ul>
  </div>
</div>

<!-- POSTS -->
<section class="hero py-4">
  <div class="container">
    <div class="mx-auto" style="max-width:720px;">
     <ul class="nav nav-tabs"> <li class="nav-item tab"><a class="nav-link tab-link" href="addfriends.php"><i class="bi bi-people"></i></a></li> <li class="nav-item tab"><a class="nav-link tab-link" href="index.php"><i class="bi bi-chat-dots"></i></a></li> <li class="nav-item tab"><a class="nav-link tab-link active" href="viewposts.php"><i class="bi bi-card-image"></i></a></li> </ul>

      <div id="posts-container">
        <?php if (!$has_posts): ?>
          <p>No posts found.</p>
        <?php else: ?>
          <?php foreach ($posts as $post):
            // Use post id as unique HTML id - avoid duplicate username ids
            $post_id = isset($post['id']) ? (int)$post['id'] : uniqid();
            $title = isset($post['title']) ? htmlspecialchars($post['title']) : '';
            $content = isset($post['content']) ? htmlspecialchars($post['content']) : '';
            $image = isset($post['image']) ? htmlspecialchars($post['image']) : '';
            $post_user = isset($post['user']) ? htmlspecialchars($post['user']) : '';
            $created_at = isset($post['created_at']) ? htmlspecialchars($post['created_at']) : '';
          ?>
            <div class="post-item d-flex flex-column p-3 border-bottom" id="post_<?php echo $post_id; ?>">
              <?php if ($image !== ''): ?>
                <img src="<?php echo $image; ?>" alt="Post Image" class="mb-2 post-image" onerror="this.src='fallback-image.jpg'">
              <?php endif; ?>
              <div>
                <h5 class="mb-1"><?php echo $title; ?></h5>
                <p class="mb-0 text-muted"><?php echo $content; ?></p>
                <small class="text-muted"><?php echo $created_at; ?></small>

                <?php
                if ($cur === $post_user) {
                    echo '<p class="mb-0 text-muted">Your Post</p>';
                } else {
                    // check friendship
                    $friend = isFriend($con, $cur, $post_user);
                    if ($friend) {
                        // friends → chat
                        ?>
                        <p class="mb-0 text-muted">
                          <a style="color:blue; cursor:pointer;" onclick="openChat('<?php echo htmlspecialchars($post_user, ENT_QUOTES); ?>')">
                            Chat with <?php echo htmlspecialchars($post_user); ?>
                          </a>
                        </p>
                        <?php
                    } else {
                        // not friends → open profile (POST)
                        ?>
                        <p class="mb-0 text-muted">
                          <a style="color:green; cursor:pointer;" onclick="openProfile('<?php echo htmlspecialchars($post_user, ENT_QUOTES); ?>')">
                            View Profile
                          </a>
                        </p>
                        <?php
                    }
                }
                ?>

              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>

<!-- Image Zoom Modal (Reusable for posts) -->
<div class="modal fade" id="postImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body p-0">
        <img id="postModalImage" src="" class="img-fluid" style="border-radius: 10px;">
      </div>
      </div>
  </div>
</div>
    
    
<!-- Logout modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Confirm Logout</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">Are you sure you want to logout?</div>
    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">No</button><button onclick="window.open('logout.php','_self')" class="btn btn-primary">Yes</button></div>
  </div></div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openChat(elUser) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'message.php';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'user';
    input.value = elUser;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

function openProfile(elUser) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'profile.php';
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id';
    input.value = elUser;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("post-image")) {

        const src = e.target.getAttribute("src");

        // Set modal image
        document.getElementById("postModalImage").src = src;

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById("postImageModal"));
        modal.show();
    }
});
</script>


</body>
</html>

<?php
// close the DB connection
if ($con) mysqli_close($con);
?>
