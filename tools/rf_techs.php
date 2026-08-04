<?php
require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/vendor/autoload.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/price.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/color.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/product.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/writer.php');

use PhpOffice\PhpSpreadsheet;

$lRowsSetup = [];
$lRowsPrint = [];
$lRowsPositions = [];
$lRows = [];

$file1 = '/var/virtual_www/lepuschitzPROD/tools/testdata/rf_print.xlsx';
$file2 = '/var/virtual_www/lepuschitzPROD/tools/testdata/rf_costs.xlsx';
$file3 = '/var/virtual_www/lepuschitzPROD/tools/testdata/rf_positions.xlsx';
$file4 = '/var/virtual_www/lepuschitzPROD/tools/testdata/rf.xlsx';

$lXlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

if ($lXlsxReader->canRead($file4)) {
    // File readable, so try to parse it

    // Increase the memory
    ini_set('memory_limit','2048M');

    // Open the spreadsheet
    $lSpreadsheet = $lXlsxReader->load($file4);
    //print_r($lSpreadsheet);
    $lAllSheets = $lSpreadsheet->getSheetNames();
    foreach ($lAllSheets as $lSheet) {
        //print_r($lSheet);
    }
    //Artikelpreisliste
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('Artikeldaten');

    foreach ($lSheetDaten1->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            $lCells[] = $cells;
        }

        $lRows[$lCells[1]] = $lCells;

        //if (count($lRows) >= 100) break;
    }
    //echo count($lRows);
    //print_r($lRows);
}

if ($lXlsxReader->canRead($file2)) {
    // File readable, so try to parse it

    // Increase the memory
    ini_set('memory_limit','2048M');

    // Open the spreadsheet
    $lSpreadsheet = $lXlsxReader->load($file2);
    //print_r($lSpreadsheet);
    $lAllSheets = $lSpreadsheet->getSheetNames();
    foreach ($lAllSheets as $lSheet) {
        //print_r($lSheet);
    }
    //Artikelpreisliste
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('costs');

    foreach ($lSheetDaten1->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            $lCells[] = $cells;
        }

        $lRowsSetup[$lCells[0]] = $lCells;

        //if (count($lRows) >= 100) break;
    }
    //echo count($lRowsSetup);
    //print_r($lRowsSetup);
}

if ($lXlsxReader->canRead($file1)) {
    // File readable, so try to parse it

    // Increase the memory
    ini_set('memory_limit','2048M');

    // Open the spreadsheet
    $lSpreadsheet = $lXlsxReader->load($file1);
    //print_r($lSpreadsheet);
    $lAllSheets = $lSpreadsheet->getSheetNames();
    foreach ($lAllSheets as $lSheet) {
        //print_r($lSheet);
    }
    //Artikelpreisliste
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('print');

    foreach ($lSheetDaten1->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            $lCells[] = $cells;
        }

        $lRowsPrint[$lCells[0]] = $lCells;

        //if (count($lRows) >= 100) break;
    }
    //echo count($lRowsPrint);
    //print_r($lRowsPrint);
}

if ($lXlsxReader->canRead($file3)) {
    // File readable, so try to parse it

    // Increase the memory
    ini_set('memory_limit','2048M');

    // Open the spreadsheet
    $lSpreadsheet = $lXlsxReader->load($file3);
    //print_r($lSpreadsheet);
    $lAllSheets = $lSpreadsheet->getSheetNames();
    foreach ($lAllSheets as $lSheet) {
        //print_r($lSheet);
    }
    //Artikelpreisliste
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('positions');

    foreach ($lSheetDaten1->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            $lCells[] = $cells;
        }

        $lRowsPositions[$lCells[0]] = $lCells;

    }
    //echo count($lRowsPositions);
    //print_r($lRowsPositions);
}

function CreateNewProduct($aProduct) {
    // Increase time limit
    set_time_limit(10);

    $args = array(
        'post_title' => $aProduct->Title,
        'post_name' => sanitize_title($aProduct->Title),
        'post_content' => null,
        'post_status' => 'publish',
        'post_type' => 'product'
    );

    $post_id = wp_insert_post($args);

    update_post_meta($post_id, 'hash_sum', $aProduct->HashSum);
    update_post_meta( $post_id, 'product_id', $aProduct->ProductCode);
    update_post_meta( $post_id, 'isActive', true);
    update_post_meta( $post_id, 'has_deal', 'No');

    $parentTitle = $aProduct->CategoryId;
    $args_parent = array(
        'post_type' => 'product_subcategory',
        'post_title' => "Trinkflaschen",
        'post_status' => 'publish'
    );
    $parent_query = new WP_Query($args_parent);
    if ($parent_query->have_posts()){
        while($parent_query->have_posts()){
            $parent_query->the_post();
            $currentparentTitle = get_post_field('post_title');

            if ($currentparentTitle == $parentTitle) {
                update_post_meta( $post_id, 'parent', get_the_ID());
                break;
            }
        }
    }

    update_post_meta( $post_id, 'source_image', (string) $aProduct->ImageUrl);
    update_post_meta( $post_id, 'description', (string) $aProduct->Description);
    update_post_meta( $post_id, 'starting_price', (string) $aProduct->Prices[0]['PricePerPiece']);

    foreach ($aProduct->Prices as $Price) {

        if ($Price['To'] == '-1' && $Price['To'] = null)
            $To = null;
        else
            $To = $Price['To'];

        $row_quantity = array(
            'quantity_from' => (string) $Price['From'],
            'quantity_to' => (string) $To,
            'price' => (string) $Price['PricePerPiece']
        );
        add_row('quantities_and_prices', $row_quantity, $post_id);
    }

    foreach ($aProduct->Colors as $Color) {
        $row_variant = array(
            'color_name' => (string) $Color['Name'],
            'color_code' => (string) $Color['Code'],
            'color_image' => (string) $Color['Image']
        );
        add_row('variants', $row_variant, $post_id);
    }

    foreach ($aProduct->Positions as $Position) {
        $row = array(
            'serial' => $Position['Serial'],
            'position_name' => (string) $Position['Name'],
            'technologies' => []
        );

        foreach ($Position['Technologies'] as $Technology) {

            $tech_code = 'RF_' . $Technology['Code'];

            global $wpdb;

            $results = $wpdb->get_results("
                    SELECT posts.ID
                    FROM ".$wpdb->prefix."posts as posts
                    LEFT OUTER JOIN ".$wpdb->prefix."postmeta as meta ON
                            posts.ID = meta.post_id
                        AND meta.meta_key = 'code'
                    WHERE
                        posts.post_type = 'technology'
                    AND posts.post_status = 'publish'
                    AND meta.meta_value = '".$tech_code."'
                 ");

            if (count($results) == 0){
                continue;
            }

            $row['technologies'][] = array(
                'technology_code' => get_post($results[0]->ID),
                'technology_name' => get_field('name', $results[0]->ID),
                'max_colors' => 'Full Color'
            );

        }

        add_row('positions', $row, $post_id);
    }
}

function UpdateProduct($aProduct, $post_id) {
    // Increase time limit
    set_time_limit(10);

    update_post_meta($post_id, 'hash_sum', $aProduct->HashSum);
    update_post_meta( $post_id, 'product_id', $aProduct->ProductCode);
    update_post_meta( $post_id, 'isActive', true);
    update_post_meta( $post_id, 'has_deal', 'No');

    $parentTitle = $aProduct->CategoryId;
    $args_parent = array(
        'post_type' => 'product_subcategory',
        'post_title' => (string) $parentTitle,
        'post_status' => 'publish'
    );
    $parent_query = new WP_Query($args_parent);
    if ($parent_query->have_posts()){
        while($parent_query->have_posts()){
            $parent_query->the_post();
            $currentparentTitle = get_post_field('post_title');

            if ($currentparentTitle == $parentTitle) {
                update_post_meta( $post_id, 'parent', get_the_ID());
                break;
            }
        }
    }

    update_post_meta( $post_id, 'source_image', (string) $aProduct->ImageUrl);
    update_post_meta( $post_id, 'description', (string) $aProduct->Description);
    update_post_meta( $post_id, 'starting_price', (string) $aProduct->Prices[0]['PricePerPiece']);

    $oldPrices = get_field('quantities_and_prices', $post_id);
    if ($oldPrices) {
        for ($i = count($oldPrices); $i > 0; $i--) {
            delete_row('quantities_and_prices', $i, $post_id);
        }
    }

    foreach ($aProduct->Prices as $Price) {

        if ($Price['To'] == '-1' && $Price['To'] = null)
            $To = null;
        else
            $To = $Price['To'];

        $row_quantity = array(
            'quantity_from' => (string) $Price['From'],
            'quantity_to' => (string) $To,
            'price' => (string) $Price['PricePerPiece']
        );
        add_row('quantities_and_prices', $row_quantity, $post_id);
    }

    $oldColors = get_field('variants', $post_id);
    if ($oldColors) {
        for ($i = count($oldColors); $i > 0; $i--) {
            delete_row('variants', $i, $post_id);
        }
    }

    foreach ($aProduct->Colors as $Color) {
        $row_variant = array(
            'color_name' => (string) $Color['Name'],
            'color_code' => (string) $Color['Code'],
            'color_image' => (string) $Color['Image']
        );
        add_row('variants', $row_variant, $post_id);
    }

    $oldPositions = get_field('positions', $post_id);
    if ($oldPositions) {
        for ($i = count($oldPositions); $i > 0; $i--) {
            delete_row('positions', $i, $post_id);
        }
    }

    foreach ($aProduct->Positions as $Position) {
        $row = array(
            'serial' => $Position['Serial'],
            'position_name' => (string) $Position['Name']
        );

        $newPosition = add_row('positions', $row, $post_id);

        foreach ($Position->Technologies as $Technology) {

            $tech_code = 'RF_' . $Technology['Code'];

            global $wpdb;

            $results = $wpdb->get_results("
                    SELECT posts.ID
                    FROM " . $wpdb->prefix . "posts as posts
                    LEFT OUTER JOIN " . $wpdb->prefix . "postmeta as meta ON
                            posts.ID = meta.post_id
                        AND meta.meta_key = 'code'
                    WHERE
                        posts.post_type = 'technology'
                    AND posts.post_status = 'publish'
                    AND meta.meta_value = '" . $tech_code . "'
                 ");

            if (count($results) == 0) {
                continue;
            }

            $sub_row = array(
                'technology_code' => get_post($results[0]->ID),
                'technology_name' => get_field('name', $results[0]->ID),
                'max_colors' => "Full Color"
            );

            add_sub_row(array('positions', $newPosition, 'technologies'), $sub_row, $post_id);

        }
    }
}

$Products = [];
$Positions = [];
$Techs = [];
$imgPrefix = "http://lepuschitz-promotion.ewebdev2.proman.at/wp-content/uploads/rf/";

foreach (array_slice($lRowsPrint, 1) as $print) {

   $lRanges = [];

   if ($print[4]){
       $lRanges[] = array(
           'From' => 1,
           'To' => $print[5] - 1,
           'PricePerUnit' => $print[4]
       );
   }

    if ($print[6]){
        $lRanges[] = array(
            'From' => $print[5],
            'To' => $print[7] - 1,
            'PricePerUnit' => $print[6]
        );
    }

    if ($print[8]){
        $lRanges[] = array(
            'From' => $print[7],
            'To' => $print[9] - 1,
            'PricePerUnit' => $print[8]
        );
    }

    if ($print[10]){
        $lRanges[] = array(
            'From' => $print[9],
            'To' => $print[11] - 1,
            'PricePerUnit' => $print[10]
        );
    }

    if ($print[12]){
        $lRanges[] = array(
            'From' => $print[11],
            'To' => $print[13] - 1,
            'PricePerUnit' => $print[12]
        );
    }

    if ($print[14]){
        $lRanges[] = array(
            'From' => $print[13],
            'To' => $print[13] - 1,
            'PricePerUnit' => $print[14]
        );
    }

   $tech = array(
       'Code' => $print[0],
       'Name' => $print[1],
       'Setup' => $lRowsSetup[$print[18]][3],
       'Ranges' => $lRanges
   );

   $Techs[$print[0]] = $tech;

}

foreach (array_slice($lRowsPositions, 1) as $lPosition) {

    if (explode(',', $lPosition[3])){
        $techs = explode(',', $lPosition[3]);
    } else {
        $techs[0] = $lPosition[3];
    }

    $Technologies = [];

    foreach ($techs as $tech) {
        $Technologies[] = $Techs[$tech];
    }

    $Position = [
        'Serial' => $lPosition[0],
        'Name' => $lPosition[1],
        'Technologies' => $Technologies
    ];

    $Positions[$lPosition[0]] = $Position;

}

foreach (array_slice($lRows, 4) as $lRow) {

    if ($lRow[2] == 'Ja')
        continue;

    $lProduct = new LProduct($lRow[8]);
    $lProduct->Positions = [];

    $lProduct->HashSum = md5(json_encode($lProduct));
    $lProduct->ProductCode = "RF_" . $lRow[1];
    $lProduct->Description = $lRow[13];

    $imgUrl = $lRow[24];
    $lProduct->ImageUrl = $imgPrefix . $imgUrl;

    //Prices
    if ($lRow[48]){
        $lProduct->Prices[] = [
            'From' => $lRow[49],
            'To' => $lRow[51] - 1,
            'PricePerPiece' => round($lRow[48], 2)
        ];
    }
    if ($lRow[50]){
        $lProduct->Prices[] = [
            'From' => $lRow[51],
            'To' => $lRow[53] - 1,
            'PricePerPiece' => round($lRow[50], 2)
        ];
    }
    if ($lRow[52]){
        $lProduct->Prices[] = [
            'From' => $lRow[53],
            'To' => $lRow[55] - 1,
            'PricePerPiece' => round($lRow[52], 2)
        ];
    }
    if ($lRow[54]){
        $lProduct->Prices[] = [
            'From' => $lRow[55],
            'To' => $lRow[57] - 1,
            'PricePerPiece' => round($lRow[54], 2)
        ];
    }
    if ($lRow[56]){
        $lProduct->Prices[] = [
            'From' => $lRow[57],
            'To' => $lRow[59] - 1,
            'PricePerPiece' => round($lRow[56], 2)
        ];
    }
    if ($lRow[58]){
        $lProduct->Prices[] = [
            'From' => $lRow[59],
            'To' => $lRow[61] - 1,
            'PricePerPiece' => round($lRow[58], 2)
        ];
    }
    if ($lRow[60]){
        $lProduct->Prices[] = [
            'From' => $lRow[61],
            'To' => null,
            'PricePerPiece' => round($lRow[60], 2)
        ];
    }

    //Images
    if ($lRow[25]){
        $lProduct->Colors[] = [
            'Name' => "Thumbnail",
            'Code' => "#ffffff",
            'Image' => $imgPrefix . $lRow[25]
        ];
    }
    if ($lRow[26]){
        $lProduct->Colors[] = [
            'Name' => "Thumbnail",
            'Code' => "#ffffff",
            'Image' => $imgPrefix . $lRow[26]
        ];
    }
    if ($lRow[27]){
        $lProduct->Colors[] = [
            'Name' => "Thumbnail",
            'Code' => "#ffffff",
            'Image' => $imgPrefix . $lRow[27]
        ];
    }
    if ($lRow[28]){
        $lProduct->Colors[] = [
            'Name' => "Thumbnail",
            'Code' => "#ffffff",
            'Image' => $imgPrefix . $lRow[28]
        ];
    }
    if ($lRow[29]){
        $lProduct->Colors[] = [
            'Name' => "Thumbnail",
            'Code' => "#ffffff",
            'Image' => $imgPrefix . $lRow[29]
        ];
    }
    if ($lRow[30]){

        $images = explode("|", $lRow[30]);

        foreach ($images as $image) {
            $lProduct->Colors[] = [
                'Name' => "Thumbnail",
                'Code' => "#ffffff",
                'Image' => $imgPrefix . $image
            ];
        }
    }

    //Positions
    if ($lRow[88]){
        $lProduct->Positions[] = $Positions[$lRow[88]];
    }
    if ($lRow[90]){
        $lProduct->Positions[] = $Positions[$lRow[90]];
    }
    if ($lRow[92]){
        $lProduct->Positions[] = $Positions[$lRow[92]];
    }
    if ($lRow[94]){
        $lProduct->Positions[] = $Positions[$lRow[94]];
    }
    if ($lRow[96]){
        $lProduct->Positions[] = $Positions[$lRow[96]];
    }
    if ($lRow[98]){
        $lProduct->Positions[] = $Positions[$lRow[98]];
    }

    $lProducts[$lRow[1]] = $lProduct;

}

foreach ($Techs as $Technology) {

    $tech_code = 'RF_' . $Technology['Code'];

    global $wpdb;

    $results = $wpdb->get_results("
                    SELECT posts.ID
                    FROM ".$wpdb->prefix."posts as posts
                    LEFT OUTER JOIN ".$wpdb->prefix."postmeta as meta ON
                            posts.ID = meta.post_id
                        AND meta.meta_key = 'code'
                    WHERE
                        posts.post_type = 'technology'
                    AND posts.post_status = 'publish'
                    AND meta.meta_value = '".$tech_code."'
                 ");

    if (count($results) == 0){

        $tech_id = wp_insert_post(array(
            'post_title' => $Technology['Code'],
            'post_name' => sanitize_title($Technology['Code']),
            'post_content' => null,
            'post_status' => 'publish',
            'post_type' => 'technology'
        ));

        update_post_meta( $tech_id, 'name', $Technology['Name']);
        update_post_meta( $tech_id, 'code', "RF_" . $Technology['Code']);
        update_post_meta( $tech_id, 'mandator', "RF");

        foreach ($Technology['Ranges'] as $Range) {
            $row_range = array(
                'quantity_from' => (string) $Range['From'],
                'quantity_to' => (string) $Range['To'],
                'unit_price' => (string) $Range['PricePerUnit'],
                'number_of_colors' => "custom",
                'setup_cost' => (string) $Technology['Setup']
            );
            add_row('ranges', $row_range, $tech_id);
        }

    } else {

        foreach ($results as $result) {

            if (get_field('mandator', $result->ID) == "RF") {
                $tech_id = $result->ID;
                break;
            } else {
                continue;
            }

        }

    }

}

$n = 0;
foreach ($lProducts as $lProduct) {

    if ($n==2)
        break;

    $query = new WP_Query(array(
        'post_type' => 'product',
        'meta_key' => 'product_id',
        'meta_value' => $lProduct->ProductCode
    ));

    if ($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();

            if (get_field('hash_sum') != $lProduct->HashSum) {
                UpdateProduct(get_the_ID(), $lProduct);
            }
        }
    } else {
        CreateNewProduct($lProduct);
    }

    $n++;
}

echo count($lProducts);
print_r($lProducts);