<?php

// This is for ANDA

// Include WP features
include_once('../wp-load.php');

// Include catalog classes
include_once(ABSPATH . '/wp-content/themes/lepuschitz_underscore/assets/lib/catalog.php');

$lCatalog = new LCatalog('Anda');
$lCatalogReader = new LAndaCatalogReader();

// Read the data
//$lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::$PRODUCTSXMLURL, LCatalogReader::$PRODUCTS);
//$lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::$PRICESXMLURL, LCatalogReader::$PRICES);
$lCatalogReader->LoadFromFileOrUrl('/var/virtual_www/lepuschitzPROD/tools/testdata/products.xml', LCatalogReader::$PRODUCTS);
$lCatalogReader->LoadFromFileOrUrl('/var/virtual_www/lepuschitzPROD/tools/testdata/prices.xml', LCatalogReader::$PRICES);

// Parse the data
$lCatalogReader->ParseData($lCatalog, null);

echo ('<pre>Finished Parsing</pre><br />');
// Write the data
$lCatalogWriter = new LCatalogWriter();
$lCatalogWriter->SaveCatalog($lCatalog);
echo ('<pre>Finished Saving</pre><br />');

// Delete all images
//$lDeleteImages = new LDeleteImages();
//$lDeleteImages->DeleteAllImages();


if (isset($_GET['debug'])) {
    //print_r($lCatalog);
    // echo ('Products: <br />');
    // print_r($lCatalog->Products);
    // echo ('<hr />Categories: <br />');
    // print_r($lCatalog->Categories);
}


//
//Call parsing_labeling.php after this is done
//





//$lGiveneuropeReader = new LGivingeuropeCatalogReader();
//$lGiveneuropeReader->LoadFromFileOrUrl('/var/virtual_www/lepuschitzPROD/giving_europe.xlsx');
//$lGiveneuropeReader->ParseData($lCatalog, null);

?>
<html>
<head>
    <title>Lepuschitz Katalog-Import</title>
</head>
<body>
<?php
/*
print("<ul>");
foreach ($lCatalog->Categories->Childs as $lChildL1) {
    print("<li>$lChildL1->Name");
    if (!is_array($lChildL1->Childs)) {
    } else {
        print("<ul>");
        foreach ($lChildL1->Childs as $lChildL2) {
            print("<li>$lChildL2->Name");
            if (!is_array($lChildL2->Childs)) {
            } else {
                print("<ul>");
                foreach ($lChildL2->Childs as $lChildL3) {
                    print("<li>$lChildL3->Name");
                }
                print("</ul>");
            }
        }
        print("</ul>");
    }
}
print("</ul>");
*/

?>
</body>
</html>

