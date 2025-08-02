/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Settings_Users_PreferenceDetail_Js("Settings_Users_Calendar_Js", {}, {

    /**
     * register Events for my preference
     */
    registerEvents: function () {
        this._super();
        Settings_Users_PreferenceEdit_Js.registerChangeEventForCurrencySeparator();
        Settings_Users_PreferenceEdit_Js.registerNameFieldChangeEvent();
    }
});