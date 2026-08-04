/**
 * Setup the calculator
 */
var SelectedPositionIndexes = [];

function InitCalculator() {
    // Fill the first variables
    //jQuery('#quantity').val(CalculatorData.LowestPriceAmount);
    AddPosition(0);

    // Recalculate
    DoCalculation();
}

function AddPosition(aPositionIndex) {
    // Check if position already added
    if (SelectedPositionIndexes.includes(aPositionIndex))
        return;

    // Calculate the left positions
    var lPositionOptions = "<option value=\"\" disabled>Position wählen</option>\n";
    for (var lIndex = 0; lIndex < CalculatorData.Positions.length; lIndex++) {
        let lPosition = CalculatorData.Positions[lIndex];
        var lDisabled = "";
        var lSelected = "";
        if (aPositionIndex == lIndex)
            lSelected = "selected";
        lPositionOptions += "<option value=\"" + lIndex + "\" " + lDisabled + " " + lSelected + ">" + lPosition['position_name'] + "</option>\n";
    }

    // Add the position to the dropdown
    let lHtml =
        "                     <div id=\"positionContainer" + aPositionIndex + "\">\n" +
        "                        <fieldset style=\"border-top: 1px solid lightgray; padding-top: 12px;\">\n" +
        "                            <div class=\"form-group-left\">\n" +
        "                                <div class=\"form-group\" id=\"position-container" + aPositionIndex + "\">\n" +
        "                                    <select id=\"position" + aPositionIndex + "\" class=\"form-control\" data-index='" + aPositionIndex + "' onchange='OnPositionChange(this)'>\n" +
        lPositionOptions +
        "                                    </select>\n" +
        "                                </div>\n" +
        "                            </div>\n" +
        "                            <div class=\"form-group-right\">\n" +
        "                                <div class=\"form-group\">\n" +
        "                                   <a href=\"#\" onclick=\"OnDeletePositionClick(this)\" data-index='" + aPositionIndex + "' id=\"deletebutton" + aPositionIndex + "\" class=\"iconbutton deletePosition\"><i class=\"fas fa-minus-circle\"></i> Druckposition löschen</a>" +
        "                                </div>\n" +
        "                            </div>\n" +
        "                        </fieldset>\n" +
        "                        <fieldset>\n" +
        "                            <div class=\"form-group-left\">\n" +
        "                                <div class=\"form-group\" id=\"refining-container" + aPositionIndex + "\">\n" +
        "                                </div>\n" +
        "                            </div>\n" +
        "                            <div class=\"form-group-right\">\n" +
        "                                <div class=\"form-group\" id=\"printColor-container" + aPositionIndex + "\">\n" +
        "                                </div>\n" +
        "                            </div>\n" +
        "                        </fieldset>\n" +
        "                        <fieldset>\n" +
        "                            <div class=\"form-group-left\">\n" +
        "                                <div class=\"form-group\">\n" +
        "                                   <a href=\"#\" onclick=\"OnAddPositionClick(this)\" id=\"addbutton" + aPositionIndex + "\" class=\"iconbutton addPosition\"><i class=\"fas fa-plus-circle\"></i> Weitere Druckposition</a>" +
        "                                </div>\n" +
        "                            </div>\n" +
        "                        </fieldset>\n" +
        "                     </div></n>";
    jQuery('#positions').append(lHtml);

    // Add position to current positions list
    SelectedPositionIndexes.push(aPositionIndex);

    // Set refinings
    SetRefinings(aPositionIndex, 0);

    // Refresh select options
    ChangeDisabled();

    // Recalculate
    DoCalculation();
}

function SetRefinings(aPositionIndex, aRefiningIndex) {
    // Calculate the refinings
    var lRefiningOptions = "<option value=\"\" disabled=\"\">Veredelung wählen</option>\n";
    for (var lIndex = 0; lIndex < CalculatorData.Positions[aPositionIndex].technologies.length; lIndex++) {
        let lTechnology = CalculatorData.Positions[aPositionIndex].technologies[lIndex];
        var lDisabled = "";
        var lSelected = "";
        if (aRefiningIndex === lIndex)
            lSelected = "selected";
        lRefiningOptions += "<option value=\"" + lIndex + "\" " + lDisabled + " " + lSelected + ">" + lTechnology['technology_name'] + "</option>\n";
    }

    let lHtml =
        "                                    <select id=\"refining" + aPositionIndex + "\" class=\"form-control\" onchange='OnRefiningChange(" + aPositionIndex + ", this)'>\n" +
        lRefiningOptions +
        "                                    </select>\n";

    jQuery('#refining-container' + aPositionIndex).html(lHtml);

    SetColorings(aPositionIndex, aRefiningIndex, 0);
}

function SetColorings(aPositionIndex, aRefiningIndex, aColoringIndex) {
    // Calculate the colorings
    var lColoringOptions = "<option value=\"\" disabled=\"\">Druckfarben wählen</option>\n";
    let lTechnology = CalculatorData.Positions[aPositionIndex].technologies[aRefiningIndex];
    let lNumberOfColors = parseInt(lTechnology['max_colors']);
    var lDisabled = "";
    var lSelected = "";

    if (lNumberOfColors > 0) {
        if (lNumberOfColors !== 16777216) {
            for (var lIndex = 0; lIndex < lNumberOfColors; lIndex++) {
                let lColoringText = (lIndex + 1) + "-färbig";
                lSelected = '';
                if (aColoringIndex === lIndex)
                    lSelected = "selected";
                lColoringOptions += "<option value=\"" + (lIndex + 1) + "\" " + lDisabled + " " + lSelected + ">" + lColoringText + "</option>\n";
            }
        } else {
            let lColoringText = "Vollfarbig";
            lSelected = "selected";
            lColoringOptions += "<option value=\"16777216\" " + lDisabled + " " + lSelected + ">" + lColoringText + "</option>\n";
        }
    } else {
        let lColoringText = "Keine Farbe wählbar";
        lSelected = "selected";
        lColoringOptions += "<option value=\"1\" " + lDisabled + " " + lSelected + ">" + lColoringText + "</option>\n";
    }

    let lHtml =
        "                                    <select id=\"coloring" + aPositionIndex + "\" class=\"form-control\" onchange='OnColoringChange(" + aPositionIndex + ", " + aRefiningIndex + ", this)'>\n" +
        lColoringOptions +
        "                                    </select>\n";

    jQuery('#printColor-container' + aPositionIndex).html(lHtml);

    // Hide coloring of only one possibility
    jQuery('#coloring' + aPositionIndex).toggle(jQuery('#coloring' + aPositionIndex + ' option').length > 2);
}

function DeletePosition(aPositionIndex) {
    // Check if position is added
    if (!SelectedPositionIndexes.includes(aPositionIndex))
        return;

    // Remove the container
    jQuery('#positionContainer' + aPositionIndex).remove();

    // Remove position from current positions list
    SelectedPositionIndexes.splice(SelectedPositionIndexes.indexOf(aPositionIndex), 1);

    // Refresh select options
    ChangeDisabled();

    // Recalculate
    DoCalculation();
}

function ChangeDisabled() {
    // Change select option states for currently listed positions
    for (var lPositionIndex = 0; lPositionIndex < SelectedPositionIndexes.length; lPositionIndex++) {
        var lPosition = SelectedPositionIndexes[lPositionIndex];

        // Get the selected one
        var lCurrentPosition = parseInt(jQuery('#position' + lPosition + ' option:selected').val());

        // Enable all
        for (lIndex = 0; lIndex < CalculatorData.Positions.length; lIndex++) {
            jQuery('#position' + lPosition + ' option[value="' + lIndex + '"]').prop('disabled', false);
        }

        // Disable all listed except the selected one
        for (var lSelectedPositionIndex = 0; lSelectedPositionIndex < SelectedPositionIndexes.length; lSelectedPositionIndex++) {
            var lSelectedPosition = SelectedPositionIndexes[lSelectedPositionIndex];
            if (lCurrentPosition !== lSelectedPosition) {
                jQuery('#position' + lPosition + ' option[value="' + lSelectedPosition + '"]').prop('disabled', true);
            }
        }
    }

    // Change add/delete buttons
    for (lPositionIndex = 0; lPositionIndex < SelectedPositionIndexes.length; lPositionIndex++) {
        lPosition = SelectedPositionIndexes[lPositionIndex];

        // Show delete button if not last one
        jQuery('#deletebutton' + lPosition).toggle(SelectedPositionIndexes.length > 1);

        // Check if last one
        if (lPositionIndex === SelectedPositionIndexes.length - 1) {
            // Is the last one, so show add button if more are possible
            jQuery('#addbutton' + lPosition).toggle(lPositionIndex < CalculatorData.Positions.length - 1);
        } else {
            // Hide add button
            jQuery('#addbutton' + lPosition).hide();
        }
    }
}

function OnPositionChange(aSender) {
    // Add the new one
    lNewPosition = parseInt(aSender.value);
    AddPosition(lNewPosition);

    // Remove the old position
    lOldPosition = parseInt(aSender.dataset.index);
    DeletePosition(lOldPosition);

    // Recalculate
    DoCalculation();
}

function OnRefiningChange(aPositionIndex, aSender) {
    // Set the new one
    lNewRefining = parseInt(aSender.value);
    SetRefinings(aPositionIndex, lNewRefining);

    // Recalculate
    DoCalculation();
}

function OnColoringChange(aPositionIndex, aRefiningIndex, aSender) {
    // Set the new one
    lNewColoring = parseInt(aSender.value);

    // Recalculate
    DoCalculation();
}

function DoCalculation() {
    // Reset error
    jQuery('#error').text('');

    // Get the number of printed articles
    let lAmountOfProducts = parseInt(jQuery("#quantity").val());
    if (lAmountOfProducts < CalculatorData.MinimumOrderQuantity || isNaN(lAmountOfProducts)) {
        lAmountOfProducts = CalculatorData.MinimumOrderQuantity;
        jQuery('#error').text("Mindestmenge von " + CalculatorData.MinimumOrderQuantity + " beachten!");
    }

    // Find the price based on number or products
    var lPricePerProduct = 0.0;
    for (var lPriceIndex = 0; lPriceIndex < CalculatorData.Rates.length; lPriceIndex++) {
        let lPriceRate = CalculatorData.Rates[lPriceIndex];
        if ((lAmountOfProducts >= lPriceRate['quantity_from']) && (lAmountOfProducts <= lPriceRate['quantity_to'])) {
            lPricePerProduct = lPriceRate['price'];
            break;
        }
    }
    var lTotalAmountOfProductsPrice = lAmountOfProducts * lPricePerProduct;
    var lTotalNettoPrice = lTotalAmountOfProductsPrice;

    // Calculate the print costs
    let lHtmlPrints = "";
    var lTotalPrintSetupCosts = 0.0;
    for (var lPositionIndex = 0; lPositionIndex < SelectedPositionIndexes.length; lPositionIndex++) {
        var lPosition = SelectedPositionIndexes[lPositionIndex];
        var lTechnologyIndex = parseInt(jQuery("#refining" + lPosition + " option:selected").val());
        var lTechnologyName = jQuery("#refining" + lPosition + " option:selected").text();
        var lTechnology = CalculatorData.Positions[lPosition].technologies[lTechnologyIndex];
        var lSelectedNumberOfColors = parseInt(jQuery("#coloring" + lPosition + " option:selected").val());
        var lSelectedNumberOfColorsName = "";
        var lTechnologyType = "";
        var lDifferentColoringsPossible = jQuery('#coloring' + lPosition + ' option').length > 2;
        if (lSelectedNumberOfColors > 0 && lTechnology['max_colors'] !== "0") {
            if (lDifferentColoringsPossible) {
                lSelectedNumberOfColorsName = ", " + jQuery("#coloring" + lPosition + " option:selected").text();
            }
            lTechnologyType = "";
        }

        let lMinimumOrderQuantity = 9223372036854775807;
        for (var lTechnologyRateIndex = 0; lTechnologyRateIndex < lTechnology['rates'].length; lTechnologyRateIndex++) {
            let lTechnologyRate = lTechnology['rates'][lTechnologyRateIndex];
            if (lMinimumOrderQuantity > parseInt(lTechnologyRate['quantity_from'])) {
                if (parseInt(lTechnologyRate['quantity_from']) > 0) {
                    lMinimumOrderQuantity = parseInt(lTechnologyRate['quantity_from']);
                }
            }
            if (parseInt(lTechnologyRate['number_of_colors']) >= lSelectedNumberOfColors) {
                // Check if range fits
                if (lTechnologyRate['quantity_to'] === "0") {
                    lTechnologyRate['quantity_to'] = "9223372036854775807";
                }
                if ((lAmountOfProducts >= parseInt(lTechnologyRate['quantity_from'])) && (lAmountOfProducts <= parseInt(lTechnologyRate['quantity_to']))) {
                    // rate found
                    let lUnitPrice = parseFloat(lTechnologyRate['unit_price']);
                    var lPrintCosts = lAmountOfProducts * lUnitPrice;

                    var lPrintSetupCostsGeneral = 0;
                    var lPrintSetupCostsPerColor = CalculatorData.SetupCostsPerColor;
                    if (lTechnology['preprintcosts'] != null && lTechnology['preprintcosts'] !== "") {
                        lPrintSetupCostsPerColor = parseInt(lTechnology['preprintcosts']);
                    }
                    if (lTechnology['preprintsetup'] != null && lTechnology['preprintsetup'] !== "") {
                        lPrintSetupCostsGeneral = parseInt(lTechnology['preprintsetup']);
                    }

                    var lNumberOfColors = lSelectedNumberOfColors;
                    if (lNumberOfColors === 16777216) {
                        lNumberOfColors = 1;
                    }

                    lTotalPrintSetupCosts += lPrintSetupCostsPerColor * lNumberOfColors + lPrintSetupCostsGeneral;
                    lTotalNettoPrice += lPrintCosts + (lPrintSetupCostsPerColor * lNumberOfColors) + lPrintSetupCostsGeneral;

                    if (lPrintCosts > 0) {
                        lHtmlPrints +=
                            "                        <tr class=\"technologyCalculation\">\n" +
                            "                            <td class=\"calculationLeft\">+ " + lTechnologyType + lTechnologyName + lSelectedNumberOfColorsName + "</td>\n" +
                            "                            <td class=\"calculationRight\">" + lPrintCosts.toFixed(2) + " €</td>\n" +
                            "                        </tr>\n";
                    }

                    // Exit loop, rate found
                    break;
                }
            }
        }

        // Check if minimum is set
        if (lMinimumOrderQuantity !== 9223372036854775807) {
            if (lAmountOfProducts < lMinimumOrderQuantity) {
                jQuery('#error').text("Mindestmenge von " + lMinimumOrderQuantity + " beachten!");
            }
        }
    }

    let lDiscountPercentage = CalculatorData.DisocuntPercentage;
    var lTotalNettoDiscountPrice = lTotalNettoPrice * ((100 - lDiscountPercentage) / 100);

    var lHtml =
        "                     <table class=\"priceNote\" style='width: 100%'>\n" +
        "                        <tbody><tr id=\"quantityCalculation\">\n" +
        "                            <td class=\"calculationLeft\">" + lAmountOfProducts + " Stück x " + lPricePerProduct.toFixed(2) + " €</td>\n" +
        "                            <td class=\"calculationRight\">" + lTotalAmountOfProductsPrice.toFixed(2) + " €</td>\n" +
        "                        </tr>\n";

    lHtml += lHtmlPrints;

    if (lTotalPrintSetupCosts > 0) {
        lHtml +=
            "                        <tr class=\"setupCalculation\">\n" +
            "                            <td class=\"calculationLeft\">+ Druckvorkosten</td>\n" +
            "                            <td class=\"calculationRight\">" + lTotalPrintSetupCosts.toFixed(2) + " €</td>\n" +
            "                        </tr>\n";
    }
    lHtml +=
        "                        <tr id=\"netCalculation\">\n" +
        "                            <td class=\"calculationLeft\">= Gesamt netto</td>\n" +
        "                            <td class=\"calculationRight\">" + lTotalNettoPrice.toFixed(2) + " €</td>\n" +
        "                        </tr>\n" +
        "                        <tr id=\"discountCalculation\">\n" +
        "                            <td class=\"calculationLeft\">- " + lDiscountPercentage + "% Online-Rabatt</td>\n" +
        "                            <td class=\"calculationRight\">- " + (lTotalNettoPrice - lTotalNettoDiscountPrice).toFixed(2) + " €</td>\n" +
        "                        </tr>\n" +
        "                        <tr id=\"priceSum\">\n" +
        "                            <td class=\"calculationLeft\">Gesamt exkl. USt.</td>\n" +
        "                            <td class=\"calculationRight\">" + lTotalNettoDiscountPrice.toFixed(2) + " €</td>\n" +
        "                        </tr>\n" +
        "                    </tbody></table>";

    jQuery("#priceNote").html(lHtml);
    jQuery("#priceNotePreview").html(lHtml);
}

function OnAddPositionClick(aSender) {
    // Find a free position to be added
    for (lIndex = 0; lIndex < CalculatorData.Positions.length; lIndex++) {
        if (!SelectedPositionIndexes.includes(lIndex)) {
            AddPosition(lIndex);
        }
    }
}

function OnDeletePositionClick(aSender) {
    lDeletePosition = parseInt(aSender.dataset.index);
    DeletePosition(lDeletePosition);
}

function PrintPDF() {
    //send the div to PDF
    jQuery(".price_note_wrapper")[0].scrollIntoView(); //scroll to div , then execute
    html2canvas($("#PriceNoteModal .price_note"), { // DIV ID HERE
        onrendered: function (canvas) {
            var imgData = canvas.toDataURL('image/png');
            var doc = new jsPDF('portrait', 'pt');
            doc.addImage(imgData, 'png', 16, 16);
            doc.save('Preisnotiz.pdf'); //SAVE PDF FILE
        }
    });
}

function togglePriceNote() {
    document.querySelector('#PriceNoteModal').classList.toggle('active');
    document.querySelector('body').classList.toggle('active');
}

// current date  dd.mm.yyyy
var d = new Date();
var month = d.getMonth() + 1;
var day = d.getDate();
var date =
    (('' + day).length < 2 ? '0' : '') + day + '.' +
    (('' + month).length < 2 ? '0' : '') + month + '.' +
    d.getFullYear();
$("#date").html(date);


