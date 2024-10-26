<?php
class Menu {
	public function out($info="",$show_menu=true) {
		$html= '
			<h1 id="menu">Fahrrad Verwaltung</h1>
		';
		if ($show_menu) {
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
		}
		
		$html.='<h1 id="menu">'.$info.'</h1>';
		return $html;
	
	}
}
	
	
	