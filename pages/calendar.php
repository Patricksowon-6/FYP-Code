<?php 
require_once(__DIR__ . '/../config.php');
require_once(HANDLER_PATH . 'tasks.php'); 

$user_id = $_SESSION['user_id'] ?? null;
$taskDeadlines = $user_id ? getTaskDeadlines($user_id) : [];

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Calendar</title>
	<link rel="stylesheet" href="<?= CSS_PATH;?>header.css" />
	<link rel="stylesheet" href="<?= CSS_PATH;?>calendar.css" />
</head>
<body style="margin-top: 100px;">
	<?php include (INCLUDES_PATH . 'logged_in_header.php'); ?>

	<?php include (INCLUDES_PATH . 'calendar_display.php'); ?>

	<script src="<?= JS_PATH; ?>calendar.js"></script>
	<script src="<?= JS_PATH; ?>header.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>

</body>
</html>

