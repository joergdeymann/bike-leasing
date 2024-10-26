<?php
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
$_POST['mail']="s.altepost@beerman.de";
$_POST['rebikeid']="LR-900655";

$out =     new Output();
$menu =    new Menu();
$wochentag=array("Sonntag","Montag","Dienstag","Mittwoch", "Donnerstag","Freitag","Samstag");
$wahltext=array("nicht reagiert","bestätigt","abgelehnt","offen","Storno/Erledigt");
$wahl=-1;
$msg="";
if (isset($_POST['wahl0'])) $wahl=0;
if (isset($_POST['wahl1'])) $wahl=1;
if (isset($_POST['wahl2'])) $wahl=2;
if (isset($_POST['wahl3'])) $wahl=3;
if (isset($_POST['wahl4'])) $wahl=4;

if ($wahl >=0) {
	$request="update rad_abholung set `status`='".$wahl."',`info`='".$_POST['info']."' where `recnum`='".$_POST['recnum']."'";
	if ($db->query($request))  {	
		$msg="<h2>Das Rad ist jetzt markiert als ".$wahltext[$wahl]."</h2>";
	} else {
		$msg="<h2>Es konnte der Status leider nicht geändert werden.</h2>";
	}
}


$request="SELECT * from `rad_kunde` where `mail`='".$_POST['mail']."'";
$result=$db->query($request);
$html="";
$recnum=array();
$count=0;
// echo '<div id="left">Hallo</div>';

$html.='<div id="left">';
$plz="";
$strasse="";

while($row=$result->fetch_assoc()) {
	if ($count == 0) {
		$html.='<b>Adresse</b>';
	} else {
		$html.='<hr>';
		$html.='<b>weitere Adressangabe</b>';
	}		
	$html.='<br><i>';

	if (!empty($row['firmaname'])) {
		$html.=$row['firmaname'].'<br>';
	}
	$html.=$row['vorname'].' '.$row['nachname']."<br>";
	$html.=$row['strasse'].'<br>';
	$html.=$row['plz'].' '.$row['ort'].'<br>';
	$html.='<b>Mail:</b>'.$row['mail'].'<br>';
	$html.='<b>Telefon 1:</b> '.$row['tel1'].'<br>';
	$html.='<b>Telefon 2:</b> '.$row['tel2'].'<br>';
	$html.='</i>';
	
	$recnum[]=$row['recnum'];
	$count++;
	$plz=$row['plz'];
	$strasse=$row['strasse'];
	// echo "<br>";
}

$where="";
foreach ($recnum as $k => $v) {
	$where.=" AND ";
	$where.="`recnum` != '$v'";
}
// var_dump($row);
// $plz=$row['plz'];
// $strasse=$row['strasse'];
$request="SELECT * from `rad_kunde` where `plz`='.$plz.' and `strasse`='.$strasse.'  $where";
$result=$db->query($request);
while($row=$result->fetch_assoc()) {
	$html.='<hr>';
	$html.='<b>weitere Adressangabe</b>';
	$html.='<i>';
	if (!empty($row['firmaname'])) {
		$html.=$row['firmaname'].'<br>';
	}
	$html.=$row['vorname'].' '.$row['nachname']."<br>";
	$html.=$row['strasse'].'<br>';
	$html.=$row['plz'].' '.$row['ort'];
	$html.='Mail: '.$_row['mail'].'<br>';
	$html.='Telefon 1: '.$_row['tel1'].'<br>';
	$html.='Telefon 2: '.$_row['tel2'].'<br>';
	$html.='</i>';
	
	$recnum[]=$row['recnum'];
	$count++;
}
$html.='</div>';

// echo $html;
// exit;

$html.='<div id="right"><div id="rahmen">';
$html.='<b>Fahrräder</b><br>';
// $html.='</div>';
$html.='<table id="liste"><tr><td><b>Bike ID</b></td><td><b>Fahrradname</b></td><td><b>Rahmennummer</b></td></tr>';


$where="";
foreach ($recnum as $k => $v) {	
	if ($where != "") {
		$where.=" OR ";
	}
	$where.="`rad_abholung`.`kundenr` = '$v'";
}
/*
SELECT * from `rad_abholung` 
left join rad_rad 
on rad_abholung.radnr = rad_rad.recnum 
where rad_abholung.abholtermin_ist = '0000-00-00 00:00:00' 
and (rad_abholung.kundenr = '172')
*/

$request ="SELECT * from `rad_abholung`";
$request.=" left join rad_rad";
$request.=" on rad_abholung.radnr = rad_rad.recnum"; 
$request.=" where rad_abholung.abholtermin_ist = '0000-00-00 00:00:00' and ($where)";
// echo $request;

$result=$db->query($request);
while($row=$result->fetch_assoc()) {
	$html.='<tr><td>'.$row['rebikeid'].'</td><td>'.$row['marke'].' '.$row['modell'].'</td><td>'.$row['rahmennr'].'</td></tr>';
}
$html.='</table>';
$html.='</div>';
$html.='<input type="submit" value="Adresse Ändern">';

$html.='</div>';
$html.='<div style="clear:both;"></div>';
$html.="<br>Hallo";

// Besser die Fahrräder den Addressen zuordnen
// Also Adresse Fahrräder , Adresse Fahrräder
// Links Adresse rechts daneben Fahrradliste mit ner Tabelle




echo $out->header();
echo "<h1>Kontrollieren Sie ihr Adresse und Bestätigen Sie ihren Termin</h1>";
echo $html;
echo $out->footer();

?>
