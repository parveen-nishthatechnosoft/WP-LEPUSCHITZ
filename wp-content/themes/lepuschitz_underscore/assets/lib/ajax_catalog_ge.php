<?php
require_once dirname(__DIR__, 5) . '/wp-load.php';
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
    header('Content-Type: application/json');
    if (!current_user_can('administrator')) {
        http_response_code(403);
        echo json_encode(['Success' => false, 'Message' => 'Administrator permission is required.']);
        return;
    }
    if (!isset($_FILES['xlsx']) || $_FILES['xlsx']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['Success' => false, 'Message' => 'The XLSX upload did not complete successfully.']);
        return;
    }
    $lXlsxFile = $_FILES['xlsx']['tmp_name'];

    $lTempFileName = tempnam(sys_get_temp_dir(), 'ge_import_');
    if ($lTempFileName !== false && !move_uploaded_file($lXlsxFile, $lTempFileName)) {
        unlink($lTempFileName);
        $lTempFileName = false;
    }
    if ($lTempFileName === false || !is_file($lTempFileName) || filesize($lTempFileName) === 0) {
        http_response_code(500);
        echo json_encode(['Success' => false, 'Message' => 'The server could not store the uploaded workbook.']);
        return;
    }

    $lResult = new stdClass();
    $lResult->Success = true;
    $lResult->FileName = basename($lTempFileName);

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
        $lTempFileToken = basename(rawurldecode((string) $_GET['tempFile']));
        if (preg_match('/^ge_import_[A-Za-z0-9]+$/', $lTempFileToken) !== 1) {
            throw new RuntimeException('The temporary import-file token is invalid. Please upload it again.');
        }
        $lTemporaryDirectory = realpath(sys_get_temp_dir());
        $lXlsxFile = realpath(sys_get_temp_dir() . DIRECTORY_SEPARATOR . $lTempFileToken);
        if ($lXlsxFile === false || $lTemporaryDirectory === false || realpath(dirname($lXlsxFile)) !== $lTemporaryDirectory) {
            throw new RuntimeException('The temporary import file is invalid. Please upload it again.');
        }

        $lMandator = new LMandator(LMandator::GE, LMandator::GE_SCRAMBLED, LMandator::GE_TYPE);
        $lCatalog = new LCatalog('Giving Europe Hauptkatalog', $lMandator, (int) $_GET['id']);
        $lCatalogReader = new LGeCatalogReader($lMandator);
        $lCatalogReader->LoadFromFileOrUrl($lXlsxFile, LCatalogReader::ALL);
        $lCatalogReader->ParseData($lCatalog, 'sendMsg');
        $lCatalogWriter = new LCatalogWriter($lMandator);
        $lCatalogWriter->SaveCatalog($lCatalog, true, 'sendMsg');
    } catch (Throwable $lException) {
        error_log('Giving Europe catalog import failed: ' . $lException->getMessage());
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
