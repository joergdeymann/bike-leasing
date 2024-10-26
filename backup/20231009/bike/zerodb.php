<?php
include "class/dbconnect.php";
include "class/class_menu.php";
include "class/class_output.php";

function multi_copy($files, $source) {
	foreach($files as $f) {
		if (!copy($source.$f, $f)) {
			return false;
		}
	}
	return true;
}

$out =     new Output();
$menu =    new Menu();
echo $out->header();
echo $menu->out("X. Rad-Datenbank auf 0 Zurücksetzen");
echo '<center>';
if (isset($_POST['zeroDB'])) {
	$request="TRUNCATE `rad_abholung`;";
	$result=$db->query($request);
	$request="TRUNCATE `rad_kunde`;";
	$result=$db->query($request);
	$request="TRUNCATE `rad_rad`;";
	$result=$db->query($request);
	echo "Rad-Datenbank wurde gelöscht!!";
}

$files=array("abholschein_vorlage.php","abholschein_vorlage_kopf.php");
if (isset($_POST['copy_at24'])) {
	$source = 'vorlagen/design_original/';
	if (multi_copy($files,$source)) {
		echo "Original Vorlage All-Transport24 geladen";
	} else {
		echo "Original Vorlage konnte nicht geladen werden";
	}
}
if (isset($_POST['copy_neu'])) {
	$source = 'vorlagen/design_joerg/';
	if (multi_copy($files,$source)) {
		echo "Vorlage Design Jörg geladen";
	} else {
		echo "Vorlage Design Jörg konnte nicht geladen werden";
	}
}
		

echo '<form method="POST" action="zerodb.php">';
echo '<input type="submit" value="Jetzt LÖSCHEN" name="zeroDB">';
echo '<br>';
echo '<br>';
echo '<input type="submit" value="Vorlage Alltransport24" name="copy_at24">';
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<input type="submit" value="Vorlage Neu" name="copy_neu">';
echo '<br>';
echo '<br>';
echo '</form>';
echo '</center>';
echo $out->footer();
?>
