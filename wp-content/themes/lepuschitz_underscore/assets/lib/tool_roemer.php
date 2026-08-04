<?php
global $lCatalogId;

$lDebug = '';
if (isset($_GET['debug']))
    $lDebug = '&debug=1';
?>
<ul>
    <li>
        <a href="javascript:UploadFile()">Import catalog</a><br>
        Artikel: <input type="file" id="roemer-file_articles"><br>
        Positionen: <input type="file" id="roemer-file_positions"><br>
        Farben: <input type="file" id="roemer-file_colors"><br>
        Kosten: <input type="file" id="roemer-file_costs"><br>
        <div>
            <progress id="progress1" value="0"></progress>
            <div id="status1" style="min-height:20px;"></div>
        </div>
    </li>
</ul>

<script>
    var source = "";

    function UploadFile() {
        // Check if file is selected
        var lFileFieldArticles = document.getElementById('roemer-file_articles');
        if (lFileFieldArticles.files.length !== 1) {
            console.log("No articles file selected")
            return;
        }
        var lFileFieldPositions = document.getElementById('roemer-file_positions');
        if (lFileFieldPositions.files.length !== 1) {
            console.log("No positions file selected")
            return;
        }
        var lFileFieldColors = document.getElementById('roemer-file_colors');
        if (lFileFieldColors.files.length !== 1) {
            console.log("No colors file selected")
            return;
        }
        var lFileFieldCosts = document.getElementById('roemer-file_costs');
        if (lFileFieldCosts.files.length !== 1) {
            console.log("No costs file selected")
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
            lResponse = JSON.parse(e.currentTarget.response);
            ImportCatalog(lResponse.FileNameArticles, lResponse.FileNamePositions, lResponse.FileNameColors, lResponse.FileNameCosts);
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
