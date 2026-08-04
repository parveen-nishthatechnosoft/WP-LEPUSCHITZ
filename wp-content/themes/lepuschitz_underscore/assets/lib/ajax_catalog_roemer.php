<?php
include_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
include_once('catalog.php');

$lTool = '';
$lDebug = '';
if (isset($_GET['debug']))
    $lDebug = '&debug=1';
if (isset($_GET['tool'])) {
    $lTool = $_GET['tool'];

    switch ($lTool) {
        case 'uploadFile':
            UploadFile();
            break;
        case 'importCatalog':
            ImportCatalog();
            break;
        case 'showTools':
            ShowTools($lDebug);
            break;
    }
}

function UploadFile() {
    // Check if file is given
    $lXlsxFileArticles = $_FILES['xlsx_articles']['tmp_name'];
    $lXlsxFilePositions = $_FILES['xlsx_positions']['tmp_name'];
    $lXlsxFileColors = $_FILES['xlsx_colors']['tmp_name'];
    $lXlsxFileCosts = $_FILES['xlsx_costs']['tmp_name'];

    // Save the file to temp folder
    $lMandator = new LMandator(LMandator::ROEMER, LMandator::ROEMER_SCRAMBLED, LMandator::ROEMER_TYPE);
    $lTempFileNameArticles = $lMandator->SaveFileToTempFile($lXlsxFileArticles);
    $lTempFileNamePositions = $lMandator->SaveFileToTempFile($lXlsxFilePositions);
    $lTempFileNameColors = $lMandator->SaveFileToTempFile($lXlsxFileColors);
    $lTempFileNameCosts = $lMandator->SaveFileToTempFile($lXlsxFileCosts);

    $lResult = new stdClass();
    $lResult->Success = true;
    $lResult->FileNameArticles = urlencode($lTempFileNameArticles);
    $lResult->FileNamePositions = urlencode($lTempFileNamePositions);
    $lResult->FileNameColors = urlencode($lTempFileNameColors);
    $lResult->FileNameCosts = urlencode($lTempFileNameCosts);

    echo(json_encode($lResult));
}

function ImportCatalog() {
    // make sure apache does not gzip this type, else it would get buffered
    header('Content-Type: text/event-stream');
    // recommended to prevent caching of event data.
    header('Cache-Control: no-cache');

    function sendMsg($id, $message, $progress) {
        $d = array(
            "message" => $message,
            "progress" => $progress
        );
        echo "id: $id" . PHP_EOL;
        echo "data: " . json_encode($d) . PHP_EOL;
        echo PHP_EOL;

        // push the data out by all force
        ob_flush();
        flush();
    }

    // Check if file is given
    $lXlsxFileArticles = $_GET['tempFileArticles'];
    $lXlsxFilePositions = $_GET['tempFilePositions'];
    $lXlsxFileColors = $_GET['tempFileColors'];
    $lXlsxFileCosts = $_GET['tempFileCosts'];

    $lCatalogId = $_GET['id'];
    $lMandator = new LMandator(LMandator::ROEMER, LMandator::ROEMER_SCRAMBLED, LMandator::ROEMER_TYPE);

    $lCatalog = new LCatalog('Römer Hauptkatalog', $lMandator, $lCatalogId);

    $lCatalogReader = new LRoemerCatalogReader($lMandator);

    // Read the data
    $lCatalogReader->LoadFromFileOrUrl($lXlsxFileArticles, LCatalogReader::PRODUCTS);
    $lCatalogReader->LoadFromFileOrUrl($lXlsxFilePositions, LCatalogReader::LABELS);
    $lCatalogReader->LoadFromFileOrUrl($lXlsxFileColors, LCatalogReader::PRINTS);
    $lCatalogReader->LoadFromFileOrUrl($lXlsxFileCosts, LCatalogReader::PRICES);

    // Parse the data
    $lCatalogReader->ParseData($lCatalog, 'sendMsg');

    // Remove temp file
    unlink($lXlsxFileArticles);
    unlink($lXlsxFilePositions);
    unlink($lXlsxFileColors);
    unlink($lXlsxFileCosts);

    // Write the data
    $lCatalogWriter = new LCatalogWriter($lMandator);
    $lCatalogWriter->SaveCatalog($lCatalog, true, 'sendMsg');
}

function ShowTools($aDebug) {
    ?>
    <html>
    <head>
        <title>Römer Katalog-Tools</title>
    </head>
    <body>
    <?php
    // Check if logged in as admin
    if (current_user_can('administrator')) {
        ?>
        <h1>Römer Katalog-Tools</h1>
        <?php
        include('tool_roemer.php');
    } else {
        ?>
        <h1>Römer Katalog-Tools</h1>
        <h2>Sie sind nicht als Administrator angemeldet!</h2>
        <?php
    }
    ?>
    </body>
    </html>
    <?php
}
