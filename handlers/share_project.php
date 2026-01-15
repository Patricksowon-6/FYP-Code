<?php
header("Content-Type: application/json");
require_once(__DIR__ . '/../config.php');

$project_id = $_POST['project_id'] ?? null;
$email = $_POST['email'] ?? null; 
$user_position = $_POST['position'];
$role = $_POST['role'];


if ((strtolower($_POST['position']) === 'viewer') || empty($_POST['position'])) {
    $user_position = 'unset';
}
elseif (strtolower($_POST['role']) === 'co-owner') {
    $user_position = $_POST['position'];
    $role = 'Co-Owner';
}
else {
    $user_position = $_POST['position'];
    $role = 'Editor';
}



if (!$project_id || !$email) {
    echo json_encode(["success" => false, "message" => "Missing data."]);
    exit;
}

// 1. Get the user ID by email
$stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "No user found with that email."]);
    exit;
}

$user = $result->fetch_assoc();
$shared_user_id = $user['user_id'];

// 2. Check if already shared
$stmt = $conn->prepare("
    SELECT * FROM project_users
    WHERE project_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $project_id, $shared_user_id);
$stmt->execute();
$exists = $stmt->get_result();

if ($exists->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "This user already has access."]);
    exit;
}

// 3. Insert share record
$stmt = $conn->prepare("
    INSERT INTO project_users (project_id, user_id, role, user_position)
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiss", $project_id, $shared_user_id, $role, $user_position);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Project shared successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to share project."]);
}
?>
