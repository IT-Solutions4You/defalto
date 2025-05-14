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
        this.registerLineItemAutoCompleteOld();
        this.registerLineItemClearOld();
        this.registerLineItemPopupOld();
        this.registerAddButtons();
        this.registerOverallDiscountActions();
        this.registerAdjustmentActions();
        this.registerCurrencyActions();
        this.registerRegionActions();
        this.registerPriceBookActions();
        this.registerPriceBookPopUpOld();
    },

    registerAddButtons: function () {
        const self = this;

        const addLineItemEventHandler = function (event, params) {
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
                'source_module': app.getModuleName(),
                'source_record': app.getRecordId(),
                'data': params.data
            };

            app.request.post({data: requestParams}).then(function (err, data) {
                app.helper.hideProgress();
                let form = jQuery('#InventoryItemPopupForm');
                let callbackParams = {
                    'cb': function (container) {
                        self.registerItemPopupEditEvents(container);
                        jQuery('input.item_text', container).focus();
                        const overallDiscount = jQuery('#overall_discount_percent').val();
                        jQuery('input.overall_discount', container).val(overallDiscount);
                        jQuery('span.display_overall_discount', container).text(overallDiscount);
                        app.event.trigger('post.InventoryItemPopup.show', form);
                        app.helper.registerLeavePageWithoutSubmit(form);
                        app.helper.registerModalDismissWithoutSubmit(form);
                    },
                    backdrop: 'static',
                    keyboard: false
                };

                app.helper.showModal(data, callbackParams);
            });        };

        const addButtonsToolbar = jQuery('.inventoryItemAddButtons');
        addButtonsToolbar.find('button').on('click', addLineItemEventHandler);

        const blockLineItemsAddDiv = jQuery('#block_line_items_add');
        blockLineItemsAddDiv.on('click', 'ul.add_menu li a', function (event) {
            const clickedItem = jQuery(this);
            const moduleName = clickedItem.data('modulename');

            if (moduleName === '') {
                addButtonsToolbar.find('button').first().trigger('click');
            } else {
                addButtonsToolbar.find('button[data-modulename="' + moduleName + '"]').trigger('click');
            }
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

        row.on('click', '.editItem', function () {
            self.editItem(rowNumber);
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
            self.recalculateDiscountDivOld(rowNumber);
        });

        row.on('change', '.discount_popup', function () {
            self.recalculateDiscountDivOld(rowNumber);
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

    registerLineItemAutoCompleteOld: function (container) {
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
                            self.mapResultsToFieldsOld(itemRow, data[0]);
                        }
                    },
                    function (error, err) {

                    }
                );
            }
        });
    },

    mapResultsToFieldsOld: function (parentRow, responseData) {
        const lineItemNameElment = jQuery('input.item_text', parentRow);
        const referenceModule = this.getLineItemSetype(parentRow);
        const selectedRegionId = jQuery('#region_id_original').val();

        for (let id in responseData) {
            let recordData = responseData[id];
            jQuery('input.productid', parentRow).val(id);
            jQuery('input.lineItemType', parentRow).val(referenceModule);
            jQuery('input.quantity', parentRow).val(1);
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

    registerLineItemClearOld: function () {
        const self = this;

        this.lineItemsHolder.on('click', '.clearLineItem', function (e) {
            const elem = jQuery(e.currentTarget);
            const parentElem = elem.closest('td');
            self.clearLineItemDetailsOld(parentElem);
            e.preventDefault();
        });
    },

    clearLineItemDetailsOld: function (parentElem) {
        const lineItemRow = parentElem.closest('tr');
        jQuery('input.form-control', lineItemRow).val('');
        jQuery('input.allowOnlyNumbers', lineItemRow).val(0);
        jQuery('input.productid', lineItemRow).val('');
        let taxElement = jQuery('select.tax', lineItemRow);
        taxElement.find('option:not(:first)').remove();
    },

    registerLineItemPopupOld: function () {
        const self = this;

        this.lineItemsHolder.on('click', '.lineItemPopup', function (e) {
            const triggerElement = jQuery(e.currentTarget);
            const popupReferenceModule = triggerElement.data('moduleName');
            self.showLineItemPopupOld({'item_module': popupReferenceModule});
            const postPopupHandler = function (e, data) {
                data = JSON.parse(data);

                if (!jQuery.isArray(data)) {
                    data = [data];
                }

                self.postLineItemSelectionActionsOld(triggerElement.closest('tr'), data, popupReferenceModule);
            };
            app.event.off('post.LineItemPopupSelection.click');
            app.event.one('post.LineItemPopupSelection.click', postPopupHandler);
        });
    },

    showLineItemPopupOld: function (callerParams) {
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

    postLineItemSelectionActionsOld: function (itemRow, selectedLineItemsData, lineItemSelectedModuleName) {
        for (let index in selectedLineItemsData) {

            if ((index * 1) !== 0) {
                jQuery('#add' + lineItemSelectedModuleName).trigger('click', selectedLineItemsData[index]);
            } else {
                itemRow.find('.lineItemType').val(lineItemSelectedModuleName);
                this.mapResultsToFieldsOld(itemRow, selectedLineItemsData[index]);
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
            span.text(columnTotal.toFixed(app.getNumberOfDecimals()));
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

        let discount_amount = this.recalculateDiscountDivOld(rowNumber);
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

    recalculateDiscountDivOld: function (rowNumber) {
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
        } else if (discount_type === 'Discount per Unit') {
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

    registerPriceBookPopUpOld: function () {
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


    /***************************************************
     ***************************************************
     ***************************************************
     ***************************************************
     ***************************************************
     ***************************************************
     ***************************************************
     ***************************************************
     ***************************************************
     ***************************************************
     ***************************************************
     ***************************************************/


    editItem: function (rowNumber) {
        app.helper.showProgress();

        const self = this;
        const clickedItem = jQuery(this);
        const moduleName = clickedItem.data('modulename');

        if (typeof params === 'undefined') {
            params = {};
        }

        const requestParams = {
            'module': 'InventoryItem',
            'view': 'PopupItemEdit',
            'record': jQuery('input[name="lineItemId' + rowNumber + '"]').val(),
            'item_type': moduleName,
            'source_module': app.getModuleName(),
            'source_record': app.getRecordId(),
            'data': params.data
        };

        app.request.post({data: requestParams}).then(function (err, data) {
            app.helper.hideProgress();
            let form = jQuery('#InventoryItemPopupForm');
            let callbackParams = {
                'cb': function (container) {
                    console.log(container);
                    self.registerItemPopupEditEvents(container);
                    jQuery('input.item_text', container).focus();
                    const overallDiscount = jQuery('#overall_discount_percent').val();
                    jQuery('input.overall_discount', container).val(overallDiscount);
                    jQuery('span.display_overall_discount', container).text(overallDiscount);
                    app.event.trigger('post.InventoryItemPopup.show', form);
                    app.helper.registerLeavePageWithoutSubmit(form);
                    app.helper.registerModalDismissWithoutSubmit(form);
                },
                backdrop: 'static',
                keyboard: false
            };

            app.helper.showModal(data, callbackParams);
        });
    },

    registerItemPopupEditEvents: function (container) {
        this.setupListeners(container);
        this.registerLineItemAutoComplete(container);
        this.registerLineItemClear(container);
        this.registerLineItemPopup(container);
        this.registerPriceBookPopUp(container);
        this.loadCkEditor(container);
        this.applyOverallDiscount(container);
    },

    setupListeners: function (container) {
        const self = this;

        container.on('click', '.saveButton', function () {
            let data = {action: 'SaveFromPopup'};
            container.find('input, select, textarea').each(function () {
                const element = jQuery(this);
                const name = element.attr('name');
                if (name) {
                    if (element.is(':checkbox')) {
                        data[name] = element.is(':checked') ? element.val() : '';
                    } else if (element.is('textarea')) {
                        const instance = CKEDITOR.instances[element.attr('id')];

                        if (instance) {
                            data[name] = instance.getData();
                        } else {
                            data[name] = element.val();
                        }
                    } else {
                        data[name] = element.val();
                    }
                }
            });
            let taxElement = jQuery('select.tax', container);
            let selectedOption = taxElement.find('option:selected');
            data.taxid = selectedOption.data('taxid');


            jQuery.ajax({
                url: 'index.php',
                method: 'POST',
                data: data,
                success: function (response) {
console.log(response);

                    container.trigger('lineSaved', [response]);
                    container.find('.btn-close').trigger('click');
                },
                error: function (xhr, status, error) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_PRODUCT_LINE_SAVE_ERROR', 'InventoryItem')});

                    container.trigger('lineErrorSaving', [error]);
                }
            });
        });

        container.on('click', '.cancelLink', function () {
            container.find('.btn-close').trigger('click');
        });

        /**
         * Recalculate on change
         */
        container.on('change', 'input, select.tax', function () {
            const element = jQuery(this);
            // Don't save on hidden input changes unless specifically needed
            if ((!element.is(':hidden') || element.hasClass('recalculateOnChange')) && !element.hasClass('doNotRecalculateOnChange') && !isNaN(element.val())) {
                self.recalculateItem(container);
            }
        });

        container.on('click', '.editProductDiscount', function () {
            jQuery('#discountSettingsDiv', container).show();
            jQuery('#tax_div', container).hide();
        });

        container.on('change', '.discount_type', function () {
            const discount_type = jQuery('.discount_type', container).val();
            const discountSymbolSpan = jQuery('.discountSymbol', container);

            if (discount_type === 'Percentage') {
                discountSymbolSpan.text('%');
            } else {
                discountSymbolSpan.text(jQuery('#currency_symbol', container).val());
            }

            self.recalculateItem(container);
        });
    },

    registerLineItemAutoComplete: function (container) {
        const self = this;

        if (typeof container == 'undefined') {
            container = this.lineItemsHolder;
        }

        container.find('input.autoComplete').autocomplete({
            minLength: 0,
            source: function (request, response) {
                const params = {
                    'module': 'InventoryItem',
                    'action': 'PopupBasicAjax',
                };
                params.search_module = container.find('.lineItemPopup').data('moduleName');
                params.search_value = request.term;

                if (!params.search_module || params.search_module === 'Text') {
                    return false;
                }

                self.searchModuleNames(params).then(function (data) {
                    const responseDataList = [];
                    let serverDataFormat = data;

                    if (serverDataFormat.length <= 0 && !jQuery('#productid', container).val()) {
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
            select: function (event, ui) {
                const selectedItemData = ui.item;

                if (typeof selectedItemData.type != 'undefined' && selectedItemData.type === "no results") {
                    return false;
                }

                const dataUrl = "index.php?module=InventoryItem&action=GetItemDetails&record=" + selectedItemData.id + "&currency_id=" + self.getCurrencyId() + "&sourceModule=" + app.getModuleName() + "&pricebookid=" + jQuery('#pricebookid_original').val();
                app.request.get({'url': dataUrl}).then(
                    function (error, data) {
                        if (error == null) {
                            self.mapResultsToFields(container, data[0]);
                        }
                    },
                    function (error, err) {

                    }
                );
            }
        });

        container.find('input.autoComplete').on('focus', function () {
            if (!jQuery(this).val()) {
                jQuery(this).autocomplete('search', '');
            }
        });
    },

    mapResultsToFields: function (container, responseData) {
        const lineItemNameElement = container.find('input.item_text');
        const selectedRegionId = jQuery('#region_id_original').val();

        for (let id in responseData) {
            let recordData = responseData[id];
            jQuery('input.productid', container).val(id);
            jQuery('textarea.description', container).text(recordData.description);
            const editorInstance = CKEDITOR.instances[jQuery('textarea.description', container).attr('id')];

            if (editorInstance) {
                editorInstance.setData(recordData.description);
            }

            jQuery('input.quantity', container).val(1);
            jQuery('input.unit', container).val(recordData.unit);
            jQuery('input.price', container).val(parseFloat(recordData.listprice).toFixed(3)).trigger('change');
            jQuery('input.purchase_cost', container).val(recordData.purchaseCost);
            jQuery('div.display_purchase_cost', container).text(recordData.purchaseCost);
            jQuery('input.pricebookid', container).val(recordData.pricebookid);
            let taxElement = jQuery('select.tax', container);
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

                percentage = (percentage * 1).toFixed(app.getNumberOfDecimals());

                let option = jQuery('<option>', {
                    value: percentage,
                    'data-taxid': taxid,
                    text: tax.tax_label + ' (' + percentage + '%)'
                });
                taxElement.append(option);
            }

            taxElement.find('option:eq(1)').prop('selected', true);
            lineItemNameElement.val(recordData.name);
            this.recalculateItem(container);
        }
    },

    recalculateItem: function (container) {
        let quantity = parseFloat(jQuery('.quantity', container).val());
        let price = parseFloat(jQuery('.price', container).val());
        let decimalPlaces = app.getNumberOfDecimals();

        if (isNaN(quantity)) {
            quantity = 0.0;
        }

        if (isNaN(price)) {
            price = 0.0;
        }

        price = (quantity * price).toFixed(decimalPlaces);
        jQuery('.subtotal', container).val(price);
        jQuery('.display_subtotal', container).html(price);
        jQuery('.subtotal_in_discount_div', container).html(price);
        price = parseFloat(price);

        const discount_type = jQuery('.discount_type', container).val();
        let discount = parseFloat(jQuery('.discount', container).val());
        let discount_amount = 0;

        if (isNaN(discount)) {
            discount = 0;
        }

        if (discount_type === 'Percentage') {
            discount_amount = price * (discount / 100);
        } else if (discount_type === 'Direct') {
            discount_amount = discount;
        } else if (discount_type === 'Discount per Unit') {
            discount_amount = quantity * discount;
        } else {
            jQuery('.discount', container).val(0);
        }

        discount_amount = discount_amount.toFixed(decimalPlaces);
        jQuery('.discount_amount', container).val(discount_amount);
        jQuery('.display_discount_amount', container).html(discount_amount);
        discount_amount = parseFloat(discount_amount);

        price = (price - discount_amount).toFixed(decimalPlaces);
        jQuery('.price_after_discount', container).val(price);
        jQuery('.display_price_after_discount', container).html(price);
        price = parseFloat(price);

        const overall_discount = parseFloat(jQuery('.overall_discount', container).val());
        let overall_discount_amount = parseFloat(jQuery('.overall_discount_amount', container).val());

        if (overall_discount && overall_discount > 0) {
            overall_discount_amount = price * (overall_discount / 100);
        }

        if (isNaN(overall_discount_amount)) {
            overall_discount_amount = 0;
        }

        overall_discount_amount = overall_discount_amount.toFixed(decimalPlaces);
        jQuery('.overall_discount_amount', container).val(overall_discount_amount);
        jQuery('.display_overall_discount_amount', container).html(overall_discount_amount);
        overall_discount_amount = parseFloat(overall_discount_amount);

        price = (price - overall_discount_amount).toFixed(decimalPlaces);
        jQuery('.price_after_overall_discount', container).val(price);
        jQuery('.display_price_after_overall_discount', container).html(price);
        price = parseFloat(price);

        let purchaseCost = parseFloat(jQuery('.purchase_cost', container).val());
        let margin = 0;
        let margin_amount = 0;

        if (!isNaN(purchaseCost) && purchaseCost > 0 && price > 0) {
            margin_amount = (price - (purchaseCost * quantity)).toFixed(decimalPlaces);
            margin = ((parseFloat(margin_amount) / price) * 100).toFixed(decimalPlaces);
        }

        jQuery('.margin', container).val(margin);
        jQuery('.display_margin', container).html(margin);
        jQuery('.margin_amount', container).val(margin_amount);
        jQuery('.display_margin_amount', container).html(margin_amount);

        let tax = parseFloat(jQuery('.tax', container).val());

        if (!tax) {
            tax = 0;
        }

        let tax_amount = price * (tax / 100);

        if (isNaN(tax_amount)) {
            tax_amount = 0;
        }

        tax_amount = tax_amount.toFixed(decimalPlaces);
        jQuery('.tax_amount', container).val(tax_amount);
        jQuery('.display_tax_amount', container).html(tax_amount);
        tax_amount = parseFloat(tax_amount);

        price = (price + tax_amount).toFixed(decimalPlaces);
        jQuery('.price_total', container).val(price);
        jQuery('.display_price_total', container).html(price);
    },

    registerLineItemClear: function (container) {
        const self = this;

        jQuery('.clearLineItem', container).on('click', function (e) {
            self.clearLineItemDetails(container);
            e.preventDefault();
        });
    },

    clearLineItemDetails: function (container) {
        jQuery('input.form-control', container).val('');
        jQuery('input.allowOnlyNumbers', container).val(0);
        jQuery('input.productid', container).val('');
        let taxElement = jQuery('select.tax', container);
        taxElement.find('option:not(:first)').remove();
        this.recalculateItem(container);
    },

    registerLineItemPopup: function (container) {
        const self = this;

        jQuery('.lineItemPopup', container).on('click', function (e) {
            const triggerElement = jQuery(e.currentTarget);
            const popupReferenceModule = triggerElement.data('moduleName');
            self.showLineItemPopup({'item_module': popupReferenceModule});
            const postPopupHandler = function (e, data) {
                data = JSON.parse(data);

                if (!jQuery.isArray(data)) {
                    data = [data];
                }

                self.postLineItemSelectionActions(container, data, popupReferenceModule);
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
            'multi_select': false,
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

    registerPriceBookPopUp: function (container) {
        const self = this;

        jQuery('.choosePriceBook', container).on('click', function (e) {
            const productId = jQuery('#productid', container).val();

            let params = {
                'module': 'PriceBooks',
                'currency_id': self.getCurrencyId(),
                'src_record': productId,
                'view': 'Popup',
                'get_url': 'getProductListPriceURL',
                'src_field': 'productid',
                'src_module': 'Products',
                'search_params': '[[["active","e","1"]]]',
            };

            const popupInstance = Vtiger_Popup_Js.getInstance();
            popupInstance.showPopup(params, 'post.LineItemPriceBookSelect.click');

            const postPriceBookPopupHandler = function (e, data) {
                const responseData = JSON.parse(data);

                let listPrice = parseFloat(responseData.price).toFixed(3);
                let priceElement = container.find('.price');
                priceElement.val(listPrice);
                priceElement.trigger('change');
                let pricebookIdElement = container.find('.pricebookid');
                pricebookIdElement.val(responseData.pricebookid);
            };

            app.event.off('post.LineItemPriceBookSelect.click');
            app.event.one('post.LineItemPriceBookSelect.click', postPriceBookPopupHandler);
        });
    },

    loadCkEditor: function (container) {
        const cke = new Vtiger_CkEditor_Js();
        const descriptionElement = container.find('.description');

        if (container.find('.description').length > 0) {
            cke.loadCkEditor(descriptionElement, {
                'height': 100, toolbar: [
                    {name: 'document', items: ['Maximize']},
                    {name: 'styles', items: ['Format', 'FontSize']},
                    {name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike']},
                    {name: 'colors', items: ['TextColor', 'RemoveFormat']},
                    {name: 'paragraph', items: ['NumberedList', 'BulletedList']},
                    {name: 'alignment', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock']},
                    {name: 'links', items: ['Link']},
                    {name: 'source', items: ['Source']}
                ]
            });
        }
    },

    applyOverallDiscount: function (container) {
        const overallDiscountPercent = jQuery('#overall_discount_percent').val();

        if (isNaN(overallDiscountPercent) || parseFloat(overallDiscountPercent) === 0.0) {
            jQuery('.overall_discount', container).val(0).closest('div.full_row').hide();
        } else {
            jQuery('.overall_discount', container).val(overallDiscountPercent);
        }
    },
});

InventoryItem_InventoryItemDetail_Js_Instance = new InventoryItem_InventoryItemDetail_Js();