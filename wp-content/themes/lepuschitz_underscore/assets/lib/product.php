<?php

/**
 * Class LProduct
 */
class LProduct {
    /**
     * @var The hash sum of the whole product
     */
    public $HashSum;

    /**
     * @var string The title for the product, eg. "Trinkbecher Mickey Mouse"
     */
    public $Title;
    /**
     * @var Productcode for displaying on website
     */
    public $ProductCode;
    /**
     * @var string The product code from the catalog
     */
    public $CatalogCode;
    /**
     * Supplier catalogue/model number. Used by the L-Shop importer.
     *
     * @var string|null
     */
    public $SupplierProductNumber;
    /**
     * @var string Description for the product (HTML)
     */
    public $Description;
    /**
     * @var int|string ID or name of category the product is in
     */
    public $CategoryIdOrName;
    /**
     * @var int|string ID or name of group the product is in
     */
    public $GroupIdOrName;
    /**
     * @var string
     */
    public $ImageUrl;
    /**
     * @var LPrice[]
     */
    public $Prices;
    /**
     * @var LColor[]
     */
    public $Colors;
    /**
     * @var
     */
    public $MimimumQuantity;

    /**
     * LProduct constructor.
     *
     * @param string $aTitle
     */
    public function __construct(string $aTitle) {
        $this->Title = $aTitle;
        $this->Prices = array();
        $this->Colors = array();
        $this->MimimumQuantity = 1;
    }

    /**
     * @param string $aColorNameOrCode
     * @param string|null $aImageUrl
     * @param string|null $aColorProductCode
     * @return bool
     */
    public function AddColorWithImage(string $aColorNameOrCode, string $aImageUrl = null, string $aColorProductCode = null) {
        // Check if already there, if not, add it
        $lNewColor = new LColor($aColorNameOrCode, $aImageUrl, $aColorProductCode);

        // Check if color with name
        if (!empty($lNewColor->ColorName)) {
            $lFound = false;
            foreach ($this->Colors as $lColor) {
                if ($lColor->ColorName == $lNewColor->ColorName) {
                    $lFound = true;
                    break;
                }
            }

            if (!$lFound) {
                $this->Colors[] = $lNewColor;
            }

            // Color was or has been added
            return true;
        }

        // Color not found
        return false;
    }

    /**
     * @param string $aColor1NameOrCode
     * @param string $aColor2NameOrCode
     * @param string|null $aImageUrl
     * @param string|null $aColorProductCode
     */
    public function AddBiColorWithImage(string $aColor1NameOrCode, string $aColor2NameOrCode, string $aImageUrl = null, string $aColorProductCode = null) {
        // Is there a second color?
        if (empty($aColor2NameOrCode)) {
            return $this->AddColorWithImage($aColor1NameOrCode, $aImageUrl, $aColorProductCode);
        }

        // Check if already there, if not, add it
        $lNewColor = new LBiColor($aColor1NameOrCode, $aColor2NameOrCode, $aImageUrl, $aColorProductCode);

        // Check if color with name
        if (!empty($lNewColor->ColorName)) {
            $lFound = false;
            foreach ($this->Colors as $lColor) {
                if ($lColor instanceof LBiColor) {
                    if (($lColor->ColorName == $lNewColor->ColorName) && ($lColor->Color2Name == $lNewColor->Color2Name)) {
                        $lFound = true;
                        break;
                    }
                }
            }

            if (!$lFound) {
                $this->Colors[] = $lNewColor;
            }

            // Color was or has been added
            return true;
        }

        // Color not found
        return false;
    }

    /**
     * @param float $aPricePerPiece
     * @param int $aFrom
     * @param int|null $aTo
     * @return LPrice
     */
    public function AddPrice(float $aPricePerPiece, int $aFrom = 1, int $aTo = null) {
        // Null check for aTo
        if ($aTo == 0)
            $aTo = null;

        // Check if price is already there
        foreach ($this->Prices as $lPrice) {
            if (($lPrice->PricePerPiece == $aPricePerPiece) && ($lPrice->From == $aFrom) && ($lPrice->To == $aTo)) {
                return $lPrice;
            }
        }

        $lNewPrice = new LPrice($aPricePerPiece, $aFrom, $aTo);
        $this->Prices[] = $lNewPrice;

        return $lNewPrice;
    }

    /**
     * @return int
     */
    public function GetMinimumQuantity() {
        $lCurrentMinimum = PHP_INT_MAX;
        foreach ($this->Prices as $lPrice) {
            if ($lPrice->From < $lCurrentMinimum) {
                $lCurrentMinimum = $lPrice->From;
                if ($lCurrentMinimum == 1)
                    break;
            }
        }

        return ($lCurrentMinimum != PHP_INT_MAX) ? $lCurrentMinimum : 1;
    }

    /**
     * @param $aColorProductCode
     * @return bool
     */
    public function ColorWithProductCodeExists($aColorProductCode): bool {
        return (array_search($aColorProductCode, array_column($this->Colors, 'ProductCode')) !== false);
    }

    /**
     * @return float
     */
    public function GetLowestPrice() {
        // Set lowest price
        $lLowestPrice = 0.0;

        if (sizeof($this->Prices) > 0) {
            $lLowestPrice = $this->Prices[0]->PricePerPiece;
            foreach ($this->Prices as $lPrice) {
                if ($lPrice->PricePerPiece < $lLowestPrice) {
                    $lLowestPrice = $lPrice->PricePerPiece;
                }
            }
        }

        return $lLowestPrice;
    }
}

class lProducts {
    /**
     * @var LProduct[]
     */
    public $Products;
    private $l = 0;

    public function __construct() {
        $this->Products = array();
    }

    public function AddProduct(string $aProductCode, string $aTitle) {
        // Check if product already exists
        $lIndex = $this->GetProductIndex($aProductCode);
        if ($lIndex) {
            return ($this->Products[$lIndex]);
        } else {
            $lNewProduct = new lProduct($aTitle);
            $lNewProduct->ProductCode = $aProductCode;
            $this->Products[] = $lNewProduct;
            return ($lNewProduct);
        }
    }

    /**
     * @param string $aProductCode
     * @return false|int|string
     */
    public function GetProductIndex(string $aProductCode) {
        $lIndex = array_search($aProductCode, array_column($this->Products, 'ProductCode'));
        return ($lIndex);
    }

    /**
     * @param string $aProductTitle
     * @return false|int|string
     */
    public function GetProductIndexByTitle(string $aProductTitle) {
        $lIndex = array_search($aProductTitle, array_column($this->Products, 'Title'));
        return ($lIndex);
    }

    /**
     * @param $aProductTitle
     * @return bool
     */
    public function ExistsProductTitle($aProductTitle) {
        // Check if product already exists
        $lIndex = $this->GetProductIndexByTitle($aProductTitle);
        return ($lIndex !== false);
    }

    /**
     * @param string $aProductCode
     * @return bool
     */
    public function ExistsProductCode(string $aProductCode) {
        // Check if product already exists
        $lIndex = $this->GetProductIndex($aProductCode);
        return ($lIndex !== false);
    }

    /**
     * @param string $aProductTitle
     * @return false|LProduct|mixed
     */
    public function GetProductByProductTitle(string $aProductTitle) {
        // Check if product title exists
        $lIndex = $this->GetProductIndexByTitle($aProductTitle);
        if ($lIndex !== false) {
            return ($this->Products[$lIndex]);
        }

        // Not found
        return false;
    }

    /**
     * @param string $aProductCode
     * @return false|LProduct|mixed
     */
    public function GetProductByProductCode(string $aProductCode) {
        // Check if product already exists
        $lIndex = $this->GetProductIndex($aProductCode);
        if ($lIndex !== false) {
            return ($this->Products[$lIndex]);
        }

        // Not found
        return false;
    }
}
