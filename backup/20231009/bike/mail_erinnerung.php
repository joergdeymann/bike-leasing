<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
include "class/class_mail.php";

$sim_mailto="joergdeymann@web.de";

$out =     new Output();
$menu =    new Menu();

$html="";
if (empty($_POST['erfassung']) and empty($_POST['sendmail'])) {
	$_POST['erfassung']="";
}

echo $out->header();
echo $menu->out("4.2 Erinnerungs Nachricht für den Kunden zur Bestätigung");


echo '<center><form action="mail_erinnerung.php" method="POST">';
echo '<table id="input">';
echo '<tr>';
echo '<th>Datum der Erfassung der Fahrräder<br>(Leer eingabe = alle)</th>';
echo '<td><input type="date" name="erfassung" value="'.$_POST['erfassung'].'"></td>';
echo '</tr>';
echo '</table>';
echo 'Simulation <input type="checkbox" name="sim" value="Ja" checked><br>';
echo '<input name="sendmail" type="submit" value="Mails versenden">';
echo '</form></center>';


if (empty($_POST['sendmail'])) {
	exit;
}
	
$dt=new DateTime($_POST['erfassung']);
$request="SELECT rebikeid,strasse,plz,ort,rad_abholung.recnum as recnum,pos,abholtermin_soll,fahrzeit,km,vorname,nachname,mail,count(kundenr) as anz,kundenr from rad_abholung";
$request.=" left join rad_kunde"; 
$request.=" on rad_kunde.recnum = rad_abholung.kundenr"; 
$request.=" left join rad_rad"; 
$request.=" on rad_rad.recnum = rad_abholung.radnr"; 
$request.=" where pos>0";
if (!empty($_POST['erfassung'])) {
	$request.=" and erfassung='".$dt->format("Y-m-d")."'";
}
$request.=" and radnr>0";
$request.=" and `status`=0"; // Nur unbeantwortete
$request.=" and abholtermin_soll >= '".(new DateTime())->format("Y-m-d H:i:s")."'";
$request.=" and abholtermin_ist = '0000-00-00 00:00:00'";
$request.=" group by rad_kunde.mail";
$request.=" order by pos";


$content=file_get_contents("mail_erinnerung.html");


$result=$db->query($request);
$html.='<center><table id="liste">';
$html_message="";
$count_ok=0;
$count_err=0;
while ($row=$result->fetch_assoc()) {	
	$row['datum']=(new DateTime($row['abholtermin_soll']))->format("d.m.Y");
	$row['uhrzeit']=(new DateTime($row['abholtermin_soll']))->format("H:i");
	$message=$content;

	if ($row['anz'] > 1) {
		$row['ihr_geleastes_fahrrad']="ihrer";
	} else {
		$row['ihr_geleastes_fahrrad']="ihres";
	}		
	
	foreach($row as $k => $v) {
		$message=str_ireplace("*$k*",$v,$message);
	}
	
	$mail=$row['mail'];
	
	$from="abholungen@all-transport24.de";
	$subject="Erinnerung: Bitte Bestätigen Sie die Abholung ".$row['ihr_geleastes_fahrrad']." geleasten Leasing-Bikes";
	$m = new sendmail();
	
	$m->setSubject($subject);
	$m->setMessage($message);
	$m->setSignature("");
	// $m->addAttachment("/img/testimg.jpg");
	$m->setFrom($from);
	$m->setReplyTo($from);
	$m->setTo($mail);
	
	
	$html.="<tr><td>$mail</td><td>";
	if (isset($_POST['sim'])) {
		if ($count_ok == 0) {
			if (empty($sim_mailto)) {
				$sim_mailto=$from;
			}
			$m->setTo($sim_mailto);
			$m->setSubject("Testmail Muster:".$subject);
			$m->send();  // Eine mail zu test 
		}
		$html_message.="<hr>Subject:".$subject."<br><br>";
		$html_message.=$message;
		$count_ok++;
		
		$html.='<p style="color:green">erfolgreich (simuliert)</p>';
	} else {
		if ($m->send()) {
			$html.='<p style="color:green">erfolgreich</p>';
			$count_ok++;
		} else {
			$html.='<p style="color:red">fehlgeschlagen</p>';
			$count_err++;
		}
	}
	$html.="</td></tr>";
}
$html.="</table></center></body></html>";

echo "<h2>$count_ok Nachrichten erfolgreich versendet <br>$count_err Nachrichten fehlgeschlagen</h2>"; 
echo "<br>";
echo $html_message;
echo $html;

?>
