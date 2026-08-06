<?php

class LAndaCatalogReader extends LCatalogReader {
    public const PRODUCTSXMLURL = 'https://xml.andapresent.com/export/products/de/HMS6EEYABH8WMPCEUVJJWFZWY32A78KPX2M7AV3X1ISLVQZX5QKUCVNSG8M3GLVE';
    public const PRICESXMLURL = 'https://xml.andapresent.com/export/prices/HMS6EEYABH8WMPCEUVJJWFZWY32A78KPX2M7AV3X1ISLVQZX5QKUCVNSG8M3GLVE';
    public const PRINTSXMLURL = 'https://xml.andapresent.com/export/printingprices/HMS6EEYABH8WMPCEUVJJWFZWY32A78KPX2M7AV3X1ISLVQZX5QKUCVNSG8M3GLVE';
    public const LABELSXMLURL = 'https://xml.andapresent.com/export/labeling/de/HMS6EEYABH8WMPCEUVJJWFZWY32A78KPX2M7AV3X1ISLVQZX5QKUCVNSG8M3GLVE';

    public const PRODUCTSXMLTESTFILE = __DIR__ . '/../../../../../tools/testdata/products.xml';
    public const PRICESXMLTESTFILE = __DIR__ . '/../../../../../tools/testdata/prices.xml';
    public const PRINTSXMLTESTFILE = __DIR__ . '/../../../../../tools/testdata/printingprices.xml';
    public const LABELSXMLTESTFILE = __DIR__ . '/../../../../../tools/testdata/labeling.xml';

    private $_ProductsData = null;
    private $_PricesData = null;
    public $_PrintsData = null;
    public $_LabelsData = null;

    /**
     * @param string $aFileName
     * @param string $aDataType
     * @return mixed|void
     */
    public function LoadFromFileOrUrl(string $aFileName, string $aDataType) {
        // Load the file for XML parsing
        $lData = @file_get_contents($aFileName);
        if ($lData === false || trim($lData) === '') {
            $lError = error_get_last();
            $lReason = '';
            if (!empty($lError['message']) && preg_match('/HTTP\/\S+\s+\d{3}\s+[^\r\n]+/', $lError['message'], $lMatch)) {
                $lReason = ': ' . $lMatch[0];
            }
            throw new RuntimeException(sprintf(
                'Could not load the Anda %s feed%s',
                $aDataType,
                $lReason
            ));
        }

        switch ($aDataType) {
            case $this::PRICES:
                $this->_PricesData = $lData;
                break;

            case $this::PRINTS:
                $this->_PrintsData = $lData;
                break;

            case $this::LABELS:
                $this->_LabelsData = $lData;
                break;

            default:
                $this->_ProductsData = $lData;
        }
    }

    /**
     * @param $aProgressHandler
     * @return mixed
     */
    public function ParseData(LCatalog $aCatalog, $aProgressHandler = null) {
        // Increase the memory
        ini_set('memory_limit', '1024M');

        // Get the XML content and parse it
        $lProductsXml = simplexml_load_string($this->_ProductsData);
        $lPricesXml = simplexml_load_string($this->_PricesData);
        $lPrintsXml = simplexml_load_string($this->_PrintsData);
        $lLabelsXml = simplexml_load_string($this->_LabelsData);

        // Count loops
        $lCurrentLoops = 0;
        $lTotalNumberOfProducts = $lProductsXml->children()->count();
        $lTotalNumberOfPrices = $lPricesXml->children()->count();
        $lTotalNumberOfPrints = $lPrintsXml->prices->children()->count();
        $lTotalNumberOfLabels = $lLabelsXml->children()->count();
        $lNumberOfLoops = $lTotalNumberOfProducts + $lTotalNumberOfPrices + $lTotalNumberOfPrints + $lTotalNumberOfLabels;

        // Now run through all products
        $lProductsCount = 0;
        $this->DoProgress($aProgressHandler, $lNumberOfLoops, $lCurrentLoops, "Reading products");
        foreach ($lProductsXml->children() as $lProductXml) {
            // Do progress
            $lProductsCount++;
            $lCurrentLoops++;
            $this->DoProgress($aProgressHandler, $lNumberOfLoops, $lCurrentLoops, "Reading products $lProductsCount / $lTotalNumberOfProducts");

            // Set time limit to run through all entries and to avoid hanging if something takes too long
            set_time_limit(10);

            // Get the full item number
            $lFullItemNumber = (string)$lProductXml->itemNumber;

            // Extract the item number
            $lMatchParts = array();
            if (!preg_match('/([a-zA-Z]+[\d]+)/', $lFullItemNumber, $lMatchParts) > 0) {
                // Problem with the product code! Maybe log this and do error handling!
                continue;
            };

            // Get the productcode
            $lProductCode = $lMatchParts[1];

            // Get the product title
            $lProductName = (string)$lProductXml->name . (!empty((string)$lProductXml->designName) ? " '" . (string)$lProductXml->designName . "'" : '');

            // Add or find the product in the list
            $lProductIsNew = !$aCatalog->Products->ExistsProductCode($lProductCode);
            $lProduct = $aCatalog->Products->AddProduct($lProductCode, $lProductName);

            // Assign further values if new
            if ($lProductIsNew) {
                // Assign description
                $lProduct->Description = (string)$lProductXml->descriptions;
                $lProduct->ImageUrl = (string)$lProductXml->primaryImage;
            }

            // And add additional information if new or already exists
            $lProduct->AddColorWithImage((string)$lProductXml->primaryColor, (string)$lProductXml->primaryImage, $lFullItemNumber);

            // Get the categories
            $lCategories = [];
            foreach ($lProductXml->categories->children() as $lCategoryXml) {
                $lCategoryLevel = (string)$lCategoryXml->level;
                $lCategoryId = (int)$lCategoryXml->externalId;
                $lCategoryName = (string)$lCategoryXml->name;
                $lCategories[$lCategoryLevel] = new LCategory($lCategoryId, $lCategoryName);
            }
            ksort($lCategories);

            $lCategoryL1 = $aCatalog->Categories->AddCategory($lCategories[1]->Id, $lCategories[1]->Name);
            $lCategoryL1->ReadAndaPrices();
            $lProduct->CategoryIdOrName = $lCategories[1]->Name;
            if (isset($lCategories[2])) {
                $lCategoryL2 = $lCategoryL1->AddCategory($lCategories[2]->Id, $lCategories[2]->Name);
                $lCategoryL2->ReadAndaPrices();
                $lProduct->GroupIdOrName = $lCategories[2]->Name;
            }
        }

        // Read price markdown from options
        $lPriceMarkdown = get_field('anda_import_markdown', 'option');

        // Now read all the prices
        $lPricesCount = 0;
        foreach ($lPricesXml->children() as $lPriceXml) {
            // Do progress
            $lPricesCount++;
            $lCurrentLoops++;
            $this->DoProgress($aProgressHandler, $lNumberOfLoops, $lCurrentLoops, "Reading product prices $lPricesCount / $lTotalNumberOfPrices");

            // Get the full item number
            $lFullItemNumber = $lPriceXml->itemNumber;

            // Extract the item number
            $lMatchParts = array();
            if (!preg_match('/([a-zA-Z]+[\d]+)/', $lFullItemNumber, $lMatchParts) > 0) {
                // Problem with the product code! Maybe log this and do error handling!
                continue;
            };

            // Get the productcode
            $lProductCode = $lMatchParts[1];

            // Check if numberpart equals full number if there is a _ in the full number
            if (strpos($lFullItemNumber, '_')) {
                if (strcmp($lFullItemNumber, $lProductCode) != 0) {
                    // Only prices from the base product will be picked
                    continue;
                }
            }

            // Check if product exists
            $lProduct = $aCatalog->Products->GetProductByProductCode($lProductCode);
            if ($lProduct === false) {
                // Product not found, skip
                continue;
            }

            // Check if price is listprice
            $lPriceType = $lPriceXml->type;
            if ($lPriceType != 'listPrice') {
                // Not a listprice, skip
                continue;
            }

            // Check if color with this product code exists
            if (!$lProduct->ColorWithProductCodeExists($lFullItemNumber)) {
                continue;
            }

            // Get the price
            $lPriceString = (string)$lPriceXml->amount;
            $lPrice = floatval($lPriceString);

            // Price calculation based on values in option or in category settings
            $lBasePrice = round($lPrice * ((100 - $lPriceMarkdown) / 100), 2);

            // Get the category rates
            $lPriceRates = $aCatalog->Categories->GetAndaPriceRates($lProduct->CategoryIdOrName);

            // Are there price rates for the category
            if (sizeof($lPriceRates) > 0) {
                // Loop through all rates
                foreach ($lPriceRates as $lPriceRate) {
                    $lRatedPrice = round($lBasePrice * ((100 + $lPriceRate->Percentage) / 100), 2);
                    $lProduct->AddPrice($lRatedPrice, $lPriceRate->From, $lPriceRate->To);
                }
            } else {
                // No rates
                $lProduct->AddPrice($lBasePrice);
            }
            $lProduct->HashSum = md5(json_encode($lProduct));
        }

        // Now read all the print technologies and prices
        $lPrintPricesCount = 0;
        foreach ($lPrintsXml->prices->children() as $lPrice) {
            // Do progress
            $lPrintPricesCount++;
            $lCurrentLoops++;
            $this->DoProgress($aProgressHandler, $lNumberOfLoops, $lCurrentLoops, "Reading printing products $lPrintPricesCount / $lTotalNumberOfPrints");

            $lNewTechnology = $aCatalog->Technologies->AddTechnology($lPrice->TechnologyCode, $lPrice->TechnologyName);
            $lNewTechnology->HashSum = md5(json_encode($lPrice));

            foreach ($lPrice->ranges->children() as $lRange) {
                // Check number of colors
                if ((string)$lRange->NumberOfColours == "fullcolour") {
                    $lNumberOfColors = LRanges::FULLCOLOR;
                } else {
                    $lNumberOfColors = (int)$lRange->NumberOfColours;
                }
                $lNewRange = $lNewTechnology->Ranges->AddRange((int)$lRange->QuantityFrom, (int)$lRange->QuantityTo, $lNumberOfColors);

                // Find the range multiplier
                $lRangeMultiplier = 1.0;
                foreach ($aCatalog->Categories->GlobalAndaRates as $lRate) {
                    if ((int)$lRange->QuantityFrom >= $lRate->From && (int)$lRange->QuantityFrom <= $lRate->To) {
                        $lRangeMultiplier = (100 + $lRate->Percentage) / 100;
                    }
                }

                $lNewRange->UnitPrice = (float)$lRange->UnitPrice * $lRangeMultiplier;
                $lNewRange->SetupPricePerColor = (float)$lRange->SetupCost;

                if (!$lNewTechnology->SizeFrom) {
                    $lNewTechnology->SizeFrom = (float)$lRange->SizeFrom;
                } else {
                    $lNewTechnology->SizeFrom = min($lNewTechnology->SizeFrom, (float)$lRange->SizeFrom);
                }

                if (!$lNewTechnology->SizeTo) {
                    $lNewTechnology->SizeTo = (float)$lRange->SizeTo;
                } else {
                    $lNewTechnology->SizeTo = max($lNewTechnology->SizeTo, (float)$lRange->SizeTo);
                }
            }
        }

        // Do the labelings
        $lLabelingsCount = 0;
        foreach ($lLabelsXml->children() as $lLabel) {
            // Do progress
            $lLabelingsCount++;
            $lCurrentLoops++;
            $this->DoProgress($aProgressHandler, $lNumberOfLoops, $lCurrentLoops, "Reading labelings $lLabelingsCount / $lTotalNumberOfLabels");

            // Extract the item number
            $lMatchParts = array();
            if (!preg_match('/([a-zA-Z]+[\d]+)/', (string)$lLabel->itemNumber, $lMatchParts) > 0) {
                // Problem with the product code! Maybe log this and do error handling!
                continue;
            };

            // Get the productcode
            $lProductCode = $lMatchParts[1];

            // Check if already labels are added
            if ($aCatalog->Labelings->LabelExists($lProductCode)) {
                continue;
            }

            // Check if product exists
            $lProduct = $aCatalog->Products->GetProductByProductCode($lProductCode);
            if ($lProduct === false) {
                // Product not found, skip
                continue;
            }

            $lNewLabel = $aCatalog->Labelings->AddLabel($lProductCode);
            $lNewLabel->PrintTemplate = (string)$lLabel->printTemplate;

            foreach ($lLabel->positions->children() as $lPosition) {
                $lNewPosition = $lNewLabel->AddPosition((string)$lPosition->posName);
                $lNewPosition->Serial = (string)$lPosition->serial;

                foreach ($lPosition->technologies->children() as $lTechnology) {
                    $lNewTechnology = $lNewPosition->AddTechnology((string)$lTechnology->Code);
                    $lNewTechnology->Name = (string)$lTechnology->Name;
                    if ((string)$lTechnology->maxColor == "Full Color") {
                        $lNumberOfColors = LRanges::FULLCOLOR;
                    } else {
                        $lNumberOfColors = (int)$lTechnology->maxColor;
                    }
                    $lNewTechnology->MaxColors = $lNumberOfColors;
                    $lNewTechnology->MaxSize = max((float)$lTechnology->maxWmm, (float)$lTechnology->maxHmm, (float)$lTechnology->maxDmm);
                }
            }
        }
    }
}
