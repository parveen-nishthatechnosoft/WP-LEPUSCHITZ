<?php

class LJungCatalogReader extends LCatalogReader {

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
            throw new RuntimeException('The JUNG Excel file is missing or cannot be read.');
        }

        $lReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $lReader->setReadDataOnly(true);
        if (!$lReader->canRead($this->_XlsxFileName)) {
            throw new RuntimeException('The uploaded file is not a readable JUNG XLSX file.');
        }

        $this->DoProgress($aProgressHandler, 1, 0, 'Reading Excel file. This can take a few minutes.');
        $lSpreadsheet = $lReader->load($this->_XlsxFileName);
        $this->DoProgress($aProgressHandler, 1, 1, 'Reading finished');

        $lSheet = $lSpreadsheet->getSheet(0);
        $lHeaderRow = $this->FindHeaderRow($lSheet);
        $lHeaders = $this->BuildHeaderMap($lSheet, $lHeaderRow);
        $this->ValidateHeaders($lHeaders);

        $lProductMarkup = (100 + (float)get_field('jung_import_markup', 'option')) / 100;
        $lNumberOfRows = $lSheet->getHighestDataRow();

        for ($lRowNumber = $lHeaderRow + 1; $lRowNumber <= $lNumberOfRows; $lRowNumber++) {
            if (isset($_GET['debug']) && $lRowNumber >= $lHeaderRow + 100) {
                break;
            }

            $this->DoProgress($aProgressHandler, $lNumberOfRows, $lRowNumber, 'Reading products');
            $lRow = $lSheet->rangeToArray(
                'A' . $lRowNumber . ':' . $lSheet->getHighestDataColumn() . $lRowNumber,
                null,
                true,
                false
            )[0];

            $lArticleNumber = trim((string)$this->Value($lRow, $lHeaders, ['Artikel-Nr. VARIANTEN', 'neue_varianten']));
            $lArticleName = trim((string)$this->Value($lRow, $lHeaders, ['Modellname', 'artikelname']));
            if ($lArticleNumber === '' || $lArticleName === '') {
                continue;
            }

            // JUNG supplies piece prices as "St.", while older files used "Stück".
            $lUnit = $this->Normalize((string)$this->Value($lRow, $lHeaders, ['Einheit Staffel', 'einheit']));
            if (!in_array($lUnit, ['st', 'stueck'], true)) {
                continue;
            }

            $lColorCode = (string)$this->Value($lRow, $lHeaders, ['Farbe (wählbar)', 'Farbe (alle)', 'farbe']);
            list($lColor1, $lColor2) = $this->ParseColors($lColorCode);
            $lImageUrl = $this->LoadImage(
                (string)$this->Value($lRow, $lHeaders, ['Bild1_Images', 'images1']),
                (string)$this->Value($lRow, $lHeaders, ['bild_link_1'])
            );

            if (!$aCatalog->Products->ExistsProductTitle($lArticleName)) {
                $lProduct = $aCatalog->Products->AddProduct($lArticleNumber, $lArticleName);
                $lProduct->CategoryIdOrName = $this->Value($lRow, $lHeaders, ['Kategorie 1', 'katalogseite']);
                $lProduct->Description = $this->Value($lRow, $lHeaders, ['Modellbeschreibung', 'details']);
                $lProduct->ImageUrl = $lImageUrl;
                $this->AddVariant($lProduct, $lColor1, $lColor2, $lImageUrl, $lArticleNumber);
                $this->AddPrices($lProduct, $lRow, $lHeaders, $lProductMarkup);
                $lProduct->MimimumQuantity = $lProduct->GetMinimumQuantity();
                $this->AddLabeling($aCatalog, $lProduct, $lRow, $lHeaders, $lProductMarkup);
            } else {
                $lProduct = $aCatalog->Products->GetProductByProductTitle($lArticleName);
                $this->AddVariant($lProduct, $lColor1, $lColor2, $lImageUrl, $lArticleNumber);
            }
        }

        // Hash the completed product, after all of its variants have been
        // collected.  This lets the writer update only changed products.
        foreach ($aCatalog->Products->Products as $lProduct) {
            $lProduct->HashSum = null;
            $lProduct->HashSum = md5(json_encode($lProduct));
        }

        $lSpreadsheet->disconnectWorksheets();
        unset($lSpreadsheet);
    }

    private function FindHeaderRow($aSheet): int {
        $lMaximum = min(10, $aSheet->getHighestDataRow());
        for ($lRow = 1; $lRow <= $lMaximum; $lRow++) {
            $lFirstCell = trim((string)$aSheet->getCell('A' . $lRow)->getValue());
            if (in_array($lFirstCell, ['Artikel-Nr. VARIANTEN', 'neue_varianten'], true)) {
                return $lRow;
            }
        }

        throw new RuntimeException('The JUNG article header row was not found.');
    }

    private function BuildHeaderMap($aSheet, int $aHeaderRow): array {
        $lValues = $aSheet->rangeToArray(
            'A' . $aHeaderRow . ':' . $aSheet->getHighestDataColumn() . $aHeaderRow,
            null,
            true,
            false
        )[0];
        $lHeaders = [];
        foreach ($lValues as $lIndex => $lValue) {
            $lName = trim((string)$lValue);
            if ($lName !== '') {
                $lHeaders[$lName] = $lIndex;
            }
        }
        return $lHeaders;
    }

    private function ValidateHeaders(array $aHeaders) {
        $lRequiredGroups = [
            ['Artikel-Nr. VARIANTEN', 'neue_varianten'],
            ['Modellname', 'artikelname'],
            ['Einheit Staffel', 'einheit'],
        ];
        foreach ($lRequiredGroups as $lAliases) {
            if ($this->HeaderIndex($aHeaders, $lAliases) === null) {
                throw new RuntimeException('Required JUNG column is missing: ' . implode(' / ', $lAliases));
            }
        }
    }

    private function HeaderIndex(array $aHeaders, array $aAliases) {
        foreach ($aAliases as $lAlias) {
            if (array_key_exists($lAlias, $aHeaders)) {
                return $aHeaders[$lAlias];
            }
        }
        return null;
    }

    private function Value(array $aRow, array $aHeaders, array $aAliases) {
        $lIndex = $this->HeaderIndex($aHeaders, $aAliases);
        return $lIndex === null ? null : ($aRow[$lIndex] ?? null);
    }

    private function Normalize(string $aValue): string {
        $lValue = function_exists('mb_strtolower') ? mb_strtolower(trim($aValue), 'UTF-8') : strtolower(trim($aValue));
        return str_replace(['.', 'ü'], ['', 'ue'], $lValue);
    }

    private function ParseColors(string $aColorCode): array {
        $lColorCode = trim($aColorCode);
        $lColorCode = str_ireplace(['créme-', 'matt-', '-transparent'], ['', '', ''], $lColorCode);
        $lColorCode = str_ireplace(['farblos', 'blank'], ['transparent', 'silber'], $lColorCode);
        if ($lColorCode === '') {
            return [LColor::NOCOLOR, ''];
        }
        $lColors = array_map('trim', explode('/', $lColorCode, 2));
        return [$lColors[0], $lColors[1] ?? ''];
    }

    private function AddVariant($aProduct, string $aColor1, string $aColor2, $aImageUrl, string $aArticleNumber) {
        $aProduct->AddBiColorWithImage($aColor1, $aColor2, $aImageUrl ?: null, $aArticleNumber);
    }

    private function AddPrices($aProduct, array $aRow, array $aHeaders, float $aMarkup) {
        $lPairs = [];
        for ($lNumber = 1; $lNumber <= 10; $lNumber++) {
            $lPairs[] = ['Mengen_Staffel' . $lNumber, 'Staffel-Preis ' . $lNumber . ' - inkl. Aufpreis'];
            $lPairs[] = ['industrie_staffel' . $lNumber, 'industrie_preis' . $lNumber];
        }

        foreach ($lPairs as $lPair) {
            $lQuantity = $this->Value($aRow, $aHeaders, [$lPair[0]]);
            $lPrice = $this->Value($aRow, $aHeaders, [$lPair[1]]);
            if (!is_numeric($lQuantity) || !is_numeric($lPrice) || (float)$lQuantity <= 0) {
                continue;
            }

            // Locate the next populated quantity to create the upper bound.
            $lNextQuantity = 0;
            $lCurrentIndex = $this->HeaderIndex($aHeaders, [$lPair[0]]);
            foreach ($aHeaders as $lHeader => $lIndex) {
                if ($lIndex > $lCurrentIndex && preg_match('/^(Mengen_Staffel|industrie_staffel)\d+$/', $lHeader)) {
                    $lCandidate = $aRow[$lIndex] ?? null;
                    if (is_numeric($lCandidate) && (float)$lCandidate > (float)$lQuantity) {
                        $lNextQuantity = (int)$lCandidate;
                        break;
                    }
                }
            }
            $aProduct->AddPrice(round((float)$lPrice * $aMarkup, 2), (int)$lQuantity, $lNextQuantity > 0 ? $lNextQuantity - 1 : 0);
        }
    }

    private function AddLabeling($aCatalog, $aProduct, array $aRow, array $aHeaders, float $aMarkup) {
        $lLabel = $aCatalog->Labelings->AddLabel($aProduct->ProductCode);
        $lPosition = $lLabel->AddPosition(LPosition::DEFAULTLABEL);
        $lPosition->Serial = 'I';

        $lTechnologyName = trim((string)$this->Value($aRow, $aHeaders, ['Veredelung', 'veredelung']));
        $lTechnologyCode = trim((string)$this->Value($aRow, $aHeaders, ['Druckgruppe', 'druckcode_lieferant']));
        if ($lTechnologyName === '' && $lTechnologyCode === '') {
            return;
        }
        if ($lTechnologyCode === '') {
            $lTechnologyCode = $lTechnologyName;
        }
        if ($lTechnologyName === '') {
            $lTechnologyName = $lTechnologyCode;
        }

        $lMaxColors = (int)$this->Value($aRow, $aHeaders, ['Farbanzahl_Druckcode', 'farbanzahl_druckcode']);
        $lSetupCosts = $this->Value($aRow, $aHeaders, ['Drucknebenkosten pro Auftrag/Motiv pauschal - Preis', 'drucknebenkosten_1']);
        $lSetupCosts = is_numeric($lSetupCosts) ? round((float)$lSetupCosts * $aMarkup, 2) : 0;

        if (!$aCatalog->Technologies->ExistsTechnologyCode($lTechnologyCode)) {
            $lTechnology = $aCatalog->Technologies->AddTechnology($lTechnologyCode, $lTechnologyName);
            $lTechnology->CostsPerColor = 0;
            $lTechnology->SetupCosts = $lSetupCosts;
            $lTechnology->Ranges->AddRangeWithPrices(1, 0, 0, 0, $lMaxColors);
        }
        if (!$lPosition->TechnologyExists($lTechnologyCode)) {
            $lTechnology = $lPosition->AddTechnology($lTechnologyCode);
            $lTechnology->Name = $lTechnologyName;
            $lTechnology->MaxColors = $lMaxColors;
        }
    }

    private function LoadImage(string $aImageFileName, string $aImageUrl = '') {
        $aImageFileName = basename(trim($aImageFileName));
        if ($aImageFileName === '') {
            return false;
        }

        $lLocalFile = $this->Mandator->ImagePath . $aImageFileName;
        if (is_file($lLocalFile) && @getimagesize($lLocalFile) !== false) {
            return LMandator::GetUrlForTempImageFileName($lLocalFile);
        }

        $lUrls = array_filter([
            trim($aImageUrl),
            'https://www.jung-europe.de/products/images/' . rawurlencode($aImageFileName),
            'ftp://jung-online:1828@ftp.jung-europe.de/Bilddaten_images/Bilddaten-Varianten_variants-images/Variantenbilder%202022/' . rawurlencode($aImageFileName),
        ]);
        $lContext = stream_context_create(['http' => ['timeout' => 10, 'follow_location' => 1], 'ftp' => ['timeout' => 10]]);
        foreach ($lUrls as $lUrl) {
            $lContent = @file_get_contents($lUrl, false, $lContext);
            if ($lContent === false || @getimagesizefromstring($lContent) === false) {
                continue;
            }
            if (@file_put_contents($lLocalFile, $lContent, LOCK_EX) !== false) {
                return LMandator::GetUrlForTempImageFileName($lLocalFile);
            }
        }
        return false;
    }
}
