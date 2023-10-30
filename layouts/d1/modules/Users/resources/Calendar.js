/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Settings_Users_PreferenceDetail_Js("Settings_Users_Calendar_Js",{},{
    
	/**
	 * register Events for my preference
	 */
	registerEvents : function(){
		this._super();
		Settings_Users_PreferenceEdit_Js.registerChangeEventForCurrencySeparator();
		Settings_Users_PreferenceEdit_Js.registerNameFieldChangeEvent();
	}
});