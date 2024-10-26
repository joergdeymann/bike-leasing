<?php
error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);
^// (A) LOAD & USE PHPSPREADSHEET LIBRARY
echo "vor require";
require "../vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
echo "nach use";
// (B) CREATE A NEW SPREADSHEET
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
 
// (C) SET CELL VALUE
$sheet->setCellValue("A1", "Hello World!");
 
// (D) SAVE TO FILE
$writer = new Xlsx($spreadsheet);
echo "vor save";
$writer->save("1-hello.xlsx"); 
echo "Keine Fehler ?";

?>
