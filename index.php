<?php require_once __DIR__ . '/config.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="<?= CSS_PATH; ?>header.css">
    <link rel="stylesheet" href="<?= CSS_PATH; ?>drag_slider.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<?php 
    include(INCLUDES_PATH . 'home_header.php');
    include(INCLUDES_PATH . 'drag_slider.php');
?>

</html>