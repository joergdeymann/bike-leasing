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
include "class/class_setup.php";

$kunde =   new Kunde($db);
$rad =     new Rad($db);
$abholung= new Abholung($db);
$out =     new Output();
$menu =    new Menu();
$set =     new Setup();

echo $out->header();
echo $menu->out("1. Import von ReBike");
echo '<div style="width:100%;text-align:right;"><a href="zerodb.php">O</a></div>';

/*
	1. Import der Datei von Re-Bike
*/

$msg="";
$html="";

if (!empty($_FILES['inputfile']['tmp_name'])) {


/*	
0.UID	
1.Firmenname (nur wenn Abholung im Unternehmen)	
2.Vorname	
3.Nachname	
4.Straße + HS-NR	
5.PLZ	
6.Ort	
7.Telefonnummer	ggf. 
8.Mobilnummer	
9.E-Mailadresse	
10.Referenznummer (Vertragsnummer)	
11.Marke	
12.Modell	
13.Modelljahr	
14.Fahrrad/Pedelec	
15.Rahmennummer	
16.Zubehör
*/
	
	$im=new Import($_FILES['inputfile']['tmp_name']);
	$im->setSeparator($set->row['rebike']);
	$im->open();
	$im->readline();

	// Check ob die Datei die richtige ist
	$control=array();
	$control[2]="Vorname";
	$control[3]="Nachname";
	$control[9]="E-Mailadresse";
	$control[12]="Modell";
	$ok=true;
	foreach($control as $k => $v) {
		if (!isset($im->row[$k])) {
			$ok=false;
			break;
		}
		if ($im->row[$k] != $v) {
			$ok=false;
			break;
		}
	}
	
	//Nur eine Header Datei $im->readline();
	// echo sizeof($im->row);
	// ### if (sizeof($im->row) == 17) { //#20
	if ($ok) {
		$rad->row=array();
		while($im->readline()) {
			$info="";
			$kunde->row=array();
			$rad->row=array();
			$abholung->row=array();

			// $pattern="/(.*?)([sS])(tr\.?|trasse|traße)\s*([0-9]+)\s*(.*?)/i";
			// $ersetze="$1$2traße $4$5";
			// $im->row[6] =preg_replace($pattern,$ersetze,$im->row[6]); // Strasse ausschreiben hier die änderung
			
			foreach($im->row as $k => $v) {
				// $im->row[$k]=mb_convert_encoding(trim($im->row[$k]),"UTF-8");
				$text=trim($im->row[$k]);
				// $im->row[$k]=iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
				// mb_detect_encoding($text, mb_detect_order(), false);
				
				// auch nicht die richtige Lösung 
				// $im->row[$k] = iconv('windows-1250', 'utf-8', $text);

				//folgendes sollte nicht mit Fehler abbrechen
				$im->row[$k]=iconv(mb_detect_encoding($text, mb_detect_order(), false), "UTF-8", $text);
			}

			
	
	
			if (empty($im->row[1])) {
				$kunde->row['istfirma']=0;
				$kunde->row['firmaname']="";
			} else {
				$kunde->row['istfirma']=1;
				$kunde->row['firmaname']=$im->row[1];
			}
			
			/*
			if (strtolower($im->row[1]) == "nutzer") {
				$kunde->row['istfirma']=0;
			} else 
			if (strtolower($im->row[1]) == "nutzerin") {
				$kunde->row['istfirma']=0;
			} else 
			if (empty($im->row[1])) {
				$kunde->row['istfirma']=0;
			} else {
				$kunde->row['istfirma']=1;
			}
			*/
			//25.09.2023 Existiert nicht mehr $kunde->row['firmaname']=$im->row[3];
			$kunde->row['vorname']  =$im->row[2]; //4
			$kunde->row['nachname'] =$im->row[3]; //5
			$pattern="/(.*?)([sS])(tr\.?|trasse|traße)( *)([0-9]+)\s*(.*?)/i"; // /s
			$ersetze="$1$2traße $5$6";
			$kunde->row['strasse']  =preg_replace($pattern,$ersetze,$im->row[4]); // 6 Strasse ausschreiben hier die änderung
			$kunde->row['plz']      =$im->row[5]; //7
			$kunde->row['ort']      =$im->row[6]; //8
			$kunde->row['tel1']     =$im->row[7]; //9 
			$kunde->row['tel2']     =$im->row[8]; //10
			$kunde->row['mail']     =$im->row[9]; //11
			
			/* 
				Wenn eine änderung folgt ist die ID angegeben, ansonsten 0
				Wenn 0 , dann muss ich den Datensatz neu laden 
			*/
			$err=false;
			
			if ($kunde->insertupdate()) {
				if (empty($kunde->row['recnum'])) {
					if ($kunde->loadByName() == false) { // Brauche ich für den recnum
						$err=true;
					}
				}
			} else {
				$err=false;
			}
			if ($err) {
				$msg.=$kunde->row['vorname']." ".$kunde->row['nachname']." wurde nicht gesichert!<br>";
			} else {
				if ($kunde->insert == false) {
					$info.='<div style="display:inline-box;color: white; background-color: orange;">Update Kunde mit neuen Werten</div>';
					// $msg.=$kunde->row['vorname']." ".$kunde->row['nachname']." wurde überschrieben!<br>";
				}
			}

			$rad->row['rebikeid']  =$im->row[0];
			$rad->row['leasingnr'] =$im->row[10]; // 1
			$rad->row['marke']     =$im->row[11]; // 14
			$rad->row['modell']    =$im->row[12]; // 15
			$rad->row['rahmennr']  =$im->row[15]; // 16 unique
			$rad->row['zubehoer']  =$im->row[16]; // 13
			// $rad->row['erfassung'] = (new Datetime())->format("Y-m-d H:i:s"); // oder now() Automatisch

			// echo "<br>Versuch1:".print_r($rad->row)."<br>";
 
			$err=false;
			if ($rad->insertupdate()) {
				if (empty($rad->row['recnum'])) {
					// if ($rad->updateByRahmennr() == false) { // Brauche ich für den recnum
						$msg.="Rad konnte nicht gespeichert werden";
						$err=true;
					// }
				}
			} else {
				$err=false;
			}
			if ($err) {
				$msg.=$rad->row['marke']." ".$rad->row['modell'].",Rahmennummer ".$rad->row['rahmennummer']." wurde nicht gesichert!<br>";
			}

			$abholung->row['erfassung']          = (new Datetime())->format("Y-m-d H:i:s"); // oder now() Automatisch
			$abholung->row['kundenr']            = $kunde->row['recnum'];
			$abholung->row['radnr']              = $rad->row['recnum'];
			$abholung->row['abholtermin_soll']   = ""; // Normal Null
			$abholung->row['abholtermin_ist']    = ""; // Null
			$abholung->row['status']             = 0;  // OK Grün, 1=Offen, 2=gecancelt
			// ##25.09.2023 raus $abholung->row['info']               = $im->row[12];
			if ($abholung->insert() == false) {
				$msg.="Abholung von ".$kunde->row['vorname']." ".$kunde->row['nachname'].", Rad ".$rad->row['marke']." ".$rad->row['modell']." konnte nicht eingerichtet werden!<br>";
			}
			
			// $rad->row['zubehoer']  =$im->row[17];
			
			
			$html.= $info."<br>";
			$html.= '<table id="liste">';
			foreach($rad->row as $k => $v) {
				$html.= "<tr>";
				$html.= "<td>".$k."</td>";
				$html.= "<td>".$v."</td>";
				$html.= "</tr>";
			}
			foreach($kunde->row as $k => $v) {
				$html.= "<tr>";
				$html.= "<td>".$k."</td>";
				$html.= "<td>".$v."</td>";
				$html.= "</tr>";
			}
			
			$html.= "<tr><td colspan=2>&nbsp;<br></td></tr>";
			$html.= "</table>";
			$html.= "<hr>";
		}
    } else {
		$key=array("UID","Firmenname (nur wenn Abholung im Unternehmen)","Vorname","Nachname","Straße + HS-NR","PLZ","Ort","Telefonnummer","ggf. Mobilnummer","E-Mailadresse","Referenznummer (Vertragsnummer)","Marke","Modell","Modelljahr","Fahrrad/Pedelec","Rahmennummer","Zubehör");
	 	
		
		$msg="<b>Falsche Import Tabelle ausgewählt</b><br>";
		$msg.="<style>td {font-size:12px;border:1px solid black;}</style>";
		$msg.="<table cellspacing=0><tr><td>KEY</td><td>Vorgabe</td><td>Ist</td></tr>";
		foreach($key as $k => $v) {
			$v2="";
			if (!empty($im->row[$k]) ) {
				$v2=$im->row[$k];
			}
			$msg.="<tr><td>$k</td><td>$v</td><td>".$v2."<td></tr>";
		}
		$msg.="</table>";
		
		
	}
}


echo '<center>';
// echo '<h2 id="red">Test<br>test</h2>';
if (!empty($msg)) {
	echo '<h2 id="red">'.$msg.'</h2>';
}
echo '<form action="import_rebike.php" method="POST" enctype="multipart/form-data">';
// echo '<form action="import_rebike.php" method="POST">';
if (empty($_FILES['inputfile']['tmp_name'])) {
	echo '<table id="input"><th>Datei von Rebike<th>';
	echo '<td><input type="file" name="inputfile" accept=".csv"></td>';
	echo '</table>';
	// damit nicht aus versehen 2 importiert wird
	echo '<input type="submit" value="Import" style="margin-left:-3em;margin-top:1em;">';
}
echo '</form></center>';

echo '<center>';
echo $html;
echo '</center>';


echo $out->footer();
?>
