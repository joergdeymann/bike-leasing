<?php
class Menu {
	public $help_html="hier ist keine Live-Hilfe verfügbar";
	public $help=array();
	
	public function __construct() {
		$this->setHelpAll();
	}
	
	public function setHelp($html="keine Hilfe vorhanden") {
		$this->$help_html=$html;
	}
	
	private function setHelpAll() {
		$this->help['import_rebike.php']='
		<b>Import der ReBike-Daten</b><br>
		<i style="font-weight:400">
		1. Excel-Tabelle mit den ReBike Daten öffnen<br>
		2. Datei speichern unter "rebike.csv" im UTF-8 CSV Format<br>
		3. Diese Seite aufrufen und den BUTTON "Durchsuchen" drücken<br>
		4. wählen Sie die Datei "rebike.csv" aus<br>
		5. Drücken Sie den Button "Import"<br>
		<br>
		Damit haben Sie alle Rebike Daten importiert und brauchen die Excel und CSV nicht mehr.<br>
		</i>
		';
		
		$this->help['liste_fuer_route.php']='
		<b>Export für Routenplaner Multiroute</b><br>
		<i style="font-weight:400">
		<table>
		<tr><td>1.</td><td>Geben sie das Datum an in der Sie die letzten Rebike Daten erfasst haben. 
		<br>Es können auch Daten von mehreren Tagen angegeben werden.</td></tr>
		<tr><td>2.</td><td>Wenn Sie nicht wollen das bereits erfasste terminierte Räder berücksichtigt werden, können Sie dies verhindern, indem Sie den Haken bei Nur neu erfasste Räder setzen.<br>Dies ist der Standart<td></tr>
		<tr><td>3.</td><td>Mit dem Button "Ansicht" können Sie sich unverbindlich ansehen, welche Adressen für den Export generiert werden.</td>
		<tr><td>4.</td><td>Mit dem Button "CSV" können laden Sie sich die Export-Datei für Multiroute herunter. Sie heist dann etwa adressen.csv oder adressen("Nummer").CSV</td>
		<tr><td>5.</td><td>Öffnen Sie Excel und importieren diese Datei adressen.csv als UTF-8<br>Es ist wichtig diese als UTF-8 zu importieren, damit die Umlaute nicht verfälscht werden.<BR>Dazu gehen Sie wie folgt vor:<br>
		a) Excel öffen<br>
		b) im Menu "Daten" klicken<br>
		c) "Aus Text/csv" wählen<br>
		d) Dateiname eingeben<br>
		e) "Importieren" drücken<br>
		</td></tr>
		<tr><td>6.</td><td>Danach muss Die Datei als Excel format gespeichert werden, damit Multiroute das lesen kann.<br>
		Das geht folgendermaßen:<br>
		1. "Datei" - "Speichern unter"<br>
		2. Geben Sie "adressen.xlsx" an und speichern sie es als Excel format</td></tr>
		</table></i>
		<b>Bearbeitung mit Multiroute</b><br>
		<i><table>
		<tr><td>1.</td><td>Öffnen Sie Multiroute unter <a href="http://www.multiroute.de" target="_blank"></td></tr>
		<tr><td>2.</td><td>Wählen Sie dort den Punkt "Durchsuchen" aus und wählen sie die "adressen.xlsx" aus</td></tr>
		<tr><td>3.</td><td>Drücken Sie den Roten Butten "Excel-Tabelle hochladen"</td></tr>
		<tr><td>4.</td><td>Vergeben Sie die passenden Überschriften<br>
		"PLZ" für die Postleitzahl<br>
		"Ort" für den Ort<br>
		"Straße" für die Straße<br>
		"Bezeichnung" für die Email-Adresse<br>
		"Zusatz1" für die Bikesale ID</td></tr>		
		</table>
		<br>
		</i>';
		
		$this->help['import_route.php']='
		<b>Import der Route</b><br>
		<i style="font-weight:400">
		1. Excel-Tabelle mit der erstellten Route von multiroute öffnen<br>
		2. Datei speichern unter "route.csv" im UTF-8 CSV Format<br>
		3. Diese Seite aufrufen, den BUTTON "Durchsuchen" drücken, "route.csv" auswählen<br>
		4. Bei "Datum der erfassten Fahrräder" muss nochmal das Datum rein, wann Bikesale importiert wurde<br>
		5. Drücken Sie den Button "Import"<br>
		<br>
		Jetzt werden die Daten von ReBike mit den vorhandenen Daten abgeglichen<br>
		</i>
		';

		$this->help['terminplanung.php']='
		<b>Terminplanung</b><br>
		<i style="font-weight:400">
		1. "Datum der Erfassung der Fahrräder": Eingabe des Datums der letzten ReBike Importierung<br>
		2. "Datum der Abholung": Wann soll die Abholung beginnen ?<br>
		3. "Maximale Fahrzeit am Tag": Wieviele Stunden am Tag der Fahrer unterwegs sein darf ?<br>
		4. "Maximale Anzahl von Fahrräder pro Tag": Wieviel Räder passsen in den Transporter ?<br>
		5. "Abholtage": An welchen Tagen werden dir Räder abgeholt ?<br>
		<br>
		a) Button "Anzeigen": um die voreingestellten Optionen auszurechenen und anzuzeigen<br>
		b) Button "Einstellungen übernehmen": wenn sie zufrieden sind mit den Einstellungen<br>
		c) Button "Fixe Zeiten zurücknehmen": wenn sie die Route von neuen planen wollen<br>
		<br>
		o Nachdem Sie die Liste angezeigt haben können Sie die Zeiten einzelnd ändern, aber nur wenn Sie die Einstellungen noch nicht übernommen haben.<br>
		o Sie können auch einzelene Termine fixieren oder wieder freigeben<br>
		o Nicht änderbare/fixe Termine sind Fett angezeit und das Datum hat ein "*"<br>
		<br>
		o von Multiroute eingestellte feste Termine werden hier als fixe Termine angezeigt und müssen nicht extra übernommen werden<br>
		</i>
		';
		
	}
	
	private function getHelp() {
		$key=basename($_SERVER['SCRIPT_NAME']);
		if (empty($this->help[$key])) {
			$this->help_html="";
		} else {
			$this->help_html=$this->help[$key];
		}
	}
	
	public function out($info="",$show_menu=true) {
/*
		$html= '
			<h1 id="menu">Fahrrad Verwaltung</h1>
		';
*/
		$this->getHelp();
		$html="";
		
		if ($show_menu) {
			$html="			
			<!-- div id='menu_group' -->
			<table id='menu_group' cellspacing=0 cellpadding=0 border=0><tr><td>
			<nav id='menu'>
			  <input type='checkbox' checked id='responsive-menu'><!--label>XXXX</label-->
			  <ul>
				<li><a class='dropdown-arrow' href='http://'>Im und Export</a>
				  <ul class='sub-menus'>
					<li><a href='import_rebike.php'   >Import von Rebike</a></li>
					<li><a href='liste_fuer_route.php'>Export für Multiroute</a></li>
					<li><a href='import_route.php'    >Import von Multiroute</a></li>
				  </ul>
				</li>
				<li><a class='dropdown-arrow' href='http://'>Terminplanung</a>
				  <ul class='sub-menus'>
				   <li><a href='terminplanung.php'>Zeitplanung</a></li>
					<li><a href='mail.php'>Bestätigungsmail versenden</a></li>
					<li><a href='mail_erinnerung.php'>Errinnerungsmail versenden</a></li>
					<li><a href='mail_zeit.php'>Mail mit Uhrzeit versenden</a></li>
				  </ul>
				</li>
				<li><a class='dropdown-arrow' href='http://'>Auswertung</a>
				  <ul class='sub-menus'>
					<li><a href='terminstatus.php'>Kundenrückmeldungen</a></li>
					<li><a href='info.php'>Daten über Bike ID</a></li>
					<li><a href='auswertung_log.php'>Logbuch Daten</a></li>
					<li><a href='auswertung_mail.php'>Logbuch Mail</a></li>
				  </ul>
				</li>
				<li><a class='dropdown-arrow' href='http://'>Abholungen</a>
				  <ul class='sub-menus'>
					<li><a href='abholschein.php'>Abholscheine drucken</a></li>
					<li><a href='liste_fahrer.php'>Liste für den Fahrer</a></li>
					<li><a href='uebersicht_abholung.php'>Übersicht Abholungen</a></li>
					<li><a href='uebersicht_versuche.php'>Übersicht Abholversuche pro Kunde</a></li>
				  </ul>
				</li>
				<li><a class='dropdown-arrow' href='http://'>Lager</a>
				  <ul class='sub-menus'>
					<li><a href='abholung_bestaetigen.php'>Lagerbestand aufnehmen</a></li>
					<li><a href='lagerbestand.php'>Rückgabe der Bikes</a></li>
					<li><a href='abgaben.php'>Zurückgegebene Bikes</a></li>
				  </ul>
				</li>
				<li><a  href='help.php'>?</a>
				  <ul class='sub-menus' id='help'>
					".$this->help_html."
				  </ul>
				</li>
				
			  </ul>
			</nav>
			
			<br><br>
			<div style='display:inline-block;margin-left:20%;font-size: 1.5em;font-weight:1000;text-align:center'>
			Herzlich Willkommen <br>
			in der <br>
			Fahrrad Verwaltung !
			</div>
			</td><td style='text-align:right'>
			
			<!-- br style='margin-top:30px'>
			</div -->
			<img src='img/logo.png' alt='ALL-TRANSPORT24'>
			</td></tr></table>
			";
			
$xx="			  <img style='position:absolute; top:-10; right:10px;' src='img/logo.png'>";
			
			
			
/*
			$html.='
				<div id="menu">
				<a href="import_rebike.php">1. Import von ReBike</a><br>
				<a href="liste_fuer_route.php">2. Erstelle Liste für die Route</a><br>
				<a href="import_route.php">3. Importiere die Route</a><br>
				<a href="terminplanung.php">4. Terminplanung</a><br>
				<a href="mail.php">4.1 Bestätigungsmail versenden</a><br>
				<a href="mail_erinnerung.php">4.2 Erinnerungsmail versenden</a><br>
				<a href="mail_zeit.php">4.3 Mail mit Uhrzeit versenden</a><br>
				<a href="terminstatus.php">5. farbliche Ansicht der Kunden nach Zeitplan oder Namen, Bestätigen/Absagen/Bearbeiten</a><br>
				<a href="abholschein.php">6. Abholscheine drucken von bestätigten Fahrrädern<br>			
				<a href="liste_fahrer.php">7. Liste für die Fahrer</a><br>
				<a href="uebersicht_abholung.php">8. Übersicht Abholungen am Tag</a><br>
				<a href="abholung_bestaetigen.php">9. Bestätigte nicht abgeholter Räder  -> Markierung als abgeholt</a><br>
				<a href="lagerbestand.php">10. Fahrräder auf Lager -> Markierung als Rückgabe</a><br>
				</div>
			';
*/		
		}
		$html.='<h1 id="menu">'.$info.'</h1>';
		return $html;
	
	}
}
	