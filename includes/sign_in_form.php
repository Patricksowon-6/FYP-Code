<?php
    require_once(__DIR__ . '/../config.php');
    require_once(HANDLER_PATH . 'create_account.php');
    require_once(HANDLER_PATH . 'sign_in_up.php');
    
    if (isset($_POST["enter"])) 
    {
        create_account($conn);
    }
    if (isset($_POST['signin'])) 
    {
        sign_in($conn);
    }
?>


<h2 class="heading">Get Back Into The Fold!</h2>

<div class="container" id="container">
    <div class="form-container sign-up-container">

        <form action="sign_in_page.php" method="post">
            <h1>Create Account</h1>

            <div class="social-container">
                <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
            </div>

            <span>or use your email for registration</span>

            <input type="text" name="full_name" required placeholder="Name">
            <input type="text" name="user_name" required placeholder="Username">
            <input type="email" name="email" required placeholder=" Email">
            <input type="password" placeholder="Password" name="password" required>
            <input type="password" name="retyped_password" required placeholder="Retype Password">

            <center><input type="submit" name="enter" value="Create An Account" id="sign-in"></center>
        </form>
    </div>
    <div class="form-container sign-in-container">

        <form action="sign_in_page.php" method="post">
            <h1>Sign in</h1>

            <div class="social-container">
                <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
            </div>

            <span>or use your account</span>

            <input type="text" placeholder="Username" name="user_name" required />
            <input type="password" placeholder="Password" name="password" required />
            <a href="<?= PAGES_URL; ?>change_password.php" id="forgot">Forgot your password?</a>

            <button style="cursor: pointer;" name="signin" id="sign-in">Sign In</button>
            <a href="<?= BASE_URL ?>index.php" id="forgot">Home</a>
        </form>
    </div>
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                
                <h1>Welcome Back!</h1>
                <p>To keep connected with us, login with your information</p>
                <button class="ghost" id="signIn">Sign In</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>New Here? Welcome!</h1>
                <p>Enter your details and start your journey with us!</p>
                <button class="ghost" id="signUp" name="enter">Sign Up</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= JS_PATH; ?>sign_in_page.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
