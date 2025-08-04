/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Settings_Vtiger_Edit_Js */
Vtiger_Edit_Js('Settings_Vtiger_Edit_Js', {}, {
    registerEvents: function () {
        this._super();
        //Register events for settings side menu (Search and collapse open icon )
        let instance = new Settings_Vtiger_Index_Js();

        instance.registerBasicSettingsEvents();
    }
})