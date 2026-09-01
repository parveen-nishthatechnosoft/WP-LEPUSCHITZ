<?php
global $lCatalogId;

$lDebug = isset($_GET['debug']) ? '&debug=1' : '';
?>
<section class="catalog-import-card" aria-labelledby="catalog-import-title">
    <div class="catalog-import-card__intro">
        <h2 id="catalog-import-title">L-Shop-Katalog importieren</h2>
        <p>Wählen Sie die aktuelle L-Shop-Produktdatei im XLSX-Format aus.</p>
    </div>
    <div class="catalog-import-fields">
        <div class="catalog-import-field">
            <label for="lshop-file">Produktdatei</label>
            <input type="file" id="lshop-file" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
        </div>
        <p class="catalog-import-help">Die Datei muss das Arbeitsblatt „Items“ sowie die Preis-Spalte „EK“ enthalten. Der Import ersetzt nur Produkte und Technologien dieses Katalogs.</p>
    </div>
    <div class="catalog-import-actions">
        <button type="button" id="lshop-import-button" class="catalog-import-button" onclick="UploadFile()">Katalog importieren</button>
        <div class="catalog-import-progress">
            <progress id="progress1" value="0" max="1"></progress>
            <div id="status1" class="catalog-import-status" aria-live="polite">Bereit für den Import.</div>
        </div>
    </div>
</section>

<script>
    var source = "";

    function SetImportState(aIsImporting, aMessage) {
        document.getElementById("lshop-import-button").disabled = aIsImporting;
        document.getElementById("status1").textContent = aMessage;
    }

    function UploadFile() {
        var lFileField = document.getElementById('lshop-file');
        if (lFileField.files.length !== 1) {
            SetImportState(false, "Bitte wählen Sie eine XLSX-Datei aus.");
            return;
        }

        var lFormData = new FormData();
        lFormData.append('xlsx', lFileField.files[0]);
        var lAjaxUrl = "/wp-content/themes/lepuschitz_underscore/assets/lib/ajax_catalog_lshop.php?tool=uploadFile<?php echo($lDebug); ?>&id=<?php echo($lCatalogId); ?>";
        source = new XMLHttpRequest();
        document.getElementById("progress1").value = 0;
        SetImportState(true, "Datei wird hochgeladen…");

        source.addEventListener("progress", function (e) {
            if (e.lengthComputable) {
                document.getElementById("progress1").value = e.loaded / e.total;
            }
        });

        source.addEventListener("load", function (e) {
            try {
                var lResponse = JSON.parse(e.currentTarget.response);
                if (e.currentTarget.status < 200 || e.currentTarget.status >= 300 || !lResponse.Success) {
                    throw new Error(lResponse.Message || "Der Upload ist fehlgeschlagen.");
                }
                ImportCatalog(lResponse.FileName);
            } catch (lError) {
                SetImportState(false, "ERROR: " + lError.message);
            }
        });

        source.addEventListener("error", function () {
            SetImportState(false, "Der Upload konnte nicht abgeschlossen werden.");
        }, false);

        source.open("POST", lAjaxUrl);
        source.send(lFormData);
    }

    function ImportCatalog(aTempFileName) {
        var lAjaxUrl = "/wp-content/themes/lepuschitz_underscore/assets/lib/ajax_catalog_lshop.php?tool=importCatalog<?php echo($lDebug); ?>&id=<?php echo($lCatalogId); ?>&tempFile=" + aTempFileName;
        source = new EventSource(lAjaxUrl);
        document.getElementById("progress1").value = 0;
        SetImportState(true, "Katalog wird importiert…");

        source.addEventListener("message", function (e) {
            var result = JSON.parse(e.data);
            document.getElementById("progress1").value = result.progress / 100;
            document.getElementById("status1").textContent = result.message;

            if (result.message.indexOf("ERROR:") === 0) {
                source.close();
                document.getElementById("lshop-import-button").disabled = false;
                return;
            }

            if (result.message === "TERMINATE") {
                source.close();
                document.getElementById("progress1").value = 1;
                SetImportState(false, "Katalog erfolgreich importiert.");
            }
        }, false);

        source.addEventListener("error", function () {
            SetImportState(false, "Der Import konnte nicht abgeschlossen werden.");
            source.close();
        }, false);
    }
</script>
