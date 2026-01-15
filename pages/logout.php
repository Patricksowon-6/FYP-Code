<?php


if(isset($_SESSION['user_id']))
{
	unset($_SESSION['user_id']);
	unset($_SESSION['project_id']);
}

header("Location: ../");
die;

?>