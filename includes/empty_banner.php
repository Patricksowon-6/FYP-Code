<?php 
    require_once(__DIR__ . '/../config.php');
?>

<div id="banner-body">
    <div class="left">
        <ul>
            <li class="active animationTop">
                <i class="fa-solid fa-house-user"></i> <!-- Have this part be the themes & genres the show is based on. the user chooses -->
                Theme/ Genre with emoji/ motif
            </li>
            <li class="animationTop delay-01">
                <i class="fa-solid fa-star"></i>
                Theme/ Genre with emoji
            </li>
            <li class="animationTop delay-02">
                <i class="fa-solid fa-comment-medical"></i>
                Theme/ Genre with emoji
            </li>
            <li class="animationTop delay-03">
                <i class="fa-solid fa-circle-info"></i>
                Theme/ Genre with emoji
            </li>
        </ul>
    </div>
    <div class="center">
        <div class="bigTitle animationTop delay-04">Welcome Back, <?php echo $_SESSION['user_name']; ?></div>
            <div class="banner">
                <img src="<?= IMG_PATH; ?>Boss Idea 1.gif" class="animationTop delay-05">
                <div class="content">
                    <div class="title animationTop delay-06">
                        Show Title <br>
                        Banner Image
                    </div>
                </div>
            </div>
        <div class="bigTitle animationTop delay-15">Brief Description</div>
            <div class="listFigure">
                <div class="item animationTop delay-16">
                    <div class="img">
                        <img src="<?= IMG_PATH; ?>Cavern Entrance Idea 1.gif" alt="">
                    </div>
                    <div class="content"> 
                        <p>
                            Lorem ipsum dolor sit, amet consectetur adipisicing elit. Debitis aut possimus necessitatibus placeat, quia rerum.
                            Lorem ipsum dolor sit, amet consectetur adipisicing elit. Debitis aut possimus necessitatibus placeat, quia rerum.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="right">
            <div class="director animationTop delay-18">
                <button class="editBtn BTN" id="openModalBtn">
                    <img src="<?= IMG_PATH; ?>Me.jpg" alt="Edit"> 
                </button>
                
                <div class="title">Profile Picture</div>
                <ul>
                    <li>
                        Your Role
                    </li>
                    <li>
                        User Position
                    </li>
                </ul>
            </div>
            <!-- actor -->
            <div class="actor">
                <div class="bigTitle animationTop delay-19">Extra Images</div> <!-- Images that the user wishes to display. Perhaps could be a favourite character, an image that reminds them of something, an image that recalls an important note, etc -->
                <ul>
                    <li class="animationTop delay-2">
                        <img src="<?= IMG_PATH ?>DGM5 Idea 1.jpg">
                    </li>

                    <li class="animationTop delay-21">
                        <img src="user2.PNG">
                    </li>

                    <li class="animationTop delay-22">
                        <img src="user3.PNG">
                    </li>

                    <li class="animationTop delay-23">
                        <img src="user4.PNG">
                    </li>

                    <li class="animationTop delay-24">
                        <img src="user5.PNG">
                    </li>
                </ul>
            </div>
        </div>
    </div>



<script src="<?= JS_PATH; ?>banner.js"></script>
