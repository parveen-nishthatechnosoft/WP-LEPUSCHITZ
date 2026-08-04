<?php

/**
 * Class LMandator
 */
class LMandator {
    // Anda Cool
    public const ANDACOOL_TYPE = 'anda';
    public const ANDACOOL = "Anda - Cool";
    public const ANDACOOL_SCRAMBLED = "a-7ad28994-n";
    // L-Shop
    public const LSHOP_TYPE = 'lshop';
    public const LSHOP = "L-Shop";
    public const LSHOP_SCRAMBLED = "l-94d0a380-s";
    // KP Plattner
    public const KP_TYPE = 'kp';
    public const KP = "KP Plattner";
    public const KP_SCRAMBLED = "k-41e1f538-p";
    // Giving Europe
    public const GE_TYPE = 'ge';
    public const GE = "Giving Europe";
    public const GE_SCRAMBLED = "g-7a83ab62-e";
    // Jung
    public const JUNG_TYPE = 'jung';
    public const JUNG = "Jung";
    public const JUNG_SCRAMBLED = "j-b26a37b1-u";
    // Römer
    public const ROEMER_TYPE = 'roemer';
    public const ROEMER = "Römer";
    public const ROEMER_SCRAMBLED = "r-34a90de6-o";

    public $Name;
    public $Type;
    public $Id;
    public $ImagePath;
    public $ImageUrl;

    /**
     * LMandator constructor.
     */
    public function __construct($aName, $aScrambledName, $aType) {
        $this->Name = $aName;
        $this->Type = $aType;
        $this->Id = $this->GetMandatorId($aName);

        $lUploadDir = wp_upload_dir();
        $this->ImagePath = $lUploadDir['basedir'] . DIRECTORY_SEPARATOR . $aScrambledName . DIRECTORY_SEPARATOR;
        $this->ImageUrl = '/wp-content/uploads/' . $aScrambledName . '/';

        // Check if folder is created
        if (!file_exists($this->ImagePath)) {
            mkdir($this->ImagePath, 0755, true);
        }
    }

    /**
     * @param string $aMandatorName
     * @return false|int
     */
    public function GetMandatorId(string $aMandatorName) {
        $lPost = get_page_by_title($aMandatorName, OBJECT, 'mandator');
        if (!$lPost)
            return false;

        return $lPost->ID;
    }

    /**
     * @param $aCatalogId
     */
    public function RenderTools($aCatalogId) {
        global $lCatalogId;
        $lCatalogId = $aCatalogId;

        switch ($this->Type) {
            case self::ANDACOOL_TYPE:
                include('tool_anda.php');
                break;
            case self::LSHOP_TYPE:
                include('tool_lshop.php');
                break;
            case self::KP_TYPE:
                include('tool_kp.php');
                break;
            case self::GE_TYPE:
                include('tool_ge.php');
                break;
            case self::JUNG_TYPE:
                include('tool_jung.php');
                break;
            case self::ROEMER_TYPE:
                include('tool_roemer.php');
        }
    }

    /**
     * @param $aFileName
     * @return false|string
     */
    public function SaveFileToTempFile($aFileName) {
        $lTempFileName = tempnam($this->ImagePath, 'tempfile_');
        move_uploaded_file($aFileName, $lTempFileName);
        return $lTempFileName;
    }

    /**
     * @return false|string
     */
    public function GenerateTempImageFileName($aExtension) {
        while (true) {
            $lImageFileName = $this->ImagePath . uniqid("img_") . '.' . $aExtension;
            if (!file_exists($lImageFileName)) {
                break;
            }
        }

        return $lImageFileName;
    }

    /**
     * @param $aFileName
     */
    public function RemoveTempImageFile($aFileName) {
        // Remove the extension if there
        $lFileName = pathinfo($aFileName, PATHINFO_FILENAME);
        if (file_exists($lFileName)) {
            unlink($lFileName);
        }
    }

    /**
     * @param $aImageFilenName
     * @return false|string
     */
    public static function GetUrlForTempImageFileName($aImageFilenName) {
        $lPos = strpos($aImageFilenName, '/wp-content/');
        if ($lPos) {
            return substr($aImageFilenName, $lPos);
        }

        return false;
    }

    /**
     * @return array
     */
    public static function GetMandatorTypes() {
        $lResult = [];
        $lResult[self::ANDACOOL_TYPE] = self::ANDACOOL;
        $lResult[self::LSHOP_TYPE] = self::LSHOP;
        $lResult[self::KP_TYPE] = self::KP;
        $lResult[self::GE_TYPE] = self::GE;
        $lResult[self::JUNG_TYPE] = self::JUNG;
        $lResult[self::ROEMER_TYPE] = self::ROEMER;

        return $lResult;
    }

    /**
     * @param $aMandatorType
     * @return string
     */
    public static function GetMandatorPrefixByType($aMandatorType) {
        return $aMandatorType . '_';
    }

    /**
     * @param $aMandatorType
     * @return mixed
     */
    public static function GetMandatorNameByType($aMandatorType) {
        $lMandatorTypes = self::GetMandatorTypes();
        return $lMandatorTypes[$aMandatorType];
    }
}
