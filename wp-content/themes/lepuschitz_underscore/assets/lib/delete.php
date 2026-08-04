<?php
include_once('writer.php');

class LDeleteImages {
    public static function DeleteAllImages() {
        $lUploadDir = wp_upload_dir();
        $lDestinationFolder = trailingslashit($lUploadDir['basedir']) . LCatalogWriter::$IMAGE_SUBFOLDER;

        if (is_dir($lDestinationFolder)) {
            if ($dh = opendir($lDestinationFolder)) {
                while (($image = readdir($dh)) !== false){
                    $file = $lDestinationFolder . $image;
                    wp_delete_file($file);
                }
                closedir($dh);
            }
        }
    }

    public static function DeleteImage(string $image) {
        $lUploadDir = wp_upload_dir();
        $lDestinationFolder = trailingslashit($lUploadDir['basedir']) . LCatalogWriter::$IMAGE_SUBFOLDER;

        $lastSlashPosition = strrpos($image, '/') + 1;
        $image = substr($image, $lastSlashPosition);
        $file = $lDestinationFolder . $image;

        if (is_dir($lDestinationFolder)) {
            if (is_file($file)) {
                wp_delete_file($file);
            }
        }
    }
}
