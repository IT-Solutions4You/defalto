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

    parseAmount: function (value) {
        let text = String(value || '').split(app.getGroupingSeparator()).join('');
        text = text.replace(app.getDecimalSeparator(), '.').replace(/[^0-9.-]/g, '');
        const amount = parseFloat(text);

        return isNaN(amount) ? 0 : amount;
    },

    getPaymentRate: function () {
        return parseFloat(this.modal.find('.paymentBulkCurrency option:selected').data('rate')) || 1;
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

        this.modal.find('.paymentBulkAmount').val(app.convertCurrencyToUserFormat(total));
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

            row.find('.paymentBulkPart').val(app.convertCurrencyToUserFormat(part));
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

        label.text(label.data('label') + ': ' + app.convertCurrencyToUserFormat(remaining));
    },

    save: function (listInstance) {
        const self = this,
            form = this.modal.find('.paymentBulkForm');

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
