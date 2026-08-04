<?php

class LRange {
    public $NumberOfColors;
    public $QuantityFrom;
    public $QuantityTo;
    public $SizeFrom;
    public $SizeTo;
    public $UnitPrice;
    public $SetupPricePerColor;
}

class lRanges {
    public const FULLCOLOR = 16777216;

    /**
     * @var LRange[]
     */
    public $Ranges = array();

    /**
     * @param $aQuantityFrom
     * @param $aQuantityTo
     * @param int $aNumberOfColors
     * @return LRange
     */
    public function AddRange($aQuantityFrom, $aQuantityTo, $aNumberOfColors = LRanges::FULLCOLOR) {
        // Check if product already exists
        $lIndex = $this->GetRangeIndex($aQuantityFrom, $aQuantityTo, $aNumberOfColors);
        if ($lIndex) {
            return ($this->Ranges[$lIndex]);
        } else {
            $lNewRange = new LRange();
            $lNewRange->QuantityFrom = $aQuantityFrom;
            $lNewRange->QuantityTo = $aQuantityTo;
            $lNewRange->NumberOfColors = $aNumberOfColors;
            $this->Ranges[] = $lNewRange;

            return $lNewRange;
        }
    }

    /**
     * @param $aQuantityFrom
     * @param $aQuantityTo
     * @param $aUnitPrice
     * @param int $aSetupPricePerColor
     * @param int $aNumberOfColors
     * @return LRange
     */
    public function AddRangeWithPrices($aQuantityFrom, $aQuantityTo, $aUnitPrice, $aSetupPricePerColor = 0, $aNumberOfColors = LRanges::FULLCOLOR) {
        $lNewRange = $this->AddRange($aQuantityFrom, $aQuantityTo, $aNumberOfColors);
        $lNewRange->UnitPrice = $aUnitPrice;
        $lNewRange->SetupPricePerColor = $aSetupPricePerColor;

        return $lNewRange;
    }

    /**
     * @param $aQuantityFrom
     * @param $aQuantityTo
     * @param $aNumberOfColors
     * @return false|int
     */
    public function GetRangeIndex($aQuantityFrom, $aQuantityTo, $aNumberOfColors) {
        // Search for range
        $lIndex = 0;
        foreach ($this->Ranges as $lRange) {
            if (($lRange->QuantityFrom == $aQuantityFrom) && ($lRange->QuantityTo == $aQuantityTo) && ($lRange->NumberOfColors == $aNumberOfColors)) {
                return $lIndex;
            }

            $lIndex++;
        }

        // Not found
        return false;
    }

    /**
     * @param $aQuantityFrom
     * @param $aQuantityTo
     * @param $aNumberOfColors
     * @return bool
     */
    public function ExistsRange($aQuantityFrom, $aQuantityTo, $aNumberOfColors) {
        // Check if range already exists
        $lIndex = $this->GetRangeIndex($aQuantityFrom, $aQuantityTo, $aNumberOfColors);
        return ($lIndex !== false);
    }
}

class LTechnologyCosts {
    public $HashSum;
    public $Code;
    public $Name;
    public $CostsPerColor;
    public $SetupCosts;
    public $SizeFrom;
    public $SizeTo;
    /**
     * @var lRanges
     */
    public $Ranges;

    public function __construct($aCode) {
        $this->Code = $aCode;
        $this->Ranges = new lRanges();
    }
}

class LTechnologies {
    /**
     * @var LTechnologyCosts[]
     */
    public $Technologies = array();

    public function AddTechnology(string $aTechnologyCode, string $aTitle) {
        // Check if product already exists
        $lIndex = $this->GetTechnologyIndex($aTechnologyCode);
        if ($lIndex) {
            return ($this->Technologies[$lIndex]);
        } else {
            $lNewTechnology = new LTechnologyCosts($aTechnologyCode);
            $lNewTechnology->Name = $aTitle;
            $this->Technologies[] = $lNewTechnology;
            return ($lNewTechnology);
        }
    }

    /**
     * @param string $aTechnologyCode
     * @return false|int|string
     */
    public function GetTechnologyIndex(string $aTechnologyCode) {
        $lIndex = array_search($aTechnologyCode, array_column($this->Technologies, 'Code'));
        return ($lIndex);
    }

    /**
     * @param string $aTechnologyCode
     * @return bool
     */
    public function ExistsTechnologyCode(string $aTechnologyCode) {
        // Check if product already exists
        $lIndex = $this->GetTechnologyIndex($aTechnologyCode);
        return ($lIndex !== false);
    }

    /**
     * @param string $aTechnologyCode
     * @return false|LTechnologyCosts|mixed
     */
    public function GetTechnologyByTechnologyCode(string $aTechnologyCode) {
        // Check if product already exists
        $lIndex = $this->GetTechnologyIndex($aTechnologyCode);
        if ($lIndex !== false) {
            return ($this->Technologies[$lIndex]);
        }

        // Not found
        return false;
    }
}
