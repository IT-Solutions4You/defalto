/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var MailManager_ProButton_Js */
jQuery.Class('MailManager_ProButton_Js', {
    instance: false,
    getInstance: function () {
        if (!this.instance) {
            this.instance = new MailManager_ProButton_Js();
        }

        return this.instance;
    }
}, {
    /**
     * Opens the Installer premium modal that describes Mail Manager Pro
     * (texts LBL_PREMIUM_MODAL_ITEM_n of the MailManager language file).
     */
    openProInfo: function () {
        if (window.DefaltoPremiumModal) {
            window.DefaltoPremiumModal.open();
        }
    },
    registerEvents: function () {
        const self = this;

        jQuery(document).on('click', '.mmProButton', function (e) {
            e.preventDefault();
            self.openProInfo();
        });
    }
});

jQuery(document).ready(function () {
    MailManager_ProButton_Js.getInstance().registerEvents();
});
