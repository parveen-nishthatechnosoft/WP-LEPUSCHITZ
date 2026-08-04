<?php

class LMarkupDown {
    public $Percentage;
    public $From;
    public $To;

    /**
     * LPrice constructor.
     *
     * @param float $aPercentage
     * @param int $aFrom
     * @param int|null $aTo
     */
    public function __construct(float $aPercentage, int $aFrom = 0, int $aTo = null) {
        // Do to check
        if ($aTo == null || $aTo == 0) {
            $aTo = PHP_INT_MAX;
        }

        $this->Percentage = $aPercentage;
        $this->From = $aFrom;
        $this->To = $aTo;
    }
}

/**
 * Class LPrice
 */
class LPrice {
    public $PricePerPiece;
    public $From;
    public $To;

    /**
     * LPrice constructor.
     *
     * @param float $aPricePerPiece
     * @param int $aFrom
     * @param int|null $aTo
     */
    public function __construct(float $aPricePerPiece, int $aFrom = 0, int $aTo = null) {
        $this->PricePerPiece = $aPricePerPiece;
        $this->From = $aFrom;
        $this->To = $aTo;
    }
}

class LPrices {
    /**
     * @var LPrice[]
     */
    public $Prices;

    public function __construct() {
        $this->Prices = array();
    }

    /**
     * @param int $aPricePerPiece
     * @param int $aFrom
     * @param int $aTo
     */
    public function AddPrice(int $aPricePerPiece, int $aFrom = 0, int $aTo = PHP_INT_MAX) {
        $lNewPrice = new LPrice($aPricePerPiece, $aFrom, $aTo);
        $this->Prices[] = $lNewPrice;
    }
}
