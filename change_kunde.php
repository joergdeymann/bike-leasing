<?php
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_kunde.php";
include "class/class_menu.php";
include "class/class_logfile.php";

$out =     new Output();
$kunde =   new Kunde($db);
$menu= new Menu();

$msg="";
$start1=false;
// echo "Erfassung=".$_POST['erfassung']."<br>";

//=================================================================
// Aufruf von change_terminstatus
//=================================================================
if (isset($_POST['kunde_recnum_start'])) {
	$_POST['kunde_recnum']=$_POST['kunde_recnum_start'];
	//=================================================================
	// Laden der Daten beim ersten Start
	//=================================================================
	$kunde->loadByRecnum($_POST['kunde_recnum']);
	$a=array("vorname","nachname","firmaname","mail","tel1","tel2","strasse","plz","ort");
	foreach($a as $k => $v) {
		$_POST[$v]=$kunde->row[$v];
	}

} else

//=================================================================
// Daten ändern
//=================================================================
if (isset($_POST['change'])) {
	$a=array("vorname","nachname","firmaname","mail","tel1","tel2","strasse","plz","ort");
	$row=array();
	$liste="";
	foreach($a as $k => $v) {
		$row[$v]=$_POST[$v];
		$liste.=$v."=".$row[$v]."\r\n";
	}
	$row['recnum']=$_POST['kunde_recnum'];
	
	if ($kunde->update($row)) {
		$msg="Daten erfolgreich geändert";
		$_POST['mail_alt']=$_POST['mail'];		
		$request="UPDATE `rad_abholung` set  `changed`=(changed | 2) where `kundenr`='".$_POST['kunde_recnum']."'";
		$db->query($request);
		
		
		// Loggen Adressänderung
		
		$log=new Logfile($db);
		$logrow=array();
		$logrow['tabelle_recnum']=$_POST['kunde_recnum'];
		$logrow['tabelle']="rad_kunde";
		$logrow['feldname']="Adresse";
		$logrow['wert_neu']=$liste;
		$logrow['user']='0';
		$log->add($logrow);
		
		
		
	} else {
		$msg="Keine Daten verändert";
	}
	$html ="";
	$html.='<center>';
	$html.='<h2>'.$msg.'</h2>';
	$html.='<form action="change_terminstatus.php" method="POST">';
	$html.='<input type="submit" value="zum Terminstatus" >';
	$html.='<input name="recnum"   type="hidden" value="'.$_POST['recnum'].'">';
	// $html.='<input name="mail_alt" type="hidden" value="'.$_POST['mail_alt'].'">';
	// $html.='<input name="rebikeid" type="text" value="'.$_POST['rebikeid'].'">';
	$html.='<input type="hidden" name="erfassung" value="'.$_POST['erfassung'].'">';    // Erfassung aus terminstatus durchschleifen
	$html.='</form>';
	$html.='</center>';
	
} else {
	$html="";
	$html.='<center>';
	$html.='<h2>'.$msg.'</h2>';
	$html.='<form action="change_kunde.php" method="POST">';
	$html.='<table id="liste">';
	$html.='<tr><td><b>Vorname</b></td><td><input name="vorname" style="width:400px" type="text" value="'.$_POST['vorname'].'"></tr>';
	$html.='<tr><td><b>Nachname</b></td><td><input name="nachname" style="width:400px" type="text" value="'.$_POST['nachname'].'"></tr>';
	$html.='<tr><td><b>Firma</b></td><td><input name="firmaname" style="width:400px" type="text" value="'.$_POST['firmaname'].'"></tr>';
	$html.='<tr><td><b>Mail</b></td><td><input name="mail" style="width:400px" type="text" value="'.$_POST['mail'].'"></tr>';
	$html.='<tr><td><b>Telefon 1</b></td><td><input name="tel1" style="width:400px" type="text" value="'.$_POST['tel1'].'"></tr>';
	$html.='<tr><td><b>Telefon 2</b></td><td><input name="tel2" style="width:400px" type="text" value="'.$_POST['tel2'].'"></tr>';
	$html.='<tr><td><b>Straße</b></td><td><input name="strasse" style="width:400px" type="text" value="'.$_POST['strasse'].'"></tr>';
	$html.='<tr><td><b>PLZ Ort</b></td><td><input name="plz" style="width:50px" type="text" value="'.$_POST['plz'].'">&nbsp;<input name="ort" style="width:400px" type="text" value="'.$_POST['ort'].'"></tr>';
	$html.='</table>';
	$html.='<input name="kunde_recnum"   type="hidden" value="'.$_POST['kunde_recnum'].'">';
	$html.='<input type="hidden" name="recnum" value="'.$_POST['recnum'].'">';          // Recnum aus terminstatus durchschleifen
	$html.='<input type="hidden" name="erfassung" value="'.$_POST['erfassung'].'">';    // Erfassung aus terminstatus durchschleifen
	// $html.='<input name="mail_alt" type="hidden" value="'.$_POST['mail_alt'].'">';
	// $html.='<input name="rebikeid" type="hidden" value="'.$_POST['rebikeid'].'">';
	$html.='<input name="change" type="submit" value="ändern">';
	$html.='<br><br><input type="submit" value="zurück ohne Änderung" formaction="change_terminstatus.php" formmethod="POST">';
	$html.='</form>';

	$html.='</center>';
}
$title="Änderung der Kundenaddresse";
echo $out->header($title);
echo $menu->out($title);
// echo $out->kopf();
echo $html;
echo $out->footer();
	
?>
