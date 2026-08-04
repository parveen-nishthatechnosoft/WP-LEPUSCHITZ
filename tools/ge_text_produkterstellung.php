<?php
require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/tools/parsing_xlsx.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/price.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/color.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/product.php');

$IMAGE_SUBFOLDER = 'catalogimages/original/';

function DownloadFile(string $aFileNameUrUrl, $aPostId) {
    global $IMAGE_SUBFOLDER;

    if (!empty($aFileNameUrUrl)) {
        // Check if folder exists
        $lUploadDir = wp_upload_dir();
        $lDestinationFolder = trailingslashit($lUploadDir['basedir']) . $IMAGE_SUBFOLDER;
        if (!file_exists($lDestinationFolder)) {
            mkdir($lDestinationFolder, 0755, true);
        }

        // Create the destination file name
        $lDestinationFileName = uniqid($aPostId . '_') . '.jpg';
        $lDestinationFile = $lDestinationFolder . $lDestinationFileName;
        while (file_exists($lDestinationFile)) {
            $lDestinationFile = $lDestinationFolder . uniqid($aPostId . '_');
        }

        $lImage = imagecreatefromstring(file_get_contents($aFileNameUrUrl));
        if (imagejpeg($lImage, $lDestinationFile)) {
            // Return the file name
            return (trailingslashit($lUploadDir['baseurl']) . $IMAGE_SUBFOLDER . $lDestinationFileName);
        }
    }

    return false;
}

function CreateNewProduct($aProduct) {
    // Increase time limit
    set_time_limit(10);

    $args = array(
        'post_title' => $aProduct->Title,
        'post_name' => sanitize_title($aProduct->Title),
        'post_content' => null,
        'post_status' => 'publish',
        'post_type' => 'product'
    );

    $post_id = wp_insert_post($args);

    update_post_meta($post_id, 'hash_sum', $aProduct->HashSum);
    update_post_meta( $post_id, 'product_id', $aProduct->ProductCode);
    update_post_meta( $post_id, 'isActive', true);
    update_post_meta( $post_id, 'has_deal', 'No');

    $parentId = $aProduct->CategoryId;
    $args_parent = array(
        'post_type' => 'product_subcategory'
    );
    $parent_query = new WP_Query($args_parent);
    while($parent_query->have_posts()){
        $parent_query->the_post();
        $currentParentId = get_field('subcategory_id');

        if ($currentParentId == $parentId) {
            update_post_meta( $post_id, 'parent', get_the_ID());
            break;
        }
    }

    update_post_meta( $post_id, 'source_image', (string) DownloadFile($aProduct->ImageUrl, $post_id));
    update_post_meta( $post_id, 'description', (string) $aProduct->Description);
    update_post_meta( $post_id, 'starting_price', (string) $aProduct->Prices[0]->PricePerPiece);

    foreach ($aProduct->Prices as $Price) {
        $row_quantity = array(
            'quantity_from' => (string) $Price->From,
            'quantity_to' => (string) $Price->To,
            'price' => (string) $Price->PricePerPiece
        );
        add_row('quantities_and_prices', $row_quantity, $post_id);
    }

    foreach ($aProduct->Colors as $Color) {
        $row_variant = array(
            'color_name' => (string) $Color->ColorName,
            'color_code' => (string) $Color->ColorCode,
            'color_image' => (string) DownloadFile($Color->ImageUrl, $post_id)
        );
        add_row('variants', $row_variant, $post_id);
    }
}

$lProducts = [];

foreach (array_slice($lRows, 1) as $lRow) {

    //print_r($lRow[1]);
    //print_r($lProducts[$lRow[1]]);

    if (isset($lProducts[$lRow[1]])) {
        $imgUlrBase = substr($lRow[74], 0, -4);

        $rangeBottom = floor($lRow[1] / 1000) * 1000;
        $rangeTop = $rangeBottom + 999;

        $fillerBottom = '';
        $fillerTop = '';
        $fillerID = '';

        for ($i = strlen($rangeBottom); $i < 6; $i++) {
            $fillerBottom = $fillerBottom . "0";
        }

        for ($i = strlen($rangeTop); $i < 6; $i++) {
            $fillerTop = $fillerTop . "0";
        }

        for ($i = strlen($lRow[1]); $i < 6; $i++) {
            $fillerID = $fillerID . "0";
        }

        $imgUrl = "https://cdn.impression-catalogue.com/files/Producten/" . $fillerBottom . $rangeBottom . "-" . $fillerTop . $rangeTop . "/" . $fillerID . $lRow[1] . "/Productfotos/" . $imgUlrBase . ".tif//size_500x500.jpg";
        if (!file_get_contents($imgUrl)){
            $imgUrl = "https://cdn.impression-catalogue.com/files/Producten/" . $fillerBottom . $rangeBottom . "-" . $fillerTop . $rangeTop . "/" . $fillerID . $lRow[1] . "/Productfotos/" . $imgUlrBase . ".jpg//size_500x500.jpg";
        }

        $lProducts[$lRow[1]]->AddColorWithImage($lRow[3], $imgUrl);

    } else {
        $lProduct = new LProduct($lRow[8]);

        $lProduct->HashSum = md5($lProduct);
        $lProduct->ProductCode = "GE_" . $lRow[1];
        $lProduct->Description = $lRow[7];
        $lProduct->CategoryId = "4257";

        $imgUlrBase = substr($lRow[74], 0, -4);

        $rangeBottom = floor($lRow[1] / 1000) * 1000;
        $rangeTop = $rangeBottom + 999;

        $fillerBottom = '';
        $fillerTop = '';
        $fillerID = '';

        for ($i = strlen($rangeBottom); $i < 6; $i++) {
            $fillerBottom = $fillerBottom . "0";
        }

        for ($i = strlen($rangeTop); $i < 6; $i++) {
            $fillerTop = $fillerTop . "0";
        }

        for ($i = strlen($lRow[1]); $i < 6; $i++) {
            $fillerID = $fillerID . "0";
        }

        $imgUrl = "https://cdn.impression-catalogue.com/files/Producten/" . $fillerBottom . $rangeBottom . "-" . $fillerTop . $rangeTop . "/" . $fillerID . $lRow[1] . "/Productfotos/" . $imgUlrBase . ".tif//size_500x500.jpg";
        if (!file_get_contents($imgUrl)){
            $imgUrl = "https://cdn.impression-catalogue.com/files/Producten/" . $fillerBottom . $rangeBottom . "-" . $fillerTop . $rangeTop . "/" . $fillerID . $lRow[1] . "/Productfotos/" . $imgUlrBase . ".jpg//size_500x500.jpg";
        }

        $lProduct->ImageUrl = $imgUrl;

        $lProduct->AddColorWithImage($lRow[3], $imgUrl);
        $lProduct->AddPrice(round($lRow[10], 2), 1, $lRow[9] - 1);
        $lProduct->AddPrice(round($lRow[13], 2), $lRow[9], $lRow[12] - 1);
        $lProduct->AddPrice(round($lRow[16], 2), $lRow[12], $lRow[15] - 1);
        $lProduct->AddPrice(round($lRow[19], 2), $lRow[15], $lRow[18] - 1);

        $lProducts[$lRow[1]] = $lProduct;
    }
}

foreach ($lProducts as $lProduct) {

    $query = new WP_Query(array(
        'post_type' => 'product',
        'meta_key' => 'product_id',
        'meta_value' => $lProduct->ProductCode
    ));

    if ($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();

            if (get_field('hash_sum') != $lProduct->HashSum) {
                CreateNewProduct($lProduct);
            }
        }
    }
}

print_r($lProducts);

/*$ID = "647";
$imgName = "000647-001999999-2D000-FRT-PRO01-FAL.jpg";
$imgUlrBase = substr($imgName, 0, -4);

$rangeBottom = floor($ID / 1000) * 1000;
$rangeTop = $rangeBottom + 999;

$fillerBottom = '';
$fillerTop = '';
$fillerID = '';

for ($i = strlen($rangeBottom); $i < 6; $i++) {
    $fillerBottom = $fillerBottom . "0";
}

for ($i = strlen($rangeTop); $i < 6; $i++) {
    $fillerTop = $fillerTop . "0";
}

for ($i = strlen($ID); $i < 6; $i++) {
    $fillerID = $fillerID . "0";
}

$imgUrl = "https://cdn.impression-catalogue.com/files/Producten/" . $fillerBottom . $rangeBottom . "-" . $fillerTop . $rangeTop . "/" . $fillerID . $ID . "/Productfotos/" . $imgUlrBase . ".tif//size_500x500.jpg";

$post_id = wp_insert_post(array(
    'post_title' => "Test Giving Europe",
    'post_name' => "test-giving-europe",
    'post_content' => null,
    'post_status' => 'publish',
    'post_type' => 'product'
));

update_post_meta($post_id, 'hash_sum', "123123123123");
update_post_meta( $post_id, 'product_id', "GE_" . $ID);
update_post_meta( $post_id, 'isActive', true);
update_post_meta( $post_id, 'has_deal', 'No');
update_post_meta( $post_id, 'source_image', (string) DownloadFile($imgUrl, $post_id));*/
