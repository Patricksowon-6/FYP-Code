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

if ($action === "upload_new_file") {

    $shoot_date_id = intval($_POST['shoot_date_id'] ?? 0);

    if (!$shoot_date_id || empty($_FILES['file'])) {
        echo json_encode([
            "success" => false,
            "error"   => "Missing shoot date or file"
        ]);
        exit;
    }

    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            "success" => false,
            "error"   => "Upload failed"
        ]);
        exit;
    }

    /* =====================================================
       FILE INFO
    ===================================================== */

    $original_name = $file['name'];
    $tmp_path      = $file['tmp_name'];
    $extension     = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    /* =====================================================
       DETERMINE CATEGORY FROM EXTENSION
    ===================================================== */

    switch ($extension) {
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'webp':
        case 'gif':
        case 'svg':
            $category = 'images';
            break;

        case 'mp4':
        case 'mov':
        case 'webm':
            $category = 'videos';
            break;

        case 'mp3':
        case 'wav':
        case 'mscz':
            $category = 'audio';
            break;

        case 'pdf':
        case 'doc':
        case 'docx':
        case 'txt':
        case 'html':
        case 'htm':
        case 'csv':
        case 'md':
            $category = 'documents';
            break;

        case 'blend':
        case 'fbx':
        case 'obj':
            $category = 'models';
            break;

        default:
            $category = 'other';
    }

    $storage_path = "project_{$project_id}/user_{$user_id}/uploads/{$category}/{$original_name}";

    // ---------------------------------------------------------
    // 9. UPLOAD TO SUPABASE
    // ---------------------------------------------------------
    $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/" . $storage_path;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => "PUT",
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
            "apikey: " . SUPABASE_SERVICE_KEY,
            "Content-Type: " . mime_content_type($file['tmp_name'])
        ],
        CURLOPT_POSTFIELDS => file_get_contents($file['tmp_name'])
    ]);

    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code < 200 || $http_code >= 300) {
        echo json_encode(['error' => 'Upload failed']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO user_files
            (project_id, user_id, original_name, path, category, file_approval)
        VALUES
            (?, ?, ?, ?, ?, 'approved')
    ");

    $stmt->bind_param(
        "iisss",
        $project_id,
        $user_id,
        $original_name,
        $storage_path,
        $category
    );

    if (!$stmt->execute()) {
        echo json_encode([
            "success" => false,
            "error"   => "Failed to save file metadata"
        ]);
        exit;
    }

    $file_id = $stmt->insert_id;

    /* =====================================================
       ATTACH FILE TO SHOOT DATE
    ===================================================== */

    $stmt = $conn->prepare("INSERT INTO shoot_date_assets (shoot_date_id, file_id, user_id, status) VALUES (?, ?, ?, 'not_ready')");

    $stmt->bind_param("iii", $shoot_date_id, $file_id, $user_id);
    $stmt->execute();

    /* =====================================================
       RESPONSE
    ===================================================== */

    echo json_encode([
        "success" => true,
        "file_id" => $file_id,
        "category" => $category,
        "public_url" => rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . ltrim($storage_path, '/')
    ]);

    exit;
}



echo json_encode(["success" => false, "error" => "Unknown action"]);
