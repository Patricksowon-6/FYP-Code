<?php
require_once(__DIR__ . '/../config.php');

$user_id = $_SESSION['user_id'] ?? 0;
$project_id = $_SESSION['project_id'] ?? 0;
if (!$user_id) exit("Not logged in");

// Fetch latest images only
$stmt = $conn->prepare("
    SELECT uf.file_id, uf.path, uf.original_name, u.user_name
    FROM user_files uf
    JOIN users u ON uf.user_id = u.user_id
    WHERE uf.user_id = ? AND uf.category = 'images'
    ORDER BY uf.uploaded_at DESC
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$uploads = [];
while ($row = $result->fetch_assoc()) {
    $row['url'] = rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/" . $row['path'];
    $uploads[] = $row;
}


    // Fetch all projects for this user with latest banner info
    $stmt = $conn->prepare("
        SELECT p.project_id, pb.show_title, pb.description, pb.banner_img
        FROM projects p
        LEFT JOIN project_banner pb 
            ON pb.banner_id = (
                SELECT banner_id 
                FROM project_banner 
                WHERE project_id = p.project_id 
                ORDER BY created_at DESC 
                LIMIT 1
            )
        WHERE p.user_id = ?
        ORDER BY p.project_id DESC
        LIMIT 3
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Helper: generate Supabase URL for project bucket
    function supabase_public_url($project_id, $file_path) {
        if (!$file_path) return '';
        $bucket_name = "project_" . $project_id;
        return rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/{$bucket_name}/{$file_path}";
    }
?>

<center>
    <h1 class="title">Recent Projects <br><br></h1>
</center>

<div class="card-container">
    <?php while ($card = $result->fetch_assoc()):
        $banner_img = $card['banner_img'] ?? '';
        $url = $banner_img ? supabase_public_url($card['project_id'], $banner_img) : IMG_PATH . "placeholder.png";
        $project_link = PAGES_URL . "project_page.php?project_id=" . $_SESSION['project_id'];
    ?>
        <a href="<?= $project_link ?>">
            <div class="box">
                <div class="img">
                    <img src="<?= $url ?>" alt="<?= htmlspecialchars($card['show_title'] ?? 'Project Banner'); ?>">
                </div>
                <div class="content">
                    <h2><?= htmlspecialchars($card['show_title'] ?? 'Untitled'); ?></h2>
                    <p><?= htmlspecialchars($card['description'] ?? 'No description'); ?></p>
                </div>
            </div>
        </a>
    <?php endwhile; ?>
</div>

<center>
    <h1 class="title">Recent Images <br><br></h1>
</center>

<div class="slider">
    <figure>
        <?php foreach ($uploads as $file): ?>
            <div class="slide">
                <img src="<?= htmlspecialchars($file['url']) ?>" alt="<?= htmlspecialchars($file['original_name']) ?>">
            </div>
        <?php endforeach; ?>
        <?php foreach ($uploads as $file): ?>
            <div class="slide">
                <img src="<?= htmlspecialchars($file['url']) ?>" alt="<?= htmlspecialchars($file['original_name']) ?>">
            </div>
        <?php endforeach; ?>
    </figure>
</div>



