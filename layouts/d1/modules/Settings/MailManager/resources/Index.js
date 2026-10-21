/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* Must extend Settings_Vtiger_Index_Js — app.controller() instantiates this class
   instead of the core one (see main module Index.js note) */
Settings_Vtiger_Index_Js('Settings_MailManager_Index_Js', {}, {

    /**
     * @return {jQuery}
     */
    getContainer: function () {
        return jQuery('.its4you-module-settings');
    },

    /**
     * Shows the post-redirect success toast (flash carrier div pattern)
     *
     * @param {jQuery} container
     */
    showFlashMessages: function (container) {
        let flash = container.find('.its4you-flash');
        if (flash.data('success')) {
            app.helper.showSuccessNotification({message: flash.data('success')});
        }
    },

    /**
     * Mailbox delete with a vtiger confirmation modal
     *
     * @param {jQuery} container
     */
    registerDeleteMailbox: function (container) {
        container.on('click', '.its4you-delete-mailbox', function () {
            let button = jQuery(this),
                message = container.find('.its4you-flash').data('delete-confirm')
                    .replace('%s', app.htmlEncode(String(button.data('name'))));

            app.helper.showConfirmationBox({message: message}).then(function () {
                let params = {
                    module: app.getModuleName(),
                    parent: app.getParentModuleName(),
                    action: 'DeleteMailbox',
                    mailboxid: button.data('mailboxid')
                };
                app.request.post({data: params}).then(function (err, data) {
                    if (err || !data || !data.success) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_ERROR')});
                        return;
                    }
                    window.location.href = 'index.php?module=' + app.getModuleName()
                        + '&parent=Settings&view=Index';
                });
            });
        });
    },

    /**
     * The admin ends a deputy's access to a personal mailbox
     *
     * @param {jQuery} container
     */
    registerRevokeDeputy: function (container) {
        container.on('click', '.its4you-revoke-deputy', function () {
            let link = jQuery(this),
                message = String(container.find('.its4you-flash').data('revoke-confirm'))
                    .replace('%s', app.htmlEncode(String(link.data('name'))));

            app.helper.showConfirmationBox({message: message}).then(function () {
                app.request.post({data: {
                    module: app.getModuleName(),
                    parent: app.getParentModuleName(),
                    action: 'RevokeDeputy',
                    mailboxid: link.data('mailboxid'),
                    userid: link.data('userid')
                }}).then(function (err, data) {
                    if (err || !data || !data.success) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_SAVE_ERROR')});
                        return;
                    }
                    link.closest('.its4you-deputy').remove();
                });
            });
        });
    },

    registerEvents: function () {
        this._super();

        let container = this.getContainer();
        if (!container.length) {
            return;
        }
        this.showFlashMessages(container);
        this.registerDeleteMailbox(container);
        this.registerRevokeDeputy(container);
    }
});
