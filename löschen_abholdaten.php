<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
include "class/class_abholung.php";

$out =     new Output();
$menu =    new Menu();

if (!empty($_POST['deldate'])) {
	$abh=new Abholung();
	$dt=new DateTime($_POST['deldate']);
	$request ="delete from rad_abholung where erfassung='".$dt->format("Y-m-d")."'";
	if ($abh->query($request)) {
		$msg="Es wurden ".$db->affected_rows." der Abholdaten unwiederbringlich gelöscht.";
		$err=false;
	} else {
		$msg="Fehler beim Löschen der Abholdaten";
		$err=true;
	}

echo $out->header();
echo $menu->out("Löschen der Abholdaten");
echo $out->err($msg,$err);
echo '<center>';
echo '<form action="loeschen_abholdaten.php" method="POST">';
echo '<table id="input"><tr><th>Löschen der Daten von:<th>';
echo '<td><input type="date" name="deldate"></td><tr>';
echo '</table>';
}
echo '</form></center>';


}
?>
