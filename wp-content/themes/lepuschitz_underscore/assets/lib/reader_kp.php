<?php

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class LKpCatalogReader extends LCatalogReader {

    private const IMPORT_PRICE_MULTIPLIER = 1.4;

    private $_XlsxFileName = null;

    public function LoadFromFileOrUrl(string $aFileName, string $aDataType) {
        if ($aDataType === self::ALL) {
            $this->_XlsxFileName = $aFileName;
        }
    }

    public function ParseData(LCatalog $aCatalog, $aProgressHandler = null) {
        ini_set('memory_limit', '2048M');
        set_time_limit(0);

        if (empty($this->_XlsxFileName) || !is_readable($this->_XlsxFileName)) {
            throw new RuntimeException('The KP Plattner Excel file is missing or cannot be read.');
        }

        // KP Plattner catalogue prices are supplier prices; apply the required 40% markup.
        $lProductMarkup = self::IMPORT_PRICE_MULTIPLIER;
        $this->DoProgress($aProgressHandler, 1, 0, 'Reading Excel file');
        $lReader = ReaderEntityFactory::createXLSXReader();
        $lReader->open($this->_XlsxFileName);

        foreach ($lReader->getSheetIterator() as $lSheet) {
            break;
        }
        if (!isset($lSheet)) {
            $lReader->close();
            throw new RuntimeException('The KP Plattner workbook contains no worksheet.');
        }

        $lNumberOfRows = 0;
        foreach ($lSheet->getRowIterator() as $lUnusedRow) {
            $lNumberOfRows++;
        }
        $this->DoProgress($aProgressHandler, 1, 1, 'Reading finished');

        $lHeaders = [];
        $lCurrentRow = 0;
        foreach ($lSheet->getRowIterator() as $lXlsxRow) {
            $lCurrentRow++;
            $lCells = $this->CellValues($lXlsxRow);

            if ($lCurrentRow === 1) {
                $lHeaders = $this->BuildHeaderMap($lCells);
                $this->ValidateHeaders($lHeaders);
                continue;
            }
            if (isset($_GET['debug']) && $lCurrentRow >= 100) {
                break;
            }

            $this->DoProgress($aProgressHandler, $lNumberOfRows, $lCurrentRow, 'Reading products');
            if ($this->Value($lCells, $lHeaders, 'Kategorie') === 'Druckkosten') {
                $this->AddTechnologyCosts($aCatalog, $lCells, $lHeaders, $lProductMarkup);
            } else {
                $this->AddProduct($aCatalog, $lCells, $lHeaders, $lProductMarkup);
            }
        }

        $lReader->close();
    }

    private function CellValues($aRow): array {
        $lValues = [];
        foreach ($aRow->getCells() as $lCell) {
            $lValues[] = $lCell->getValue();
        }
        return $lValues;
    }

    private function BuildHeaderMap(array $aCells): array {
        $lHeaders = [];
        foreach ($aCells as $lIndex => $lHeader) {
            $lHeader = trim((string)$lHeader);
            if ($lHeader !== '') {
                $lHeaders[$lHeader] = $lIndex;
            }
        }
        return $lHeaders;
    }

    private function ValidateHeaders(array $aHeaders) {
        $lRequired = ['Artikelnummer', 'Artikelname', 'Kategorie', 'Bildname', 'Staffelmenge1', 'Einkaufspreis1', 'Druckcode'];
        foreach ($lRequired as $lHeader) {
            if ($this->HeaderIndex($aHeaders, $lHeader) === null) {
                throw new RuntimeException('Required KP Plattner column is missing: ' . $lHeader);
            }
        }
    }

    private function HeaderIndex(array $aHeaders, string $aHeader) {
        $lAliases = [
            'Artikelnummer' => ['Artikelnummer', 'Art-Nr'],
            'Bildname' => ['Bildname', 'Bildname 1'],
        ];
        foreach ($lAliases[$aHeader] ?? [$aHeader] as $lAlias) {
            if (array_key_exists($lAlias, $aHeaders)) {
                return $aHeaders[$lAlias];
            }
        }
        return null;
    }

    private function Value(array $aCells, array $aHeaders, string $aHeader) {
        $lIndex = $this->HeaderIndex($aHeaders, $aHeader);
        if ($lIndex === null) {
            return null;
        }
        return $aCells[$lIndex] ?? null;
    }

    private function Number($aValue) {
        if (is_int($aValue) || is_float($aValue)) {
            return $aValue;
        }
        $lValue = str_replace(["\xc2\xa0", ' '], '', trim((string)$aValue));
        if (preg_match('/^-?\d+(?:[.,]\d+)?$/', $lValue) !== 1) {
            return null;
        }
        return (float)str_replace(',', '.', $lValue);
    }

    private function AddProduct($aCatalog, array $aCells, array $aHeaders, float $aMarkup) {
        $lVariantNumber = trim((string)$this->Value($aCells, $aHeaders, 'Artikelnummer'));
        $lArticleName = trim((string)$this->Value($aCells, $aHeaders, 'Artikelname'));
        if ($lVariantNumber === '' || $lArticleName === '') {
            return;
        }

        list($lColor1, $lColor2) = $this->ParseColors((string)$this->Value($aCells, $aHeaders, 'Feuerzeugfarbe'));
        $lImageName = basename(trim((string)$this->Value($aCells, $aHeaders, 'Bildname')));
        $lImageUrl = $lImageName === '' ? null : $aCatalog->Mandator->ImageUrl . rawurlencode($lImageName);

        if (!$aCatalog->Products->ExistsProductCode($lArticleName)) {
            $lProduct = $aCatalog->Products->AddProduct($lArticleName, $lArticleName);
            $lProduct->Description = $this->Value($aCells, $aHeaders, 'Artikelbeschreibung');
            $lProduct->CategoryIdOrName = $this->Value($aCells, $aHeaders, 'Kategorie');
            $lProduct->ImageUrl = $lImageUrl;
            $this->AddVariant($lProduct, $lColor1, $lColor2, $lImageUrl, $lVariantNumber);
            $this->AddProductPrices($lProduct, $aCells, $aHeaders, $aMarkup);
            $lProduct->MimimumQuantity = $lProduct->GetMinimumQuantity();
            $this->AddProductLabeling($aCatalog, $lProduct, (string)$this->Value($aCells, $aHeaders, 'Druckcode'));
        } else {
            $lProduct = $aCatalog->Products->GetProductByProductCode($lArticleName);
            $this->AddVariant($lProduct, $lColor1, $lColor2, $lImageUrl, $lVariantNumber);
        }
    }

    private function ParseColors(string $aColors): array {
        $lColors = array_map('trim', explode('/', $aColors, 2));
        $lColor1 = $lColors[0] === '' ? LColor::NOCOLOR : $lColors[0];
        $lColor2 = $lColors[1] ?? '';
        foreach (['lColor1', 'lColor2'] as $lVariable) {
            $$lVariable = trim(str_ireplace(['Chrom', 'stainless steel', 'Nickel'], ['silver', 'silver', 'darkgray'], $$lVariable));
        }
        return [$lColor1, $lColor2];
    }

    private function AddVariant($aProduct, string $aColor1, string $aColor2, $aImageUrl, string $aVariantNumber) {
        if ($aColor2 === '') {
            $aProduct->AddColorWithImage($aColor1, $aImageUrl, $aVariantNumber);
        } else {
            $aProduct->AddBiColorWithImage($aColor1, $aColor2, $aImageUrl, $aVariantNumber);
        }
    }

    private function AddProductPrices($aProduct, array $aCells, array $aHeaders, float $aMarkup) {
        $lQuantities = [];
        $lPrices = [];
        for ($lIndex = 1; $lIndex <= 5; $lIndex++) {
            $lQuantityHeader = $lIndex <= 3 ? 'Staffelmenge' . $lIndex : 'Staffelmenge ' . $lIndex;
            $lQuantities[] = $this->Value($aCells, $aHeaders, $lQuantityHeader);
            $lPrices[] = $this->Value($aCells, $aHeaders, 'Einkaufspreis' . $lIndex);
        }

        for ($lIndex = 0; $lIndex < 5; $lIndex++) {
            $lQuantity = $this->Number($lQuantities[$lIndex]);
            $lPrice = $this->Number($lPrices[$lIndex]);
            if ($lQuantity === null || $lPrice === null) {
                continue;
            }
            $lNextQuantity = 0;
            for ($lNext = $lIndex + 1; $lNext < 5; $lNext++) {
                $lCandidate = $this->Number($lQuantities[$lNext]);
                if ($lCandidate !== null) {
                    $lNextQuantity = (int)$lCandidate;
                    break;
                }
            }
            $aProduct->AddPrice(
                round((float)$lPrice * $aMarkup, 2),
                (int)$lQuantity,
                $lNextQuantity > 0 ? $lNextQuantity - 1 : 0
            );
        }
    }

    private function AddProductLabeling($aCatalog, $aProduct, string $aTechnologyCodes) {
        $lLabel = $aCatalog->Labelings->AddLabel($aProduct->ProductCode);
        $lFront = $lLabel->AddPosition(LPosition::FRONTSIDELABEL);
        $lFront->Serial = 'F';
        $lBack = $lLabel->AddPosition(LPosition::BACKSIDELABEL);
        $lBack->Serial = 'B';

        foreach (array_filter(array_map('trim', explode('/', $aTechnologyCodes))) as $lCode) {
            $lDefinition = $this->TechnologyDefinition($lCode);
            if ($lDefinition === null) {
                continue;
            }
            foreach ([$lFront, $lBack] as $lPosition) {
                $lTechnology = $lPosition->AddTechnology($lCode);
                $lTechnology->Name = $lDefinition[0];
                $lTechnology->MaxColors = $lDefinition[1];
            }
        }
    }

    private function TechnologyDefinition(string $aCode) {
        switch ($aCode) {
            case 'D1':
            case 'D2':
                return ['Digitaldruck', LRanges::FULLCOLOR];
            case 'S1':
            case 'S2':
                return ['Siebdruck', 4];
            case 'T1':
            case 'T2':
                return ['Tampondruck', 4];
            case 'L':
                return ['Lasergravur', 1];
        }
        return null;
    }

    private function AddTechnologyCosts($aCatalog, array $aCells, array $aHeaders, float $aMarkup) {
        $lTechnologyName = trim((string)$this->Value($aCells, $aHeaders, 'Artikelname'));
        $lMap = [
            'Digitaldruck 1' => 'D1',
            'Digitaldruck 2' => 'D2',
            'Siebdruck 1' => 'S1',
            'Siebdruck 2' => 'S2',
            'Siebdruck 2 (DJEEP)' => 'S2',
            'Tampondruck T1' => 'T1',
            'Tampondruck T2' => 'T2',
            'Gravurkosten' => 'L',
        ];
        if (!isset($lMap[$lTechnologyName])) {
            return;
        }

        $lCode = $lMap[$lTechnologyName];
        $lDefinition = $this->TechnologyDefinition($lCode);
        $lTechnology = $aCatalog->Technologies->ExistsTechnologyCode($lCode)
            ? $aCatalog->Technologies->GetTechnologyByTechnologyCode($lCode)
            : $aCatalog->Technologies->AddTechnology($lCode, $lTechnologyName);

        $lLastRange = null;
        for ($lIndex = 1; $lIndex <= 5; $lIndex++) {
            $lQuantityHeader = $lIndex <= 3 ? 'Staffelmenge' . $lIndex : 'Staffelmenge ' . $lIndex;
            $lQuantity = $this->Number($this->Value($aCells, $aHeaders, $lQuantityHeader));
            $lPrice = $this->Number($this->Value($aCells, $aHeaders, 'Einkaufspreis' . $lIndex));
            if ($lQuantity === null || $lPrice === null) {
                continue;
            }

            $lNextQuantity = 0;
            for ($lNext = $lIndex + 1; $lNext <= 5; $lNext++) {
                $lNextHeader = $lNext <= 3 ? 'Staffelmenge' . $lNext : 'Staffelmenge ' . $lNext;
                $lCandidate = $this->Number($this->Value($aCells, $aHeaders, $lNextHeader));
                if ($lCandidate !== null) {
                    $lNextQuantity = (int)$lCandidate;
                    break;
                }
            }
            $lLastRange = $lTechnology->Ranges->AddRangeWithPrices(
                (int)$lQuantity,
                $lNextQuantity > 0 ? $lNextQuantity - 1 : 0,
                round((float)$lPrice * $aMarkup, 2),
                0,
                $lDefinition[1]
            );
        }
        if ($lLastRange !== null) {
            $lLastRange->QuantityTo = null;
        }
    }
}
