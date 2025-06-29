<?php 
function safe($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm'])) {
    
    $bgColor = $_POST['bg-color'] ?? '#ADD8E6';

    setcookie("user_bg_color", $bgColor, time() + 3600, "/");

   
    $conn = new mysqli("localhost", "root", "", "database");
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

  
    $fullName  = $conn->real_escape_string($_POST['full-name']);
    $email     = $conn->real_escape_string($_POST['email']);
    $password  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birthDate = $conn->real_escape_string($_POST['birth-date']);
    $gender    = $conn->real_escape_string($_POST['gender']);
    $country   = $conn->real_escape_string($_POST['country']);

    
    $sql = "INSERT INTO users (full_name, email, password, birth_date, gender, country, bg_color) 
            VALUES ('$fullName', '$email', '$password', '$birthDate', '$gender', '$country', '$bgColor')";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.html");
        exit;
    } else {
        echo "Database Insert Error: " . $conn->error;
    }

    $conn->close();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['confirm'])) {
    $fullName  = $_POST['full-name']  ?? '';
    $email     = $_POST['email']      ?? '';
    $password  = $_POST['password']   ?? '';
    $birthDate = $_POST['birth-date'] ?? '';
    $gender    = $_POST['gender']     ?? '';
    $country   = $_POST['country']    ?? '';
    $bgColor   = $_POST['bg-color']   ?? '#ADD8E6';
    $terms     = isset($_POST['terms']) ? 'Agreed' : 'Not Agreed';

    if (!$fullName || !$email || !$password || !$birthDate || !$gender || !$country) {
        echo "<h2>Invalid submission. Please <a href='index.html'>go back</a> and fill all fields correctly.</h2>";
        exit;
    }
} else {
    header("Location: index.html");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Registration Info</title>
  <style>
    body { background-color: <?php echo safe($bgColor); ?>; font-family: Arial, sans-serif; padding: 20px; }
    .info-box { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 8px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
    h2 { text-align: center; }
    dl { font-size: 16px; }
    dt { font-weight: bold; margin-top: 10px; }
    dd { margin-left: 20px; margin-bottom: 10px; }
    form { text-align: center; margin-top: 20px; }
    button { padding: 10px 20px; font-size: 16px; margin: 0 10px; cursor: pointer; border: none; border-radius: 5px; }
    button.confirm { background-color: #4CAF50; color: white; }
    button.cancel  { background-color: #f44336; color: white; }
  </style>
</head>
<body>
  <div class="info-box">
    <h2>Please Confirm Your Information</h2>
    <dl>
      <dt>Full Name:</dt><dd><?php echo safe($fullName); ?></dd>
      <dt>Email:</dt><dd><?php echo safe($email); ?></dd>
      <dt>Password:</dt><dd><?php echo str_repeat('*', strlen($password)); ?></dd>
      <dt>Birth Date:</dt><dd><?php echo safe($birthDate); ?></dd>
      <dt>Gender:</dt><dd><?php echo safe($gender); ?></dd>
      <dt>Country:</dt><dd><?php echo safe($country); ?></dd>
      <dt>Background Color:</dt>
          <dd><span style="display:inline-block;width:20px;height:20px;background-color:<?php echo safe($bgColor); ?>;border:1px solid #000;"></span> <?php echo safe($bgColor); ?></dd>
      <dt>Terms Accepted:</dt><dd><?php echo safe($terms); ?></dd>
    </dl>
    <form action="process.php" method="post">
      <input type="hidden" name="full-name"  value="<?php echo safe($fullName); ?>" />
      <input type="hidden" name="email"      value="<?php echo safe($email); ?>" />
      <input type="hidden" name="password"   value="<?php echo safe($password); ?>" />
      <input type="hidden" name="birth-date" value="<?php echo safe($birthDate); ?>" />
      <input type="hidden" name="gender"     value="<?php echo safe($gender); ?>" />
      <input type="hidden" name="country"    value="<?php echo safe($country); ?>" />
      <input type="hidden" name="bg-color"   value="<?php echo safe($bgColor); ?>" />
      <input type="hidden" name="terms"      value="<?php echo safe($terms); ?>" />
      <button type="submit" name="confirm" class="confirm">Confirm</button>
      <button type="button" onclick="window.close()" class="cancel">Cancel</button>
    </form>
  </div>
</body>
</html>
