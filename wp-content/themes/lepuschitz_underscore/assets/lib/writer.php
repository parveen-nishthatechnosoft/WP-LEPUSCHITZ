<?php

include_once('delete.php');

class LCatalogWriter {
    public static $IMAGE_SUBFOLDER = 'catalogimages/original/';

    /**
     * @var LMandator
     */
    public $Mandator;

    private $ProgressId = 0;

    public function __construct($aMandator) {
        $this->Mandator = $aMandator;
    }

    function DoProgress($aProgressHandler, $aMaximumNumber, $aCurrentNumber, $aMessage = "") {
        if ($aProgressHandler != null) {
            $this->ProgressId++;
            $lProgress = $aCurrentNumber / $aMaximumNumber * 100;
            call_user_func($aProgressHandler, $this->ProgressId, $aMessage, $lProgress);
        } else {
            echo("Step $aCurrentNumber/$aMaximumNumber: $aMessage<br>");
            ob_flush();
        }
    }

    /**
     * @param LCatalog $aCatalog
     */
    public function SaveCatalog(LCatalog $aCatalog, bool $aDeleteAll = false, $aProgressHandler = null) {
        // Get the numbers of actions to be done
        $lTotalNumberOfProducts = sizeof($aCatalog->Products->Products);
        $lTotalNumberOfTechnologies = sizeof($aCatalog->Technologies->Technologies);
        $lTotalNumberOfLabelings = sizeof($aCatalog->Labelings->Labels);
        $lTotalNumberOfLoops = $lTotalNumberOfProducts + $lTotalNumberOfTechnologies + $lTotalNumberOfLabelings;
        $lCurrentNumberOfLoops = 0;

        // Check if everything should be deleted before
        if ($aDeleteAll) {
            $this->DeleteAll($aCatalog, $aProgressHandler, false);
        }

        $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, 'Importing products');
        $lNumberOfProducts = 0;
        foreach ($aCatalog->Products->Products as $lProduct) {
            // Do progress
            $lNumberOfProducts++;
            $lCurrentNumberOfLoops++;
            $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, "Importing product $lProduct->ProductCode ($lProduct->Title)");

            set_time_limit(5);

            $lIsNewProduct = true;

            $args = array(
                'post_type' => 'product',
                'meta_key' => 'product_id',
                'meta_value' => $lProduct->ProductCode
            );

            $post_query = new WP_Query($args);

            while ($post_query->have_posts()) {
                $post_query->the_post();

                $lIsNewProduct = false;
                $productPostId = get_the_ID();
                break;
            }

            // Check if there is no colors
            if (sizeof($lProduct->Colors) == 0) {
                // Add dummy color
                $lProduct->AddColorWithImage(LColor::NOCOLOR);
            }

            // Check if there is a price
            if ((sizeof($lProduct->Prices) > 0) && ($lProduct->CategoryIdOrName != null)) {
                if ($lIsNewProduct) {
                    $this->CreateNewProduct($lProduct, $aCatalog);
                } else {
                    // Older imports may have omitted this link; it is required
                    // later to resolve the catalogue's technologies and pricing.
                    update_post_meta($productPostId, 'catalog_Id', $aCatalog->Id);
                    $lHashSum = (string)$lProduct->HashSum;
                    $currentHashSum = get_post_field('hash_sum', $productPostId);

                    if ($lHashSum != $currentHashSum) {
                        $this->UpdateProduct($productPostId, $lProduct, $aCatalog);
                    }
                }
            }
        }
        $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, "Finished importing $lNumberOfProducts products.");

        // Do technologies now
        $lTechnologiesCount = 0;
        $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, 'Importing technologies');
        foreach ($aCatalog->Technologies->Technologies as $lTechnology) {
            // Do progress
            $lTechnologiesCount++;
            $lCurrentNumberOfLoops++;
            $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, "Importing technology $lTechnology->Code ($lTechnology->Name)");

            set_time_limit(5);

            // Check if technology already exists
            $lIsNewTechnology = true;
            $args = array(
                'post_type' => 'technology',
                'meta_query' => array(
                    array(
                        'key' => 'code',
                        'value' => $lTechnology->Code
                    ),
                    array(
                        'key' => 'catalog_Id',
                        'value' => $aCatalog->Id,
                    )
                )
            );

            $post_query = new WP_Query($args);
            if ($post_query->have_posts()) {
                $post_query->the_post();

                $lIsNewTechnology = false;
                $techPostId = get_the_ID();
            }

            if ($lIsNewTechnology) {
                $this->CreateNewTechnology($lTechnology, $aCatalog);
            } else {
                // Keep existing technologies associated with their catalogue so
                // AddPositions can resolve and attach them to each product.
                update_post_meta($techPostId, 'catalog_Id', $aCatalog->Id);
                $lHashSum = (string)$lTechnology->HashSum;
                $currentHashSum = get_post_field('hash_sum', $techPostId);

                if ($lHashSum != $currentHashSum)
                    $this->UpdateTechnology($techPostId, $lTechnology, $aCatalog);
            }
        }
        $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, "Finished importing $lTechnologiesCount technologies.");

        // Do the labelings
        $lLabelingsCount = 0;
        $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, 'Importing labelings');
        foreach ($aCatalog->Labelings->Labels as $lLabeling) {
            // Do progress
            $lLabelingsCount++;
            $lCurrentNumberOfLoops++;
            $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, "Importing labeling $lLabeling->ItemNumber");

            set_time_limit(5);

            $lItemNumber = $lLabeling->ItemNumber;

            $this->AddPositions($aCatalog, $lLabeling, $lItemNumber);
        }
        $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lCurrentNumberOfLoops, "Finished importing $lLabelingsCount labelings.");

        $this->DoProgress($aProgressHandler, $lTotalNumberOfLoops, $lTotalNumberOfLoops, "TERMINATE");
    }

    private function AddPositions(LCatalog $aCatalog, LLabeling $aLabeling, string $aItemNumber) {
        set_time_limit(5);

        // Run through all products with the current item number
        $lArgs = array(
            'post_type' => 'product',
            'meta_key' => 'product_id',
            'meta_value' => $aItemNumber
        );

        $lProductsQuery = new WP_Query($lArgs);

        while ($lProductsQuery->have_posts()) {
            $lProductsQuery->the_post();
            $lProductId = get_the_ID();

            // Delete the old positions if there
            $lOldPositions = get_field('positions', $lProductId);
            if ($lOldPositions) {
                for ($i = count($lOldPositions); $i > 0; $i--) {
                    delete_row("positions", $i, $lProductId);
                }
            }

            foreach ($aLabeling->Positions as $aPosition) {
                $lPositionRow = array(
                    'serial' => $aPosition->Serial,
                    'position_name' => $aPosition->PositionName,
                    'position_image' => $aPosition->PositionImage
                );
                $lNewPosition = add_row('positions', $lPositionRow, $lProductId);

                foreach ($aPosition->Technologies as $lTechnology) {
                    $lTechCode = $lTechnology->Code;

                    // Check if anda
                    $lTechArgs = array(
                        'post_type' => 'technology',
                        'post_status' => 'publish',
                        'meta_query' => array(
                            array(
                                'key' => 'code',
                                'value' => $lTechCode
                            ),
                            array(
                                'key' => 'catalog_Id',
                                'value' => $aCatalog->Id
                            )
                        )
                    );
                    $lTechQuery = new WP_Query($lTechArgs);

                    // Is there a technology there
                    $lTechnologyPost = null;
                    if ($lTechQuery->have_posts()) {
                        // Get the first one
                        $lTechnologyPost = $lTechQuery->next_post();
                    } else {
                        // No technology, query for variant
                        if ($this->Mandator->Name == LMandator::ANDACOOL) {
                            $lTechArgs = array(
                                'post_type' => 'technology',
                                'post_status' => 'publish',
                                'meta_query' => array(
                                    array(
                                        'key' => 'code',
                                        'value' => '^' . $lTechCode,
                                        'compare' => 'RLIKE'
                                    ),
                                    array(
                                        'key' => 'catalog_Id',
                                        'value' => $aCatalog->Id
                                    ),
                                    array(
                                        'key' => 'size_from',
                                        'value' => $lTechnology->MaxSize,
                                        'compare' => '>='
                                    ),
                                    array(
                                        'key' => 'size_to',
                                        'value' => $lTechnology->MaxSize,
                                        'compare' => '<='
                                    )
                                )
                            );
                        } else {
                            $lTechArgs = array(
                                'post_type' => 'technology',
                                'post_status' => 'publish',
                                'meta_query' => array(
                                    array(
                                        'key' => 'code',
                                        'value' => '^' . $lTechCode,
                                        'compare' => 'RLIKE'
                                    ),
                                    array(
                                        'key' => 'catalog_Id',
                                        'value' => $aCatalog->Id
                                    )
                                )
                            );
                        }

                        $lTechQuery = new WP_Query($lTechArgs);
                        while ($lTechQuery->have_posts()) {
                            $lTechnologyPost = $lTechQuery->next_post();
                        }
                    }

                    if ($lTechnologyPost != null) {
                        // Check if technologyname is empty
                        if (empty($lTechnology->Name)) {
                            // Take it from the technology itself
                            $lTechnology->Name = get_field('name', $lTechnologyPost->ID);
                        }

                        $lSubRow = array(
                            'technology_code' => $lTechnologyPost->ID,
                            'technology_name' => $lTechnology->Name,
                            'max_colors' => $lTechnology->MaxColors,
                            'max_size' => $lTechnology->MaxSize
                        );

                        add_sub_row(array(
                            'positions',
                            $lNewPosition,
                            'technologies'
                        ), $lSubRow);
                    }
                }
            }
        }
    }

    /**
     * @param LTechnologyCosts $aTechnology
     * @param LCatalog $aCatalog
     */
    private function CreateNewTechnology(LTechnologyCosts $aTechnology, LCatalog $aCatalog) {
        // Increase time limit
        set_time_limit(10);

        $args_technology = array(
            'post_title' => $aTechnology->Code,
            'post_name' => sanitize_title($aTechnology->Code),
            'post_content' => null,
            'post_status' => 'publish',
            'post_type' => 'technology'
        );

        $id = wp_insert_post($args_technology);

        update_post_meta($id, 'hash_sum', $aTechnology->HashSum);
        update_post_meta($id, 'code', $aTechnology->Code);
        update_post_meta($id, 'name', $aTechnology->Name);
        update_post_meta($id, 'size_from', $aTechnology->SizeFrom);
        update_post_meta($id, 'size_to', $aTechnology->SizeTo);
        update_post_meta($id, 'preprintcosts', $aTechnology->CostsPerColor);
        update_post_meta($id, 'preprintsetup', $aTechnology->SetupCosts);
        update_post_meta($id, 'catalog_Id', $aCatalog->Id);

        foreach ($aTechnology->Ranges->Ranges as $lRange) {
            $row = array(
                'number_of_colors' => $lRange->NumberOfColors,
                'quantity_from' => $lRange->QuantityFrom,
                'quantity_to' => $lRange->QuantityTo,
                'unit_price' => $lRange->UnitPrice,
                'setup_cost' => $lRange->SetupPricePerColor
            );
            add_row('ranges', $row, $id);
        }
    }

    private function UpdateTechnology($post_id, LTechnologyCosts $aTechnology, LCatalog $aCatalog) {
        // Increase time limit
        set_time_limit(10);

        update_post_meta($post_id, 'hash_sum', $aTechnology->HashSum);
        update_post_meta($post_id, 'title', (string)$aTechnology->Code);
        update_post_meta($post_id, 'name', sanitize_title((string)$aTechnology->Code));
        update_post_meta($post_id, 'code', (string)$aTechnology->Code);
        update_post_meta($post_id, 'name', (string)$aTechnology->Name);
        update_post_meta($post_id, 'size_from', (string)$aTechnology->SizeFrom);
        update_post_meta($post_id, 'size_to', (string)$aTechnology->SizeTo);
        update_post_meta($post_id, 'mandatorid', $this->Mandator->Id);
        update_post_meta($post_id, 'catalog_Id', $aCatalog->Id);

        $oldRanges = get_field('ranges', $post_id);
        if ($oldRanges) {
            for ($i = count($oldRanges); $i > 0; $i--) {
                delete_row('ranges', $i, $post_id);
            }
        }

        foreach ($aTechnology->Ranges->Ranges as $aRange) {
            $row = array(
                'number_of_colors' => (string)$aRange->NumberOfColors,
                'quantity_from' => (string)$aRange->QuantityFrom,
                'quantity_to' => (string)$aRange->QuantityTo,
                'unit_price' => (string)$aRange->UnitPrice,
                'setup_cost' => (string)$aRange->SetupPricePerColor
            );
            add_row('ranges', $row, $post_id);
        }
    }

    /**
     * @param LProduct $aProduct
     * @param LCatalog $aCatalog
     * If new product, this function creates a post with the product data
     */
    private function CreateNewProduct(LProduct $aProduct, LCatalog $aCatalog) {
        // Increase time limit
        set_time_limit(10);

        // Do the product_group by mapping. If no mapping, ignore the product
        $lMappedGroupId = $aCatalog->Mapping->GetMappedGroup($aProduct);
        if (!$lMappedGroupId) {
            return false;
        }

        // Group exists, so create the new product
        $args = array(
            'post_title' => $aProduct->Title,
            'post_name' => sanitize_title($aProduct->Title),
            'post_content' => null,
            'post_status' => 'publish',
            'post_type' => 'product'
        );

        $post_id = wp_insert_post($args);

        update_post_meta($post_id, 'catalog_Id', $aCatalog->Id);
        update_post_meta($post_id, 'parent', $lMappedGroupId);
        update_post_meta($post_id, 'hash_sum', $aProduct->HashSum);
        update_post_meta($post_id, 'product_id', $aProduct->ProductCode);
        update_post_meta($post_id, 'isActive', true);
        update_post_meta($post_id, 'has_deal', 'No');
        if ($aCatalog->Mandator->Type === LMandator::LSHOP_TYPE) {
            update_post_meta($post_id, 'lshop_catalog_number', (string)$aProduct->SupplierProductNumber);
        }
        update_post_meta($post_id, 'source_image', (string)$this->DownloadFile($aProduct->ImageUrl, $post_id));
        update_post_meta($post_id, 'description', (string)$aProduct->Description);
        update_post_meta($post_id, 'starting_price', (string)$aProduct->GetLowestPrice());
        update_post_meta($post_id, 'minimum_order_quantity', (string)$aProduct->MimimumQuantity);

        foreach ($aProduct->Prices as $lPrice) {
            // Clear out max int
            if ($lPrice->To == PHP_INT_MAX) {
                $lPrice->To = '';
            }

            $row_quantity = array(
                'quantity_from' => (string)$lPrice->From,
                'quantity_to' => (string)$lPrice->To,
                'price' => (string)$lPrice->PricePerPiece
            );
            add_row('quantities_and_prices', $row_quantity, $post_id);
        }

        foreach ($aProduct->Colors as $Color) {
            $row_variant = array(
                'color_productcode' => (string)$Color->ProductCode,
                'color_name' => (string)$Color->ColorName,
                'color_code' => (string)$Color->ColorCode,
                'color_image' => $this->DownloadFile((string)$Color->ImageUrl, $post_id)
            );

            if ($Color instanceof LBiColor) {
                $row_variant = array(
                    'color_productcode' => (string)$Color->ProductCode,
                    'color_name' => (string)$Color->ColorName,
                    'color_code' => (string)$Color->ColorCode,
                    'color2_name' => (string)$Color->Color2Name,
                    'color2_code' => (string)$Color->Color2Code,
                    'color_image' => $this->DownloadFile((string)$Color->ImageUrl, $post_id)
                );
            }

            add_row('variants', $row_variant, $post_id);
        }
    }

    /**
     * @param $post_id
     * @param $aProduct
     * If already existing product, this function updates the product data
     */
    private function UpdateProduct($post_id, LProduct $aProduct, LCatalog $aCatalog) {
        // Increase time limit
        set_time_limit(15);

        update_post_meta($post_id, 'hash_sum', $aProduct->HashSum);
        update_post_meta($post_id, 'product_id', $aProduct->ProductCode);
        update_post_meta($post_id, 'isActive', true);
        update_post_meta($post_id, 'has_deal', 'No');
        update_post_meta($post_id, 'catalog_Id', $aCatalog->Id);
        if ($aCatalog->Mandator->Type === LMandator::LSHOP_TYPE) {
            update_post_meta($post_id, 'lshop_catalog_number', (string)$aProduct->SupplierProductNumber);
        }

        $parentId = $aProduct->CategoryIdOrName;

        $args_parent = array(
            'post_type' => 'product_subcategory',
            'meta_key' => 'subcategory_id',
            'meta_value' => $parentId
        );

        $parent_query = new WP_Query($args_parent);

        while ($parent_query->have_posts()) {
            $parent_query->the_post();

            update_post_meta($post_id, 'parent', get_the_ID());
            break;
        }
        LDeleteImages::DeleteImage(get_post_field('source_image', $post_id));
        update_post_meta($post_id, 'source_image', (string)$this->DownloadFile($aProduct->ImageUrl, $post_id));

        update_post_meta($post_id, 'description', (string)$aProduct->Description[0]);
        update_post_meta($post_id, 'starting_price', (string)$aProduct->GetLowestPrice());
        update_post_meta($post_id, 'minimum_order_quantity', (string)$aProduct->MimimumQuantity);

        $lOldQuantities = get_field('quantities_and_prices', $post_id);
        if ($lOldQuantities) {
            for ($i = count($lOldQuantities); $i > 0; $i--) {
                delete_row('quantities_and_prices', $i, $post_id);
            }
        }

        while (have_rows('variants', $post_id)) {
            the_row();

            LDeleteImages::DeleteImage(get_sub_field('color_image'));
        }

        $lOldColors = get_field('variants', $post_id);
        if ($lOldColors) {
            for ($i = count($lOldColors); $i > 0; $i--) {
                delete_row('variants', $i, $post_id);
            }
        }

        foreach ($aProduct->Prices as $lPrice) {
            // Clear out max int
            if ($lPrice->To == PHP_INT_MAX) {
                $lPrice->To = '';
            }

            $row_quantity = array(
                'quantity_from' => (string)$lPrice->From,
                'quantity_to' => (string)$lPrice->To,
                'price' => (string)$lPrice->PricePerPiece
            );
            add_row('quantities_and_prices', $row_quantity, $post_id);
        }

        foreach ($aProduct->Colors as $Color) {
            $row_variant = array(
                'color_name' => (string)$Color->ColorName,
                'color_code' => (string)$Color->ColorCode,
                'color_image' => (string)$this->DownloadFile($Color->ImageUrl, $post_id)
            );
            add_row('variants', $row_variant, $post_id);
        }
    }

    /**
     * @param string $aFileNameOrUrl
     * @param $aPostId
     * @return false|string Returns the file name or false if it fails
     */
    private function DownloadFile(string $aFileNameOrUrl, $aPostId) {
        if (!empty($aFileNameOrUrl)) {
            // Check if file is already in the filesystem
            if (file_exists($aFileNameOrUrl)) {
                return get_site_url(null, $aFileNameOrUrl);
            }

            if (file_exists(ABSPATH.$aFileNameOrUrl)) {
                return get_site_url(null, $aFileNameOrUrl);
            }

            // Check if folder exists
            $lUploadDir = wp_upload_dir();
            $lDestinationFolder = trailingslashit($lUploadDir['basedir']) . LCatalogWriter::$IMAGE_SUBFOLDER;
            if (!file_exists($lDestinationFolder)) {
                mkdir($lDestinationFolder, 0755, true);
            }

            // Create the destination file name
            if (strpos($aFileNameOrUrl, 'http') == 0) {
                $lPath = parse_url($aFileNameOrUrl, PHP_URL_PATH);
                $lDestinationFileName = basename($lPath);
            } else {
                $lDestinationFileName = uniqid($aPostId . '_') . '.jpg';
            }
            $lDestinationFile = $lDestinationFolder . $lDestinationFileName;
            while (file_exists($lDestinationFile)) {
                $lDestinationFile = $lDestinationFolder . uniqid($aPostId . '_');
            }

            $lImageFileContent = file_get_contents($aFileNameOrUrl);
            if (!empty($lImageFileContent)) {
                $lImage = imagecreatefromstring($lImageFileContent);
                if (imagejpeg($lImage, $lDestinationFile)) {
                    // Return the file name
                    return (trailingslashit($lUploadDir['baseurl']) . LCatalogWriter::$IMAGE_SUBFOLDER . $lDestinationFileName);
                }
            }
        }

        return false;
    }

    /**
     * Delete everything for the given catalog
     *
     * @param LCatalog $aCatalog
     */
    private function DeleteAll(LCatalog $aCatalog, $aProgressHandler, bool $aUseWordpress = true) {
        if (!$aUseWordpress) {
            global $wpdb;

            // Delete posts of type technology and products based on catalog
            $lSql = 'DELETE wp FROM wp_posts wp LEFT JOIN wp_postmeta pm ON pm.post_id = wp.ID WHERE pm.meta_key = "catalog_Id" AND pm.meta_value = ' . $aCatalog->Id . ' AND wp.post_type IN ("technology", "product")';
            $wpdb->query($lSql);

            // Delete orphaned meta infos
            $LSql = 'DELETE pm FROM wp_postmeta pm LEFT JOIN wp_posts wp ON wp.ID = pm.post_id WHERE wp.ID IS NULL';
            $wpdb->query($lSql);

        } else {
            // Get the ids
            $lTechnologyPostIds = get_posts(array(
                'fields' => 'ids',
                'posts_per_page' => -1,
                'post_type' => 'technology',
                'meta_key' => 'catalog_Id',
                'meta_value' => $aCatalog->Id
            ));

            $lProductPostIds = get_posts(array(
                'fields' => 'ids',
                'posts_per_page' => -1,
                'post_type' => 'product',
                'meta_key' => 'catalog_Id',
                'meta_value' => $aCatalog->Id
            ));

            $lNumberOfLoops = sizeof($lTechnologyPostIds) + sizeof($lProductPostIds);
            $lCurrentLoops = 0;

            // Delete technologies
            foreach ($lTechnologyPostIds as $lPostId) {
                set_time_limit(5);

                // Delete the technology post
                wp_delete_post($lPostId, true);

                $lCurrentLoops++;
                $this->DoProgress($aProgressHandler, $lNumberOfLoops, $lCurrentLoops, "Deleting technologies");
            }

            // Delete products
            foreach ($lProductPostIds as $lPostId) {
                set_time_limit(5);

                // Get the images and delete them
                LDeleteImages::DeleteImage(get_post_field('source_image', $lPostId));

                if (have_rows('variants', $lPostId)) {
                    while (have_rows('variants', $lPostId)) {
                        the_row();

                        LDeleteImages::DeleteImage(get_sub_field('color_image'));
                    }
                }

                // Delete the product post
                wp_delete_post($lPostId, true);

                $lCurrentLoops++;
                $this->DoProgress($aProgressHandler, $lNumberOfLoops, $lCurrentLoops, "Deleting products");
            }
        }
    }
}
