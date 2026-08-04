<?php

require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');

class LTechnology {
    public $HashSum;
    public $Code;
    public $Name;
    public $SizeFrom;
    public $SizeTo;
    public $Ranges = array();
}

class LRange {
    public $NumberOfColors;
    public $From;
    public $To;
    public $UnitPrice;
    public $SetupPrice;
}

function CreateNewTechnology(LTechnology $aTechnology) {
    // Increase time limit
    set_time_limit(10);

    $args_technology = array(
        'post_title' => (string) $aTechnology->Code,
        'post_name' => sanitize_title((string) $aTechnology->Code),
        'post_content' => null,
        'post_status' => 'publish',
        'post_type' => 'technology'
    );

    $id = wp_insert_post($args_technology);

    update_post_meta($id, 'hash_sum', $aTechnology->HashSum);
    update_post_meta($id, 'code', (string) $aTechnology->Code);
    update_post_meta($id, 'name', (string) $aTechnology->Name);
    update_post_meta($id, 'size_from', (string) $aTechnology->SizeFrom);
    update_post_meta($id, 'size_to', (string) $aTechnology->SizeTo);
    update_post_meta($id, 'mandator', 'AC');

    foreach ($aTechnology->Ranges as $aRange){
        $row = array(
            'number_of_colors' => (string) $aRange->NumberOfColors,
            'quantity_from' => (string) $aRange->From,
            'quantity_to' => (string) $aRange->To,
            'unit_price' => (string) $aRange->UnitPrice,
            'setup_cost' => (string) $aRange->SetupPrice
        );
        add_row('ranges', $row, $id);
    }
}

function UpdateTechnology($post_id, LTechnology $aTechnology) {
    // Increase time limit
    set_time_limit(10);

    update_post_meta($post_id, 'hash_sum', $aTechnology->HashSum);
    update_post_meta($post_id, 'title', (string) $aTechnology->Code);
    update_post_meta($post_id, 'name', sanitize_title((string) $aTechnology->Code));
    update_post_meta($post_id, 'code', (string) $aTechnology->Code);
    update_post_meta($post_id, 'name', (string) $aTechnology->Name);
    update_post_meta($post_id, 'size_from', (string) $aTechnology->SizeFrom);
    update_post_meta($post_id, 'size_to', (string) $aTechnology->SizeTo);
    update_post_meta($post_id, 'mandator', 'AC');

    $oldRanges = get_field('ranges', $post_id);
    if ($oldRanges) {
        for ($i = count($oldRanges); $i > 0; $i--) {
            delete_row('ranges', $i, $post_id);
        }
    }

    foreach ($aTechnology->Ranges as $aRange){
        $row = array(
            'number_of_colors' => (string) $aRange->NumberOfColors,
            'quantity_from' => (string) $aRange->From,
            'quantity_to' => (string) $aRange->To,
            'unit_price' => (string) $aRange->UnitPrice,
            'setup_cost' => (string) $aRange->SetupPrice
        );
        add_row('ranges', $row, $post_id);
    }
}

$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/printingprices.xml';

$xml = simplexml_load_file($file) or die("Error: Cannot create object");
$lTechnologies = array();

foreach ($xml->prices->children() as $lPrice){
    $lTechnology = new LTechnology();

    $lTechnology->Code = (string) $lPrice->TechnologyCode;
    $lTechnology->Name = (string) $lPrice->TechnologyName;

    foreach ($lPrice->ranges->children() as $Range) {
        $lRange = new LRange();

        $lRange->NumberOfColors = (string) $Range->NumberOfColours;
        $lRange->From = (string) $Range->QuantityFrom;
        $lRange->To = (string) $Range->QuantityTo;
        $lTechnology->SizeFrom = (string) $Range->SizeFrom;
        $lTechnology->SizeTo = (string) $Range->SizeTo;
        $lRange->UnitPrice = (string) $Range->UnitPrice;
        $lRange->SetupPrice = (string) $Range->SetupCost;

        array_push($lTechnology->Ranges, $lRange);
    }

    $lTechnology->HashSum = md5(json_encode($lTechnology));

    array_push($lTechnologies, $lTechnology);
}

foreach ($lTechnologies as $lTechnology){
    $newTech = true;

    $args = array(
        'post_type' => 'technology',
        'meta_key' => 'code',
        'meta_value' => $lTechnology->Code
    );

    $post_query = new WP_Query($args);

    while($post_query->have_posts() ) {
        $post_query->the_post();

        $newTech = false;
        $techPostId = get_the_ID();
        break;
    }

    if ($newTech)
        CreateNewTechnology($lTechnology);
    else {
        $lHashSum = (string) $lTechnology->HashSum;
        $currentHashSum = get_post_field('hash_sum', $techPostId);

        if ($lHashSum != $currentHashSum)
            UpdateTechnology($techPostId, $lTechnology);
    }
}

print_r($lTechnologies);
//print_r($xml->prices->children());