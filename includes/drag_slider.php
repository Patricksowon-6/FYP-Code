<?php
    require_once(__DIR__ . '/../config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="<?= CSS_PATH; ?>drag_slider.css">
</head>
<body>

    <div id="image-track" data-mouse-down-at="0" data-prev-percentage="0">
        <img class="image" src="<?= IMG_PATH; ?>2.jpeg" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>1.jpeg" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>3.jpeg" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>4.jpeg" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>" draggable="false" />
        <img class="image" src="<?= IMG_PATH; ?>" draggable="false" />
    </div>

    <script src="<?= JS_PATH; ?>drag_slider.js"></script>
    
</body>
</html>