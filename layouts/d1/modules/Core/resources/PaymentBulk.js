/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Bulk payment of documents selected in a list. The list action (Core_PaymentConfig_Helper::getBulkPaymentUrl())
 * opens the window of Core_PaymentBulk_View. The amount is split over the documents in their order, documents
 * marked Pay Off first, each up to its open amount converted into the payment currency; the rest stays not split
 * and the save adds it to the last document. Loaded on every page as a header script of the payment modules.
 */
/** @var Core_PaymentBulk_Js */
jQuery.Class('Core_PaymentBulk_Js', {
    /**
     * Opens the bulk payment window for the selected records of the current list.
     * @param {string} paymentModule Payment module that pays the documents
     * @param {string} documentModule Module of the list
     */
    open: function (paymentModule, documentModule) {
        const listInstance = app.controller(),
            selection = listInstance.getListSelectAllParams(true);

        if (!selection) {
            listInstance.noRecordSelectedAlert();

            return;
        }

        const data = jQuery.extend(listInstance.getDefaultParams(), selection, {
            module: paymentModule,
            view: 'PaymentBulk',
            source_module: documentModule
        });

        delete data.parent;
        app.helper.showProgress();
        app.request.get({data: data}).then(function (error, html) {
            app.helper.hideProgress();

            if (error === null && html) {
                app.helper.showModal(html, {
                    cb: function (modal) {
                        new Core_PaymentBulk_Js().register(modal, listInstance);
                    }
                });
            }
        });
    }
}, {
    modal: null,

    /**
     * Reads an amount typed in the window: a plain number like in the edit forms (replaceCommaWithDot), so a comma
     * is the decimal separator too and spaces are ignored.
     * @param {string} value
     * @returns {number}
     */
    parseAmount: function (value) {
        const text = String(value || '').replace(/\s/g, '').replace(',', '.').replace(/[^0-9.-]/g, ''),
            amount = parseFloat(text);

        return isNaN(amount) ? 0 : amount;
    },

    /**
     * Returns the rate of the payment currency chosen in the currency menu of the amount.
     * @returns {number}
     */
    getPaymentRate: function () {
        const currencyId = this.modal.find('.paymentBulkCurrency').val();

        return parseFloat(this.modal.find('.amountCurrencyOption[data-value="' + currencyId + '"]').data('rate')) || 1;
    },

    /**
     * Formats an amount for the inputs of the window like the edit forms: a dot as the decimal separator, no
     * grouping (the save reads the values with CurrencyField::convertToDBFormat(), which does not remove grouping
     * separators, so "2 288.55" was saved as 2) and the user's number of decimals; trailing decimal zeros are
     * dropped when the user truncates them, so 6094.5100 shows as 6094.51.
     * @param {number} amount
     * @returns {string}
     */
    formatAmount: function (amount) {
        const decimals = parseInt(app.getNumberOfDecimals()),
            value = (Number(amount) || 0).toFixed(isNaN(decimals) ? 2 : decimals);

        if (_USERMETA.truncateTrailingZeros != '1' || value.indexOf('.') === -1) {
            return value;
        }

        return value.replace(/0+$/, '').replace(/\.$/, '');
    },

    /**
     * Rewrites the typed amounts as plain numbers before the save, e.g. a comma as the decimal separator.
     */
    normalizeAmounts: function () {
        const self = this;

        this.modal.find('.paymentBulkAmount, .paymentBulkPart').each(function () {
            const input = jQuery(this);

            if (input.val().trim() !== '') {
                input.val(self.formatAmount(self.parseAmount(input.val())));
            }
        });
    },

    /**
     * Returns the open amount of a document row in the payment currency.
     * @param {jQuery} row
     * @returns {number}
     */
    getOpenAmount: function (row) {
        const documentRate = parseFloat(row.data('rate')) || 1;

        return (parseFloat(row.data('open')) || 0) / documentRate * this.getPaymentRate();
    },

    /**
     * Fills the amount with the sum of the open amounts in the payment currency.
     */
    fillAmount: function () {
        const self = this;
        let total = 0;

        this.modal.find('.paymentBulkRow').each(function () {
            total += self.getOpenAmount(jQuery(this));
        });

        this.modal.find('.paymentBulkAmount').val(this.formatAmount(total));
    },

    /**
     * Splits the amount: Pay Off rows first, then the others in their order, each up to its open amount.
     */
    distribute: function () {
        const self = this,
            payOffRows = [],
            otherRows = [];
        let remaining = this.parseAmount(this.modal.find('.paymentBulkAmount').val());

        // jQuery add() would return the rows in page order, so the Pay Off rows are collected in arrays
        this.modal.find('.paymentBulkRow').each(function () {
            const row = jQuery(this);

            (row.find('.paymentBulkPayOff').is(':checked') ? payOffRows : otherRows).push(row);
        });

        jQuery.each(payOffRows.concat(otherRows), function (index, row) {
            const part = Math.max(0, Math.min(remaining, self.getOpenAmount(row)));

            row.find('.paymentBulkPart').val(self.formatAmount(part));
            remaining -= part;
        });

        this.updateRemaining();
    },

    updateRemaining: function () {
        const self = this,
            label = this.modal.find('.paymentBulkRemaining');
        let remaining = this.parseAmount(this.modal.find('.paymentBulkAmount').val());

        this.modal.find('.paymentBulkPart').each(function () {
            remaining -= self.parseAmount(jQuery(this).val());
        });

        label.text(label.data('label') + ': ' + this.formatAmount(remaining));
    },

    save: function (listInstance) {
        const self = this,
            form = this.modal.find('.paymentBulkForm');

        this.normalizeAmounts();
        app.helper.showProgress();
        app.request.post({data: form.serializeFormData()}).then(function (error, data) {
            app.helper.hideProgress();

            if (error !== null) {
                app.helper.showErrorNotification({message: error.message || error});

                return;
            }

            app.helper.hideModal();
            app.helper.showSuccessNotification({message: data.message});

            if (listInstance && typeof listInstance.loadListViewRecords === 'function') {
                listInstance.loadListViewRecords();
            }
        });
    },

    /**
     * Registers the events of the opened window.
     * @param {jQuery} modal
     * @param {Object} listInstance List controller to reload after the save
     */
    register: function (modal, listInstance) {
        const self = this;

        this.modal = modal;

        if (!modal.find('.paymentBulkRow').length) {
            return;
        }

        vtUtils.applyFieldElementsView(modal);
        this.fillAmount();
        this.distribute();

        modal.on('change', '.paymentBulkAmount, .paymentBulkCurrency, .paymentBulkPayOff', function (event) {
            if (jQuery(event.target).hasClass('paymentBulkCurrency')) {
                self.fillAmount();
            }

            self.distribute();
        });
        modal.on('input', '.paymentBulkPart', function () {
            self.updateRemaining();
        });
        modal.on('submit', '.paymentBulkForm', function (event) {
            event.preventDefault();
            self.save(listInstance);
        });
    }
});
