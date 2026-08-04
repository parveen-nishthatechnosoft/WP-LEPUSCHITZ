<?php

require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/vendor/autoload.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/price.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/color.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/product.php');

$lRows = [];

$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/jung.xlsx';

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
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('Artikelliste');

    foreach ($lSheetDaten1->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {

            $cells = $cell->getValue();
            $lCells[] = $cells;

            if (count($lCells) == 63) break;

        }

        $lRows[] = $lCells;

        //if (count($lRows) >= 10) break;
    }

    //print_r($lRows);
}

$lProducts = [];
$imgPrefix = "http://lepuschitz-promotion.ewebdev2.proman.at/wp-content/uploads/jung/";

foreach (array_slice($lRows, 1) as $lRow) {

    if (isset($lProducts[(string) $lRow[1]])) {

        $Farbe = explode('-', $lRow[4]);

        if ($Farbe[0] == "blank")
            $Farbe[0] = "white";
        elseif ($Farbe[0] == "matt")
            $Farbe[0] = "silber";

        $lProduct->AddColorWithImage($Farbe[0], $imgPrefix . $lRow[53]);

    } else {
        $lProduct = new LProduct((string) $lRow[1]);

        $ProductCode = explode('.', $lRow[0]);

        $lProduct->HashSum = md5(json_encode($lProduct));
        $lProduct->ProductCode = "JN_" . $ProductCode[0] . $ProductCode[1];
        $lProduct->Description = $lRow[2];
        $lProduct->CategoryId = "JUNG";

        $imgUrl = $imgPrefix . $lRow[53];
        $lProduct->ImageUrl = $imgUrl;

        if ($lRow[33])
            $lProduct->AddPrice(round($lRow[33], 2), 1, (int) $lRow[43] - 1);
        if ($lRow[34])
            $lProduct->AddPrice(round($lRow[34], 2), (int) $lRow[43], (int) $lRow[44] - 1);
        if ($lRow[35])
            $lProduct->AddPrice(round($lRow[35], 2), (int) $lRow[44], (int) $lRow[45] - 1);
        if ($lRow[36])
            $lProduct->AddPrice(round($lRow[36], 2), (int) $lRow[45], (int) $lRow[46] - 1);
        if ($lRow[37])
            $lProduct->AddPrice(round($lRow[37], 2), (int) $lRow[46], (int) $lRow[47] - 1);
        if ($lRow[38])
            $lProduct->AddPrice(round($lRow[38], 2), (int) $lRow[47], (int) $lRow[58] - 1);
        if ($lRow[39])
            $lProduct->AddPrice(round($lRow[39], 2), (int) $lRow[58], (int) $lRow[49] - 1);
        if ($lRow[40])
            $lProduct->AddPrice(round($lRow[40], 2), (int) $lRow[49], (int) $lRow[50] - 1);
        if ($lRow[41])
            $lProduct->AddPrice(round($lRow[41], 2), (int) $lRow[50], (int) $lRow[51] - 1);
        if ($lRow[42])
            $lProduct->AddPrice(round($lRow[42], 2), (int) $lRow[51], (int) $lRow[52]);

        $Farbe = explode('-', $lRow[4]);

        if ($Farbe[0] == "blank")
            $Farbe[0] = "white";
        elseif ($Farbe[0] == "matt")
            $Farbe[0] = "silber";

        $lProduct->AddColorWithImage($Farbe[0], $imgPrefix . $lRow[53]);

        $lProducts[(string) $lRow[1]] = $lProduct;
    }

}
echo count($lProducts);
print_r($lProducts);