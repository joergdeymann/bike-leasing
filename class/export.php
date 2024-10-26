<?php

// export datei
// wichtig die mussen mit chr() geschrieben werden und nixht mit zb \xEF
// Die ersten Zeichen nennen sich BOM

/*
Für Excel mit utf-8
$path = 'path/to/directory/';
$fileName = 'myFile.csv';
$file = fopen($path . $fileName, 'w');
fwrite($file, chr(255) . chr(254) . mb_convert_encoding( $content, 'UTF-16LE', 'UTF-8'));
fclose ($file);
*/

/*
Andere mit utf-8:
$path = 'path/to/directory/';
$fileName = 'myFile.txt';
$file = fopen($path . $fileName, 'w');
fwrite($file, chr(239) . chr(187) . chr(191) . $content);
fclose ($file);
*/

/*
Formate BOM: https://de.wikipedia.org/wiki/Byte_Order_Mark
UTF-8: chr(239) . chr(187) . chr(191)
UTF-16LE: chr(255) . chr(254)
*/

/*
// Weiteres was zu beachten ist

1. Zeilenumbrüche / semikoln/Tab/Komma im Text -> dann in "" bei einer CSV
2. Wenn ein Anführungszeichen, zb in Zoll im Text ist -> dann keine anführungszeichen
3. Wenn 1 und 2 der Fall sind, dann in anführungszeichen und vorher das " in Typografisches umwandeln
*/

/*
UTF8 code entfernen ? nicht getestet

if(substr($str, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
    $str = substr($str, 3);
}  
*/


?>

