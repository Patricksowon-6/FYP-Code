<?php 
require_once(__DIR__ . '/../config.php');

// 1️⃣ Get project_id from URL
$project_id = $_GET['project_id'] ?? null;
if (!$project_id) die("Project ID not provided.");

// 2️⃣ Fetch the latest banner for this project
$stmt = $conn->prepare("
    SELECT * 
    FROM project_banner
    WHERE project_id = ?
    ORDER BY banner_id DESC
    LIMIT 1
");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$banner = $result->fetch_assoc();
if (!$banner) die("No banner found for this project.");



$stmt = $conn->prepare("
    SELECT role, user_position
    FROM project_users
    WHERE project_id = ? AND user_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $project_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$card = $result->fetch_assoc();


// 3️⃣ Supabase bucket
$project_bucket = "project_" . $project_id;

// 4️⃣ Helper: generate public URL
function supabase_public_url($bucket_name, $path) {
    if (!$path) return '';
    return rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/{$bucket_name}/{$path}";
}

// 5️⃣ Build URLs
$banner_img_url  = supabase_public_url($project_bucket, $banner['banner_img']);
$quote_img_url   = supabase_public_url($project_bucket, $banner['quote_img']);
$profile_img_url = supabase_public_url($project_bucket, $banner['profile_img']);
$circle_img_urls = [];
for ($i=1; $i<=5; $i++) {
    $circle_img_urls[$i] = supabase_public_url($project_bucket, $banner["circle_img_$i"] ?? null);
}
?>



<div id="banner-body">
    <div class="left">
        <ul>
            <li class="active animationTop">
                <?= htmlspecialchars($banner['emoji1']) ?><br><?= htmlspecialchars($banner['genre1']) ?>
            </li>
            <li class="animationTop delay-01">
                <?= htmlspecialchars($banner['emoji2']) ?><br><?= htmlspecialchars($banner['genre2']) ?>
            </li>
            <li class="animationTop delay-02">
                <?= htmlspecialchars($banner['emoji3']) ?><br><?= htmlspecialchars($banner['genre3']) ?>
            </li>
            <li class="animationTop delay-03">
                <?= htmlspecialchars($banner['emoji4']) ?><br><?= htmlspecialchars($banner['genre4']) ?>
            </li>
            <li class="animationTop delay-03">
                <?= htmlspecialchars($banner['emoji5']) ?><br><?= htmlspecialchars($banner['genre5']) ?>
            </li>
        </ul>
    </div>

    <div class="center">
        <div class="bigTitle animationTop delay-04">
            Welcome Back, <?= htmlspecialchars($_SESSION['user_name']) ?>
        </div>

        <div class="banner">
            <img src="<?= supabase_public_url($project_bucket, $banner['banner_img']); ?>" class="animationTop delay-05">
            <div class="content">
                <div class="title animationTop delay-06">
                    <?= htmlspecialchars($banner['show_title']) ?>
                </div>
            </div>
        </div>

        <div class="bigTitle animationTop delay-15">Description</div>

        <div class="listFigure">
            <div class="item animationTop delay-16">
                <div class="img">
                    <img src="<?= supabase_public_url($project_bucket, $banner['quote_img']); ?>" alt="">
                </div>
                <div class="content">
                    <p><?= htmlspecialchars($banner['description']); ?> </p>
                </div>
            </div>
        </div><br><br><br>

        <?php if ($card['role'] === 'Owner'): ?>
            <div class="listFigure">
                <div class="item animationTop delay-16">
                    <div class="buttons">
                        <button id="edit">Edit Banner</button>
                        &emsp; &emsp;
                        <button id="openDeleteModal" type="button">Delete Project</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="right">
        <div class="director animationTop delay-18">
            <button class="editBtn BTN" id="openModalBtn">
                <img src="<?= supabase_public_url($project_bucket, $banner['profile_img']); ?>" alt="Profile">
            </button>

            <div class="title"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
            <ul>
                <li><?= htmlspecialchars($card['user_position']) ?></li>
                <li><?= htmlspecialchars($card['role']) ?></li>
            </ul>
        </div>

        <div class="actor">
            <div class="bigTitle animationTop delay-19">Extra Images</div>
            <ul>
                <li class="animationTop delay-20">
                    <img src="<?= supabase_public_url($project_bucket, $banner['circle_img_1']); ?>">
                </li>
                <li class="animationTop delay-21">
                    <img src="<?= supabase_public_url($project_bucket, $banner['circle_img_2']); ?>">
                </li>
                <li class="animationTop delay-22">
                    <img src="<?= supabase_public_url($project_bucket, $banner['circle_img_3']); ?>">
                </li>
                <li class="animationTop delay-23">
                    <img src="<?= supabase_public_url($project_bucket, $banner['circle_img_4']); ?>">
                </li>
                <li class="animationTop delay-24">
                    <img src="<?= supabase_public_url($project_bucket, $banner['circle_img_5']); ?>">
                </li>
            </ul>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal-overlay" style="display:none;">
    <div class="modal-box">
        <h3>Delete Project?</h3>
        <p>This project will be permanently deleted in one week! After that, it's gone!</p>
        <form method="POST" action="<?= BASE_URL ?>handlers/delete_project.php">
            <button type="button">Cancel</button>
            <button type="submit" class="danger">Yes, Delete Project</button>
        </form>

    </div>
</div>