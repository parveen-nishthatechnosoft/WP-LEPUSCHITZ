<?php
require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/vendor/autoload.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/price.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/color.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/product.php');

use PhpOffice\PhpSpreadsheet;

class LTechnology {
    public $Code;
    public $Name;
    public $Mandator;
    public $Setup;
    public $Ranges = [];
}

class LRange {
    public $Colors;
    public $From;
    public $To;
    public $Price;
}

$lRows = [];

$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/kp.xlsx';

$lXlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

if ($lXlsxReader->canRead($file)) {
    // File readable, so try to parse it

    // Increase the memory
    ini_set('memory_limit','2048M');

    // Open the spreadsheet
    $lSpreadsheet = $lXlsxReader->load($file);
    //print_r($lSpreadsheet);
    $lAllSheets = $lSpreadsheet->getSheetNames();
    foreach ($lAllSheets as $lSheet) {
        //print_r($lSheet);
    }
    //Artikelpreisliste
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('Artikel');
    foreach ($lSheetDaten1->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            array_push($lCells, $cells);
        }

        if ($lCells[4] == "Druckkosten")
            $lRows[] = $lCells;
        //array_push($lRows, $lCells);
        //if (count($lRows) >= 100) break;
    }
    //echo count($lRows);
    //print_r($lRows);
}

$Technologies = [];

foreach (array_slice($lRows, 1) as $Row) {

    if ($Row[1] == "Siebdruck 1"){
        $Technology = new LTechnology();
        $Technology->Code = "S1";
        $Technology->Name = "Siebdruck";
        $Technology->Mandator = "KP";

        if ($Row[15]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = 1;
            $Range->To = $Row[10] - 1;
            $Range->Price = $Row[15];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[16]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[10];
            $Range->To = $Row[11] - 1;
            $Range->Price = $Row[16];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[17]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[11];
            $Range->To = $Row[12] - 1;
            $Range->Price = $Row[17];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[18]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[12];
            $Range->To = $Row[13] - 1;
            $Range->Price = $Row[18];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[19]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[13];
            $Range->To = $Row[14] - 1;
            $Range->Price = $Row[19];
            $Technology->Ranges[] = $Range;
        }

        $Technologies[$Technology->Code] = $Technology;
    }

    if ($Row[1] == "Siebdruck 2"){
        $Technology = new LTechnology();
        $Technology->Code = "S2";
        $Technology->Name = "Siebdruck";
        $Technology->Mandator = "KP";

        if ($Row[15]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = 1;
            $Range->To = $Row[10] - 1;
            $Range->Price = $Row[15];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[16]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[10];
            $Range->To = $Row[11] - 1;
            $Range->Price = $Row[16];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[17]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[11];
            $Range->To = $Row[12] - 1;
            $Range->Price = $Row[17];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[18]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[12];
            $Range->To = $Row[13] - 1;
            $Range->Price = $Row[18];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[19]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[13];
            $Range->To = $Row[14] - 1;
            $Range->Price = $Row[19];
            $Technology->Ranges[] = $Range;
        }

        $Technologies[$Technology->Code] = $Technology;
    }

    if ($Row[1] == "Digitaldruck 1"){
        $Technology = new LTechnology();
        $Technology->Code = "D1";
        $Technology->Name = "Digitaldruck";
        $Technology->Mandator = "KP";

        if ($Row[15]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = 1;
            $Range->To = $Row[10] - 1;
            $Range->Price = $Row[15];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[16]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[10];
            $Range->To = $Row[11] - 1;
            $Range->Price = $Row[16];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[17]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[11];
            $Range->To = $Row[12] - 1;
            $Range->Price = $Row[17];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[18]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[12];
            $Range->To = $Row[13] - 1;
            $Range->Price = $Row[18];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[19]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[13];
            $Range->To = $Row[14] - 1;
            $Range->Price = $Row[19];
            $Technology->Ranges[] = $Range;
        }

        $Technologies[$Technology->Code] = $Technology;
    }

    if ($Row[1] == "Digitaldruck 2"){
        $Technology = new LTechnology();
        $Technology->Code = "D2";
        $Technology->Name = "Digitaldruck";
        $Technology->Mandator = "KP";

        if ($Row[15]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = 1;
            $Range->To = $Row[10] - 1;
            $Range->Price = $Row[15];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[16]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[10];
            $Range->To = $Row[11] - 1;
            $Range->Price = $Row[16];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[17]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[11];
            $Range->To = $Row[12] - 1;
            $Range->Price = $Row[17];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[18]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[12];
            $Range->To = $Row[13] - 1;
            $Range->Price = $Row[18];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[19]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[13];
            $Range->To = $Row[14] - 1;
            $Range->Price = $Row[19];
            $Technology->Ranges[] = $Range;
        }

        $Technologies[$Technology->Code] = $Technology;
    }

    if ($Row[1] == "Tampondruck T1"){
        $Technology = new LTechnology();
        $Technology->Code = "T1";
        $Technology->Name = "Tampondruck";
        $Technology->Mandator = "KP";

        if ($Row[15]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = 1;
            $Range->To = $Row[10] - 1;
            $Range->Price = $Row[15];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[16]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[10];
            $Range->To = $Row[11] - 1;
            $Range->Price = $Row[16];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[17]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[11];
            $Range->To = $Row[12] - 1;
            $Range->Price = $Row[17];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[18]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[12];
            $Range->To = $Row[13] - 1;
            $Range->Price = $Row[18];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[19]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[13];
            $Range->To = $Row[14] - 1;
            $Range->Price = $Row[19];
            $Technology->Ranges[] = $Range;
        }

        $Technologies[$Technology->Code] = $Technology;
    }

    if ($Row[1] == "Tampondruck T2"){
        $Technology = new LTechnology();
        $Technology->Code = "T2";
        $Technology->Name = "Tampondruck";
        $Technology->Mandator = "KP";

        if ($Row[15]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = 1;
            $Range->To = $Row[10] - 1;
            $Range->Price = $Row[15];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[16]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[10];
            $Range->To = $Row[11] - 1;
            $Range->Price = $Row[16];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[17]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[11];
            $Range->To = $Row[12] - 1;
            $Range->Price = $Row[17];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[18]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[12];
            $Range->To = $Row[13] - 1;
            $Range->Price = $Row[18];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[19]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[13];
            $Range->To = $Row[14] - 1;
            $Range->Price = $Row[19];
            $Technology->Ranges[] = $Range;
        }

        $Technologies[$Technology->Code] = $Technology;
    }

    if ($Row[1] == "Gravurkosten"){
        $Technology = new LTechnology();
        $Technology->Code = "L";
        $Technology->Name = "Lasergravur";
        $Technology->Mandator = "KP";

        if ($Row[15]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = 1;
            $Range->To = $Row[10] - 1;
            $Range->Price = $Row[15];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[16]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[10];
            $Range->To = $Row[11] - 1;
            $Range->Price = $Row[16];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[17]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[11];
            $Range->To = $Row[12] - 1;
            $Range->Price = $Row[17];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[18]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[12];
            $Range->To = $Row[13] - 1;
            $Range->Price = $Row[18];
            $Technology->Ranges[] = $Range;
        }
        if ($Row[19]) {
            $Range = new LRange();
            $Range->Colors = 1;
            $Range->From = $Row[13];
            $Range->To = $Row[14] - 1;
            $Range->Price = $Row[19];
            $Technology->Ranges[] = $Range;
        }

        $Technologies[$Technology->Code] = $Technology;
    }
}

foreach (array_slice($lRows, 1) as $Row) {

    if ($Row[1] == "Siebkosten") {
        $Technologies['S1']->Setup = round($Row[15], 2);
        $Technologies['S2']->Setup = round($Row[15], 2);
    }

    if ($Row[1] == "Vorkosten Digitaldruck") {
        $Technologies['D1']->Setup = round($Row[15], 2);
        $Technologies['D2']->Setup = round($Row[15], 2);
    }

    if ($Row[1] == "Vorkosten Lasergravur") {
        $Technologies['L']->Setup = round($Row[15], 2);
    }

    if ($Row[1] == "Klischeekosten") {
        $Technologies['T1']->Setup = round($Row[15], 2);
        $Technologies['T2']->Setup = round($Row[15], 2);
    }
}

foreach ($Technologies as $lTechnology) {

    $isNewTech = true;

    $args = array(
        'post_type' => 'technology',
        'meta_key' => 'code',
        'meta_value' => 'KP_' . $lTechnology->Code
    );

    $post_query = new WP_Query($args);

    while($post_query->have_posts()) {
        $post_query->the_post();

        $isNewTech = false;
        break;
    }

    if ($isNewTech) {
        $newTech = wp_insert_post(array(
            'post_title' => $lTechnology->Code,
            'post_name' => sanitize_title($lTechnology->Code),
            'post_content' => null,
            'post_status' => 'publish',
            'post_type' => 'technology'
        ));

        update_post_meta($newTech, 'code', "KP_" . $lTechnology->Code);
        update_post_meta($newTech, 'name', $lTechnology->Name);
        update_post_meta($newTech, 'mandator', $lTechnology->Mandator);

        foreach ($lTechnology->Ranges as $aRange) {
            $row_range = array(
                'quantity_from' => (string) $aRange->From,
                'quantity_to' => (string) $aRange->To,
                'unit_price' => (string) $aRange->Price,
                'number_of_colors' => (string) $aRange->Colors,
                'setup_cost' => (string) $lTechnology->Setup
            );
            add_row('ranges', $row_range, $newTech);
        }
    }
}

print_r($Technologies);
