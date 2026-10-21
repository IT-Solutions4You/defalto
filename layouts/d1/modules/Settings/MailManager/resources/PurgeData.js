/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Settings_MailManager_PurgeData_Js */
Settings_Vtiger_Index_Js('Settings_MailManager_PurgeData_Js', {}, {
    /**
     * Asks for confirmation, deletes the stored mail and updates the counter in place.
     */
    purgeStoredMail: function () {
        const container = jQuery('#mmProPurgeData');

        app.helper.showConfirmationBox({message: app.vtranslate('JS_LBL_PURGE_CONFIRM')}).then(function () {
            app.helper.showProgress();
            app.request.post({data: {module: 'MailManager', parent: 'Settings', action: 'PurgeData'}}).then(function (error) {
                app.helper.hideProgress();

                if (error) {
                    app.helper.showErrorNotification({message: error.message});

                    return;
                }

                container.find('#mmProStoredMessages').text('0');
                container.find('[data-mm-purge]').prop('disabled', true);
                app.helper.showSuccessNotification({message: app.vtranslate('JS_LBL_PURGE_DONE')});
            });
        });
    },
    registerEvents: function () {
        this._super();

        const self = this;

        jQuery('#mmProPurgeData').on('click', '[data-mm-purge]', function () {
            self.purgeStoredMail();
        });
    }
});
