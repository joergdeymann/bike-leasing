<?php
// include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_menu.php";

include "class/class_output.php";

if (isset($_POST['status']) and !empty($_POST['rad_abholung_recnum'])) {
	$request="update `rad_abholung` set `abholtermin_ist`=now(),`status`='11' where `recnum`='".$_POST['rad_abholung_recnum']."'";
	$db->query($request);
}
	

if (empty($_POST['abholtermin_von'])) {
	$_POST['abholtermin_von']="";
}
if (empty($_POST['abholtermin_bis'])) {
	$_POST['abholtermin_bis']="";
}

$csv =  chr(239) . chr(187) . chr(191);
$csv.="Wochentag;Abholtermin;Bike ID;Fahrrad\r\n";	

$html="";
$html.='<center>';
$html.='<form method="POST" action="abholung_bestaetigen.php">';
$html.='<table id="input">';
$html.='<tr><td><b>Eingrenzung von</b></td><td><input name="abholtermin_von" type="date" value="'.$_POST['abholtermin_von'].'"></td></tr>';
$html.='<tr><td><b>Eingrenzung bis</b></td><td><input name="abholtermin_bis" type="date" value="'.$_POST['abholtermin_bis'].'"></td></tr>';
$html.='</table>';
$html.='<input type="submit" value="GO">';
$html.='<span style="margin-left:2em"></span>';
$html.='<input name="csv" type="submit" value="CSV">';
$html.='</form>';
$html.='<br>';

$where="";
if (!empty($_POST['abholtermin_von'])) {
	$dt_von=new DateTime();
}
if (!empty($_POST['abholtermin_von']) and !empty($_POST['abholtermin_bis'])) {
	$where=" and abholtermin_soll between '".$_POST['abholtermin_von']." 00:00:00' and '".$_POST['abholtermin_bis']." 23:59:59'";
} else 
if (!empty($_POST['abholtermin_von'])) {
	$where=" and abholtermin_soll >= '".$_POST['abholtermin_von']." 00:00:00'";
} else
if (!empty($_POST['abholtermin_bis'])) {
	$where=" and abholtermin_soll <= '".$_POST['abholtermin_bis']." 23:59:59'";
} 

echo "Allow Invalid Dates";

$request="SET SQL_MODE='ALLOW_INVALID_DATES';";
$result=$db->query($request);

/*
	 Abholug bestätigen
*/
// $html="";


$request ="select *,rad_abholung.recnum as rad_abholung_recnum from `rad_abholung` ";
$request.=" LEFT join rad_rad";
$request.=" on rad_abholung.radnr = rad_rad.recnum"; 
$request.=" where `abholtermin_ist` = '0000-00-00 00:00:00'";
$request.=" and `rebikeid` <> '' ";
$request.=" and `abholtermin_soll` != '0000-00-00 00:00:00'";
// $request.=" and rad_abholung.status = '1'";
if ($where=="")  {
	$request.=" and `abholtermin_soll` <= now()";
} else {
	$request.=$where;
}
// $request.=" and status <= 1"; //15.11.2023
// $request.=" and `erfassung`=max(`erfassung`)"; // 15.11.2023 Keine alten Bestände mehr anzeigen hier das geht nicht
$request.=" order by rebikeid,`erfassung` DESC";
// $request.=" group by erfassung";
// $request.=" having erfassung=max(erfassung)";

//echo $request;
// echo $request;

// $request.=" and `abholtermin_soll` > ";


$result=$db->query($request);
$last_bikeid="XX";
$html.='<center>';
$html.='<table id="liste">';
if ($result->num_rows == 0) {
	$html.="<tr><td><b>Alle Fahrräder wurden abgeholt</b></td></tr>";
} else {
	$html.='<tr><td colspan=2><b>Abholtermin</b></td><td><b>Bike ID</b></td><td><b>Fahrrad</b></td><td><b>Aktion</b></td></tr>';
}
while($row=$result->fetch_assoc()) {
	if ($row['rebikeid'] == $last_bikeid) {
		continue;
	}
	$last_bikeid=$row['rebikeid'];
	$action ='<form method="POST" action="abholung_bestaetigen.php">';
	$action.='<input type="hidden" name="rad_abholung_recnum" value="'.$row['rad_abholung_recnum'].'">';
	$action.='<input type="submit" name="status" value="abgeholt">';
	$action.='</form>';

	$html.='<tr>';
	$tag=(new DateTime($row['abholtermin_soll']))->format("w");
	$wochentag=array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag")[$tag];
	$datum=(new DateTime($row['abholtermin_soll']))->format("d.m.Y");
	$html.='<td>'.$wochentag.'</td>';
	$html.='<td>'.$datum.'</td>';
	$html.='<td>'.$row['rebikeid'].'</td>';
	$html.='<td>'.$row['marke'].' '.$row['modell'].'</td>';
	$html.='<td>'.$action.'</td>';
	$html.='</tr>';
	
	$csv.="\"".$wochentag;
	$csv.="\";\"".$datum;
	$csv.="\";\"".$row['rebikeid'];
	$fahrrad=$row['marke'].' '.$row['modell'];
	$csv.="\";\"".str_replace("\"","“", $fahrrad);
	$csv.="\"\r\n";
	
	
}
$html.='</table></center>';

echo "Fast am ende";

$menu =    new Menu();
$out =     new Output();

if (isset($_POST['csv'])) {
	echo $out->header_csv("lager.csv");
	echo $csv;	
} else {
	echo $out->header();
	echo $menu->out("9. nicht abgeholter Räder als Abgeholt markieren");
	echo $html;
	echo $out->footer();
}



?>
