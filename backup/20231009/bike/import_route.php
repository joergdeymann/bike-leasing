<?php


function custom_error_handler($errno, $errstr, $errfile, $errline) {
    echo "Fehler: [$errno] $errstr - $errfile:$errline";
    die();
}

set_error_handler("custom_error_handler");




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
include "class/class_setup.php";

// <input id="dateifeld" type="file" onChange="
// alert(document.getElementById('dateifeld').value)"> 

//  onchange="this.form.fakeupload.value=this.value;"
$kunde =   new Kunde($db);
$rad =     new Rad($db);
$abholung= new Abholung($db);
$out =     new Output();
$menu =    new Menu();
$set =     new Setup();

if (empty($_POST['erfassung'])) {
	$_POST['erfassung']=(new DateTime())->format("Y-m-d");
}

echo $out->header();
echo $menu->out("3. Import der Route");
// echo "<pre>";
// var_dump($_FILES);
// echo "</pre>";

/*
	3. Import der Route 
	- Bei der Erstellung der Route wird nur eine Addresse je Rad übermittelt, 
	  daher kann es nicht zum doppelten Import kommen
	- Beim Import werden alle Datensätze der Adresse auf gleiche Zeit und KM gesetzt.
	
*/

$msg="";
$html="";
$info="";
$increment_pos=1;

if (!empty($_FILES['inputfile']['tmp_name'])) {
	
	
	$im=new Import($_FILES['inputfile']['tmp_name']);
	$im->setSeparator($set->row['route']);
	$im->open();
	$im->readline();
	if (sizeof($im->row) == 18) {

		$headlines=$im->row;
		$im->readline(); // 2. Kopfzeile überlesen und checken auf Header dateien, diese müssen alle Korrekt sein
		

		$row=array();
		$where=array();
		
		$abholung->row=array();
		$pos=1;
		$dt=new DateTime($_POST['erfassung']);
		$html.="<b>Erfassungsdatum: ".$dt->format("d.m.Y")."</b><br>";
		$html.= '<table id="liste">';
/*
0=Name;
1=Adresse;
2=Fahrzeit;
3=Strecke;
4=Ankunft;
5=Abreise;
6=Aufenthaltsdauer;
7=Kommentar; Start ODER Ziel
8=Breitengrad;
9=Längengrad;
10=Zusatz 1;
11=Zusatz 2;
12=Zusatz 3;
13=Zusatz 4;
14=Zusatz 5;
15=PLZ;
16=Ort;
17=Straße
*/
		$last_im_row=array();
		while($im->readline()) {
			$info="";
			$row=array();
			foreach($im->row as $k => $v) {
				// $im->row[$k]=mb_convert_encoding(trim($im->row[$k]),"UTF-8");
				$text=trim($im->row[$k]);
				// $im->row[$k]=iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text); // Fehler
				// $im->row[$k]=iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8//TRANSLIT", $text); // Fehler
				// $im->row[$k]=iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8//IGNORE", $text);   // ohne Umlaute
				
				// lieber anders machen, da die andere Liste falsch übersetzt wurde
				// $im->row[$k] = iconv('windows-1250', 'utf-8', $text); // Funktioniert bisher
				
				// Der Fehler sollte dadurcht entstanden sein dass detect_encoding NULL zurückgab )true->false gesetzt für annähreung
				// wenns nicht klappt getrennt mb_detct strict, wenn null dann windows-conv, was aber ja auch falsch ist oder dannn so lassen
				$im->row[$k]=iconv(mb_detect_encoding($text, mb_detect_order(), false), "UTF-8", $text); 
				// echo "KEY:$k = ".$im->row[$k]."<br>";				
			}
			
			//
			// check auf 2 ähnlicht Datensätze hintereinander
			//
			if (empty($last_im_row[0])) {
	/*
	echo "EMPTY";
	echo "<pre>";
	var_dump($im->row);
	echo "</pre>";
	*/		
				$last_im_row=$im->row;
				$doppelt=false;
			} else {	
				$doppelt=true;
				if ($im->row['0'] != $last_im_row['0']) {  // Email
					$doppelt=false;
				}
				if ($im->row['15'] != $last_im_row['15']) { //PLZ
					$doppelt=false;
				}
				if ($im->row['16'] != $last_im_row['16']) { // Ort
					$doppelt=false;
				}
				if ($im->row['17'] != $last_im_row['17']) { // Strasse
					$doppelt=false;
				}
				// echo $im->row['0']." ".$im->row['15']." ".$im->row['16']." ".$im->row['17']."<br>";
				// echo $last_im_row['0']." ".$last_im_row['15']." ".$last_im_row['16']." ".$last_im_row['17']."<br>";
				if ($doppelt) {
					// ECHO "Doppelt<br>";				
					continue;
				}
				
				$last_im_row=$im->row;
			}


			
				
			
	/*
			echo "<pre>";
			var_dump($im->row);
			echo "</pre>";
	*/

			
			// 1. Daten übertragen;
			// 2. Wenn neue email =  die letzte mail -> dann kein eintrag-> besser array sichern und vergleichen
			//    Wenn gleich dann continue;
			// 3. Wenn Start -> überprufung Datensatz schon einmal vorhanden pos = 1
			// 4. Wenn Ziel  -> überprüfung ob Datensatz schon vorhanden 
			
			
			 


// echo "email:".$im->row[0]."<br>";
		
			// Start festlegen, eventuell neu speichern
			if (empty($im->row[2]) or ($im->row[7] == "Start")) {
// echo "Start:Begin";				
				$im->row[2]="00:00";  // Zeitabstand
				$im->row[3]="0";      // KM eine Strecke
				if (empty($im->row[8])) {
					$im->row[8]="0"; // Coordinaten von Bonifatius Straße 160 , Essen
				}
				if (empty($im->row[9])) {
					$im->row[9]="0"; // Coordinaten von Bonifatius Straße 160 , Essen
				}
				
				// Daten in der Firmenliste sichern, falls nicht vorhanden
				// ## Bitte alles testen hier und nachsehen, 
				// ## Kunde darf nur einmal angelegt werden und der Recnum gesichert werden
				// ## Eigentlich könnten hier auch die richtigen Werte stehen 
				$where=array();
				$where['mail']    =$im->row[0]; // Mail    Hier steht Bonifatius Straße 160
				$where['vorname'] =$im->row[1]; // "Bonifatius Straße 160 PLZ ORT"
				$where['nachname']=$im->row[7]; // "Start"
				$kunde->loadByWhere($where);
				if ($kunde->next() == false) { // Not found
					$row=$where;
					$kunde->insert($row);
				}

				$where=array();
				$where['kundenr']=$kunde->row['recnum'];
				$where['radnr']=0;
				$where['erfassung']=$_POST['erfassung'];

				$order="pos";
				$abholung->loadByWhere($where,$order);
//				if ($abholung->count() == 0) {
					$row=array();
					$row['kundenr']=$kunde->row['recnum'];
					$row['radnr']=0;                         // Keine Abholung vom Rad
					$row['erfassung'] = $_POST['erfassung']; // Mitr diesen Erfassugsdatum speichern
					$a=explode(":",$im->row[2]);
					$row['fahrzeit']=$a[0]*60+$a[1];   // Fahrzeit und Start HH:MM sollte hier 0 sein
					$row['pos'] = $pos;
					$row['km']=(int)$im->row[3];       //KM
					$row['breitengrad']=$im->row[8];   //Coordianten
					$row['laengengrad']=$im->row[9];   //Coordianten
					if (!empty($im->row[5])) {
						$dt=new DateTime($im->row[5]);
						$row['abholtermin_soll']=$dt->format("Y-m-d H:i:00"); // Abfahrtszeit
					}
					// echo $where;
				if ($abholung->count() == 0) {
					$abholung->insert($row);
				} else {
					$w=array();
					$w['erfassung']=$_POST['erfassung'];
					$w['vorname'] =$im->row[1]; // "Bonifatius Straße 160 PLZ ORT"
					$w['nachname']=$im->row[7]; // "Start"
					$abholung->setRoute($row,$w);
				}
				
				$html.="<tr>";
				$html.= "<th>Pos</th>";
				$html.= "<th>Mail</th>";
				$html.= "<th>Fahrzeit</th>";
				$html.= "<th>KM</th>";
				$html.= "<th>Info</th>";
				$html.="</tr>";
				
				$row['pos']=$pos;
				$where['mail']=$im->row[0];
				$info="";
				$increment_pos=1;
// echo "Start:ende";				
				
				goto display;
				
			}
			
			if ($im->row[7] == "Ziel") {
//			if (empty($im->row[2]) or ($im->row[7] == "Ziel")) {
//		echo "Ziel:Begin";
				$where=array();
				$where['mail']    =$im->row[0]; // Mail    Hier steht Bonifatius Straße 160
				$where['vorname'] =$im->row[1]; // "Bonifatius Straße 160 PLZ ORT" 
				$where['nachname']=$im->row[7]; // "Ziel"  
				$kunde->loadByWhere($where);
				if ($kunde->next() == false) { // Not found
					$row=$where;		
					$kunde->insert($row);
				}
				// Update falls schon 2x vorhanden
				// Laden, falls vorhanden dann üpdaten sonst neu anlegen


				$row=array();
				$row['kundenr']=$kunde->row['recnum'];
				$a=explode(":",$im->row[2]);
				$row['fahrzeit']=$a[0]*60+$a[1];   // Fahrzeit und Start HH:MM
				$row['pos'] = $pos;
				$row['km']=(int)$im->row[3];       //KM
				$row['breitengrad']=$im->row[8];   //Coordianten
				$row['laengengrad']=$im->row[9];   //Coordianten
				$row['erfassung']=$_POST['erfassung'];
				if (!empty($im->row[4])) {
					$dt=new DateTime($im->row[4]);
					$dt->modify("-".$row['fahrzeit']." minutes");
					$row['abholtermin_soll']=$dt->format("Y-m-d H:i:00"); // Abfahrtszeit
				}

				$w=array();
				$w['kundenr']=$kunde->row['recnum'];
				$w['radnr']=0;
				$w['erfassung']=$_POST['erfassung'];
				$order="pos";
				$abholung->loadByWhere($w,$order);
				// echo $abholung->count();
				if ($abholung->count() == 0) {
					$abholung->insert($row);
				} else {
					$w=array();
					$w['erfassung']=$_POST['erfassung'];
					$w['vorname'] =$im->row[1]; // "Bonifatius Straße 160 PLZ ORT"
					$w['nachname']=$im->row[7]; // "Start"
					$abholung->setRoute($row,$w);
				}
				
				// $abholung->insert($row);
//		echo "Ziel:ENDE";
				goto display;
			}


			// Speicherdaten
			//$row['fahrzeit']+=$im->row[6];
			$row=array();
			$a=explode(":",$im->row[2]);
			$row['fahrzeit']=$a[0]*60+$a[1];   // Fahrzeit und Start HH:MM
			$row['pos'] = $pos;
			$row['km']=(int)$im->row[3];       //KM
			$row['breitengrad']=$im->row[8];   //Coordianten
			$row['laengengrad']=$im->row[9];   //Coordianten
			if (!empty($im->row[4])) {
				$dt=new DateTime($im->row[4]);
				$dt->modify("-".$row['fahrzeit']." minutes");
				$row['abholtermin_soll']=$dt->format("Y-m-d H:i:00"); // Abfahrtszeit
			}
			
			// Daten nicht mit 0 Überschreiben, kommt bei 2 oder mehr Datensätzen vor
			if (($row['fahrzeit'] == 0) and ($row['km'] == 0)) { 
				unset ($row['fahrzeit']);
				unset ($row['km']);
			}
		
			
			// Suchangaben
			$where=array();
			$where['mail']=$im->row[0];     // Wichtigste	
			$where['plz']=$im->row[15];     // Falls gleiche Mail aber unterschiedliche Abholorte
			$where['ort']=$im->row[16];		// dto.

			$pattern="/(.*?)([sS])(tr\.?|trasse|traße)( *)([0-9]+)\s*(.*?)/i"; // /s
			$ersetze="$1$2traße $5$6";
			$where['strasse']  =preg_replace($pattern,$ersetze,$im->row[17]); // Strasse ausschreiben hier die änderung
			
			// $where['strasse']=$im->row[17]; // dto.

			//#05.09.2023 Sicher der Werte für andere rechungen
			$plz=$where['plz'];
			$ort=$where['ort'];
			$str=$where['strasse'];
			$mail=$im->row[0];
			

			$adresse_where=$where;
			$adresse_csv=$where['plz']." ".$where['ort'].",".$where['strasse'];
			$adresse_display=false;
			
			if (!empty($_POST['erfassung'])) {
				$where['erfassung']=$_POST['erfassung'];
			}
			$info="";
	/*		
			echo "1:Pos=$pos:".$row['pos'].":".$where['mail']."<br>";
			if ($row['pos'] == 0) {
				exit;
			}
	*/
	
			$abholung->setRoute($row,$where);   // Update, und dann abfrage
			$increment_pos=1;

			// $plz=$where['plz'];
			// $ort=$where['ort'];
			// $str=$where['strasse'];
			// $mail=$where['mail'];
		
			$savover=4;
			// Keine Änderung bei allen Angabrn Versuch dann über die Adresse alleine
			if ($abholung->matched() == 0) {
				//Über Adresse probieren
				unset($where['mail']);
				$where['plz']=$plz;     // Nur Adresse
				$where['ort']=$ort;		// dto.
				$where['strasse']=$str; // 
	/*
				echo "3:Pos=$pos:".$row['pos'].":".$where['strasse']."<br>";
				if ($row['pos'] == 0) {
					exit;
				}
	*/			
				$abholung->setRoute($row,$where);
				if ($abholung->matched()>0) {   
					$info.='<div style="display:inline-box;color: white; background-color: orange;">Zeitdaten nur über Adresse</div>';
					$adresse_display=true;
					$saveover=1;
				}
			}

			// Versuch über Mail
			if ($abholung->matched() == 0) {
				$where['mail']=$mail;
				unset ($where['plz']);     // Nur Adresse
				unset ($where['ort']);	 // dto.
				unset ($where['strasse']); // 

	/*
				echo "3:Pos=$pos:".$row['pos'].":$str<br>";
				if ($row['pos'] == 0) {
					exit;
				}
	*/			
				$abholung->setRoute($row,$where);
				if ($abholung->matched()>0) {   
					$info.='<div style="display:inline-box;color: white; background-color: green;">Zeitdaten nur über Mail.</div>';
					$adresse_display=true;
					$saveover=2;
				}
			}
			if ($abholung->changed() > 1) {
				$info.='<div style="display:inline-box;color: white; background-color: green;">Mehrere Fahrräder</div>';
				// $adresse_display=true;
			}
			if ($abholung->matched() == 0 ) {
				$info.='<div style="display:inline-box;color: white; background-color: red;">nicht gefunden</div>';
				$row['pos']="";
				$increment_pos=0;
				$adresse_display=true;
				$saveover=0;
			}
			
			// Zuviel Info, nicht nötig die Adresse zu laden, höchstens die Fahrräder
			// Bei speicherm über Email , die Adressen trotzdem anzeigen, fallls unterschiede da sind
			if ($adresse_display) {
				$info.= '<div style="background-color:orange;">';
				$info.= "CSV:<br>$adresse_csv<br>";
				$info.= "Datenbank:<br>";
				
				unset($where['erfassung']); // Erst mal so normal muss ich das über die abholung laufen lassen
				$kunde->loadByWhere($where);
				while($kunde->next()) {
					$info.=$kunde->row['plz']." ".$kunde->row['ort'].",".$kunde->row['strasse']."<br>";
					if ($kunde->row['plz'] != $adresse_where['plz']) {
						$info.="<b>PLZ ist anders</b><br>";
					}
					if ($kunde->row['ort'] != $adresse_where['ort']) {
						$info.="<b>Ort ist anders</b><br>";
					}
					if ($kunde->row['strasse'] != $adresse_where['strasse']) {
						$info.="<b>Strasse ist anders</b><br>";
					}
				}
				// $info.="Mail: ".$im->row[0]."<br>";
				$info.="</div>";
				
			}
			
display:			
			if (empty($row['fahrzeit']) and empty($row['km'])) { 
				$row['fahrzeit']="keine Änderung";
				$row['km']="keine Änderung";
			}
			
			
			// if (empty($row['mail'])) {
			//	$row['mail']=$mail;
			// }
			// echo $row['pos'];					
			$html.= "<tr>";
			$html.= "<td>".$row['pos']."</td>";
			$html.= "<td>".$where['mail']."</td>";
			$html.= "<td>".$row['fahrzeit']."</td>";
			$html.= "<td>".$row['km']."</td>";
			$html.= "<td>".$info."</td>";
			$html.= "</tr>";

			$pos+=$increment_pos;	

			
		}
		$html.= "</table>";
    } else {
		$msg="Falsche Import Tabelle ausgewählt";
	}
}


echo '<center>';
// echo '<h2 id="red">Test<br>test</h2>';
if (!empty($msg)) {
	echo '<h2 id="red">'.$msg.'</h2>';
}
echo '<form action="import_route.php" method="POST" enctype="multipart/form-data">';
// echo '<form action="import_rebike.php" method="POST">';

echo '<table border=1>';
echo '<tr>';
echo '<th>Datei vom Routenplaner</th>';
echo '<td><input type="file" name="inputfile" accept=".csv"></td>';
echo '</tr>';
echo '<tr>';
echo '<th>Datum der Erfassung der Fahrräder</th>';
echo '<td><input type="date" name="erfassung" value="'.$_POST['erfassung'].'"></td>';
echo '</tr>';
echo '</table>';
echo '<input type="submit" value="Import">';
echo '</form></center>';

echo '<center>';
echo $html;
echo '</center>';


echo $out->footer();
?>
