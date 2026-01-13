<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}
$cur = $_SESSION["username"];
$target = $_POST["user"] ?? $_POST["target"] ?? $_GET["user"] ?? null;

if (!$target) {
    echo "<form id='redirectForm' method='post' action='index.php'>
        <input type='hidden' name='error' value='missing_user'>
      </form>
      <script>document.getElementById('redirectForm').submit();</script>";
    exit();
}


$no = "";
$msg = 0;

$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");

// Count messages
$query = "SELECT COUNT(*) AS message_count FROM message WHERE ((`user`='$target' AND `from`='$cur') OR (`user`='$cur' AND `from`='$target'));";
$result = mysqli_query($con, $query);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    if ($row["message_count"] > 0) {
        $msg = 1;
    }
}

// Clear chat
if (isset($_POST["clear"])) {
    // First, select all messages to find images
$imgQuery = "SELECT content, type FROM message WHERE ((`user`='$target' AND `from`='$cur') OR (`user`='$cur' AND `from`='$target')) AND `type`='image'";
$imgResult = mysqli_query($con, $imgQuery);

// Delete image files from server
if ($imgResult && mysqli_num_rows($imgResult) > 0) {
    while ($img = mysqli_fetch_assoc($imgResult)) {
        $imagePath = $img["content"];
        if (file_exists($imagePath)) {
            unlink($imagePath); // Delete the image
        }
    }
}

// Now delete the messages
$delQuery = "DELETE FROM message WHERE (`user`='$target' AND `from`='$cur') OR (`user`='$cur' AND `from`='$target');";
$delResult = mysqli_query($con, $delQuery);
if ($delResult) {
    $msg = 0;
    echo "<form id='redirectForm' method='post' action='message.php'>
        <input type='hidden' name='user' value='$target'>
      </form>
      <script>document.getElementById('redirectForm').submit();</script>";
exit();

    
} else {
    echo "<form id='redirectForm' method='post' action='message.php'>
        <input type='hidden' name='user' value='$target'>
      </form>
      <script>document.getElementById('redirectForm').submit();</script>";
exit();

}


}
?>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="index.css">
    <style>
.navbar-brand {
  margin-left: 30px; /* or adjust px as needed */
  display: flex;
  align-items: center;
  gap: 0.2rem;
}
a.btn-sm {
  font-size: 0.75rem;
  padding: 3px 6px;
}

        .name {
            display: inline-block;
        }
        .username {
            font-size: 14px;
            margin-left: 3.2rem;
        }
        .nav-link:hover {
            text-decoration: none;
            background-color: lightblue;
            color: black;
            border-radius: 10px;
        }
        .navbar {
            position: sticky;
            top: 0;
            margin-bottom: 1%;
            z-index: 1000;
        }
        .navbar-brand img {
            margin-right: 0.5rem;
            width: 2.5rem;
            border-radius: 50%;
        }
        .msgs {
            padding: 0.5rem;
            box-shadow: 5px 5px 5px #888;
            border-radius: 0.5rem;
            display: inline-block;
            max-width: 25%;
            clear: both;
            margin-bottom: 5px;
        }
        .sent {
            background-color: lightgreen;
            float: right;
            margin-right: 10%;
        }
        .received {
            background-color: yellow;
            float: left;
            margin-left: 10%;
        }
        .msg-container {
            overflow-y: scroll;
            padding-bottom: 80px;
        }
        .time {
            font-size: 10px;
        }
        .msg-form {
            position: fixed;
            bottom: 0;
            width: 100%;
            background-color: white;
            padding: 10px;
            box-shadow: 0px -2px 4px rgba(0, 0, 0, 0.1);
        }
        @media screen and (max-width: 768px) {
    .msgs {
        max-width: 70%; /* Wider bubbles */
    }

    .sent {
        margin-right: 3%;
    }

    .received {
        margin-left: 3%;
    }
}

.offcanvas-body .nav-item {
    margin-bottom: 15px;
}

.offcanvas-body .nav-link {
    font-size: 1rem;
    color: black;
}

    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script>
        function scr() {
            window.scrollTo(0, document.body.scrollHeight);
            document.getElementById("message").focus();
        }
        function send(form, event) {
            event.preventDefault();
            form.submit();
            form.reset();
        }
    </script>
</head>
<body onload="scr()">

<!-- Modal for Clear Chat -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clear entire chat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to clear this chat? This process is irreversible.
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <form method="post">
    <input type="hidden" name="user" value="<?php echo htmlspecialchars($target); ?>">
    <button type="submit" name="clear" class="btn btn-primary">Yes</button>
</form>

            </div>
        </div>
    </div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <?php
    $sql = "SELECT * FROM account WHERE username='$target';";
    $result = mysqli_query($con, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        echo '<a href="index.php" 
   style="position: fixed; top: 2%; left: 2%; z-index: 1050; font-size: 1.2rem; color: black; padding: 8px 12px; border-radius: 5px; text-decoration: none; display: flex; align-items: center; gap: 6px;">
  <i class="fas fa-arrow-left"></i>
</a>
';
        echo '<a class="navbar-brand" href="#">';
        echo '<img src="' . $row["profile_picture"] . '" alt="Logo">';
        echo '<span class="name">' . $row["fullname"] . '</span>';
        $no = $row["phone"];
        echo '</a>';
    } else {
        echo "No matching records found.";
    }
    ?>

    <!-- Main nav items for large screens -->
    <div class="collapse navbar-collapse justify-content-end d-none d-lg-flex" id="navbarNav">
      <ul class="navbar-nav">
        <?php if ($msg == 1): ?>
        <li class="nav-item">
          <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Clear Chat</a>
        </li>
        <?php endif; ?>
        <li class="nav-item">
          <form method="post" action="profile.php" class="d-inline">
            <input type="hidden" name="id" value="<?php echo $target; ?>">
            <input class="nav-link" type="submit" value="Profile" style="cursor:pointer; background:none; border:none;">
          </form>
        </li>
       <?php if (!empty($no)): ?>
<li class="nav-item">
  <a class="nav-link" href="tel:<?php echo htmlspecialchars($no); ?>">Call Now</a>
</li>
<?php endif; ?>

      </ul>
    </div>

    <!-- Hamburger button for small screens -->
    <button class="btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight" aria-label="Toggle menu" style="height: 38px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-list" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M2.5 12.5a.5.5 0 010-1h11a.5.5 0 010 1h-11zm0-5a.5.5 0 010-1h11a.5.5 0 010 1h-11zm0-5a.5.5 0 010-1h11a.5.5 0 010 1h-11z"/>
      </svg>
    </button>
  </div>
</nav>

<!-- Offcanvas menu -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel" style="width: 40%; height: fit-content;">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasRightLabel">Menu</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <ul class="navbar-nav">
      <?php if ($msg == 1): ?>
      <li class="nav-item mt-3">
        <a class="nav-link" style="color:black" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">Clear Chat</a>
      </li>
      <?php endif; ?>

      <li class="nav-item">
        <form method="post" action="profile.php" class="d-inline">
            <input type="hidden" name="id" value="<?php echo $target; ?>">
            <input class="nav-link" style="color:black" type="submit" value="Profile">
        </form>
      </li>
      <?php if (!empty($no)): ?>
<li class="nav-item">
  <a class="nav-link" href="tel:<?php echo htmlspecialchars($no); ?>">Call Now</a>
</li>
<?php endif; ?>
    </ul>
  </div>
</div>

<!-- Message Container -->
<div class="msg-container">
    <?php
    $query = "SELECT * FROM message WHERE (`user`='$target' AND `from`='$cur') OR (`user`='$cur' AND `from`='$target') ORDER BY time;";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $isSent = $row["from"] == $cur;
            $class = $isSent ? "sent" : "received";
           echo '<p class="msgs ' . $class . '">';
if ($row["type"] === "image") {
    echo '<img src="' . $row["content"] . '" alt="image" style="max-width:100%; border-radius: 8px;" class="clickable-image" data-bs-toggle="modal" data-bs-target="#imageModal"><br>';

} else {
    echo htmlspecialchars($row["content"]) . '<br>';
}
echo '[<span class="time">' . $row["time"] . '</span>]</p>';

        }
    }
    ?>
</div>

<!-- Message Form -->
<div class="msg-form">
    <center>
<div id="imagePreviewContainer" style="display:none; margin-bottom: 10px;">
  <img id="imagePreview" src="#" alt="Image Preview" style="max-width: 100px; border-radius: 8px;" />
  <button type="button" onclick="removeImage()" class="btn btn-sm btn-danger ml-2">Remove</button>
</div>
 <form action="sendmsg.php" method="post" enctype="multipart/form-data" onsubmit="send(this, event)" class="d-flex align-items-center justify-content-center gap-2">
    <!-- Message input -->
    <input type="text" id="message" name="msg" class="form-control" style="width:50%" placeholder="Enter a message">

    <!-- Image Upload -->
    <input type="file" name="image" id="imageUpload" accept="image/*" hidden>
    <label for="imageUpload" class="btn btn-outline-secondary mb-0" id="imageLabel" title="Attach image">
        <i class="bi bi-camera"></i>
    </label>

    <!-- Hidden Target -->
    <input type="hidden" name="target" value="<?php echo $target; ?>">

    <!-- Submit -->
    <button type="submit" class="btn btn-primary">Send</button>
</form>
    </center>
</div>


<!-- Modal for Image Zoom -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <img id="modalImage" src="" alt="Zoomed Image" class="img-fluid">
      </div>
      <div class="modal-footer">
      <a id="downloadBtn" class="btn btn-success" href="#" download>
          <i class="bi bi-download"></i> Download
        </a>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const msgInput = document.getElementById('message');
const imageInput = document.getElementById('imageUpload');

// Disable message input when image is selected
imageInput.addEventListener('change', function () {
    if (imageInput.files.length > 0) {
        msgInput.disabled = true;
    } else {
        msgInput.disabled = false;
    }
});

// Disable image input when typing
msgInput.addEventListener('input', function () {
    if (msgInput.value.trim() !== '') {
        imageInput.disabled = true;
        document.getElementById('imageLabel').classList.add('disabled');
    } else {
        imageInput.disabled = false;
        document.getElementById('imageLabel').classList.remove('disabled');
    }
});
</script>
<script>
const currentUser = "<?php echo addslashes($cur); ?>";
const targetUser = "<?php echo addslashes($target); ?>";

function fetchMessages() {
    fetch('fetch_messages.php?user=' + encodeURIComponent(targetUser))
        .then(response => response.json())
        .then(data => {
            const container = document.querySelector('.msg-container');
            container.innerHTML = '';  // Clear current messages

            data.forEach(msg => {
                const p = document.createElement('p');
                const isSent = msg.from === currentUser;
                p.className = 'msgs ' + (isSent ? 'sent' : 'received');

                if (msg.type === 'image') {
                    const img = document.createElement('img');
                    img.src = msg.content;
                    img.style.maxWidth = '100%';
                    img.style.borderRadius = '8px';
                    img.className = 'clickable-image';
                    p.appendChild(img);
                    p.appendChild(document.createElement('br'));
                } else {
                    p.textContent = msg.content + ' ';
                }

                const timeSpan = document.createElement('span');
                timeSpan.className = 'time';
                timeSpan.textContent = '[' + msg.time + ']';
                p.appendChild(timeSpan);

                container.appendChild(p);
            });

            // Scroll to bottom after loading messages
            container.scrollTop = container.scrollHeight;
        })
        .catch(err => {
            console.error("Error fetching messages:", err);
        });
}

// Fetch messages every 3 seconds (3000ms)
setInterval(fetchMessages, 1500);

// Initial fetch
fetchMessages();
document.getElementById('imageUpload').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    const container = document.getElementById('imagePreviewContainer');

    if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = "block";
        };
        reader.readAsDataURL(file);
    } else {
        container.style.display = "none";
        preview.src = "#";
    }
});

function removeImage() {
    document.getElementById('imageUpload').value = '';
    document.getElementById('imagePreviewContainer').style.display = 'none';
    document.getElementById('imagePreview').src = '#';
    document.getElementById('message').disabled = false;
    document.getElementById('imageUpload').disabled = false;
}
document.querySelector('.msg-container').addEventListener('click', function (e) {
    if (e.target && e.target.classList.contains('clickable-image')) {
        const imageSrc = e.target.getAttribute('src');

        // Set modal image
        document.getElementById('modalImage').src = imageSrc;

        // Force browser to treat it as a downloadable image
        const downloadBtn = document.getElementById('downloadBtn');
        downloadBtn.href = imageSrc;

        // Extract filename from path (fallback if needed)
        const filename = imageSrc.split('/').pop().split('?')[0]; 
        downloadBtn.setAttribute('download', filename);

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('imageModal'));
        modal.show();
    }
});



</script>

</body>
</html>
