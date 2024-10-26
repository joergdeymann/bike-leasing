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
if (empty($_POST['abholtermin_soll'])) {
	$_POST['abholtermin_soll']="";
}
/*
if (empty($_POST['erfassung']) and empty($_POST['sendmail'])) {
	$_POST['erfassung']="";
}
*/

echo $out->header();
echo $menu->out("4.3 Versenden der Mail zur Bekanntgabe der Uhrzeit");
echo '<center><form action="mail_zeit1.php" method="POST">';
echo '<table id="input">';
echo '<tr>';
echo '<th>Abholdatum der Fahrräder<br>(Leer eingabe = alle)</th>';
echo '<td><input type="date" name="abholtermin_soll" value="'.$_POST['abholtermin_soll'].'"></td>';
echo '</tr>';

echo '<tr>';
echo '<th style="vertical-alig:top;">Status<br>wer darf die Mail bekommen ?</th>';
$checked=array("","","","","");
if (isset($_POST['status'])) {
	foreach($_POST['status'] as $k=>$v) {
		if (!is_null($_POST['status'][$k])) {
			$checked[$k]="checked";
		} else {
			$checked[$k]="";
		}
		//	if (isset($status[$k])) {
	}
} else {
	if (isset($_SERVER['HTTP_REFERER'])) {
		if (basename($_SERVER['HTTP_REFERER']) != basename($_SERVER['PHP_SELF'])) {
			$checked=array(0 => "checked",1 => "checked",2 => "",3 => "",4 => "");
		}
	} else {
		$checked=array(0 => "checked",1 => "checked",2 => "",3 => "",4 => "");
	}
}
echo '<td>   
<input type="checkbox" '.$checked[0].'        name="status[0]" value="0"> ungesehen<br> 
<input type="checkbox" '.$checked[1].'        name="status[1]" value="1"> bestätigt<br> 
<input type="checkbox" '.$checked[2].'        name="status[2]" value="2"> abgelehnt<br> 
<input type="checkbox" '.$checked[3].'        name="status[3]" value="3"> Krank/offen<br> 
<input type="checkbox" '.$checked[4].'        name="status[4]" value="4"> geklaut/Storno/Problemfall<br>';
echo '</td>';

echo '</tr>';
echo '</table>';

echo '<br>';

echo '<table id="input">';
// echo '<tr><th colspan=2>Simulation</th></tr>';

echo '<tr>';
echo '<th>Simulation aktivieren</th>';
echo '<td><input type="checkbox" name="sim" value="Ja" checked></td>';
echo '</tr>';

echo '<tr>';
echo '<th>Simulations Ergebnis<br>zur folgender Email senden:<br>
(leer=TO:abholungen@all-transport24.de)<br></th>';
echo '<td><input type="text" name="sim_mailto"  style="width:30em;"></td></tr></table>';

// $_POST['sim_mailto']="joergdeymann@web.de";
echo '<input name="sendmail" type="submit" value="Mails versenden">';
echo '</form></center>';


	
$dt=new DateTime($_POST['abholtermin_soll']);
$request="SELECT rebikeid,strasse,plz,ort,rad_abholung.recnum as recnum,pos,abholtermin_soll,fahrzeit,km,vorname,nachname,mail,count(kundenr) as anz,kundenr from rad_abholung";
$request.=" left join rad_kunde"; 
$request.=" on rad_kunde.recnum = rad_abholung.kundenr"; 
$request.=" left join rad_rad"; 
$request.=" on rad_rad.recnum = rad_abholung.radnr"; 
$request.=" where pos>0";

if (!empty($_POST['abholtermin_soll'])) {
	$request.=" and left(abholtermin_soll,10)='".$dt->format("Y-m-d")."'";
} else {
    $request.=" and abholtermin_soll >= '".(new DateTime())->format("Y-m-d H:i:s")."'";
}

$request.=" and radnr>0";
if (isset($_POST['status'])) {
	$request.=" AND (`status`='".implode("' or `status`='",$_POST['status'])."')";
}
/*
if (!empty($st)) {
	$request.=" and ($st)";
}
*/
		
// $request.=" and abholtermin_soll >= '".(new DateTime())->format("Y-m-d H:i:s")."'";
$request.=" and abholtermin_ist = '0000-00-00 00:00:00'";
$request.=" group by rad_kunde.mail";
$request.=" order by pos";

// echo $request;

// echo $request;
// $row['ihr_geleastes_fahrrad']=
// $ihr_geleastes_fahrrad[0]="Ihres geleasten Fahrrades"
// $ihr_geleastes_fahrrad[1]="Ihrer geleasten Fahrräder"
// $row['ihr_fahrrad']=
// $ihr_fahrrad[0]="Ihr Fahrrad"
// $ihr_fahrrad[1]="Ihre Fahrräder";

$content=file_get_contents("mail_zeit.html");


$result=$db->query($request);
$html.='<center><table id="liste">';
$html_message="";
$count_ok=0;
$count_err=0;

while ($row=$result->fetch_assoc()) {	
	$dt=new DateTime($row['abholtermin_soll']);
	$dt->modify("+".$row['fahrzeit']." minutes");
	$row['datum']=$dt->format("d.m.Y");	
	$row['uhrzeit']=$dt->format("H:i");
	$message=$content;
	
	foreach($row as $k => $v) {
		$message=str_ireplace("*$k*",$v,$message);
	}
	
	$mail=$row['mail'];
	
	// $mail="joergdeymann@web.de"; // um zu sehen wie die Mail aussieht
	$from="abholungen@all-transport24.de";
	$subject="Wir haben eine ungefähre Abholzeit der Leasing-Bikes für Sie!";
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
			if (empty($_POST['sim_mailto'])) {
				$sim_mailto=$from;
			} else {
				$sim_mailto=$_POST['sim_mailto'];
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

if (isset($_POST['sim'])) {
	echo "<h1>Simulation</h1>";
}

echo "<h2>$count_ok Nachrichten erfolgreich versendet <br>$count_err Nachrichten fehlgeschlagen</h2>"; 
echo "<br>";
echo $html_message;
echo $html;

?>
