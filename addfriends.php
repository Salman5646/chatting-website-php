<?php
session_start();
if(!isset($_SESSION['username'])) {
header("Location: login.html");
}
$cur=$_SESSION['username'];
// CHECK IF USER PROFILE HAS ANY PENDING (EMPTY) FIELDS
$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");

$check_sql = "
    SELECT 
        fullname, email, phone, bio, gender, dob, profile_picture
    FROM account
    WHERE username = '$cur'
";

$check_result = mysqli_query($con, $check_sql);
$pending = false;

if ($check_result) {
    $row = mysqli_fetch_assoc($check_result);

    foreach ($row as $field => $value) {
        if ($value === NULL || trim($value) === "") {
            $pending = true;
            break;
        }
    }
}

mysqli_close($con);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add friends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="index.css">
<style>

    .nav-link:hover{
        text-decoration:none;
        transform:none;
        background-color:lightblue;
        color:black;
        border-radius:10px;
    }
    .navbar {
    position: sticky;
    top: 0;
    height: auto !important;
    z-index: 1000;
}
.navbar-brand img
    {
        margin-left:1rem;
        width: 3rem;
        border-radius:50%;
        height: 3rem;
    }
    .flex-hero {
    border: 0.2px solid #ccc;
    margin-bottom: 0.75rem;
    padding: 0.75rem 1rem;
    justify-content: left;
    align-items: center;
    display: flex;
    text-align: center;
    color: black;
    background: white;
    border-radius: 10px;
    box-shadow: 0 1px 6px rgba(0, 0, 0, 0.05);
    transition: background-color 0.2s ease;
}
.flex-hero:hover {
    background-color: #f8f9fa;
}
.tab{
    width:33%;
}

    .tabs{
        
        text-align:center;
        border-bottom:1px solid black;
    }
    .tab-link
    {
color:black;
    }
    .active
    {
        color: blue !important;
        font-weight:bold;
    }
    .req{
        color:white;
        text-decoration:none;
    }
    .small-badge {
    padding: 3px 6px;
    vertical-align: top;
}
    .offcanvas-body{
        overflow-y:auto;
    }
    
.account-tab {
    position: relative;
    display: inline-block;
    margin-right:0.5rem;
}

.profile-badge {
    position: absolute;
    top: -5px;
    right: -10px;
    background: red;
    color: white;
    padding: 2px 6px;
    font-size: 10px;
    border-radius: 50%;
}
    .mobile-account {
    position: relative;
    display: inline-block;
}

.mobile-profile-badge {
    position: absolute;
    top: -4px;
    right: -8px;
    background: red;
    color: white;
    padding: 2px 6px;
    font-size: 10px;
    border-radius: 50%;
}

</style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <img src="whatsapp.png" alt="Logo" width="30" height="24">
    </a>

    <!-- Search input -->
    <input class="form-control me-2" id="txt" type="search" placeholder="Search users here" style="max-width: 50%;">

    <!-- Menu button with icon, next to search -->
    <button class="btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" aria-label="Toggle menu" style="height: 38px;">
      <!-- Bootstrap hamburger icon SVG -->
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-list" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M2.5 12.5a.5.5 0 010-1h11a.5.5 0 010 1h-11zm0-5a.5.5 0 010-1h11a.5.5 0 010 1h-11zm0-5a.5.5 0 010-1h11a.5.5 0 010 1h-11z"/>
      </svg>
    </button>

    <!-- Original toggler hidden -->
    <button class="navbar-toggler d-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <form method="post" action="profile.php">
            <input type="hidden" name="edit" value="none">
            <input type="hidden" name="id" value="<?php echo $cur;?>">
            <div class="account-tab">
    <input class="nav-link" type="submit" value="My Account">

    <?php if ($pending) { ?>
        <span class="profile-badge">!</span>
    <?php } ?>
</div>

          </form>
        </li>
        <li class="nav-item">
    <a class="nav-link tab-link req" href="friendrequests.php">
      Requests
      <?php
        // Show count badge if there are pending friend requests
        $con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
        $cur = $_SESSION['username'];
        $rq_sql = "SELECT COUNT(*) AS cnt FROM friends WHERE user2 = '$cur' AND status = 'pending'";
        $rq_result = mysqli_query($con, $rq_sql);
        $count = 0;
        if ($rq_result) {
            $row = mysqli_fetch_assoc($rq_result);
            $count = $row['cnt'];
        }
        mysqli_close($con);
        if ($count > 0) {
            echo " <span class='badge bg-danger small-badge'>$count</span>";
        }
      ?>
    </a>
  </li>
        <li class="nav-item">
          <a class="nav-link" href="addpost.php">Add post</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Offcanvas menu -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel" style="width: 40%;height:50%">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasRightLabel">Menu</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="navbar-nav">
      <li class="nav-item mb-2 mobile-account">
        <form method="post" action="profile.php" class="d-inline">
          <input type="hidden" name="edit" value="none">
          <input type="hidden" name="id" value="<?php echo $cur;?>">
                    <input class="nav-link" style="color:black" type="submit" value="My Account" style="border: none; cursor: pointer;">
            <?php if ($pending) { ?>
        <span class="mobile-profile-badge">!</span>
    <?php } ?>
        </form>
        
      </li>
      <li class="nav-item mb-2">
    <a class="nav-link" style="color:black" href="friendrequests.php">
      Requests
      <?php
        // Show count badge if there are pending friend requests
        $con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
        $cur = $_SESSION['username'];
        $rq_sql = "SELECT COUNT(*) AS cnt FROM friends WHERE user2 = '$cur' AND status = 'pending'";
        $rq_result = mysqli_query($con, $rq_sql);
        $count = 0;
        if ($rq_result) {
            $row = mysqli_fetch_assoc($rq_result);
            $count = $row['cnt'];
        }
        mysqli_close($con);
        if ($count > 0) {
            echo " <span class='badge bg-danger small-badge'>$count</span>";
        }
      ?>
    </a>
  </li>
      <li class="nav-item mb-2">
        <a class="nav-link" style="color:black" href="addpost.php">Add post</a>
      </li>
      <li class="nav-item mb-2">
        <a class="nav-link" style="color:black" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
      </li>
    </ul>
  </div>
</div>



<section class="hero py-4">
    </li>
    <div class="container">
        <div class="mx-auto" style="max-width: 720px;">

  <ul class="nav nav-tabs" >
  <li class="nav-item tabs tab">
    <a class="nav-link tab-link active" aria-current="page" href="addfriends.php" data-bs-toggle="tooltip" data-bs-placement="top" title="Users">
      <i class="bi bi-people"></i>
    </a>
  </li>
  <li class="nav-item tabs tab">
    <a class="nav-link tab-link" href="index.php" data-bs-toggle="tooltip" data-bs-placement="top" title="Chats">
      <i class="bi bi-chat-dots"></i>
    </a>
  </li>
  <li class="nav-item tabs tab">
    <a class="nav-link tab-link" href="viewposts.php" data-bs-toggle="tooltip" data-bs-placement="top" title="Posts">
      <i class="bi bi-card-image"></i>
    </a>
  </li>
</ul>

        <?php
        $con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
        $sql="
SELECT * FROM account 
WHERE username != '$cur'
AND username NOT IN (
    SELECT CASE 
             WHEN user1 = '$cur' THEN user2 
             ELSE user1 
           END 
    FROM friends 
    WHERE (user1 = '$cur' OR user2 = '$cur') 
      AND status = 'accepted'
)
ORDER BY rand()

";
        $result = mysqli_query($con, $sql);
        if(mysqli_num_rows($result)>0)
        {
            while($row=mysqli_fetch_assoc($result)){
                echo '<div class="flex-hero searchable" onclick="openProfile(\'' . $row["username"] . '\')">';
                echo '<a class="navbar-brand" href="#"><img src="'.$row["profile_picture"].'" alt="Logo" width="30" height="24"></a>';
                echo '<div class="hero-content">';
                echo '<h4 id="' . $row["username"] . '">' . $row["fullname"] . '</h4>';
                echo '</div>';
                echo '</div>';
            }
            
}
else {
    echo "No users found";
}
            ?>
            
            </div>
            </div>
    </section>

      
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to logout?
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button onclick="window.open('logout.php','_self')" class="btn btn-primary">Yes</button>
            </div>
        </div>
    </div>
</div>

     <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
        <script>
           
function openProfile(elId) {
    const userId = elId;

    // Create a hidden form dynamically
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'profile.php';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'id';
    input.value = userId;


    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}


 document.getElementById('txt').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const divs = document.querySelectorAll('.searchable');

            divs.forEach(div => {
                const name = div.querySelector('h4').textContent.toLowerCase();

                if (name.includes(query)) {
                    div.style.display = 'flex';
                } else {
                    div.style.display = 'none';
                }
            });
        });
        </script>



</body>

</html>
