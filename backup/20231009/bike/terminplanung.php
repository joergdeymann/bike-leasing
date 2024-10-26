<?php
/*
TODO:

*/
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
// include "class/class_import.php";
// include "class/class_kunde.php";
// include "class/class_rad.php";
// include "class/class_abholung.php";
include "class/class_menu.php";

//
// Markierte Tage an denen Abgeholt wird analysieren und den nächsten verfügbaren raussuchen
// Wen kein Tag verfügbar ist (alle nicht markiert sind) dann den Originalen wieder zurückgeben
//
function getWorkday($dt) {
	$dt_pre=$dt;
	$c=0;
	while ($c<7) {
		$w='wochentag'.$dt->format("w");
		if (empty($_POST[$w])) {			
			$dt->modify("1 day");
		} else {
			break;
		}
		$c++;
	}
	if ($c== 7) {
		return $dt_pre;
	} else {
		return $dt;
	}
}

// $kunde =   new Kunde($db);
// $rad =     new Rad($db);
// $abholung= new Abholung($db);
$out =     new Output();
$menu =    new Menu();
$msg="";

$wochentag=array("Sonntag","Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");


$html="";
if (isset($_POST['del'])) {
	$request="delete from rad_abholung where `recnum`='".$_POST['recnum']."'";
	$result=$db->query($request);
	$msg.="Einen Datensatz gelöscht";
}

if (isset($_POST['anpassen']) or isset($_POST['reset'])) {
	if (isset($_POST['anpassen'])) {
		$termin=(new DateTime($_POST['date']." ".$_POST['time'].":00"))->format("Y-m-d H:i:s");
		$dt=new DateTime($_POST['date']." ".$_POST['time'].":00");
		// $termin->modify("-".." minutes");
		$dt->modify("-".$_POST['fahrzeit']." minutes");
		$termin=$dt->format("Y-m-d H:i:s");
		
	} else {
		$termin="0000-00-00 00:00:00";
	}
		
	$request="update rad_abholung set abholtermin_soll='".$termin."' where recnum='".$_POST['recnum']."'";
	
	if ($result=$db->query($request)) {
		$msg="Abholungstermin von ".$_POST['vorname']." ".$_POST['nachname']." erfolgreich geändert";
	} else {
		$msg="Konnte Abholngstermin von ".$_POST['vorname']." ".$_POST['nachname']." nicht ändern";
		$_POST['change']=true;
	}	
		
}
if (isset($_POST['undo'])) {
	$request="update `rad_abholung` set `abholtermin_soll` = '0000-00-00 00:00:00' where `erfassung`='".$_POST['erfassung']."'";
	if ($db->query($request)) {
		$msg="Zeitreset erfrolgreich";
	}
}


if (isset($_POST['change']) and !empty($_POST['recnum'])) {
	$request ="SELECT * from rad_abholung";
	$request.=" LEFT JOIN rad_kunde";
	$request.=" ON rad_abholung.kundenr=rad_kunde.recnum";
	$request.=" WHERE rad_abholung.kundenr ='".$_POST['recnum']."'" ;
	$request.=" GROUP BY rad_abholung.kundenr";
// echo $request;

	$result=$db->query($request);
	$row=$result->fetch_assoc();
	$dt=new DateTime($_POST['datetime']);
	
	$html="";
	$html.='<center>';

	if (!empty($msg)) {
		$html.=$msg;
	}
	
	$html.='<form action="terminplanung.php" method="POST">';
	$html.='<table>';
	$html.='<tr><th>Name</th><td>'.$row['vorname']." ".$row['nachname'].'</td></tr>';
	$html.='<tr><th>Mail</th><td>'.$row['mail'].'</td></tr>';
	$html.='<tr><th>Abholdatum:</th><td><input name="date" type="date" value="'.$dt->format("Y-m-d").'">&nbsp;<input name="time" type="time" value="'.$dt->format("H:i").'"></td></tr>';
	$html.='</table>';
	$html.='<input type="submit" value="anpassen" name="anpassen">';
	$html.='<input type="submit" value="Termin zurücksetzen" name="reset">';
	$html.='<input type="hidden" value="'.$row['vorname'].'"  name="vorname">';
	$html.='<input type="hidden" value="'.$row['nachname'].'" name="nachname">';
	

	$html.='<input type="hidden" name="recnum" value="'.$_POST['recnum'].'">';
	$html.='<input type="hidden" name="erfassung" value="'.$_POST['erfassung'].'">';
	$html.='<input type="hidden" name="abholung_start" value="'.$_POST['abholung_start'].'">';
	$html.='<input type="hidden" name="max_zeit" value="'.$_POST['max_zeit'].'">';
	$html.='<input type="hidden" name="max_rad" value="'.$_POST['max_rad'].'">';
	$html.='<input type="hidden" name="fahrzeit" value="'.$_POST['fahrzeit'].'">';
	$w=array("1"=>"Montag", "2"=>"Dienstag", "3"=>"Mittwoch", "4"=>"Donnerstag", "5"=>"Freitag", "6"=>"Samstag","0"=>"Sonntag");	
	foreach ($w as $k=>$v) {
		$checked="";
		$tag="wochentag".$k;
		if (!empty($_POST[$tag])) {
			$v="1";
		} else {
			$v="0";
		}
		$html.= '<input type="hidden" value="'.$v.'" name="'.$tag.'">';
	}	
	
	// hidden input
	// hidden input ende
	$html.='</form>';
	$html.='</center>';
	echo $out->header();
	echo $menu->out("4. Terminplanung");
	echo $html;	
	echo $out->footer();
	exit;
	
}
	


if (empty($_POST['erfassung'])) {
	$_POST['erfassung']=(new DateTime())->format("Y-m-d");
	$_POST['wochentag1']="1";
	$_POST['wochentag2']="1";
	$_POST['wochentag3']="1";
	$_POST['wochentag4']="1";
	$_POST['wochentag5']="1";
	$_POST['wochentag6']="1";
	
}
if (empty($_POST['abholung_start'])) {
	$dt=new DateTime();
	$dt->modify("+1 day");
	// $dt=getWorkday($dt);
	$_POST['abholung_start']=$dt->format("Y-m-d");
}
if (empty($_POST['max_zeit'])) {
	$_POST['max_zeit']="08:00";
}
if (empty($_POST['max_rad'])) {
	$_POST['max_rad']="20";
}

// Eingaben
/*
if (!empty($msg)) {
	$html.=$msg;
}
*/
$html.= '<form action="terminplanung.php" method="POST">';
$html.= '<table border=1>';
$html.= '<tr>';
$html.= '<th>Datum der Erfassung der Fahrräder</th>';
$html.= '<td><input type="date" name="erfassung" value="'.$_POST['erfassung'].'"></td>';
$html.= '</tr>';
$html.= '<tr>';
$html.= '<th>Begin der Abholung</th>';
$html.= '<td><input type="date" name="abholung_start" value="'.$_POST['abholung_start'].'"></td>';
$html.= '</tr>';
$html.= '<tr>';
$html.= '<th>Maximale Fahrzeit pro Tag</th>';
$html.= '<td><input type="time" name="max_zeit" value="'.$_POST['max_zeit'].'"></td>';
$html.= '</tr>';
$html.= '<tr>';
$html.= '<th>Maximale Anzahl von Fahrräder pro Tag</th>';
$html.= '<td><input style="width:4em;" type="number" name="max_rad" value="'.$_POST['max_rad'].'"></td>';
$html.= '</tr>';
$html.= '<tr>';
$html.= '<th>Abholtage</th>';
$html.= '<td>';

$w=array("1"=>"Montag", "2"=>"Dienstag", "3"=>"Mittwoch", "4"=>"Donnerstag", "5"=>"Freitag", "6"=>"Samstag","0"=>"Sonntag");	
foreach ($w as $k=>$v) {
	$checked="";
	$tag="wochentag".$k;
	if (!empty($_POST[$tag])) {
		$checked="checked";
	}
	$html.= '<input type="checkbox" value="1" name="'.$tag.'" '.$checked.'>'.$v.'<br>';
}	
$html.= '</td>';
$html.= '</tr>';
$html.= '</table>';
$html.= '<input type="submit" value="Anzeigen">';
$html.= '&nbsp;&nbsp;&nbsp;<input name="save" type="submit" value="Einstellungen übernehmen">';
$html.= '&nbsp;&nbsp;&nbsp;<input name="undo" type="submit" value="Fixe Zeiten zurücksetzen">';
$html.= '</form>';

//
// liste
//
$html.='<table id="liste">';
$html.='<tr><th>Pos</th><th>Abholdatum - Zeit</th><th>Action</th><th>Name</th><th>Mail</th><th>Räder</th></tr>';

$dt=new DateTime($_POST['erfassung']);
$request="SELECT strasse,plz,ort,rad_abholung.recnum as recnum,pos,abholtermin_soll,fahrzeit,km,vorname,nachname,CONCAT(vorname,' ',nachname) as name,mail,count(kundenr) as anz,kundenr from rad_abholung";
$request.=" left join rad_kunde"; 
$request.=" on rad_kunde.recnum = rad_abholung.kundenr"; 
$request.=" where erfassung='".$dt->format("Y-m-d")."'";
//XX$request.=" and pos>0";
// $request.=" group by kundenr";
$request.=" group by rad_kunde.vorname,rad_kunde.nachname,rad_kunde.plz,rad_kunde.strasse";
// $request.=" group by rad_kunde.plz,rad_kunde.strasse";
$request.=" order by pos";

// echo $request."<br>";
$dt_last=new DateTime("2000-01-01 00:00:00");
$lastdt="";
$fahrzeit_summe=0;
$fahrrad_summe=0;

$z=explode(":",$_POST['max_zeit']);
$fahrzeit_max=$z[0]*60+$z[1];
$fahrrad_max=$_POST['max_rad'];

$result=$db->query($request);
while ($row=$result->fetch_assoc()) {	
	if (($row['nachname'] == "Start") or ($row['nachname'] == "Ziel")) { // Startpunkt
		$row['anz']=0;
	}
	
	$mod="";
	// echo "*".$row['abholtermin_soll']."*<br>";
	// Wenn Fixes Datum
	if ($row['abholtermin_soll'] != '0000-00-00 00:00:00') {
		// Termin holen und mit der Auswahl anpassen
		$dt=getWorkday(new DateTime($row['abholtermin_soll']));

		$mod="*";	//Falls Abholungstermin bereits fix dieses Anzeigen
	} else {
		if (empty($lastdt)) {

			// Datum für den ersten Start aus eingabe vorbereiten
			$dt=getWorkday(new DateTime($_POST['abholung_start']." 00:00:00"));		
			$row['abholtermin_soll']=$dt->format("Y-m-d 00:00:00");
			// echo "Neuer Termin";
		} else {
			// Vorheringen Termin termin
			$row['abholtermin_soll']=$lastdt->format('Y-m-d H:i:s');
			$dt=$lastdt;
		}
	}

	
	//Erst mal den Wert setzten
	if (empty($lastdt)) {
		$lastdt=$dt;
	}

	//Abholzeit erhöhen
	$dt->modify("+".$row['fahrzeit']." minute");
	
	// Ehöhung der Fahrzeit und der Radzahl
	$fahrzeit_summe+=$row['fahrzeit'];
	$fahrrad_summe+=$row['anz'];

	// Maximum erreicht , dann Tageswechsel
	if (($fahrzeit_summe > $fahrzeit_max) or ($fahrrad_summe > $fahrrad_max)) {

		//echo "**".$fahrzeit_summe.">".$fahrzeit_max.'<br>';
		$fahrzeit_summe=$row['fahrzeit'];
		$fahrrad_summe=$row['anz'];

		if (empty($mod)) {
			$dt=new DateTime($dt->format("Y-m-d 00:00:00"));
			$dt->modify("+1 day");
			$dt->modify("+".$row['fahrzeit']." minutes");
			$dt=getWorkday($dt);
		}
		$lastdt=$dt; // Keine Prüfung mehr auf unterschiedliche Datums
	} else

	// Fahrzeit zurücksetzen wenn sich der Tag laut db ändert
	if ($dt->format("Y-m-d") != $lastdt->format("Y-m-d")) {
		// $mod="*";
		$lastdt  = $dt;
		$fahrzeit_summe=$row['fahrzeit'];
		$fahrrad_summe=$row['anz'];
		
		
		if (empty($mod)) {
			$dt=new DateTime($dt->format("Y-m-d H:i:s"));
			$dt->modify("+".$row['fahrzeit']." minutes");
			$dt=getWorkday($dt);
		}
	}


	$lastdt=$dt;
	$dt_raw=new DateTime($dt->format("Y-m-d H:i:s"));
	if ($row['abholtermin_soll'] != '0000-00-00 00:00:00') {
		// $dt_raw->modify("-".$row['fahrzeit']." minute");
	} else {
// echo $row['pos']."NULL<br>"; 
// exit;		
	}		

	if (isset($_POST['save'])) {
		// unprofessionell aber besser als Fehlkerhaft:
		// Ende unprofessionell
		
		$r ="select `recnum` from `rad_kunde`";
		$r.= " where `strasse` ='".$row['strasse']."'";
		$r.= " and `plz` ='".$row['plz']."'";
		$r.= " and `vorname` ='".$row['vorname']."'";
		$r.= " and `nachname` ='".$row['nachname']."'";
		$request = "update `rad_abholung` set `abholtermin_soll` = '".$dt_raw->format("Y-m-d H:i:s")."'";
		$request.= " where (`recnum`= ANY (".$r.") or `recnum`='".$row['recnum']."')";
		$request.= " and `erfassung` ='".$_POST['erfassung']."'";
		



		//#05.09.2023 $request = "update `rad_abholung` set `abholtermin_soll` = '".$dt_raw->format("Y-m-d H:i:s")."'";
		//#05.09.2023 $request.= " where recnum='".$row['recnum']."'";

		// $request.= " where kundenr='".$row['kundenr']."'";
		// $request.= " and erfassung='".$_POST['erfassung']."'";
		
		// Über die Adresse ist das mit der Zeit am besten
		// $request.= " where (erfassung='".$_POST['erfassung']."'";
		// $request.= " and strasse='".$row['strasse']."'";
		// $request.= " and plz='".$row['plz']."'";
		// $request.= " and ort='".$row['ort']."')";
		// $request.= " or (recnum='".$row['recnum']."')";
		// echo $request;

		if ($db->query($request)) {
			$mod="*";
			if ($msg=="") {
				$msg="Terminplanung festgelegt<br>";
			}

		} else {
			$msg.="Termin von ".$row['name']." konnte nicht festgelegt werden.<br>";
		}		
	}	


	$html.='<tr>';

	$html.='<td>'.$row['pos'].'</td>';
	$html.='<td>';
	if (!empty($mod)) {
		$html.='<b>';
	}
	$html.='<span style="display:inline-block;width:6em;">'.$wochentag[$dt->format("w")].'</span>';
	$html.=$dt->format("d.m.Y H:i");
	$html.=$mod;
	if (!empty($mod)) {
		$html.='</b>';
	}
	$html.='</td>';
	
	$html.='<form action="terminplanung.php" method="POST">';
	$html.='<td>';
	$html.='<input type="hidden" value="'.$row['recnum'].'" name="recnum">';
	$html.='<input type="hidden" value="'.$dt_raw->format("Y-m-d H:i:s").'" name="datetime">';
	$html.='<input type="hidden" name="erfassung" value="'.$_POST['erfassung'].'">';
	$html.='<input type="hidden" name="abholung_start" value="'.$_POST['abholung_start'].'">';
	$html.='<input type="hidden" name="max_zeit" value="'.$_POST['max_zeit'].'">';
	$html.='<input type="hidden" name="max_rad" value="'.$_POST['max_rad'].'">';
	$html.='<input type="hidden" name="fahrzeit" value="'.$row['fahrzeit'].'">';
	$w=array("1"=>"Montag", "2"=>"Dienstag", "3"=>"Mittwoch", "4"=>"Donnerstag", "5"=>"Freitag", "6"=>"Samstag","0"=>"Sonntag");	
	foreach ($w as $k=>$v) {
		$checked="";
		$tag="wochentag".$k;
		if (!empty($_POST[$tag])) {
			$v="1";
		} else {
			$v="0";
		}
		$html.= '<input type="hidden" value="'.$v.'" name="'.$tag.'">';
	}	
	
	
	$html.='<input type="submit" value="Zeit ändern" name="change">';
	$html.='<input type="submit" value="Löschen" name="del">';
	$html.='</td>';
	$html.='</form>';
	
	$html.='<td>'.$row['name'].'</td>';
	$html.='<td>'.$row['mail'].'</td>';
	$html.='<td>'.$row['anz'].'</td>';
	
	$html.='</tr>';
	
	if (isset($_POST['save'])) {
		$dt_raw=new DateTime($dt->format("Y-m-d H:i:s"));
		$dt_raw->modify("-".$row['fahrzeit']." minutes");

		$request = "update `rad_abholung` set `abholtermin_soll` = '".$dt_raw->format("Y-m-d H:i:s")."'";
		$request.= " where kundenr='".$row['kundenr']."'";
		$request.= " and erfassung='".$_POST['erfassung']."'";
		// $request.= " where recnum='".$row['recnum']."'";
		if ($db->query($request)) {
			if ($msg=="") {
				$msg="Terminplanung festgelegt<br>";
			}

		} else {
			$msg.="Termin von ".$row['name']." konnte nicht festgelegt werden.<br>";
		}		
	}	
	
}

$html.='</table>';
	
echo $out->header();
echo $menu->out("4. Terminplanung");
echo '<center>';
echo $msg.'<br>';
echo $html;	
echo '</center>';	
echo $out->footer();
?>
