<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_output.php";
include "class/class_menu.php";


if (empty($_POST['abholtermin'])) {
	$_POST['abholtermin']="";
}


/*
$request ="select * from rad_abholung";
$request.=" left join rad_kunde";
$request.=" on rad_abholung.kundenr = rad_kunde.recnum";
$request.=" left join rad_rad";
$request.=" on rad_abholung.radnr = rad_rad.recnum";
$request.=" where abholtermin_soll > '0000-00-00 00:00:00'";
$request.=" and   abholtermin_ist  = '0000-00-00 00:00:00'";
// $request.=" and   erfassung = '".$dt->format("Y-m-d")."'";
$request.=" and   status = '1'";
$request.=" order by abholtermin_soll";
*/


// echo $request;

$out =     new Output();
$menu =    new Menu();
echo $out->header();
echo $menu->out("7. Abholinformation für den Fahrer");
echo '<style>';
echo '
@page { 
	margin: 0cm !important;
	size: A4 landscape;
}
@media print {
	  
	br#pagebreak {
	   page-break-after: always;
	}
	html, body {
		width: 297mm !important;
		height: 210mm !important;
		color-adjust:exact;
		display: inline-block; 		
	}
	div#menu,h1#menu,center#menu,#noprint {
		 visibility: hidden;
		 display:none;
	}
	center {
		margin-top:1cm;
	}
	table#liste {
		background-color:transparent;
	}
	table#liste tr:nth-of-type(odd) td {
		background-color: #EEEEEE;
	}
	table#liste,table#liste2 th {
		background-color: #CCCCCC;
	}	
	
}
';
echo '</style>';
echo '<center id="noprint">';
// echo '<center id="menu">';
echo '<form action="liste_fahrer.php" method="POST">';
echo '<table id="input">
<tr><td><b>Abholtag:</b></td><td><input name="abholtermin" type="date" value="'.$_POST['abholtermin'].'"></td></tr>

<tr>
<th style="vertical-alig:top;">Status<br>wer wird angefahren ?</th>';

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
echo '<center>';

// if (!empty($_POST['abholtermin'])) {
	echo '<h1>Abholung Tag '.(new Datetime($_POST['abholtermin']))->format("d.m.Y").'</h1>';
	
	echo '<table id="liste" cellspacing=0 border=1 bordercolor="blue">';
	echo '<tr><th>Mail</th><th>Kunde</th><th>Adresse</th><th>Telefon</th><th>ID</th><th>Zeit</th></tr>';

	$request="
	select * from `rad_abholung`
	left join `rad_rad`
	on rad_rad.recnum=rad_abholung.radnr
	left join `rad_kunde`
	on rad_kunde.recnum=rad_abholung.kundenr";
	
	$request.=" where `abholtermin_ist` = '0000-00-00 00:00:00'";
	$request.=" and radnr>0"; // Ohne Bonifatiusstraße
	
	
	if (!empty($_POST['abholtermin'])) {	
		$request.=" and left(`abholtermin_soll`,10) = '".(new Datetime($_POST['abholtermin']))->format("Y-m-d")."'";
	} else {
		$request.=" and abholtermin_soll >now()";
	}
	
	if (isset($_POST['status'])) {
		$request.=" AND (`status`='".implode("' or `status`='",$_POST['status'])."')";
	}

	
	
	// where `status`='1'
	$request.=" order by `abholtermin_soll`";
	$result=$db->query($request);
	while ($row=$result->fetch_assoc()) {
		$firma="";
		if (!empty($row['firmaname'])) {
			$firma=$row['firmaname']."<br>";
		}
		echo '<tr>';
		echo '<td style="vertical-align:top;">'.$row['mail'].'</td>';
		echo '<td style="vertical-align:top;">'.$firma.$row['vorname'].'<br>'.$row['nachname'].'</td>';
		echo '<td style="vertical-align:top;">'.$row['strasse'].'<br>'.$row['plz'].' '.$row['ort'].'</td>';
		echo '<td style="vertical-align:top;">'.$row['tel1'].'<br>'.$row['tel2'].'</td>';
		echo '<td style="vertical-align:top;">'.$row['rebikeid'].'</td>';
		$dt=new Datetime($row['abholtermin_soll']);
		$dt->modify("+".$row['fahrzeit']." minutes");
		
		echo '<td style="vertical-align:top;">'.$dt->format("Y-m-d H:i").'</td>';
		echo '</tr>';
	}
	echo '</table>';
// }
echo '</center>';
echo "</body></html>";	
