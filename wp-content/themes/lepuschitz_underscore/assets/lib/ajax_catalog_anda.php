<?php
require_once dirname(__DIR__, 5) . '/wp-load.php';
include_once('catalog.php');

$lTool = '';
$lDebug = '';
if (isset($_GET['debug']))
    $lDebug = '&debug=1';
if (isset($_GET['tool'])) {
    $lTool = $_GET['tool'];

    switch ($lTool) {
        case 'importCatalog':
            ImportCatalog();
            break;
        case 'showTools':
            ShowTools($lDebug);
            break;
    }
}

function ImportCatalog() {
    // make sure apache does not gzip this type, else it would get buffered
    header('Content-Type: text/event-stream');
    // recommended to prevent caching of event data.
    header('Cache-Control: no-cache');

    function sendMsg($aId, $aMessage, $aProgress) {
        $d = array(
            "message" => $aMessage,
            "progress" => $aProgress
        );
        echo "id: $aId" . PHP_EOL;
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;

        // push the data out by all force
        ob_flush();
        flush();
    }

    try {
        if (!current_user_can('administrator')) {
            throw new RuntimeException('Administrator permission is required.');
        }
        $lCatalogId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($lCatalogId <= 0) {
            throw new InvalidArgumentException('Missing or invalid catalog ID.');
        }

        $lMandator = new LMandator(LMandator::ANDACOOL, LMandator::ANDACOOL_SCRAMBLED, LMandator::ANDACOOL_TYPE);

        $lCatalog = new LCatalog('AndaCool Hauptkatalog', $lMandator, $lCatalogId);

        $lCatalogReader = new LAndaCatalogReader($lMandator);

        if (TARGET === 'LIVE') {
            $lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::PRODUCTSXMLURL, LCatalogReader::PRODUCTS);
            $lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::PRICESXMLURL, LCatalogReader::PRICES);
            $lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::PRINTSXMLURL, LCatalogReader::PRINTS);
            $lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::LABELSXMLURL, LCatalogReader::LABELS);
        } else {
            $lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::PRODUCTSXMLTESTFILE, LCatalogReader::PRODUCTS);
            $lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::PRICESXMLTESTFILE, LCatalogReader::PRICES);
            $lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::PRINTSXMLTESTFILE, LCatalogReader::PRINTS);
            $lCatalogReader->LoadFromFileOrUrl(LAndaCatalogReader::LABELSXMLTESTFILE, LCatalogReader::LABELS);
        }

        // Parse the data
        $lCatalogReader->ParseData($lCatalog, 'sendMsg');

        // Write the data
        $lCatalogWriter = new LCatalogWriter($lMandator);
        // Synchronize products without deleting their posts, preserving IDs
        // and any related sales or order history.
        $lCatalogWriter->SaveCatalog($lCatalog, false, 'sendMsg', true);
    } catch (Throwable $lException) {
        error_log('Anda catalog import failed: ' . $lException->getMessage());
        sendMsg('error', 'Import failed: ' . $lException->getMessage(), 0);
    }
}

function ShowTools($aDebug) {
    ?>
    <html>
    <head>
        <title>Anda Cool Katalog-Tools</title>
    </head>
    <body>
    <?php
    // Check if logged in as admin
    if (current_user_can('administrator')) {
        ?>
        <h1>Anda Cool Katalog-Tools</h1>
    <?php
        include ('tool_anda.php');
    } else {
    ?>
        <h1>Anda Cool Katalog-Tools</h1>
        <h2>Sie sind nicht als Administrator angemeldet!</h2>
        <?php
    }
    ?>
    </body>
    </html>
    <?php
}
