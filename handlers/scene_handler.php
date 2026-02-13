<?php
require_once __DIR__ . "/../config.php";

header("Content-Type: application/json");

$user_id = $_SESSION['user_id'] ?? null;
$action  = $_REQUEST['action'] ?? null;
$project_id = $_SESSION['project_id'] ?? 0;

if (!$user_id) {
    echo json_encode(["success" => false, "error" => "Unauthorized"]);
    exit;
}

if (!$action) {
    echo json_encode(["success" => false, "error" => "No action"]);
    exit;
}

if ($action === "add_shoot_date") {

    $project_id = intval($_SESSION['project_id'] ?? 0);
    $scene      = trim($_POST['scene'] ?? "");
    $date       = $_POST['date'] ?? "";

    if (!$project_id || !$scene || !$date) {
        echo json_encode(["success" => false, "error" => "Missing fields"]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO shoot_dates (project_id, user_id, scene_name, shoot_date)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("iiss", $project_id, $user_id, $scene, $date);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

/* =====================================================
   GET SHOOT DATES (LEFT SIDEBAR)
===================================================== */
if ($action === "get_shoot_dates") {

    // JS passes project_id, but fallback to session to be safe
    $project_id = intval($_GET['project_id'] ?? $_SESSION['project_id'] ?? 0);

    $stmt = $conn->prepare("
        SELECT
            shoot_date_id,
            scene_name AS scene,
            shoot_date AS date
        FROM shoot_dates
        WHERE project_id = ?
        ORDER BY shoot_date ASC
    ");

    $stmt->bind_param("i", $project_id);
    $stmt->execute();

    $res = $stmt->get_result();
    echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    exit;
}

/* =====================================================
   ATTACH EXISTING FILE TO SHOOT DATE
===================================================== */
if ($action === "attach_file") {

    $shoot_date_id = intval($_POST['shoot_date_id'] ?? 0);
    $file_id       = intval($_POST['file_id'] ?? 0);

    if (!$shoot_date_id || !$file_id) {
        echo json_encode(["success" => false]);
        exit;
    }

    // Prevent duplicate attachments
    $check = $conn->prepare("
        SELECT asset_id
        FROM shoot_date_assets
        WHERE shoot_date_id = ? AND file_id = ?
    ");
    $check->bind_param("ii", $shoot_date_id, $file_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(["success" => true]);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO shoot_date_assets (shoot_date_id, file_id, user_id)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iii", $shoot_date_id, $file_id, $user_id);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit;
}

/* =====================================================
   GET ASSETS FOR A SHOOT DATE
===================================================== */
if ($action === "get_assets_for_shoot") {

    $shoot_date_id = intval($_GET['shoot_date_id'] ?? 0);

    $stmt = $conn->prepare("
        SELECT
            sda.asset_id,
            f.original_name AS title,
            f.path AS url,
            f.category,
            COALESCE(sda.status, 'not_ready') AS status
        FROM shoot_date_assets sda
        JOIN user_files f ON f.file_id = sda.file_id
        WHERE sda.shoot_date_id = ?
        ORDER BY sda.created_at DESC
    ");

    $stmt->bind_param("i", $shoot_date_id);
    $stmt->execute();

    $res = $stmt->get_result();
    $assets = [];

    while ($row = $res->fetch_assoc()) {
        $assets[] = [
            "asset_id"   => (int)$row['asset_id'],
            "title"      => $row['title'],
            "category"   => strtolower(trim($row['category'])),
            "status"     => $row['status'],
            "public_url" => rtrim(SUPABASE_URL, '/')
                . "/storage/v1/object/public/"
                . ltrim($row['url'], '/')
        ];
    }

    echo json_encode($assets);
    exit;
}



/* =====================================================
   GET EXISTING FILES (SUPABASE METADATA)
===================================================== */

if ($action === "get_files") {

    $project_id = intval($_GET['project_id'] ?? $_SESSION['project_id'] ?? 0);
    $category   = $_GET['category'] ?? "";

    $stmt = $conn->prepare("
        SELECT
            file_id,
            original_name,
            path,
            category
        FROM user_files
        WHERE project_id = ?
          AND category = ?
          AND file_approval = 'approved'
        ORDER BY uploaded_at DESC
    ");

    $stmt->bind_param("is", $project_id, $category);
    $stmt->execute();

    $res = $stmt->get_result();
    $files = [];

    while ($row = $res->fetch_assoc()) {

        $files[] = [
            "file_id"       => $row['file_id'],
            "original_name" => $row['original_name'],
            "category"      => strtolower(trim($row['category'])),
            "public_url"    => rtrim(SUPABASE_URL, '/')
                . "/storage/v1/object/public/"
                . ltrim($row['path'], '/')
        ];
    }

    echo json_encode($files);
    exit;

}
if ($action === "update_asset_status") {

    $asset_id = intval($_POST['asset_id'] ?? 0);
    $status   = $_POST['status'] ?? null;

    if (!$asset_id || !$status) {
        echo json_encode([
            "success" => false,
            "error" => "Missing asset ID or status",
            "debug" => $_POST // 👈 temporary, remove later
        ]);
        exit;
    }

    $allowed = ["ready", "in_progress", "not_ready"];
    if (!in_array($status, $allowed, true)) {
        echo json_encode(["success" => false, "error" => "Invalid status"]);
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE shoot_date_assets
        SET status = ?
        WHERE asset_id = ?
    ");
    $stmt->bind_param("si", $status, $asset_id);

    echo json_encode(["success" => $stmt->execute()]);
    exit;
}



echo json_encode(["success" => false, "error" => "Unknown action"]);
