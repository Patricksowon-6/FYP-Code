<?php
require_once(__DIR__ . '/../config.php'); // DB + SUPABASE_URL

header('Content-Type: application/json; charset=utf-8');

// -------------------- READ FILTERS --------------------
$file_name   = $_GET['file_name']   ?? null;
$user_name   = $_GET['user_name']   ?? null;
$extension   = $_GET['extension']   ?? null;
$asset_type  = $_GET['asset_type']  ?? null;

// -------------------- BASE QUERY --------------------
$sql = "SELECT 
            uf.file_id,
            uf.user_id,
            uf.original_name,
            uf.category,
            uf.path,
            u.username AS user_name
        FROM user_files uf
        JOIN users u ON uf.user_id = u.user_id
        WHERE uf.file_approval = 'approved'";

$params = [];
$types = "";

// -------------------- APPLY FILTERS --------------------
if (!empty($file_name)) {
    $sql .= " AND uf.original_name LIKE ?";
    $params[] = "%{$file_name}%";
    $types .= "s";
}

if (!empty($user_name)) {
    $sql .= " AND u.username = ?";
    $params[] = $user_name;
    $types .= "s";
}

if (!empty($extension)) {
    $sql .= " AND uf.original_name LIKE ?";
    $params[] = "%.{$extension}";
    $types .= "s";
}

if (!empty($asset_type)) {
    $sql .= " AND uf.category = ?";
    $params[] = $asset_type;
    $types .= "s";
}

$sql .= " ORDER BY uf.uploaded_at DESC";

// -------------------- RUN QUERY --------------------
$stmt = $conn->prepare($sql);

if ($types !== "") {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// -------------------- FORMAT RESULTS --------------------
$files = [];
while ($row = $result->fetch_assoc()) {
    $row['url'] = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/" . $row['path'];
    $files[] = $row;
}

echo json_encode($files, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
