<?php
// include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_menu.php";
include "class/class_output.php";


if (isset($_POST['status']) and !empty($_POST['rad_abholung_recnum'])) {
	$request="update `rad_abholung` set `rueckgabetermin`=now() where `recnum`='".$_POST['rad_abholung_recnum']."'";
	$db->query($request);
}
	

if (empty($_POST['rebikeid'])) {
	$_POST['rebikeid']="";
}

$html="";
$html.='<center>';
$html.='<form method="POST" action="lagerbestand.php">';
$html.='<table id="input">';
$html.='<tr><td><b>ID</b></td><td><input style="idth:300px;" name="rebikeid" type="text" value="'.$_POST['rebikeid'].'"></td></tr>';
$html.='</table>';
$html.='<input type="submit" value="GO">';
$html.='</form>';
$html.='<br>';

$where="";
$where="";
if (!empty($_POST['rebikeid'])) {
	$where=" and rebikeid like '%".$_POST['rebikeid']."%'";
}


/*
	 Abholug bestätigen
*/
// $html="";


$request ="select *,rad_abholung.recnum as rad_abholung_recnum from `rad_abholung` ";
$request.=" LEFT join rad_rad";
$request.=" on rad_abholung.radnr = rad_rad.recnum"; 
$request.=" where `abholtermin_ist` <> '0000-00-00 00:00:00'";
$request.=" and `rueckgabetermin` = '0000-00-00 00:00:00'";
$request.=" and `rebikeid` <> '' ";
$request.=$where;
$request.="order by `rebikeid`,`erfassung` DESC";
//echo $request;

// echo $request;
// $request.=" and `abholtermin_soll` > ";
$result=$db->query($request);
$html.='<center>';
$html.='<table id="liste">';
if ($result->num_rows == 0) {
	$html.="<tr><td><b>Keine Fahrräder auf Lager</b></td></tr>";
} else {
	$html.='<tr><td><b>Bike ID</b></td><td><b>Fahrrad</b></td><td><b>Aktion</b></td></tr>';
}
$count=$result->num_rows;
$last_bikeid="XX";
$anz=0;
while($row=$result->fetch_assoc()) {
	if ($row['rebikeid'] == $last_bikeid) {
		continue;
	}
	$last_bikeid=$row['rebikeid'];
	
	$anz++;
	$action ='<form method="POST" action="lagerbestand.php">';
	$action.='<input type="hidden" name="rad_abholung_recnum" value="'.$row['rad_abholung_recnum'].'">';
	$action.='<input type="submit" name="status" value="Rückgabe">';
	$action.='</form>';

	$html.='<tr>';
	$html.='<td>'.$row['rebikeid'].'</td>';
	$html.='<td>'.$row['marke'].' '.$row['modell'].'</td>';
	$html.='<td>'.$action.'</td>';
	$html.='</tr>';
}
$html.='</table><br>'; 
// $html.='Anzahl Lagerbestand:'.$result->num_rows.'<br>';
$html.='Anzahl Lagerbestand:'.$anz.'<br>';
$html.='</center>';

$menu =    new Menu();
$out =     new Output();
echo $out->header();
echo $menu->out("Lagerbestand Räder");
echo $html;
echo $out->footer();


?>
