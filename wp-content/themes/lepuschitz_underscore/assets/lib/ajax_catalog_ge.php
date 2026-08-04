<?php
include_once ($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
include_once ('catalog.php');

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
    $lXlsxFile = $_FILES['xlsx']['tmp_name'];

    // Save the file to temp folder
    $lMandator = new LMandator(LMandator::GE, LMandator::GE_SCRAMBLED, LMandator::GE_TYPE);
    $lTempFileName = $lMandator->SaveFileToTempFile($lXlsxFile);

    $lResult = new stdClass();
    $lResult->Success = true;
    $lResult->FileName = urlencode($lTempFileName);

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
    $lXlsxFile = $_GET['tempFile'];

    $lCatalogId = $_GET['id'];
    $lMandator = new LMandator(LMandator::GE, LMandator::GE_SCRAMBLED, LMandator::GE_TYPE);

    $lCatalog = new LCatalog('Giving Europe Hauptkatalog', $lMandator, $lCatalogId);

    $lCatalogReader = new LGeCatalogReader($lMandator);

    // Read the data
    $lCatalogReader->LoadFromFileOrUrl($lXlsxFile, LCatalogReader::ALL);

    // Parse the data
    $lCatalogReader->ParseData($lCatalog, 'sendMsg');

    // Remove temp file
    unlink($lXlsxFile);

    // Write the data
    $lCatalogWriter = new LCatalogWriter($lMandator);
    $lCatalogWriter->SaveCatalog($lCatalog, true, 'sendMsg');
}

function ShowTools($aDebug) {
    ?>
    <html>
    <head>
        <title>Giving Europe Katalog-Tools</title>
    </head>
    <body>
    <?php
    // Check if logged in as admin
    if (current_user_can('administrator')) {
        ?>
        <h1>Giving Europe Katalog-Tools</h1>
    <?php
        include ('tool_ge.php');
    } else {
    ?>
        <h1>Giving Europe Katalog-Tools</h1>
        <h2>Sie sind nicht als Administrator angemeldet!</h2>
        <?php
    }
    ?>
    </body>
    </html>
    <?php
}
