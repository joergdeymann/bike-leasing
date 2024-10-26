<?php
class Database {
	protected  $fieldtype; // array aufbau: "fieldname => content"
	protected  $database ; // eigentlich $table
	protected  $table; // eigentlich $table noch nicht eingebaut nur manuell
	protected  $db = "";

	public $insert=false;
	public $update=false;
	public $row = array();
	public $result;

	public function __construct(&$db) {
		$this->db=$db;		
		// $this->table=&$this->database;
	}

	private function mysql_error(&$request) {
		echo '<div style="display:inlne-box;margin:10px;padding:5px; border:red solid 2px;background-color: #EEEEEE;color:black;">';
		echo "Tabelle:".$this->table."<br>";
		echo "Script:". $_SERVER["SCRIPT_NAME"]."<br>";
		echo "Fehler:". $this->db->errno.":".$this->db->error."<br>";
		echo "Request:<br>";
		echo $request."<br>";
		echo "</div>";
	}
	private function mysql_warning(&$request) {
		echo '<div style="display:inlne-box;margin:10px;padding:5px; border:red solid 2px;background-color: #EEEEEE;color:black;">';
		echo "Tabelle:".$this->table."<br>";
		echo "Script:". $_SERVER["SCRIPT_NAME"]."<br>";
		$e = mysqli_get_warnings($this->db);
		do {
		   echo "Warning: $e->errno: $e->message<br>";
		} while ($e->next());
		echo "Request:<br>";
		echo $request."<br>";
		echo "</div>";
	}
	
	public function query(&$request) {
		try  {
			$this->result = $this->db->query($request) or die($this->mysql_error($request));
			if (mysqli_warning_count($this->db)) {
			   $this->mysql_warning($request);
			   return false;
			}			
			return $this->result;
		} catch (Exception $e) {
			$this->mysql_error($request);
			return false;
		}	
	}
	
	public function setField($fieldname,$content) {
		if ($this->fieldtype[$fieldname] == "date") {
			if (empty($content)) {
				$this->row[$fieldname]="NULL";
			} else {
				$this->row[$fieldname]=(new DateTime($content))->format("y-m-d H:i:s");
			}
		} else {
			$this->row[$fieldname]=$content;
		}
	}
	
	public function getField($fieldname) {
		if (empty($content)) {
			$f="";
		} else if ($this->row[$fieldname]=="NULL") {
			$f="";
		} else {	
			$f=$this->row[$fieldname];
		}
		// ich glaube das ist nicht nötig, das muss anders geregelt werden über Class Datum
		if (!empty($f) and $this->fieldtype[$fieldname] == "date") {
			$f=(new DateTime($f))->format("d.m.Y H:i:s");
		}
		return $f;
			
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

		// $request="insert into ".$this->database." SET $set ON DUPLICATE KEY UPDATE $set";	
		$request="insert into ".$this->database." SET $set ON DUPLICATE KEY UPDATE $set,recnum=LAST_INSERT_ID(recnum)";	
		// echo $request."<br>";
		try {
			$result = $this->db->query($request);
		} catch (Exception $e) {
			echo $this->db->errno.":".$this->db->error."<br>";
			return false;
		}

		if ($result) {
			// echo "ID:".			$this->db->insert_id."<br>";
			// echo "recnum:".			$recnum."<br>";
			$this->row['recnum']=$this->db->insert_id;
			$this->insert=false;
			$this->update=false;
			// echo "AFFECTED:".$this->db->affected_rows."<br>";		
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