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
        this.dummyLineItemRow = jQuery('#row0');
        this.lineItemsHolder = jQuery('#lineItemTab');
        this.numOfLineItems = this.lineItemsHolder.find('.' + this.lineItemDetectingClass).length;
    },

    registerBasicEvents: function (container) {
        this._super(container);
        this.registerAddProductService();
    },

    registerAddProductService: function () {
        const self = this;
        const addLineItemEventHandler = function (e, data) {
            const currentTarget = jQuery(e.currentTarget);
            const params = {'currentTarget': currentTarget};
            console.log(params);
            let newLineItem = self.getNewLineItem(params);
            console.log(newLineItem);
            /*newLineItem = newLineItem.appendTo(self.lineItemsHolder);
            newLineItem.find('input.productName').addClass('autoComplete');
            newLineItem.find('.ignore-ui-registration').removeClass('ignore-ui-registration');
            vtUtils.applyFieldElementsView(newLineItem);
            app.event.trigger('post.lineItem.New', newLineItem);
            self.checkLineItemRow();
            self.registerLineItemAutoComplete(newLineItem);
            if (typeof data != "undefined") {
                self.mapResultsToFields(newLineItem, data);
            }*/
        };

        jQuery('#addProduct').on('click', addLineItemEventHandler);
        jQuery('#addService').on('click', addLineItemEventHandler);
        jQuery('#addText').on('click', addLineItemEventHandler);
    },

    getLineItemSetype: function (row) {
        return row.find('.lineItemType').val();
    },

    getNewLineItem: function (params) {
        let currentTarget = params.currentTarget,
            itemType = currentTarget.data('moduleName'),
            newRow = this.dummyLineItemRow.clone(true).removeClass('hide').addClass(this.lineItemDetectingClass).removeClass('lineItemCloneCopy');

        newRow.find('.individualTaxContainer').removeClass('opacity-0');
        newRow.find('.lineItemPopup').filter(':not([data-module-name="' + itemType + '"])').remove();
        newRow.find('.lineItemType').val(itemType);
        ++this.numOfLineItems;
        this.updateRowNumberForRow(newRow, ++this.numOfLineItems);
        this.initializeLineItemRowCustomFields(newRow, ++this.numOfLineItems);

        return newRow;
    },

    updateRowNumberForRow: function (lineItemRow, expectedSequenceNumber, currentSequenceNumber) {
        if (typeof currentSequenceNumber == 'undefined') {
            //by default there will zero current sequence number
            currentSequenceNumber = 0;
        }

        let idFields = [
                'productName', 'subproduct_ids', 'hdnProductId', 'purchaseCost', 'margin', 'comment', 'qty',
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

});

InventoryItem_InventoryItemDetail_Js_Instance = new InventoryItem_InventoryItemDetail_Js();