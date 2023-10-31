/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger_Edit_Js("Settings_Vtiger_Edit_Js",{},{
    
    registerEvents : function() {
        this._super();
        //Register events for settings side menu (Search and collapse open icon )
        var instance = new Settings_Vtiger_Index_Js(); 
        instance.registerBasicSettingsEvents();
    }
})