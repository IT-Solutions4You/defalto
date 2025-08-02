/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
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
