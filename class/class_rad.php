<?php
class Rad extends Database {
	public $row=array();        // Array Eine Zeile
	
	public function __construct($db) {
		$this->db=$db;
		$this->database="rad_rad";
		$this->name="rad_rad";
	}

	public function loadByName($rahmenr) {
		if ($rahmennr=="") {
			$rahmennr=$this->row['rahmennr'];
		}
		
		$request ="SELECT * from rad_rad";
		$request.=" WHERE rahmennr='".$this->db->real_escape_string($rahmennr)."'";
		$result = $this->db->query($request);
		$this->row = $result->fetch_assoc();
		return $this->row;
	}	

	/*
		Daten verändern
	*/
	public function updateByRahmennr($row="") {
		if (empty($row)) {
			$row=$this->row;
		}
		// print_r($row);
		// echo "<br>";
		$rn="";
		if (isset($row['rahmennr'])) {
			$rn=$row['rahmennr'];
		} else 
		if (isset($this->row['rahmennr'])) {
			$rn=$this->row['rahmennr'];
		}
		
		if ($rn == "") {
			return false; // Update ohne recnum nicht möglich
		}
		
		unset($row['rn']);
		
		$set="";
		foreach($row as $k => $v) {
			$this->row[$k]=$v; 
			
			if ($set != "") {
				$set.=",";
			}
			

			$set.="`".$k."`='".$this->db->real_escape_string($v)."'";
		}
		
		$request="update ".$this->database." set $set where `rahmennr`='".$rn."'";	
		$result = $this->db->query($request);
		
		$this->row['rahmennr']=$rn;
		return $result; // Arrayoffset = null ?
		
	}
}

?>