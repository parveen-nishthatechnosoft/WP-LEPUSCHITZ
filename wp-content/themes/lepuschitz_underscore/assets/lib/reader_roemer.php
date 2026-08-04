<?php

use PhpOffice\PhpSpreadsheet;

class LRoemerCatalogReader extends LCatalogReader {

    private $_ArticlesFileName = null;
    private $_CostsDataFileName = null;
    public $_ColorsDataFileName = null;
    public $_PositionsDataFileName = null;

    /**
     * @param string $aFileName
     * @param string $aDataType
     * @return mixed
     */
    public function LoadFromFileOrUrl(string $aFileName, string $aDataType) {
        // If http, use context
        switch ($aDataType) {
            case $this::PRICES:
                $this->_CostsDataFileName = $aFileName;
                break;

            case $this::PRINTS:
                $this->_ColorsDataFileName = $aFileName;
                break;

            case $this::LABELS:
                $this->_PositionsDataFileName = $aFileName;
                break;

            default:
                $this->_ArticlesFileName = $aFileName;
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

        $lProductMarkUp = (100 + get_field('roemer_import_markup', 'option')) / 100;
        $lTechnologyMarkup = $lProductMarkUp;

        // Set time limit to run through all entries and to avoid hanging if something takes too long
        set_time_limit(0);

        // Open the spreadsheet
        $lXlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

        // Can the costs file be read
        if ($lXlsxReader->canRead($this->_CostsDataFileName)) {
            // Load the file
            $this->DoProgress($aProgressHandler, 1, 0, "Reading costs excel file.");
            $lSpreadsheet = $lXlsxReader->load($this->_CostsDataFileName);
            $this->DoProgress($aProgressHandler, 1, 1, "Reading finished");

            // Select first sheet
            $lSheetCosts = $lSpreadsheet->getSheet(0);
            $lNumberOfRows = $lSheetCosts->getHighestRow();

            $lCurrentRow = 0;
            $lCosts = [];
            foreach ($lSheetCosts->getRowIterator() as $lRow) {
                $lCurrentRow++;

                if (isset($_GET['debug'])) {
                    if ($lCurrentRow == 100)
                        break;
                }

                // Skip first row
                if ($lCurrentRow == 1) {
                    continue;
                }

                $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, "Reading cost");

                $lCells = LCatalogReader::PhpOfficeGetCellsFromRow($lRow);

                $lCost = new LRoemerCost($lCells[0], $lCells[1], $lCells[3], $lCells[4] == 1);
                $lCosts[$lCells[0]] = $lCost;
            }
        }

        // Can the colors file be read
        if ($lXlsxReader->canRead($this->_ColorsDataFileName)) {
            // Load the file
            $this->DoProgress($aProgressHandler, 1, 0, "Reading colors excel file.");
            $lSpreadsheet = $lXlsxReader->load($this->_ColorsDataFileName);
            $this->DoProgress($aProgressHandler, 1, 1, "Reading finished");

            // Select first sheet
            $lSheetColors = $lSpreadsheet->getSheet(0);
            $lNumberOfRows = $lSheetColors->getHighestRow();

            $lCurrentRow = 0;
            foreach ($lSheetColors->getRowIterator() as $lRow) {
                $lCurrentRow++;

                if (isset($_GET['debug'])) {
                    if ($lCurrentRow == 100)
                        break;
                }

                // Skip first row
                if ($lCurrentRow == 1) {
                    continue;
                }

                $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, "Reading color");

                $lCells = LCatalogReader::PhpOfficeGetCellsFromRow($lRow);

                $lNewTechnology = $aCatalog->Technologies->AddTechnology($lCells[0], $lCells[1]);
                $lNewTechnology->CostsPerColor = 0;
                $lNewTechnology->SetupCosts = $lCosts[$lCells[18]]->Price;
                if (!empty($lCells[4])) {
                    $lNewTechnology->Ranges->AddRangeWithPrices($lCells[5], !empty($lCells[7]) ? $lCells[7] : 0, round($lCells[4] * $lTechnologyMarkup, 2), 0, 1);
                }
                if (!empty($lCells[6])) {
                    $lNewTechnology->Ranges->AddRangeWithPrices($lCells[7], !empty($lCells[9]) ? $lCells[9] : 0, round($lCells[6] * $lTechnologyMarkup, 2), 0, 1);
                }
                if (!empty($lCells[8])) {
                    $lNewTechnology->Ranges->AddRangeWithPrices($lCells[9], !empty($lCells[11]) ? $lCells[11] : 0, round($lCells[8] * $lTechnologyMarkup, 2), 0, 1);
                }
                if (!empty($lCells[10])) {
                    $lNewTechnology->Ranges->AddRangeWithPrices($lCells[11], !empty($lCells[13]) ? $lCells[13] : 0, round($lCells[10] * $lTechnologyMarkup, 2), 0, 1);
                }
                if (!empty($lCells[12])) {
                    $lNewTechnology->Ranges->AddRangeWithPrices($lCells[13], !empty($lCells[15]) ? $lCells[15] : 0, round($lCells[12] * $lTechnologyMarkup, 2), 0, 1);
                }
                if (!empty($lCells[14])) {
                    $lNewTechnology->Ranges->AddRangeWithPrices($lCells[15], 0, round($lCells[14] * $lTechnologyMarkup, 2), 0, 1);
                }
            }
        }

        // Can the positions file be read
        if ($lXlsxReader->canRead($this->_PositionsDataFileName)) {
            // Load the file
            $this->DoProgress($aProgressHandler, 1, 0, "Reading positions excel file.");
            $lSpreadsheet = $lXlsxReader->load($this->_PositionsDataFileName);
            $this->DoProgress($aProgressHandler, 1, 1, "Reading finished");

            // Select first sheet
            $lSheetPositions = $lSpreadsheet->getSheet(0);
            $lNumberOfRows = $lSheetPositions->getHighestRow();

            $lCurrentRow = 0;
            $lPositions = [];
            foreach ($lSheetPositions->getRowIterator() as $lRow) {
                $lCurrentRow++;

                if (isset($_GET['debug'])) {
                    if ($lCurrentRow == 100)
                        break;
                }

                // Skip first row
                if ($lCurrentRow == 1) {
                    continue;
                }

                $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, "Reading positions");

                $lCells = LCatalogReader::PhpOfficeGetCellsFromRow($lRow);

                $lCost = new LRoemerPosition($lCells[0], $lCells[1], $lCells[3]);
                $lPositions[$lCells[0]] = $lCost;
            }
        }

        // Can the products file be read
        if ($lXlsxReader->canRead($this->_ArticlesFileName)) {
            // Load the file
            $this->DoProgress($aProgressHandler, 1, 0, "Reading products excel file. Can take a view minutes!");
            $lSpreadsheet = $lXlsxReader->load($this->_ArticlesFileName);
            $this->DoProgress($aProgressHandler, 1, 1, "Reading finished");

            // Artikelliste (first sheet)
            $lSheetArticles = $lSpreadsheet->getSheet(0);
            $lNumberOfRows = $lSheetArticles->getHighestRow();

            $lCurrentRow = 0;
            foreach ($lSheetArticles->getRowIterator() as $lRow) {
                $lCurrentRow++;

                if (isset($_GET['debug'])) {
                    if ($lCurrentRow == 100)
                        break;
                }

                // Skip first rows
                if ($lCurrentRow < 5) {
                    continue;
                }

                $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, "Reading products");

                $lCells = LCatalogReader::PhpOfficeGetCellsFromRow($lRow);

                // Create the product
                $lArticleNumber = $lCells[1];
                $lArticleName = $lCells[8];

                // Check if article-number begins with specific string, then skip
                $lSkipStrings = [
                    '6SPO',
                    'ASA',
                    '2P'
                ];
                foreach ($lSkipStrings as $lSkipString) {
                    if (strpos($lArticleNumber, $lSkipString) === 0)
                        continue 2;
                }

                // Load the image for the product
                $lImageUrl = "";
                if (file_exists($aCatalog->Mandator->ImagePath . $lCells[24])) {
                    $lImageUrl = $aCatalog->Mandator->ImageUrl . $lCells[24];
                }

                if (!$aCatalog->Products->ExistsProductCode($lArticleNumber)) {
                    // Create a new product
                    $lNewProduct = $aCatalog->Products->AddProduct($lArticleNumber, $lArticleName);
                    $lNewProduct->CategoryIdOrName = $lCells[19];
                    $lNewProduct->Description = $lCells[15];
                    $lNewProduct->ImageUrl = $lImageUrl;

                    if (!empty($lCells[48] && !empty($lCells[49]))) {
                        $lNewProduct->AddPrice(round($lCells[48] * $lProductMarkUp, 2), $lCells[49], empty($lCells[51]) ? 0 : $lCells[51] - 1);
                    }
                    if (!empty($lCells[50] && !empty($lCells[51]))) {
                        $lNewProduct->AddPrice(round($lCells[50] * $lProductMarkUp, 2), $lCells[51], empty($lCells[53]) ? 0 : $lCells[53] - 1);
                    }
                    if (!empty($lCells[52]) && !empty($lCells[53])) {
                        $lNewProduct->AddPrice(round($lCells[52] * $lProductMarkUp, 2), $lCells[53], empty($lCells[55]) ? 0 : $lCells[55] - 1);
                    }
                    if (!empty($lCells[54]) && !empty($lCells[55])) {
                        $lNewProduct->AddPrice(round($lCells[54] * $lProductMarkUp, 2), $lCells[55], empty($lCells[57]) ? 0 : $lCells[57] - 1);
                    }
                    if (!empty($lCells[56]) && !empty($lCells[57])) {
                        $lNewProduct->AddPrice(round($lCells[56] * $lProductMarkUp, 2), $lCells[57], empty($lCells[59]) ? 0 : $lCells[59] - 1);
                    }
                    if (!empty($lCells[58]) && !empty($lCells[59])) {
                        $lNewProduct->AddPrice(round($lCells[58] * $lProductMarkUp, 2), $lCells[59], empty($lCells[61]) ? 0 : $lCells[61] - 1);
                    }
                    if (!empty($lCells[60]) && !empty($lCells[61])) {
                        $lNewProduct->AddPrice(round($lCells[60] * $lProductMarkUp, 2), $lCells[61], 0);
                    }

                    $lNewProduct->MimimumQuantity = $lNewProduct->GetMinimumQuantity();

                    // Labelings
                    $lPositionIndex = 1;
                    $lNewLabel = $aCatalog->Labelings->AddLabel($lNewProduct->ProductCode);
                    if (!empty($lCells[88])) {
                        $lPosition = $lPositions[$lCells[88]];
                        $lNewPosition = $lNewLabel->AddPosition($lPosition->Title);
                        $lNewPosition->Serial = LCatalogReader::NumberToRomanRepresentation($lPositionIndex++);
                        foreach ($lPosition->PrintTypeSkus as $lPrintTypeSku) {
                            $lNewTechnology = $lNewPosition->AddTechnology($lPrintTypeSku);
                            $lNewTechnology->Name = $aCatalog->Technologies->GetTechnologyByTechnologyCode($lPrintTypeSku)->Name;
                            $lNewTechnology->MaxColors = 1;
                        }
                    }
                } else {
                    // Add additional colors
                    $lExistingProduct = $aCatalog->Products->GetProductByProductTitle($lArticleName);
                }
            }
        }
    }
}

class LRoemerPosition {
    public $Sku;
    public $Title;
    public $PrintTypeSkus = [];

    public function __construct($aSku, $aTitle, $aPrintTypeSkus) {
        $this->Sku = $aSku;
        $this->Title = $aTitle;

        // Split printtype skus
        $lPrintTypeSkus = explode(',', $aPrintTypeSkus);
        foreach ($lPrintTypeSkus as $lPrintTypeSku) {
            $this->PrintTypeSkus[] = $lPrintTypeSku;
        }
    }
}

class LRoemerCost {
    public $Sku;
    public $Title;
    public $Price;
    public $PricePerItem;

    public function __construct($aSku, $aTitle, $aPrice, $aPricePerItem) {
        $this->Sku = $aSku;
        $this->Title = $aTitle;
        $this->Price = $aPrice;
        $this->PricePerItem = $aPricePerItem;
    }
}
