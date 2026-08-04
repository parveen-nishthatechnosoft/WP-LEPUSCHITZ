<?php
//echo "Hi";

require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/vendor/autoload.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/price.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/color.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/product.php');

use PhpOffice\PhpSpreadsheet;

class LPosition {
    public $Name;
    public $Technologies = [];
}

class LTechnology {
    public $Code;
    public $Name;
    public $Colors;
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
            'color_name' => (string) $Color->ColorName,
            'color_code' => (string) $Color->ColorCode,
            'color_image' => (string) $Color->ImageUrl
        );
        add_row('variants', $row_variant, $post_id);
    }

    foreach ($aProduct->Positions as $Position) {
        $row = array(
            'serial' => "I",
            'position_name' => (string) $Position->Name,
            'technologies' => []
        );

        foreach ($Position->Technologies as $Technology) {

            $tech_code = 'KP_' . $Technology->Code;

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
                'max_colors' => (string) $Technology->Colors
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
    update_post_meta( $post_id, 'starting_price', (string) $aProduct->Prices[0]->PricePerPiece);

    $oldPrices = get_field('quantities_and_prices', $post_id);
    if ($oldPrices) {
        for ($i = count($oldPrices); $i > 0; $i--) {
            delete_row('quantities_and_prices', $i, $post_id);
        }
    }

    foreach ($aProduct->Prices as $Price) {
        $row_quantity = array(
            'quantity_from' => (string) $Price->From,
            'quantity_to' => (string) $Price->To,
            'price' => (string) $Price->PricePerPiece
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
            'color_name' => (string) $Color->ColorName,
            'color_code' => (string) $Color->ColorCode,
            'color_image' => (string) $Color->ImageUrl
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
            'serial' => "I",
            'position_name' => (string) $Position->Name
        );

        $newPosition = add_row('positions', $row, $post_id);

        foreach ($Position->Technologies as $Technology) {

            $tech_code = 'KP_' . $Technology->Code;

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
                'max_colors' => (string)$Technology->Colors
            );

            add_sub_row(array('positions', $newPosition, 'technologies'), $sub_row, $post_id);

        }
    }
}

$lProducts = [];
$imgPrefix = "http://lepuschitz-promotion.ewebdev2.proman.at/wp-content/uploads/kp/";

foreach (array_slice($lRows, 1) as $lRow) {

    //print_r($lRow[1]);
    //print_r($lProducts[$lRow[1]]);

    if (!$lRow[1])
        break;

    if (isset($lProducts[$lRow[1]])) {

        if (!isset($lProducts[$lRow[1]]->Colors[$lRow[2]])){
            $lProduct->AddColorWithImage($lRow[2], $imgPrefix . $lRow[6]);
        }

    } else {
        $lProduct = new LProduct((string) $lRow[1]);

        $lProduct->HashSum = md5(json_encode($lProduct));
        $lProduct->ProductCode = "KP_" . $lRow[0];
        $lProduct->Description = $lRow[3];
        $lProduct->CategoryId = "Feuerzeuge";

        $imgUrl = $imgPrefix . $lRow[6];
        $lProduct->ImageUrl = $imgUrl;

        $lProduct->AddColorWithImage($lRow[2], $imgPrefix . $lRow[6]);

        if ($lRow[15])
            $lProduct->AddPrice(round($lRow[15], 2), 1, $lRow[10] - 1);
        if ($lRow[16])
            $lProduct->AddPrice(round($lRow[16], 2), $lRow[10], $lRow[11] - 1);
        if ($lRow[17])
            $lProduct->AddPrice(round($lRow[17], 2), $lRow[11], $lRow[12] - 1);
        if ($lRow[18])
            $lProduct->AddPrice(round($lRow[18], 2), $lRow[12], $lRow[13] - 1);
        if ($lRow[19])
            $lProduct->AddPrice(round($lRow[19], 2), $lRow[13], $lRow[14] - 1);

        $techCodes = explode("/", $lRow[20]);

        $Position = new LPosition();
        $Position->Name = "Körper";
        $lProduct->Positions[] = $Position;

        foreach ($techCodes as $code) {
            $Technology = new LTechnology();
            $Technology->Code = $code;
            $Technology->Colors = 1;

            if ($code == 'S1' || $code == 'S2')
                $Technology->Name = "Siebdruck";
            elseif ($code == 'T1' || $code == 'T2')
                $Technology->Name = "Tampondruck";
            elseif ($code == 'D1' || $code == 'D2')
                $Technology->Name = "Digitaldruck";
            elseif ($code == 'L')
                $Technology->Name = "Lasergravur";

            $lProduct->Positions[0]->Technologies[] = $Technology;
        }

        $lProducts[$lRow[1]] = $lProduct;
    }
}

$n = 0;
foreach ($lProducts as $lProduct) {

    if ($n==10)
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