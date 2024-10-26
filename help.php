<?php
// include "class/class_session.php";
include "class/dbconnect.php";
include "class/class_menu.php";
include "class/class_output.php";


$html="";
// $html.='<div style="display: inline-block;width:80%;background-color:#EEEEEE;ocupacy:0.9; text-align:left;">';
$html.='<center>';
$html.='<div style="display: inline-block;text-align:left !important;background-color:#EEEEEE;opacity:0.9;padding:10px;border-radius: 15px;">';
$html.='<b>REIHENFOLGE</b>';
$html.='<br>1. Import von Rebike:<i>Erst mal die Daten der Bikes und Kunden holen</i>';
$html.="<br>2. Export nach Multiroute: <i>Routen Optimierung</i>";
$html.="<br>3. Import von Multiroute: <i>für die Weiterverarbeitung</i>";
$html.="<br>4. Zeitplanung : <i>Hat der Kunde Zeit ?</i>";
$html.="<br>5. Bestätigungsmail versenden: <i>für alle zugesagten und unbeantworteten Kunden</i>";
$html.="<br>6. Erinnerungsmail versenden: <i>für alle Kunden die noch nicht geantwortet haben</i>";
$html.="<br>7. ";
$html.='</div>';
$html.='</center>';



$menu =    new Menu();
$out =     new Output();
echo $out->header();
echo $menu->out("Hilfeübersicht");
echo $html;
echo $out->footer();


?>
