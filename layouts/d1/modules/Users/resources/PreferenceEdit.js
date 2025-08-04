/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Users_Edit_Js("Settings_Users_PreferenceEdit_Js", {

    /**
     * Function to register change event for currency separator
     */
    registerChangeEventForCurrencySeparator: function () {
        var form = jQuery('form');
        jQuery('[name="currency_decimal_separator"]', form).on('change', function (e) {
            var element = jQuery(e.currentTarget);
            var selectedValue = element.val();
            var groupingSeparatorValue = jQuery('[name="currency_grouping_separator"]', form).data('selectedValue');
            if (groupingSeparatorValue == selectedValue) {
                app.helper.showErrorNotification({'message': app.vtranslate('JS_DECIMAL_SEPARATOR_AND_GROUPING_SEPARATOR_CANT_BE_SAME')});
                var previousSelectedValue = element.data('selectedValue');
                element.select2('val', previousSelectedValue);
            } else {
                element.data('selectedValue', selectedValue);
            }
        })
        jQuery('[name="currency_grouping_separator"]', form).on('change', function (e) {
            var element = jQuery(e.currentTarget);
            var selectedValue = element.val();
            var decimalSeparatorValue = jQuery('[name="currency_decimal_separator"]', form).data('selectedValue');
            if (decimalSeparatorValue == selectedValue) {
                app.helper.showErrorNotification({'message': app.vtranslate('JS_DECIMAL_SEPARATOR_AND_GROUPING_SEPARATOR_CANT_BE_SAME')});
                var previousSelectedValue = element.data('selectedValue');
                element.select2('val', previousSelectedValue);
            } else {
                element.data('selectedValue', selectedValue);
            }
        })
    },

    registerNameFieldChangeEvent: function () {
        var form = jQuery('form');
        var specialChars = /[<\>\"\,]/;
        jQuery('[name="first_name"]', form).on('change', function (e) {
            var firstNameEle = jQuery(e.currentTarget);
            var firstName = firstNameEle.val();
            var firstNameOldVal = firstNameEle.parent().find('.fieldname').data('prev-value');
            if (specialChars.test(firstName)) {
                Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_COMMA_NOT_ALLOWED_USERS'));
                firstNameEle.val(firstNameOldVal);
            }
        });
        jQuery('[name="last_name"]', form).on('change', function (e) {
            var lastNameEle = jQuery(e.currentTarget);
            var lastName = lastNameEle.val();
            var lastNameOldVal = lastNameEle.parent().find('.fieldname').data('prev-value');
            if (specialChars.test(lastName)) {
                Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_COMMA_NOT_ALLOWED_USERS'));
                lastNameEle.val(lastNameOldVal);
            }
        });
    }
}, {

    registerNameFieldChangeEvent: function () {
        var form = jQuery('form');
        jQuery('[name="first_name"]', form).on('change', function (e) {
            var firstNameEle = jQuery(e.currentTarget);
            var firstName = firstNameEle.val();
            var firstNameOldVal = firstNameEle.parent().find('.fieldname').data('prev-value');
            if (firstName.indexOf(',') !== -1) {
                Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_COMMA_NOT_ALLOWED_USERS'));
                firstNameEle.val(firstNameOldVal);
            }
        });
        jQuery('[name="last_name"]', form).on('change', function (e) {
            var lastNameEle = jQuery(e.currentTarget);
            var lastName = lastNameEle.val();
            var lastNameOldVal = lastNameEle.parent().find('.fieldname').data('prev-value');
            if (lastName.indexOf(',') !== -1) {
                Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_COMMA_NOT_ALLOWED_USERS'));
                lastNameEle.val(lastNameOldVal);
            }
        });
    }
}, {

    /**
     * register Events for my preference
     */
    registerEvents: function () {
        this._super();
        Users_PreferenceEdit_Js.registerChangeEventForCurrencySeparator();
    }
});