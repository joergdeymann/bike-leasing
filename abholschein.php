<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_output.php";
include "class/class_menu.php";


if (empty($_POST['abholtermin'])) {
	$_POST['abholtermin']="";
}



// abholschein erstelllen
$dt=new DateTime();
$html_head=file_get_contents("abholschein_vorlage_kopf.php");
$html_body=file_get_contents("abholschein_vorlage.php");


$request ="select *,rad_abholung.info as info_ok from rad_abholung";
$request.=" left join rad_kunde";
$request.=" on rad_abholung.kundenr = rad_kunde.recnum";
$request.=" left join rad_rad";
$request.=" on rad_abholung.radnr = rad_rad.recnum";
// $request.=" where abholtermin_soll > '0000-00-00 00:00:00'";
if(empty($_POST['abholtermin'])) {
	$request.=" where abholtermin_soll > now()";
} else {
	$request.=" where left(abholtermin_soll,10) ='".(new DateTime($_POST['abholtermin']))->format("Y-m-d")."'"; 
}
$request.=" and   abholtermin_ist  = '0000-00-00 00:00:00'";
if (isset($_POST['status'])) {
	$request.=" AND (`status`='".implode("' or `status`='",$_POST['status'])."')";
}

// $request.=" and (status=0 or status=1)"; // Temporär 10.10.2023
// $request.=" and   erfassung = '".$dt->format("Y-m-d")."'";
// nicht mit rein nehmen, wird trotzdem angefahren, $request.=" and   status = '1'";
// $request.=" and `pos`>0";
$request.=" and `radnr` > '0'";
$request.=" order by abholtermin_soll";


// echo $request;

$out =     new Output();
$menu =    new Menu();
echo $out->header();
echo $menu->out("6. Ausgabe der Abholscheine");



echo '<center id="noprint">';
// echo '<center id="menu">';
echo '<form action="abholschein.php" method="POST">';
echo '<table id="input">
<tr><td><b>Abholtag:</b></td><td><input name="abholtermin" type="date" value="'.$_POST['abholtermin'].'"></td></tr>

<tr>
<th style="vertical-align:top;background-color:white;">Status<br>wer wird angefahren ?</th>';

$checked=array("","","","","");
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
			$checked=array(0 => "checked",1 => "checked",2 => "",3 => "",4 => "");
		}
	} else {
		$checked=array(0 => "checked",1 => "checked",2 => "",3 => "",4 => "");
	}
}
echo '<td>   
<input type="checkbox" '.$checked[0].'        name="status[0]" value="0"> ungesehen<br> 
<input type="checkbox" '.$checked[1].'        name="status[1]" value="1"> bestätigt<br> 
<input type="checkbox" '.$checked[2].'        name="status[2]" value="2"> abgelehnt<br> 
<input type="checkbox" '.$checked[3].'        name="status[3]" value="3"> Krank/offen<br> 
<input type="checkbox" '.$checked[4].'        name="status[4]" value="4"> geklaut/Storno/Problemfall<br>';
echo '</td>';

echo '</tr></table>';

echo '<input type="submit" value="LOS">';
echo '</form><br><br>';
echo '</center>';







echo $html_head;

$result=$db->query($request);
while($row=$result->fetch_assoc()) {
	$body=$html_body;
	$row['abholtermin_soll']=(new DateTime($row['abholtermin_soll']))->format("d.m.Y");
	if (!empty($row["info_ok"])) {
		$row["info_ok"].="<br><br>";
	}
	if (!empty($row['info_abholung'])) {
		$row['info_abholung']=str_ireplace("\r\n","<br>",$row['info_abholung'])."<br>";
	}
	if (!empty($row['zubehoer'])) {
		$row['zubehoer']="<b>Zubehör:</b><br>".str_ireplace("\r\n","<br>",$row['zubehoer']);
	}
	foreach($row as $k => $v) {
		$body=str_ireplace("*".$k."*",$v,$body);
	}
	echo $body;	
}
$out->footer();;	

?>
