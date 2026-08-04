<?php
function getProductThumbnail($aProductId) {
    // Get the image url
    $lImageUrl = get_field('source_image', $aProductId);

    // Get variations and check if a not white or black image is there
    $lVariations = get_field('variants', $aProductId);
    $lVariatioUrls = [];
    foreach ($lVariations as $lVariation) {
        $lVarColorCode = strtolower($lVariation['color_code']);
        if ($lVarColorCode != "#ffffff" && $lVarColorCode != "#000000") {
            $lVariatioUrls[] = $lVariation['color_image'];
        }
    }

    if (sizeof($lVariatioUrls) > 0) {
        $lIndex = $aProductId % sizeof($lVariatioUrls);
        $lImageUrl = $lVariatioUrls[$lIndex];
    }

    return getResizedImage($lImageUrl, 190, 150, 'thumb_');
}

function getProductImage($aProductId) {
    // Get the image url
    $lImageUrl = get_field('source_image', $aProductId);

    // Get variations and check if a not white or black image is there
    $lVariations = get_field('variants', $aProductId);
    $lVariatioUrls = [];
    foreach ($lVariations as $lVariation) {
        $lVarColorCode = strtolower($lVariation['color_code']);
        if ($lVarColorCode != "#ffffff" && $lVarColorCode != "#000000") {
            $lVariatioUrls[] = $lVariation['color_image'];
        }
    }

    if (sizeof($lVariatioUrls) > 0) {
        $lIndex = $aProductId % sizeof($lVariatioUrls);
        $lImageUrl = $lVariatioUrls[$lIndex];
    }

    return getResizedImage($lImageUrl, 758, 758, 'image758_');
}

/**
 * Returns the path for the thumbnail image and also removes the domain part (should not be necessary)
 * If the thumbnail is not there, it should be generated
 *
 * @param $aProductId
 * @return string
 */
function getResizedImage($aImageUrl, $aMaxWidth, $aMaxHeight, $aPrefix) {
    // Split URL
    $lUrlParts = parse_url($aImageUrl);

    // Get the path
    $lPath = $lUrlParts['path'];

    // Split the path
    $lPathParts = explode('/', $lPath);

    // Get the last one
    $lImageFile = end($lPathParts);

    // Prepend prefix
    $lDestImageFile = $aPrefix . $lImageFile;

    // Replace it in the name
    $lThumbPath = str_replace($lImageFile, $lDestImageFile, $lPath);

    // Create the filesystem path
    $lDestImageFile = ABSPATH . $lThumbPath;
    $lDestImageFile = str_replace(array(
        '/',
        '\\',
        '//',
        '\\\\'
    ), DIRECTORY_SEPARATOR, $lDestImageFile);

    // Check if the file exists
    if (!file_exists($lDestImageFile)) {
        // Create the thumbnail image
        $lImageFile = ABSPATH . $lPath;
        $lImageFile = str_replace(array(
            '/',
            '\\',
            '//',
            '\\\\'
        ), DIRECTORY_SEPARATOR, $lImageFile);
        MLotzImageConverter::resizeImage($lImageFile, $lDestImageFile, $aMaxWidth, $aMaxHeight);
    }

    return $lThumbPath;
}



class MLotzImageConverter {
    /**
     * @param $aSourceFile
     * @param $aDestFile
     * @param $aNewWidth
     * @param $aNewHeight
     * @param bool $aCrop
     * @param string $aBackgroundColor
     * @return bool
     */
    public static function resizeImage($aSourceFile, $aDestFile, $aNewWidth, $aNewHeight, $aCrop = true, $aBackgroundColor = "FFFFFF") {
        $lMime = getimagesize($aSourceFile);

        if ($lMime['mime'] == 'image/png') {
            $lSrcImage = imagecreatefrompng($aSourceFile);
        }
        if ($lMime['mime'] == 'image/jpg' || $lMime['mime'] == 'image/jpeg' || $lMime['mime'] == 'image/pjpeg') {
            $lSrcImage = imagecreatefromjpeg($aSourceFile);
        }

        $lOldX = imageSX($lSrcImage);
        $lOldY = imageSY($lSrcImage);

        // Calc dividers
        $aspectForW = $aNewWidth / $lOldX;
        $aspectForH = $aNewHeight / $lOldY;
        $aspect = min($aspectForW, $aspectForH);

        $lThumbWidth = $lOldX * $aspect;
        $lThumbHeight = $lOldY * $aspect;

        if ($aCrop) {
            $lDestImage = ImageCreateTrueColor($lThumbWidth, $lThumbHeight);
            imagecopyresampled($lDestImage, $lSrcImage, 0, 0, 0, 0, $lThumbWidth, $lThumbHeight, $lOldX, $lOldY);
        } else {
            // No cropping Create image
            $lDestImage = ImageCreateTrueColor($aNewWidth, $aNewHeight);

            // Fill with background
            $hexcolor = str_split($aBackgroundColor, 2);

            // Convert HEX values to DECIMAL
            $lRed = hexdec("0x{$hexcolor[0]}");
            $lGreen = hexdec("0x{$hexcolor[1]}");
            $lBlue = hexdec("0x{$hexcolor[2]}");

            $lBackgroundColor = imagecolorallocate($lDestImage, $lRed, $lGreen, $lBlue);
            imagefilledrectangle($lDestImage, 0, 0, $aNewWidth, $aNewHeight, $lBackgroundColor);

            imagecopyresampled($lDestImage, $lSrcImage, ($aNewWidth - $lThumbWidth) / 2, ($aNewHeight - $lThumbHeight) / 2, 0, 0, $lThumbWidth, $lThumbHeight, $lOldX, $lOldY);
        }

        // New save location
        if ($lMime['mime'] == 'image/png') {
            $result = imagepng($lDestImage, $aDestFile, 8);
        }
        if ($lMime['mime'] == 'image/jpg' || $lMime['mime'] == 'image/jpeg' || $lMime['mime'] == 'image/pjpeg') {
            $result = imagejpeg($lDestImage, $aDestFile, 80);
        }

        imagedestroy($lDestImage);
        imagedestroy($lSrcImage);

        return $result;
    }
}
