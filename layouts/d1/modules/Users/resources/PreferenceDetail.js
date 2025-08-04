/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Users_Detail_Js("Settings_Users_PreferenceDetail_Js", {}, {

    /**
     * We have to load Settings Index Js but the parent module name will be empty so we are extending this api and passing
     * last parameter as settings (This is useful to settings side events like accordion click and settings menu search)
     */
    addIndexComponent: function () {
        this.addModuleSpecificComponent('Index', app.getModuleName(), 'Settings');
    },

    /**
     * register Events for my preference
     */
    registerEvents: function () {
        this._super();
        Settings_Users_PreferenceEdit_Js.registerChangeEventForCurrencySeparator();
        Settings_Users_PreferenceEdit_Js.registerNameFieldChangeEvent();
    }
});