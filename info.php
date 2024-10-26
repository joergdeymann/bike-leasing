<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
$out =     new Output();
$menu =    new Menu();
$html="";
$msg="";
$err=false;

if (empty($_POST['rebikeid'])) {
// 	echo "empty";
	$_POST['rebikeid']="";
} else {
	$_POST['rebikeid']=	trim($_POST['rebikeid']);
// 	echo "emptry";
}


$html.='<center id="eingabe">';
$html.='<form action="info.php" method="POST">';
$html.='<table id="input">';
$html.='<tr><th>Bike ID</th><td><input name="rebikeid" type="text" value="'.$_POST['rebikeid'].'"></td></tr>';
$html.='</table>';
$html.='<br><input type="submit" value="Anzeigen">';
$html.='</form>';

// echo "nach HTML";

if (!empty($_POST['rebikeid'])) {
	$request ="SELECT *,rad_abholung.recnum as abholung_recnum,rad_rad.recnum as rad_recnum from `rad_abholung`";
	$request.=" left join rad_rad";
	$request.=" on rad_abholung.radnr = rad_rad.recnum"; 
	$request.=" left join rad_kunde";
	$request.=" on rad_abholung.kundenr = rad_kunde.recnum"; 
	$request.=" where rad_rad.rebikeid='".$_POST['rebikeid']."'";
	$request.=" order by rad_abholung.abholtermin_soll";
	
	// echo $request;
	// echo "<br>";
	$result=$db->query($request); // or die mysqli_error();
	// echo "after";
	while($row=$result->fetch_assoc()) {
		$dt=new DateTime($row['abholtermin_soll']);
		$a=array(0=>"ungesehen",1=>"bestätigt",2=>"abgelehnt",3=>"Krank/offen",4=>"geklaut/Storno/Problemfall",11=>"abgeholt");
		$statustext=$a[$row['status']];
		
		$dt_last=$dt->format("Y-m-d");

		$html.="<h1>Abholdatum:".$dt->format("d.m.Y")."</h1>";


		$html.='<table border=0 style="width:70%;"><tr><td style="width:30%;vertical-align:top;text-align:center;">';
		$html.='<center>';
		$html.='<table id="liste">';		
		$html.='<tr><th colspan=2>Kundendaten</th></th>';
		$html.='<tr><td><b>Kunde</b></td><td>';
		$html.=$row['vorname']." ".$row['nachname']."<br>";
		$html.=$row['firmaname']."<br>";
		$html.=$row['strasse']."<br>";
		$html.=$row['plz']." ".$row['ort'];
		$html.='</td></tr>';
		$html.='<tr><td><b><nobr>1. Telefon</nobr></b></td><td>'.$row['tel1'].'</td></tr>';
		$html.='<tr><td><b><nobr>2. Telefon<nobr></b></td><td>'.$row['tel2'].'</td></tr>';
		$html.='<tr><td><b>Mail</b></td><td>'.$row['mail'].'</td></tr>';
		$html.='</table>';
		$html.='<br>';
		$html.='<form method="POST" action="info_kunde.php">';
		$html.='<input type="hidden" value="'.$row['rad_recnum'].'">';
		// $html.='<input type="submit" value="bearbeiten" name="info">';
		$html.='</form>';
		$html.='</center>';		
		

		$html.='</td><td>&nbsp;</td><td style="width:70%;vertical-align:top;">';

		$html.='<table id="liste">';		
		$html.='<tr><th colspan=2>Fahrraddaten</th></th>';
		$html.='<tr><td><b>Bikesale ID</b></td><td>'.$row['rebikeid'].'</td></tr>';
		$html.='<tr><td><b>Status</b></td><td><div id="status'.$row['status'].'">'.$row['status'].':'.$statustext.'</div></td></tr>';
		$html.='<tr><td><b>Rahmennummer</b></td><td>'.$row['rahmennr'].'</td></tr>';
		$html.='<tr><td><b>Leasingnummer</b></td><td>'.$row['leasingnr'].'</td></tr>';
		$html.='<tr><td><b>Hersteller</b></td><td>'.$row['marke'].'</td></tr>';
		$html.='<tr><td><b>Modell</b></td><td>'.$row['modell'].'</td></tr>';
		$html.='<tr><td><b>Zubehör</b></td><td>'.$row['zubehoer'].'</td></tr>';
		$html.='</table>';

		$html.='</td></tr></table><br><br>';
	}
}	
	
	// $request.=" where rad_abholung.abholtermin_ist = '0000-00-00 00:00:00'";
	// $request.=" and rad_abholung.kundenr='$recnum'";

	


echo $out->header("Ansehen der Bike Daten");
echo $menu->out("Ansehen der Bike Daten");
echo $out->msg($msg,$err);
echo $html;

echo $out->footer();

?>
