/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger_Popup_Js("PriceBooks_Popup_Js",{},{
	
	/**
	 * Function to pass params for request
	 */
	getCompleteParams : function(){
		var params = this._super();
		params['currency_id'] = jQuery('#currencyId').val();
		return params;
	},
	
	registerEvents: function(){
		this._super();
	}
});

