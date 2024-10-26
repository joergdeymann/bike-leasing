<?php
session_start();
if (empty($_SESSION['user']) and basename($_SERVER['PHP_SELF']) != "menu.php") {
	header("location:menu.php");
}	
?>
