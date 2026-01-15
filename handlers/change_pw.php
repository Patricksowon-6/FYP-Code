<?php
    function make_change($conn)
	{
		$new_pw = $_SESSION['new_password'];
        $username = $_SESSION['user_name'];

        $sql = "UPDATE Users 
                SET password = $new_pw 
                WHERE user_name = '$username'";
        $result = mysqli_query($conn, $sql);

		if ($result) {
			header("Location: index.php");
        	die;
		}
		echo "<script>window.alert('Error Occurred When Trying To Change Password!')</script>";	
	}
?>