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
    header('Content-Type: application/json');
    if (!current_user_can('administrator')) {
        http_response_code(403);
        echo json_encode(['Success' => false, 'Message' => 'Administrator permission is required.']);
        return;
    }

    $lFields = ['xlsx_articles', 'xlsx_positions', 'xlsx_colors', 'xlsx_costs'];
    $lFiles = [];
    foreach ($lFields as $lField) {
        if (!isset($_FILES[$lField]) || $_FILES[$lField]['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['Success' => false, 'Message' => 'All four Römer XLSX files are required.']);
            return;
        }
        $lTempFile = tempnam(sys_get_temp_dir(), 'roemer_import_');
        if ($lTempFile === false || !move_uploaded_file($_FILES[$lField]['tmp_name'], $lTempFile) || filesize($lTempFile) === 0) {
            if (is_string($lTempFile) && is_file($lTempFile)) {
                unlink($lTempFile);
            }
            foreach ($lFiles as $lSavedFile) {
                unlink($lSavedFile);
            }
            http_response_code(500);
            echo json_encode(['Success' => false, 'Message' => 'The server could not store the uploaded Römer workbooks.']);
            return;
        }
        $lFiles[$lField] = $lTempFile;
    }

    $lResult = new stdClass();
    $lResult->Success = true;
    $lResult->FileNameArticles = basename($lFiles['xlsx_articles']);
    $lResult->FileNamePositions = basename($lFiles['xlsx_positions']);
    $lResult->FileNameColors = basename($lFiles['xlsx_colors']);
    $lResult->FileNameCosts = basename($lFiles['xlsx_costs']);

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

    $lTemporaryFiles = [];
    try {
        if (!current_user_can('administrator')) {
            throw new RuntimeException('Administrator permission is required.');
        }
        if (empty($_GET['id'])) {
            throw new RuntimeException('The Römer catalogue ID is missing.');
        }

        $lParameters = ['tempFileArticles', 'tempFilePositions', 'tempFileColors', 'tempFileCosts'];
        $lTemporaryDirectory = realpath(sys_get_temp_dir());
        foreach ($lParameters as $lParameter) {
            $lToken = basename(rawurldecode((string)($_GET[$lParameter] ?? '')));
            if (preg_match('/^roemer_import_[A-Za-z0-9]+$/', $lToken) !== 1) {
                throw new RuntimeException('A Römer temporary-file token is invalid. Please upload all files again.');
            }
            $lFile = realpath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $lToken);
            if ($lFile === false || realpath(dirname($lFile)) !== $lTemporaryDirectory || basename($lFile) !== $lToken) {
                throw new RuntimeException('A Römer temporary import file is missing. Please upload all files again.');
            }
            $lTemporaryFiles[$lParameter] = $lFile;
        }

        $lMandator = new LMandator(LMandator::ROEMER, LMandator::ROEMER_SCRAMBLED, LMandator::ROEMER_TYPE);
        $lCatalog = new LCatalog('Römer Hauptkatalog', $lMandator, (int)$_GET['id']);
        $lCatalogReader = new LRoemerCatalogReader($lMandator);
        $lCatalogReader->LoadFromFileOrUrl($lTemporaryFiles['tempFileArticles'], LCatalogReader::PRODUCTS);
        $lCatalogReader->LoadFromFileOrUrl($lTemporaryFiles['tempFilePositions'], LCatalogReader::LABELS);
        $lCatalogReader->LoadFromFileOrUrl($lTemporaryFiles['tempFileColors'], LCatalogReader::PRINTS);
        $lCatalogReader->LoadFromFileOrUrl($lTemporaryFiles['tempFileCosts'], LCatalogReader::PRICES);
        $lCatalogReader->ParseData($lCatalog, 'sendMsg');

        if (count($lCatalog->Products->Products) === 0) {
            throw new RuntimeException('No importable products were found. Confirm that the Artikel file is the Römer product export.');
        }

        $lCatalogWriter = new LCatalogWriter($lMandator);
        // Synchronize instead of replacing the catalogue. Existing product IDs
        // must remain intact for sales and order history.
        $lCatalogWriter->SaveCatalog($lCatalog, false, 'sendMsg', true);
    } catch (Throwable $lException) {
        sendMsg(-1, 'ERROR: ' . $lException->getMessage(), 0);
    } finally {
        foreach ($lTemporaryFiles as $lTemporaryFile) {
            if (is_file($lTemporaryFile)) {
                unlink($lTemporaryFile);
            }
        }
    }
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
