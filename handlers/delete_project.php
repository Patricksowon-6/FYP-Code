<?php
require_once(__DIR__ . '/../config.php');

$user_id = $_SESSION['user_id'] ?? null;
$project_id = $_POST['project_id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$user_id) {
    die("Not logged in.");
}

if (!$project_id) {
    die("Project ID missing.");
}

if (!$action) {
    die("Action missing.");
}

// EDIT BANNER
if ($_POST['action'] === 'edit_banner') {
    header("Location: " . BASE_URL . "pages/more_info.php?project_id=" . $_POST['project_id']);
    exit;
}

// DELETE PROJECT
if ($action === 'delete_project') {

    $check = $conn->prepare("
        SELECT * FROM project_users
        WHERE project_id = ? AND user_id = ? AND role = 'Owner'
    ");

    $check->bind_param("ii", $project_id, $user_id);
    $check->execute();

    $result = $check->get_result();

    if ($result->num_rows === 0) {
        die("Permission denied.");
    }

    // Delete related records
    $tables = ['user_files','project_banner','project_users','projects'];

    foreach ($tables as $table) {

        $stmt = $conn->prepare("DELETE FROM $table WHERE project_id = ?");
        $stmt->bind_param("i", $project_id);
        $stmt->execute();
        $stmt->close();

    }

    $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/bucket/" . "project_" . $project_id;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
            "apikey: " . SUPABASE_SERVICE_KEY
        ]
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($http_code !== 200 && $http_code !== 204) {
        error_log("Supabase bucket deletion failed for $bucket_name: HTTP $http_code, response: $response");
    }

    header("Location: ../pages/projects.php");
    exit;
}

// Fallback
die("Unknown action.");
?>