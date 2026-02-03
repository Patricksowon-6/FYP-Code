<?php 
    require_once(__DIR__ . '/../config.php');  
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
            <a href="<?= PAGES_URL ?>logged_in.php" class="nav__link nav__link">
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
        </div>
        <div class="nav__overlay"></div>
    </nav>
    <script src="<?= JS_PATH; ?>header.js"></script>
</body>
