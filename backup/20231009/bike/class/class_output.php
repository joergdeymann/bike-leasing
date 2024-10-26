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
			$html.='<link rel="stylesheet" href="menu.css">';
		} else {
			$html.='<link rel="stylesheet" href="standart.css">';
		}
		$html.="</head><body>";
		
		return $html;
	}
	
	//
	// Header für den Download
	//
	public function header_csv() {
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=\"adressen.csv\"");
	    // readfile($dir.$file);
		// echo "Hier kommt der Inhalt hin";
    }
	
	public function footer() {
		$html="</body></html>";
		return $html;
	}
}
?>
