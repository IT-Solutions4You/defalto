/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* Must extend Vtiger_Index_Js — app.controller() instantiates this class by
   the module + view name convention. The settings themselves live in
   MySettingsPanel.js (also used inside the inbox) */
Vtiger_Index_Js('MailManager_ProMySettings_Js', {}, {

    registerEvents: function () {
        this._super();

        let match = window.location.search.match(/[?&]tab=([a-z]+)/);
        new MailManager_ProMySettingsPanel_Js(jQuery('body'), {
            tab: match ? match[1] : '',
            // the page renders one mailbox — switching reloads with the param
            onMailboxChange: function (mailboxId, tab) {
                window.location.href = 'index.php?module=' + app.getModuleName()
                    + '&view=ProMySettings&app=TOOLS&mailbox=' + mailboxId + (tab ? '&tab=' + tab : '');
            }
        });
    }
});
