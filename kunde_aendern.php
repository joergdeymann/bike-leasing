<?php
session_start();
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_kunde.php";
include "class/class_logfile.php";

$out =     new Output();
$kunde =   new Kunde($db);
$msg="";
$start1=false;
if (empty($_POST['mail_alt'])) {
	$_POST['mail_alt']=$_POST['mail'];
	$start1=true;
}
//=================================================================
// Kontrolle ob Richtiger Kunde
//=================================================================
if (empty($_SESSION['mail'])) {
	header("location:kunde_bestaetigung.php");
	exit;
}


//=================================================================
// Daten ändern
//=================================================================
if (isset($_POST['change'])) {
	$a=array("vorname","nachname","firmaname","mail","tel1","tel2","strasse","plz","ort","recnum");
	$row=array();
	$liste="";
	foreach($a as $k => $v) {
		$row[$v]=$_POST[$v];
		if ($v != "recnum") {
			$liste.=$v."=".$row[$v]."\r\n";
		}
	}
	if ($kunde->update($row)) {
		$msg="Daten erfolgreich geändert";
		$_POST['mail_alt']=$_POST['mail'];		
		$request="UPDATE `rad_abholung` set  `changed`=(changed | 2) where `kundenr`='".$_POST['recnum']."'";
		$db->query($request);
		
		
		// Loggen Adressänderung
		
		$log=new Logfile($db);
		$logrow=array();
		$logrow['tabelle_recnum']=$row['recnum'];
		$logrow['tabelle']="rad_kunde";
		$logrow['feldname']="Adresse";
		$logrow['wert_neu']=$liste;
		$logrow['user']=$row['recnum'];
		$log->add($logrow);
		
		
		
	} else {
		$msg="Keine Daten verändert";
	}
	$html ="";
	$html.='<center>';
	$html.='<h2>'.$msg.'</h2>';
	$html.='<form action="kunde_bestaetigung.php" method="POST">';
	$html.='<input type="submit" value="zur Bestätigung" >';
	$html.='<input name="recnum"   type="hidden" value="'.$_POST['recnum'].'">';
	$html.='<input name="mail_alt" type="hidden" value="'.$_POST['mail_alt'].'">';
	$html.='<input name="rebikeid" type="hidden" value="'.$_POST['rebikeid'].'">';
	$html.='</form>';
	$html.='</center>';
	
} else {
	//=================================================================
	// Laden der Daten beim ersten Start
	//=================================================================
	if ($start1) {
		$kunde->loadByRecnum($_POST['recnum']);
		$a=array("vorname","nachname","firmaname","mail","tel1","tel2","strasse","plz","ort","recnum");
		foreach($a as $k => $v) {
			$_POST[$v]=$kunde->row[$v];
		}
	}
		

	$html="";
	$html.='<center>';
	$html.='<h2>'.$msg.'</h2>';
	$html.='<form action="kunde_aendern.php" method="POST">';
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
	$html.='<input name="recnum"   type="hidden" value="'.$_POST['recnum'].'">';
	$html.='<input name="mail_alt" type="hidden" value="'.$_POST['mail_alt'].'">';
	$html.='<input name="rebikeid" type="hidden" value="'.$_POST['rebikeid'].'">';
	$html.='<input name="change" type="submit" value="ändern">';
	$html.='<br><br><input type="submit" value="zurück ohne Änderung" formaction="kunde_bestaetigung.php" formmethod="POST">';
	$html.='</form>';

	$html.='</center>';
}

echo $out->header("Bestätigung der Adresse und Räder");
echo $out->kopf();
echo $html;
echo $out->footer();
	
?>
