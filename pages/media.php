<?php 
require_once(__DIR__ . '/../config.php');

$user_id = $_SESSION['user_id'] ?? 0;
$project_id = $_SESSION['project_id'];

if (!$user_id) die("User not logged in.");

if (!$project_id) {
    die("No project selected.");
}

$type = $_GET['type'] ?? 'images';

$stmt = $conn->prepare("
    SELECT p.project_id, pb.show_title, pb.description, pb.banner_img
    FROM projects p
    LEFT JOIN project_banner pb 
        ON pb.banner_id = (
            SELECT banner_id 
            FROM project_banner 
            WHERE project_id = p.project_id 
            ORDER BY created_at DESC 
            LIMIT 1
        )
    WHERE p.user_id = ?
    ORDER BY p.project_id DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$projects_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Media Library</title>

<link rel="stylesheet" href="<?= CSS_PATH ?>header.css">
<link rel="stylesheet" href="<?= CSS_PATH ?>media_side_bar.css">
<link rel="stylesheet" href="<?= CSS_PATH ?>card_view.css">
<link rel="stylesheet" href="<?= CSS_PATH ?>filter_box.css">
<link rel="stylesheet" href="<?= CSS_PATH ?>prev_next_page.css">

<script>
    // Make project_id globally available before any other JS
    window.PROJECT_ID = <?= intval($_SESSION['project_id']) ?>;
    window.MEDIA_TYPE = <?= json_encode($type) ?>;
</script>
</head>
<body class="preload">

<?php 
include(INCLUDES_PATH . 'project_header.php'); 
include(INCLUDES_PATH . 'media_side_bar.php'); 
?>


<?php
switch ($type) {
    case 'audio':
        include INCLUDES_PATH . 'media_audio.php';
        break;
    case 'images':
        include INCLUDES_PATH . 'media_images.php';
        break;
    case 'docs':
        include INCLUDES_PATH . 'media_docs.php';
        break;
    case 'videos':
        include INCLUDES_PATH . 'media_videos.php';
        break;
    case 'model_art':
        include INCLUDES_PATH . 'media_model_art.php';
        break;
    case 'filtered':
        include INCLUDES_PATH . 'media_filtered.php';
        break;
    case 'other':
        include INCLUDES_PATH . 'media_other.php';
        break;
    case 'pending':
        include INCLUDES_PATH . 'media_pending.php';
        break;
    default:
        include INCLUDES_PATH . 'media_images.php';
}
?>

<?php include(INCLUDES_PATH . 'filter_box.php'); ?>


<script src="<?= JS_PATH ?>delete_file.js"></script>
<script src="<?= JS_PATH ?>filter_box.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
</body>
</html>
