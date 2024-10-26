<?php
/*
TODO:
Tabelle Kunde anlegen
Tabelle Rad anlegen
Tabelle Abholung anlegen

Function $kunde->loadByName() anlegen
Function $rad->loadByRahmennr() anlegen

*/
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_import.php";
include "class/class_kunde.php";
include "class/class_rad.php";
include "class/class_abholung.php";
include "class/class_menu.php";

// <input id="dateifeld" type="file" onChange="
// alert(document.getElementById('dateifeld').value)"> 

//  onchange="this.form.fakeupload.value=this.value;"
$menu=     new Menu();
$kunde =   new Kunde($db);
$rad =     new Rad($db);
$abholung= new Abholung($db);
$out =     new Output();
$separator=";";


if (empty($_POST['date_von'])) {
	$_POST['date_von']=(new DateTime())->format("Y-m-d");
}
if (empty($_POST['date_bis'])) {
	$_POST['date_bis']=(new DateTime($_POST['date_von']))->format("Y-m-d");
}

if (isset($_POST['csv'])) {
	echo $out->header_csv();
} else {
	echo $out->header();
	echo $menu->out("2. Liste für Route erstellen");
}

// echo "<pre>";
// var_dump($_FILES);
// echo "</pre>";

$html="";
$csv="";
$msg="";
// if (!empty($_FILES['outputfile']['tmp_name'])) {
if (!empty($_POST['submit']) or (!empty($_POST['csv']))) {
	
	// $ex=new Export($_FILES['inputfile']['tmp_name']);
	//  $ex->setSeparator(",");
	// $ex->open();
	
	$html.= '<table id="liste">';
	$dtvon=new DateTime($_POST['date_von']);
	$dtbis=new DateTime($_POST['date_bis']);	
	// OK $request="SELECT *,count(kundenr) from rad_abholung";
	
	$request="SELECT vorname,nachname,strasse,plz,ort,mail,count(kundenr) from rad_abholung";
	$request.=" left join rad_kunde"; 
	$request.=" on rad_kunde.recnum = rad_abholung.kundenr"; 
	$request.=" where 1=1";
	$request.=" and erfassung between '".$dtvon->format("Y-m-d")."' and '".$dtbis->format("Y-m-d")."'";
	if (!empty($_POST['neue'])) {
		$request.=" and abholtermin_ist = '0000-00-00'";
	}
	$request.=" group by kundenr";
	$request.=" order by nachname,vorname";
	// echo "<br>".$request."<br>";
	
	//CSV Braucht das nicht
	// $r=array();
	// $r['mail']='Beschreibung';
	// $r['strasse']='Straße';
	// $r['plz']='PLZ';
	// $r['ort']='Ort';
	// $csv.=implode($separator,$r)."\r\n";
	
	//Bonifatiusstraße als anfang und ende
	$r=array();
	$r['mail']='Bonifaciusstraße 160';
	$r['strasse']='Bonifaciusstraße 160';
	$r['plz']='45309';
	$r['ort']='Essen';
	$csv_start=implode($separator,$r)."\r\n";
	

	$result=$db->query($request);
	while ($row=$result->fetch_assoc()) {
		$r=array();
		$r['mail']=$row['mail'];
		$r['strasse']=$row['strasse'];
		$r['plz']=$row['plz'];
		$r['ort']=$row['ort'];
		$csv.= implode($separator,$r)."\r\n";
		
		$html.= "<tr>";
		foreach($row as $k=>$v) {
			$html.="<td>";
			$html.= $v;
			$html.= "</td>";
		}
		$html.="</tr>";
	}

	$html.= "</table>";
	
}


if (isset($_POST['csv'])) {
	// echo "CSV!!!<br>";
	echo $csv_start;
	echo $csv;
	echo $csv_start;
	exit;
}

/*
	1. Import der Datei von Re-Bike
*/
echo '<center>';
echo '<form action="liste_fuer_route.php" method="POST" enctype="multipart/form-data">';
// echo '<form action="import_rebike.php" method="POST">';
echo '<table>';
// echo '<tr><th>Neue Datei für den Routenplaner<th>';
// echo '<td><input type="file" name="outputfile" accept=".csv"></td>';
// echo '</tr>';

echo '<tr><th>Erfassung von<th>';
echo '<td><input type="date" name="date_von" value="'.$_POST['date_von'].'"></td>';
echo '</tr>';
echo '<tr><th>Erfassung bis<th>';
echo '<td><input type="date" name="date_bis" value="'.$_POST['date_bis'].'"></td>';
echo '</tr>';
echo '<tr><th>Nur neu erfassten Räder<br>ohne festgelegtem Abholdatum<th>';
echo '<td style="vertical-align:top"><input type="checkbox" name="neue" value="1"';
if (!isset($_POST['neue']) or !empty($_POST['neue']))  {	
	echo " checked";
}

echo '></td>';
echo '</tr>';

echo '</table><br>';
echo '<input name="submit" type="submit" value="Ansicht">';
echo '<span style="margin-left:2em"></span>';
echo '<input name="csv" type="submit" value="CSV">';
echo '</form></center>';
echo '<br>';

echo '<center>';
echo $html;
echo '</center>';
echo $out->footer();
?>