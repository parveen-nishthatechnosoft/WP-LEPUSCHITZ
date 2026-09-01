<?php
global $lCatalogId;

$lDebug = isset($_GET['debug']) ? '&debug=1' : '';
?>
<section class="catalog-import-card" aria-labelledby="catalog-import-title">
    <div class="catalog-import-card__intro">
        <h2 id="catalog-import-title">Anda-Cool-Katalog importieren</h2>
        <p>Produkt-, Preis- und Veredelungsdaten werden direkt aus dem Anda-Cool-Datenfeed aktualisiert.</p>
    </div>
    <div class="catalog-import-fields">
        <p class="catalog-import-help">Für diesen Katalog ist kein Dateiupload erforderlich. Starten Sie die Aktualisierung, um die aktuellen Lieferantendaten zu übernehmen.</p>
    </div>
    <div class="catalog-import-actions">
        <button type="button" id="anda-import-button" class="catalog-import-button" onclick="ImportCatalog()">Katalog aktualisieren</button>
        <div class="catalog-import-progress">
            <progress id="progress1" value="0" max="1"></progress>
            <div id="status1" class="catalog-import-status" aria-live="polite">Bereit für die Aktualisierung.</div>
        </div>
    </div>
</section>

<script>
    var source = "";

    function ImportCatalog() {
        var lButton = document.getElementById("anda-import-button");
        var lStatus = document.getElementById("status1");
        lButton.disabled = true;
        lStatus.textContent = "Aktualisierung wird gestartet…";
        document.getElementById("progress1").value = 0;

        var lAjaxUrl = "/wp-content/themes/lepuschitz_underscore/assets/lib/ajax_catalog_anda.php?tool=importCatalog<?php echo($lDebug); ?>&id=<?php echo($lCatalogId); ?>";
        source = new EventSource(lAjaxUrl);

        source.addEventListener("message", function (e) {
            var result = JSON.parse(e.data);
            document.getElementById("progress1").value = result.progress / 100;
            lStatus.textContent = result.message;

            if (result.message.indexOf("Import failed:") === 0 || result.message.indexOf("ERROR:") === 0) {
                source.close();
                lButton.disabled = false;
                return;
            }

            if (result.message === "TERMINATE") {
                source.close();
                document.getElementById("progress1").value = 1;
                lStatus.textContent = "Katalog erfolgreich aktualisiert.";
                lButton.disabled = false;
            }
        }, false);

        source.addEventListener("error", function () {
            lStatus.textContent = "Die Aktualisierung konnte nicht abgeschlossen werden.";
            source.close();
            lButton.disabled = false;
        }, false);
    }
</script>
