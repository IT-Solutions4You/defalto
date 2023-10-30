/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
Settings_Currency_Js('Settings_Currency_List_Js', {}, {
	
	init : function() {
            this._super();
		this.addComponents();
	},
	
	addComponents : function() {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	}
});
