/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
Vtiger_Edit_Js("Contacts_Edit_Js", {}, {
    /**
     * Function to check for Portal User
     */
    checkForPortalUser: function (form) {
        var element = jQuery('[name="portal"]', form);
        var response = element.is(':checked');
        var primaryEmailField = jQuery('[name="email"]');
        var primaryEmailValue = primaryEmailField.val();
        if (response) {
            if (primaryEmailField.length == 0) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_PRIMARY_EMAIL_FIELD_DOES_NOT_EXISTS')});
                return false;
            }
            if (primaryEmailValue == "") {
                app.helper.showErrorNotification({message: app.vtranslate('JS_PLEASE_ENTER_PRIMARY_EMAIL_VALUE_TO_ENABLE_PORTAL_USER')});
                return false;
            }
        }
        return true;
    },
    /**
     * Function to register recordpresave event
     */
    registerRecordPreSaveEvent: function (form) {
        var thisInstance = this;
        if (typeof form == 'undefined') {
            form = this.getForm();
        }

        app.event.on(Vtiger_Edit_Js.recordPresaveEvent, function (e) {
            var result = thisInstance.checkForPortalUser(form);
            if (!result) {
                e.preventDefault();
            }
        });

    },

    registerBasicEvents: function (container) {
        this._super(container);
        this.registerRecordPreSaveEvent(container);
    }
})