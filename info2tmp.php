<?php
session_start();
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";

$out =     new Output();
$abh =     new Abholung($db);
$msg="";

if (isset($_GET['mail'])) {
	$_POST['mail']=$_GET['mail'];
}
if (isset($_GET['rebikeid'])) {
	$_POST['rebikeid']=$_GET['rebikeid'];
}

class Abholung {
	public $db;
	public $kunde_row;
	public $abholung_row;
	
	public $kunde_recnumlist=array();
	public $abholung_recnumlist=array();
	
	public $html="";
	public $count=0;
	
	public function __construct($db) {
		$this->db=$db;
	}


	//
	// Hauptadresse
	//
	public function getHtmlByMail() {
		$this->count=0;
		$this->html="";
		$request="SELECT * from `rad_kunde` where `mail`='".$_POST['mail']."'";
		$this->getHtmlByRequest($request);
	}


	public function getHtmlByRequest($request) {
		$html=&$this->html;
		$recnum=&$this->kunde_recnumlist;
		$count = &$this->count;
		
		$result=$this->db->query($request);
		$html.="<center>";
		$html.="<table cellspacing=0>";
		while($row=$result->fetch_assoc()) {
			
			// $html.='<div id="left">';
			$html.='<tr><td style="vertical-align:top;">';
			$html.='<div id="adresse">';
			if ($count == 0) { // Später nur ganz oben
				$html.='<b>Adresse</b>';
			} else {
				// $html.='<hr>';
				$html.='<b>weitere Adressangabe</b>';
			}		
			$html.='<br><i>';

			if (!empty($row['firmaname'])) {
				$html.=$row['firmaname'].'<br>';
			}
			$html.=$row['vorname'].' '.$row['nachname']."<br>";
			$html.=$row['strasse'].'<br>';
			$html.=$row['plz'].' '.$row['ort'].'<br>';
			$html.='<b>Mail:</b>'.$row['mail'].'<br>';
			$html.='<b>Telefon 1:</b> '.$row['tel1'].'<br>';
			$html.='<b>Telefon 2:</b> '.$row['tel2'].'<br>';
			$html.='</i>';
			$html.='<form method="POST" action="kunde_aendern.php">';
			$html.='<input type="hidden" value="'.$row['recnum'].'" name="recnum">';
			$html.='<input type="submit" value="Adresse ändern">';
			$html.='<input type="hidden" name="rebikeid"  value="'.$_POST['rebikeid'].'">';
			$html.='<input type="hidden" name="mail"      value="'.$_POST['mail'].'">';
			$html.='</form>';
			$html.="</div>";
			$html.='</td><td style="vertical-align:top;">';

			
			$this->getBikeInfo($row['recnum']);
			$html.="</td></tr>";
			// $html.='<div id="clear"></div>';

			
		// $html.="</div>";
			
			$recnum[]=$row['recnum'];		
			$count++;
			$this->kunde_row=$row;			
		}
		$html.="</table>";

		
		$html.="</center>";
	}
	
	private function getBikeInfo($recnum) {
		$count=0;
		$html="";
		$html.='<div><div id="radliste">';
		$html.='<b>Fahrräder</b><br>';
		$html.='<table id="liste2"><tr><td><b>Bike ID</b></td><td><b>Fahrradname</b></td><td><b>Rahmennummer</b></td><td><b>Aktion</b></td></tr>';

	
			$request ="SELECT *,rad_abholung.recnum as abholung_recnum,firmaname from `rad_abholung`";
			$request.=" left join rad_rad";
			$request.=" on rad_abholung.radnr = rad_rad.recnum"; 
			$request.=" left join rad_kunde";
			$request.=" on rad_abholung.kundenr = rad_kunde.recnum"; 
			$request.=" where rad_abholung.abholtermin_ist = '0000-00-00 00:00:00'";
			$request.=" and rad_abholung.kundenr='$recnum'";
			$firmaname="";
		
		$result=$this->db->query($request);
		$dt=new DateTime();
		while($row=$result->fetch_assoc()) {
			$this->abholung_recnumlist[]=$row['abholung_recnum'];
			//echo $row['abholung_recnum'].";";			
			if ($row['status']>0) {
				$status='id="status'.$row['status'].'"';
			} else  {
				$status='id="color'.$count.'"';
				if ($count = 0) { 
					$count=1;
				}else {
					$count=0;
				}
			}
			if (empty($firmaname) and !empty($row['firmaname'])) {
				$firmaname=$row['firmaname'];
			}
			$_POST['info_abholung']=$row['info_abholung'];
			$html.='<tr '.$status.'><td>'.$row['rebikeid'].'</td><td>'.$row['marke'].' '.$row['modell'].'</td><td>'.$row['rahmennr'].'</td>';
			$html.='<td>';
			$html.='<form method="POST" action="kunde_bestaetigung.php">';
			$html.='<input type="hidden" name="recnum" value="'.$row['abholung_recnum'].'">';
			$html.='<input id="status1" type="submit" name="wahl1"  value="bestätigen">&nbsp;';
			$html.='<input id="status2" type="submit" name="wahl2"  value="ablehnen">&nbsp;<br>';
			// $html.='<input id="status3" type="submit" name="wahl3A" value="offen">&nbsp;';
			// $html.='<input id="status3" type="submit" name="wahl3B" value="Urlaub"><br>';
			$html.='<input id="status3" type="submit" name="wahl3C" value="Krank">&nbsp;';
			$html.='<input id="status4" type="submit" name="wahl4"  value="geklaut">&nbsp;';
			$html.='<input              type="hidden" name="rebikeid"  value="'.$_POST['rebikeid'].'">&nbsp;';
			$html.='<input              type="hidden" name="mail"      value="'.$_POST['mail'].'">&nbsp;';
			$html.='</form>';
			$html.='</td>';			
			$html.='</tr>';

			$dt=new DateTime($row['abholtermin_soll']);
		}
		$html.='</table>';
		
		$html.='</div><br>';
		// $dt=new DateTime($row['abholtermin_soll']);
		//$html2 ='<div id="radliste" style="font-size:1.5em;"><b>Abhol-Termin:'.$dt->format("d.m.Y")." circa ".$dt->format("H:i").'</b></div>';
		$html2 ='<div id="radliste">';
		$html2.='<p style="font-size:1.50em;margin:0;"><b>Abhol-Termin:'.$dt->format("d.m.Y").'</b></p>';
		$html2.='<p style="font-size:1.00em;margin:0;">Einen Termin mit ungefährer Uhrzeit erhalten Sie von uns später in einer E-Mail</p>';
		$html2.='</div>';
		$html2.='<br>';

		$html.='<center>';
		$html.='<form method="POST" action="kunde_bestaetigung.php">';
		$html.='<table style="border:solid red 1px;width:99%;">'; 
		$html.='<tr><th><b>';
		$html.='Infos zur Abholung !<br>';
		if (!empty($firmaname)) {
			$html.='WICHTIG:<br>Bitte die Öffnungszeiten Ihrer Firma<br>für die Erreichbarkeit angeben!';
		}
		$html.='</b></th></tr>';
		
		// var_dump( $this->abholung_recnumlist);
		// echo implode(" and recnum=",$this->abholung_recnumlist);
		if (sizeof($this->abholung_recnumlist)>0)  {
			$_POST['abholung_recnumlist']='recnum='.implode(" or recnum=",$this->abholung_recnumlist);
		} else {
			$_POST['abholung_recnumlist']="";
		}
		$html.='<input type="hidden" value="'.$_POST['abholung_recnumlist'].'" name="abholung_recnumlist">';
		$html.='<tr><td style="text-align:center;"><textarea name="info_abholung" style="width:95%;height:5rem;">'.$_POST['info_abholung'].'</textarea></td></tr>';
		$html.='<tr><td style="text-align:center;"><input name="info_change" type="submit" value="Infos ändern"></td></tr>';
		$html.='</table>';
		$html.='<input              type="hidden" name="rebikeid"  value="'.$_POST['rebikeid'].'">';
		$html.='</form>';
		$html.='</center>';


		
		$this->html.=$html2.$html;

	}
	
}
// Ende der Klasse



//=================================================================
// Begin
//=================================================================
if (!empty($_POST['mail'])) {
	$mail=$_POST['mail'];
}
if (!empty($_SESSION['mail'])) {
	$_POST['mail']=$_SESSION['mail'];
}
if (!empty($_POST['mail']) and !empty($_POST['rebikeid'])) {
	// 1. Versuch über die Session, wenns schief geht über eine eventuell veränderte Mail
	
	$request ="SELECT count(*) as count from rad_abholung"; 
	$request.=" left join rad_kunde";
	$request.=" on rad_kunde.recnum = rad_abholung.kundenr";
	$request.=" left join rad_rad";
	$request.=" on rad_rad.recnum = rad_abholung.radnr";
	$request.=" where `rebikeid`='".$_POST['rebikeid']."'";
	$request.=" and mail='".$_POST['mail']."'";
	$result=$db->query($request);
	$row=$result->fetch_assoc();
	if ($row['count'] ==0) {
		// 2. Versuch
		if ($mail != $_POST['mail']) {
			$_POST['mail']=$mail;
			$request ="SELECT count(*) as count from rad_abholung"; 
			$request.=" left join rad_kunde";
			$request.=" on rad_kunde.recnum = rad_abholung.kundenr";
			$request.=" left join rad_rad";
			$request.=" on rad_rad.recnum = rad_abholung.radnr";
			$request.=" where `rebikeid`='".$_POST['rebikeid']."'";
			$request.=" and mail='".$_POST['mail']."'";
			$result=$db->query($request);
			$row=$result->fetch_assoc();
			if ($row['count'] ==0) {
				$msg="Mail und Bike ID stimmen nicht überein!";
			} else {
				$_SESSION['mail']=$_POST['mail'];
			}

			
		} else {
			$msg="Mail und Bike ID stimmen nicht überein!";
		}
	} else {
		// hier alles Fertig und eingeloggt
		
		$_SESSION['mail']=$_POST['mail'];
		
	}
} else {
	$msg="Bitte Logindaten eingeben:";
	if (empty($_POST['mail'])) {
		$_POST['mail']="";
	}
	if (empty($_POST['rebikeid'])) {
		$_POST['rebikeid']="";
	}
}


if (!empty($msg)) {
	$html="<center>";
	$html.='<h2>'.$msg.'</h2>';
	$html.='<form action="kunde_bestaetigung.php" method="POST">';
	$html.='<table>';
	$html.='<tr><th>Mail</th><td><input style="width: 200px;" type="text" name="mail" value="'.$_POST['mail'].'"></tr>';
	$html.='<tr><th>Bike-ID</th><td><input type="text" name="rebikeid" value="'.$_POST['rebikeid'].'"></tr>';
	$html.='</table>';
	$html.='<input type="submit" value="LOS">';
	$html.='</form>';
	$html.="</center>";

	echo $out->header("Bestätigung der Adresse und Räder");
	echo $out->kopf();
	echo $html;
	echo $out->footer();
	
	exit;
}



$status=0;
if (!empty($_POST['wahl1'])) {
	$status=1;
	$info="";
}
if (!empty($_POST['wahl2'])) {
	$status=2;
	$info="";
}
if (!empty($_POST['wahl3A'])) {
	$status=3;
	$info="keine Angabe";
}
if (!empty($_POST['wahl3B'])) {
	$status=3;
	$info="Urlaub";
}
if (!empty($_POST['wahl3C'])) {
	$status=3;
	$info="Krank";
}
if (!empty($_POST['wahl4'])) {
	$status=4;
	$info="Fahrrad geklaut";
}


function isAllowed(&$db) {
	$request ="select `abholtermin_soll` from `rad_abholung` ";
	$request.=" where `kundenr`=";
	$request.=" (SELECT recnum from rad_kunde where `mail`='".$_POST['mail']."' limit 1)";

	$result=$db->query($request);		
	$row=$result->fetch_assoc();
	$dt=new DateTime($row['abholtermin_soll']);
	$dt->modify("-8 hours");
	$dt_now=new DateTime();
	$msg="";
	
	if ($dt < $dt_now) {
		$msg="Ändern der Daten ab 8 Stunden vor der Abholung nicht mehr möglich";
	}
	return $msg;
		
	
	
	//--- 4 Stunden nach änderung ---
	$request="select `change_datum` from `rad_kunde` where `mail`='".$_POST['mail']."' limit 1";
	// $request="select `change_datum` from `rad_kunde`  limit 1";

	$result=$db->query($request);		
	$row=$result->fetch_assoc();

	if ($row['change_datum'] == NULL) {
		$dt=new DateTime();
	} else {
		$dt=new DateTime($row['change_datum']);
	}
	$dt->modify("+4 hours");
	$dt_now=new DateTime();
	$msg="";
	if ($dt_now > $dt) {
		$msg="Ändern der Daten nicht mehr möglich, Sie haben bereits Ihre Leasing-Bikes bestätigt!";
	}
	return $msg;
}			

if (!empty($_POST['info_abholung']) and isset($_POST['info_change'])) {
	$msg=isAllowed($db);
	if ($msg == "") {
		$request ="update rad_abholung set info_abholung='".$_POST['info_abholung']."', changed=(changed | 1)";
		$request.="	where ".$_POST['abholung_recnumlist'];
		// hier ändern !!

		// echo $request."<br>";
		if ($result=$db->query($request)) {
			$msg="Abholungsinfo wurde geändert!";			
		} else {
			$msg="Abholungsinfo konnte nicht aktualisiert werden!";
		}
	}
}

if ($status>0) {
	// echo "STATUS:".$_POST['recnum'];
	$msg=isAllowed($db);
	if ($msg == "") {
		$request="update rad_abholung set status='".$status."',info='".$info."' where recnum='".$_POST['recnum']."'";
		if ($result=$db->query($request)) {
			$msg="Sie haben den Abholstatus in ".array("","Bestätigt","Abgelehnt","Krank","geklaut")[$status]." geändert";
		    $request="update `rad_kunde` set `change_datum`=now() where `mail`='".$_POST['mail']."' and `change_datum` is NULL";
			$result=$db->query($request);
		} else {
			$msg="Status konnte nicht aktualisiert werden!";
		}
	}
}

$abh->getHtmlByMail();
$where="";
foreach ($abh->kunde_recnumlist as $k => $v) {
	$where.=" AND ";
	$where.="`recnum` != '".$v."'";
}
$request="SELECT * from `rad_kunde` where `plz`='".$abh->kunde_row['plz']."' and `strasse`='".$abh->kunde_row['strasse']."'  $where";

$abh->getHtmlByRequest($request);

echo $out->header("Bestätigung der Adresse und Räder");
echo $out->kopf();
if (!empty($msg)) {
	echo '<center><h2 style="text-align:center;border:1px solid orange;width:50%">'.$msg.'</h2></center>';
}
echo $abh->html;
echo $out->footer();

?>
