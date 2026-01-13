<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.html");
    exit();
}

$con = mysqli_connect("sql209.byethost17.com", "b17_40616871", "Salman@56", "b17_40616871_main");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

$cur = $_SESSION['username'];

$query = "SELECT account.username, account.fullname, account.profile_picture 
          FROM friends 
          JOIN account ON friends.user1 = account.username 
          WHERE friends.user2 = '$cur' AND friends.status = 'pending'";

$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <title>Friend Requests</title>
  <link rel="stylesheet" 
        href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <script>
  function openProfile(elId) {
      const form = document.createElement('form');
      form.method = 'POST';
      form.action = 'profile.php';

      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'id';
      input.value = elId;

      form.appendChild(input);
      document.body.appendChild(form);
      form.submit();
  }
  </script>
</head>
<body class="bg-dark text-white">
  <div class="container mt-4">

    <a href="index.php" class="btn btn-light mb-3">
      Back to Home
    </a>

    <h3>Pending Friend Requests</h3>
    <hr>

    <?php
    if (mysqli_num_rows($result) === 0) {
        echo "<p>No pending requests.</p>";
    } else {
        while ($row = mysqli_fetch_assoc($result)) {

            $pic = $row["profile_picture"] ?: "default.jpg"; // fallback

            echo '<div class="card mb-3 bg-light text-dark">';
            echo '  <div class="card-body d-flex align-items-center justify-content-between">';
            echo '    <div class="d-flex align-items-center" style="cursor:pointer;" onclick="openProfile(\'' . $row["username"] . '\')">';
            echo '      <img src="' . htmlspecialchars($pic) . '" class="rounded-circle mr-3" width="50" height="50">';
            echo '      <h5 class="mb-0">' . htmlspecialchars($row["fullname"]) . '</h5>';
            echo '    </div>';
            echo '    <div>';
            echo '      <form method="POST" action="handle_request.php" style="display:inline-block;">';
            echo '        <input type="hidden" name="fromUser" value="' . htmlspecialchars($row["username"]) . '">';
            echo '        <input type="hidden" name="action" value="accept">';
            echo '        <button type="submit" class="btn btn-success btn-sm">Accept</button>';
            echo '      </form>';
            echo '      <form method="POST" action="handle_request.php" style="display:inline-block; margin-left: 5px;">';
            echo '        <input type="hidden" name="fromUser" value="' . htmlspecialchars($row["username"]) . '">';
            echo '        <input type="hidden" name="action" value="reject">';
            echo '        <button type="submit" class="btn btn-danger btn-sm">Reject</button>';
            echo '      </form>';
            echo '    </div>';
            echo '  </div>';
            echo '</div>';

        }
    }

    mysqli_close($con);
    ?>
  </div>
</body>
</html>
