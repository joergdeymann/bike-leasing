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
	div#menu,h1#menu,center#menu {
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
echo '<center id="menu">';
echo '<form action="liste_fahrer.php" method="POST">';
echo '<table"><tr><td><b>Abholtag:</b></td><td><input name="abholtermin" type="date" value="'.$_POST['abholtermin'].'"></td></tr></table>';
echo '<input type="submit" value="LOS">';
echo '</form><br><br>';
echo '</center>';
echo '<center>';

if (!empty($_POST['abholtermin'])) {
	echo '<h1>Abholung Tag '.(new Datetime($_POST['abholtermin']))->format("d.m.Y").'</h1>';
	
	echo '<table id="liste" cellspacing=0 border=1 bordercolor="blue">';
	echo '<tr><th>Mail</th><th>Kunde</th><th>Adresse</th><th>Telefon</th><th>ID</th><th>Zeit</th></tr>';

	$request="
	select * from `rad_abholung`
	left join `rad_rad`
	on rad_rad.recnum=rad_abholung.radnr
	left join `rad_kunde`
	on rad_kunde.recnum=rad_abholung.kundenr
	where `status`='1'
	and `abholtermin_ist` = '0000-00-00 00:00:00'
	and left(`abholtermin_soll`,10) = '".(new Datetime($_POST['abholtermin']))->format("Y-m-d")."'
	order by `abholtermin_soll`";

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
		echo '<td style="vertical-align:top;">'.(new Datetime($row['abholtermin_soll']))->format("Y-m-d H:i").'</td>';
		echo '</tr>';
	}
	echo '</table>';
}
echo '</center>';
echo "</body></html>";	
