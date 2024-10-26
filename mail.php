<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
include "class/class_mail.php";
include "class/class_abholung.php";
$out =     new Output();
$menu =    new Menu();
$abh = New Abholung($db);

$from="abholungen@all-transport24.de";
if (isset($_GET['mail'])) {
	$_POST['sim_mailto']=$_GET['mail'];
} 
if (empty($_POST['sim_mailto'])) {
	$_POST['sim_mailto']=$from;
}
$sim_mailto=$_POST['sim_mailto'];

$html="";



if (empty($_POST['erfassung']) and empty($_POST['sendmail'])) {
	$erfassung=$abh->getLastErfassung();
	//$_POST['erfassung']=(new DateTime())->format("Y-m-d");
	$_POST['erfassung']=$erfassung;
}

if (empty($_POST['erfassung'])) {
	$_POST['erfassung']="";
}

echo $out->header();
echo $menu->out("4.1. Versenden der Mail zur Aufforderung der Bestätigung");


echo '<center><form action="mail.php" method="POST">';
echo '<table id="input">';
echo '<tr>';
echo '<th>Datum der Erfassung der Fahrräder<br>(Leer eingabe = alle)</th>';
echo '<td><input type="date" name="erfassung" value="'.$_POST['erfassung'].'"></td>';
echo '</tr>';
echo '</table>';


echo 'Simulation zu '.$sim_mailto.' <input type="checkbox" name="sim" value="Ja" checked><br>';
echo '<input name="sim_mailto" type="hidden" value="'.$sim_mailto.'">';
echo '<input name="sendmail" type="submit" value="Mails versenden">';
echo '</form></center>';


if (empty($_POST['sendmail'])) {
	exit;
}


	
$request="SELECT rebikeid,strasse,plz,ort,rad_abholung.recnum as recnum,pos,abholtermin_soll,fahrzeit,km,vorname,nachname,mail,count(kundenr) as anz,kundenr from rad_abholung";
$request.=" left join rad_kunde"; 
$request.=" on rad_kunde.recnum = rad_abholung.kundenr"; 
$request.=" left join rad_rad"; 
$request.=" on rad_rad.recnum = rad_abholung.radnr"; 
$request.=" where pos>0";
$erfassung=$abh->getLastErfassung();


if (!empty($_POST['erfassung'])) {
	$dt=new DateTime($_POST['erfassung']);
	$request.=" and erfassung='".$dt->format("Y-m-d")."'";
}
$request.=" and radnr>0";
// $request.=" and status=0";
$request.=" and abholtermin_soll >= '".(new DateTime())->format("Y-m-d H:i:s")."'";
$request.=" and abholtermin_ist = '0000-00-00 00:00:00'";
$request.=" group by rad_kunde.mail";
$request.=" order by pos";

// echo $request;
// $row['ihr_geleastes_fahrrad']=
// $ihr_geleastes_fahrrad[0]="Ihres geleasten Fahrrades"
// $ihr_geleastes_fahrrad[1]="Ihrer geleasten Fahrräder"
// $row['ihr_fahrrad']=
// $ihr_fahrrad[0]="Ihr Fahrrad"
// $ihr_fahrrad[1]="Ihre Fahrräder";

$content=file_get_contents("mail.html");
$sig=file_get_contents("signatur.html");

$result=$db->query($request);
$html.='<center><table id="liste">';
$html_message="";
$count_ok=0;
$count_err=0;

while ($row=$result->fetch_assoc()) {	
	$row['datum']=(new DateTime($row['abholtermin_soll']))->format("d.m.Y");
	if ($row['anz'] > 1) {
		// $row['ihr_geleastes_fahrrad']="ihrer geleasten Fahrräder";
		$row['ihr_geleastes_fahrrad']="ihrer";
	} else {
//		$row['ihr_geleastes_fahrrad']="ihres geleasten Fahrrads";
		$row['ihr_geleastes_fahrrad']="ihres";
	}		
	$message=$content;
	
	foreach($row as $k => $v) {
		$message=str_ireplace("*$k*",$v,$message);
	}
	
	//$mail="joergdeymann@web.de"; // um zu sehen wie die Mail aussieht
	$mail=$row['mail'];
	$from="abholungen@all-transport24.de";
	$subject="Bitte Bestätigen Sie die Abholung ".$row['ihr_geleastes_fahrrad']." geleasten Leasing-Bikes";
	$m = new sendmail();
	
	$m->setSubject($subject);
	$m->setMessage($message);
	$m->setSignature($sig);
	// $m->addAttachment("/img/testimg.jpg");
	$m->setFrom($from);
	$m->setReplyTo($from);
	$m->setTo($mail);
	
	
	$html.="<tr><td>$mail</td><td>";
	if (isset($_POST['sim'])) {
		// echo "sim";
		if ($count_ok == 0) {
			if (empty($sim_mailto)) {
				$sim_mailto=$from;
			}
			//echo $sim_mailto;		
			$m->setTo($sim_mailto);
			$m->setSubject("Testmail Muster:".$subject);
			$m->send();  // Eine mail zu test 
		}

		$html_message.="<hr>Subject:".$subject;
		$html_message.=$message;
		
		$html.='<p style="color:green">erfolgreich (simuliert)</p>';
		$count_ok++;
		
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
