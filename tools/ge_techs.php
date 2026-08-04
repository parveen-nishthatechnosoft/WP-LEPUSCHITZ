<?php
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
    $lAllSheets = $lSpreadsheet->getSheetNames();

    $Sheet = $lSpreadsheet->getSheetByName('Daten 5');
    $rows = [];
    foreach ($Sheet->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            $lCells[] = $cells;
        }

        $rows[] = $cells;
        $lRows[] = $lCells;
        //if (count($lRows) >= 10) break;
    }
}

$Prices = [];
$Names = [];
$nameStart = null;
$counter = 2;

foreach (array_slice($lRows, 2) as $Price) {

    if ($Price[0] == "FO") {
        $nameStart = $counter;
        break;
    } else {
        $Prices[] = $Price;
        $counter++;
    }
}

if ($nameStart) {
    foreach (array_slice($lRows, $nameStart + 2) as $Name) {

        if ($Name[0] == "FO") {
            break;
        } else {
            $holder = [];
            $holder[] = $Name[0];
            $holder[] = $Name[1];
            $Names[] = $holder;
        }
    }
}

$Techs = [];

foreach ($Prices as $Price) {

    foreach ($Names as $Name) {
        if ($Name[0] == $Price[0]){
            $techName = $Name[1];
            break;
        }
    }

    $Price[] = $techName;
    $Techs[] = $Price;
}

foreach ($Techs as $Tech) {
    set_time_limit(10);

    $post_id = wp_insert_post(array(
        'post_title' => $Tech[0],
        'post_name' => sanitize_title($Tech[0]),
        'post_content' => null,
        'post_status' => 'publish',
        'post_type' => 'technology'
    ));

    update_post_meta($post_id, 'code', 'GE_' . $Tech[0]);
    update_post_meta($post_id, 'name', $Tech[16]);
    update_post_meta($post_id, 'mandator', 'GE');

    add_row('ranges', array(
        'number_of_colors' => 'custom',
        'quantity_from' => '1',
        'quantity_to' => '50',
        'unit_price' => $Tech[1],
        'setup_cost' => $Tech[11]

    ), $post_id);

    add_row('ranges', array(
        'number_of_colors' => 'custom',
        'quantity_from' => '51',
        'quantity_to' => '100',
        'unit_price' => $Tech[3],
        'setup_cost' => $Tech[11]

    ), $post_id);

    add_row('ranges', array(
        'number_of_colors' => 'custom',
        'quantity_from' => '101',
        'quantity_to' => '250',
        'unit_price' => $Tech[4],
        'setup_cost' => $Tech[11]

    ), $post_id);

    add_row('ranges', array(
        'number_of_colors' => 'custom',
        'quantity_from' => '251',
        'quantity_to' => '500',
        'unit_price' => $Tech[5],
        'setup_cost' => $Tech[11]

    ), $post_id);

    add_row('ranges', array(
        'number_of_colors' => 'custom',
        'quantity_from' => '501',
        'quantity_to' => '1000',
        'unit_price' => $Tech[6],
        'setup_cost' => $Tech[11]

    ), $post_id);

    add_row('ranges', array(
        'number_of_colors' => 'custom',
        'quantity_from' => '1001',
        'quantity_to' => '2500',
        'unit_price' => $Tech[6],
        'setup_cost' => $Tech[11]

    ), $post_id);

    add_row('ranges', array(
        'number_of_colors' => 'custom',
        'quantity_from' => '2501',
        'quantity_to' => '5000',
        'unit_price' => $Tech[7],
        'setup_cost' => $Tech[11]

    ), $post_id);

    add_row('ranges', array(
        'number_of_colors' => 'custom',
        'quantity_from' => '5001',
        'quantity_to' => '10000',
        'unit_price' => $Tech[8],
        'setup_cost' => $Tech[11]

    ), $post_id);

    break;
}