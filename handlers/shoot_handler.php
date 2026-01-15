<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json");

session_start();
$project_id = $_SESSION['project_id'] ?? 0;
$user_id = $_SESSION["user_id"] ?? 0;

$action = $_GET["action"] ?? $_POST["action"] ?? null;

if (!$action) {
    echo json_encode(["error" => "No action specified"]);
    exit;
}

/*************************************************************
 1. GET SHOOT DATES
*************************************************************/
if ($action === "get_shoot_dates") {
    $project_id = $_GET["project_id"] ?? $project_id;

    $stmt = $conn->prepare("
        SELECT shoot_date_id, shoot_date AS date, name AS scene
        FROM shoot_dates
        WHERE project_id = ?
        ORDER BY shoot_date ASC
    ");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    exit;
}

/*************************************************************
 2. ADD SHOOT DATE
*************************************************************/
if ($action === "add_shoot_date") {
    $project_id = $_POST["project_id"] ?? $project_id;
    $date = $_POST["date"] ?? null;
    $scene = $_POST["scene"] ?? null;

    if (!$date || !$scene) {
        echo json_encode(["success" => false, "error" => "Missing date or scene"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO shoot_dates (project_id, user_id, shoot_date, name)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $project_id, $user_id, $date, $scene);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    exit;
}

/*************************************************************
 3. LOAD ASSETS FOR SHOOT DATE
*************************************************************/
if ($action === "get_assets_for_shoot") {
    $shoot_date_id = $_GET["shoot_date_id"] ?? 0;

    $stmt = $conn->prepare("
        SELECT 
            uf.file_id AS id,
            uf.original_name AS title,
            uf.category,
            CONCAT(?, '/storage/v1/object/public/', uf.path) AS url,
            CASE WHEN uf.category='images' THEN 'image'
                 WHEN uf.category='docs' THEN 'pdf'
                 ELSE 'file'
            END AS type
        FROM shoot_date_assets sda
        JOIN user_files uf ON uf.file_id = sda.file_id
        WHERE sda.shoot_date_id = ?
        ORDER BY uf.uploaded_at DESC
    ");
    $stmt->bind_param("si", SUPABASE_URL, $shoot_date_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    exit;
}

/*************************************************************
 4. GET FILES IN CATEGORY
*************************************************************/
if ($action === "get_files") {
    $project_id = $_GET["project_id"] ?? $project_id;
    $category = $_GET["category"] ?? "";

    $stmt = $conn->prepare("
        SELECT 
            uf.file_id,
            uf.original_name,
            uf.path,
            CONCAT(?, '/storage/v1/object/public/', uf.path) AS public_url,
            (uf.mime_type LIKE 'image/%') AS is_image,
            (uf.mime_type LIKE 'video/%') AS is_video,
            (uf.mime_type LIKE 'audio/%') AS is_audio
        FROM user_files uf
        WHERE uf.project_id = ? AND uf.category = ?
        ORDER BY uf.uploaded_at DESC
    ");
    $stmt->bind_param("sis", SUPABASE_URL, $project_id, $category);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    exit;
}

/*************************************************************
 5. ATTACH FILE TO SHOOT DATE
*************************************************************/
if ($action === "attach_file") {
    $shoot_date_id = $_POST["shoot_date_id"] ?? 0;
    $file_id = $_POST["file_id"] ?? 0;

    if (!$shoot_date_id || !$file_id) {
        echo json_encode(["success" => false, "error" => "Missing IDs"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO shoot_date_assets (shoot_date_id, file_id, user_id)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iii", $shoot_date_id, $file_id, $user_id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "error" => $stmt->error]);
    }
    exit;
}

echo json_encode(["error" => "Invalid action"]);
?>