/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Split of a payment over several documents in the full payment form. Below the Reference field it lists the
 * documents with the amount of this payment for each, suggested by Core_PaymentAllocation_Action (by due date) and
 * editable; the inputs payment_allocation[document id] are saved by Core_PaymentSave_Action.
 */
/** @var Core_PaymentAllocation_Js */
jQuery.Class('Core_PaymentAllocation_Js', {}, {
    form: null,
    container: null,
    timer: null,

    /**
     * Starts the split in a payment form that has the Reference field (jQuery.Class calls init from the constructor).
     * @param {jQuery} form
     */
    init: function (form) {
        if (!form) {
            return;
        }

        this.form = form;
        this.container = jQuery('<div class="paymentAllocation mt-2"></div>');
        form.find('input[name="reference_id"]').closest('.js-multireference-field').after(this.container);
        this.registerEvents();
        this.refresh();
    },

    getReferenceIds: function () {
        return jQuery.grep((this.form.find('input[name="reference_id"]').val() || '').split(/[;,]/), function (id) {
            return parseInt(id) > 0;
        });
    },

    /**
     * Converts an amount in the user format into a number.
     * @param {string} value
     * @returns {number}
     */
    parseAmount: function (value) {
        let text = String(value || '').split(app.getGroupingSeparator()).join('');
        text = text.replace(app.getDecimalSeparator(), '.').replace(/[^0-9.-]/g, '');
        const amount = parseFloat(text);

        return isNaN(amount) ? 0 : amount;
    },

    /**
     * Loads the suggested or stored split after the documents, the amount or the currency changed.
     */
    refresh: function () {
        const self = this,
            documentIds = this.getReferenceIds();

        if (documentIds.length < 2) {
            this.container.empty();

            return;
        }

        app.request.post({
            data: {
                module: this.form.find('[name="module"]').val(),
                action: 'PaymentAllocation',
                record: this.form.find('[name="record"]').val(),
                amount: this.form.find('[name="payment_amount"]').val(),
                currency_id: this.form.find('[name="payment_amount_currency_id"]').val(),
                document_ids: documentIds.join(';')
            }
        }).then(function (error, data) {
            if (error === null && data && data.rows) {
                self.render(data.rows);
            }
        });
    },

    render: function (rows) {
        const table = jQuery('<table class="table table-sm align-middle mb-1"></table>'),
            body = jQuery('<tbody></tbody>');

        table.append(jQuery('<thead><tr><th></th><th class="text-end"></th></tr></thead>'));
        table.find('th').eq(0).text(app.vtranslate('JS_PAYMENT_SPLIT'));
        table.find('th').eq(1).text(app.vtranslate('JS_PAYMENT_SPLIT_AMOUNT'));

        jQuery.each(rows, function (index, row) {
            const tr = jQuery('<tr></tr>'),
                input = jQuery('<input type="text" class="form-control form-control-sm text-end">');

            input.attr('name', 'payment_allocation[' + row.id + ']').val(app.convertCurrencyToUserFormat(row.amount));
            tr.append(jQuery('<td></td>').text(row.number + ' – ' + row.name));
            tr.append(jQuery('<td class="w-25"></td>').append(input));
            body.append(tr);
        });

        table.append(body);
        this.container.empty().append(table).append('<div class="form-text paymentAllocationRemaining"></div>');
        this.updateRemaining();
    },

    /**
     * Shows the part of the payment amount that is not split; the save adds it to the last document.
     */
    updateRemaining: function () {
        const self = this;
        let remaining = this.parseAmount(this.form.find('[name="payment_amount"]').val());

        this.container.find('input[name^="payment_allocation"]').each(function () {
            remaining -= self.parseAmount(jQuery(this).val());
        });

        this.container.find('.paymentAllocationRemaining').text(app.vtranslate('JS_PAYMENT_SPLIT_REMAINING') + ' ' + app.convertCurrencyToUserFormat(remaining));
    },

    registerEvents: function () {
        const self = this,
            refreshLater = function () {
                clearTimeout(self.timer);
                self.timer = setTimeout(function () {
                    self.refresh();
                }, 300);
            };

        this.form.on('change', 'input[name="reference_id"], [name="payment_amount"], [name="payment_amount_currency_id"]', refreshLater);
        this.container.on('input', 'input', function () {
            self.updateRemaining();
        });
    }
});

jQuery(function () {
    const form = jQuery('#EditView');

    if (form.find('input[name="reference_id"]').length) {
        new Core_PaymentAllocation_Js(form);
    }
});
