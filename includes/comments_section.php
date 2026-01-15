<?php
require_once(__DIR__ . '/../config.php');

// Fetch user
$username   = $_SESSION['user_name'] ?? 'Guest';
$user_id    = $_SESSION['user_id'] ?? 0;
$project_id = $_SESSION['project_id'] ?? null;
$file_id    = intval($_GET['file_id'] ?? 0);

// Fetch project banner
$stmt = $conn->prepare("SELECT * FROM project_banner WHERE project_id = ? LIMIT 1");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$banner = $stmt->get_result()->fetch_assoc();

// Fetch asset category, original name, and file path
$stmt = $conn->prepare("SELECT category, original_name, path FROM user_files WHERE file_id = ? LIMIT 1");
$stmt->bind_param("i", $file_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

// Supabase public URL helper
function supabase_public_url($path) {
    if (!$path) return '';
    return rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . $path;
}

// Get file URL from GET (used for images, audio, video) or fallback to DB path
$asset_url = $_GET['image'] ?? $category['path'] ?? null;

// Determine asset type and placeholder image
$asset_type  = $category['category'] ?? null;
$asset_image = null;

if ($asset_type) {
    switch ($asset_type) {
        case 'models':
            $asset_image = IMG_PATH . 'model_icon.png';
            break;
        case 'other':
            $asset_image = IMG_PATH . 'other_icon.png';
            break;
        // images, audio, videos, documents will use $asset_url directly
    }
}

// Helper: get file extension safely
$ext = $asset_url ? strtolower(pathinfo($asset_url, PATHINFO_EXTENSION)) : '';
?>

<div class="content" style="margin-top: 100px;">
    <div class="comments-layout">

        <?php if ($asset_type): ?>
            <div class="left-image">
                <h1><?= htmlspecialchars($category['original_name']) ?></h1>

                <?php if ($asset_type === 'images' && $asset_url): ?>
                    <img src="<?= htmlspecialchars($asset_url) ?>" class="clicked-image" alt="Asset preview">

                <?php elseif ($asset_type === 'audio' && $asset_url): ?>
                    <audio controls class="media-player">
                        <source src="<?= htmlspecialchars($asset_url) ?>" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>

                <?php elseif ($asset_type === 'videos' && $asset_url): ?>
                    <video controls class="media-player" style="width:100%; max-width:700px; height:auto;">
                        <source src="<?= supabase_public_url($asset_url) ?>" type="video/mp4">
                        Your browser does not support the video element.
                    </video>

                <?php elseif ($asset_type === 'documents' && $asset_url): ?>
                    <?php if ($ext === ['pdf', 'docx', 'doc','docx','xls','xlsx','ppt']): ?>
                        <iframe src="<?= supabase_public_url($asset_url) ?>" width="500px" height="700px" style="border:none;"></iframe>
                    <?php elseif (in_array($ext, ['doc','docx','xls','xlsx','ppt','pptx'])): ?>
                        <iframe src="https://docs.google.com/gview?url=<?= urlencode(supabase_public_url($asset_url)) ?>&embedded=true" width="500px" height="700px" style="border:none;"></iframe>
                    <?php else: ?>
                        <img src="<?= IMG_PATH . 'other_icon.png' ?>" class="clicked-image" alt="Document placeholder">
                    <?php endif; ?>

                <?php elseif ($asset_image): ?>
                    <img src="<?= htmlspecialchars($asset_image) ?>" class="clicked-image" alt="Asset icon">

                <?php else: ?>
                    <p style="color:#777;">Preview not available</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <section class="comment-section">
            <div class="comment-box">
                <img src="<?= htmlspecialchars(supabase_public_url($banner['profile_img'] ?? '')) ?>" alt="User avatar" class="comment-avatar">

                <div class="comment-input">
                    <span class="comment-username"><?= htmlspecialchars($username) ?></span>
                    <textarea placeholder="Add a comment..."></textarea>
                    <button id="send-comment">Send</button>
                </div>
            </div>

            <div id="comments-list" data-file-id="<?= $file_id ?>" data-user-id="<?= $user_id ?>"></div>
        </section>
    </div>
</div>