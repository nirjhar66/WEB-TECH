<?php
session_start();


if (!isset($_SESSION['user_name'])) {
    header("Location: index.html");
    exit;
}


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


if (!isset($_POST['cities'])) {
    header("Location: check.php");
    exit;
}

$selectedCities = $_POST['cities'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>AQI Results</title>
  <style>
    body { background-color: <?= $bgColor ?>; font-family: Arial, sans-serif; padding: 30px; }
    table { margin-top:30px; border-collapse:collapse; width:600px; background-color: <?= $bgColor ?>; }
    th,td { border:1px solid #aaa; padding:8px; text-align:left; }
    th    { background-color:#d6eaf8; }
    button { padding:8px 16px; font-size:14px; cursor:pointer; background:#f44336; color:#fff; border:none; border-radius:4px; }
    button:hover { background:#c62828; }
  </style>
</head>
<body>
  
  <div style="position:absolute; top:10px; left:20px; font-weight:bold;">
    <?= htmlspecialchars($_SESSION['user_name']) ?>
  </div>

  
  <div style="position:absolute; top:10px; right:20px;">
    <form action="showaqi.php" method="get" style="display:inline;">
      <button type="submit" name="logout" value="1">Logout</button>
    </form>
  </div>

  <h2>Selected Cities AQI Data</h2>

<?php
$conn = new mysqli("localhost", "root", "", "database");
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($conn->connect_error) { die("<br><strong>Connection failed:</strong> " . $conn->connect_error); }

$placeholders = implode(',', array_fill(0, count($selectedCities), '?'));
$stmt = $conn->prepare("SELECT city, country, aqi FROM aqi WHERE city IN ($placeholders)");
$types = str_repeat('s', count($selectedCities));
$stmt->bind_param($types, ...$selectedCities);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<table id='aqi-table'><tr><th>City</th><th>Country</th><th>AQI</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>".htmlspecialchars($row['city'])."</td><td>".htmlspecialchars($row['country'])."</td><td>".htmlspecialchars($row['aqi'])."</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p>No data found for the selected cities.</p>";
}
$stmt->close();
$conn->close();
?>
</body>
</html>
