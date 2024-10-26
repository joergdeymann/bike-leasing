<?php
class Kunde extends Database {
	// public $row=array();        // Array Eine Zeile
	
	public function __construct($db) {
		$this->db=$db;
		$this->database="rad_kunde";
	}
	
	public function loadByName($vorname="",$nachname="") {
		if ($vorname=="") {
			$vorname=$this->row['vorname'];
		}
		if ($nachname=="") {
			$nachname=$this->row['nachname'];
		}
		
		$request ="SELECT * from rad_kunde";
		$request.=" WHERE vorname='".$this->db->real_escape_string($vorname)."'";
		$request.=" AND  nachname='".$this->db->real_escape_string($nachname)."'";
		if ($result = $this->db->query($request)) {
			$this->row = $result->fetch_assoc();
			return $this->row;
		} else {
			return false;
		}
	}			
	
	public function loadByMail($mail="") {
		if ($mail=="") {
			$mail=$this->row['mail'];
		}
		
		$request ="SELECT * from rad_kunde";
		$request.=" WHERE mail='".$this->db->real_escape_string($mail)."'";
		$result = $this->db->query($request);
		$this->row = $result->fetch_assoc();
		return $this->row;
	}			
	
		/*
		Daten verändern
	*/
	public function updateByName($row="") {
		if (empty($row)) {
			$row=$this->row;
		}


		if (!empty($row['vorname']) and !empty($row['nachname'])) {
			$vorname=$this->row['vorname'];
			$nachname=$this->row['nachname'];
		} else 
		if (!empty($this->row['vorname']) and !empty($this->row['nachname'])) {
			$vorname=$this->row['vorname'];
			$nachname=$this->row['nachname'];
		} else {
			return false; // Update ohne KEY nicht möglich
		}
			


		
		unset($row['vorname']);
		unset($row['nachname']);
		
		$set="";
		foreach($row as $k => $v) {
			$this->row[$k]=$v; 
			
			if ($set != "") {
				$set.=",";
			}
			

			$set.="`".$k."`='".$this->db->real_escape_string($v)."'";
		}
		
		$request="update ".$this->database." set $set where `vorname`='".$vorname."' and `nachname`='".$nachname."'";	
		$result = $this->db->query($request);
		
		$this->row['vorname']=$vorname;
		$this->row['nachname']=$nachname;
		return $result; // Arrayoffset = null ?
		
	}

}
?>