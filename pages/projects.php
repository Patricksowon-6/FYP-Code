<?php 
    require_once(__DIR__ . '/../config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Project Creator</title>
    <link rel="stylesheet" href="<?= CSS_PATH ?>header.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>projects.css">
</head>
<body>

<?php 
    include(INCLUDES_PATH . 'logged_in_header.php'); 
    include(INCLUDES_PATH . 'project_rows.php'); 
?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>
