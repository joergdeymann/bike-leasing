<?php
require "vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class Excel {
	public $col=1;
	public $row=1;
	// public $table; 
	private $spreadsheet;
	private $sheet;
	function __construct() {
		$this->spreadsheet = new Spreadsheet();
		$this->sheet = $this->spreadsheet->getActiveSheet();

	}
	function setRow($row) {		
		$this->col=1;
		foreach ($row as $k => $v) {
			$c=char(64+$this->col).$this->row;
 
			$this->sheet->setCellValue($c, $v);
		}		
		$this->row++;
	}
}

/*
0. Composer
a) DOWNLOAD
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

b) Kontrolle
php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

c)  Install
php composer-setup.php

c) Installer entfernen
php -r "unlink('composer-setup.php');"

d) Testen ob es geht
php composer.phar

in WIN
echo @php "%~dp0composer.phar" %*>composer.bat


1. 
- in den HTTPD Verzeichnis z.b: cd bike/
- Eingabe:
	composer require phpoffice/phpspreadsheet
	
2. einbindung in PHP - Script
require "../vendor/autoload.php"

#--------------------------------------------------------------------------------
# Beispiel Erstellen der xlsx auf den Server
#--------------------------------------------------------------------------------

<?php
// (A) LOAD & USE PHPSPREADSHEET LIBRARY
require "vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
// (B) CREATE A NEW SPREADSHEET
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
 
// (C) SET CELL VALUE
$sheet->setCellValue("A1", "Hello World!");
 
// (D) SAVE TO FILE
$writer = new Xlsx($spreadsheet);
$writer->save("1-hello.xlsx"); 
?>

#--------------------------------------------------------------------------------
# Beispiel Output erzeugen
#--------------------------------------------------------------------------------
<?php
// (A) LOAD & USE PHPSPREADSHEET LIBRARY
require "vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
// (B) CREATE A NEW SPREADSHEET
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
 
// (C) SET CELL VALUE
$sheet->setCellValue("A1", "Hello World!");
 
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

#--------------------------------------------------------------------------------
# Beispiel Lesen einer Xls datei
#--------------------------------------------------------------------------------

<?php
// (A) LOAD PHPSPREADSHEET LIBRARY
require "vendor/autoload.php";
 
// (B) READ FILE
$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
$spreadsheet = $reader->load("1-hello.xlsx");
 
// (C) READ CELLS
$sheet = $spreadsheet->getSheet(0);
$cell = $sheet->getCell("A1");
$value = $cell->getValue();
echo $value;
?>

#--------------------------------------------------------------------------------
# Beispiel Worksheets (Reiter)
#--------------------------------------------------------------------------------
<?php
// (A) LOAD & USE PHPSPREADSHEET LIBRARY
require "vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
// (B) FIRST WORKSHEET
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("First Sheet");
$sheet->setCellValue("A1", "Hello World!");
 
// (C) ADD WORKSHEET
$spreadsheet->createSheet();
 
// (C1) WORKSHEETS ARE IN RUNNING SEQUENCE NUMBER - 0, 1, 2, ...
$sheet = $spreadsheet->getSheet(1);
 
// (C2) ALTERNATIVELY, WE CAN GET BY NAME (AFTER WE SET THE TITLE)
//$sheet = $spreadsheet->getSheetByName("TITLE");
 
// (C3) SET WORKSHEET TITLE + CELL VALUE
$sheet->setTitle("Second Sheet");
$sheet->setCellValue("A1", "Foo Bar!");
 
// (D) COPY WORKSHEET
$evilClone = clone $spreadsheet->getSheet(0);
$evilClone->setTitle("Evil Clone");
$spreadsheet->addSheet($evilClone);
 
// (E) DELETE WORKSHEET
// $spreadsheet->removeSheetByIndex(0);
 
// (F) GET TOTAL NUMBER OF WORKSHEETS
// $total = $spreadsheet->getSheetCount();
 
// (G) SAVE TO SERVER
$writer = new Xlsx($spreadsheet);
$writer->save("4-worksheets.xlsx");
?>

#--------------------------------------------------------------------------------
# Beispiel Worksheets (Reiter)
#--------------------------------------------------------------------------------
<?php
// (A) LOAD & USE PHPSPREADSHEET LIBRARY
require "vendor/autoload.php";
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
 
// (B) CREATE A NEW SPREADSHEET & DUMMY DATA
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue("B1", "Hello");
$sheet->setCellValue("B2", "World!");
$sheet->setCellValue("B3", "Foo");
$sheet->setCellValue("B4", "Bar");
$sheet->getRowDimension("3")->setRowHeight(50);
 
// (C) SET STYLE
$styleSet = [
  // (C1) FONT
  "font" => [
    "bold" => true,
    "italic" => true,
    "underline" => true,
    "strikethrough" => true,
    "color" => ["argb" => "FFFF0000"],
    "name" => "Cooper Hewitt",
    "size" => 22
  ],
 
  // (C2) ALIGNMENT
  "alignment" => [
    "horizontal" => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
    // \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
    // \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
    "vertical" => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM
    // \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP
    // \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
  ],
 
  // (C3) BORDER
  "borders" => [
    "top" => [
      "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
      "color" => ["argb" => "FFFF0000"]
    ],
    "bottom" => [
      "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
      "color" => ["argb" => "FF00FF00"]
    ],
    "left" => [
      "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
      "color" => ["argb" => "FF0000FF"]
    ],
    "right" => [
      "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
      "color" => ["argb" => "FF0000FF"]
    ]
    /* ALTERNATIVELY, THIS WILL SET ALL
    "outline" => [
      "borderStyle" => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
      "color" => ["argb" => "FFFF0000"]
    ]*/
  ],
 
  // (C4) FILL
  "fill" => [
    // SOLID FILL
    "fillType" => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
    "color" => ["argb" => "FF110000"]
 
    /* GRADIENT FILL
    "fillType" => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
    "rotation" => 90,
    "startColor" => [
      "argb" => "FF000000",
    ],
    "endColor" => [
      "argb" => "FFFFFFFF",
    ]*/
  ]
];
$style = $sheet->getStyle("B3");
// $style = $sheet->getStyle("B1:B4");
$style->applyFromArray($styleSet);

// (D) SAVE TO FILE
$writer = new Xlsx($spreadsheet);
$writer->save("7-formatting.xlsx");
?>