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
        this.registerEvents();
    },

    initializeVariables: function () {
        this.dummyLineItemRow = jQuery('#dummyItemRow');
        this.dummyTextRow = jQuery('#dummyTextRow');
        this.lineItemsHolder = jQuery('#lineItemTab');
        this.numOfLineItems = this.lineItemsHolder.find('.' + this.lineItemDetectingClass).length;
    },

    registerBasicEvents: function (container) {
        this._super(container);
        this.registerAddButtons();
        this.makeLineItemsSortable();
        this.addRowListeners();
    },

    registerAddButtons: function () {
        const self = this;

        const addTextLineHandler = function (e, data) {
            const currentTarget = jQuery(e.currentTarget);
            const params = {'currentTarget': currentTarget};
            let newTextLine = self.getNewTextItem(params);
            newTextLine = newTextLine.appendTo(self.lineItemsHolder);
            self.setupRowListeners(self.numOfLineItems);
            app.event.trigger('post.textLine.New', newTextLine);
        };

        const addLineItemEventHandler = function (e, data) {
            const currentTarget = jQuery(e.currentTarget);
            const params = {'currentTarget': currentTarget};
            let newLineItem = self.getNewLineItem(params);
            newLineItem = newLineItem.appendTo(self.lineItemsHolder);
            newLineItem.find('input.productName').addClass('autoComplete');
            newLineItem.find('.ignore-ui-registration').removeClass('ignore-ui-registration');
            vtUtils.applyFieldElementsView(newLineItem);
            app.event.trigger('post.lineItem.New', newLineItem);
            self.registerLineItemAutoComplete(newLineItem);
            self.setupRowListeners(self.numOfLineItems);

            if (typeof data != "undefined") {
                // self.mapResultsToFields(newLineItem, data);
            }
        };

        jQuery('#addProduct').on('click', addLineItemEventHandler);
        jQuery('#addService').on('click', addLineItemEventHandler);
        jQuery('#addText').on('click', addTextLineHandler);
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
        this.initializeLineItemRowCustomFields(newRow, this.numOfLineItems);

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
        this.initializeLineItemRowCustomFields(newRow, this.numOfLineItems);

        return newRow;
    },

    updateRowNumberForRow: function (lineItemRow, expectedSequenceNumber, currentSequenceNumber) {
        if (typeof currentSequenceNumber == 'undefined') {
            //by default there will zero current sequence number
            currentSequenceNumber = 0;
        }

        let idFields = [
                'item_text', 'subproduct_ids', 'productid', 'purchaseCost', 'margin', 'comment', 'quantity',
                'listPrice', 'discount_div', 'discount_type', 'discount_percentage',
                'discount_amount', 'lineItemType', 'searchIcon', 'netPrice', 'subprod_names',
                'productTotal', 'discountTotal', 'totalAfterDiscount', 'taxTotal'
            ],
            classFields = [
                'taxPercentage'
            ];

        //To handle variable tax ids
        for (let classIndex in classFields) {
            let className = classFields[classIndex];
            jQuery('.' + className, lineItemRow).each(function (index, domElement) {
                let idString = domElement.id;
                //remove last character which will be the row number
                idFields.push(idString.slice(0, (idString.length - currentSequenceNumber.length)));
            });
        }

        let expectedRowId = 'row' + expectedSequenceNumber;

        for (let idIndex in idFields) {
            let elementId = idFields[idIndex],
                actualElementId = elementId + currentSequenceNumber,
                expectedElementId = elementId + expectedSequenceNumber;

            lineItemRow.find('#' + actualElementId).attr('id', expectedElementId)
                .filter('[name="' + actualElementId + '"]').attr('name', expectedElementId);
        }

        let nameFields = [
            'discount', 'purchaseCost', 'margin'
        ];

        for (let nameIndex in nameFields) {
            let elementName = nameFields[nameIndex],
                actualElementName = elementName + currentSequenceNumber,
                expectedElementName = elementName + expectedSequenceNumber;

            lineItemRow.find('[name="' + actualElementName + '"]').attr('name', expectedElementName);
        }

        lineItemRow.attr('id', expectedRowId).attr('data-row-num', expectedSequenceNumber);
        lineItemRow.find('input.rowNumber').val(expectedSequenceNumber);

        return lineItemRow;
    },

    initializeLineItemRowCustomFields: function (lineItemRow, rowNum) {
        const lineItemType = lineItemRow.find('input.lineItemType').val();

        for (let cfName in this.customLineItemFields) {
            let elementName = cfName + rowNum;
            let element = lineItemRow.find('[name="' + elementName + '"]');
            let cfDataType = this.customLineItemFields[cfName];

            if (cfDataType === 'picklist' || cfDataType === 'multipicklist') {
                if (cfDataType === 'multipicklist') {
                    element = lineItemRow.find('[name="' + elementName + '[]"]');
                }

                let picklistValues = element.data('productPicklistValues');
                let options = '';

                if (lineItemType === 'Services') {
                    picklistValues = element.data('servicePicklistValues');
                }

                if (cfDataType === 'picklist') {
                    options = '<option value="">' + app.vtranslate('JS_SELECT_OPTION') + '</option>';
                }

                for (let picklistName in picklistValues) {
                    let pickListValue = picklistValues[picklistName];
                    options += '<option value="' + picklistName + '">' + pickListValue + '</option>';
                }

                element.html(options);
                element.addClass('select2');
            }

            let defaultValueInfo = this.customFieldsDefaultValues[cfName];

            if (defaultValueInfo) {
                let defaultValue = defaultValueInfo;

                if (typeof defaultValueInfo == 'object') {
                    defaultValue = defaultValueInfo.productFieldDefaultValue;

                    if (lineItemType === 'Services') {
                        defaultValue = defaultValueInfo.serviceFieldDefaultValue;
                    }
                }

                if (cfDataType === 'multipicklist') {
                    if (defaultValue.length > 0) {
                        defaultValue = defaultValue.split(" |##| ");
                        let setDefaultValue = function (picklistElement, values) {
                            for (let index in values) {
                                let picklistVal = values[index];
                                picklistElement.find('option[value="' + picklistVal + '"]').prop('selected', true);
                            }
                        };
                        setDefaultValue(element, defaultValue);
                    }
                } else {
                    element.val(defaultValue);
                }
            } else {
                element.val('');
            }
        }

        return lineItemRow;
    },

    makeLineItemsSortable: function () {
        jQuery('#lineItemTab tbody').sortable({
            handle: '.drag_drop_line_item',
            update: function (event, ui) {
                const newOrder = jQuery(this).sortable('toArray');
                const data = [];

                jQuery.each(newOrder, function (index, value) {
                    if (value != '') {
                        jQuery('#' + value).find('input.rowSequence').val(index);
                        data.push({
                            'id': jQuery('#' + value).find('input.lineItemId').val(),
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
                    console.log(res);
                });
            }
        });
    },

    addRowListeners: function () {
        const self = this;
        $('.lineItemRow').each(function () {
            const rowNum = $(this).data('row-num');
            self.setupRowListeners(rowNum);
        });
    },

    setupRowListeners: function (rowNum) {
        const self = this;
        const row = $(`#row${rowNum}`);

        // Use event delegation for all input events within the row
        row.on('change blur', 'input, select, textarea', function () {
            const element = $(this);
            // Don't save on hidden input changes unless specifically needed
            if (!element.is(':hidden') || element.hasClass('saveOnChange')) {
                self.saveProductLine(rowNum);
            }
        });
    },

    saveProductLine: function (rowNum) {
        const row = $(`#row${rowNum}`);
        const data = this.serializeRow(row);

        // Check if the row has any non-empty values
        const hasContent = Object.values(data).some(value => value !== '' && value != null);

        if (hasContent) {
            $.ajax({
                url: 'index.php',
                method: 'POST',
                data: {
                    rowNum: rowNum,
                    module: 'InventoryItem',
                    action: 'SaveProductLine',
                    data: data
                },
                success: function (response) {
                    if (response.newLineItemId) {
                        row.find('[name="lineItemId"]').val(response.newLineItemId);
                    }

                    row.trigger('lineSaved', [response]);
                },
                error: function (xhr, status, error) {
                    row.trigger('lineErrorSaving', [error]);
                }
            });
        }
    },

    serializeRow: function (row) {
        let data = {};
        row.find('input, select, textarea').each(function () {
            const element = $(this);
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
                //To stop selection if no results is selected
                if (typeof selectedItemData.type != 'undefined' && selectedItemData.type == "no results") {
                    return false;
                }
                const element = jQuery(this);
                element.attr('disabled', 'disabled');
                const tdElement = element.closest('td');
                const selectedModule = tdElement.find('.lineItemPopup').data('moduleName');
                const dataUrl = "index.php?module=Inventory&action=GetTaxes&record=" + selectedItemData.id + "&currency_id=" + jQuery('#currency_id option:selected').val() + "&sourceModule=" + app.getModuleName();
                app.request.get({'url': dataUrl}).then(
                    function (error, data) {
                        if (error == null) {
                            const itemRow = self.getClosestLineItemRow(element);
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

});

InventoryItem_InventoryItemDetail_Js_Instance = new InventoryItem_InventoryItemDetail_Js();