<?php 
    require_once(__DIR__ . '/../config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments</title>

    <!-- Styles -->
    <link rel="stylesheet" href="<?= CSS_PATH ?>header.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>comments_page.css">
</head>
<body>

<?php 
    include(INCLUDES_PATH . 'project_header.php'); 
    include(INCLUDES_PATH . 'comments_section.php'); 

?>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script src="<?= JS_PATH ?>comments_page.js"></script>

</body>
</html>
