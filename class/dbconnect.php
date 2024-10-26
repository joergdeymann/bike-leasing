<?php
// error_reporting(E_ALL);
// entspricht : ini_set('error_reporting', E_ALL);

$dbname = "k149450_dok"; 
$user="k149450_dok";
$pw="Sy8DY~,q8E4=i6s";
$host="10.35.233.28:3306";

$dbname = "k149450_dok"; 
$user="k149450_dok";
$pw="Sy8DY~,q8E4=i6s";
$host="10.35.233.28:3306";


$db = new mysqli($host, $user, $pw, $dbname);
if ($db->connect_errno) {
    die("Verbindung fehlgeschlagen: " . $db->connect_error);
}
$db->set_charset("utf8mb4");


/*
function mysql_fehler() {
     $logfehler = date("Y-m-d/H:i:s | ");
     $logfehler .= ('File: '.$_SERVER['PHP_SELF'].' | ');
     $logfehler .= ('MySQL Err.: '.$db->error().' | ');
     $logfehler .= ('IP: '.$_SERVER['REMOTE_ADDR']);  
     $logfehler .= chr(13).chr(10);
     error_log ($logfehler, 3, "mysql_error_log.txt");
     echo '<font color="#ff0000"><b><u><h3>Datenbank Fehler:</h3></u><br>';
     echo $logfehler;
     echo '<br>Das Script wurde vorzeitig beendet und der Webmaster wurde informiert!</b></font><br><br><input type="button" value="Zur&uuml;ck" onClick="history.back()">';    
} 
*/

?>