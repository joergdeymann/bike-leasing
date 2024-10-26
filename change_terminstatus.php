<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
include "class/class_logfile.php";
$out =     new Output();
$menu =    new Menu();
$wochentag=array("Sonntag","Montag","Dienstag","Mittwoch", "Donnerstag","Freitag","Samstag");
$wahltext=array("nicht reagiert","bestätigt","abgelehnt","offen","geklaut","Storno/erledigt");
$wahl=-1;
$msg="";
$err=false;

if (isset($_POST['wahl0'])) $wahl=0;
if (isset($_POST['wahl1'])) $wahl=1;
if (isset($_POST['wahl2'])) $wahl=2;
if (isset($_POST['wahl3'])) $wahl=3;
if (isset($_POST['wahl4'])) $wahl=4;
if (isset($_POST['wahl5'])) $wahl=5;

// echo $wahl;

if ($wahl >=0 or !empty($_POST['change'])) {
	// $request="update rad_abholung set `status`='".$wahl."',`info`='".$_POST['info']."' where `recnum`='".$_POST['recnum']."'";
	$request="update rad_abholung set `status`='".$wahl."',`info`='".$_POST['info']."',`info_abholung`='".$_POST['info_abholung']."',`user`='2'";
	$request.="	where `recnum`='".$_POST['recnum']."'";
	$request.=" and ("; 
	$request.="      `info_abholung` <> '".$_POST['info_abholung']."'";
	$request.=" or   `info` <> '".$_POST['info']."'";
	$request.=" or   `status` <> '".$wahl."'";
	$request.=")"; 

// echo $request;	
	if ($db->query($request))  {	
		$msg="<h2>Das Rad ist jetzt markiert als ".$wahltext[$wahl]."</h2>";
	} else {
		$msg="<h2>Es konnte der Status leider nicht geändert werden.</h2>";
		$err=true;
	}
	
	if (!$err) {
		// Loggen Adressänderung
		
		$log=new Logfile($db);
		$logrow=array();
		$logrow['tabelle_recnum']=$_POST['recnum'];
		$logrow['tabelle']="rad_abholung";
		$logrow['feldname']="status";
		$logrow['wert_neu']=$wahl;
		$logrow['user']=0;
		$log->add($logrow);

		$logrow=array();
		$logrow['tabelle_recnum']=$_POST['recnum'];
		$logrow['tabelle']="rad_abholung";
		$logrow['feldname']="info_abholung";
		$logrow['wert_neu']=$_POST['info_abholung'];
		$logrow['user']=0;
		$log->add($logrow);

		$logrow=array();
		$logrow['tabelle_recnum']=$_POST['recnum'];
		$logrow['tabelle']="rad_abholung";
		$logrow['feldname']="info";
		$logrow['wert_neu']=$_POST['info'];
		$logrow['user']=0;
		$log->add($logrow);
	}
}

if (!empty($_POST['save'])) {
	// $request="update rad_abholung set `status`='".$wahl."',`info`='".$_POST['info']."' where `recnum`='".$_POST['recnum']."'";
	$request="update rad_abholung set `info`='".$_POST['info']."',`info_abholung`='".$_POST['info_abholung']."',`user`='2' where `recnum`='".$_POST['recnum']."'";
	$request.=" and (";
	$request.="`info_abholung` <> '".$_POST['info_abholung']."'";
	$request.=" or `info` <> '".$_POST['info']."'";
	$request.=")";
	if ($db->query($request))  {	
		$msg="<h2>Infodaten wurden geändert</h2>";
	} else {
		$msg="<h2>Infodaten konnten leider nicht geändert werden.</h2>";
		$err=true;
	}
	
	if (!$err) {
		// Loggen Adressänderung
		
		$log=new Logfile($db);

		$logrow=array();
		$logrow['tabelle_recnum']=$_POST['recnum'];
		$logrow['tabelle']="rad_abholung";
		$logrow['feldname']="info_abholung";
		$logrow['wert_neu']=$_POST['info_abholung'];
		$logrow['user']=0;
		$log->add($logrow);

		$logrow=array();
		$logrow['tabelle_recnum']=$_POST['recnum'];
		$logrow['tabelle']="rad_abholung";
		$logrow['feldname']="info";
		$logrow['wert_neu']=$_POST['info'];
		$logrow['user']=0;
		$log->add($logrow);

	}
	
	
}

// Was ist das jetzt
// Bedeutet: änderung gesehen 
if (!empty($_POST['changed'])) {
	$request="update rad_abholung set `changed`='0' where `recnum`='".$_POST['recnum']."'";
	if ($db->query($request))  {	
		$msg="<h2>Der Infos und Adressen sind jetzt als gelesen markiert</h2>";
	} else {
		$msg="<h2>Die Infos konnnten nicht als gelesen markiert werden</h2>";
		$err=true;
	}
}


if (!empty($_POST['weiter'])) {
	$request ="update rad_abholung";
	$request.=" set `info`='".$_POST['info'];
	$request.="',`info_abholung`='".$_POST['info_abholung'];
	$request.="',`user`='2'"; //geändert vom Admin
	$request.=" where `recnum`='".$_POST['recnum']."'";
	$request.=" and `info_abholung` <> '".$_POST['info_abholung']."'";
	$request.=" and `info` <> '".$_POST['info_abholung']."'";

	if ($db->query($request))  {	
		$msg="<h2>Infodaten wurden geändert</h2>";
		$_SESSION['eintritt']=$_POST['eintritt'];
		$_SESSION['recnum']=$_POST['recnum'];

		// Loggen Adressänderung
		$log=new Logfile($db);

		$logrow=array();
		$logrow['tabelle_recnum']=$_POST['recnum'];
		$logrow['tabelle']="rad_abholung";
		$logrow['feldname']="info_abholung";
		$logrow['wert_neu']=$_POST['info_abholung'];
		$logrow['user']=0;
		$log->add($logrow);

		$logrow=array();
		$logrow['tabelle_recnum']=$_POST['recnum'];
		$logrow['tabelle']="rad_abholung";
		$logrow['feldname']="info";
		$logrow['wert_neu']=$_POST['info'];
		$logrow['user']=0;
		$log->add($logrow);

		
		header('location:terminstatus.php#'.$_POST['recnum']);
		exit;
 	} else {
		$msg="<h2>Infodaten konnten leider nicht geändert werden.</h2>";
	}
	
	
}	

	


$html="";
$html.='<center>';
$html.=$msg;
$html.='<form action="change_terminstatus.php" method="POST">';

$request ="select changed,rad_abholung.info,info_abholung,rebikeid,concat(vorname,' ',nachname) as name,status,rad_abholung.recnum as recnum,abholtermin_soll,pos,mail,concat(strasse,', ',plz,' ',ort) as adresse, rebikeid, rad_abholung.info, concat(marke,' ',modell) as fahrradname,rad_kunde.recnum as kunde_recnum from `rad_abholung`"; 
$request.=" left join rad_kunde";
$request.=" on rad_kunde.recnum = rad_abholung.kundenr";
$request.=" left join rad_rad";
$request.=" on rad_rad.recnum = rad_abholung.radnr";
$request.=" where `rad_abholung`.`recnum`='".$_POST['recnum']."'";
$request.=" order by pos,fahrradname";
// echo $request."<br>";
$result=$db->query($request);
if ($result) {
	$html.='<table cellspacing=0 id="liste">';
	$row=$result->fetch_assoc();
	$dt=new DateTime($row['abholtermin_soll']);
	$abholtermin=$wochentag[$dt->format("w")].", ".$dt->format("d.m.Y H:i");
	$html.='<tr><td><b>Name</b></td><td>'.$row['name'].'</td></tr>';
	$html.='<tr><td><b>Mail</b></td><td>'.$row['mail'].'</td></tr>';
	$html.='<tr><td><b>Adresse</b></td><td>'.$row['adresse'].'</td></tr>';
	$html.='<tr><td><b>Abholtermin </b></td><td>'.$abholtermin.'</td></tr>';
	$html.='<tr><td><b>&nbsp; </b></td><td>';
	$html.='<input type="hidden" name="kunde_recnum_start" value="'.$row['kunde_recnum'].'">';
	$html.='<input type="submit" value="Kundendaten ändern" formmethod="POST" formaction="change_kunde.php">';
	$html.='</td></tr>';
	
	$html.='<tr><td><b>Fahrrad</b></td><td>'.$row['fahrradname'].'</td></tr>';
	$html.='<tr><td><b>Bike ID</b></td><td id="status'.$row['status'].'">'.$row['rebikeid'].'</td></tr>';

	$html.='<tr><td valign="top"><b>Rad Info<br>(Kurzinfo Online)</b></td><td>';
	$html.='<textarea style="width:20em;" rows=5 name="info">'.$row['info'].'</textarea>';
	$html.='</td></tr>';
	$html.='<tr><td valign="top"><b>Abhol Info<br>für Abholschein</b></td><td>';
	$html.='<textarea style="width:20em;" rows=5 name="info_abholung">'.$row['info_abholung'].'</textarea>';
	$html.='</td></tr>';
	$html.='<tr><td valign="top"><b>Auswahl</b></td><td>';
	$html.='<input type="hidden" name="recnum" value="'.$_POST['recnum'].'">';
	$html.='<input type="hidden" name="erfassung" value="'.$_POST['erfassung'].'">';
	// $html.='<input name="mail_alt" type="hidden" value="'.$_POST['mail_alt'].'">';
	// $html.='<input name="rebikeid" type="text" value="'.$_POST['rebikeid'].'">';
	
	$html.='<input id="status0" type="submit" name="wahl0" value="nicht reagiert"><br>';
	$html.='<input id="status1" type="submit" name="wahl1" value="bestätigt">&nbsp;&nbsp;&nbsp;';
	$html.='<input id="status2" type="submit" name="wahl2" value="abgelehnt"><br>';
	$html.='<input id="status3" type="submit" name="wahl3" value="offen">&nbsp;&nbsp;&nbsp;';
	$html.='<input id="status4" type="submit" name="wahl4" value="geklaut"><br>';
	$html.='<input id="status5" type="submit" name="wahl5" value="Storno"><br>';
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

	$html.='<tr><td valign="top">';	
	$html.='<b>Info Daten ändern</b><br>';
	$html.='</td><td>';
	$html.='<input type="submit" name="save" value="ändern">';
	$html.='</td>';
	$html.='</tr>';
	$html.='</table>';
	
	
// 	$html.='</form>';

} else {
	$html.="Fehler beim Laden der Kundendaten!";
}
//$html.='<form action="terminstatus.php#'.$_POST['recnum'].'" method="POST">';
$html.='<input type="submit" name="weiter" value="weiter">';
$html.='<input type="hidden" name="erfassung" value="'.$_POST['erfassung'].'">';
$html.='</form>';
$html.='</center>';


echo $out->header();
echo $menu->out("5. Status der Termine ändern");
echo $html;
echo $out->footer();

?>
