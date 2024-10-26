<?php
/*
	Ausgaben 
*/
class Output {
	private $title;
	public function __construct() {
		$this->title="";
	}
	
	public function header($title="") {
		if (empty($title)) {
			$title=$this->title;
		} else {
			$this->title=$title;
		}
		
		$html ='<!doctype html>';
		$html.='<html lang="de">';

		$html.='<head>';
		$html.='<meta charset="utf-8">';
		$html.='<link rel="icon" type="image/vnd.microsoft.icon" href="favicon.ico">';
		
		$html.="<title>".$title."</title>";

		if (basename($_SERVER['SCRIPT_NAME'])  == "abholschein.php") {
			$html.='<link rel="stylesheet" href="abholschein.css">';
		} else {
			$html.='<link rel="stylesheet" href="standart.css">';
		}
		$html.='<link rel="stylesheet" href="menu.css">';
		$html.="</head><body>";
		
		return $html;
	}
	
	public function kopf() {
		$html="";
		$html.= '<div style="margin-left:1%; margin-right:1%;"><div style="text-align:right;">';
		$html.= '<img alt="All-Transport24" src="img/logo.png" style="height:100px;">';
		$html.= '</div>';
		$html.= '<h1 style="margin-top:0px;">Kontrollieren Sie ihr Adresse und Bestätigen Sie ihren Termin</h1>';
		$html.= '</div>';
		return $html;
	}
	//
	// Header für den Download
	//
	public function header_csv($filename="adressen.csv") {		
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=\"".$filename."\"");
	    // readfile($dir.$file);
		// echo "Hier kommt der Inhalt hin";
    }
	// UTF Dokumentencode senden
	public function utf() {
		return chr(239) . chr(187) . chr(191);
	}
	
	public function footer() {
		$html="</body></html>";
		return $html;
	}

	public function msg($msg,$err) {
		if (empty($msg)) {
			return "";
		}
		
		if ($err) {
			return '<h2 id="red">'.$msg.'</h2>';
		} else {
			return '<h2 id="green">'.$msg.'</h2>';
		}
	}

}
?>
