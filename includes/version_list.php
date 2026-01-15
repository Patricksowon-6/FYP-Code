<?php
require_once __DIR__ . '/../config.php';

/* ============================
   VALIDATE INPUT
============================ */

$user_id = $_SESSION['user_id'] ?? 0;
$file_id = isset($_GET['file_id']) ? (int) $_GET['file_id'] : 0;

if (!$user_id || !$file_id) {
    echo "Missing parameters.";
    exit;
}

/* ============================
   1. FETCH MAIN FILE
============================ */

$stmt = $conn->prepare("
    SELECT 
        file_id,
        path,
        uploaded_at,
        original_name,
        category
    FROM user_files
    WHERE file_id = ? AND user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $file_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();
$file = $result->fetch_assoc();

$stmt->close();

if (!$file) {
    echo "File not found or access denied.";
    exit;
}

$file_path     = $file['path'];
$uploaded_at   = $file['uploaded_at'];
$original_name = $file['original_name'];
$category      = $file['category'];

/* ============================
   2. FETCH FILE VERSIONS
============================ */

$versions = [];

$stmt = $conn->prepare("
    SELECT path, uploaded_at
    FROM file_versions
    WHERE file_id = ?
    ORDER BY version_id ASC
");
$stmt->bind_param("i", $file_id);
$stmt->execute();

$result = $stmt->get_result();

$version_number = 1;

while ($row = $result->fetch_assoc()) {
    $versions[] = [
        "version" => $version_number++,
        "date"    => date("M d, Y", strtotime($row['uploaded_at'])),
        "url"     => rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . $row['path']
    ];
}

$stmt->close();

/* ============================
   3. ADD CURRENT FILE AS LATEST
============================ */

$versions[] = [
    "version" => $version_number,
    "date"    => date("M d, Y", strtotime($uploaded_at)),
    "url"     => rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . $file_path
];
?>

<div class="container">
    <h1 class="heading">File Versions</h1>

    <div class="file-info">
        <div class="file-info-left">
            <strong>File:</strong> <?= htmlspecialchars($original_name) ?><br>
            <strong>Latest Version:</strong> V<?= count($versions) ?><br>
        </div>
        <div class="file-info-right">
            <?php if ($category === 'images'): ?>
                <img src="<?= $public_url_main ?>" alt="<?= htmlspecialchars($original_name) ?>">

            <?php elseif ($category === 'models'): ?>
                <img src="<?= IMG_PATH ?>model_icon.png" alt="model_icon">

            <?php elseif ($category === 'documents'): ?>
                <img src="<?= IMG_PATH ?>document_icon.png" alt="model_icon">

            <?php elseif ($category === 'audio'): ?>
                <img src="<?= IMG_PATH ?>audio_icon.png" alt="model_icon">
            
            <?php elseif ($category === 'other'): ?>
                <img src="<?= IMG_PATH ?>audio_icon.png" alt="model_icon">

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
            <a href="<?= $version["url"] ?>" class="download-btn" target="_blank">Download</a>
        </div>
    <?php endforeach; ?>
</div>
