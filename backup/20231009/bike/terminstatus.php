<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
$out =     new Output();
$menu =    new Menu();
$wochentag=array("Sonntag","Montag","Dienstag","Mittwoch", "Donnerstag","Freitag","Samstag");


if (empty($_POST['erfassung'])) {
	$dt=new Datetime();
	$_POST['erfassung']=$dt->format("Y-m-d");
	$_POST['erfassung']="";
} else {
	$dt=new DateTime($_POST['erfassung']);
	$_POST['erfassung']=$dt->format("Y-m-d");	
}

$html="";
$html ='<center><form action="terminstatus.php" method="POST">';
// $html.='Erfassungsdatum: <input name="erfassung" type="date" value="'.$dt->format("Y-m-d").'">';
$html.='<table>';
$html.='<tr><td><b style="font-size:1.5em">Erfassungsdatum:</b></td>';
$html.='<td style="vertical-align:top;padding-top:5px;"><input name="erfassung" type="date" value="'.$_POST['erfassung'].'"></td>';
$html.='</tr></table>';
$html.='leer = alle terminierten zukünftigen Abholungen:';
$html.='<br><input type="submit" value="Anzeigen">';
$html.='</form></center>';
$html.='<br>';
	
// $_POST['erfassung']="2023-09-05";
$request ="select changed,rad_abholung.info as info1,info_abholung as info2,status,rad_abholung.recnum as recnum,abholtermin_soll,fahrzeit,pos,mail,concat(strasse,', ',plz,' ',ort) as adresse, rebikeid, rad_abholung.info, concat(marke,' ',modell) as fahrradname from `rad_abholung`"; 
$request.=" left join rad_kunde";
$request.=" on rad_kunde.recnum = rad_abholung.kundenr";
$request.=" left join rad_rad";
$request.=" on rad_rad.recnum = rad_abholung.radnr";

if (empty($_POST['erfassung'])) {
	$request.=" where pos>0";  // Nicht verarbeitete nicht anzeigen
	// $request.=" and radnr>0";  // dto. Bonifaciusstraße auch nicht
	$request.=" and `abholtermin_soll` >= '".(new DateTime())->format("Y-m-d H:i:s")."'";
	$request.=" and `abholtermin_ist` = '0000-00-00 00:00:00'";
	$request.=" order by erfassung,pos,abholtermin_soll,fahrradname";
} else {
	$request.=" where `erfassung`='".$_POST['erfassung']."'";
	// $request.=" and radnr>0";  // dto. Bonifaciusstraße auch nicht
	$request.=" order by pos,abholtermin_soll,fahrradname";
}

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
		$html.='<tr><td>Pos</td><td>Radnr</td><td>Termin</td><td>Mail</td><td>Adresse</td><td>ID</td><td>Fahrradname</td><td>Info</td><td style="text-align:right;">Aktion</td></tr>';
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
	$html.='<td>'.$action.'</td>';
	$html.='</tr>';
		
		
}
$html.="</table>";
echo $out->header();
echo $menu->out("5. Status der Termine ansehen und ändern");
echo "<center>";
echo $html;
echo "</center>";
echo $out->footer();


/*
Terminstatus
*/
?>
