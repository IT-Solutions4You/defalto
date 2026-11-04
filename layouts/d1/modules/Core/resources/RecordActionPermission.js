/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Record action restrictions banner in the detail view: asks for confirmation before an admin removes a rule.
 * The banner is part of the detail header, which can be reloaded by ajax, so the handler is bound to the document.
 */
jQuery.Class('Vtiger_RecordActionPermission_Js', {
    _instance: null,

    getInstance() {
        if (!this._instance) {
            this._instance = new Vtiger_RecordActionPermission_Js();
        }

        return this._instance;
    },
}, {
    isRegistered: false,

    registerEvents() {
        if (this.isRegistered) {
            return;
        }

        this.isRegistered = true;
        this.registerRemoveConfirmation();
    },

    registerRemoveConfirmation() {
        jQuery(document).on('submit', '.recordActionPermissionRemove', function (event) {
            const form = jQuery(this);

            if (form.data('confirmed')) {
                return;
            }

            event.preventDefault();
            app.helper.showConfirmationBox({'message': form.data('confirm')}).then(function () {
                form.data('confirmed', true);
                form.trigger('submit');
            });
        });
    },
});

jQuery(function () {
    Vtiger_RecordActionPermission_Js.getInstance().registerEvents();
});
