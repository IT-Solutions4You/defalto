/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Settings_MailManager_SetupWizard_Js */
Settings_Vtiger_Index_Js('Settings_MailManager_SetupWizard_Js', {}, {
    /**
     * Sends one wizard operation (finish or skip) and opens Mail Manager afterwards.
     * A navigation is used because the wizard ends and the mail client takes over the page.
     */
    sendOperation: function (mode) {
        const form = jQuery('#mmProSetupWizardForm'),
            params = jQuery.extend({}, form.serializeFormData(), {
                module: 'MailManager',
                parent: 'Settings',
                action: 'SetupWizard',
                mode: mode
            });

        app.helper.showProgress();
        app.request.post({data: params}).then(function (error) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification({message: error.message});

                return;
            }

            app.helper.showSuccessNotification({message: app.vtranslate('JS_SETTINGS_SAVED')});
            window.location.href = 'index.php?module=MailManager&view=List&app=TOOLS';
        });
    },
    registerEvents: function () {
        this._super();

        const self = this;

        jQuery('#mmProSetupWizard').on('click', '[data-mm-setup]', function () {
            self.sendOperation(jQuery(this).data('mmSetup'));
        });
    }
});
