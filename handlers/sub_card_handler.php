<?php
require_once(__DIR__ . '/../config.php');

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
$project_id = $_SESSION['project_id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

global $conn;

/* ==========================================================
   🔹 Upload to Supabase
========================================================== */
function upload_to_supabase($file, $project_id, $user_id, $folder = 'uploads/cards') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

    $filename = preg_replace('/\s+/', '_', basename($file['name']));
    $path = "project_{$project_id}/user_{$user_id}/{$folder}/" . uniqid() . "_" . $filename;

    $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/" . $path . "?upsert=true";

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer " . SUPABASE_SERVICE_KEY,
            "apikey: " . SUPABASE_SERVICE_KEY,
            "Content-Type: " . mime_content_type($file['tmp_name'])
        ],
        CURLOPT_POSTFIELDS => file_get_contents($file['tmp_name'])
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code >= 200 && $http_code < 300) ? $path : null;
}

/* ==========================================================
   🔹 Delete from Supabase
========================================================== */
function delete_from_supabase($path) {
    if (!$path) return false;
    $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/" . $path;

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
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code >= 200 && $http_code < 300);
}

/* ==========================================================
   🔹 Create Sub Card
========================================================== */
function createSubCard($main_card_id, $title, $description, $path, $original_name) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO sub_card (main_card_id, title, description, path, original_name)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("issss", $main_card_id, $title, $description, $path, $original_name);
    $stmt->execute();

    return $stmt->insert_id;
}

/* ==========================================================
   🔹 Update Sub Card
========================================================== */
function updateSubCard($sub_card_id, $title, $description, $path = null, $original_name = null) {
    global $conn;

    if ($path && $original_name) {
        $stmt = $conn->prepare("
            UPDATE sub_card 
            SET title=?, description=?, path=?, original_name=?
            WHERE sub_card_id=?
        ");
        $stmt->bind_param("ssssi", $title, $description, $path, $original_name, $sub_card_id);
    } else {
        $stmt = $conn->prepare("
            UPDATE sub_card 
            SET title=?, description=?
            WHERE sub_card_id=?
        ");
        $stmt->bind_param("ssi", $title, $description, $sub_card_id);
    }

    return $stmt->execute();
}

/* ==========================================================
   🔹 Get Sub Cards
========================================================== */
function getSubCards($main_card_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM sub_card WHERE main_card_id=?");
    $stmt->bind_param("i", $main_card_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($rows as &$row) {
        $row['image_url'] = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . $row['path'];
    }

    return $rows;
}

/* ==========================================================
   🔹 Delete Sub Card
========================================================== */
function deleteSubCard($sub_card_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT path FROM sub_card WHERE sub_card_id=?");
    $stmt->bind_param("i", $sub_card_id);
    $stmt->execute();
    $path = ($stmt->get_result()->fetch_assoc())['path'] ?? null;

    $stmt = $conn->prepare("DELETE FROM sub_card WHERE sub_card_id=?");
    $stmt->bind_param("i", $sub_card_id);
    $ok = $stmt->execute();

    if ($ok && $path) delete_from_supabase($path);

    return $ok;
}

/* ==========================================================
    🔹 HANDLER
========================================================== */
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && isset($_GET['main_card_id'])) {
    echo json_encode(getSubCards((int)$_GET['main_card_id']));
    exit;
}

if ($method === 'POST') {
    $sub_card_id = $_POST['sub_card_id'] ?? null;
    $main_card_id = $_POST['main_card_id'] ?? null;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$main_card_id || !$title || !$description) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    $path = null;
    $original_name = null;

    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $path = upload_to_supabase($_FILES['image'], $project_id, $user_id, 'uploads/cards');
        $original_name = $_FILES['image']['name'];
    }

    if ($sub_card_id) {
        $ok = updateSubCard((int)$sub_card_id, $title, $description, $path, $original_name);
        echo json_encode(['success' => $ok, 'updated' => true]);
        exit;
    } else {
        $new_id = createSubCard((int)$main_card_id, $title, $description, $path, $original_name);
        echo json_encode(['success' => true, 'sub_card_id' => $new_id]);
        exit;
    }
}

if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $del);
    $id = (int)($del['sub_card_id'] ?? 0);

    echo json_encode(['success' => deleteSubCard($id)]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unsupported method']);
exit;
?>
