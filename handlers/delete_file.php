<?php

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$user_id = $_SESSION['user_id'] ?? 0;
$project_id = $_SESSION['project_id'] ?? 0;

// ✅ 1. Ensure logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// ✅ 2. Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// ✅ 3. Parse JSON or form data
$input = file_get_contents('php://input');
$body = [];
if ($input) {
    $decoded = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $body = $decoded;
    }
}
$body = array_merge($body, $_POST);

if (empty($body['asset_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing asset_id']);
    exit;
}

$asset_id = intval($body['asset_id']);
$user_id = $_SESSION['user_id'];


// ==========================================
// 4. Look up file + ownership + role
// ==========================================
$stmt = $conn->prepare("
    SELECT uf.file_id, uf.path, uf.original_name, u.user_name AS file_owner_username, p.user_id AS project_owner_id, pu.role AS project_role
    FROM user_files uf
    JOIN users u ON uf.user_id = u.user_id
    JOIN projects p ON uf.project_id = p.project_id
    LEFT JOIN project_users pu 
        ON pu.project_id = p.project_id 
        AND pu.user_id = ?
    WHERE uf.file_id = ? AND uf.project_id = ?
    LIMIT 1
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB prepare failed']);
    exit;
}

$stmt->bind_param('iii', $user_id, $asset_id, $project_id);
$stmt->execute();
$result = $stmt->get_result();
$file = $result->fetch_assoc();
$stmt->close();

if (!$file) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File not found']);
    exit;
}


// ==========================================
// ✅ 5. Delete from Supabase
// ==========================================
$path = ltrim($file['path'], '/'); // no leading slash
$supabase_url = rtrim(SUPABASE_URL, '/');
$service_key = SUPABASE_SERVICE_KEY;

$delete_url = "{$supabase_url}/storage/v1/object/{$path}";

$ch = curl_init($delete_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "DELETE",
    CURLOPT_POSTFIELDS => json_encode(['prefixes' => [$path]]),
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer {$service_key}",
        "apikey: {$service_key}",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// ==========================================
// ✅ 6. If Supabase deletion is OK, remove from DB
// ==========================================
if (($http_code >= 200 && $http_code < 300) || $http_code === 404) {
    $del = $conn->prepare("DELETE FROM user_files WHERE file_id = ? AND project_id = ?");
    if (!$del) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'DB prepare error on delete']);
        exit;
    }

    $del->bind_param('ii', $asset_id, $project_id);
    $ok = $del->execute();
    $del->close();

    if ($ok) {
        echo json_encode([
            'success' => true,
            'message' => 'File deleted successfully',
            'deleted_path' => $path
        ]);
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete database record']);
        exit;
    }
} else {
    // ❌ Supabase returned an error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete from Supabase',
        'supabase_status' => $http_code,
        'supabase_response' => $response,
        'curl_error' => $error,
        'sent_path' => $path
    ]);
    exit;
}
