<?php
    require_once(__DIR__ . '/../config.php');

    $project_id = $_GET['project_id'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="<?= CSS_PATH; ?>character_slider.css">
    <link rel="stylesheet" href="<?= CSS_PATH; ?>header.css">

    <script>
        window.PROJECT_ID = <?= intval($_GET['project_id']) ?>;
    </script>
</head>
<body>
    
    <?php
        include(INCLUDES_PATH . 'project_header.php'); 
        include(INCLUDES_PATH . 'character_slider.php'); 
    ?>

    <script src="<?= JS_PATH; ?>character_slider.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>

</body>
</html>
