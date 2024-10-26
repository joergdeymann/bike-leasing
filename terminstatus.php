<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
include "class/class_abholung.php";

$out =     new Output();
$menu =    new Menu();
$abh =     new Abholung($db);
$wochentag=array("Sonntag","Montag","Dienstag","Mittwoch", "Donnerstag","Freitag","Samstag");

// Bei Fremdaufrufen wieder zurück an alter stelle springen, wenns nicht mit POST geht
if (!empty($_SESSION['eintritt'])) {
	$_POST['eintritt']=$_SESSION['eintritt'];
	unset ($_SESSION['eintritt']);
}
if (!empty($_SESSION['recnum'])) {
	$_POST['recnum']=$_SESSION['recnum'];
	unset ($_SESSION['recnum']);
}

if (empty($_POST['abholtermin'])) {
	$_POST['abholtermin']="";
}

$erfassung=$abh->getLastErfassung();

if (empty($_POST['erfassung'])) {
	$dt=new Datetime();
	$_POST['erfassung']=$dt->format("Y-m-d");
	$_POST['erfassung']="";
} else {
	$dt=new DateTime($_POST['erfassung']);
	$_POST['erfassung']=$dt->format("Y-m-d");	
}
// $csv = chr(255) . chr(254);
// $csv =  chr(239) . chr(187) . chr(191);
// $csv.="Abholdatum;Abholzeit;E-Mail;Adresse;Rebike ID;Bike Name;Info1;Info2;Status;Statustext\r\n";	
$csv ="UID;Nachname des Kunden;Abholdatum;Status;Grund bei Storno;Rahmennummer\r\n";

$html="";
$html ='<center><form action="terminstatus.php" method="POST">';
// $html.='Erfassungsdatum: <input name="erfassung" type="date" value="'.$dt->format("Y-m-d").'">';
$html.='<table id="input">';
$html.='<tr><td><b>Erfassungsdatum:</b><br>';
$html.='<i style="font size:12px;">leer = alle terminierten zukünftigen Abholungen</i>';
$html.='</td>';
$html.='<td style="vertical-align:top;padding-top:5px;"><input name="erfassung" type="date" value="'.$_POST['erfassung'].'"></td>';
$html.='</tr>';
$html.='<tr><td><b>Abholtag:</b><br>';
$html.='<i style="font size:12px;">leer = alle Tage</i>';
$html.='</td><td><input name="abholtermin" type="date" value="'.$_POST['abholtermin'].'"></td></tr>';
$html.='
<tr>
<th style="vertical-alig:top;">Status<br><i>was soll angezeit werden ?</i></th>';

$checked=array(0 => "",1 => "",2 => "",3 => "",4 => "",5=>"" ,11 => "");
if (isset($_POST['status'])) {
	foreach($_POST['status'] as $k=>$v) {
		// echo "<br>$k=>$v<br>";
		if (!is_null($_POST['status'][$k])) {
			$checked[$k]="checked";
		} else {
			$checked[$k]="";
		}
		//	if (isset($status[$k])) {
	}
} else {
	if (isset($_SERVER['HTTP_REFERER'])) {
		if (basename($_SERVER['HTTP_REFERER']) != basename($_SERVER['PHP_SELF'])) {
			$checked=array(0 => "checked",1 => "checked",2 => "",3 => "",4 => "",5=>"",11 => "");
		}
	} else {
		$checked=array(0 => "",1 => "",2 => "",3 => "",4 => "",5=>"",11 => "");
	}
}
$html.= '<td>   
<input type="checkbox" '.$checked[0].'        name="status[0]" value="0"> ungesehen<br> 
<input type="checkbox" '.$checked[1].'        name="status[1]" value="1"> bestätigt<br> 
<input type="checkbox" '.$checked[2].'        name="status[2]" value="2"> abgelehnt<br> 
<input type="checkbox" '.$checked[3].'        name="status[3]" value="3"> Krank/offen<br> 
<input type="checkbox" '.$checked[4].'        name="status[4]" value="4"> geklaut/Problemfall<br>
<input type="checkbox" '.$checked[5].'        name="status[5]" value="5"> Storno<br>
<input type="checkbox" '.$checked[11].'        name="status[11]" value="11"> abgeholte<br>';
$html.= '</td>';
$html.= '</tr>';
$html.='</table>';
$html.='<br><input type="submit" value="Anzeigen">';
$html.='<span style="margin-left:2em"></span>';
$html.='<input name="csv" type="submit" value="CSV">';
$html.='</form></center>';
$html.='<br>';
	
// $_POST['erfassung']="2023-09-05";
$request ="select nachname,rahmennr,abholtermin_ist,changed,rad_abholung.info as info1,info_abholung as info2,status,rad_abholung.recnum as recnum,abholtermin_soll,fahrzeit,pos,mail,concat(strasse,', ',plz,' ',ort) as adresse, rebikeid, rad_abholung.info, concat(marke,' ',modell) as fahrradname from `rad_abholung`"; 
$request.=" left join rad_kunde";
$request.=" on rad_kunde.recnum = rad_abholung.kundenr";
$request.=" left join rad_rad";
$request.=" on rad_rad.recnum = rad_abholung.radnr";

if (empty($_POST['erfassung'])) {
	$request.=" where pos>0";  // Nicht verarbeitete nicht anzeigen
	// $request.=" and radnr>0";  // dto. Bonifaciusstraße auch nicht
	// 11.10.2023 $request.=" and `abholtermin_soll` >= '".(new DateTime())->format("Y-m-d H:i:s")."'";
	// 17.10.2023 $request.=" and `abholtermin_ist` = '0000-00-00 00:00:00'";
	$request.=" and `rueckgabetermin` = '0000-00-00 00:00:00'"; // 17.10.2023
	// $request.=" order by erfassung,pos,abholtermin_soll,fahrradname";
} else {
	$request.=" where `erfassung`='".$_POST['erfassung']."'";
	// $request.=" and radnr>0";  // dto. Bonifaciusstraße auch nicht
	// $request.=" order by erfassung,pos,abholtermin_soll,fahrradname";
}
if (!empty($_POST['abholtermin'])) {
	$dt=new DateTime($_POST['abholtermin']);
	$request.=" AND left(abholtermin_soll,10) = '".$dt->format("Y-m-d")."'"; 
}
if (isset($_POST['status'])) {
	$request.=" AND (`status`='".implode("' or `status`='",$_POST['status'])."')";
}
	$request.=" and radnr>0";  // dto. Bonifaciusstraße auch nicht

$request.=" order by erfassung,pos,abholtermin_soll,fahrradname";

// $request.=" order by pos,abholtermin_soll,fahrradname";
// echo $request."<br>";

$dt_last=new DateTime("0000-00-00");
$tagnr=0;
$radnr=0;
$first=0;
$result=$db->query($request);
while($row=$result->fetch_assoc()) {
	if ($first==0) {
		$first++;
		$html.='<table cellspacing=0 id="liste2">';
		$html.='<tr><th>Pos</th><th>Radnr</th><th>Termin</th><th>Mail</th><th>Adresse</th><th>ID</th><th>Fahrradname</th><th>Info</th><th style="text-align:right;">Aktion</th></tr>';
	}
	$dt=new DateTime($row['abholtermin_soll']);
	$dt->modify("+".$row['fahrzeit']." minute");
	if ( $dt->format("Y-m-d") != $dt_last->format("Y-m-d")) {
		$dt_last=$dt;
		$tagnr++;
		$color="";
		if ($dt->format("w") == 6) {
			$color="color:#ff0000;";
		}
		$html.='<td colspan=9><h1 style="background-color:#7030a0;'.$color.'">Abholtag '.$tagnr.':'.$wochentag[$dt->format("w")].', '.$dt->format("d.m.Y").'</h1></td>';
	}

	$hinweis="";
	if ((!empty($row['info1'])) or (!empty($row['info2']))) {
		if ($row['changed']>0) {
			// Kunde hat Adresse oder/und Info geändert
			$hinweis='<div id="blink_i"><i style="position:relative;top:-2px">i</i></div>';
		} else {
			// Keine Änderung vom Kunden
			$hinweis='<div id="std_i"><i style="position:relative;top:-2px">i</i></div>';
			// $hinweis='<div style="border-radius:50%;display:inline-block;background-color:orange;text-align:center;vertical-align:middle;width:1em;height:1em;padding:2px;margin:0px;"><i style="position:relative;top:-2px">i</i></div>';
		}
	}
	// $hinweis='<span width="3em">'.$hinweis.'</span>';
	$hinweis='<div style="display:inline-block;width:2em;">'.$hinweis.'</div>';

	$action ='<form method="POST" action="change_terminstatus.php"><nobr>';
	$action.='<input type="hidden" name="recnum" value="'.$row['recnum'].'">';
	$action.='<input type="hidden" name="erfassung" value="'.$_POST['erfassung'].'">';
	$action.=$hinweis;
	$action.='<input type="submit" value="status">';
	$action.='</nobr></form>';
	
	if (!empty($row['rebikeid'] )) {
		$radnr++;
	}
	if ($row['status']>0) {
		$color='<tr id="status'.$row['status'].'">';
	} else {
		$c=$radnr%2;
		$color='<tr id="color'.$c.'">';
	}
    
	// Abfrage nicht mehr nötig ? villeicht noch die Datenbank anpassen 
	// alle dessen abholtermin_ist nicht 0 bekommen status 11
	/*
	if ($row['abholtermin_ist'] != "0000-00-00 00:00:00") {
		$color='<tr id="status11">';
	}
	*/
	
// 		$color='<tr id="status'.$row['status'].'">';
	
	if (trim($row['adresse'])==",") {
		$row['adresse']="";
	}
/*	
	$hinweis="";
	if ((!empty($row['info1'])) or (!empty($row['info2']))) {
		$hinweis='(i)';
	}
	// $hinweis='<span width="3em">'.$hinweis.'</span>';
	$hinweis='<i style="width:"3em">'.$hinweis.'</i>';
*/
	
	$html.=$color;
	$html.='<td id="'.$row['recnum'].'">'.$row['pos'].'</td>';
	$html.='<td>'.$radnr.'</td>';
	$html.='<td>'.$dt->format("H:i").'</td>';
	$html.='<td>'.$row['mail'].'</td>';
	$html.='<td>'.$row['adresse'].'</td>';
	$html.='<td>'.$row['rebikeid'].'</td>';
	$html.='<td>'.$row['fahrradname'].'</td>';
	$html.='<td>'.$row['info'].'</td>';
	// $html.='<td>'.$row['info2'].'</td>';
	$html.='<td>'.$action.'</td>';
	$html.='</tr>';
	

	/* CSV Datei erstellen */
	if (!empty($row['rebikeid'])) { 
		// $a=array("ungesehen","bestätigt","abgelehnt","Krank/Urlaub/offen","geklaut/Problemfall","Storno");
		// $a[11]="abgeholt am ".$dt->format("d.m.Y");
        //          grau                  grün               rot             orange                  blau
		$a=array("noch nicht reagiert","Zusage liegt vor","Absage liegt vor","Krank","Storno","Storno");
		$a[11]="abgeholt"; //dunkelgrün
		
		$k=$row['status'];

		// auch hier nicht mehr nötig
		/*
		if ($row['abholtermin_ist'] != "0000-00-00 00:00:00") {
			$k=11;
		}
		*/
		
		$statustext=$a[$k];
		
		// $dt_ist=new DateTime($row['abholtermin_ist']);
		
		/*
		$csv.="\"".$dt->format("d.m.Y");
		$csv.="\";\"".$dt->format("H:i");
		$csv.="\";\"".$row['mail'];
		$csv.="\";\"".str_replace("\"","“", $row['adresse']);
		$csv.="\";\"".$row['rebikeid'];
		$csv.="\";\"".str_replace("\"","“", $row['fahrradname']);
		$csv.="\";\"".str_replace("\"","“", $row['info1']);
		$csv.="\";\"".str_replace("\"","“", $row['info2']);
		$csv.="\";\"".$row['status'];
		$csv.="\";\"".str_replace("\"","“", $statustext);
		$csv.="\"\r\n";
		*/
		
		$csv.="\"".$row['rebikeid'];
		$csv.="\";\"".$row['nachname'];
		$csv.="\";\"".$dt->format("d.m.Y");
		$csv.="\";\"".str_replace("\"","“", $statustext);
		if ($row['status'] == 5) {	
			$info=$row['info1'];
			
			if (!empty($row['info']) and !empty($row['info2'])) {
				$info.=",";
			}
			$info.=$row['info'];
		
		
			$csv.="\";\"".str_replace("\"","“", $info);
		} else {
			$csv.="\";\"";
		}
		$csv.="\";\"".$row['rahmennr'];
		$csv.="\"\r\n";
	}	
	
	
}
$html.="</table>";

if (isset($_POST['csv'])) {
	echo $out->header_csv("Termine.csv");
	echo $out->utf();
	echo $csv;
	
} else {
	echo $out->header();
	echo $menu->out("5. Status der Termine ansehen und ändern");
	echo "<center>";
	echo $html;
	echo "</center>";
	echo $out->footer();
}

/*
Terminstatus
*/
?>
