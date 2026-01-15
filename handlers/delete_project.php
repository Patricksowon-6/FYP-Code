<?php
require_once(__DIR__ . '/../config.php');

/* ============================
   DELETE PROJECT
============================ */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/projects.php");
    exit;
}

$user_id    = $_SESSION['user_id']    ?? null;
$project_id = $_SESSION['project_id'] ?? null;

if (!$user_id || !$project_id) {
    die("Unauthorized.");
}

/* ============================
   VERIFY OWNERSHIP
============================ */

$check = $conn->prepare("
    SELECT 1 FROM project_users
    WHERE project_id = ? AND user_id = ? AND role = 'Owner'
");
$check->bind_param("ii", $project_id, $user_id);
$check->execute();

if ($check->get_result()->num_rows === 0) {
    die("Permission denied.");
}

/* ============================
   DELETE DATABASE RECORDS
============================ */

// Order matters (FK safety)

$stmt = $conn->prepare("DELETE FROM user_files WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM project_banner WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM project_users WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM projects WHERE project_id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();

/* ============================
   DELETE SUPABASE BUCKET
============================ */

$bucket_name = "project_" . $project_id;
delete_supabase_bucket($bucket_name);

/* ============================
   CLEAN SESSION
============================ */

unset(
    $_SESSION['project_id'],
    $_SESSION['show_title'],
    $_SESSION['quote'],
    $_SESSION['banner_img'],
    $_SESSION['quote_img'],
    $_SESSION['profile_img']
);

/* ============================
   REDIRECT
============================ */

header("Location: ../pages/projects.php");
exit;


/* ============================
   HELPERS
============================ */

function delete_supabase_bucket($bucket_name) {
    $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/bucket/" . $bucket_name;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
            "apikey: " . SUPABASE_SERVICE_KEY
        ]
    ]);

    curl_exec($ch);
    curl_close($ch);
}
?>