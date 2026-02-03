<?php
require_once(__DIR__ . '/../config.php');

/* ============================
   HANDLE APPROVE / REJECT
============================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $project_id = $_SESSION['project_id'];

    if (isset($_POST['file_id'])) {
        $file_id = (int) $_POST['file_id'];

        // Fetch file info + project and user position
        $stmt = $conn->prepare("
            SELECT f.category, p.user_id AS owner_id, pu.role, pu.user_position
            FROM user_files f
            JOIN projects p ON f.project_id = p.project_id
            LEFT JOIN project_users pu ON pu.project_id = p.project_id AND pu.user_id = ?
            WHERE f.file_id = ?
            LIMIT 1
        ");
        $stmt->bind_param("ii", $user_id, $file_id);
        $stmt->execute();
        $stmt->bind_result($category, $owner_id, $role, $user_position);
        $stmt->fetch();
        $stmt->close();

        if (!$role) {
            echo json_encode(['error' => 'Invalid file or no permission']);
            exit;
        }

        // Define position-category approval mapping
        $position_approval = [
            'Writer'    => ['documents'],
            'Artist'    => ['images', 'models'],
            'Musician'  => ['audio'],
            'Cameraman' => ['videos'],
            'Designer'  => ['models', 'images']
        ];

        $can_approve = ($role === 'Owner' || $role === 'Co_owner') ||
                        (isset($position_approval[$user_position]) && in_array($category, $position_approval[$user_position], true));

        if (!$can_approve) {
            echo "<script>alert('You do not have permission to approve/reject this file');</script>";
            header("Location: media.php?type=images&project_id=$project_id");
        }
        elseif (isset($_POST['approved']) && $can_approve) {
            $status = 'approved';
            $stmt = $conn->prepare("UPDATE user_files SET file_approval = ? WHERE file_id = ?");
            $stmt->bind_param("si", $status, $file_id);
            $stmt->execute();
            $stmt->close();

            echo "<script>alert('Asset approved!');</script>";
            header("Location: media.php?type=images&project_id=$project_id");

        } elseif (isset($_POST['rejected']) && $can_approve) {
            $stmt = $conn->prepare("DELETE FROM user_files WHERE file_id = ?");
            $stmt->bind_param("i", $file_id);
            $stmt->execute();
            $stmt->close();

            echo "<script>alert('Asset rejected!');</script>";
            header("Location: media.php?type=images&project_id=$project_id");
        }
    }
}


/* ============================
   FETCH FILES
============================ */

$stmt = $conn->prepare("
    SELECT uf.file_id, uf.path, uf.original_name, uf.file_approval, uf.category, u.user_name, p.project_id
    FROM user_files uf
    JOIN users u ON uf.user_id = u.user_id
    JOIN projects p ON uf.project_id = p.project_id
    WHERE p.project_id = ?
    ORDER BY uf.uploaded_at DESC
");

$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php if ($result->num_rows > 0): ?>
    <?php while ($img = $result->fetch_assoc()):
        $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/{$img['path']}";
    ?>

        <?php if ($img['file_approval'] === 'pending'): ?>
            <div class="video" data-asset-id="<?= $img['file_id'] ?>">
                <center>

                    <?php if ($img['category'] === 'images' && $url): ?>
                        <img src="<?= htmlspecialchars($url) ?>" class="thumbnail-image" alt="Asset preview">

                    <?php elseif ($img['category'] === 'videos' && $url): ?>
                        <img
                            src="<?= IMG_PATH ?>video_icon.png"
                            class="thumbnail-image videoPreviewBtn"
                            style="cursor: pointer;"
                            alt="Video"
                            data-video="<?= htmlspecialchars($url) ?>"
                        >

                    <?php elseif ($img['category'] === 'documents'): ?>
                        <img src="<?= IMG_PATH ?>document_icon.png" class="thumbnail-image" alt="Document">

                    <?php elseif ($img['category'] === 'models'): ?>
                        <img src="<?= IMG_PATH ?>model_icon.png" class="thumbnail-image" alt="Model">

                    <?php elseif ($img['category'] === 'other'): ?>
                        <img src="<?= IMG_PATH ?>other_icon.png" class="thumbnail-image" alt="Other">

                    <?php elseif ($img['category'] === 'audio' && $url): ?>
                        <audio controls class="media-player">
                            <source src="<?= htmlspecialchars($url) ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>

                    <?php else: ?>
                        <p style="color:#777;">Preview not available</p>
                    <?php endif; ?>

                    <h1 class="file-name">
                        <?= htmlspecialchars($img['original_name']) ?><br><br>
                    </h1>

                    <h3 class="person-name">
                        By <?= htmlspecialchars($img['user_name']) ?><br><br>
                    </h3>
                    

                    <div class="action-buttons">
                        <form method="post">
                            <input type="hidden" name="file_id" value="<?= $img['file_id'] ?>">

                            <button type="submit" name="approved" id="approve">
                                Approve
                            </button>
                            &emsp;
                            <button type="submit" name="rejected" id="reject">
                                Reject
                            </button>
                        </form>
                    </div>

                </center>
            </div>
        <?php endif; ?>

    <?php endwhile; ?>

<?php else: ?>

    <!-- EMPTY STATE -->
    <div class="empty-state">
        <div class="empty-box">
            <i class="fa-solid fa-photo-film empty-icon"></i>

            <h2>No pending assets</h2>
            <p>Add your first asset to get started with this project.</p>

            <a href="<?= PAGES_URL ?>file_upload.php?project_id=<?= $project_id ?>" class="add-asset-btn">
                <i class="fa-solid fa-plus"></i> Add Asset
            </a>
        </div>
    </div>

<?php endif; ?>

<!-- VIDEO MODAL -->
<div id="videoModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>🎬 Video Player</h2>
        <video id="videoPlayer" controls width="100%" preload="metadata"></video>
    </div>
</div>

<script src="<?= JS_PATH ?>video.js"></script>
