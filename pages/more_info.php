<?php
// Clear project-related session data BEFORE including config.php
$project_keys = ['project_id', 'show_title', 'description', 'banner_img', 'quote_img', 'profile_img'];
for ($i = 1; $i <= 5; $i++) {
    $project_keys[] = "emoji$i";
    $project_keys[] = "genre$i";
    $project_keys[] = "circle_img_$i";
}

foreach ($project_keys as $key) {
    unset($_SESSION[$key]);
}

// Now include config
require_once(__DIR__ . '/../config.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>More About You</title>
    <link rel="stylesheet" href="<?= CSS_PATH; ?>banner.css">
    <link rel="stylesheet" href="<?= CSS_PATH; ?>banner_form.css">
    <link rel="stylesheet" href="<?= CSS_PATH; ?>spinner.css">
</head>
<body id="body">
    <center>
        <h2 class="welcome" style="max-width: 750px;">
            Let's make a banner for your work! Below is a template to follow. Don't worry, it can all be changed later!
        </h2>
    </center>
    

    <div class="cont" style="display: flex; justify-content:center; align-content:center;">
        <?php include INCLUDES_PATH . 'banner_form.php' ?>
        <?php include INCLUDES_PATH . 'empty_banner.php' ?>
    </div>

    <div id="upload-loader" style="display:none;">
        <div class="spinner"></div>
    </div>
    
    <script src="<?= JS_PATH; ?>spinner.js"></script>
    <script src="<?= JS_PATH; ?>banner.js"></script>
</body>
</html>
