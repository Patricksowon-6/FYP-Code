<?php
$stmt = $conn->prepare("
    SELECT uf.file_id, uf.path, uf.original_name, u.user_name, p.project_id, uf.file_approval
    FROM user_files uf
    JOIN users u ON uf.user_id = u.user_id
    JOIN projects p ON uf.project_id = p.project_id
    WHERE uf.category = 'audio' AND p.project_id = ?
    ORDER BY uf.uploaded_at DESC
");

$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<?php if ($result->num_rows > 0): ?>

    <?php while ($audio = $result->fetch_assoc()):
        $url = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/{$audio['path']}";
    ?>
        <?php if ($audio['file_approval'] === 'approved'): ?>
            <div class="video" style="margin-top: 100px;" data-asset-id="<?= $audio['file_id'] ?>">
                <center>
                    <!-- Audio preview -->
                    <audio controls>
                        <source src="<?= htmlspecialchars($url) ?>" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>

                    <h1 class="file-name">
                        <?= htmlspecialchars($audio['original_name']); ?><br><br>
                    </h1>

                    <h3 class="person-name">
                        By <?= htmlspecialchars($_SESSION['user_name']); ?><br><br>
                    </h3>

                    <div class="action-buttons">
                        <a href="<?= htmlspecialchars($url) ?>" download="<?= htmlspecialchars($audio['original_name']) ?>" target="_blank">
                            <i class="fa-solid fa-download"></i>
                        </a>

                        <button type="button" class="delete-btn"
                                onclick="openDeleteModal('<?= $audio['file_id'] ?>')">
                            <i class="fa-solid fa-trash"></i>
                        </button>

                        <a href="<?= PAGES_URL ?>comments_page.php?image=<?= urlencode($url) ?>&project_id=<?= $project_id ?>&file_id=<?= $audio['file_id'] ?>">
                            <i class="fa-solid fa-comment"></i>
                        </a>

                        <?php include(INCLUDES_PATH . 'kebab.php'); ?>
                    </div>
                </center>
            </div>
            <?php endif; ?>
    <?php endwhile; ?>

<?php else: ?>

    <!-- EMPTY STATE (CONSISTENT ACROSS APP) -->
    <div class="empty-state">
        <div class="empty-box">
            <i class="fa-solid fa-photo-film empty-icon"></i>

            <h2>No audio yet</h2>
            <p>Add your first asset to get started with this project.</p>

            <a href="<?= PAGES_URL ?>file_upload.php" class="add-asset-btn">
                <i class="fa-solid fa-plus"></i> Add Asset
            </a>
        </div>
    </div>

<?php endif; ?>


<!-- DELETE CONFIRMATION MODAL -->
<div id="deleteModal" class="modal-overlay">
    <div class="modal-box">
        <h3>Are you sure?</h3>
        <p>This action cannot be undone. The asset will be permanently deleted.</p>
        <div class="modal-actions">
            <button class="cancel-btn" onclick="closeDeleteModal()">Cancel</button>
            <button class="confirm-btn" id="confirmDeleteBtn">Delete</button>
        </div>
    </div>
</div>
