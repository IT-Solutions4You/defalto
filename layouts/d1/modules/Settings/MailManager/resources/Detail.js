/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* The admin's mailbox detail — the My Settings panel in admin mode plus the
   wizard's certificate check and type toggle; extends Settings_Vtiger_Index_Js
   like Index.js */
Settings_Vtiger_Index_Js('Settings_MailManager_Detail_Js', {}, jQuery.extend({},
    window.MailManager_ProMailboxWizard_Mixin, {

    /**
     * @return {jQuery}
     */
    getContainer: function () {
        return jQuery('.its4you-my-settings');
    },

    registerEvents: function () {
        this._super();

        let container = this.getContainer(),
            match = window.location.search.match(/[?&]tab=([a-z]+)/);
        if (!container.length) {
            return;
        }

        new MailManager_ProMySettingsPanel_Js(jQuery('body'), {
            admin: true,
            tab: match ? match[1] : '',
            // the page renders one mailbox — switching reloads with the record
            onMailboxChange: function (mailboxId, tab) {
                window.location.href = 'index.php?module=' + app.getModuleName()
                    + '&parent=Settings&view=Detail&record=' + mailboxId + (tab ? '&tab=' + tab : '');
            }
        });

        this.registerCertificateTrust(container);
        // the members select (.select2) is initialised by the core on page load
        this.registerMailboxTypeToggle(container);
    }
}));
