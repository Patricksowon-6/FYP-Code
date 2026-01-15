<?php
    require_once(__DIR__ . '/../config.php');
    require_once(HANDLER_PATH . 'functions.php');

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="<?= CSS_PATH ?>login.css">
</head>
<body>
    <div class="container" id="container">
        <div class="form-container sign-in">
            <form method="post" action="change_password.php">
                <h2>Change your password</h2>

                <center><br><h3>Enter Your Email To Get Password Change Link:</h3><br></center>
                <input type="email" name="email" required placeholder="Email...">

                <center><input type="submit" name="change" value="Change My Password" id="sign-in"></center>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-right">
                    <h1>Already Have An Account?</h1>
                    <a href="<?= PAGES_URL; ?>sign_in_page.php" id="back">Go Login Now</a>
                </div>
            </div>
        </div>
    </div><br><br><br><br><br><br><br>
    <script src="index.js"> </script>

</body>
</html>
