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
        const subtotal = Math.round(quantity * price * 100) / 100;
        // The overall discount of the document is not editable here, but the server applies it to every item
        const overallDiscount = this.parseNumber(this.getFormValue('overall_discount'));
        const afterDiscount = subtotal - this.getItemDiscount(row, quantity, subtotal);
        const net = overallDiscount > 0 ? afterDiscount - Math.round(afterDiscount * overallDiscount) / 100 : afterDiscount;
        const tax = Math.round(net * this.parseNumber(row.find('.simplifiedItemTax').val())) / 100;
        const total = Math.round((net + tax) * 100) / 100;

        // The total of the row contains the tax, the same as the price total of the item in the standard block
        row.data('net', net).data('tax', tax);
        row.find('.simplifiedItemTotal').data('total', total).text(this.formatNumber(total));
    },

    /**
     * Discount of the item, set in the standard block. It is not editable here, but it is part of the price of the item.
     */
    getItemDiscount: function (row, quantity, subtotal) {
        const discount = this.parseNumber(row.attr('data-discount'));

        switch (row.attr('data-discount-type')) {
            case 'Percentage':
                return subtotal * discount / 100;
            case 'Direct':
                return this.parseNumber(row.attr('data-discount-amount'));
            case 'Discount per Unit':
                return quantity * discount;
        }

        return 0;
    },

    recalculateTotal: function () {
        const self = this;
        let netTotal = 0;
        let taxTotal = 0;

        this.container.find('.simplifiedItemRow:not(.d-none)').each(function () {
            const row = jQuery(this);

            netTotal += parseFloat(row.data('net')) || 0;
            taxTotal += parseFloat(row.data('tax')) || 0;
        });

        taxTotal = Math.round(taxTotal * 100) / 100;

        this.container.find('.simplifiedItemsNetTotal').text(self.formatNumber(netTotal));
        this.container.find('.simplifiedItemsTaxTotal').text(self.formatNumber(taxTotal));
        // Total with the tax, or the total of the rows when the company does not use taxes
        this.container.find('.simplifiedItemsGrandTotal').text(self.formatNumber(netTotal + taxTotal));
        // Grand total: the total with the tax and the adjustment, the same as the server saves it
        this.container.find('.simplifiedItemsFinalTotal').text(self.formatNumber(netTotal + taxTotal + self.parseNumber(self.getFormValue('adjustment'))));
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
        const template = moduleName ? '#simplifiedItemRowTemplate' + moduleName : '#simplifiedTextRowTemplate';
        const html = jQuery(template).html().replace(/__INDEX__/g, index);
        const row = jQuery(jQuery.trim(html));

        nextIndexElement.val(index + 1);
        row.attr('data-module', moduleName || '');

        if (label) {
            row.find('.simplifiedItemText').attr('placeholder', label);
        }

        // Before the totals
        this.container.find('tfoot').before(row);
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
     * The tax select looks the same as the selects of the rest of the system
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
        // Not visible: the purchase cost of the product and the price book the price comes from, both are saved with the item
        row.find('.simplifiedItemPurchaseCost').val(details.purchaseCost === undefined ? '' : details.purchaseCost);
        row.find('.simplifiedItemPriceBookId').val(details.pricebookid || 0);
        row.find('.simplifiedItemDescription').val(details.description || '');

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
     * @param {string|number} [keepTaxId] tax which stays selected when the product still has it, the first one is selected otherwise
     */
    fillTaxes: function (row, taxes, keepTaxId) {
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

        const kept = keepTaxId ? select.find('option').filter(function () {
            return String(jQuery(this).data('taxid')) === String(keepTaxId);
        }).first() : jQuery();

        (kept.length ? kept : select.find('option:eq(1)')).prop('selected', true);
        row.find('.simplifiedItemTaxId').val(select.find('option:selected').data('taxid') || 0);
        // The select2 view shows the new options and the selected one
        select.trigger('change.select2');
    },

    /**
     * @param {jQuery} input
     * @param {string} label unit as shown to the user, the server converts it back to the stored value
     */
    selectUnit: function (input, label) {
        if (label) {
            input.val(label);
        }
    },

    /**
     * Currency, tax region and price book of the document, the same as in the standard block: the prices and the taxes of the items
     * which are selected from a product or service are loaded again for the new value.
     */
    registerSettings: function () {
        const self = this;
        const currency = jQuery('#simplifiedCurrency');
        const region = jQuery('#simplifiedRegion');
        const priceBook = jQuery('#simplifiedPriceBook');

        this.registerSettingMenus();
        this.filterPriceBooks();

        const onChange = function (select, message) {
            select.on('change', function () {
                const original = String(select.data('original'));

                if (select.val() === original) {
                    return;
                }

                const apply = function () {
                    select.data('original', select.val());
                    self.filterPriceBooks();
                    self.reloadItems();
                };
                const rows = self.getProductRows();

                if (!rows.length) {
                    apply();

                    return;
                }

                app.helper.showConfirmationBox({message: app.vtranslate(message)}).then(apply, function () {
                    select.val(original).trigger('change.select2');
                });
            });
        };

        onChange(currency, 'JS_CONFIRM_CURRENCY_CHANGE');
        onChange(region, 'JS_CONFIRM_TAXES_AND_CHARGES_REPLACE');
        onChange(priceBook, 'JS_CONFIRM_PRICEBOOK_CHANGE');
    },

    /**
     * The buttons in the header (as in the standard block) show the value of the hidden select and change it
     */
    registerSettingMenus: function () {
        const container = this.container;

        container.find('.simplifiedSettingMenu').each(function () {
            const menu = jQuery(this);
            const select = jQuery(menu.data('select'));
            const button = menu.siblings('.simplifiedSettingButton');
            const sync = function () {
                const value = String(select.val());

                button.text(select.find('option:selected').text());
                menu.find('a.dropdown-item[data-value]').each(function () {
                    const item = jQuery(this);
                    const hidden = item.data('currency') !== undefined && String(item.data('currency')) !== String(container.find('#simplifiedCurrency').val());

                    item.closest('li').toggleClass('d-none', hidden);
                    item.toggleClass('current', String(item.data('value')) === value);
                });
            };

            select.on('change.select2', sync);
            container.find('#simplifiedCurrency').on('change.select2', sync);
            menu.on('click', 'a.dropdown-item[data-value]', function () {
                select.val(String(jQuery(this).data('value'))).trigger('change');
            });
            sync();
        });
    },

    /**
     * Only the price books of the currency of the document can be selected
     */
    filterPriceBooks: function () {
        const priceBook = jQuery('#simplifiedPriceBook');
        const currencyId = String(this.getFormValue('currency_id'));

        if (!priceBook.length) {
            return;
        }

        priceBook.find('option[data-currency]').each(function () {
            const option = jQuery(this);
            const visible = !currencyId || String(option.data('currency')) === currencyId;

            option.prop('disabled', !visible);
        });

        if (priceBook.find('option:selected').prop('disabled')) {
            priceBook.val('0').data('original', '0');
        }

        priceBook.trigger('change.select2');
    },

    getProductRows: function () {
        return this.container.find('.simplifiedItemRow:not(.d-none)').filter(function () {
            return parseInt(jQuery(this).find('.simplifiedItemProductId').val(), 10) > 0;
        });
    },

    /**
     * Prices, purchase costs and taxes of all the selected products and services for the current currency, price book and tax region
     */
    reloadItems: function () {
        const self = this;

        this.getProductRows().each(function () {
            const row = jQuery(this);
            const productId = row.find('.simplifiedItemProductId').val();
            const params = {
                module: 'InventoryItem',
                action: 'GetItemDetails',
                record: productId,
                currency_id: self.getFormValue('currency_id'),
                sourceModule: app.getModuleName(),
                pricebookid: self.getFormValue('pricebookid'),
            };

            app.request.post({data: params}).then(function (error, data) {
                const details = !error && data && data[0] ? data[0][productId] : null;

                if (!details) {
                    return;
                }

                // The text, the quantity, the unit and the description stay as the user wrote them
                row.find('.simplifiedItemPrice').val(details.listprice);
                row.find('.simplifiedItemPurchaseCost').val(details.purchaseCost === undefined ? '' : details.purchaseCost);
                row.find('.simplifiedItemPriceBookId').val(details.pricebookid || 0);
                self.fillTaxes(row, details.taxes, row.find('.simplifiedItemTaxId').val());
                self.recalculateRow(row);
                self.recalculateTotal();
            });
        });
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

        this.container.on('input change', '[name="adjustment"]', function () {
            self.recalculateTotal();
        });

        this.container.on('change', '.simplifiedItemTax', function () {
            const select = jQuery(this);

            const row = select.closest('.simplifiedItemRow');

            row.find('.simplifiedItemTaxId').val(select.find('option:selected').data('taxid') || 0);
            self.recalculateRow(row);
            self.recalculateTotal();
        });

        // The description is one line, it grows to 4 lines while the user writes in it
        this.container.on('focus', '.simplifiedItemDescription', function () {
            jQuery(this).attr('rows', 4);
        });
        this.container.on('blur', '.simplifiedItemDescription', function () {
            jQuery(this).attr('rows', 1);
        });

        this.registerSettings();

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
