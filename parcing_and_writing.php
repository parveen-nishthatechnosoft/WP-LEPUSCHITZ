<?php
require_once('/var/virtual_www/lepuschitz-promotion/wp-load.php');
require_once('/var/virtual_www/lepuschitz-promotion/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitz-promotion/tools/parsing_test.php');
require_once('/var/virtual_www/lepuschitz-promotion/tools/parsing_labelings.php');

// Include catalog classes
include_once(ABSPATH . '/wp-content/themes/lepuschitz_underscore/assets/lib/catalog.php');

$lCatalog = new LCatalog('Anda');

$lCatalogReader = new LAndaCatalogReader();

// Read the data
$lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::$PRODUCTSXMLURL, LCatalogReader::$PRODUCTS);
$lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::$PRICESXMLURL, LCatalogReader::$PRICES);
$lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::$PRINTSXMLURL, LCatalogReader::$PRINTS);
$lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::$LABELSXMLURL, LCatalogReader::$LABELS);
// Parse the data
$lCatalogReader->ParseData($lCatalog, null);

// Write the data
$lCatalogWriter = new LCatalogWriter();
$lCatalogWriter->SaveCatalog($lCatalog);

// Read Technologies
$xml = simplexml_load_string($lCatalogReader->_PrintsData) or die("Error: Cannot create object");
$lTechnologies = array();

foreach ($xml->prices->children() as $lPrice){
    $lTechnology = new LTechnology();

    $lTechnology->Code = $lPrice->TechnologyCode;
    $lTechnology->Name = $lPrice->TechnologyName;

    foreach ($lPrice->ranges->children() as $Range) {
        $lRange = new LRange();

        $lRange->NumberOfColors = $Range->NumberOfColours;
        $lRange->From = $Range->QuantityFrom;
        $lRange->To = $Range->QuantityTo;
        $lRange->UnitPrice = $Range->UnitPrice;
        $lRange->SetupPrice = $Range->SetupCost;

        array_push($lTechnology->Ranges, $lRange);
    }

    array_push($lTechnologies, $lTechnology);
}

foreach ($lTechnologies as $lTechnology){
    $newTechnology = true;

    $args = array(
        'post_type' => 'technology',
        'meta_key' => 'code',
        'meta_value' => (string) $lTechnology->Code
    );

    $post_query = new WP_Query($args);

    while($post_query->have_posts()) {
        $post_query->the_post();

        $newTechnology = false;
        $technologyPostID = get_the_ID();
        break;
    }

    if ($newTechnology)
        CreateNewTechnology($lTechnology);
    else
        UpdateTechnology($technologyPostID, $lTechnology);
}


// Labelings

$xml = simplexml_load_file($lCatalogReader->_LabelsData) or die("Error: Cannot create object");

$lLabelings = array();

foreach ($xml->children() as $Labeling) {
    $lLabeling = new LLabeling();

    // Extract the item number
    $lMatchParts = array();
    if (!preg_match('/([a-zA-Z]+[\d]+)/', $Labeling->itemNumber, $lMatchParts) > 0) {
        // Problem with the product code! Maybe log this and do error handling!
        continue;
    };

    // Get the productcode
    $lProductCode = $lMatchParts[1];

    $lLabeling->ItemNumber = $lProductCode;
    $lLabeling->PrintTemplate = $Labeling->printTemplate;

    foreach ($Labeling->positions->children() as $Position) {
        $lPosition = new LPosition();

        $lPosition->Serial = $Position->serial;
        $lPosition->PositionName = $Position->posName;
        $lPosition->PositionImage = $Position->posImage;

        foreach ($Position->technologies->children() as $Technology) {
            $lTechnology = new LTechnology();

            $lTechnology->Code = $Technology->Code;
            $lTechnology->Name = $Technology->Name;
            $lTechnology->MaxColors = $Technology->maxColor;

            array_push($lPosition->Technologies, $lTechnology);
        }

        array_push($lLabeling->Positions, $lPosition);
    }

    array_push($lLabelings, $lLabeling);
}

echo "<div class=\"total\">" . count($lLabelings) . "</div>";
echo "<div class=\"finished\">";

foreach ($lLabelings as $Labeling) {
    $itemNumber = $Labeling->ItemNumber;

    AddPositions($Labeling, $itemNumber);
    echo 1;
}

echo "</div>";
