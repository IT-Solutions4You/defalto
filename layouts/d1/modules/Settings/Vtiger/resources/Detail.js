/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger.Class("Settings_Vtiger_Detail_Js",{},{
	detailViewForm : false,

    init : function() {
       this.addComponents();
    },
   
    addComponents : function (){
      this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
    },

	/**
	 * Function which will give the detail view form
	 * @return : jQuery element
	 */
	getForm : function() {
		if(this.detailViewForm === false) {
			this.detailViewForm = jQuery('#detailView');
		}
		return this.detailViewForm;
	},

	/**
	 * Function to register form for validation
	 */
	registerFormForValidation : function(){
        var detailViewForm = this.getForm();
        if(detailViewForm.length > 0) {
            detailViewForm.vtValidate();
        }
	},

	/**
	 * Function which will handle the registrations for the elements
	 */
	registerEvents : function() {
		this.registerFormForValidation();
	}
});