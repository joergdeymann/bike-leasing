<?php
/*
	Anwendung:
	Key:Value
	Key:Value

	z.B:
	$ini=new Setup();
	echo $ini->row['rebike'];
	
	Später Gruppen hinzufügen
*/
class Setup {
	public $row;
	
	public function __construct($file="setup.ini") {
		$this->row=Array();
		$this->getIni($file);
	}

	private function getIni($file) {
		$fp=fopen($file,"r");

		while($r=fgets($fp)) {
			list($k, $v) = explode(':',$r, 2);
			$v=preg_replace("#[\r\n]#", '', $v);  
			
			$this->row[$k]=$v;
		}
		fclose($fp);
	}
}
?>