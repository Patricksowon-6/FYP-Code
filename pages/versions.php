<?php require_once __DIR__ . '/../config.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Versions</title>
    <link rel="stylesheet" href="<?= CSS_PATH ?>versions.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>header.css">
</head>
<body>

    <?php 
        include (INCLUDES_PATH . 'project_header.php'); ?>

    <div style="margin-top: 100px;"></div>
    <?php
        include (INCLUDES_PATH . 'version_list.php'); 
    ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>
