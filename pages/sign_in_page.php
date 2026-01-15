<?php
    require_once(__DIR__ . '/../config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="<?= CSS_PATH; ?>sign_in_page.css">
    <link rel="stylesheet" href="<?= CSS_PATH; ?>header.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
	<?php
        include (INCLUDES_PATH . 'home_header.php'); 
        include (INCLUDES_PATH . 'sign_in_form.php');
    ?>

    <script src="<?= JS_PATH; ?>sign_in_page.js"></script>
    <script src="<?= JS_PATH; ?>header.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>

</body>
</html>

