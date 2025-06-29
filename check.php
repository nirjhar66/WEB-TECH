<?php 
session_start();


$bgColor = '#e6f2ff';
if (isset($_COOKIE['user_bg_color'])) {
    $bgColor = htmlspecialchars($_COOKIE['user_bg_color']);
}


if (isset($_GET['logout'])) {
    setcookie('user_bg_color', '', time() - 3600, '/');
    session_destroy();
    header("Location: index.html");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $conn = new mysqli("localhost", "root", "", "database");
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT full_name, password, bg_color FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_name'] = $user['full_name'];
            $colour  = $user['bg_color'] ?? '#e6f2ff';
            setcookie('user_bg_color', $colour, time() + 3600, '/');
            $bgColor = htmlspecialchars($colour);
        } else {
            die("Incorrect email or password. <a href='index.html'>Try again</a>");
        }
    } else {
        die("Incorrect email or password. <a href='index.html'>Try again</a>");
    }
    $stmt->close();
    $conn->close();
}


$cities = ["Los Angeles","Delhi","Beijing","São Paulo","Berlin","Sydney","Toronto","Paris","Tokyo","London",
           "Seoul","Moscow","Mexico City","Rome","Johannesburg","Jakarta","Lahore","Cairo","Dhaka","Istanbul"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Select Cities</title>
  <style>
    body { background-color: <?= $bgColor ?>; font-family: Arial, sans-serif; padding: 30px; }
    .cities-container { max-height:400px; overflow-y:auto; border:1px solid #ccc; padding:10px; width:350px; background:#fff; box-sizing:border-box; }
    label { display:block; margin-bottom:6px; font-size:14px; cursor:pointer; }
    .error { color:red; display:none; margin-top:10px; }
    button:disabled { background:#ccc; cursor:not-allowed; }
    button { margin-top:15px; padding:10px 20px; font-size:16px; cursor:pointer; background-color:#006a4e; color:white; border:none; border-radius:4px; }
    button:hover:enabled { background-color:#004d3e; }
  </style>
</head>
<body>
<?php if (isset($_SESSION['user_name'])): ?>
  <div style="position:absolute; top:10px; left:20px; font-weight:bold;">
    Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> |
    <a href="check.php?logout=1" style="color:red; text-decoration:none;">Logout</a>
  </div>
<?php endif; ?>

  <h2>Select up to 10 cities</h2>

  
  <form method="post" action="showaqi.php" id="cities-form">
    <div class="cities-container" id="cities-container">
      <?php foreach ($cities as $city): ?>
        <label>
          <input type="checkbox" name="cities[]" value="<?= htmlspecialchars($city) ?>" />
          <?= htmlspecialchars($city) ?>
        </label>
      <?php endforeach; ?>
    </div>
    <span class="error" id="select-error">Please select between 1 and 10 cities.</span><br>
    <button type="submit" id="submit-btn" disabled>Submit</button>
  </form>

  <script>
    const checkboxes = document.querySelectorAll('input[name="cities[]"]');
    const submitBtn  = document.getElementById('submit-btn');
    const errorMsg   = document.getElementById('select-error');

    function updateSubmitButton() {
      const c = Array.from(checkboxes).filter(cb => cb.checked).length;
      submitBtn.disabled = !(c >= 1 && c <= 10);
      errorMsg.style.display = submitBtn.disabled ? 'inline' : 'none';
    }
    checkboxes.forEach(cb => cb.addEventListener('change', () => {
      const c = Array.from(checkboxes).filter(x => x.checked).length;
      if (c > 10) { cb.checked = false; alert('You can select up to 10 cities only.'); }
      updateSubmitButton();
    }));
    updateSubmitButton();
  </script>
</body>
</html>
