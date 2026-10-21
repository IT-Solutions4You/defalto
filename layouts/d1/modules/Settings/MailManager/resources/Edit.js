/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* Must extend Settings_Vtiger_Index_Js — app.controller() instantiates this class
   instead of the core one. The wizard itself is MailboxWizard.js (shared with
   the users' own mailbox wizard in the module) */
Settings_Vtiger_Index_Js('Settings_MailManager_Edit_Js', {}, jQuery.extend({},
    window.MailManager_ProMailboxWizard_Mixin, {

    registerEvents: function () {
        this._super();

        let container = this.getContainer();
        if (!container.length) {
            return;
        }
        this.registerWizardEvents(container);
    }
}));
