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
    $lMandator = new LMandator(LMandator::LSHOP, LMandator::LSHOP_SCRAMBLED, LMandator::LSHOP_TYPE);
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

    $lXlsxFile = null;
    try {
        if (!current_user_can('administrator')) {
            throw new RuntimeException('Administrator permission is required.');
        }
        if (empty($_GET['tempFile']) || empty($_GET['id'])) {
            throw new RuntimeException('The uploaded file or catalogue ID is missing.');
        }

        $lXlsxFile = $_GET['tempFile'];
        $lCatalogId = (int)$_GET['id'];
        $lMandator = new LMandator(LMandator::LSHOP, LMandator::LSHOP_SCRAMBLED, LMandator::LSHOP_TYPE);
        $lCatalog = new LCatalog('L-Shop Hauptkatalog', $lMandator, $lCatalogId);
        $lCatalogReader = new LLshopCatalogReader($lMandator);
        $lCatalogReader->LoadFromFileOrUrl($lXlsxFile, LCatalogReader::ALL);
        $lCatalogReader->ParseData($lCatalog, 'sendMsg');

        $lCatalogWriter = new LCatalogWriter($lMandator);
        $lCatalogWriter->SaveCatalog($lCatalog, true, 'sendMsg');
    } catch (Throwable $lException) {
        sendMsg(-1, 'ERROR: ' . $lException->getMessage(), 0);
    } finally {
        if ($lXlsxFile !== null && is_file($lXlsxFile)) {
            unlink($lXlsxFile);
        }
    }
}

function ShowTools($aDebug) {
    ?>
    <html>
    <head>
        <title>L-Shop Katalog-Tools</title>
    </head>
    <body>
    <?php
    // Check if logged in as admin
    if (current_user_can('administrator')) {
        ?>
        <h1>L-Shop Katalog-Tools</h1>
    <?php
        include ('tool_lshop.php');
    } else {
    ?>
        <h1>L-Shop Katalog-Tools</h1>
        <h2>Sie sind nicht als Administrator angemeldet!</h2>
        <?php
    }
    ?>
    </body>
    </html>
    <?php
}
