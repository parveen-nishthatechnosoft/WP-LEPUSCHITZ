<?php
// Get the product
if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    $lProductID = $_GET['product_id'];
} else {
    $lProductID = get_the_ID();
}

// Get the categories
$lSubcategoryId = get_field('parent', $lProductID);
$lCategoryId = get_field('parent', $lSubcategoryId);

// Get data for price calculator
$lRates = get_field('quantities_and_prices', $lProductID);
$lLowestPriceAmount = 1;
foreach ($lRates as &$lRate) {
    $lRate['price'] = (float)$lRate['price'];
    $lRate['quantity_from'] = (int)$lRate['quantity_from'];

    if ($lLowestPriceAmount < $lRate['quantity_from']) {
        $lLowestPriceAmount = $lRate['quantity_from'];
    }

    if (!empty($lRate['quantity_to'])) {
        $lRate['quantity_to'] = (int)$lRate['quantity_to'];
    } else {
        $lRate['quantity_to'] = PHP_INT_MAX;
    }
}
$lRates[sizeof($lRates) - 1]['quantity_to'] = PHP_INT_MAX;
$lLowestPrice = get_field('starting_price', $lProductID);
$lMinimumOrderQuantity = get_field('minimum_order_quantity', $lProductID);
if (empty($lMinimumOrderQuantity) || $lMinimumOrderQuantity == 0) {
    $lMinimumOrderQuantity = 1;
}

// Get positions
$lPositions = get_field('positions', $lProductID);

// Check if there is technology names in there, if not, copy from technology itself
if (($lPositions != null) && (sizeof($lPositions) > 0)) {
    foreach ($lPositions as &$lPosition) {
        foreach ($lPosition['technologies'] as &$lTechnology) {
            if (empty($lTechnology['technology_name']) || strlen($lTechnology['technology_name']) < 12) {
                // Get the name
                $lTechName = get_field('name', $lTechnology['technology_code']);
                $lTechnology['technology_name'] = $lTechName;

                //! Maybe update the data for later usage. Not done now!
            }
        }
    }
}

$lCatalogId = get_field('catalog_Id', $lProductID);
$lMandatorId = get_field('catalog_mandantId', $lCatalogId);
$lMandateType = get_field('mandate-type', $lMandatorId);
$lMandatorPreprintCostsPrefix = LMandator::GetMandatorPrefixByType($lMandateType);

$lSetupCostsPerColor = get_field($lMandatorPreprintCostsPrefix . 'preprintcosts', $lSubcategoryId);
if (empty($lSetupCostsPerColor)) {
    $lSetupCostsPerColor = get_field($lMandatorPreprintCostsPrefix . 'preprintcosts', $lCategoryId);
}
if (empty($lSetupCostsPerColor)) {
    $lSetupCostsPerColor = get_field($lMandatorPreprintCostsPrefix . 'preprintcosts', 'option');
}
$lCalculatorData = new stdClass();
$lCalculatorData->Rates = $lRates;
$lCalculatorData->LowestPrice = (float)$lLowestPrice;
$lCalculatorData->LowestPriceAmount = $lLowestPriceAmount;
$lCalculatorData->SetupCostsPerColor = (float)$lSetupCostsPerColor;
$lCalculatorData->MinimumOrderQuantity = (int)$lMinimumOrderQuantity;

if ($lMandateType == LMandator::JUNG_TYPE) {
    $lCalculatorData->DisocuntPercentage = 3;
} elseif ($lMandateType == LMandator::ANDACOOL_TYPE || $lMandateType == LMandator::KP_TYPE) {
    $lCalculatorData->DisocuntPercentage = 4;
} else {
    $lCalculatorData->DisocuntPercentage = 6;
}

// Check if there is a calculator possible
$lCalculatorData->Positions = is_array($lPositions) ? $lPositions : [];
$lCalculatorData->EnableCalculator = !empty($lRates);

if ($lCalculatorData->EnableCalculator) {
    // Add the technology cost ranges
    foreach ($lCalculatorData->Positions as &$lPosition) {
        foreach ($lPosition['technologies'] as &$lTechnology) {
            // Get the technology id
            $lTechnologyId = $lTechnology['technology_code'];

            // Get the technology data
            $lTechnology['rates'] = get_field('ranges', $lTechnologyId);

            foreach ($lTechnology['rates'] as &$lRate) {
                $lRate['quantity_from'] = (int)$lRate['quantity_from'];

                if (!empty($lRate['quantity_to'])) {
                    $lRate['quantity_to'] = (int)$lRate['quantity_to'];
                } else {
                    $lRate['quantity_to'] = PHP_INT_MAX;
                }
            }

            // Get the technology preprintcosts if there
            $lTechnology['preprintcosts'] = get_field('preprintcosts', $lTechnologyId);
            $lTechnology['preprintsetup'] = get_field('preprintsetup', $lTechnologyId);
        }
    }
}
?>
<script>
    let CalculatorData = <?php echo(json_encode($lCalculatorData)); ?>;
    jQuery(function () {
        if (CalculatorData.EnableCalculator) {
            InitCalculator();
        }
    })
</script>
<div class="breadcrumbs">
    <span class="breadbcrumb-green"><a href="/">Home</a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadbcrumb-green"><a href="/products">Produkte</a></span>
    &nbsp;<span class="breadcrumb-purple">/</span>
    <span class="breadbcrumb-green"><a href="<?php echo get_post_field('guid', $lCategoryId) . "?category=" . get_post_field('ID', $lCategoryId); ?>"><?php echo get_post_field('post_title', $lCategoryId); ?></a></span>
    <span class="breadcrumb-purple">/</span>&nbsp;
    <span class="breadcrumb-purple"><?php echo get_post_field('post_title', $lSubcategoryId); ?></span>
</div>

<div class="left-and-right">
    <div class="left-column desktop">
        <?php include "_accordion.php"; ?>
    </div>
    <div class="right-column">
        <div class="product-details">
            <div class="product-details-left">
                <div id="imgs">
                    <img loading=lazy src="<?php echo(getProductImage($lProductID)); ?>" alt="">
                </div>

                <span class="starting-price">
                ab&nbsp;&euro;&nbsp;<?php
                    // Round to two digits
                    $lPrice = get_field('starting_price', $lProductID);
                    echo(number_format($lPrice, 2));
                ?>
                </span>

                <div class="colors">
                    <?php
                    $lVariants = get_field('variants', $lProductID);
                    // Check if there are colors in it
                    foreach ($lVariants as $lVariant) {
                        if ($lVariant['color_name'] != LColor::NOCOLOR_DE) {
                            echo("<h2>Produktfarben:</h2>");
                            break;
                        }
                    }

                    foreach ($lVariants as $lVariant) {
                        if ($lVariant['color_name'] != LColor::NOCOLOR_DE) {
                            if ($lVariant['color_name'] == "Thumbnail") {
                                ?>
                                <div id="<?php echo($lVariant['color_image']); ?>" style="border-color: <?php echo($lVariant['color_code']); ?>">
                                    <span id="clr_<?php echo($lVariant['color_name']); ?>" style="background-image: url('<?php the_sub_field('color_image'); ?>'); background-size: contain"></span>
                                </div>
                                <?php
                            } else {
                                $lColor1ImageUrl = $lVariant['color_image'];
                                $lColor1Image = getResizedImage($lColor1ImageUrl, 758, 758, 'image758_');
                                $lColor1Hex = $lVariant['color_code'];
                                $lColor1Name = $lVariant['color_name'];
                                $lColor2Hex = $lVariant['color2_code'];
                                $lColor2Name = $lVariant['color2_name'];

                                if (empty($lColor2Name)) {
                                    ?>
                                    <div id="<?php echo($lColor1Image); ?>" style="border-color: <?php echo($lColor1Hex); ?>">
                                        <span id="clr_<?php echo($lColor1Name); ?>" style="background-color: <?php echo($lColor1Hex); ?>"></span>
                                    </div>
                                    <?php
                                } else {
                                    ?>
                                    <div id="<?php echo($lColor1Image); ?>" style="border-color: <?php echo($lColor1Hex); ?>">
                                        <span class="halve" id="clr_<?php echo($lColor1Name); ?>" style="background-color: <?php echo($lColor1Hex); ?>"></span>
                                        <span class="halve" id="clr_<?php echo($lColor2Name); ?>" style="background-color: <?php echo($lColor2Hex); ?>"></span>
                                    </div>
                                    <?php
                                }
                            }
                        }
                    }
                    ?>

                </div>

                <div class="have-questions desktop">
                    <table class="questions">
                        <tbody>
                        <tr>
                            <td class="table-upper" colspan="3">Haben Sie Fragen?</td>
                        </tr>
                        <tr>
                            <td class="table-link">
                                <a href="/wp-content/uploads/2021/04/Druckdatenblatt.pdf" class="link" target="_blank">
                                    Druckdaten
                                </a>
                            </td>
                            <td class="table-link">
                                <a href="/faq" class="link">
                                    FAQ
                                </a>
                            </td>
                            <td class="table-link">
                                <a href="/spezifikationen/" class="link">
                                    Spezifikationen
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="table-phone" colspan="3">
                                Servicetelefon:
                                <a href="tel:+432246503330">
                                    +43 2246 50333 0
                                </a><br>
                                Email: <a href="mailto:office@lepuschitz-promotion.at">office@lepuschitz-promotion.at</a>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="product-details-right">
                <?php
                if (current_user_can('editor') || current_user_can('administrator')) {
                    ?>
                    <p style="font-weight: bold; color: green;">Lieferant: <?php echo(LMandator::GetMandatorNameByType($lMandateType)); ?></p>
                    <?php
                }
                ?>
                <h2 class="title text-left mobi-text-left">
                    <?php echo get_post_field('post_title', $lProductID); ?>
                </h2>
                <p class="product-description">
                    <?php the_field('description', $lProductID); ?>
                </p>

                <p class="product-sizes">
                    <?php
                    if (have_rows('product_sizes', $lProductID)) {
                        while (have_rows('product_sizes', $lProductID)) {
                            the_row();
                            echo get_sub_field('size') . " | ";
                        }
                    }
                    ?>
                </p>
                <?php
                if ($lCalculatorData->EnableCalculator && ($lMandateType != LMandator::ROEMER_TYPE)) {
                    ?>
                    <h2 class="prod_hide_mobile">Preisrechner <span>(inkl. Veredelung)</span></h2>
                    <div id="calculatorFields">
                        <div class="calculator">
                            <fieldset>
                                <div class="form-group-left">
                                    <div class="form-group">
                                        <label for="quantity">Menge</label>
                                        <input type="text" id="quantity" class="form-control" placeholder="Anzahl angeben" oninput="DoCalculation()">
                                    </div>
                                </div>
                                <div class="form-group-right">
                                    <div class="form-group">
                                        <label for="quantity">&nbsp;</label>
                                        <span id="error" class="error"></span>
                                    </div>
                                </div>
                            </fieldset>
                            <div id="positions">

                            </div>
                        </div>
                    </div>
                    <div id="calculation">
                        <div id="priceNote"></div>
                    </div>

                    <button id="btnNote" class="btn mobi-btn btn-default mobi-btn-default" onclick="togglePriceNote()">Preisnotiz</button>

                    <div style="display: none">
                        <h2>Mengenpreise:</h2>
                        <div class="product-pricing">
                            <div id="pricing_info" style="display: none"></div>
                            <table>
                                <tbody>
                                <tr>
                                    <th>Menge</th>
                                    <?php
                                    if (have_rows('quantities_and_prices', $lProductID)) {
                                        while (have_rows('quantities_and_prices', $lProductID)) {
                                            the_row();
                                            echo "<td>" . get_sub_field('quantity_from') . "</td>";
                                        }
                                    }
                                    ?>
                                </tr>
                                <tr>
                                    <th>Preis/Stk.&nbsp;&euro;</th>
                                    <?php
                                    if (have_rows('quantities_and_prices', $lProductID)) {
                                        while (have_rows('quantities_and_prices', $lProductID)) {
                                            the_row();
                                            echo "<td>" . get_sub_field('price') . "</td>";
                                        }
                                    }
                                    ?>
                                </tr>
                                </tbody>
                            </table>
                            <a href="/kontakt">
                                <button class="btn btn-default mobi-btn-default btn-contact">Anfrage senden</button>
                            </a>
                        </div>

                        <h2 class="prod_hide_mobile">Preisrechner <span>(inkl. Veredelung)</span></h2>
                        <button id="btnCalculator" class="btn mobi-btn btn-default mobi-btn-default" onclick="toggleCalculator()">Preis berechnen</button>
                    </div>
                    <?php
                } else {
                    // Depending on catalog, show message
                    if (($lMandateType == LMandator::LSHOP_TYPE) || ($lMandateType == LMandator::ROEMER_TYPE)) {
                        ?>
                        <div>
                            <h3 style="font-weight: bold; margin-bottom: 16px;">Preis- und Druckinformation:</h3>
                            <p>
                                Für die optimale Veredelungstechnik für das von Ihnen ausgewählte Produkt gehen wir persönlich auf Ihre Wünsche ein und bieten
                                Ihnen eine unverbindliche telefonische Beratung an und optimieren so Ihr Angebot.
                            </p>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>

        <div class="have-questions mobi">
            <table class="questions">
                <tbody>
                <tr>
                    <td class="table-upper" colspan="3">Haben Sie Fragen?</td>
                </tr>
                <tr>
                    <td class="table-link">
                        <a href="/wp-content/uploads/2021/04/Druckdatenblatt.pdf" class="link">
                            Druckdaten
                        </a>
                    </td>
                    <td class="table-link">
                        <a href="/faq" class="link">
                            FAQ
                        </a>
                    </td>
                    <td class="table-link">
                        <a href="/spezifikationen/" class="link">
                            Spezifikationen
                        </a>
                    </td>
                </tr>
                <tr>
                    <td class="table-phone" colspan="3">
                        Servicetelefon:
                        <a href="tel:+432246503330">
                            +43 2246 50333 0
                        </a>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <?php
        $_category = get_post_field('post_title', $lCategoryId);
        if (!empty($_category)) {
            ?>
            <div class="other-subcategories">

                <h1>Ähnliche Produkte in der Kategorie <span class="bold"><?php echo($_category); ?></span>:</h1>
                <div class="archive">
                    <div class="gallery-container">
                        <div class="gallery">
                            <?php

                            $currentParent = get_field('parent', $lProductID);

                            $query = new WP_Query(array(
                                'post_type' => 'product',
                                'post_status' => 'publish',
                                'meta_query' => array(
                                    array(
                                        'key' => 'parent',
                                        'value' => $lSubcategoryId
                                    ),
                                    array(
                                        'key' => 'source_image',
                                        'value' => array(''),
                                        'compare' => 'NOT IN'
                                    )
                                ),
                            ));

                            $i = 0;

                            while ($query->have_posts()) {
                                $query->the_post();
                                if (get_field('parent') == $currentParent) {
                                    if (get_the_ID() == $lProductID) {
                                        continue;
                                    }
                                    $lThumbnailImage = getProductThumbnail(get_the_ID());
                                    ?>
                                    <a href="<?php echo get_permalink() . "?product_id=" . get_the_ID(); ?>" class="gallery-element">
                                        <div class="gallery-image" style="background-image: url('<?php echo($lThumbnailImage); ?>')"></div>
                                        <span><?php the_title(); ?></span>
                                    </a>
                                    <?php
                                }
                                if ($i == 7) {
                                    break;
                                }
                                $i++;
                            } ?>
                        </div>
                    </div>
                </div>

            </div>
            <?php
        }
        ?>
    </div>
</div>

<div id="PriceNoteModal" class="modal">
    <button id="btnClose" class="" onclick="togglePriceNote()">X</button>
    <div class="price_note_wrapper">
        <div class="price_note">
            <div class="price_toprow">
                <div class="price_logo">
                    <img src="https://www.lepuschitz-promotion.at/wp-content/uploads/2020/09/lepuschitz-logo.png" alt="">
                </div>
                <div class="price_informations">
                    <div class="price_infotel">
                        <a href="tel:+432246503330">
                            <i class="fas fa-mobile biggermargin"></i>
                            <p> +43 2246 50333 0</p>
                        </a>
                    </div>
                    <div class="price_infomail">
                        <a href="mailto:office@lepuschitz-promotion.at">
                            <i class="fas fa-envelope"></i>
                            <p> office@lepuschitz-promotion.at</p>
                        </a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="price_headnote">
                <h2>Preisnotiz</h2>
                <p>vom</p>
                <div id="date"></div>
            </div>
            <p>Sehr geehrter Kunde!</p>
            <p>Vielen Dank für Ihr Interesse. Gerne erstellen wir die gewünschte unverbindliche Preisnotiz.</p>
            <div class="price_productname">
                <h2><?php echo get_post_field('post_title', $lProductID) ?></h2>
            </div>
            <div class="price_productdesc">
                <p><?php the_field('description', $lProductID); ?></p>
            </div>
            <div class="price_productsmalldesc">
                <p>
                    <?php
                    if (have_rows('product_sizes', $lProductID)) :
                        while (have_rows('product_sizes', $lProductID)) :
                            the_row();

                            echo get_sub_field('size') . " | ";
                        endwhile;
                    endif;
                    ?>

                    Farbe:
                    <?php
                    if (have_rows('variants', $lProductID)) :
                        while (have_rows('variants', $lProductID)) :
                            the_row();

                            echo get_sub_field('color_name') . " | ";
                        endwhile;
                    endif;
                    ?>

                </p>
            </div>
            <div class="price_productinfo">
                <div class="price_productgallery">
                    <img src="<?php the_field('source_image', $lProductID); ?>" alt="mainimg">
                </div>
            </div>

            <div id="calculation">
                <div id="priceNotePreview" class="priceNote"></div>
            </div>
            <p>Preise verstehen sich zuzüglich gesetzlicher Umsatzsteuer.</p>
            <p>Lieferung: ab Werk ca. 3 Wochen ab Auftragsfreigabe vorbehaltlich Verfügbarkeit.</p>
            <p class="price_bold">Preisnotiz gültig für Industrie, Handel und Gewerbe - Irrtümer und Preisänderungen
                vorbehalten. Es gelten unsere Konditionen und AGBs!</p>
        </div>
        <div class="price_download">
            <a href="#" id="btnPDF" onclick="PrintPDF()">Als PDF speichern</a>
        </div>
    </div>
</div>
