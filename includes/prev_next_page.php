<?php
require_once(__DIR__ . '/../config.php'); 

// Make sure $type is defined before including this file
$type = $type ?? 'images'; 
$items_per_page = 12; 
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Count total items for this type
$countStmt = $conn->prepare("SELECT COUNT(*) AS total FROM user_files WHERE category = ?");
$countStmt->bind_param("s", $type);
$countStmt->execute();
$total_items = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;
$total_pages = ceil($total_items / $items_per_page);

// Calculate offset
$offset = ($page - 1) * $items_per_page;

// Fetch assets for current page
$type_safe = $conn->real_escape_string($type);

$query = "
    SELECT uf.file_id, uf.bucket, uf.path, uf.original_name, u.user_name
    FROM user_files uf
    JOIN users u ON uf.user_id = u.user_id
    WHERE uf.category = '$type_safe'
    ORDER BY uf.uploaded_at DESC
    LIMIT $items_per_page OFFSET $offset
";

$result = $conn->query($query);

// Expose variables to the including file
$pagination = [
    'page' => $page,
    'total_pages' => $total_pages,
    'items' => $result
];


?>

<!-- Pagination links -->
<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?type=<?= $type ?>&page=<?= $page - 1 ?>" class="page-btn">&laquo; Prev</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?type=<?= $type ?>&page=<?= $i ?>" 
           class="page-btn <?= ($i === $page) ? 'active' : '' ?>">
           <?= $i ?>
        </a>
    <?php endfor; ?>

    <?php if ($page < $total_pages): ?>
        <a href="?type=<?= $type ?>&page=<?= $page + 1 ?>" class="page-btn">Next &raquo;</a>
    <?php endif; ?>
</div>