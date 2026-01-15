<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

// ---------------------------------------------------------
// 1. AUTH CHECK
// ---------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$user_id    = $_SESSION['user_id'];
$project_id = $_SESSION['project_id'] ?? null;

if (!$project_id) {
    echo json_encode(['error' => 'Project ID missing']);
    exit;
}

// ---------------------------------------------------------
// 2. VERIFY PROJECT ACCESS (ROLE + USER_POSITION)
// ---------------------------------------------------------
$stmt = $conn->prepare("
    SELECT
        CASE
            WHEN p.user_id = ? THEN 'owner'
            ELSE COALESCE(pu.role, 'editor')
        END AS role,
        pu.user_position
    FROM projects p
    LEFT JOIN project_users pu
        ON pu.project_id = p.project_id
        AND pu.user_id = ?
    WHERE p.project_id = ?
    LIMIT 1
");

$stmt->bind_param("iii", $user_id, $user_id, $project_id);
$stmt->execute();
$stmt->bind_result($role, $user_position);
$stmt->fetch();
$stmt->close();

if (!$role) {
    echo json_encode(['error' => 'Invalid project']);
    exit;
}

// ---------------------------------------------------------
// 3. VALIDATE INPUT
// ---------------------------------------------------------
if (empty($_FILES['file']['name']) || empty($_POST['type'])) {
    echo json_encode(['error' => 'Missing file or type']);
    exit;
}

$file = $_FILES['file'];
$type = $_POST['type'];
$force_version = isset($_POST['force_version']) ? (int)$_POST['force_version'] : 0;

// ---------------------------------------------------------
// 4. CATEGORY LOOKUP
// ---------------------------------------------------------
$categories = [
    'document_upload' => 'documents',
    'image_upload'    => 'images',
    'audio_upload'    => 'audio',
    'video_upload'    => 'videos',
    'model_upload'    => 'models',
    'other_upload'    => 'other',
];

if (!isset($categories[$type])) {
    echo json_encode(['error' => 'Invalid upload type']);
    exit;
}

$category     = $categories[$type];
$originalName = $file['name'];
$filename     = preg_replace('/\s+/', '_', basename($originalName));

// ---------------------------------------------------------
// 5. DUPLICATE CHECK
// ---------------------------------------------------------
$stmt = $conn->prepare("
    SELECT file_id
    FROM user_files
    WHERE user_id = ? AND project_id = ? AND category = ? AND original_name = ?
");
$stmt->bind_param("iiss", $user_id, $project_id, $category, $originalName);
$stmt->execute();
$stmt->store_result();

$duplicate_exists = $stmt->num_rows > 0;
$stmt->bind_result($existing_file_id);
$stmt->fetch();
$stmt->close();

// ---------------------------------------------------------
// 6. DUPLICATE MODAL SIGNAL
// ---------------------------------------------------------
if ($duplicate_exists && !$force_version) {
    echo json_encode([
        'duplicate'     => true,
        'original_name' => $originalName
    ]);
    exit;
}

// ---------------------------------------------------------
// 7. VERSION FILENAME HELPER
// ---------------------------------------------------------
function generateVersionedFilename($conn, $user_id, $project_id, $category, $originalName) {
    $ext  = pathinfo($originalName, PATHINFO_EXTENSION);
    $name = pathinfo($originalName, PATHINFO_FILENAME);
    $count = 0;

    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM file_versions v
        JOIN user_files f ON v.file_id = f.file_id
        WHERE f.user_id = ? AND f.project_id = ? 
          AND f.category = ? AND f.original_name = ?
    ");
    $stmt->bind_param("iiss", $user_id, $project_id, $category, $originalName);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return "{$name}_v" . ($count + 1) . ".{$ext}";
}

// ---------------------------------------------------------
// 8. STORAGE PATH
// ---------------------------------------------------------
if (!$duplicate_exists) {
    $path = "project_$project_id/user_$user_id/uploads/$category/$filename";
} else {
    $path = "project_$project_id/user_$user_id/uploads/versions/$category/" .
        generateVersionedFilename($conn, $user_id, $project_id, $category, $originalName);
}

// ---------------------------------------------------------
// 9. UPLOAD TO SUPABASE
// ---------------------------------------------------------
$url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/" . $path;

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

$public_url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . $path;

// ---------------------------------------------------------
// 10. POSITION-BASED APPROVAL LOGIC
// ---------------------------------------------------------
$position_permissions = [
    'Writer'    => ['documents'],
    'Artist'    => ['images', 'models'],
    'Musician'  => ['audio'],
    'Cameraman' => ['videos'],
    'Designer'  => ['models', 'images']
];

// Owners bypass approval
if ($role === 'owner' || $role === 'co-owner') {
    $requires_approval = false;
} else {
    $requires_approval = (
        empty($user_position) ||
        !isset($position_permissions[$user_position]) ||
        !in_array($category, $position_permissions[$user_position], true)
    );
}

$file_approval = $requires_approval ? 'pending' : 'approved';

// ---------------------------------------------------------
// 11. DATABASE INSERTS
// ---------------------------------------------------------
if (!$duplicate_exists) {
    $stmt = $conn->prepare("
        INSERT INTO user_files 
        (user_id, project_id, category, path, original_name, file_approval)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissss", $user_id, $project_id, $category, $path, $originalName, $file_approval);
    $stmt->execute();
    $file_id = $stmt->insert_id;
    $stmt->close();
} else {
    $file_id = $existing_file_id;
    $stmt = $conn->prepare("
        INSERT INTO file_versions (file_id, user_id, path, original_name)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iiss", $file_id, $user_id, $path, $originalName);
    $stmt->execute();
    $stmt->close();
}

// ---------------------------------------------------------
// 12. PERMISSION RESPONSE
// ---------------------------------------------------------
if ($requires_approval) {
    echo json_encode([
        'permission'    => false,
        'pending'       => true,
        'role'          => $role,
        'user_position' => $user_position,
        'category'      => $category,
        'file_id'       => $file_id
    ]);
    exit;
}

// ---------------------------------------------------------
// 13. SUCCESS RESPONSE
// ---------------------------------------------------------
echo json_encode([
    'message'       => $duplicate_exists ? 'New version saved' : 'Upload successful',
    'url'           => $public_url,
    'category'      => $category,
    'file_id'       => $file_id,
    'approved'      => true,
    'user_position' => $user_position
]);
