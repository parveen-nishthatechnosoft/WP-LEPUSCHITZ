<?php
//echo "Hi";

require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/vendor/autoload.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/price.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/color.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/product.php');

use PhpOffice\PhpSpreadsheet;

$lRows = [];

$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/lshop.xlsx';

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
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('Items');
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
        //if (count($lRows) >= 100) break;
    }
    //echo count($lRows);
    //print_r($lRows);
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

        $new_subcategory = wp_insert_post(array(
            'post_title' => $parentTitle,
            'post_name' => sanitize_title($parentTitle),
            'post_content' => null,
            'post_status' => 'publish',
            'post_type' => 'product_subcategory'
        ));

        update_post_meta( $new_subcategory, 'parent', '4282');
        update_post_meta( $post_id, 'parent', $new_subcategory);
    }

    update_post_meta( $post_id, 'source_image', (string) $aProduct->ImageUrl);
    update_post_meta( $post_id, 'description', (string) $aProduct->Description);
    update_post_meta( $post_id, 'starting_price', (string) $aProduct->Prices[0]->PricePerPiece);

    foreach ($aProduct->Prices as $Price) {
        $row_quantity = array(
            'quantity_from' => (string) $Price->From,
            'quantity_to' => (string) $Price->To,
            'price' => (string) $Price->PricePerPiece
        );
        add_row('quantities_and_prices', $row_quantity, $post_id);
    }

    foreach ($aProduct->Colors as $Color) {
        $row_variant = array(
            'color_name' => (string) $Color['ColorName'],
            'color_code' => (string) $Color['ColorCode'],
            'color_image' => (string) $Color['ImageUrl']
        );
        add_row('variants', $row_variant, $post_id);
    }

    foreach ($aProduct->Sizes as $Size) {
        $row_size = array(
            'size' => (string) $Size

        );
        add_row('product_sizes', $row_size, $post_id);
    }
}

$lProducts = [];
$imgPrefix = "http://lepuschitz-promotion.ewebdev2.proman.at/wp-content/uploads/lshop/";

foreach (array_slice($lRows, 1) as $lRow) {

    //print_r($lRow[1]);
    //print_r($lProducts[$lRow[1]]);

    if (isset($lProducts[$lRow[1]])) {

        if (!isset($lProducts[$lRow[1]]->Colors[$lRow[29]])){
            $lProducts[$lRow[1]]->Colors[$lRow[29]] = array(
                'ColorName' => $lRow[25],
                'ColorCode' => "#" . $lRow[29],
                'ImageUrl' => $imgPrefix . $lRow[47]
            );
        }

        if (!isset($lProduct->Sizes[$lRow[33]])){
            $lProduct->Sizes[$lRow[33]] = $lRow[33];
        }

    } else {
        $lProduct = new LProduct($lRow[48]);

        $lProduct->Sizes = [];

        $lProduct->HashSum = md5(json_encode($lProduct));
        $lProduct->ProductCode = "LS_" . $lRow[1];
        $lProduct->Description = $lRow[49];
        $lProduct->CategoryId = $lRow[55];

        $imgUrl = $imgPrefix . $lRow[47];
        $lProduct->ImageUrl = $imgUrl;

        $lProducts[$lRow[1]]->Colors[$lRow[29]] = array(
            'ColorName' => $lRow[25],
            'ColorCode' => "#" . $lRow[29],
            'ImageUrl' => $imgPrefix . $lRow[47]
        );
        $lProduct->AddPrice(round($lRow[18], 2), 1, 1000000);
        $lProduct->Sizes[$lRow[33]] = $lRow[33];

        $lProducts[$lRow[1]] = $lProduct;
        //print_r($lProduct);
    }
}

foreach ($lProducts as $lProduct) {

    $query = new WP_Query(array(
        'post_type' => 'product',
        'meta_key' => 'product_id',
        'meta_value' => $lProduct->ProductCode
    ));

    if ($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();

            if (get_field('hash_sum') != $lProduct->HashSum) {
                CreateNewProduct($lProduct);
            }
        }
    } else {
        CreateNewProduct($lProduct);
    }
}

echo count($lProducts);
print_r($lProducts);