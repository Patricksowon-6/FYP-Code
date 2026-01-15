<?php 
require_once(__DIR__ . '/../config.php'); 

$user_id = $_SESSION['user_id'] ?? 0;
$project_id = $_SESSION['project_id'] ?? null;

// --- Query stays exactly the same ---
$sql = "SELECT uf.file_id, uf.project_id, uf.original_name, uf.user_id, uf.file_approval, 
               u.user_name AS user_name, uf.category, uf.path
        FROM user_files uf
        JOIN users u ON uf.user_id = u.user_id
        WHERE 1=1";

$params = [];
$types = "";

if (!empty($_GET['file_name'])) {
    $sql .= " AND original_name LIKE ?";
    $params[] = "%" . $_GET['file_name'] . "%";
    $types .= "s";
}
if (!empty($_GET['user_name'])) {
    $sql .= " AND uf.user_id = (SELECT user_id FROM users WHERE user_name = ? LIMIT 1)";
    $params[] = $_GET['user_name'];
    $types .= "s";
}

if (!empty($_GET['extension'])) {
    $sql .= " AND original_name LIKE ?";
    $params[] = "%." . $_GET['extension'];
    $types .= "s";
}
if (!empty($_GET['asset_type'])) {
    $sql .= " AND category = ?";
    $params[] = $_GET['asset_type'];
    $types .= "s";
}

$sql .= " ORDER BY uploaded_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $row['url'] = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/{$row['path']}";
    $files[] = $row;
}
?>

<?php if(empty($files)): ?>

    <div class="empty-state">
        <div class="empty-box">
            <i class="fa-solid fa-folder-open empty-icon"></i>
            <h2>No results found</h2>
            <p>Try adjusting your filters to find assets.</p>
            <button class="add-asset-btn" onclick="document.getElementById('filterModal').style.display='flex'">
                <i class="fa-solid fa-filter"></i> Change Filters
            </button>
        </div>
    </div>

<?php else: ?>

    <?php foreach($files as $file): ?>
        <?php if ($file['file_approval'] === 'approved'): ?>

            <div class="video" data-asset-id="<?= $file['file_id'] ?>">
                <center>
                    <?php if($file['category'] === 'videos'): ?>
                        <img src="<?= IMG_PATH ?>video_icon.png"
                             class="thumbnail-image videoPreviewBtn"
                             alt="Video"
                             data-video="<?= htmlspecialchars($file['url']); ?>">

                    <?php elseif ($file['category'] === 'documents'): ?>
                        <img src="<?= IMG_PATH ?>document_icon.png"
                             class="thumbnail-image"
                             alt="Document">

                    <?php elseif ($file['category'] === 'models'): ?>
                        <img src="<?= IMG_PATH ?>model_icon.png"
                             class="thumbnail-image"
                             alt="Model">

                    <?php elseif ($file['category'] === 'other'): ?>
                        <img src="<?= IMG_PATH ?>other_icon.png"
                             class="thumbnail-image"
                             alt="Other">

                    <?php elseif ($file['category'] === 'audio'): ?>
                        <audio controls class="media-player">
                            <source src="<?= htmlspecialchars($file['url']); ?>" type="audio/mpeg">
                            Your browser does not support the audio element.
                        </audio>

                    <?php elseif ($file['category'] === 'images'): ?>
                        <img src="<?= htmlspecialchars($file['url']); ?>"
                             class="thumbnail-image"
                             alt="<?= htmlspecialchars($file['original_name']); ?>">

                    <?php endif; ?>

                    <h1 class="file-name"><?= htmlspecialchars($file['original_name']); ?><br><br></h1>
                    <h3 class="person-name">By <?= htmlspecialchars($file['user_name'] ?? $_SESSION['user_name']); ?><br><br></h3>

                    <div class="action-buttons">
                        <a href="<?= htmlspecialchars($file['url']); ?>" download target="_blank">
                            <i class="fa-solid fa-download"></i>
                        </a>

                        <button type="button" class="delete-btn"
                                onclick="openDeleteModal('<?= $file['file_id'] ?>')">
                            <i class="fa-solid fa-trash"></i>
                        </button>

                        <a href="<?= PAGES_URL ?>comments_page.php?image=<?= urlencode($file['path']) ?>&project_id=<?= $project_id ?>&file_id=<?= $file['file_id'] ?>">
                            <i class="fa-solid fa-comment"></i>
                        </a>

                        <?php include(INCLUDES_PATH . 'kebab.php'); ?>
                    </div>
                </center>
            </div>

        <?php endif; ?>
    <?php endforeach; ?>

<?php endif; ?>


<!-- VIDEO MODAL shared with videos page -->
<div id="videoModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>🎬 Video Player</h2>
        <video id="videoPlayer" controls width="100%" preload="metadata"></video>
    </div>
</div>

<script src="<?= JS_PATH ?>video.js"></script>
