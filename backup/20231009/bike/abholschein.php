<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_output.php";
include "class/class_menu.php";


// abholschein erstelllen
$dt=new DateTime("2023-09-12");
$html_head=file_get_contents("abholschein_vorlage_kopf.php");
$html_body=file_get_contents("abholschein_vorlage.php");


$request ="select *,rad_abholung.info as info_ok from rad_abholung";
$request.=" left join rad_kunde";
$request.=" on rad_abholung.kundenr = rad_kunde.recnum";
$request.=" left join rad_rad";
$request.=" on rad_abholung.radnr = rad_rad.recnum";
$request.=" where abholtermin_soll > '0000-00-00 00:00:00'";
$request.=" and   abholtermin_ist  = '0000-00-00 00:00:00'";
// $request.=" and   erfassung = '".$dt->format("Y-m-d")."'";
$request.=" and   status = '1'";
$request.=" order by abholtermin_soll";


// echo $request;

$out =     new Output();
$menu =    new Menu();
echo $out->header();
echo $menu->out("6. Ausgabe der Abholscheine");


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
echo "</body></html>";	

?>
