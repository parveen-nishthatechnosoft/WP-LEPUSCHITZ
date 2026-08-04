<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '/var/virtual_www/lepuschitzPROD/tools/spout/Autoloader/autoload.php';

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;



$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/lshop.xlsx';
$reader = ReaderEntityFactory::createReaderFromFile($file);
$reader->open($file);

$max_Rows = 150;
$_max_cells = 150;


// error_log('In SPOUT');

$_sheet_name = 'Items';
$_found_sheet = false;

foreach ($reader->getSheetIterator() as $sheet)
{
	// Stores Data in a arary
	$_data = array(
   			'maxCellSize' => '',
   			'data' => array(),
   			'sheetname' => array(),
   			'debug' => array(),
   		);
	// Store available Sheet Names
	$_data['sheetname'][] = $sheet->getName();


    // only read data from $_sheet_name sheet
    if ($sheet->getName() == $_sheet_name) 
    {
    	$_data['debug'][] = 'Sheet found';
   		$maxCellSize = 0;   		
   		$_rows_counter = 0;
   		$_found_sheet = true;

        foreach ( $sheet->getRowIterator() as $row )
        {
        	$_rows_counter++;
        	 
        	$cells = $row->getCells();

        	// Should find Max Cell size
        	$maxCellSize = max($maxCellSize, count($cells) );
            // do something with the row, get cells data
          
            $lCells = array();

            $cells_counter = 0;
            foreach ($cells as $keyCell => $cell)
            {
                $value = $cell->getValue();
                $lCells[] = $value;   

                $cells_counter++;
                // Reached Limit of cells?
                if ( $cells_counter >= $_max_cells )
                {
                	break;
                }
            }            
            $_data['data'][] = $lCells;

            // Reached limited of Rows?
            // Uncomment if you want all rows
            if ($_rows_counter >= $max_Rows )
            {
            	break;
            }
        }
        // Store MaxSize of cells
   		$_data['maxCellSize'] = $maxCellSize;
        break; // no need to read more sheets
    } 
}

$reader->close();


if (!$_found_sheet )
{
	$_data['debug'][] = 'Sheet not found';
} 

echo('<pre>');
print_r ( $_data );
echo ('</pre>');


