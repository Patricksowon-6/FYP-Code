<?php
$project_id = $_SESSION['project_id'] ?? 0;
$type = $type ?? 'images';

$media_types = [
    'images' => ['icon' => 'fa-solid fa-image', 'label' => 'Images'],
    'audio' => ['icon' => 'fa-solid fa-headphones', 'label' => 'Audio'],
    'docs' => ['icon' => 'fas fa-stream', 'label' => 'Documents'],
    'videos' => ['icon' => 'fa-solid fa-video', 'label' => 'Video'],
    'model_art' => ['icon' => 'fa-solid fa-pen-ruler', 'label' => 'Models & Art'],
    'other' => ['icon' => 'fa-solid fa-globe', 'label' => 'Other'],
    'pending' => ['icon' => 'fa-solid fa-inbox', 'label' => 'Pending'],
];
?>

<div class="sidebar">
    <ul class="main-menu">
        <?php foreach ($media_types as $key => $data): ?>
            <li>
                <a class="category-btn <?= ($type === $key) ? 'active' : '' ?>"
                   href="<?= PAGES_URL ?>media.php?type=<?= $key ?>&project_id=<?= $project_id ?>">
                    <i class="<?= $data['icon'] ?>"></i>&emsp;<?= $data['label'] ?>
                </a>
            </li>
        <?php endforeach; ?>

        <li>
            <button type="button" id="openFilterBtn" class="category-btn">
                <i class="fa-solid fa-filter"></i>&emsp;Search/ Filter
            </button>
        </li>
        <li>
            <a href="<?= PAGES_URL; ?>file_upload.php"><i class="fa fa-upload"></i>&emsp;Upload</a>
        </li>
    </ul>
</div>
