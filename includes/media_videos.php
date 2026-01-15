<?php
$stmt = $conn->prepare("
    SELECT uf.file_id, uf.path, uf.original_name, u.user_name, p.project_id, uf.file_approval
    FROM user_files uf
    JOIN users u ON uf.user_id = u.user_id
    JOIN projects p ON uf.project_id = p.project_id
    WHERE uf.category = 'videos' AND p.project_id = ?
    ORDER BY uf.uploaded_at DESC
");

$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<?php if ($result->num_rows > 0): ?>

    <?php while ($video = $result->fetch_assoc()):
        $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/{$video['path']}";
    ?>
        <?php if ($video['file_approval'] === 'approved'): ?>
            <div class="video" style="margin-top: 100px;" data-asset-id="<?= $video['file_id'] ?>">
                <center>
                    <!-- Thumbnail / Play button -->
                    <img src="<?= IMG_PATH ?>video_icon.png" class="thumbnail-image videoPreviewBtn" alt="Video" data-video="<?= htmlspecialchars($url) ?>">

                    <!-- <video controls class="media-player" style="width:100%; max-width:700px; height:auto;">
                        <source src="<?= htmlspecialchars($url) ?>" type="video/mp4">
                        Your browser does not support the video element.
                    </video> -->

                    <h1 class="file-name"><?= htmlspecialchars($video['original_name']); ?><br><br></h1>
                    <h3 class="person-name">By <?= htmlspecialchars($_SESSION['user_name']); ?><br><br></h3>

                    <div class="action-buttons">
                        <a href="<?= htmlspecialchars($url) ?>" download target="_blank">
                            <i class="fa-solid fa-download"></i>
                        </a>

                        <button type="button" class="delete-btn" onclick="openDeleteModal('<?= $video['file_id'] ?>')">
                            <i class="fa-solid fa-trash"></i>
                        </button>

                        <a href="<?= PAGES_URL ?>comments_page.php?image=<?= urlencode($video['path']) ?>&project_id=<?= $project_id ?>&file_id=<?= $video['file_id'] ?>">
                            <i class="fa-solid fa-comment"></i>
                        </a>
                        <?php include(INCLUDES_PATH . 'kebab.php'); ?>
                    </div>
                </center>
            </div>
        <?php endif; ?>
    <?php endwhile; ?>

<?php else: ?>

    <!-- EMPTY STATE (CONSISTENT WITH OTHER ASSETS) -->
    <div class="empty-state">
        <div class="empty-box">
            <i class="fa-solid fa-photo-film empty-icon"></i>

            <h2>No videos yet</h2>
            <p>Add your first asset to get started with this project.</p>

            <a href="<?= PAGES_URL ?>file_upload.php" class="add-asset-btn">
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
