/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** @var Settings_IncomingPayment_Index_Js */
Settings_Vtiger_Index_Js('Settings_IncomingPayment_Index_Js', {}, {
    getContainer: function () {
        return jQuery('.incoming-payment-settings');
    },

    /**
     * Rebuilds the status value selects of a row from the values of the chosen status field.
     */
    fillStatusValues: function (row) {
        const statusField = row.find('.statusField'),
            picklists = statusField.data('picklists') || {},
            values = picklists[statusField.val()] || {};

        row.find('.statusValue').each(function () {
            const select = jQuery(this),
                selected = select.val() || select.data('selected');

            select.empty().append(new Option(app.vtranslate('JS_NONE'), ''));

            jQuery.each(values, function (value, label) {
                select.append(new Option(label, value, false, value === selected));
            });
        });
    },

    /**
     * Checks that every active module has a total field.
     */
    validate: function () {
        let valid = true;

        this.getContainer().find('.integrationRow').each(function () {
            const row = jQuery(this),
                active = row.find('input[type="checkbox"]').is(':checked'),
                totalField = row.find('.totalField').val();

            if (active && !totalField) {
                valid = false;
            }
        });

        if (!valid) {
            app.helper.showErrorNotification({message: app.vtranslate('JS_TOTAL_FIELD_REQUIRED')});
        }

        return valid;
    },

    save: function () {
        const form = this.getContainer().find('.incomingPaymentIntegrationForm'),
            params = {
                module: 'IncomingPayment',
                parent: 'Settings',
                action: 'IntegrationSave'
            };

        if (!this.validate()) {
            return;
        }

        jQuery.each(form.serializeArray(), function (index, item) {
            params[item.name] = item.value;
        });

        app.helper.showProgress();
        app.request.post({data: params}).then(function (error) {
            app.helper.hideProgress();

            if (error === null) {
                app.helper.showSuccessNotification({message: app.vtranslate('JS_INTEGRATION_SAVED')});
                return;
            }

            app.helper.showErrorNotification({message: error.message || error});
        });
    },

    registerEvents: function () {
        const self = this,
            container = this.getContainer();

        this._super();

        container.on('change', '.statusField', function () {
            self.fillStatusValues(jQuery(this).closest('.integrationRow'));
        });

        container.on('click', '.saveIntegration', function () {
            self.save();
        });

        container.on('submit', '.incomingPaymentIntegrationForm', function (event) {
            event.preventDefault();
            self.save();
        });
    }
});
