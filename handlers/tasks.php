<?php
require_once(__DIR__ . '/../config.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$user_id = $_SESSION['user_id'] ?? 0;

if (!$user_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

global $conn;

/* -------------------------------
   CRUD FUNCTIONS
--------------------------------*/

function createTask($user_id, $content, $status = 'todo', $deadline = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO Tasks (user_id, content, status, deadline) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $content, $status, $deadline);
    $stmt->execute();
    return $stmt->insert_id;
}

function getTasks($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM Tasks WHERE user_id = ? ORDER BY created_at ASC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function updateTask($user_id, $task_id, $content, $status, $deadline = null) {
    global $conn;
    $stmt = $conn->prepare("UPDATE Tasks 
        SET content = ?, status = ?, deadline = ?, updated_at = NOW() 
        WHERE task_id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $content, $status, $deadline, $task_id, $user_id);
    return $stmt->execute();
}

function deleteTask($user_id, $task_id) {
    global $conn;
    $stmt = $conn->prepare("DELETE FROM Tasks WHERE task_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    return $stmt->execute();
}


/* -------------------------------
   API ENDPOINT HANDLER
--------------------------------*/

$method = $_SERVER['REQUEST_METHOD'];

// --- CREATE (POST)
if ($method === 'POST') {
    $content  = $_POST['content'] ?? '';
    $status   = $_POST['status'] ?? 'todo';
    $deadline = $_POST['deadline'] ?? null;

    $task_id = createTask($user_id, $content, $status, $deadline);

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'task_id' => $task_id]);
    exit;
}

// --- UPDATE (PUT)
if ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);

    $task_id  = $_PUT['task_id'] ?? null;
    $content  = $_PUT['content'] ?? '';
    $status   = $_PUT['status'] ?? 'todo';
    $deadline = $_PUT['deadline'] ?? null;

    updateTask($user_id, $task_id, $content, $status, $deadline);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// --- DELETE
if ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $task_id = $_DELETE['task_id'] ?? null;

    deleteTask($user_id, $task_id);

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

// --- FETCH ALL
if ($method === 'GET' && isset($_GET['fetch'])) {
    header('Content-Type: application/json');
    echo json_encode(getTasks($user_id));
    exit;
}


function getTaskDeadlines($user_id) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT content, deadline FROM Tasks WHERE user_id = ? AND deadline IS NOT NULL");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $deadlines = [];
    
    while ($row = $result->fetch_assoc()) {
        // Format date as "YYYY-M-D" to match JS date keys
        $dateKey = date('Y-n-j', strtotime($row['deadline']));
        $deadlines[$dateKey][] = $row['content'];
    }
    
    return $deadlines;
}

?>
