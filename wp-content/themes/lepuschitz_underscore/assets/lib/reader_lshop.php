<?php

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class LLshopCatalogReader extends LCatalogReader {

    private $_XlsxFileName = null;
    private $_AllReadyTriedOrLoadedFiles = [];

    /**
     * @param string $aFileName
     * @param string $aDataType
     * @return mixed
     */
    public function LoadFromFileOrUrl(string $aFileName, string $aDataType) {
        switch ($aDataType) {
            case $this::ALL:
                $this->_XlsxFileName = $aFileName;
                break;
        }
    }

    /**
     * @param LCatalog $aCatalog
     * @param null $aProgressHandler
     * @return mixed
     */
    public function ParseData(LCatalog $aCatalog, $aProgressHandler = null) {
        // Increase the memory
        ini_set('memory_limit', '2048M');

        // Set time limit to run through all entries and to avoid hanging if something takes too long
        set_time_limit(0);

        $lProductMarkUp = (100 + get_field('lshop_import_markup', 'option')) / 100;

        if (empty($this->_XlsxFileName) || !is_readable($this->_XlsxFileName)) {
            throw new RuntimeException('The L-Shop Excel file is missing or cannot be read.');
        }

        // Read the data
        $this->DoProgress($aProgressHandler, 1, 0, "Reading excel file");
        $lXlsxReader = ReaderEntityFactory::createXLSXReader();
        $lXlsxReader->open($this->_XlsxFileName);
        $this->DoProgress($aProgressHandler, 1, 1, "Reading finished");

        // Get the main sheet
        $lSpreadsheet = null;
        foreach ($lXlsxReader->getSheetIterator() as $lSheet) {
            if ($lSheet->getName() === 'Items') {
                $lSpreadsheet = $lSheet;
                break;
            }
        }
        if ($lSpreadsheet === null) {
            $lXlsxReader->close();
            throw new RuntimeException('The L-Shop workbook must contain a worksheet named "Items".');
        }

        $lHeaderNames = [];
        foreach ($lSpreadsheet->getRowIterator() as $lHeaderRow) {
            foreach ($lHeaderRow->getCells() as $lHeaderCell) {
                $lHeaderNames[] = trim((string)$lHeaderCell->getValue());
            }
            break;
        }
        if (!in_array('EK', $lHeaderNames, true)) {
            $lXlsxReader->close();
            if (in_array('ArticleNr', $lHeaderNames, true) && in_array('CatalogNr', $lHeaderNames, true)) {
                throw new RuntimeException('This is an L-Shop item-data export without prices. Please export the price file that includes the "EK" column and import that file instead.');
            }
            throw new RuntimeException('The L-Shop workbook is missing the required "EK" price column.');
        }

        // Get number of rows
        $lNumberOfRows = 0; // $lSheetData-> getHighestRow() - 1;
        $this->DoProgress($aProgressHandler, 1, 0, "Calculating rows");
        foreach ($lSpreadsheet->getRowIterator() as $XlsRow) {
            $lNumberOfRows++;
            if ($lNumberOfRows % 100 == 0) {
                $this->DoProgress($aProgressHandler, 1, 0, "Calculating rows: " . $lNumberOfRows);
            }
        }

        // Loop by skipping the first row
        $lCurrentRow = 0;
        foreach ($lSpreadsheet->getRowIterator() as $XlsRow) {
            $lCurrentRow++;

            // Skip first row
            if ($lCurrentRow == 1)
                continue;

            $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, "Reading products");

            // Get the cells
            $lCells = $XlsRow->getCells();

            // Load the image for product
            $lImageFileForProductUrl = '';
            if ($this->LoadImageFromWeb($lCells[34]->getValue(), $lCells[1]->getValue(), $lCells[1]->getValue().'.jpg')) {
                $lImageFileForProductUrl = $aCatalog->Mandator->ImageUrl . $lCells[1]->getValue().'.jpg';
            }

            // Load the image from web for color
            $lImageFileForColorUrl = '';
            if ($this->LoadImageFromWeb($lCells[34]->getValue(), $lCells[1]->getValue(), $lCells[47]->getValue())) {
                $lImageFileForColorUrl = $aCatalog->Mandator->ImageUrl . $lCells[47]->getValue();
            }

            // Parse the row now
            $lArticleNumber = $lCells[1]->getValue();
            if (!$aCatalog->Products->ExistsProductCode($lArticleNumber)) {
                // Create a new product
                $lNewProduct = $aCatalog->Products->AddProduct($lArticleNumber, $lCells[48]->getValue());
                $lNewProduct->SupplierProductNumber = trim((string)$lCells[1]->getValue());
                $lNewProduct->Description = $lCells[49]->getValue();
                $lNewProduct->ImageUrl = $lImageFileForProductUrl;

                $lColor1 = $lCells[25]->getValue();
                $lColor2 = $lCells[26]->getValue();

                if (empty($lColor2)) {
                    if (!$lNewProduct->AddColorWithImage($lColor1, $lImageFileForColorUrl, $lCells[0]->getValue())) {
                        $lNewProduct->AddColorWithImage('#' . $lCells[29]->getValue(), $lImageFileForColorUrl, $lCells[0]->getValue());
                    }
                } else {
                    if (!$lNewProduct->AddBiColorWithImage($lColor1, $lColor2, $lImageFileForColorUrl, $lCells[0]->getValue())) {
                        $lNewProduct->AddBiColorWithImage('#' . $lCells[29]->getValue(), '#' . $lCells[30]->getValue(), $lImageFileForColorUrl, $lCells[0]->getValue());
                    }
                }
                $lNewProduct->AddPrice(round($lCells[2]->getValue() * $lProductMarkUp, 2));

                // The L-Shop export's Product column (BD / zero-based index 55)
                // is the usable product category for the catalogue mapping.
                $lNewProduct->CategoryIdOrName = $lCells[55]->getValue();
                $lNewProduct->GroupIdOrName = $lCells[51]->getValue();
            } else {
                // Add additional colors if needed
                $lExistingProduct = $aCatalog->Products->GetProductByProductCode($lArticleNumber);

                $lColor1 = $lCells[25]->getValue();
                $lColor2 = $lCells[26]->getValue();

                if (empty($lColor2)) {
                    if (!$lExistingProduct->AddColorWithImage($lColor1, $lImageFileForColorUrl, $lCells[0]->getValue())) {
                        $lExistingProduct->AddColorWithImage('#' . $lCells[29]->getValue(), $lImageFileForColorUrl, $lCells[0]->getValue());
                    }
                } else {
                    if (!$lExistingProduct->AddBiColorWithImage($lColor1, $lColor2, $lImageFileForColorUrl, $lCells[0]->getValue())) {
                        $lExistingProduct->AddBiColorWithImage('#' . $lCells[29]->getValue(), '#' . $lCells[30]->getValue(), $lImageFileForColorUrl, $lCells[0]->getValue());
                    }
                }
            }
        }
    }

    private function LoadImageFromWeb($aCollectionName, $aFolder, $aFileName, $aOverWrite = false) {
        // Check if already tried to load or loaded
        if (in_array($aCollectionName.$aFolder.$aFileName, $this->_AllReadyTriedOrLoadedFiles)) {
            return true;
        }

        // Create the upload folder
        $lUploadLshopDir = $this->Mandator->ImagePath;

        // Create destination file name
        $lDestinationFileName = $lUploadLshopDir . $aFileName;

        // Check if file is already there
        if (!$aOverWrite) {
            if (file_exists($lDestinationFileName) && (filesize($lDestinationFileName) > 0)) {
                return true;
            }
        }

        // Create temp file
        $lFileName = tempnam($lUploadLshopDir, 'lshownload-zip');

        // Build the url and file link
        $lUrl = 'https://download.l-shop.team/download/picture-service/zip';
        $lFileLink = $aCollectionName . '/' . $aFolder . '/' . $aFileName;

        // Try the loading twice if once failed
        $lAttempts = 0;
        $lResult = false;
        while ($lAttempts < 2) {
            $lAttempts++;
            // Create temp file
            $lTempFile = fopen($lFileName, 'w+');

            // Init curl
            $lCurl = curl_init();
            curl_setopt_array($lCurl, array(
                CURLOPT_URL => $lUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array('image[]' => $lFileLink),
                CURLOPT_FILE => $lTempFile
            ));

            // Trigger the download
            $data = curl_exec($lCurl);
            curl_close($lCurl);
            fclose($lTempFile);

            // Check if ok
            if (!$data) {
                error_log('LSHOP: Curl-Fehler: ' . curl_error($lCurl));
            } else {
                // Extract from the zip
                $lZip = new ZipArchive();
                if ($lZip->open($lFileName) === true) {
                    for ($i = 0; $i < $lZip->numFiles; $i++) {
                        $filename = $lZip->getNameIndex($i);
                        $fileinfo = pathinfo($filename);
                        if ($fileinfo['basename'] == $aFileName) {
                            // Copy to filesystem
                            copy("zip://" . $lFileName . "#" . $filename, $lDestinationFileName);

                            // Check file size
                            if (filesize($lDestinationFileName) == 0) {
                                unlink($lDestinationFileName);
                                error_log("LSHOP: Filesize of $aFileName is 0! collection: $aCollectionName, folder: $aFolder");
                            } else {
                                // Everything went fine
                                $lResult = true;
                            }
                        }
                    }
                    $lZip->close();
                }
            }

            // Delete the temp file
            unlink($lFileName);

            // Check if successful, break out of while
            if ($lResult)
                break;

        }

        // Remember as already tried or loaded
        $this->_AllReadyTriedOrLoadedFiles[] = $aCollectionName.$aFolder.$aFileName;

        // Do resizing
        if ($lResult) {

        }

        return $lResult;
    }
}
