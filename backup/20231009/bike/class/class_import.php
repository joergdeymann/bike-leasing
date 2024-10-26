<?php
class Import  {
	private $file;      // Filename
	private $fp;        // Filpointer
	private $separator; // Trennzeichen
	private $enclosure; // Zeichen für Strings, wo ein Trennzeichen enthalten ist
	private $escape;    // Escape - Zeichen
	
	public $headline;   // Array Überschrift
	public $row;        // Array Eine Zeile
	
	public function __construct($file) {
		$this->file=$file;
		$this->separator=";";
		$this->enclosure="\"";
		$this->escape="\\";
	}		
	public function setFile($file) {
		$this->file=$file;		
	}
	public function setSeparator($s) {
		$this->separator=$s;
	}
	
	// $handle = fopen("ftp://user:password@example.com/somefile.txt", "w")
	public function open() {
		$this->fp=fopen($this->file,"r");
	}
	
	public function readline() {
		// fgetcsv($fp, length, separator, enclosure)
		if ($this->row=fgetcsv($this->fp,5000,$this->separator,$this->enclosure,$this->escape)) {
			$this->row=array_map('trim',$this->row);
			return $this->row;
		} else {
			return false;
		}	
	}	
	
	public function readHeadline() {
		if ($line=$this->fp->readline()) {
			$line=array_map('trim',$line);
			$this->headline=explode($line);
			return $line;
		} else {
			return false;
		}	
	}	
	
}
?>
