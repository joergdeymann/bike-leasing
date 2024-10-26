<?php
class Database {
	protected  $database; // eigentlich $table
	protected  $db = "";

	public $insert=false;
	public $update=false;
	public $row = array();
	public $result;

	public function __construct(&$db) {
		$this->db=$db;		
	}

	public function getErrCode() {
		return $this->db->errno;
	}
	
	public function matched() {
		list($matched, $changed, $warnings) = sscanf($this->db->info, "Rows matched: %d Changed: %d Warnings: %d");
		return $matched;
	}
	
	public function changed() {
		list($matched, $changed, $warnings) = sscanf($this->db->info, "Rows matched: %d Changed: %d Warnings: %d");
		return $changed;
	}
	
	public function loadByRecnum($recnum=0) {
		if (($recnum==0) and !empty($this->row['recnum'])) {
			$recnum=$this->row['recnum'];
		}
		$request="select * from ".$this->database." where recnum='".$recnum."'";
		$result = $this->db->query($request);
		$this->row = $result->fetch_assoc();
		return $this->row;
	}

	/*
		Daten einfügen
	*/
	
	public function insert($row="") {
		if (empty($row)) {
			$row=$this->row;
		}
		// $recnum=$row['recnum'];
		unset ($row['recnum']);  // zur Sicherheit
		
		$this->row=$row;
		$values="";
		$keys="";
		foreach($row as $k => $v) {
			$this->row[$k]=$v; 
			if ($values != "") {
				$values.=",";
				$keys.=",";
			}
			$values.= "'".$this->db->real_escape_string($v)."'";
			$keys  .= "`".$k."`";
		}

		$request="insert into ".$this->database." ($keys) values ($values)";	
		//echo $request."<br>";			
		try  {
			$result = $this->db->query($request);
			if ($result) {
				$this->row['recnum']=$this->db->insert_id;
				// echo "ID=".$this->row['recnum']."<br>";				
			} 
			return $result;
		} catch (Exception $e) {
			if ($this->db->errno == 1062) {  // Duplicate Entry
				return false;
			}
			echo '<div style="display:inlne-box;margin:10px;padding:5px; border:red solid 2px;background-color: #EEEEEE;color:black;">';
			echo "Tabelle:".$this->database."<br>";
			echo "Script:". $_SERVER["SCRIPT_NAME"]."<br>";
			echo "Fehler:". $this->db->errno.":".$this->db->error."<br>";
			echo "Request:<br>";
			echo $request."<br>";
			echo "</div>";
			
			
			return false;
		}
	}

	/*
		Daten verändern
	*/
	public function update($row="") {
		if (empty($row)) {
			$row=$this->row;
		}

		$recnum=0;
		if (isset($row['recnum'])) {
			$recnum=$row['recnum'];
		} else 
		if (isset($this->row['recnum'])) {
			$recnum=$this->row['recnum'];
		}
		
		if ($recnum == 0) {
			return false; // Update ohne recnum nicht möglich
		}
		
		unset($row['recnum']);
		
		$set="";
		foreach($row as $k => $v) {
			$this->row[$k]=$v; 
			
			if ($set != "") {
				$set.=",";
			}
			

			$set.="`".$k."`='".$this->db->real_escape_string($v)."'";
		}
		
		$request="update ".$this->database." set $set where `recnum`='".$recnum."'";	
		$result = $this->db->query($request);
		
		$this->row['recnum']=$recnum;
		return $result; // Arrayoffset = null ?
		
	}
	
	public function insertupdate($row="") {
		if (empty($row)) {
			$row=$this->row;
		}
		// $recnum=$row['recnum'];
		unset ($row['recnum']);  // zur Sicherheit
		
		$this->row=$row;
		$set="";
		foreach($row as $k => $v) {
			$this->row[$k]=$v; 			
			if ($set != "") {
				$set.=",";
			}
			$set.="`".$k."`='".$this->db->real_escape_string($v)."'";
		}

		$request="insert into ".$this->database." SET $set ON DUPLICATE KEY UPDATE $set";	
		//echo $request."<br>";			
		try {
			$result = $this->db->query($request);
		} catch (Exception $e) {
			echo $this->db->errno.":".$this->db->error."<br>";
			return false;
		}

		if ($result) {
			$this->row['recnum']=$this->db->insert_id;
			$this->insert=false;
			$this->update=false;
			if ($this->db->affected_rows == 1) $this->insert=true;
			if ($this->db->affected_rows == 2) $this->update=true;
			// 0 wenn keine änderung
			
		} 
		return $result;
	}
	
	
	protected function where2string($wherestack) {
		$where="";
		foreach ($wherestack as $k => $v) {
			if (!empty($where)) {
				$where.=" AND ";
			} else {
				$where=" WHERE ";
			}
			$where.="`$k` = '$v'";
		}
		return $where;
	}
		
	public function loadByWhere($wherestack,$order="") {
		$order="";
		if (!empty($order)) {
			$order=" ORDER BY $order";
		}	
		
		$where=$this->where2string($wherestack);
			
		$request="SELECT * FROM ".$this->database." ".$where.$order;
		$this->result = $this->db->query($request);
		return $this->result;
	}

	public function next() {
		$this->row=$this->result->fetch_assoc();
		return $this->row;
	}
	
	public function count() {
		return $this->result->num_rows;
	}

}
?>