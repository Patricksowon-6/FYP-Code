<?php 
    require_once(__DIR__ . '/../config.php');  
?>

<body class="preload">
    <header class="header">
        <button class="header__button" id="btnNav" type="button">
            <i class="material-icons">menu</i>
        </button>
        <nav>
            <ul>
                <li><a href="#">About</a></li>
                <li><a href="#">Gallery</a></li>
                <li><a href="#">Art</a></li>
                <li><a href="<?= PAGES_URL; ?>sign_in_page.php">Sign In / Up</a></li>
            </ul>
        </nav>
    </header>

    <nav class="nav">
        <div class="nav__links">
            <a href="#" class="nav__link nav__link">
                <i class="material-icons">dashboard</i>
                Dashboard
            </a>
            <a class="nav__link" href="#">
                <i class="material-icons">source</i>
                Projects
            </a>
            <a class="nav__link" href="#">
                <i class="material-icons">lock</i>
                Security
            </a>
            <a class="nav__link" href="#">
                <i class="material-icons">history</i>
                History
            </a>
            <a class="nav__link" href="#">
                <i class="material-icons">person</i>
                Profile
            </a>
        </div>
        <div class="nav__overlay"></div>
    </nav>
    <script src="<?= JS_PATH; ?>header.js"></script>
</body>
