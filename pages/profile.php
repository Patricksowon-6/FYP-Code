<?php 
require_once(__DIR__ . '/../config.php'); 

$user_id = $_SESSION['user_id'] ?? null;

// Fetch user info from DB
$stmt = $conn->prepare("SELECT user_name, email, profile_pic FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Profile Page</title>
    <link rel="stylesheet" href="<?= CSS_PATH ?>header.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>profile.css">
</head>
<body>

    <?php
        include(INCLUDES_PATH . 'project_header.php'); 
    ?>
    <br><br><br><br><br><br><br><br>
    <?php
        include(INCLUDES_PATH . 'profile_box.php'); 
    ?>

    <script src="<?= JS_PATH ?>header.js"></script>
    <script src="<?= JS_PATH ?>profile.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>