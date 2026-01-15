<?php 
    require_once(__DIR__ . '/../config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="<?= CSS_PATH ?>header.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>dashboard.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>projects.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>main_carousel.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>footer.css">
</head>
<body>

<?php 
    include(INCLUDES_PATH . 'logged_in_header.php'); 
    include(INCLUDES_PATH . 'dashboard.php'); 
?>

<div style="margin-top: 100px;"></div>

<?php
    include(INCLUDES_PATH . 'recents.php'); 
?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</html>
</body>
