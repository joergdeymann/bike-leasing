<?php
class Abholung extends Database {
	public $row=array();        // Array Eine Zeile
	
	public function __construct($db) {
		$this->db=$db;
		$this->database="rad_abholung";
	}

	public function setRoute($row,$where) { 		
		$where_kunde=array();
		$where_kunde['erfassung']=$where['erfassung'];
		unset ($where['erfassung']);
		
		$request_bedingung ="SELECT recnum FROM rad_kunde"; 
		// $request_bedingung.=" WHERE `mail`   ='".$where['mail']."'";
		$request_bedingung.=$this->where2string($where);
		// echo $request_bedingung."<br>"; //####		
		// $request_bedingung.=" limit 1";
		// Problem bei 2 Kunden mit gleicher Mail !!
		
		
		// $request_bedingung.=" AND   `name`   ='".$where['name']."'";    // habe hierfür kein Input
		// $request_bedingung.=" AND   `vorname`='".$where['vorname']."'"; // habe hierfür kein Input
		// Anscheinend ändert der Routenplane die Straßennamen, dann passt es nicht mehr
		// email ist aber eindutig und damit auch genug
		// es gibt aber auch eine Email mit unterschiedlichen namen
		
		// $request_bedingung.=" AND    `plz`='".$where['plz']."'";
		// $request_bedingung.=" AND     `ort`='".$where['ort']."'";
		// $request_bedingung.=" AND `strasse`='".$where['strasse']."'";
		
		$set="";
		foreach($row as $k => $v) {
			if ($set != "") {
				$set.=",";
			}
			$set.="`".$k."`='".$this->db->real_escape_string($v)."'";
		}
		// echo $set."<br>";				
		$request ="UPDATE rad_abholung";
		$request.=" set $set";
		$request.=" WHERE (kundenr =  ANY (".$request_bedingung."))"; //ANY weil Email ist bei Kunde nicht eindeutig
		$request.=" AND (erfassung = '".$where_kunde['erfassung']."')";
		// echo '$abholung->seRoute()<br>';
		// echo $request;
		// echo "<br>";
		$result=$this->db->query($request);

		// $prototype='Rows matched: 0 Changed: 1 Warnings: 2'; 
		// list($matched, $changed, $warnings) = sscanf($prototype, "Rows matched: %d Changed: %d Warnings: %d");		
		// list($matched, $changed, $warnings) = sscanf($this->db->info, "Rows matched: %d Changed: %d Warnings: %d"); //####
		// echo $this->db->info."<br>"; //####
		// echo "Matches: $matched ----";
		// echo "Geändert:".$this->db->affected_rows."---$request_bedingung</br>";		
		if ($this->db->affected_rows > 20) {
			echo "Zuviele Geändert:".$this->db->affected_rows."</br>";		
			echo $request;
		}
		return $result;
	}		
	
}
?>