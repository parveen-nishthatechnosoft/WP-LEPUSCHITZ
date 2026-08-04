<?php

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class LKpCatalogReader extends LCatalogReader {

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

        $lProductMarkUp = (100 + get_field('kp_import_markup', 'option')) / 100;
        $lTechnologyMarkup = $lProductMarkUp;

        // Set time limit to run through all entries and to avoid hanging if something takes too long
        set_time_limit(0);

        // Read the data
        $this->DoProgress($aProgressHandler, 1, 0, "Reading excel file");
        $lXlsxReader = ReaderEntityFactory::createXLSXReader();
        $lXlsxReader->open($this->_XlsxFileName);
        $this->DoProgress($aProgressHandler, 1, 1, "Reading finished");

        // Get the main sheet
        foreach ($lXlsxReader->getSheetIterator() as $lSpreadsheet) {
            // Take the first one
            break;
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

            // Check if not "Druckkosten"
            if ($lCells[4]->getValue() !== "Druckkosten") {
                // Product

                // Generate image file link (images are uploaded direct to the KP folder
                $lImageFileUrl = $aCatalog->Mandator->ImageUrl . $lCells[6]->getValue();

                // Get the color and do a preparse, split, color replacing
                $lColors = $lCells[2]->getValue();

                // Only first color
                $lColors = explode('/', $lColors);

                $lColor1 = $lColors[0];
                $lColor1 = trim(str_ireplace('Chrom', 'silver', $lColor1));
                $lColor1 = trim(str_ireplace('stainless steel', 'silver', $lColor1));
                $lColor1 = trim(str_ireplace('Nickel', 'darkgray', $lColor1));

                $lColor2 = null;
                if (isset($lColors[1])) {
                    $lColor2 = $lColors[1];
                    $lColor2 = trim(str_ireplace('Chrom', 'silver', $lColor2));
                    $lColor2 = trim(str_ireplace('stainless steel', 'silver', $lColor2));
                    $lColor2 = trim(str_ireplace('Nickel', 'darkgray', $lColor2));
                }

                // Parse the row now
                $lArticleNumber = $lCells[1]->getValue();
                if (!$aCatalog->Products->ExistsProductCode($lArticleNumber)) {
                    // Create a new product
                    $lNewProduct = $aCatalog->Products->AddProduct($lArticleNumber, $lArticleNumber);
                    $lNewProduct->Description = $lCells[3]->getValue();
                    $lNewProduct->ImageUrl = $lImageFileUrl;
                    if ($lColor2 == null)
                        $lNewProduct->AddColorWithImage($lColor1, $lImageFileUrl, $lCells[0]->getValue()); else
                        $lNewProduct->AddBiColorWithImage($lColor1, $lColor2, $lImageFileUrl, $lCells[0]->getValue());

                    $lLastPrice = null;
                    if (!empty($lCells[16]->getValue()))
                        $lLastPrice = $lNewProduct->AddPrice(round($lCells[16]->getValue() * $lProductMarkUp, 2), 1, $lCells[11]->getValue() - 1);
                    if (!empty($lCells[17]->getValue()))
                        $lLastPrice = $lNewProduct->AddPrice(round($lCells[17]->getValue() * $lProductMarkUp, 2), $lCells[11]->getValue(), $lCells[12]->getValue() - 1);
                    if (!empty($lCells[18]->getValue()))
                        $lLastPrice = $lNewProduct->AddPrice(round($lCells[18]->getValue() * $lProductMarkUp, 2), $lCells[12]->getValue(), $lCells[13]->getValue() - 1);
                    if (!empty($lCells[19]->getValue()))
                        $lLastPrice = $lNewProduct->AddPrice(round($lCells[19]->getValue() * $lProductMarkUp, 2), $lCells[13]->getValue(), $lCells[14]->getValue() - 1);
                    if (!empty($lCells[20]->getValue()))
                        $lLastPrice = $lNewProduct->AddPrice(round($lCells[20]->getValue() * $lProductMarkUp, 2), $lCells[14]->getValue(), $lCells[15]->getValue() - 1);

                    // Empty last to to null
                    if ($lLastPrice != null) {
                        $lLastPrice->To = null;
                    }

                    // Try to find the category/subcategory
                    $lNewProduct->CategoryIdOrName = $lCells[4]->getValue();

                    // Do the technology things two sided
                    $lNewLabel = $aCatalog->Labelings->AddLabel($lNewProduct->ProductCode);
                    $lNewPositionF = $lNewLabel->AddPosition(LPosition::FRONTSIDELABEL);
                    $lNewPositionF->Serial = 'F';    // There is always only one position at the moment
                    $lNewPositionR = $lNewLabel->AddPosition(LPosition::BACKSIDELABEL);
                    $lNewPositionR->Serial = 'B';    // There is always only one position at the moment
                    $lTechCodes = explode("/", $lCells[22]->getValue());
                    foreach ($lTechCodes as $lTechCode) {
                        $lNumberOfColors = LRanges::FULLCOLOR;

                        switch ($lTechCode) {
                            case "D1":
                            case "D2":
                                $lNewTechnologyName = "Digitaldruck";
                                break;
                            case "S1":
                            case "S2":
                                $lNewTechnologyName = "Siebdruck";
                                $lNumberOfColors = 4;
                                break;
                            case "T1":
                            case "T2":
                                $lNewTechnologyName = "Tampondruck";
                                $lNumberOfColors = 4;
                                break;
                            case "L":
                                $lNewTechnologyName = "Lasergravur";
                                $lNumberOfColors = 0;
                                break;
                            default:
                                continue 2;
                        }

                        $lNewTechnologyF = $lNewPositionF->AddTechnology($lTechCode);
                        $lNewTechnologyF->Name = $lNewTechnologyName;
                        $lNewTechnologyF->MaxColors = $lNumberOfColors;

                        $lNewTechnologyR = $lNewPositionR->AddTechnology($lTechCode);
                        $lNewTechnologyR->Name = $lNewTechnologyName;
                        $lNewTechnologyR->MaxColors = $lNumberOfColors;
                    }
                } else {
                    // Add additional colors
                    $lExistingProduct = $aCatalog->Products->GetProductByProductCode($lArticleNumber);
                    if ($lColor2 == null) {
                        $lExistingProduct->AddColorWithImage($lColor1, $lImageFileUrl, $lCells[0]->getValue());
                    } else {
                        $lExistingProduct->AddBiColorWithImage($lColor1, $lColor2, $lImageFileUrl, $lCells[0]->getValue());
                    }
                }
            } else {
                // Druckkosten
                $lTechnologyName = $lCells[1]->getValue();

                $lTechCode = "";
                $lNumberOfColors = LRanges::FULLCOLOR;
                switch ($lTechnologyName) {
                    case "Digitaldruck 1":
                        $lTechCode = "D1";
                        break;
                    case "Digitaldruck 2":
                        $lTechCode = "D2";
                        break;
                    case "Siebdruck 1":
                        $lTechCode = "S1";
                        $lNumberOfColors = 4;
                        break;
                    case "Siebdruck 2":
                        $lTechCode = "S2";
                        $lNumberOfColors = 4;
                        break;
                    case "Tampondruck T1":
                        $lTechCode = "T1";
                        $lNumberOfColors = 4;
                        break;
                    case "Tampondruck T2":
                        $lTechCode = "T2";
                        $lNumberOfColors = 4;
                        break;
                    case "Gravurkosten":
                        $lTechCode = "L";
                        $lNumberOfColors = 1;
                        break;
                    default:
                        continue 2;
                }

                // Ranges
                $lRange1 = $lCells[12]->getValue();
                $lRange2 = $lCells[13]->getValue();
                $lRange3 = $lCells[14]->getValue();
                $lRange4 = $lCells[15]->getValue();
                $lRange5 = $lCells[16]->getValue();

                // Range prices
                $lPriceRange1 = $lCells[17]->getValue();
                $lPriceRange2 = $lCells[18]->getValue();
                $lPriceRange3 = $lCells[19]->getValue();
                $lPriceRange4 = $lCells[20]->getValue();
                $lPriceRange5 = $lCells[21]->getValue();

                $lNewTechnology = $aCatalog->Technologies->AddTechnology($lTechCode, $lTechnologyName);
                $lLastRangePrice = $lNewTechnology->Ranges->AddRangeWithPrices(1, $lRange1 - 1, $lPriceRange1, 0, $lNumberOfColors);
                if (!empty($lRange2)) {
                    $lLastRangePrice = $lNewTechnology->Ranges->AddRangeWithPrices($lRange1, $lRange2 - 1, $lPriceRange2, 0, $lNumberOfColors);
                    if (!empty($lRange3)) {
                        $lLastRangePrice = $lNewTechnology->Ranges->AddRangeWithPrices($lRange2, $lRange3 - 1, $lPriceRange3, 0, $lNumberOfColors);
                        if (!empty($lRange4)) {
                            $lLastRangePrice = $lNewTechnology->Ranges->AddRangeWithPrices($lRange3, $lRange4 - 1, $lPriceRange4, 0, $lNumberOfColors);
                            if (!empty($lRange5)) {
                                $lLastRangePrice = $lNewTechnology->Ranges->AddRangeWithPrices($lRange4, $lRange5 - 1, $lPriceRange5, 0, $lNumberOfColors);
                            }
                        }
                    }
                }

                // Empty the last QuantityTo value
                $lLastRangePrice->QuantityTo = null;
            }
        }
    }
}
