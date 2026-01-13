<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$curUser = $_SESSION['username'];

$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
if (!$con) {
    die("DB connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['cont'] ?? '');

    if (empty($title) || empty($content) || !isset($_FILES['img'])) {
        $error = "Please provide all required fields.";
    } else {
        $uploadDir = __DIR__ . '/posts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $image = $_FILES['img'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image['type'], $allowedTypes)) {
            $error = "Only JPG, PNG, and GIF images are allowed.";
        } elseif ($image['error'] !== UPLOAD_ERR_OK) {
            $error = "Error uploading image.";
        } elseif ($image['size'] > 5 * 1024 * 1024) {
            $error = "Image size must not exceed 5MB.";
        } else {
            $ext = pathinfo($image['name'], PATHINFO_EXTENSION);
            $filename = 'post_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $destination = $uploadDir . $filename;

            if (!move_uploaded_file($image['tmp_name'], $destination)) {
                $error = "Failed to move uploaded file.";
            } else {
                $titleEsc = mysqli_real_escape_string($con, $title);
                $contentEsc = mysqli_real_escape_string($con, $content);
                $imagePath = 'posts/' . $filename;
                date_default_timezone_set('Asia/Kolkata');
                $createdAt = date('Y-m-d H:i:s');  // Gives IST time

                $sql = "INSERT INTO posts (`user`, `title`, `content`, `image`, `time`) VALUES ('$curUser', '$titleEsc', '$contentEsc', '$imagePath','$createdAt');";
                if (mysqli_query($con, $sql)) {
                    header("Location: index.php?post=success");
                    exit();
                } else {
                    $error = "Error saving post: " . mysqli_error($con);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Post</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/x-icon" href="/tablogo.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <style>
    body {
  background: linear-gradient(135deg, #e0f7fa, #fce4ec);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

    .login-container { 
  margin-top: 5%;
  background: white;
  border-radius: 12px;
  padding: 10px;
}
.form-text {
  color: #d32f2f; /* Material red */
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

.error {
  border-color: #d32f2f !important;
}

 
    .password-toggle { background-color: white; border: none; position: absolute; top: 50%; right: 10px; transform: translateY(-50%); cursor: pointer; }
    .spinner { display: none; margin-left: 10px; }
    .spinner.active { display: inline-block; }
  </style>
</head>
<body>
<a href="index.php" class="btn btn-dark" style="color:black;position: fixed; top: 10px; left: 10px; z-index: 1050;background-color:transparent;border:none">
  <i class="fas fa-arrow-left"></i>
</a>

<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-6 col-sm-8 col-10 bg-white p-4 mt-5 rounded">
      <div class="login-container">
        <div class="text-center mb-4"><h2>Add a new post</h2></div>

        <?php if (!empty($error)): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form enctype="multipart/form-data" method="post">
          <div class="form-group">
            <label for="title">Add title</label>
            <input type="text" class="form-control" id="title" name="title" required>
          </div>
          <div class="form-group">
            <label for="file">Choose image</label>
            <input type="file" id="file" name="img" class="form-control" accept="image/*" required>
          </div>
          <div class="form-group">
            <label for="content">Add Caption</label>
            <textarea class="form-control" id="content" name="cont" required></textarea>
          </div>
          <button type="button" class="btn btn-secondary btn-block" onclick="generateDescriptionWithImage(event)">
            AI Description <span class="spinner fa fa-spinner fa-spin"></span>
          </button>

          <div id="postPreview" class="post-item d-flex flex-column p-3 border mt-3 rounded" style="display: none; background-color: #f9f9f9;">
            <img id="previewImage" src="#" alt="Preview Image" class="mb-2 post-image" style="width: 100%; border-radius: 8px;" onerror="this.src='default.jpg'">
            <div>
              <h5 id="previewTitle">Title will appear here</h5>
              <p id="previewCaption" class="text-muted">Caption will appear here</p>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block mt-3">Add Post</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- JS Includes -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById("file");
    const titleInput = document.getElementById("title");
    const contentInput = document.getElementById("content");
    const previewImage = document.getElementById("previewImage");
    const previewTitle = document.getElementById("previewTitle");
    const previewCaption = document.getElementById("previewCaption");
    const postPreview = document.getElementById("postPreview");

    fileInput.addEventListener("change", function(event) {
      const file = event.target.files[0];
      if (file && file.type.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImage.src = e.target.result;
          postPreview.style.display = "block";
        };
        reader.readAsDataURL(file);
      } else {
        previewImage.src = "default.jpg";
        postPreview.style.display = "block";
      }
    });

    titleInput.addEventListener("input", function() {
      previewTitle.textContent = this.value.trim() || "Title will appear here";
      postPreview.style.display = "block";
    });

    contentInput.addEventListener("input", function() {
      previewCaption.textContent = this.value.trim() || "Caption will appear here";
      postPreview.style.display = "block";
    });
  });

  function generateDescriptionWithImage(event) {
    const title = document.getElementById("title").value.trim();
    const file = document.getElementById("file").files[0];
    const btn = event.target;

    if (!title || !file) {
      alert("Please provide both a title and an image.");
      return;
    }

    btn.disabled = true;
    btn.querySelector('.spinner').classList.add('active');

    const reader = new FileReader();
    reader.onload = () => {
      const base64 = reader.result.split(",")[1];
      fetch("generate-description.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `title=${encodeURIComponent(title)}&image=${encodeURIComponent(base64)}&mimeType=${encodeURIComponent(file.type)}`
      })
      .then(response => {
        if (!response.ok) throw new Error(`Server error: ${response.status}`);
        return response.text();
      })
      .then(text => {
        if (text.startsWith("Error:")) {
          alert(text);
        } else {
          document.getElementById("content").value = text;
          document.getElementById("previewCaption").textContent = text || "Caption will appear here";
          document.getElementById("postPreview").style.display = "block";
        }
      })
      .catch(err => alert(`Failed to generate description: ${err.message}`))
      .finally(() => {
        btn.disabled = false;
        btn.querySelector('.spinner').classList.remove('active');
        btn.firstChild.nodeValue = "AI Description ";
      });
    };
    reader.readAsDataURL(file);
  }
</script>
</body>
</html>
