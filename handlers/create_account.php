<?php
    function create_account($conn)
    {
        //PHP For creating an account with error checking
        if ($_SERVER["REQUEST_METHOD"] == "POST") 
        {
            if (isset($_POST["full_name"]) && 
				isset($_POST["user_name"]) && 
				isset($_POST["email"]) && 
				isset($_POST["password"]) && 
				isset($_POST["retyped_password"])) 
            {
                if ($_POST["password"] === $_POST["retyped_password"] && strlen($_POST["password"]) >= 8) 
                {
                    $email = filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL);
                    $sql = "SELECT * FROM Users WHERE email = '$email'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        echo "<script>window.alert('Email already taken! Try Another One!')</script>";
                    }
                    else
                    {
                        $full_name = strtolower(filter_input(INPUT_POST, "full_name", FILTER_SANITIZE_SPECIAL_CHARS)); 
                        $sql = "SELECT * FROM Users WHERE full_name = '$full_name'";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            echo "<script>window.alert('Full Name already taken! Try Another One!')</script>";
                        }
                        else
                        {
							$username = filter_input(INPUT_POST, "user_name", FILTER_SANITIZE_SPECIAL_CHARS);
							$sql = "SELECT * FROM Users WHERE user_name = '$username'";
							$result = $conn->query($sql);

							if ($result->num_rows > 0) {
								echo "<script>window.alert('Username already taken! Try Another One!')</script>";
							}

							else
							{
								$password = $_POST['password'] ?? '';
                                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

								$sql = "INSERT INTO Users (user_name, email, password, full_name) VALUES ('$username', '$email', '$hashedPassword', '$full_name')";
								mysqli_query($conn, $sql);

								$_SESSION['user_name'] = $_POST['user_name'];
								$_SESSION['user_id'] = $_POST['user_id'];
								header("Location: sign_in_page.php");
                                echo "<script>window.alert('Account Created! Use details to Log In!')</script>";
								die;
							}
                        }
                    }
                }
                else if($_POST["password"] != $_POST["retyped_password"])
                {
                    echo "<script>window.alert('Passwords do not match! Try Again!')</script>";
                }
                else if(strlen($_POST["password"]) < 8)
                {
                    echo "<script>window.alert('Passwords should be at least 8 characters long! Try Again!')</script>";
                }
            }
        }
    }

?>