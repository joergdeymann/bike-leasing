<?php
// (A) LOAD & USE PHPSPREADSHEET LIBRARY
require "../vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
// (B) CREATE A NEW SPREADSHEET
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
 
// (C) SET CELL VALUE
$sheet->setCellValue("A1", "Hello World!");
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);
// (D) SEND DOWNLOAD HEADERS
// ob_clean();
// ob_start();
$writer = new Xlsx($spreadsheet);
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment;filename=\"2-download.xlsx\"");
header("Cache-Control: max-age=0");
header("Expires: Fri, 11 Nov 2011 11:11:11 GMT");
header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
header("Cache-Control: cache, must-revalidate");
header("Pragma: public");
$writer->save("php://output");
// ob_end_flush();
?>
