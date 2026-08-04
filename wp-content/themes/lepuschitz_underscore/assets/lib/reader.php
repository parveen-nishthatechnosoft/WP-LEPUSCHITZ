<?php
require_once('vendor/autoload.php');
require_once('catalog.php');
require_once('product.php');
require_once('price.php');

use PhpOffice\PhpSpreadsheet;

/**
 * Class LCatalogReader
 */
abstract class LCatalogReader {
    public const ALL = 'ALL';
    public const PRODUCTS = 'PRODUCTS';
    public const PRICES = 'PRICES';
    public const PRINTS = 'PRINTS';
    public const LABELS = 'LABELS';

    public const COL_A = 0;
    public const COL_B = 1;
    public const COL_C = 2;
    public const COL_D = 3;
    public const COL_E = 4;
    public const COL_F = 5;
    public const COL_G = 6;
    public const COL_H = 7;
    public const COL_I = 8;
    public const COL_J = 9;
    public const COL_K = 10;
    public const COL_L = 11;
    public const COL_M = 12;
    public const COL_N = 13;
    public const COL_O = 14;
    public const COL_P = 15;
    public const COL_Q = 16;
    public const COL_R = 17;
    public const COL_S = 18;
    public const COL_T = 19;
    public const COL_U = 20;
    public const COL_V = 21;
    public const COL_W = 22;
    public const COL_X = 23;
    public const COL_Y = 24;
    public const COL_Z = 25;
    public const COL_AA = 26;
    public const COL_AB = 27;
    public const COL_AC = 28;
    public const COL_AD = 29;
    public const COL_AE = 30;
    public const COL_AF = 31;
    public const COL_AG = 32;
    public const COL_AH = 33;
    public const COL_AI = 34;
    public const COL_AJ = 35;
    public const COL_AK = 36;
    public const COL_AL = 37;
    public const COL_AM = 38;
    public const COL_AN = 39;
    public const COL_AO = 40;
    public const COL_AP = 41;
    public const COL_AQ = 42;
    public const COL_AR = 43;
    public const COL_AS = 44;
    public const COL_AT = 45;
    public const COL_AU = 46;
    public const COL_AV = 47;
    public const COL_AW = 48;
    public const COL_AX = 49;
    public const COL_AY = 50;
    public const COL_AZ = 51;
    public const COL_BA = 52;
    public const COL_BB = 53;
    public const COL_BC = 54;
    public const COL_BD = 55;
    public const COL_BE = 56;
    public const COL_BF = 57;
    public const COL_BG = 58;
    public const COL_BH = 59;
    public const COL_BI = 60;
    public const COL_BJ = 61;
    public const COL_BK = 62;
    public const COL_BL = 63;
    public const COL_BM = 64;
    public const COL_BN = 65;
    public const COL_BO = 66;
    public const COL_BP = 67;
    public const COL_BQ = 68;
    public const COL_BR = 69;
    public const COL_BS = 70;
    public const COL_BT = 71;
    public const COL_BU = 72;
    public const COL_BV = 73;
    public const COL_BW = 74;
    public const COL_BX = 75;
    public const COL_BY = 76;
    public const COL_BZ = 77;
    
    /**
     * @var LMandator
     */
    public $Mandator;
    private $ProgressId = 0;

    public function __construct($aMandator) {
        $this->Mandator = $aMandator;
    }

    /**
     * @param string $aFileName
     * @param string $aDataType
     * @return mixed
     */
    abstract public function LoadFromFileOrUrl(string $aFileName, string $aDataType);

    /**
     * @param LCatalog $aCatalog
     * @param $aProgressHandler
     * @return mixed
     */
    abstract public function ParseData(LCatalog $aCatalog, $aProgressHandler = null);

    function DoProgress($aProgressHandler, $aMaximumNumber, $aCurrentNumber, $aMessage = "") {
        if ($aProgressHandler != null) {
            $this->ProgressId++;
            $lProgress = $aCurrentNumber / $aMaximumNumber * 100;
            call_user_func($aProgressHandler, $this->ProgressId, $aMessage, $lProgress);
        } else {
            echo ("Step $aCurrentNumber/$aMaximumNumber: $aMessage<br>");
            ob_flush();
        }
    }

    /**
     * @param $aCell
     * @return mixed
     */
    public static function PhpOfficeGetCellValue($aCell) {
        if ($aCell->getDataType() == 'inlineStr') {
            return $aCell->getCalculatedValue(false);
        } else {
            return $aCell->getValue();
        }
    }

    /**
     * @param $aRow
     * @return array
     */
    public static function PhpOfficeGetCellsFromRow($aRow): array {
        $lCells = array();
        foreach ($aRow->getCellIterator() as $lCell) {
            array_push($lCells, LCatalogReader::PhpOfficeGetCellValue($lCell));
        }

        return $lCells;
    }

    /**
     * @param int $aNumber
     * @return string
     */
    public static function NumberToRomanRepresentation($aNumber) {
        $lMap = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1);
        $lReturnValue = '';
        while ($aNumber > 0) {
            foreach ($lMap as $roman => $int) {
                if($aNumber >= $int) {
                    $aNumber -= $int;
                    $lReturnValue .= $roman;
                    break;
                }
            }
        }
        return $lReturnValue;
    }
}
