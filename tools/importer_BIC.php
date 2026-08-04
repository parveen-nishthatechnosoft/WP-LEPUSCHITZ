<?php
//echo "Hi";

require_once('/var/virtual_www/lepuschitzPROD/wp-load.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-includes/post.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/vendor/autoload.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/price.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/color.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/product.php');
require_once('/var/virtual_www/lepuschitzPROD/wp-content/themes/lepuschitz_underscore/assets/lib/writer.php');

use PhpOffice\PhpSpreadsheet;

class LPosition {
    public $Name;
    public $Technologies = [];
}

class LTechnology {
    public $Code;
    public $Name;
    public $Colors;
}

$lRowsProducts = [];
$lRowsColors = [];
$lRowImages = [];
$lRowPrintings = [];

$file = '/var/virtual_www/lepuschitzPROD/tools/testdata/bic.xlsx';

$lXlsxReader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();

$_bDoImport = true;
if ($lXlsxReader->canRead($file))
{
    // File readable, so try to parse it

    // Increase the memory
    ini_set('memory_limit','2048M');

    // Open the spreadsheet
    $lSpreadsheet = $lXlsxReader->load($file);
    //print_r($lSpreadsheet);
    $lAllSheets = $lSpreadsheet->getSheetNames();

    //Artikelpreisliste
    $lSheetDaten1 = $lSpreadsheet->getSheetByName('Product');
    foreach ($lSheetDaten1->getRowIterator() as $row)
    {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            array_push($lCells, $cells);
        }
		
        // echo ('$lCells[4]: ' .$lCells[4] .' <br />');
        $name = explode(" ", $lCells[4]);
		
		// Filter Products based on Name;
		// Every other product is hidden in Excel
        if ($name[0] == "name" )
		{}
        else if(
        	( array_key_exists(1, $name ) ) &&
				($name[1] == "J23" ||
				$name[1] == "J25" ||
				$name[1] == "J26" ||
				$name[1] == "J38" ||
				$name[1] == "J39")
			)
		{
            $lRowsProducts[] = $lCells;
            echo ('Added ProductCode: ' . $lCells[2] . '<br />');
            var_dump( $lCells );
		}
    }
    echo ('Count: $lRowsProducts - '. count($lRowsProducts) .'<br />');
    //print_r($lRowsProducts);
	
	
    $lSheetDaten2 = $lSpreadsheet->getSheetByName('Components');
    foreach ($lSheetDaten2->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            array_push($lCells, $cells);
        }

        $productExists = array_search($lCells[2], array_column($lRowsProducts, '2'));

        if ($productExists)
            $lRowsColors[] = $lCells;
    }
    //print_r($lRowsColors);
	
	// Get Images
    $lSheetDaten3 = $lSpreadsheet->getSheetByName('Images');

    foreach ($lSheetDaten3->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            array_push($lCells, $cells);
        }

        $productExists = array_search($lCells[2], array_column($lRowsProducts, '2'));

        if ($productExists) {
            if (isset($lRowImages[$lCells[2]])){

                if ($lCells[3]) {

                    if (!isset($lRowImages[$lCells[2]][$lCells[3]])) {
                        $lRowImages[$lCells[2]][$lCells[3]] = $lCells;
                    }

                }

            } else {
                $lRowImages[$lCells[2]] = [];

                if ($lCells[3]) {
                    $lRowImages[$lCells[2]][$lCells[3]] = $lCells;
                }
            }
        }
    }
    //print_r($lRowImages);
	
	// Technologies
    $_Technologies = $lSpreadsheet->getSheetByName('Imprint Methods');
    foreach ($_Technologies->getRowIterator() as $row) {
        $lCells = array();
        foreach ($row->getCellIterator() as $cell) {
            $cells = [];
            $cells = $cell->getValue();
            array_push($lCells, $cells);
        }
		// echo ( 'Looking for: ' .$lCells[2] .'<br />');
        $productExists = array_search($lCells[2], array_column($lRowsProducts, '2'));
		//var_dump( $productExists );
		echo ('<br />');
        if ($productExists !== false ) {
        	echo ( '$productExists: ' .$lCells[2] .'<br />');
            if (isset($lRowPrintings[$lCells[2]])){

                if ($lCells[8]) {
                    if (!isset($lRowPrintings[$lCells[2]][$lCells[8]])) {
                        $lRowPrintings[$lCells[2]][$lCells[8]] = $lCells;
                    }
                }
            } else {
                $lRowPrintings[$lCells[2]] = [];
                if ($lCells[8]) {
                    $lRowPrintings[$lCells[2]][$lCells[8]] = $lCells;
                }
            }
        } else
		{

		}
    }
    //print_r($lRowPrintings[array_key_first($lRowPrintings)]);
	
	// free up Memory
	unset($lXlsxReader);
}
else
{
	$_bDoImport = false;
}

if ( $_bDoImport )
{
	$lProducts = [];
	
	foreach ( $lRowsProducts as $lRow) {
		
		$lProduct = new LProduct($lRow[4]);
		$lProduct->Positions = [];
	
		$lProduct->HashSum = md5(json_encode($lProduct));
		$lProduct->ProductCode = "BC_" . $lRow[2];
		$lProduct->Description = $lRow[5];
	
		$imgUrl = $lRow[19];
		$lProduct->ImageUrl = $imgUrl;
	
		if ($lRow[33])
			$lProduct->AddPrice(round($lRow[33], 2), $lRow[31], $lRow[32]);
		if ($lRow[36])
			$lProduct->AddPrice(round($lRow[36], 2), $lRow[34], $lRow[35]);
		if ($lRow[39])
			$lProduct->AddPrice(round($lRow[39], 2), $lRow[37], $lRow[38]);
		if ($lRow[42])
			$lProduct->AddPrice(round($lRow[42], 2), $lRow[40], $lRow[41]);
		if ($lRow[45])
			$lProduct->AddPrice(round($lRow[45], 2), $lRow[43], $lRow[44]);
		if ($lRow[48])
			$lProduct->AddPrice(round($lRow[48], 2), $lRow[46], $lRow[47]);
		if ($lRow[51])
			$lProduct->AddPrice(round($lRow[51], 2), $lRow[49], $lRow[50]);
		if ($lRow[54])
			$lProduct->AddPrice(round($lRow[54], 2), $lRow[52], $lRow[53]);
		if ($lRow[57])
			$lProduct->AddPrice(round($lRow[57], 2), $lRow[55], $lRow[56]);
		if ($lRow[60])
			$lProduct->AddPrice(round($lRow[60], 2), $lRow[58], $lRow[59]);
	
		$lProducts[$lRow[2]] = $lProduct;
	}
	
	foreach ($lRowsColors as $lRow) {
	
		if (isset($lProducts[$lRow[2]])) {
			$lProducts[$lRow[2]]->Colors[$lRow[4]] = array(
				'ColorName' => $lRow[14],
				'ColorCode' => "#" . $lRow[15],
				'ImageUrl' => $lRowImages[$lRow[2]][$lRow[4]][13]
			);
		}
	}
	
	echo ('Technologies <br />');
	foreach ($lRowPrintings as $lRow) {
		foreach ($lRow as $Position) {
			
			echo ('looking for Product: '. $Position[2] . '<br />' );
			// Check if Related Product exists, via ProductCode
			if (isset($lProducts[$Position[2]]))
			{
				echo ('Product exists: '. $Position[2] . '<br />' );
				
				$name = explode(" ", $Position[7])[0] . "_" . explode(" ", $Position[7])[1];
				$techCode = substr($Position[9], 0, 1) . $Position[11];
				
				echo ( '$techCode: '.$techCode.'<br /> Name: '.$name. '<br />');
				
				// Check if Tech. is already there ?
				if (!isset($lProducts[$Position[2]]->Positions[$name]))
				{
					echo('TEch is not set for Product <br />');
					if ($Position[15] == "calculatedPrice"){
	
						$lProducts[$Position[2]]->Positions[$name] = array(
							'Name' => $Position[7],
							'Technologies' => []
						);
	
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode] = array(
							'Code' => substr($Position[9], 0, 1) . $Position[11],
							'Name' => $Position[9],
							'Colors' => $Position[11],
							'Ranges' => []
						);
	
						if ($Position[19]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[17],
								'To' => $Position[18],
								'Price' => round($Position[19], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[22]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[20],
								'To' => $Position[21],
								'Price' => round($Position[22], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[25]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[23],
								'To' => $Position[24],
								'Price' => round($Position[25], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[28]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[26],
								'To' => $Position[27],
								'Price' => round($Position[28], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[31]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[29],
								'To' => $Position[30],
								'Price' => round($Position[31], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[34]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[32],
								'To' => $Position[33],
								'Price' => round($Position[34], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[37]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[35],
								'To' => $Position[36],
								'Price' => round($Position[37], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[40]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[38],
								'To' => $Position[39],
								'Price' => round($Position[40], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[43]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[41],
								'To' => $Position[42],
								'Price' => round($Position[43], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
						if ($Position[46]) {
							$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
								'From' => $Position[44],
								'To' => $Position[45],
								'Price' => round($Position[46], 2),
								'Colors' => $Position[11],
								'Setup' => 25
							);
						}
	
					}
	
				} else {
	
					$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode] = array(
						'Code' => substr($Position[9], 0, 1) . $Position[11],
						'Name' => $Position[9],
						'Colors' => $Position[11],
						'Ranges' => []
					);
	
					if ($Position[19]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[17],
							'To' => $Position[18],
							'Price' => round($Position[19], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[22]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[20],
							'To' => $Position[21],
							'Price' => round($Position[22], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[25]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[23],
							'To' => $Position[24],
							'Price' => round($Position[25], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[28]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[26],
							'To' => $Position[27],
							'Price' => round($Position[28], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[31]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[29],
							'To' => $Position[30],
							'Price' => round($Position[31], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[34]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[32],
							'To' => $Position[33],
							'Price' => round($Position[34], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[37]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[35],
							'To' => $Position[36],
							'Price' => round($Position[37], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[40]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[38],
							'To' => $Position[39],
							'Price' => round($Position[40], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[43]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[41],
							'To' => $Position[42],
							'Price' => round($Position[43], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
					if ($Position[46]) {
						$lProducts[$Position[2]]->Positions[$name]['Technologies'][$techCode]['Ranges'][] = array(
							'From' => $Position[44],
							'To' => $Position[45],
							'Price' => round($Position[46], 2),
							'Colors' => $Position[11],
							'Setup' => 25
						);
					}
				}
			}
		}
	}
	
	$n = 0;
	foreach ($lProducts as $lProduct) {
	
		// Debug, only add 2 Products
		/*
		if ($n == 1)
		{
			break;
		}
		var_dump($lProduct);
		*/
		$query = new WP_Query(array(
			'post_type' => 'product',
			'meta_key' => 'product_id',
			'meta_value' => $lProduct->ProductCode
		));
	
		if ($query->have_posts()) {
			while($query->have_posts()) {
				$query->the_post();
				if (get_field('hash_sum') != $lProduct->HashSum) {
					UpdateProduct(get_the_ID(), $lProduct);
					echo ('Update Product:' . $lProduct->Title . '<br />');
				}
			}
		}
		else
		{
			echo ('Create Product: ' . $lProduct->Title . '<br />');
			CreateNewProduct($lProduct);
		}
		$n++;
	}
} // do Import
//print_r($lProducts);

function CreateNewProduct($aProduct) {
	// Increase time limit
	//set_time_limit(10);
	
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
	
	$parentTitle = "Feuerzeuge";
	
	$args_parent = array(
		'post_type' => 'product_subcategory',
		'post_title' => $parentTitle,
		'post_status' => 'publish'
	);
	$parent_query = new WP_Query($args_parent);
	if ($parent_query->have_posts()){
		$newParent = true;
		
		while($parent_query->have_posts()){
			$parent_query->the_post();
			
			$currentparentTitle = get_post_field('post_title');
			
			if ($currentparentTitle == $parentTitle) {
				update_post_meta( $post_id, 'parent', get_the_ID());
				$newParent = false;
				break;
			}
		}
		
		if ($newParent) {
			$new_subcategory = wp_insert_post(array(
				'post_title' => $parentTitle,
				'post_name' => sanitize_title($parentTitle),
				'post_content' => null,
				'post_status' => 'publish',
				'post_type' => 'product_subcategory'
			));
			
			update_post_meta( $new_subcategory, 'parent', '4252');
			update_post_meta( $post_id, 'parent', $new_subcategory);
		}
		
	} else {
		$new_subcategory = wp_insert_post(array(
			'post_title' => $parentTitle,
			'post_name' => sanitize_title($parentTitle),
			'post_content' => null,
			'post_status' => 'publish',
			'post_type' => 'product_subcategory'
		));
		
		update_post_meta( $new_subcategory, 'parent', '4252');
		update_post_meta( $post_id, 'parent', $new_subcategory);
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
			'color_name' => (string) $Color['ColorName'],
			'color_code' => (string) $Color['ColorCode'],
			'color_image' => (string) DownloadFile($Color['ImageUrl'], $post_id)
		);
		add_row('variants', $row_variant, $post_id);
	}
	
	foreach ($aProduct->Positions as $Position) {
		$row = array(
			'position_name' => (string) $Position['Name'],
			'technologies' => []
		);
		
		foreach ($Position['Technologies'] as $Technology) {
			
			$tech_code = 'BC_' . $Technology['Code'];
			
			global $wpdb;
			
			$results = $wpdb->get_results("
                    SELECT posts.ID
                    FROM ".$wpdb->prefix."posts as posts
                    LEFT OUTER JOIN ".$wpdb->prefix."postmeta as meta ON
                            posts.ID = meta.post_id
                        AND meta.meta_key = 'code'
                    WHERE
                        posts.post_type = 'technology'
                    AND posts.post_status = 'publish'
                    AND meta.meta_value = '".$tech_code."'
                 ");
			
			if (count($results) == 0){
				
				$tech_id = wp_insert_post(array(
					'post_title' => $Technology['Code'],
					'post_name' => sanitize_title($Technology['Code']),
					'post_content' => null,
					'post_status' => 'publish',
					'post_type' => 'technology'
				));
				
				update_post_meta( $tech_id, 'name', $Technology['Name']);
				update_post_meta( $tech_id, 'code', "BC_" . $Technology['Code']);
				update_post_meta( $tech_id, 'mandator', "BC");
				
				foreach ($Technology['Ranges'] as $Range) {
					$row_range = array(
						'quantity_from' => (string) $Range['From'],
						'quantity_to' => (string) $Range['To'],
						'unit_price' => (string) $Range['Price'],
						'number_of_colors' => (string) $Range['Colors'],
						'setup_cost' => (string) $Range['Setup']
					);
					add_row('ranges', $row_range, $tech_id);
				}
				
			} else {
				
				foreach ($results as $result) {
					
					if (get_field('mandator', $result->ID) == "BC") {
						$tech_id = $result->ID;
						break;
					} else {
						continue;
					}
					
				}
				
			}
			
			$row['technologies'][] = array(
				'technology_code' => get_post($tech_id),
				'technology_name' => get_field('name', $tech_id),
				'max_colors' => (string) $Technology['Colors']
			);
			
		}
		
		add_row('positions', $row, $post_id);
	}
}

function UpdateProduct($post_id, LProduct $aProduct) {
	// Increase time limit
	set_time_limit(15);
	
	$lImageDelete = new LDeleteImages();
	
	update_post_meta( $post_id, 'hash_sum', $aProduct->HashSum);
	update_post_meta( $post_id, 'product_id', $aProduct->ProductCode);
	update_post_meta( $post_id, 'isActive', true);
	update_post_meta( $post_id, 'has_deal', 'No');
	
	$parentId = $aProduct->CategoryId;
	
	$args_parent = array(
		'post_type' => 'product_subcategory',
		'meta_key' => 'subcategory_id',
		'meta_value' => $parentId
	);
	
	$parent_query = new WP_Query($args_parent);
	
	while($parent_query->have_posts()){
		$parent_query->the_post();
		
		update_post_meta( $post_id, 'parent', get_the_ID());
		break;
	}
	DeleteImage(get_post_field('source_image', $post_id));
	update_post_meta( $post_id, 'source_image', (string) DownloadFile($aProduct->ImageUrl, $post_id), $post_id);
	
	update_post_meta( $post_id, 'description', (string) $aProduct->Description[0]);
	update_post_meta( $post_id, 'starting_price', (string) $aProduct->Prices[0]->PricePerPiece);
	
	$lOldQuantities = get_field('quantities_and_prices', $post_id);
	if ($lOldQuantities) {
		for ($i = count($lOldQuantities); $i > 0; $i--) {
			delete_row('quantities_and_prices', $i, $post_id);
		}
	}
	
	while (have_rows('variants', $post_id)) {
		the_row();
		
		DeleteImage(get_sub_field('color_image'));
	}
	
	$lOldColors = get_field('variants', $post_id);
	if ($lOldColors) {
		for ($i = count($lOldColors); $i > 0; $i--) {
			delete_row('variants', $i, $post_id);
		}
	}
	
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
			'color_image' => (string) DownloadFile($Color['ImageUrl'], $post_id)
		);
		add_row('variants', $row_variant, $post_id);
	}
}

function DownloadFile(string $aFileNameUrUrl, $aPostId) {
	if (!empty($aFileNameUrUrl)) {
		// Check if folder exists
		$lUploadDir = wp_upload_dir();
		$lDestinationFolder = trailingslashit($lUploadDir['basedir']) . LCatalogWriter::$IMAGE_SUBFOLDER;
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
			return (trailingslashit($lUploadDir['baseurl']) . LCatalogWriter::$IMAGE_SUBFOLDER . $lDestinationFileName);
		}
	}
	
	return false;
}

function DeleteImage(string $image) {
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