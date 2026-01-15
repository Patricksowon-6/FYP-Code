<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

/* ---------------------------
   AUTH & PROJECT VALIDATION
----------------------------*/
$user_id = $_SESSION['user_id'] ?? 0;
$project_id = $_SESSION['project_id'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

global $conn;

$stmt = $conn->prepare("
    SELECT 1
    FROM projects p
    LEFT JOIN project_users pu 
        ON pu.project_id = p.project_id AND pu.user_id = ?
    WHERE p.project_id = ? AND (p.user_id = ? OR pu.user_id = ?)
    LIMIT 1
");
$stmt->bind_param("iiii", $user_id, $project_id, $user_id, $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    echo json_encode(['error' => 'Invalid project']);
    exit;
}
$stmt->close();


/* ---------------------------
    SUPABASE UPLOAD FUNCTION
----------------------------*/
function upload_to_supabase($file, $user_id, $project_id) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;

    $filename = preg_replace('/\s+/', '_', basename($file['name']));
    $path = "project_{$project_id}/user_{$user_id}/uploads/cards/" . uniqid() . '_' . $filename;

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
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($code >= 200 && $code < 300) ? $path : null;
}


/* ---------------------------
        DB FUNCTIONS
----------------------------*/
function createMainCard($project_id, $user_id, $name, $purpose, $type, $path, $original_name) {
    global $conn;
    $stmt = $conn->prepare("
        INSERT INTO main_card (project_id, user_id, card_name, card_purpose, card_type, path, original_name)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisssss", $project_id, $user_id, $name, $purpose, $type, $path, $original_name);
    $stmt->execute();
    return $stmt->insert_id;
}

function updateMainCard($card_id, $user_id, $name, $purpose, $type, $path = null, $original_name = null) {
    global $conn;

    if ($path && $original_name) {
        $stmt = $conn->prepare("
            UPDATE main_card
            SET card_name=?, card_purpose=?, card_type=?, path=?, original_name=?, updated_at=NOW()
            WHERE main_card_id=? AND user_id=?
        ");
        $stmt->bind_param("sssssii", $name, $purpose, $type, $path, $original_name, $card_id, $user_id);
    } else {
        $stmt = $conn->prepare("
            UPDATE main_card
            SET card_name=?, card_purpose=?, card_type=?, updated_at=NOW()
            WHERE main_card_id=? AND user_id=?
        ");
        $stmt->bind_param("sssii", $name, $purpose, $type, $card_id, $user_id);
    }

    return $stmt->execute();
}

function getMainCards($project_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM main_card WHERE project_id=? ORDER BY main_card_id DESC");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $cards = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($cards as &$card) {
        if (!empty($card['path'])) {
            $card['image_url'] = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . $card['path'];
        } else {
            $card['image_url'] = null;
        }
    }

    return $cards;
}


/* ---------------------------
        ROUTES
----------------------------*/
$method = $_SERVER['REQUEST_METHOD'];


/* ---- POST (create/update) ---- */
if ($method === 'POST') {

    $card_id   = $_POST['card_id'] ?? null;
    $name      = trim($_POST['name'] ?? '');
    $purpose   = trim($_POST['purpose'] ?? '');
    $card_type = trim($_POST['card_type'] ?? '');

    if ($name === '' || $purpose === '' || $card_type === '') {
        echo json_encode(['success' => false, 'error' => 'Missing fields']);
        exit;
    }

    // optional image upload
    $image_path = null;
    $original_name = null;

    if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_path = upload_to_supabase($_FILES['image'], $user_id, $project_id);
        $original_name = $_FILES['image']['name'];
    }

    if ($card_id) {
        // UPDATE
        $ok = updateMainCard((int)$card_id, $user_id, $name, $purpose, $card_type, $image_path, $original_name);
        echo json_encode(['success' => (bool)$ok, 'updated' => (bool)$ok]);
        exit;
    } else {
        // CREATE
        $new_id = createMainCard($project_id, $user_id, $name, $purpose, $card_type, $image_path, $original_name);
        echo json_encode(['success' => true, 'main_card_id' => $new_id]);
        exit;
    }
}


/* ---- GET (fetch list) ---- */
if ($method === 'GET' && isset($_GET['fetch'])) {
    echo json_encode(getMainCards($project_id));
    exit;
}


/* ---- DELETE ---- */
if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $del);
    $id = $del['main_card_id'] ?? 0;

    if ($id) {
        $stmt = $conn->prepare("DELETE FROM main_card WHERE main_card_id=? AND user_id=?");
        $stmt->bind_param("ii", $id, $user_id);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Missing ID']);
    exit;
}


/* ---- FALLBACK ---- */
echo json_encode(['success' => false, 'error' => 'Unsupported method']);
exit;
?>