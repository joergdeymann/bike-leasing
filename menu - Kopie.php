<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";

$out =     new Output();
$menu =    new Menu();
$msg="";
$zeige_menu=false;
$pw="2023: Der 1. Januar ist Neu(Jahr)";
$pw="2023: D#1(Neu)!";
if (!empty($_SESSION['user'])) {
	$zeige_menu=true;
} else {
	if (!empty($_POST['user']) and !empty($_POST['pw'])) {
		if (($_POST['user'] == "Rasched Tamiz") and ($_POST['pw'] == $pw)) {
			$_SESSION['user']=$_POST['user'];
			$zeige_menu=true;
		}
	}
}	


echo $out->header();
echo $menu->out("0. Menu und Login",$zeige_menu);
if (empty($_SESSION['user'])) {
	echo '<center>';
	echo '<form method="POST" action="menu.php">';
	echo '<table id="liste">';
	echo '<tr><td><b>Login User</b></td><td><input style="width:200px;" type="text"     name="user"></td></tr>';
	echo '<tr><td><b>Passwort  </b></td><td><input style="width:200px;" type="password" name="pw"></td></tr>';
	echo '<tr><td><b>&nbsp;    </b></td><td><input value="OK"           type="submit"   name="ok"></td></tr>';
	echo '</table>';
	echo '</form>';
	echo '</center>';
}

echo $out->footer();

	

?>
