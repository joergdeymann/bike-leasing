<?php
session_start();
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_kunde.php";
include "class/class_abholung.php";
include "class/class_menu.php";
include "class/class_logfile.php";
$menu =    new Menu();
$out =     new Output();
$kunde =   new Kunde($db);
$abh=      new Abholung($db);
$msg="";

if (empty($_POST['rebikeid'])) {
	$_POST['rebikeid']="";
}
if (empty($_POST['datumvon'])) {
	$_POST['datumvon']="";
}
if (empty($_POST['datumbis'])) {
	$_POST['datumbis']="";
}



	$html="";
	$html.='<center>';
	$html.='<h2>'.$msg.'</h2>';
	$html.='<form action="auswertung_log.php" method="POST">';
	$html.='<table id="liste">';
	$html.='<tr><td><b>Bike ID:</b></td><td><input name="rebikeid" style="width:100px" type="text" value="'.$_POST['rebikeid'].'"></tr>';
	$html.='<tr><td><b>Datum von:</b></td><td><input name="datumvon" type="date" value="'.$_POST['datumvon'].'"></tr>';
	$html.='<tr><td><b>Datum bis:</b></td><td><input name="datumbis" type="date" value="'.$_POST['datumbis'].'"></tr>';
	
	$html.='<tr><td><b>Sortierung</b></td><td>
	<label><input name="sort" type="radio" value="rebikeid">Bike ID,Datum,Zeit</label><br>';
/*
	<label><input name="sort" type="radio" value="datum">Datum, Zeit</label><br>
	<label><input name="sort" type="radio" value="user">Nutzer,Datum, Zeit</label><br>*/
	
	$html.='</td></tr>';
	$html.='</table>';
	$html.='<input name="show" type="submit" value="Anzeigen">';
	$html.='</form>';
	$html.='</center><br>';



/*
1. Suche: Abholung
2. Suche: kunde
*/

$request ="SELECT rad_log.datum,concat(user2.vorname,' ',user2.nachname) as log_user,tabelle,wert_neu,rad_log.user,rebikeid,feldname,rad_kunde.mail as kunde_mail"; 
// $request ="SELECT rad_log.datum,concat(user2.vorname,' ',user2.nachname) as log_user,tabelle,feld,wert_neu";
//  *,,rad_kunde.mail as kunde_mail from rad_log"; // ,rad_log.user
$request.=" from rad_log";
$request.=" left join rad_abholung";
$request.=" on rad_log.tabelle_recnum = rad_abholung.recnum";
$request.=" and rad_log.tabelle = 'rad_kunde'"; 
$request.=" left join rad_kunde";
$request.=" on rad_abholung.kundenr = rad_kunde.recnum";
$request.=" left join rad_rad";
$request.=" on rad_abholung.radnr = rad_rad.recnum";
$request.=" left join rad_kunde as user2";
$request.=" on rad_log.user = user2.recnum";
$request.=" and rad_log.tabelle = 'rad_kunde'"; 
$request.=" where tabelle='rad_kunde'";
if (!empty($_POST['datumvon'])) {
	$dt=new DateTime($_POST['datumvon']);
	$request.=" and rad_log.datum >= '".$dt->format("Y-m-d 00:00:00")."'";
}
if (!empty($_POST['datumbis'])) {
	$dt=new DateTime($_POST['datumbis']);
	$request.=" and rad_log.datum <= '".$dt->format("Y-m-d 23:59:59")."'";
}
if (!empty($_POST['rebikeid'])) {
	$request.=" and rebikeid = '".$_POST['rebikeid']."'";
}
// $request.=" order by rebikeid,datum";
$request.=" order by rad_log.tabelle,rebikeid,feldname,datum";
$abh->query($request);
// $html="";
$rebikeid="XXX";
$wert_neu="XXX";
$feld="XXX";
$addresse_text="";
$addresse=array("","","","","","","","","","");

$html.='<center>';
$html.='<table id="liste">';

while($row=$abh->next()) {
	if ($rebikeid != $row['rebikeid']) {
		$rebikeid = $row['rebikeid'];
		$html.='<tr><th colspan="4">'.$rebikeid.' ('.$row['kunde_mail'].')</th></tr>';
		$html.='<tr><th>Datum</th><th>geändert von</th><th>Feld</th><th>Änderung</th></tr>';
	}
	if ($row['user'] == 0) {
		$row['log_user']="<b>Admin</b>";
	}
	
	$addresse_text="";
	if (($row['wert_neu'] != $wert_neu) or  ($feld != $row['tabelle'].'.'.$row['feldname'])) {
		$addresse_neu=explode("<br>",preg_replace("/[\r\n]{1,2}/","<br>",$row['wert_neu']));
		foreach($addresse_neu as $k => $v) {
			if ($addresse[$k] != $addresse_neu[$k]) {
				$addresse[$k]=$addresse_neu[$k];
				$addresse_text.='<div style="background-color:orange;border:solid 1px red;">'.$addresse_neu[$k].'</div>';
			} else {
				$addresse_text.=$addresse_neu[$k].'<br>';
			}
		}
		
	} else {
		$addresse_text=preg_replace("/[\r\n]{1,2}/","<br>",$row['wert_neu']);
		$addresse=explode("<br>",$addresse_text);
	}
	
	$bg="";
	
	$html.='<tr>';
	$html.='<td>'.$row['datum'].'</td>';
	$html.='<td>'.$row['log_user'].'</td>';
	$html.='<td>'.$row['tabelle'].'.'.$row['feldname'].'</td>';
	$html.='<td>'.$addresse_text.'</td>';
	$html.='</tr>';	
}
$html.='</table>';
$html.='</center>';

$request ="SELECT rad_log.datum,concat(user2.vorname,' ',user2.nachname) as log_user,tabelle,wert_neu,rad_log.user,rebikeid,feldname,rad_kunde.mail as kunde_mail"; 
// $request ="SELECT rad_log.datum,concat(user2.vorname,' ',user2.nachname) as log_user,tabelle,feld,wert_neu";
//  *,,rad_kunde.mail as kunde_mail from rad_log"; // ,rad_log.user
$request.=" from rad_log";
$request.=" left join rad_abholung";
$request.=" on rad_log.tabelle_recnum = rad_abholung.recnum";
$request.=" and rad_log.tabelle = 'rad_abholung'"; 
$request.=" left join rad_kunde";
$request.=" on rad_abholung.kundenr = rad_kunde.recnum";
$request.=" left join rad_rad";
$request.=" on rad_abholung.radnr = rad_rad.recnum";
$request.=" left join rad_kunde as user2";
$request.=" on rad_log.user = user2.recnum";
$request.=" and rad_log.tabelle = 'rad_abholung'"; 
$request.=" where tabelle='rad_abholung'";
if (!empty($_POST['datumvon'])) {
	$dt=new DateTime($_POST['datumvon']);
	$request.=" and rad_log.datum >= '".$dt->format("Y-m-d 00:00:00")."'";
}
if (!empty($_POST['datumbis'])) {
	$dt=new DateTime($_POST['datumbis']);
	$request.=" and rad_log.datum <= '".$dt->format("Y-m-d 23:59:59")."'";
}
if (!empty($_POST['rebikeid'])) {
	$request.=" and rebikeid = '".$_POST['rebikeid']."'";
}
// $request.=" order by rebikeid,datum";
$request.=" order by rad_log.tabelle,rebikeid,feldname,datum";
$abh->query($request);
// $html="";
$rebikeid="XXX";
$wert_neu="XXX";
$feld="XXX";
$html.='<center>';
$html.='<table id="liste">';

while($row=$abh->next()) {
	if ($rebikeid != $row['rebikeid']) {
		$rebikeid = $row['rebikeid'];
		$html.='<tr><th colspan="4">'.$rebikeid.' ('.$row['kunde_mail'].')</th></tr>';
		$html.='<tr><th>Datum</th><th>geändert von</th><th>Feld</th><th>Änderung</th></tr>';
	}
	if ($row['user'] == 0) {
		$row['log_user']="<b>Admin</b>";
	}
	$bg="";
	// if (($row['wert_neu'] != $wert_neu) and ($feld == $row['tabelle'].'.'.$row['feldname'])) {
	if (($row['wert_neu'] != $wert_neu) or  ($feld != $row['tabelle'].'.'.$row['feldname'])) {
		$bg='style="background-color:orange;border:solid 1px red;"';	
	}
	$wert_neu=$row['wert_neu'];
	$feld=$row['tabelle'].'.'.$row['feldname'];
	
	$html.='<tr>';
	$html.='<td>'.$row['datum'].'</td>';
	$html.='<td>'.$row['log_user'].'</td>';
	$html.='<td>'.$row['tabelle'].'.'.$row['feldname'].'</td>';
	$html.='<td '.$bg.'>'.preg_replace("/[\r\n]{1,2}/","<br>",$row['wert_neu']).'</td>';
	$html.='</tr>';	
}
$html.='</table>';
$html.='</center>';

$titel="Änderungs-Logbuch";
echo $out->header($titel);
echo $menu->out($titel);
echo $html;
echo $out->footer();
	
?>
