<?php
include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_database.php";
include "class/class_output.php";
include "class/class_menu.php";
include "class/class_mail.php";

$out =     new Output();
$menu =    new Menu();

$html="";
if (empty($_POST['erfassung']) and empty($_POST['sendmail'])) {
	$_POST['erfassung']=(new DateTime())->format("Y-m-d");
}

echo $out->header();
echo $menu->out("4.1. Versenden der Mail zur Aufforderung der Bestätigung");


echo '<center><form action="mail.php" method="POST">';
echo '<table>';
echo '<tr>';
echo '<th>Datum der Erfassung der Fahrräder</th>';
echo '<td><input type="date" name="erfassung" value="'.$_POST['erfassung'].'"></td>';
echo '</tr>';
echo '</table>';
echo 'Simulation <input type="checkbox" name="sim" value="Ja"><br>';
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
$request.=" and status=0";
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


$result=$db->query($request);
$html.='<center><table id="liste">';
while ($row=$result->fetch_assoc()) {	
	$mail=$row['mail'];
	$message="";
	$message.='<h1 style="background-color:red;">Bestätigen der Abholung Ihrer geleasten Fahrräder</h1>';
	$message.='Guten Tag, '.$row['vorname'].' '.$row['nachname'].' !<br>';

	$dt=new DateTime($row['abholtermin_soll']);
	$rad="Ihr Fahrrad";
	if ($row['anz'] > 1) {
		$rad="Ihre Fahrräder";
	}
	$message.='Wir holen '.$rad.' am '.$dt->format("d.m.Y").' ab.<br>';
	$message.='Drücken Sie folgenden Button um auf die Bestätigungsseite zu kommen:<br>';
	// $message.='<style>a:hover { font-color:white;}</style>';
	$message.='<a href="https://www.all-transport24.de/bike/kunde_bestaetigung.php?mail='.$row['mail'].'&rebikeid='.$row['rebikeid'].'" ';
	$message.=' style="border: 1px black solid; margin-left:100px;display:block; text-decoration:none;width:150px;height:100px;
	background-color:#00FF00;color:black;fon-weight:1000;font-size:2em;text-align:center;padding-top: 50px;">GO</a><br>';
	$message.='Dies ist eine einmalige Nachricht.<br>';
	
	$sig ='<br>Mit freundlichen Grüßen<br><br>Rasched Tamiz<br><br>Vertrieb<br>';
	$sig.='<div style="display:inline-block;margin-top:10px;padding:5px;border:1px solid red;">';
	$sig.="<b>All Transport 24 e.K</b><br>";
	$sig.="Bonifaciusstraße 160<br>";
	$sig.="D-45309 Essen<br><br>";

	$sig.="Telefon: +49 201 86210-0<br>";
	$sig.="Mobil: +49 152 02 88888 4<br>";
	$sig.="Fax: +49 201  86210-10<br>";
	$sig.="E-Mail: abholungen@all-transport24.de<br>";
	$sig.="Web: www.all-transport24.de<br>";
	$sig.="</div>";
	

	
	
	//$mail="joergdeymann@web.de"; // um zu sehen wie die Mail aussieht
	$from="abholungen@all-transport24.de";
	$m = new sendmail();
	$m->setSubject("Bitte Bestätigen Sie die Abholung Ihrer geleasten Fahrräder");
	$m->setMessage($message);
	$m->setSignature($sig);
	// $m->addAttachment("/img/testimg.jpg");
	$m->setFrom($from);
	$m->setReplyTo($from);
	$m->setTo($mail);
	

	$html.="<tr><td>$mail</td><td>";
	if (isset($_POST['sim'])) {
		$html.='<p style="color:green">erfolgreich (simuliert)</p>';
	} else {
		if ($m->send()) {
		// if ($m->testmail()) {
			$html.='<p style="color:green">erfolgreich</p>';
		} else {
			$html.='<p style="color:red">fehlgeschlagen</p>';
		}
	}
	$html.="</td></tr>";
}
$html.="</table></center></body></html>";

echo $html;

?>
