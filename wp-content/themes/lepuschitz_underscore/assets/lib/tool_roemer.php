<?php
global $lCatalogId;

$lDebug = '';
if (isset($_GET['debug']))
    $lDebug = '&debug=1';
?>
<section class="catalog-import-card" aria-labelledby="catalog-import-title">
    <div class="catalog-import-card__intro">
        <h2 id="catalog-import-title">Römer-Katalog importieren</h2>
        <p>Für den vollständigen Katalogimport werden vier zusammengehörige XLSX-Dateien benötigt.</p>
    </div>
    <div class="catalog-import-fields">
        <div class="catalog-import-field">
            <label for="roemer-file_articles">1. Artikel und Produkte</label>
            <input type="file" id="roemer-file_articles" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>
        <div class="catalog-import-field">
            <label for="roemer-file_positions">2. Druckpositionen</label>
            <input type="file" id="roemer-file_positions" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>
        <div class="catalog-import-field">
            <label for="roemer-file_colors">3. Druckpreise und Farben</label>
            <input type="file" id="roemer-file_colors" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>
        <div class="catalog-import-field">
            <label for="roemer-file_costs">4. Vorkosten</label>
            <input type="file" id="roemer-file_costs" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>
        <p class="catalog-import-help">Die Datei „print_detail“ ist keine Artikeldatei. Verwenden Sie im ersten Feld den Römer-Produktkatalog mit Artikelnummern, Kategorien und Preisen.</p>
    </div>
    <div class="catalog-import-actions">
        <button type="button" class="catalog-import-button" onclick="UploadFile()">Katalog importieren</button>
        <div class="catalog-import-progress">
            <progress id="progress1" value="0" max="1"></progress>
            <div id="status1" class="catalog-import-status" aria-live="polite">Bereit für den Import.</div>
        </div>
    </div>
</section>

<script>
    var source = "";

    function UploadFile() {
        // Check if file is selected
        var lFileFieldArticles = document.getElementById('roemer-file_articles');
        if (lFileFieldArticles.files.length !== 1) {
            document.getElementById("status1").textContent = "Bitte wählen Sie die Artikel-/Produktdatei aus.";
            return;
        }
        var lFileFieldPositions = document.getElementById('roemer-file_positions');
        if (lFileFieldPositions.files.length !== 1) {
            document.getElementById("status1").textContent = "Bitte wählen Sie die Datei mit den Druckpositionen aus.";
            return;
        }
        var lFileFieldColors = document.getElementById('roemer-file_colors');
        if (lFileFieldColors.files.length !== 1) {
            document.getElementById("status1").textContent = "Bitte wählen Sie die Datei mit Druckpreisen und Farben aus.";
            return;
        }
        var lFileFieldCosts = document.getElementById('roemer-file_costs');
        if (lFileFieldCosts.files.length !== 1) {
            document.getElementById("status1").textContent = "Bitte wählen Sie die Datei mit den Vorkosten aus.";
            return;
        }

        // Get the file data
        var lFormData = new FormData();
        lFormData.append('xlsx_articles', lFileFieldArticles.files[0]);
        lFormData.append('xlsx_positions', lFileFieldPositions.files[0]);
        lFormData.append('xlsx_colors', lFileFieldColors.files[0]);
        lFormData.append('xlsx_costs', lFileFieldCosts.files[0]);

        // define the EventSource object and the
        // push notifications sending server side script
        var lAjaxUrl = "/wp-content/themes/lepuschitz_underscore/assets/lib/ajax_catalog_roemer.php?tool=uploadFile<?php echo($lDebug); ?>&id=<?php echo($lCatalogId); ?>";
        source = new XMLHttpRequest();

        // reset the progress bar to zero
        document.getElementById("progress1").value = 0;

        // a message is received
        source.addEventListener("progress", function (e) {
            var percent;
            if (e.lengthComputable === true) {
                // set progress bar and status message according to received value
                document.getElementById("progress1").value = Math.round((e.loaded / e.total) * 100);
            }
        });

        // upload finished, trigger importing with the returned temp file
        source.addEventListener("load", function (e) {
            try {
                var lResponse = JSON.parse(e.currentTarget.response);
                if (e.currentTarget.status < 200 || e.currentTarget.status >= 300 || !lResponse.Success) {
                    throw new Error(lResponse.Message || "Upload failed.");
                }
                ImportCatalog(lResponse.FileNameArticles, lResponse.FileNamePositions, lResponse.FileNameColors, lResponse.FileNameCosts);
            } catch (lError) {
                document.getElementById("status1").textContent = "ERROR: " + lError.message;
            }
        });

        // an error is received
        source.addEventListener("error", function (e) {
            document.getElementById("status1").innerHTML = "Oops: There was an error!";
            source.close();
        }, false);

        source.open("POST", lAjaxUrl);
        source.send(lFormData);
        document.getElementById("status1").innerHTML = 'Uploading file';
    }

    function ImportCatalog(aTempFileNameArticles, aTempFileNamePositions, aTempFileNameColors, aTempFileNameCosts) {
        // define the EventSource object and the
        // push notifications sending server side script
        var lAjaxUrl = "/wp-content/themes/lepuschitz_underscore/assets/lib/ajax_catalog_roemer.php?tool=importCatalog<?php echo($lDebug); ?>&id=<?php echo($lCatalogId); ?>" +
            "&tempFileArticles=" + aTempFileNameArticles +
            "&tempFilePositions=" + aTempFileNamePositions +
            "&tempFileColors=" + aTempFileNameColors +
            "&tempFileCosts=" + aTempFileNameCosts;
        source = new EventSource(lAjaxUrl);

        // reset the progress bar to zero
        document.getElementById("progress1").value = 0;

        // a message is received
        source.addEventListener("message", function (e) {
            // data is JSON encoded on the server
            var result = JSON.parse(e.data);

            // set progress bar and status message according to received value
            document.getElementById("progress1").value = result.progress / 100;
            document.getElementById("status1").innerHTML = result.message;

            if (result.message.indexOf("ERROR: ") === 0) {
                source.close();
                document.getElementById("status1").textContent = result.message;
                return;
            }

            if (result.message === "TERMINATE") {
                source.close();
                document.getElementById("progress1").value = 1;
            }
        }, false);

        // an error is received
        source.addEventListener("error", function (e) {
            document.getElementById("status1").innerHTML = "Oops: There was an error!";
            source.close();
        }, false);
    }
</script>

<style>
    /* Reset the default appearance */
    progress {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }

    /* webkit-browser: z.B. Chrome, Opera (>12) */
    progress::-webkit-progress-bar {
        background: #EFEFEF;
        border-radius: 5px;
    }

    progress::-webkit-progress-value {
        background-color: #C5A000;
        border-radius: 5px;
    }

    /* Firefox */
    progress::-moz-progress-bar {
        background-color: #C5A000;
    }

    /* all browsers */
    progress {
        background: #EFEFEF;
        border-radius: 5px;
        border: 1px solid #C0C0C0;
        width: 300px;
        height: 10px;
        color: #C5A000 /* only IE10+ needs this */;
        margin-top: 10px;
    }
</style>
