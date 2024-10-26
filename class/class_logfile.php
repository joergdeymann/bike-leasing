<?php
class Logfile extends Database {
	// protected $fieldtype;
	
	public function __construct($db) {
		$this->db=$db;
		$this->database="rad_log";
		$this->table="rad_log";

		$this->fieldtype=array(
		"recnum"   =>"number",
		"datum"    => "date",
		"tabelle"  => "string",
		"feldname" => "string",
		"tabelle_recnum" => "number",
		"wert_neu" => "string",
		"user"     => "number"
		);
	}
	
	public function add($row) {
		$request="insert into rad_log (`tabelle_recnum`,`tabelle`,`feldname`,`wert_neu`,`user`) values (
		'".$row['tabelle_recnum']."',
		'".$row['tabelle']."',
		'".$row['feldname']."',
		'".$row['wert_neu']."',".
		$row['user'].")";
		return ($this->query($request));
	}

}

?>