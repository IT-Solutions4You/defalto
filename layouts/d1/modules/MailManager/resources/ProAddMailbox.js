/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* A user adds an own mailbox — the admin's wizard (MailboxWizard.js) on a
   module page. Must extend Vtiger_Index_Js — app.controller() instantiates
   this class by the module + view name convention */
Vtiger_Index_Js('MailManager_ProAddMailbox_Js', {}, jQuery.extend({},
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
