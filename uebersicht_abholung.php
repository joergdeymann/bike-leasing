<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_menu.php";
include "class/class_output.php";
if (empty($_POST['abholtermin_von'])) {
	$_POST['abholtermin_von']="";
}
if (empty($_POST['abholtermin_bis'])) {
	$_POST['abholtermin_bis']="";
}


$html="";
$html.='<center>';
$html.='<form method="POST" action="uebersicht_abholung.php">';
$html.='<table id="input">';
$html.='<tr><td><b>Eingrenzung von</b></td><td><input name="abholtermin_von" type="date" value="'.$_POST['abholtermin_von'].'"></td></tr>';
$html.='<tr><td><b>Eingrenzung bis</b></td><td><input name="abholtermin_bis" type="date" value="'.$_POST['abholtermin_bis'].'"></td></tr>';
$html.='</table>';
$html.='<input type="submit" value="GO">';
$html.='</form>';
$html.='<br>';

$where="";
if (!empty($_POST['abholtermin_von'])) {
	$dt_von=new DateTime();
}
if (!empty($_POST['abholtermin_von']) and !empty($_POST['abholtermin_bis'])) {
	$where=" where abholtermin_soll between '".$_POST['abholtermin_von']." 00:00:00' and '".$_POST['abholtermin_bis']." 23:59:59'";
//	$where =" where abholtermin_soll >= '".$_POST['abholtermin_von']." '";
//	$where.=" and   abholtermin_soll <= '".$_POST['abholtermin_bis']."'";
} else 
if (!empty($_POST['abholtermin_von'])) {
	$where=" where abholtermin_soll >= '".$_POST['abholtermin_von']." 00:00:00'";
} else
if (!empty($_POST['abholtermin_bis'])) {
	$where=" where abholtermin_soll <= '".$_POST['abholtermin_bis']." 23:59:59'";
} 
$request ="SELECT left(abholtermin_soll,10) as abholtermin,count(*) as anz,count(if(status='1',1,NULL)) as ready,count(if(rueckgabetermin<>'0000-00-00 00:00:00',1,NULL)) as rueck,min(pos) as min,max(pos) as max from `rad_abholung`";
$request.=" $where";
$request.=" group by left(abholtermin_soll,10)";
$request.=" order by abholtermin_soll;";
//echo $request;

$result=$db->query($request);
$html.='<table id="liste" cellpadding=5>';
$html.='<tr><th colspan=2>Abholtermin</th><th style="text-align:right">Rad Anzahl</th><th style="text-align:right">Bereit</th><th style="text-align:right">Rückgabe</th><th style="text-align:right">Pos. von</th><th style="text-align:right">Pos. bis</th>';
while($row=$result->fetch_assoc()) {
	$tag=(new DateTime($row['abholtermin']))->format("w");
	if (($tag == 0) or ($tag==6)) {
		$background="background-color:red;color=yellow;";
	} else {
		$background="";
	}
	$wochentag=array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag")[$tag].",";
	$datum=(new DateTime($row['abholtermin']))->format("d.m.Y");
	$html.='<tr>';
	$html.='<td style="text-align:left;'.$background.'">'.$wochentag.'</td>';
	$html.='<td style="text-align:right;'.$background.'">'.$datum.'</td>';
	$html.='<td style="text-align:right;'.$background.'">'.$row['anz'].'</td>';
	$html.='<td style="text-align:right;'.$background.'">'.$row['ready'].'</td>';
	$html.='<td style="text-align:right;'.$background.'">'.$row['rueck'].'</td>';
	$html.='<td style="text-align:right;'.$background.'">'.$row['min'].'</td>';
	$html.='<td style="text-align:right;'.$background.'">'.$row['max'].'</td>';
	
	$html.='</tr>';	
}
$html.="</table></center>";

$menu =    new Menu();
$out =     new Output();
echo $out->header();
echo $menu->out("8. Übersicht Abholungen am Tag");
echo $html;
echo $out->footer();

?>
