<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
$out =     new Output();
$menu =    new Menu();
$wochentag=array("Sonntag","Montag","Dienstag","Mittwoch", "Donnerstag","Freitag","Samstag");
$wahltext=array("nicht reagiert","bestätigt","abgelehnt","offen","Storno/Erledigt");
$wahl=-1;
$msg="";
if (isset($_POST['wahl0'])) $wahl=0;
if (isset($_POST['wahl1'])) $wahl=1;
if (isset($_POST['wahl2'])) $wahl=2;
if (isset($_POST['wahl3'])) $wahl=3;
if (isset($_POST['wahl4'])) $wahl=4;

if ($wahl >=0) {
	// $request="update rad_abholung set `status`='".$wahl."',`info`='".$_POST['info']."' where `recnum`='".$_POST['recnum']."'";
	$request="update rad_abholung set `status`='".$wahl."',`info`='".$_POST['info']."',`info_abholung`='".$_POST['info_abholung']."' where `recnum`='".$_POST['recnum']."'";
	if ($db->query($request))  {	
		$msg="<h2>Das Rad ist jetzt markiert als ".$wahltext[$wahl]."</h2>";
	} else {
		$msg="<h2>Es konnte der Status leider nicht geändert werden.</h2>";
	}
}

if (!empty($_POST['changed'])) {
	$request="update rad_abholung set `changed`='0' where `recnum`='".$_POST['recnum']."'";
	if ($db->query($request))  {	
		$msg="<h2>Der Infos und Adressen sind jetzt als gelesen markiert</h2>";
	} else {
		$msg="<h2>Die Infos konnnten nicht als gelesen markiert werden</h2>";
	}
}

$html="";
$html.='<center>';
$html.=$msg;
$html.='<table cellspacing=0 id="liste">';
$request ="select changed,rad_abholung.info,info_abholung,rebikeid,concat(vorname,' ',nachname) as name,status,rad_abholung.recnum as recnum,abholtermin_soll,pos,mail,concat(strasse,', ',plz,' ',ort) as adresse, rebikeid, rad_abholung.info, concat(marke,' ',modell) as fahrradname from `rad_abholung`"; 
$request.=" left join rad_kunde";
$request.=" on rad_kunde.recnum = rad_abholung.kundenr";
$request.=" left join rad_rad";
$request.=" on rad_rad.recnum = rad_abholung.radnr";
$request.=" where `rad_abholung`.`recnum`='".$_POST['recnum']."'";
$request.=" order by pos,fahrradname";
// echo $request."<br>";
$result=$db->query($request);
if ($result) {
	$row=$result->fetch_assoc();
	$dt=new DateTime($row['abholtermin_soll']);
	$abholtermin=$wochentag[$dt->format("w")].", ".$dt->format("d.m.Y H:i");
	$html.='<tr><td><b>Name</b></td><td>'.$row['name'].'</td></tr>';
	$html.='<tr><td><b>Mail</b></td><td>'.$row['mail'].'</td></tr>';
	$html.='<tr><td><b>Adresse</b></td><td>'.$row['adresse'].'</td></tr>';
	$html.='<tr><td><b>Abholtermin </b></td><td>'.$abholtermin.'</td></tr>';
	$html.='<tr><td><b>Fahrrad</b></td><td>'.$row['fahrradname'].'</td></tr>';
	$html.='<tr><td><b>Bike ID</b></td><td>'.$row['rebikeid'].'</td></tr>';
	$html.='<form action="change_terminstatus.php" method="POST">';
	$html.='<tr><td valign="top"><b>Rad Info<br>(Kurzinfo Online)</b></td><td>';
	$html.='<textarea style="width:20em;" rows=5 name="info">'.$row['info'].'</textarea>';
	$html.='</td></tr>';
	$html.='<tr><td valign="top"><b>Abhol Info<br>für Abholschein</b></td><td>';
	$html.='<textarea style="width:20em;" rows=5 name="info_abholung">'.$row['info_abholung'].'</textarea>';
	$html.='</td></tr>';
	$html.='<tr><td valign="top"><b>Auswahl</b></td><td>';
	$html.='<input type="hidden" name="recnum" value="'.$_POST['recnum'].'">';
	$html.='<input id="status0" type="submit" name="wahl0" value="nicht reagiert"><br>';
	$html.='<input id="status1" type="submit" name="wahl1" value="bestätigt">&nbsp;&nbsp;&nbsp;';
	$html.='<input id="status2" type="submit" name="wahl2" value="abgelehnt"><br>';
	$html.='<input id="status3" type="submit" name="wahl3" value="offen">&nbsp;&nbsp;&nbsp;';
	$html.='<input id="status4" type="submit" name="wahl4" value="Storno/Erledigt"><br>';
	$html.='</td>';
	$html.='</tr>';
	
	if ($row['changed'] > 0) { 	
		$html.='<tr><td valign="top">';
		if ($row['changed'] &1) {
			$html.='<b>Infos gesehen ?</b><br>';
		}
		if ($row['changed'] &2) {
			$html.='<b>Adresse gechecked ?</b><br>';
		}
		$html.='<b>und alles erledigt?</b></br>';
		
		$html.='</td><td>';
		$html.='<input type="submit" name="changed" value="Ja">';
		$html.='</td>';
		$html.='</tr>';
	}
	
	$html.='</form>';

} else {
	$html.="Fehler beim Laden der Kundendaten!";
}
$html.='</table>';
$html.='<form action="terminstatus.php" method="POST">';
$html.='<input type="submit" name="weiter" value="weiter">';
$html.='</form>';
$html.='</center>';


echo $out->header();
echo $menu->out("5. Status der Termine ändern");
echo $html;
echo $out->footer();

?>
