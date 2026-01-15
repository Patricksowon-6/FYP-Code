<?php
require_once(__DIR__ . '/../config.php');

header("Content-Type: application/json");

$user_id = $_SESSION['user_id'] ?? 0;

// Add comment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$user_id) {
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }

    $file_id = intval($_POST['file_id']);
    $comment_text = trim($_POST['comment_text']);

    if ($comment_text === '') {
        echo json_encode(['error' => 'Empty comment']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO comments (file_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $file_id, $user_id, $comment_text);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

// Get comments
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $file_id = intval($_GET['file_id']);

    $stmt = $conn->prepare("
        SELECT c.comment_id, c.user_id, c.comment_text, c.created_at, u.user_name
        FROM comments c
        JOIN Users u ON c.user_id = u.user_id
        WHERE c.file_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    echo json_encode($comments);
    exit;
}

// Delete comment
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    $comment_id = intval($_DELETE['comment_id']);

    $stmt = $conn->prepare("DELETE FROM comments WHERE comment_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $comment_id, $user_id);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

// Edit comment
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $comment_id = intval($_PUT['comment_id']);
    $new_text = trim($_PUT['comment_text']);

    $stmt = $conn->prepare("
        UPDATE comments 
        SET comment_text = ?
        WHERE comment_id = ? AND user_id = ?
    ");
    $stmt->bind_param("sii", $new_text, $comment_id, $user_id);
    $stmt->execute();

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
exit;
?>
