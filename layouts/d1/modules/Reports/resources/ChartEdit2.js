
/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
Reports_Edit3_Js("Reports_ChartEdit2_Js",{},{

	calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = this.advanceFilterInstance.getValues();
		jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
	},

	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#chart_report_step2');
		}

		if(container.is('#chart_report_step2')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#chart_report_step2'));
		}
	},

	submit : function(){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		thisInstance.calculateValues();
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
	}
});