<?php
//echo "Hi";

require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet;

$lRows = [];

$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/test.xlsx';

$lXlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

if ($lXlsxReader->canRead($file)) {
    // File readable, so try to parse it

    // Increase the memory
    ini_set('memory_limit','1024M');

    // Open the spreadsheet
    $lSpreadsheet = $lXlsxReader->load($file);
    //print_r($lSpreadsheet);
    $lAllSheets = $lSpreadsheet->getSheetNames();
    foreach ($lAllSheets as $lSheet) {
        //print_r($lSheet);
    }
    //Artikelpreisliste
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('Artikelpreisliste');
    $rows = [];
    foreach ($lSheetDaten1->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            array_push($lCells, $cells);
        }

        $rows[] = $cells;
        $lRows[] = $lCells;
        //array_push($lRows, $lCells);
        if (count($lRows) >= 100) break;
    }
    //print_r($lRows);
    //echo count($lRows);
}