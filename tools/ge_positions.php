<?php
require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet;

class LProduct {
    public $ID;
    public $Positions = [];
}

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

$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/test.xlsx';

$lXlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

if ($lXlsxReader->canRead($file)) {
    // File readable, so try to parse it

    // Increase the memory
    ini_set('memory_limit','1024M');

    // Open the spreadsheet
    $lSpreadsheet = $lXlsxReader->load($file);
    $lAllSheets = $lSpreadsheet->getSheetNames();

    $Sheet = $lSpreadsheet->getSheetByName('Daten 1');
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

$Products = [];

foreach (array_slice($lRows, 1) as $Row) {

    if (isset($Products[$Row[0]])){

        if (isset($Products[$Row[0]]->Positions[$Row[2]])) {

            if (isset($Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]])) {

            } else {
                $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]] = new LTechnology();
                $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]]->Code = $Row[7];
                $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]]->Colors = $Row[9];
            }

        } else {
            $Products[$Row[0]]->Positions[$Row[2]] = new LPosition();
            $Products[$Row[0]]->Positions[$Row[2]]->Name = $Row[2];
            $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]] = new LTechnology();
            $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]]->Code = $Row[7];
            $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]]->Colors = $Row[9];
        }

    } else {
        $Products[$Row[0]] = new LProduct();
        $Products[$Row[0]]->ID = $Row[0];
        $Products[$Row[0]]->Positions[$Row[2]] = new LPosition();
        $Products[$Row[0]]->Positions[$Row[2]]->Name = $Row[2];
        $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]] = new LTechnology();
        $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]]->Code = $Row[7];
        $Products[$Row[0]]->Positions[$Row[2]]->Technologies[$Row[7]]->Colors = $Row[9];
    }
}

foreach ($Products as $Product) {

    $query = new WP_Query(array(
        'post_type' => 'product',
        'meta_key' => 'product_id',
        'meta_value' => 'GE_' . $Product->ID
    ));

    while($query->have_posts()) {
        $query->the_post();

        $positionExists = false;

        $lOldPositions = get_field('positions');
        if ($lOldPositions) {
            for ($i = count($lOldPositions); $i > 0; $i--) {
                delete_row('positions', $i);
            }
        }

        foreach ($Product->Positions as $aPosition) {

            if (have_rows('positions')){
                while (have_rows('positions')){
                    the_row();

                    if (get_sub_field('position_name') == $aPosition->Name) {
                        $positionExists = true;
                        break;
                    }
                }
            }

            if ($positionExists) {
                continue;
            }

            $row = array(
                'position_name' => (string) $aPosition->Name
            );
            $newPosition = add_row('positions', $row);

            foreach ($aPosition->Technologies as $Technology) {

                $tech_code = 'GE_' . $Technology->Code;

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

                $sub_row = array(
                    'technology_code' => get_post($results[0]->ID),
                    'technology_name' => get_field('name', $results[0]->ID),
                    'max_colors' => (string) $Technology->Colors
                );

                add_sub_row(array('positions', $newPosition, 'technologies'), $sub_row);
            }

            /*if (have_rows('positions')){
                while (have_rows('positions')){
                    the_row();

                    if (get_sub_field('serial') == (string) $aPosition->Serial){
                        foreach ($aPosition->Technologies as $Technology) {
                            $sub_row = array(
                                'technology_code' => (string) $Technology->Code,
                                'technology_name' => (string) $Technology->Name,
                                'max_colors' => (string) $Technology->MaxColors
                            );

                            add_sub_row('technologies', $sub_row);
                        }
                    }
                }
            }*/
        }
    }
}