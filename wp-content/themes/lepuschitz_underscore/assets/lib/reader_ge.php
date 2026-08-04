<?php

use PhpOffice\PhpSpreadsheet;

class LGeCatalogReader extends LCatalogReader {

    private $_XlsxFileName = null;

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

        $lProductMarkUp = (100 + get_field('ge_import_markup', 'option')) / 100;
        $lTechnologyMarkup = $lProductMarkUp;

        // Set time limit to run through all entries and to avoid hanging if something takes too long
        set_time_limit(0);

        // Open the spreadsheet
        $lXlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        // Can the file be read
        if ($lXlsxReader->canRead($this->_XlsxFileName)) {
            // Load the file
            $this->DoProgress($aProgressHandler, 1, 0, "Reading excel file. Can take a view minutes!");
            $lSpreadsheet = $lXlsxReader->load($this->_XlsxFileName);
            $this->DoProgress($aProgressHandler, 1, 1, "Reading finished");

            // Artikelpreisliste
            $lSheetArticles = $lSpreadsheet->getSheetByName('Artikelpreisliste');
            $lNumberOfRows = $lSheetArticles->getHighestRow();

            $lCurrentRow = 0;
            foreach ($lSheetArticles->getRowIterator() as $lRow) {
                $lCurrentRow++;

                if (isset($_GET['debug'])) {
                    if ($lCurrentRow == 100)
                        break;
                }

                // Skip first row
                if ($lCurrentRow == 1) {
                    continue;
                }

                $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, "Reading products");

                $lCells = array();
                foreach ($lRow->getCellIterator() as $lCell) {
                    array_push($lCells, $lCell->getValue());
                }

                // Check if there is a price
                if (!is_numeric($lCells[9])) {
                    continue;
                }

                // Create the product
                $lArticleNumber = $lCells[1];
                $lArticleName = $lCells[8];

                // Load the image for the product
                $lImageUrl = $this->LoadImageFromWeb($lCells);

                if (!$aCatalog->Products->ExistsProductCode($lArticleNumber)) {
                    // Create a new product
                    $lNewProduct = $aCatalog->Products->AddProduct($lArticleNumber, $lArticleName);
                    $lNewProduct->CategoryIdOrName = $lCells[56];
                    $lNewProduct->Description = $lCells[7];
                    $lNewProduct->ImageUrl = $lImageUrl;
                    $lNewProduct->AddColorWithImage($lCells[3], $lImageUrl);
                    $lNewProduct->AddPrice(round($lCells[10] * $lProductMarkUp, 2), 1, $lCells[9] - 1);
                    $lNewProduct->AddPrice(round($lCells[13] * $lProductMarkUp, 2), $lCells[9], $lCells[12] - 1);
                    $lNewProduct->AddPrice(round($lCells[16] * $lProductMarkUp, 2), $lCells[12], $lCells[15] - 1);
                    $lNewProduct->AddPrice(round($lCells[19] * $lProductMarkUp, 2), $lCells[15], $lCells[18] - 1);
                } else {
                    // Add additional colors
                    $lExistingProduct = $aCatalog->Products->GetProductByProductCode($lArticleNumber);
                    $lExistingProduct->AddColorWithImage($lCells[3], $lImageUrl);
                }
            }

            // Daten 1
            $lSheetData1 = $lSpreadsheet->getSheetByName('Daten 1');
            $lNumberOfRows = $lSheetData1->getHighestRow();

            $lCurrentRow = 0;
            $lTechnologyMaxColors = [];
            foreach ($lSheetData1->getRowIterator() as $lRow) {
                $lCurrentRow++;

                // Skip first row
                if ($lCurrentRow == 1)
                    continue;

                $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, "Reading labelings");

                $lCells = array();
                foreach ($lRow->getCellIterator() as $lCell) {
                    array_push($lCells, $lCell->getValue());
                }

                // Get the product number and add leading 0 if needed
                $lArticleNumber = $lCells[0];

                // Get the product
                if (!$aCatalog->Products->ExistsProductCode($lArticleNumber)) {
                    continue;
                }

                $lNewLabel = $aCatalog->Labelings->AddLabel($lArticleNumber);
                $lNewPosition = $lNewLabel->AddPosition($lCells[2]);
                $lNewPosition->Serial = LCatalogReader::NumberToRomanRepresentation(sizeof($lNewLabel->Positions));
                $lNewTechnology = $lNewPosition->AddTechnology($lCells[7]);
                $lNewTechnology->MaxColors = $lCells[9] < 98 ? $lCells[9] : LRanges::FULLCOLOR;

                $lTechnologyMaxColors[$lCells[7]] = $lCells[9];
            }

            // Daten 5
            $lSheetData5 = $lSpreadsheet->getSheetByName('Daten 5');
            $lNumberOfRows = $lSheetData5->getHighestRow();

            $lCurrentRow = 0;
            foreach ($lSheetData5->getRowIterator() as $lRow) {
                $lCurrentRow++;

                $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, "Reading technologies");

                $lCells = array();
                foreach ($lRow->getCellIterator() as $lCell) {
                    array_push($lCells, $lCell->getValue());
                }

                // In the first line, read the ranges
                if ($lCurrentRow == 1) {
                    $lRange1 = $lCells[1];
                    $lRange2 = $lCells[2];
                    $lRange3 = $lCells[3];
                    $lRange4 = $lCells[4];
                    $lRange5 = $lCells[5];
                    $lRange6 = $lCells[6];
                    $lRange7 = $lCells[7];
                    $lRange8 = $lCells[8];
                    $lRange9 = $lCells[9];
                    continue;
                }

                // Get the technology code, ...
                $lTechCode = trim($lCells[0]);

                // Is it empty or invalid or handling code (HK...)
                if (empty($lTechCode) || ($lTechCode == '-') || (substr($lTechCode, 0, 2) == 'HK')) {
                    continue;
                }

                $lTechnology = $aCatalog->Technologies->AddTechnology($lTechCode, $lTechCode);  // Techname will be replaced later

                // Check if technology-name
                $lCheckValue = $lCells[4];
                if (!empty($lCheckValue)) {
                    // Get the prices for the ranges
                    $lRange1Price = $lCells[1];
                    $lRange2Price = $lCells[2];
                    $lRange3Price = $lCells[3];
                    $lRange4Price = $lCells[4];
                    $lRange5Price = $lCells[5];
                    $lRange6Price = $lCells[6];
                    $lRange7Price = $lCells[7];
                    $lRange8Price = $lCells[8];
                    $lRange9Price = $lCells[9];

                    $lNumberOfColors = LRanges::FULLCOLOR;
                    if (isset($lTechnologyMaxColors[$lTechCode])) {
                        $lNumberOfColors = $lTechnologyMaxColors[$lTechCode];
                    }

                    if (strcmp("inkl.", $lRange1Price) == 0) {
                        $lTechnology->Ranges->AddRangeWithPrices(1, 0, 0, round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                    } else {
                        if (is_numeric($lRange1Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange1, $lRange2 - 1, round($lRange1Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                        if (is_numeric($lRange2Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange2, $lRange3 - 1, round($lRange2Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                        if (is_numeric($lRange3Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange3, $lRange4 - 1, round($lRange3Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                        if (is_numeric($lRange4Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange4, $lRange5 - 1, round($lRange4Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                        if (is_numeric($lRange5Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange5, $lRange6 - 1, round($lRange5Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                        if (is_numeric($lRange6Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange6, $lRange7 - 1, round($lRange6Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                        if (is_numeric($lRange7Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange7, $lRange8 - 1, round($lRange7Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                        if (is_numeric($lRange8Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange8, $lRange9 - 1, round($lRange8Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                        if (is_numeric($lRange9Price)) {
                            $lTechnology->Ranges->AddRangeWithPrices($lRange9, 0, round($lRange9Price * $lTechnologyMarkup, 2), round($lCells[11] * $lTechnologyMarkup, 2), $lNumberOfColors);
                        }
                    }
                } else {
                    // Tech name
                    $lTechName = $lCells[1];
                    if (!empty($lTechName) && !is_numeric($lTechName) && ($lTechnology->Code == $lCells[0])) {
                        $lTechnology->Name = $lTechName;
                    }
                }
            }
        }
    }

    /**
     * @param $lCells
     * @return false|string
     */
    private function LoadImageFromWeb($lCells) {
        // Create the filename
        $lImageFileName = str_replace('.tif', 'jpg', $this->Mandator->ImagePath . $lCells[74]);

        // Check if it exists
        if (file_exists($lImageFileName)) {
            return LMandator::GetUrlForTempImageFileName($lImageFileName);
        }

        // Check the image file
        $lImgUrlBase = substr($lCells[74], 0, -4);

        $lRangeBottom = floor($lCells[1] / 1000) * 1000;
        $lRangeTop = $lRangeBottom + 999;

        $lFillerBottom = '';
        $lFillerTop = '';
        $lFillerID = '';

        for ($i = strlen($lRangeBottom); $i < 6; $i++) {
            $lFillerBottom = $lFillerBottom . "0";
        }

        for ($i = strlen($lRangeTop); $i < 6; $i++) {
            $lFillerTop = $lFillerTop . "0";
        }

        for ($i = strlen($lCells[1]); $i < 6; $i++) {
            $lFillerID = $lFillerID . "0";
        }

        // Try to load the JPG variant
        $imgUrl = "https://cdn.impression-catalogue.com/files/Producten/" . $lFillerBottom . $lRangeBottom . "-" . $lFillerTop . $lRangeTop . "/" . $lFillerID . $lCells[1] . "/Productfotos/" . $lImgUrlBase . ".jpg//size_500x500.jpg";
        $lImageData = file_get_contents($imgUrl);
        if (!$lImageData) {
            $imgUrl = "https://cdn.impression-catalogue.com/files/Producten/" . $lFillerBottom . $lRangeBottom . "-" . $lFillerTop . $lRangeTop . "/" . $lFillerID . $lCells[1] . "/Productfotos/" . $lImgUrlBase . ".tif//size_500x500.jpg";
            $lImageData = file_get_contents($imgUrl);
        }

        // Save the date to the file system
        if ($lImageData) {
            file_put_contents($lImageFileName, $lImageData);
            return LMandator::GetUrlForTempImageFileName($lImageFileName);
        }

        return false;
    }
}
