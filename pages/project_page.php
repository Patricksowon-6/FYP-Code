<?php 
require_once(__DIR__ . '/../config.php');
$project_id = $_GET['project_id'] ?? null;
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT role, user_position
    FROM project_users
    WHERE project_id = ? AND user_id = ?
    LIMIT 1
");

$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$role = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="<?= CSS_PATH ?>header.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>banner.css">
    <link rel="stylesheet" href="<?= CSS_PATH ?>share.css">
</head>
<body>

<?php 
    include(INCLUDES_PATH . 'project_header.php'); 
    include(INCLUDES_PATH . 'banner.php'); 
?>

<?php if ($role['role'] === 'Owner' || $role['role'] === 'Co-Owner'): ?>
    <div id="share_box">
        <form class="share-card" id="shareForm" action="<?= HANDLER_PATH ?>share_project.php" method="POST" novalidate>
            <input type="hidden" name="project_id" value="<?= htmlspecialchars($project_id) ?>">

            <h2 class="share-card__title">Share this project</h2>

            <label class="share-card__label" for="share-email">Teammate's email</label>
            <input id="share-email" class="share-card__input" type="email" name="email" placeholder="teammate@example.com" required/>

            <label class="share-card__label" for="share-role">Role</label>
            <select id="share-role" name="role" class="share-card__select" required>
                <option value="" disabled selected>Select a position</option>

                <?php if ($role['role'] === 'Owner'): ?>
                    <option value="Co-Owner">Co-Owner</option>
                <?php endif; ?>

                <option value="Editor">Editor</option>
                <option value="Viewer">Viewer</option>
            </select>

            <label class="share-card__label" for="share-role">Position</label>
            <input type="text" id="share-role" name="position" class="share-card__select" required>

            <div class="share-card__actions">
                <button class="btn btn--primary" type="submit" name="share_submit">Share</button>
                <button class="btn btn--ghost" type="button" onclick="this.form.reset()">Reset</button>
            </div><br><br>

            <p id="shareStatus"></p>
        </form>
    </div>
<?php endif; ?>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
    <script src="<?= JS_PATH ?>share_project.js"></script>
    <script src="<?= JS_PATH ?>banner.js"></script>

</html>
</body>
