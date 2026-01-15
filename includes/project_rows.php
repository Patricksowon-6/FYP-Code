<?php 
require_once(__DIR__ . '/../config.php');

$user_id = $_SESSION['user_id'] ?? 0;
if (!$user_id) die("User not logged in.");

// Fetch ALL projects the user can access:
// 1. Projects they own
// 2. Projects shared with them via project_users

$stmt = $conn->prepare("
    SELECT DISTINCT 
        p.project_id,
        pb.show_title,
        pb.description,
        pb.banner_img,
        CASE 
            WHEN p.user_id = ? THEN 'Owner'
            ELSE pu.role 
        END AS access_role
    FROM projects p

    -- Load latest banner
    LEFT JOIN project_banner pb 
        ON pb.banner_id = (
            SELECT banner_id 
            FROM project_banner 
            WHERE project_id = p.project_id 
            ORDER BY created_at DESC 
            LIMIT 1
        )

    -- Shared access
    LEFT JOIN project_users pu
        ON pu.project_id = p.project_id 
        AND pu.user_id = ?

    WHERE p.user_id = ?   -- user owns the project
       OR pu.user_id = ?  -- OR user is shared with

    ORDER BY p.project_id DESC
");

$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Helper: generate Supabase URL for project bucket
function supabase_public_url($project_id, $file_path) {
    if (!$file_path) return '';
    $bucket_name = "project_" . $project_id;
    return rtrim(SUPABASE_URL, '/') . "/storage/v1/object/public/{$bucket_name}/{$file_path}";
}
?>

<h1>Project Dashboard</h1>

<div class="card-container">
    <?php while ($card = $result->fetch_assoc()):
        $banner_img = $card['banner_img'] ?? '';
        $url = $banner_img 
            ? supabase_public_url($card['project_id'], $banner_img) 
            : IMG_PATH . "placeholder.png";

        $project_link = PAGES_URL . "project_page.php?project_id=" . $card['project_id'];
    ?>
        <a href="<?= $project_link ?>">
            <div class="box">

                <!-- Banner -->
                <div class="img">
                    <img src="<?= $url ?>" alt="<?= htmlspecialchars($card['show_title'] ?? 'Project Banner'); ?>">
                </div>

                <!-- Title + Description -->
                <div class="content">
                    <h2><?= htmlspecialchars($card['show_title'] ?? 'Untitled'); ?></h2>
                    <p><?= htmlspecialchars($card['description'] ?? 'No description'); ?></p>
                </div>

            </div>
        </a>
    <?php endwhile; ?>
</div>

<a href="<?= BASE_URL ?>handlers/reset_form.php" class="open-btn">+ Create New Project</a>
