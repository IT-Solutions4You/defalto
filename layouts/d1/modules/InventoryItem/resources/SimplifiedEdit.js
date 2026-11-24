/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** @var InventoryItem_SimplifiedEdit_Js */
Vtiger.Class('InventoryItem_SimplifiedEdit_Js', {}, {
    container: null,

    parseNumber: function (value) {
        const number = parseFloat(String(value).replace(',', '.').replace(/\s/g, ''));

        return isNaN(number) ? 0 : number;
    },

    formatNumber: function (value) {
        return app.convertCurrencyToUserFormat(value.toFixed(app.getNumberOfDecimals()));
    },

    recalculateRow: function (row) {
        // A text row has no quantity, price or total
        if (!row.attr('data-module')) {
            return;
        }

        const quantity = this.parseNumber(row.find('.simplifiedItemQuantity').val());
        const price = this.parseNumber(row.find('.simplifiedItemPrice').val());
        const total = Math.round(quantity * price * 100) / 100;

        row.find('.simplifiedItemTotal').data('total', total).text(this.formatNumber(total));
    },

    recalculateTotal: function () {
        const self = this;
        let netTotal = 0;
        let taxTotal = 0;

        this.container.find('.simplifiedItemRow:not(.d-none)').each(function () {
            const row = jQuery(this);
            const total = parseFloat(row.find('.simplifiedItemTotal').data('total')) || 0;
            const taxPercentage = self.parseNumber(row.find('.simplifiedItemTax').val());

            netTotal += total;
            taxTotal += Math.round(total * taxPercentage) / 100;
        });

        taxTotal = Math.round(taxTotal * 100) / 100;

        this.container.find('.simplifiedItemsNetTotal').text(self.formatNumber(netTotal));
        this.container.find('.simplifiedItemsTaxTotal').text(self.formatNumber(taxTotal));
        // Total with the tax, or the total of the rows when the company does not use taxes
        this.container.find('.simplifiedItemsGrandTotal').text(self.formatNumber(netTotal + taxTotal));
    },

    recalculateAll: function () {
        const self = this;

        this.container.find('.simplifiedItemRow').each(function () {
            self.recalculateRow(jQuery(this));
        });
        this.recalculateTotal();
    },

    /**
     * @param {string} moduleName Products or Services, empty for a text row
     * @param {string} label name of the row type shown as a placeholder
     */
    addRow: function (moduleName, label) {
        const nextIndexElement = jQuery('#simplifiedItemsNextIndex');
        const index = parseInt(nextIndexElement.val(), 10);
        const template = moduleName ? '#simplifiedItemRowTemplate' : '#simplifiedTextRowTemplate';
        const html = jQuery(template).html().replace(/__INDEX__/g, index);
        const row = jQuery(html);

        nextIndexElement.val(index + 1);
        row.attr('data-module', moduleName || '');

        if (label) {
            row.find('.simplifiedItemText').attr('placeholder', label);
        }

        this.container.find('tbody').append(row);
        this.registerSelect2(row);
        this.registerAutoComplete(row);
        this.recalculateRow(row);
        this.recalculateTotal();
        row.find('.simplifiedItemText').trigger('focus');
    },

    /**
     * The rows of a product or service must be selected from the list, the same as in the standard block
     *
     * @return {boolean} true when all the rows are selected
     */
    validateSelectedItems: function () {
        const emptyRow = this.container.find('.simplifiedItemRow:not(.d-none)').filter(function () {
            const row = jQuery(this);

            return row.attr('data-module') && !(parseInt(row.find('.simplifiedItemProductId').val(), 10) > 0);
        }).first();

        if (!emptyRow.length) {
            return true;
        }

        emptyRow.find('.simplifiedItemText').addClass('is-invalid').trigger('focus');
        app.helper.showErrorNotification({message: this.container.data('selectMessage')});

        return false;
    },

    /**
     * Unit and tax selects look the same as the selects of the rest of the system
     */
    registerSelect2: function (row) {
        vtUtils.showSelect2ElementView(row.find('select.select2'), {width: '100%'});
    },

    getFormValue: function (name) {
        const element = jQuery('#EditView').find('[name="' + name + '"]');

        if (element.length) {
            return element.val();
        }

        return jQuery('#' + name + '_original').val();
    },

    /**
     * Ajax autocomplete of the product or service the row is for, the same as in the standard product block
     */
    registerAutoComplete: function (row) {
        const self = this;
        const moduleName = row.attr('data-module');
        const input = row.find('.simplifiedItemText');

        if (!moduleName || !input.length) {
            return;
        }

        input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                const params = {
                    module: 'InventoryItem',
                    action: 'PopupBasicAjax',
                    base_record: jQuery('#EditView').find('[name="record"]').val() || '',
                    base_module: app.getModuleName(),
                    currency_id: self.getFormValue('currency_id'),
                    pricebookid: self.getFormValue('pricebookid'),
                    search_module: moduleName,
                    search_value: request.term,
                };

                app.request.post({data: params}).then(function (error, data) {
                    let results = error === null && Array.isArray(data) ? data : [];

                    if (!results.length) {
                        results = [{label: app.vtranslate('JS_NO_RESULTS_FOUND'), type: 'no results'}];
                    }

                    response(results);
                });
            },
            select: function (event, ui) {
                if (ui.item.type === 'no results') {
                    return false;
                }

                self.loadItemDetails(row, ui.item);

                // The label of the list contains markup, the row gets the plain name of the record
                return false;
            },
        }).data('ui-autocomplete')._renderItem = function (ul, item) {
            if (item && item.type === 'no results') {
                return jQuery('<li>').addClass('ui-state-disabled').append('<div>' + (item.label || '') + '</div>').appendTo(ul);
            }

            return jQuery('<li>').append('<div>' + item.label + '</div>').appendTo(ul);
        };

        input.on('focus', function () {
            if (!jQuery(this).val()) {
                jQuery(this).autocomplete('search', '');
            }
        });
    },

    loadItemDetails: function (row, item) {
        const self = this;
        const params = {
            module: 'InventoryItem',
            action: 'GetItemDetails',
            record: item.id,
            currency_id: self.getFormValue('currency_id'),
            sourceModule: app.getModuleName(),
            pricebookid: self.getFormValue('pricebookid'),
        };

        app.request.post({data: params}).then(function (error, data) {
            if (error !== null || !data || !data[0]) {
                return;
            }

            const details = data[0][item.id];

            if (details) {
                self.fillRow(row, item.id, details);
            }
        });
    },

    fillRow: function (row, productId, details) {
        const quantityElement = row.find('.simplifiedItemQuantity');

        row.find('.simplifiedItemProductId').val(productId);
        row.find('.simplifiedItemText').val(details.name);
        row.find('.simplifiedItemPrice').val(details.listprice);

        if (!this.parseNumber(quantityElement.val())) {
            quantityElement.val(details.quantity);
        }

        this.selectUnit(row.find('.simplifiedItemUnit'), details.unit);
        this.fillTaxes(row, details.taxes);
        row.find('.simplifiedItemText').removeClass('is-invalid');
        this.recalculateRow(row);
        this.recalculateTotal();
    },

    /**
     * Taxes of the selected product or service, the first one is selected. The percentage depends on the tax region of the document.
     *
     * @param {jQuery} row
     * @param {Object} taxes by tax id
     */
    fillTaxes: function (row, taxes) {
        const select = row.find('.simplifiedItemTax');

        if (!select.length) {
            return;
        }

        const regionId = this.getFormValue('region_id');

        select.find('option:not(:first)').remove();

        jQuery.each(taxes || {}, function (taxId, tax) {
            let percentage = tax.percentage;
            const regions = tax.regions ? JSON.parse(tax.regions) : {};

            if (regionId && regions[regionId] !== undefined) {
                percentage = regions[regionId];
            }

            percentage = (percentage * 1).toFixed(app.getNumberOfDecimals());

            jQuery('<option>', {
                value: percentage,
                'data-taxid': taxId,
                text: tax.tax_label + ' (' + percentage + '%)',
            }).appendTo(select);
        });

        select.find('option:eq(1)').prop('selected', true);
        row.find('.simplifiedItemTaxId').val(select.find('option:selected').data('taxid') || 0);
        // The select2 view shows the new options and the selected one
        select.trigger('change.select2');
    },

    /**
     * @param {jQuery} select
     * @param {string} label unit as shown to the user, the server converts it back to the stored value
     */
    selectUnit: function (select, label) {
        if (!label) {
            return;
        }

        let option = select.find('option').filter(function () {
            return jQuery(this).val() === label || jQuery(this).text() === label;
        }).first();

        if (!option.length) {
            option = jQuery('<option>').val(label).text(label).appendTo(select);
        }

        select.val(option.val()).trigger('change.select2');
    },

    removeRow: function (row) {
        if (parseInt(row.find('input[name$="[id]"]').val(), 10) > 0) {
            // Saved item: the form tells the server to delete it
            row.find('.simplifiedItemDeleted').val(1);
            row.find('input:not([type="hidden"]), select').prop('disabled', true).removeAttr('data-rule-required');
            row.addClass('d-none');
        } else {
            row.remove();
        }

        this.recalculateTotal();
    },

    registerEvents: function () {
        const self = this;

        this.container = jQuery('.simplifiedItemsBlock');

        if (!this.container.length) {
            return;
        }

        this.container.on('input', '.simplifiedItemQuantity, .simplifiedItemPrice', function () {
            self.recalculateRow(jQuery(this).closest('.simplifiedItemRow'));
            self.recalculateTotal();
        });

        this.container.on('change', '.simplifiedItemTax', function () {
            const select = jQuery(this);

            select.closest('.simplifiedItemRow').find('.simplifiedItemTaxId').val(select.find('option:selected').data('taxid') || 0);
            self.recalculateTotal();
        });

        this.container.on('click', '.removeSimplifiedItem', function () {
            self.removeRow(jQuery(this).closest('.simplifiedItemRow'));
        });

        this.container.on('click', '.addSimplifiedItem', function () {
            const button = jQuery(this);

            self.addRow(button.data('modulename'), button.data('label'));
        });

        this.container.find('.simplifiedItemRow').each(function () {
            self.registerSelect2(jQuery(this));
            self.registerAutoComplete(jQuery(this));
        });

        // The form is not sent while a product or service row is not selected from the list
        document.addEventListener('submit', function (event) {
            if (event.target && event.target.id === 'EditView' && !self.validateSelectedItems()) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);

        this.recalculateAll();
    },
});

document.addEventListener('DOMContentLoaded', function () {
    new InventoryItem_SimplifiedEdit_Js().registerEvents();
});
