/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Reports_Edit_Js("Reports_PivotEdit2_Js",{},{
	step2Container : false,

	advanceFilterInstance : false,

	init : function() {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the report step2 elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.step2Container;
	},

	/**
	 * Function to set the report step2 container
	 * @params : element - which represents the report step3 container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.step2Container = element;
		return this;
	},

	/**
	 * Function  to intialize the reports step2
	 */
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#report_step2');
		}

		if(container.is('#report_step2')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#report_step2'));
		}
	},

	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
	},

    submit : function(){
		var aDeferred = jQuery.Deferred();
		this.calculateValues();
		var form = this.getContainer();
		var formData = form.serializeFormData();
		app.helper.showProgress();
		app.request.post({data:formData}).then(
			function(error,data) {
				form.hide();
				app.helper.hideProgress();
				aDeferred.resolve(data);
			},
			function(error,err){

			}
		);
		return aDeferred.promise();
	},

	registerEvents : function(){
		var container = this.getContainer();
		vtUtils.applyFieldElementsView(container);
		this.advanceFilterInstance = Vtiger_AdvanceFilter_Js.getInstance(jQuery('.filterContainer',container));
	}
});




