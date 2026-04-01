<?php
require_once __DIR__ . '/../config.php';

$user_id = $_SESSION['user_id'] ?? 0;
$file_id = isset($_GET['file_id']) ? (int) $_GET['file_id'] : 0;

if (!$user_id || !$file_id) {
    echo "Missing parameters.";
    exit;
}

/* ============================
   FETCH ALL VERSIONS (INCLUDING CURRENT FILE)
============================ */

$stmt = $conn->prepare("
    SELECT path, uploaded_at FROM (
        SELECT path, uploaded_at
        FROM user_files
        WHERE file_id = ? AND user_id = ?
        
        UNION ALL
        
        SELECT path, uploaded_at
        FROM file_versions
        WHERE file_id = ?
    ) AS combined
    ORDER BY uploaded_at DESC
");
$stmt->bind_param("iii", $file_id, $user_id, $file_id);
$stmt->execute();
$result = $stmt->get_result();

$versions = [];
$version_number = 1;

while ($row = $result->fetch_assoc()) {
    $versions[] = [
        "version" => $version_number++,
        "date"    => date("M d, Y g:i:s A", strtotime($row['uploaded_at'])),
        "url"     => rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . $row['path']
    ];
}

$stmt->close();

/* ============================
   FETCH MAIN FILE INFO
============================ */

$stmt = $conn->prepare("
    SELECT original_name, category
    FROM user_files
    WHERE file_id = ? AND user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();
$file_info = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>

<div class="container">
    <h1 class="heading">File Versions</h1>

    <div class="file-info">
        <div class="file-info-left">
            <strong>Latest Version:</strong> V<?= $versions[0]['version'] ?><br>
            <strong>Original/ First Version:</strong> V<?= end($versions)['version'] ?><br>
        </div>
        <div class="file-info-right">
            <?php
            $category = $file_info['category'];
            if ($category === 'images'): ?>
                <img src="<?= $versions[0]['url'] ?>" alt="<?= htmlspecialchars($file_info['original_name']) ?>">
            <?php elseif ($category === 'models'): ?>
                <img src="<?= IMG_PATH ?>model_icon.png" alt="model_icon">
            <?php elseif ($category === 'documents'): ?>
                <img src="<?= IMG_PATH ?>document_icon.png" alt="document_icon">
            <?php elseif ($category === 'audio'): ?>
                <img src="<?= IMG_PATH ?>audio_icon.png" alt="audio_icon">
            <?php elseif ($category === 'videos'): ?>
                <img src="<?= IMG_PATH ?>video_icon.png" alt="video_icon">
            <?php else: ?>
                <img src="<?= IMG_PATH ?>other_icon.png" alt="other_icon">
            <?php endif; ?>
        </div>
    </div>

    <?php foreach ($versions as $version): ?>
        <div class="version">
            <div class="version-left">
                <span class="version-number">Version <?= $version["version"] ?></span>
                <span class="version-meta">
                    Uploaded on <?= $version["date"] ?>
                </span>
            </div>
            <div class="version-right">
                <a href="<?= $version["url"] ?>" class="download-btn" target="_blank">Delete</a> &emsp;
                <a href="<?= $version["url"] ?>" class="download-btn" target="_blank">Download</a>
            </div>
        </div>
    <?php endforeach; ?>
</div>