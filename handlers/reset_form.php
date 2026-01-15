<?php
require_once(__DIR__ . '/../config.php');

// Keep user logged in but clear project-related data
$project_keys = [
    'project_id', 'show_title', 'description', 'banner_img', 'quote_img', 'profile_img'
];
for ($i = 1; $i <= 5; $i++) {
    $project_keys[] = "emoji$i";
    $project_keys[] = "genre$i";
    $project_keys[] = "circle_img_$i";
}

foreach ($project_keys as $key) {
    unset($_SESSION[$key]);
}

// Redirect to the project creation page
header("Location: ../pages/more_info.php");
exit;
?>