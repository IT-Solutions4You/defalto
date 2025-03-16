/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @var InventoryItem_InventoryItemDetail_Js */
Vtiger_Detail_Js('InventoryItem_InventoryItemDetail_Js', {}, {
    lineItemDetectingClass: 'lineItemRow',
    numOfLineItems: 0,

    init: function () {
        this._super();
        this.initializeVariables();
        this.registerBasicEvents();
    },

    initializeVariables: function () {
        this.dummyLineItemRow = jQuery('#dummyItemRow');
        this.dummyTextRow = jQuery('#dummyTextRow');
        this.lineItemsHolder = jQuery('#lineItemTab');
        this.numOfLineItems = this.lineItemsHolder.find('.' + this.lineItemDetectingClass).length;
    },

    registerBasicEvents: function () {
        this._super();
        this.registerItemsTableEvents();
        const self = this;

        app.event.on(Vtiger_Detail_Js.relatedListLoad, function () {
            self.initializeVariables();
            self.registerItemsTableEvents();
        });
    },

    registerItemsTableEvents: function () {
        this.recalculateTotals();
        this.makeLineItemsSortable();
        this.addRowListeners();
        this.registerLineItemAutoComplete();
        this.registerLineItemClear();
        this.registerLineItemPopup();
        this.registerAddButtons();
        this.registerOverallDiscountActions();
        this.registerAdjustmentActions();
        this.registerCurrencyActions();
        this.registerRegionActions();
        this.registerPriceBookActions();
        this.registerPriceBookPopUp();
    },

    registerAddButtons: function () {
        const self = this;

        const addTextLineHandler = function (e) {
            const currentTarget = jQuery(e.currentTarget);
            const params = {'currentTarget': currentTarget};
            let newTextLine = self.getNewTextItem(params);
            newTextLine = newTextLine.appendTo(self.lineItemsHolder);
            self.setupRowListeners(self.numOfLineItems);
            jQuery('.editRow', newTextLine).trigger('click');
            app.event.trigger('post.textLine.New', newTextLine);
            jQuery('.item_text', newTextLine).focus();
        };

        const addLineItemEventHandler = function (e, data) {
            const currentTarget = jQuery(e.currentTarget);
            const params = {'currentTarget': currentTarget};
            let newLineItem = self.getNewLineItem(params);
            newLineItem = newLineItem.appendTo(self.lineItemsHolder);
            newLineItem.find('input.item_text').addClass('autoComplete');
            newLineItem.find('.ignore-ui-registration').removeClass('ignore-ui-registration');
            vtUtils.applyFieldElementsView(newLineItem);
            self.registerLineItemAutoComplete(newLineItem);
            self.setupRowListeners(self.numOfLineItems);
            vtUtils.showSelect2ElementView(newLineItem.find('select.discount_type'));
            jQuery('.editRow', newLineItem).trigger('click');
            app.event.trigger('post.lineItem.New', newLineItem);

            if (typeof data != "undefined") {
                self.mapResultsToFields(newLineItem, data);
            } else {
                jQuery('.lineItemPopup', newLineItem).trigger('click');
            }
        };

        jQuery('#addText').on('click', addTextLineHandler);
        const addButtonsToolbar = jQuery('.inventoryItemAddButtons');
        addButtonsToolbar.find('button').not(':eq(0)').on('click', addLineItemEventHandler);

        const blockLineItemsAddDiv = jQuery('#block_line_items_add');
        /*blockLineItemsAddDiv.on('click', 'ul.add_menu li a', function (event) {
            const clickedItem = jQuery(this);
            const moduleName = clickedItem.data('modulename');

            if (moduleName === '') {
                addButtonsToolbar.find('button').first().trigger('click');
            } else {
                addButtonsToolbar.find('button[data-module-name="' + moduleName + '"]').trigger('click');
            }
        });*/
        blockLineItemsAddDiv.on('click', 'ul.add_menu li a', function (event, params) {
            app.helper.showProgress();

            const clickedItem = jQuery(this);
            const moduleName = clickedItem.data('modulename');

            if (typeof params === 'undefined') {
                params = {};
            }

            const requestParams = {
                'module': 'InventoryItem',
                'view': 'PopupItemEdit',
                'item_type': moduleName,
                'for_module': app.getModuleName(),
                'data': params.data
            };

            app.request.post({data:requestParams}).then(function(err,data) {
                app.helper.hideProgress();
                // console.log(err);
                // console.log(data);
                let form = jQuery('#InventoryItemPopupForm');
                let callbackparams = {
                    'cb': function (container) {
                        //self.registerPostReferenceEvent(container);
                        console.log('haf');
                        app.event.trigger('post.InventoryItemPopup.show', form);
                        app.helper.registerLeavePageWithoutSubmit(form);
                        app.helper.registerModalDismissWithoutSubmit(form);
                    },
                    backdrop: 'static',
                    keyboard: false
                };

                app.helper.showModal(data, callbackparams);
            });
        });
    },

    getLineItemSetype: function (row) {
        return row.find('.lineItemType').val();
    },

    getNewTextItem: function (params) {
        let currentTarget = params.currentTarget,
            itemType = currentTarget.data('moduleName'),
            newRow = this.dummyTextRow.clone(true).removeClass('hide').addClass(this.lineItemDetectingClass).removeClass('lineItemCloneCopy');

        newRow.attr('id', 'row0');
        newRow.find('.individualTaxContainer').removeClass('opacity-0');
        newRow.find('.lineItemPopup').filter(':not([data-module-name="' + itemType + '"])').remove();
        newRow.find('.lineItemType').val(itemType);
        ++this.numOfLineItems;
        this.updateRowNumberForRow(newRow, this.numOfLineItems);
        this.updateRowSequence(newRow);

        return newRow;
    },

    getNewLineItem: function (params) {
        let currentTarget = params.currentTarget,
            itemType = currentTarget.data('moduleName'),
            newRow = this.dummyLineItemRow.clone(true).removeClass('hide').addClass(this.lineItemDetectingClass).removeClass('lineItemCloneCopy');

        newRow.find('.individualTaxContainer').removeClass('opacity-0');
        newRow.find('.lineItemPopup').filter(':not([data-module-name="' + itemType + '"])').remove();
        newRow.find('.lineItemType').val(itemType);
        ++this.numOfLineItems;
        this.updateRowNumberForRow(newRow, this.numOfLineItems);
        this.updateRowSequence(newRow);

        return newRow;
    },

    updateRowNumberForRow: function (lineItemRow, expectedSequenceNumber) {
        const regex = /0$/;

        lineItemRow.find('*').each(function () {
            const thisElement = jQuery(this);

            if (thisElement.attr('id')) {
                const oldId = thisElement.attr('id');
                if (regex.test(oldId)) {
                    const newId = oldId.replace(regex, expectedSequenceNumber);
                    thisElement.attr('id', newId);
                }
            }

            if (thisElement.attr('name')) {
                const oldName = thisElement.attr('name');
                if (regex.test(oldName)) {
                    const newName = oldName.replace(regex, expectedSequenceNumber);
                    thisElement.attr('name', newName);
                }
            }

            if (thisElement.is('span') && thisElement.attr('class')) {
                const classes = thisElement.attr('class').split(' ');
                const updatedClasses = classes.map(className => {
                    if (regex.test(className)) {
                        return className.replace(regex, expectedSequenceNumber);
                    }
                    return className;
                }).join(' ');

                thisElement.attr('class', updatedClasses);
            }
        });

        let expectedRowId = 'row' + expectedSequenceNumber;
        lineItemRow.attr('id', expectedRowId).attr('data-row-num', expectedSequenceNumber);
        lineItemRow.find('input.rowNumber').val(expectedSequenceNumber);

        return lineItemRow;
    },

    updateRowSequence: function (row) {
        row.find('.rowSequence').val(1);
        const productTable = this.lineItemsHolder;

        if (productTable.find('tr').length > 0) {
            const lastRow = productTable.find('.lineItemRow:last');

            if (lastRow.length > 0) {
                const lastRowSequence = parseInt(lastRow.find('.rowSequence').val());
                row.find('.rowSequence').val(lastRowSequence + 1);
            }
        }
    },

    makeLineItemsSortable: function () {
        jQuery('#lineItemTab tbody').sortable({
            handle: '.drag_drop_line_item',
            update: function () {
                const newOrder = jQuery(this).sortable('toArray');
                const data = [];

                jQuery.each(newOrder, function (index, value) {
                    if (value !== '') {
                        let valueElement = jQuery('#' + value);
                        valueElement.find('input.rowSequence').val(index);
                        data.push({
                            'id': valueElement.find('input.lineItemId').val(),
                            'sequence': index,
                        });
                    }
                });

                const requestParams = {
                    'module': 'InventoryItem',
                    'action': 'SortLineItems',
                    'record': app.getRecordId(),
                    'data': JSON.stringify(data),
                };

                app.request.post({"data": requestParams}).then(function (err, res) {
                });
            }
        });
    },

    addRowListeners: function () {
        const self = this;
        jQuery('.lineItemRow').each(function () {
            const rowNum = jQuery(this).data('row-num');
            self.setupRowListeners(rowNum);
        });
    },

    setupRowListeners: function (rowNumber) {
        const self = this;
        const row = jQuery('#row' + rowNumber);

        row.on('click', '.deleteRow', function () {
            self.deleteProductLine(rowNumber);
        });

        row.on('click', '.editRow', function () {
            self.editProductLine(rowNumber);
            let tableWidth = jQuery(this).closest('table').outerWidth();
            let textarea = jQuery(this).closest('tr').find('textarea.description');

            textarea.css({
                'width': tableWidth * 0.5 + 'px',
                'max-width': tableWidth * 0.9 + 'px',
            });

            let textareaHeight = textarea.outerHeight() + 3;
            textarea.closest('td').css('padding-bottom', textareaHeight + 'px');
        });

        row.on('click', '.saveRow', function () {
            self.saveProductLine(rowNumber);
            jQuery('.item_text_td', row).css('padding-bottom', '0px');
        });

        row.on('click', '.cancelEditRow', function () {
            self.cancelEditProductLine(rowNumber);
            jQuery('.item_text_td', row).css('padding-bottom', '0px');
        });

        row.on('change', 'input, select.tax', function () {
            const element = jQuery(this);
            // Don't save on hidden input changes unless specifically needed
            if ((!element.is(':hidden') || element.hasClass('recalculateOnChange')) && !element.hasClass('doNotRecalculateOnChange') && !isNaN(element.val())) {
                self.recalculateProductLine(rowNumber);
            }
        });

        row.on('click', '.editProductDiscount', function () {
            jQuery('#discountSettingsDiv' + rowNumber).show();
        });

        row.on('change', '.discount_type', function () {
            self.recalculateDiscountDiv(rowNumber);
        });

        row.on('change', '.discount_popup', function () {
            self.recalculateDiscountDiv(rowNumber);
        });

        row.on('click', '.closeDiscountDiv', function () {
            jQuery('#discount_popup' + rowNumber).val(jQuery('#original_discount' + rowNumber).val());
            jQuery('#discount_type' + rowNumber).val(jQuery('#original_discount_type' + rowNumber).val());
            jQuery('#discount_type' + rowNumber).trigger('change');
            jQuery(this).closest('.discountSettingsDiv').hide();
        });

        row.on('click', '.applyDiscount', function () {
            jQuery('#discount' + rowNumber).val(jQuery('#discount_popup' + rowNumber).val());
            jQuery('#original_discount' + rowNumber).val(jQuery('#discount' + rowNumber).val());
            jQuery('#original_discount_type' + rowNumber).val(jQuery('#discount_type' + rowNumber).val());
            self.recalculateProductLine(rowNumber);
            jQuery(this).closest('.discountSettingsDiv').hide();
        });

        jQuery(document).on('input mouseup', 'textarea.description', function () {
            let newHeight = jQuery(this).outerHeight() + 3;
            jQuery(this).closest('td').css('padding-bottom', newHeight + 'px');
        });
    },

    saveProductLine: function (rowNumber) {
        const row = jQuery('#row' + rowNumber);
        const data = this.serializeRow(row);
        let taxElement = jQuery('select.tax', row);
        let selectedOption = taxElement.find('option:selected');
        data.taxid = selectedOption.data('taxid');

        // Check if the row has any non-empty values
        const hasContent = Object.values(data).some(value => value !== '' && value != null);

        if (hasContent) {
            jQuery.ajax({
                url: 'index.php',
                method: 'POST',
                data: {
                    rowNum: rowNumber,
                    module: 'InventoryItem',
                    action: 'SaveProductLine',
                    for_record: app.getRecordId(),
                    data: data
                },
                success: function (response) {
                    if (!isNaN(response.result)) {
                        row.find('[name="lineItemId' + rowNumber + '"]').val(response.result);
                    }

                    jQuery('.noEditLineItem', row).toggleClass('hide');
                    jQuery('.editLineItem', row).toggleClass('hide');

                    jQuery('input', row).each(function () {
                        const element = jQuery(this);
                        const elementId = element.attr('id');

                        if (elementId) {
                            const originalElement = jQuery('#original_' + elementId);

                            if (originalElement.length > 0) {
                                originalElement.val(element.val());
                            }

                            const displayElement = jQuery('.display_' + elementId);

                            if (displayElement.length > 0) {
                                displayElement.text(element.val());
                            }
                        }
                    });

                    jQuery('.display_tax' + rowNumber).text(jQuery('#tax' + rowNumber).val());

                    row.trigger('lineSaved', [response]);
                },
                error: function (xhr, status, error) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_PRODUCT_LINE_SAVE_ERROR', 'InventoryItem')});

                    row.trigger('lineErrorSaving', [error]);
                }
            });
        }
    },

    deleteProductLine: function (rowNumber) {
        if (confirm(app.vtranslate('JS_ARE_YOU_SURE_YOU_WANT_TO_DELETE')) === true) {
            const row = jQuery('#row' + rowNumber);
            app.request.post({
                'data': {
                    module: 'InventoryItem',
                    action: 'DeleteProductLine',
                    for_record: app.getRecordId(),
                    lineItemId: jQuery('input[name="lineItemId' + rowNumber + '"]').val()
                }
            }).then(function () {
                row.remove();
            });
        }
    },

    serializeRow: function (row) {
        let data = {};
        row.find('input, select, textarea').each(function () {
            const element = jQuery(this);
            const name = element.attr('name');
            if (name) {
                if (element.is(':checkbox')) {
                    data[name] = element.is(':checked') ? element.val() : '';
                } else {
                    data[name] = element.val();
                }
            }
        });

        return data;
    },

    editProductLine: function (rowNumber) {
        const row = jQuery('#row' + rowNumber);
        jQuery('.noEditLineItem', row).toggleClass('hide');
        jQuery('.editLineItem', row).toggleClass('hide');

    },

    cancelEditProductLine: function (rowNumber) {
        const row = jQuery('#row' + rowNumber);
        jQuery('.noEditLineItem', row).toggleClass('hide');
        jQuery('.editLineItem', row).toggleClass('hide');

        jQuery('input', row).each(function () {
            const element = jQuery(this);
            const elementId = element.attr('id');

            if (elementId) {
                const originalElement = jQuery('#original_' + elementId);

                if (originalElement.length > 0) {
                    element.val(originalElement.val());
                }
            }
        });
    },

    registerLineItemAutoComplete: function (container) {
        const self = this;

        if (typeof container == 'undefined') {
            container = this.lineItemsHolder;
        }

        container.find('input.autoComplete').autocomplete({
            'minLength': '3',
            'source': function (request, response) {
                const inputElement = jQuery(this.element[0]);
                const tdElement = inputElement.closest('td');
                const searchValue = request.term;
                const params = {};
                params.search_module = tdElement.find('.lineItemPopup').data('moduleName');
                params.search_value = searchValue;
                self.searchModuleNames(params).then(function (data) {
                    const responseDataList = [];
                    let serverDataFormat = data;

                    if (serverDataFormat.length <= 0) {
                        serverDataFormat = [{
                            'label': app.vtranslate('JS_NO_RESULTS_FOUND'),
                            'type': 'no results'
                        }];
                    }

                    for (let id in serverDataFormat) {
                        responseDataList.push(serverDataFormat[id]);
                    }

                    response(responseDataList);
                });
            },
            'select': function (event, ui) {
                const selectedItemData = ui.item;

                if (typeof selectedItemData.type != 'undefined' && selectedItemData.type === "no results") {
                    return false;
                }

                const element = jQuery(this);
                const tdElement = element.closest('td');
                const selectedModule = tdElement.find('.lineItemPopup').data('moduleName');
                const dataUrl = "index.php?module=InventoryItem&action=GetItemDetails&record=" + selectedItemData.id + "&currency_id=" + self.getCurrencyId() + "&sourceModule=" + app.getModuleName() + "&pricebookid=" + jQuery('#pricebookid_original').val();
                app.request.get({'url': dataUrl}).then(
                    function (error, data) {
                        if (error == null) {
                            const itemRow = element.parents('tr').first();
                            itemRow.find('.lineItemType').val(selectedModule);
                            self.mapResultsToFields(itemRow, data[0]);
                        }
                    },
                    function (error, err) {

                    }
                );
            }
        });
    },

    mapResultsToFields: function (parentRow, responseData) {
        const lineItemNameElment = jQuery('input.item_text', parentRow);
        const referenceModule = this.getLineItemSetype(parentRow);
        const selectedRegionId = jQuery('#region_id_original').val();

        for (let id in responseData) {
            let recordData = responseData[id];
            jQuery('input.productid', parentRow).val(id);
            jQuery('input.lineItemType', parentRow).val(referenceModule);
            jQuery('input.quantity', parentRow).val(1).focus();
            jQuery('input.unit', parentRow).val(recordData.unit);
            jQuery('input.price', parentRow).val(recordData.listprice).trigger('change');
            jQuery('input.purchase_cost', parentRow).val(recordData.purchaseCost);
            jQuery('input.pricebookid', parentRow).val(recordData.pricebookid);
            jQuery('input.overall_discount', parentRow).val(jQuery('#overall_discount_percent').val());
            let taxElement = jQuery('select.tax', parentRow);
            taxElement.find('option:not(:first)').remove();
            let recordTaxes = recordData.taxes;

            for (let taxid in recordTaxes) {
                let tax = recordTaxes[taxid];
                let percentage = tax.percentage;
                let regions = tax.regions ? JSON.parse(tax.regions) : {};

                for (let regionId in regions) {
                    if (regionId == selectedRegionId) {
                        percentage = regions[regionId];
                    }
                }

                percentage = (percentage * 1).toFixed(2);

                let option = jQuery('<option>', {
                    value: percentage,
                    'data-taxid': taxid,
                    text: tax.tax_label + ' (' + percentage + '%)'
                });
                taxElement.append(option);
            }

            taxElement.find('option:eq(1)').prop('selected', true);
            lineItemNameElment.val(recordData.name);
            this.recalculateProductLine(parentRow.data('rowNum'));
        }
    },

    registerLineItemClear: function () {
        const self = this;

        this.lineItemsHolder.on('click', '.clearLineItem', function (e) {
            const elem = jQuery(e.currentTarget);
            const parentElem = elem.closest('td');
            self.clearLineItemDetails(parentElem);
            e.preventDefault();
        });
    },

    clearLineItemDetails: function (parentElem) {
        const lineItemRow = parentElem.closest('tr');
        jQuery('input.form-control', lineItemRow).val('');
        jQuery('input.allowOnlyNumbers', lineItemRow).val(0);
        jQuery('input.productid', lineItemRow).val('');
        let taxElement = jQuery('select.tax', lineItemRow);
        taxElement.find('option:not(:first)').remove();
    },

    registerLineItemPopup: function () {
        const self = this;

        this.lineItemsHolder.on('click', '.lineItemPopup', function (e) {
            const triggerElement = jQuery(e.currentTarget);
            const popupReferenceModule = triggerElement.data('moduleName');
            self.showLineItemPopup({'item_module': popupReferenceModule});
            const postPopupHandler = function (e, data) {
                data = JSON.parse(data);

                if (!jQuery.isArray(data)) {
                    data = [data];
                }

                self.postLineItemSelectionActions(triggerElement.closest('tr'), data, popupReferenceModule);
            };
            app.event.off('post.LineItemPopupSelection.click');
            app.event.one('post.LineItemPopupSelection.click', postPopupHandler);
        });
    },

    showLineItemPopup: function (callerParams) {
        let params = {
            'module': 'InventoryItem',
            'view': 'ItemsPopup',
            'src_module': this.getModuleName(),
            'src_record': app.getRecordId(),
            'multi_select': true,
            'currency_id': this.getCurrencyId(),
        };
        params = jQuery.extend(params, callerParams);
        const popupInstance = InventoryItem_Popup_Js.getInstance();
        popupInstance.showPopup(params, 'post.LineItemPopupSelection.click');
    },

    postLineItemSelectionActions: function (itemRow, selectedLineItemsData, lineItemSelectedModuleName) {
        for (let index in selectedLineItemsData) {

            if ((index * 1) !== 0) {
                jQuery('#add' + lineItemSelectedModuleName).trigger('click', selectedLineItemsData[index]);
            } else {
                itemRow.find('.lineItemType').val(lineItemSelectedModuleName);
                this.mapResultsToFields(itemRow, selectedLineItemsData[index]);
            }
        }
    },

    recalculateTotals: function () {
        const self = this;
        jQuery('tfoot span[class^="total_"]', this.lineItemsHolder).each(function () {
            let span = jQuery(this);

            // Extract the specific class part (e.g., from "total_price_total" -> "price_total")
            let targetClass = span.attr('class').replace('total_', '');

            // Initialize total for this column
            let columnTotal = 0;

            // Find all inputs in tbody with the specific class and sum their values
            jQuery('tbody input.' + targetClass, self.lineItemsHolder).each(function () {
                let inputValue = parseFloat(jQuery(this).val()) || 0; // Parse value, default to 0
                columnTotal += inputValue;
            });

            if (targetClass === 'price_total') {
                let adjustment = parseFloat(jQuery('#adjustment').val());

                if (!isNaN(adjustment)) {
                    columnTotal += adjustment;
                }
            }

            // Update the total in the corresponding span (formatted to 2 decimal places)
            span.text(columnTotal.toFixed(2));
        });
    },

    recalculateProductLine: function (rowNumber) {
        const row = jQuery('#row' + rowNumber);
        const quantity = parseFloat(jQuery('.quantity', row).val());
        let price = parseFloat(jQuery('.price', row).val());
        price = quantity * price;
        jQuery('.subtotal', row).val(price.toFixed(2));
        jQuery('.display_subtotal' + rowNumber, row).html(price.toFixed(2));
        jQuery('.subtotal_in_discount_div', row).html(price.toFixed(2));

        let discount_amount = this.recalculateDiscountDiv(rowNumber);
        jQuery('.discount_amount', row).val(discount_amount);

        price = price - discount_amount;
        jQuery('.price_after_discount', row).val(price.toFixed(2));
        jQuery('.display_price_after_discount' + rowNumber, row).html(price.toFixed(2));

        const overall_discount = parseFloat(jQuery('.overall_discount', row).val());
        let overall_discount_amount = parseFloat(jQuery('.overall_discount_amount', row).val());

        if (overall_discount && overall_discount > 0) {
            overall_discount_amount = price * (overall_discount / 100);
            jQuery('.overall_discount_amount', row).val(overall_discount_amount.toFixed(2));
        }

        if (isNaN(overall_discount_amount)) {
            overall_discount_amount = 0;
        }

        price = price - overall_discount_amount;
        jQuery('.price_after_overall_discount', row).val(price.toFixed(2));
        jQuery('.display_price_after_overall_discount' + rowNumber, row).html(price.toFixed(2));

        let purchseCost = parseFloat(jQuery('.purchase_cost', row).val());
        let margin = 0;

        if (!isNaN(purchseCost) && purchseCost > 0) {
            margin = price - (purchseCost * quantity);
        }

        jQuery('.margin', row).val(margin.toFixed(2));
        jQuery('display_margin' + rowNumber, row).html(margin.toFixed(2));

        let tax = parseFloat(jQuery('.tax', row).val());

        if (!tax) {
            tax = 0;
        }

        let tax_amount = price * (tax / 100);

        if (isNaN(tax_amount)) {
            tax_amount = 0;
        }

        jQuery('.tax_amount', row).val(tax_amount.toFixed(2));
        price = price + tax_amount;
        jQuery('.price_total', row).val(price.toFixed(2));
        jQuery('.display_total' + rowNumber, row).html(price.toFixed(2));

        this.recalculateTotals();
    },

    recalculateDiscountDiv: function (rowNumber) {
        const row = jQuery('#row' + rowNumber);
        const quantity = parseFloat(jQuery('.quantity', row).val());
        const price = parseFloat(jQuery('.subtotal', row).val());
        const discount_type = jQuery('.discount_type', row).val();
        let discount = parseFloat(jQuery('.discount_popup', row).val());
        let discount_amount = 0;

        if (isNaN(discount)) {
            discount = 0;
        }

        if (discount_type === 'Percentage') {
            discount_amount = price * (discount / 100);
        } else if (discount_type === 'Direct') {
            discount_amount = discount;
        } else if (discount_type === 'Product Unit Price') {
            discount_amount = quantity * discount;
        }

        discount_amount = discount_amount.toFixed(2);

        jQuery('.discount_computed_value', row).val(discount_amount);

        return discount_amount;
    },

    registerOverallDiscountActions: function () {
        const self = this;

        this.lineItemsHolder.on('click', '.editOverallDiscount', function () {
            jQuery('#overallDiscountSettingDiv').show();
            jQuery('#overall_discount_percent').focus();
        });

        this.lineItemsHolder.on('click', '.closeOverallDiscountDiv', function () {
            jQuery('#overall_discount_percent').val(jQuery('#original_overall_discount_percent').val());
            jQuery('#overall_discount_percent').trigger('change');
            jQuery('#overallDiscountSettingDiv').hide();
        });

        this.lineItemsHolder.on('change', '#overall_discount_percent', function () {
            let price = parseFloat(jQuery('.total_price_after_discount').text());
            let overall_discount_amount = price * (jQuery('#overall_discount_percent').val() / 100);
            jQuery('#overall_discount_amount').val(overall_discount_amount.toFixed(2));
        });

        this.lineItemsHolder.on('click', '.saveOverallDiscount', function () {
            const overallDiscountPercent = parseFloat(jQuery('#overall_discount_percent').val());
            const originalOverallDiscountPercent = parseFloat(jQuery('#original_overall_discount_percent').val());

            if (originalOverallDiscountPercent !== overallDiscountPercent) {
                app.helper.showProgress();
                const params = {
                    module: 'InventoryItem',
                    action: 'SaveItemsBlockDetail',
                    mode: 'saveOverallDiscount',
                    for_record: app.getRecordId(),
                    overall_discount_percent: overallDiscountPercent,
                };

                app.request.post({"data": params}).then(function (err, res) {
                    location.reload();
                });
            } else {
                app.helper.showSuccessNotification({'message': app.vtranslate('JS_SUCCESS')});
                jQuery('#overallDiscountSettingDiv').hide();
            }
        });
    },

    registerAdjustmentActions: function () {
        const self = this;

        this.lineItemsHolder.on('click', '.editAdjustment', function () {
            jQuery('#adjustmentSettingDiv').show();
            jQuery('#adjustment').focus();
            jQuery('#adjustment').trigger('change');
        });

        this.lineItemsHolder.on('click', '.closeAdjustmentDiv', function () {
            jQuery('#adjustment').val(jQuery('#original_adjustment').val());
            jQuery('#adjustment').trigger('change');
            jQuery('#adjustmentSettingDiv').hide();
        });

        this.lineItemsHolder.on('change keyup', '#adjustment', function () {
            let price = 0;
            jQuery('tbody input.price_total', self.lineItemsHolder).each(function () {
                price += parseFloat(jQuery(this).val()) || 0; // Parse value, default to 0
            });
            let adjustment = parseFloat(jQuery('#adjustment').val());

            if (isNaN(adjustment)) {
                adjustment = 0;
            }

            jQuery('#total_with_adjustment').val((price + adjustment).toFixed(2));
        });

        this.lineItemsHolder.on('click', '.saveAdjustment', function () {
            const adjustment = parseFloat(jQuery('#adjustment').val());
            const originalAdjustment = parseFloat(jQuery('#original_adjustment').val());

            if (originalAdjustment !== adjustment) {
                app.helper.showProgress();
                const params = {
                    module: 'InventoryItem',
                    action: 'SaveItemsBlockDetail',
                    mode: 'saveAdjustment',
                    for_record: app.getRecordId(),
                    for_module: app.getModuleName(),
                    adjustment: adjustment,
                };

                app.request.post({"data": params}).then(function (err, res) {
                    app.helper.showSuccessNotification({'message': app.vtranslate('JS_SUCCESS')});
                    jQuery('#original_adjustment').val(adjustment);
                    jQuery('.total_price_total').text(jQuery('#total_with_adjustment').val());
                    jQuery('.adjustmentDisplay').text(jQuery('#adjustment').val());
                    app.helper.hideProgress();
                    jQuery('#adjustmentSettingDiv').hide();
                });
            } else {
                jQuery('#adjustmentSettingDiv').hide();
            }
        });
    },

    registerCurrencyActions: function () {
        const self = this;
        const blockLineItemsCurrencyDiv = jQuery('#block_line_items_currency');
        blockLineItemsCurrencyDiv.on('click', 'ul.currency li a', function (event) {
            const clickedItem = jQuery(this);
            const currency = clickedItem.data('currencyid');
            const originalCurrency = jQuery('#currency_id_original').val();

            if (typeof currency == 'undefined') {
                return true;
            }

            event.preventDefault();

            if (originalCurrency != currency) {
                app.helper.showConfirmationBox({'message': app.vtranslate('JS_CONFIRM_CURRENCY_CHANGE')}).then(
                    function () {
                        app.helper.showProgress();
                        const params = {
                            module: 'InventoryItem',
                            action: 'SaveItemsBlockDetail',
                            mode: 'saveCurrency',
                            for_record: app.getRecordId(),
                            for_module: app.getModuleName(),
                            currency_id: currency,
                        };

                        app.request.post({"data": params}).then(function (err, res) {
                            app.helper.showSuccessNotification({'message': app.vtranslate('JS_SUCCESS')});
                            jQuery('#currency_id_original').val(currency);
                            app.helper.hideProgress();
                            jQuery('button.currency-button').text(clickedItem.text());
                            location.reload();
                        });
                    },
                    function (error, err) {
                    }
                );
            }
        });
    },

    registerRegionActions: function () {
        const self = this;
        const blockLineItemsRegionDiv = jQuery('#block_line_items_region');

        blockLineItemsRegionDiv.on('click', 'ul.region li a', function (event) {
            const clickedItem = jQuery(this);
            const region = clickedItem.data('regionid');
            const originalRegion = jQuery('#region_id_original').val();

            if (typeof region == 'undefined') {
                return true;
            }

            event.preventDefault();

            if (originalRegion != region) {
                app.helper.showConfirmationBox({'message': app.vtranslate('JS_CONFIRM_TAXES_AND_CHARGES_REPLACE')}).then(
                    function () {
                        app.helper.showProgress();
                        const params = {
                            module: 'InventoryItem',
                            action: 'SaveItemsBlockDetail',
                            mode: 'saveRegion',
                            for_record: app.getRecordId(),
                            for_module: app.getModuleName(),
                            region_id: region,
                        };

                        app.request.post({"data": params}).then(function (err, res) {
                            app.helper.showSuccessNotification({'message': app.vtranslate('JS_SUCCESS')});
                            jQuery('#region_id_original').val(region);
                            app.helper.hideProgress();
                            jQuery('button.region-button').text(clickedItem.text());
                            location.reload();
                        });
                    },
                    function (error, err) {
                    }
                );
            }
        });
    },

    registerPriceBookActions: function () {
        const self = this;
        const blockLineItemsPriceBookDiv = jQuery('#block_line_items_pricebook');
        blockLineItemsPriceBookDiv.on('click', 'ul.pricebook li a', function (event) {
            const clickedItem = jQuery(this);
            const priceBook = clickedItem.data('pricebookid');
            const originalPriceBook = jQuery('#pricebookid_original').val();

            if (typeof priceBook == 'undefined') {
                return true;
            }

            event.preventDefault();

            if (originalPriceBook != priceBook) {
                app.helper.showConfirmationBox({'message': app.vtranslate('JS_CONFIRM_PRICEBOOK_CHANGE')}).then(
                    function () {
                        app.helper.showProgress();
                        const params = {
                            module: 'InventoryItem',
                            action: 'SaveItemsBlockDetail',
                            mode: 'savePriceBook',
                            for_record: app.getRecordId(),
                            for_module: app.getModuleName(),
                            pricebookid: priceBook,
                        };

                        app.request.post({"data": params}).then(function (err, res) {
                            app.helper.showSuccessNotification({'message': app.vtranslate('JS_SUCCESS')});
                            jQuery('#pricebookid_original').val(priceBook);
                            app.helper.hideProgress();
                            jQuery('button.pricebook-button').text(clickedItem.text());
                            location.reload();
                        });
                    },
                    function (error, err) {
                    }
                );
            }
        });
    },

    registerPriceBookPopUp: function () {
        const self = this;

        this.lineItemsHolder.on('click', '.choosePriceBook', function (e) {
            const triggerElement = jQuery(e.currentTarget);
            const lineItemRow = triggerElement.parents('tr').first();
            const rowNumber = lineItemRow.find('input.rowNumber').val();
            const productId = lineItemRow.find('#productid' + rowNumber).val();

            let params = {
                'module': 'PriceBooks',
                'currency_id': self.getCurrencyId(),
                'src_record': productId,
                'view': 'Popup',
                'get_url': 'getProductListPriceURL',
                'src_field': 'productid',
                'src_module': 'Products',
            };

            const popupInstance = Vtiger_Popup_Js.getInstance();
            popupInstance.showPopup(params, 'post.LineItemPriceBookSelect.click');

            const postPriceBookPopupHandler = function (e, data) {
                const responseData = JSON.parse(data);

                let listPrice = parseFloat(responseData.price).toFixed(3);
                let priceElement = lineItemRow.find('.price');
                priceElement.val(listPrice);
                priceElement.trigger('change');
                let pricebookidElement = lineItemRow.find('.pricebookid');
                pricebookidElement.val(responseData.pricebookid);
            };

            app.event.off('post.LineItemPriceBookSelect.click');
            app.event.one('post.LineItemPriceBookSelect.click', postPriceBookPopupHandler);
        });
    },

    getCurrencyId: function () {
        return jQuery('#currency_id_original').val();
    },
});

InventoryItem_InventoryItemDetail_Js_Instance = new InventoryItem_InventoryItemDetail_Js();