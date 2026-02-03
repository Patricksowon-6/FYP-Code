<?php 
require_once(__DIR__ . '/../config.php');  

$user_id = $_SESSION['user_id'] ?? 0;
$project_id = $_SESSION['project_id'];

if ($user_id) {
    $stmt = $conn->prepare("SELECT project_id FROM projects WHERE user_id = ? ORDER BY project_id DESC LIMIT 1");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $project_id = $row['project_id'];
    }
}
?>

<body class="preload">
    <header class="header">
        <button class="header__button" id="btnNav" type="button">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div class="title"><h1>FYP Management System</h1></div>
        <nav>
            <ul>
                <li><a href="#">About</a></li>
                <li><a href="#">Gallery</a></li>
                <li><a href="<?= PAGES_URL; ?>profile.php">Profile</a></li>
                <li><a href="<?= PAGES_URL ?>logout.php">Log Out</a></li>
            </ul>
        </nav>
    </header>

    <nav class="nav">
        <div class="nav__links">
            <a href="<?= PAGES_URL ?>logged_in.php" class="nav__link">
                <i class="fa-solid fa-house"></i>&emsp;
                Home
            </a>
            <a class="nav__link" href="<?= PAGES_URL; ?>projects.php">
                <i class="fa-solid fa-film"></i>&emsp;
                Projects
            </a>
            <!-- <a class="nav__link" href="<?= PAGES_URL; ?>whiteboard.php">
                <i class="fa-solid fa-chalkboard"></i>&emsp;
                Whiteboard
            </a>
            <a class="nav__link" href="<?= PAGES_URL; ?>calendar.php">
                <i class="fa-solid fa-calendar"></i>&emsp;
                Calendar
            </a>
            <a class="nav__link" href="<?= PAGES_URL; ?>task_board.php">
                <i class="fa-regular fa-clipboard"></i>&emsp;
                Task Board
            </a> -->
            <a class="nav__link" href="<?= PAGES_URL; ?>components.php">
                <i class="fa-solid fa-folder-open"></i>&emsp;
                Component Viewer
            </a>
            <!-- Updated Media Manager button -->
            <a class="nav__link" href="<?= PAGES_URL ?>media.php?type=images&project_id=<?= $project_id ?>">
                <i class="fa-solid fa-camera"></i>&emsp;
                Media Manager
            </a>
            <a class="nav__link" href="<?= PAGES_URL; ?>scene_assets.php">
                <i class="fa-regular fa-clipboard"></i>&emsp;
                Scene Assets
            </a>
        </div>
        <div class="nav__overlay"></div>
    </nav>
    <script src="<?= JS_PATH; ?>header.js"></script>
</body>
