/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

function VtPivotFormatter() {
    "use strict";
    var self = {};
    self.format = function (value) {
        var V = "";
        try {
            if(!((value % 1) === 0))
                V = numberFormat(value,app.getNumberOfDecimals(),app.getDecimalSeparator(),app.getGroupingSeparator());
            else
                V = numberFormat(value,"","",app.getGroupingSeparator());
            if(V==0)
                V="";
          
        } catch (Error) {

        }
        return V;
    };
    return self;
}

$.unc.plugins.addFormatter('VtPivotDataFormatter', VtPivotFormatter);


/**
 * Format a number and return a string based on input settings
 * @param {Number} number The input number to format
 * @param {Number} decimals The amount of decimals
 * @param {String} decPoint The decimal point, defaults to the one given in the lang options
 * @param {String} thousandsSep The thousands separator, defaults to the one given in the lang options
 */
function numberFormat(number, decimals, decPoint, thousandsSep) {
	
    // http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_number_format/
    var n = +number || 0,
		c = decimals === -1 ?
			(n.toString().split('.')[1] || '').length : // preserve decimals
			(isNaN(decimals = Math.abs(decimals)) ? 2 : decimals),
		d =  decPoint,
		t =  thousandsSep,
		s = n < 0 ? "-" : "",
		i = String(parseInt(n = Math.abs(n).toFixed(c))),
		j = i.length > 3 ? i.length % 3 : 0;

	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) +
		(c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}