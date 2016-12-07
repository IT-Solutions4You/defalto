/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
Reports_Edit_Js("Reports_PivotEdit3_Js",{

    registerFieldForChosen : function() {
		vtUtils.showSelect2ElementView(jQuery('.reportsDetailHeader #pivot_rowfields'), {maximumSelectionSize: 3});
		vtUtils.showSelect2ElementView(jQuery('.reportsDetailHeader #pivot_columnfields'), {maximumSelectionSize: 3});
        vtUtils.showSelect2ElementView(jQuery('.reportsDetailHeader #pivot_datafields'), {maximumSelectionSize: 3});
	},

    initPivotFields : function() {
		var rowFields = jQuery('#pivot_rowfields');
		var columnFields = jQuery('#pivot_columnfields');
        var dataFields = jQuery('#pivot_datafields');

        var xfields = JSON.parse(jQuery('input[name=hdnrowfields]').val());
		var yfields = JSON.parse(jQuery('input[name=hdncolumnfields]').val());
        var zfields = JSON.parse(jQuery('input[name=hdndatafields]').val());

		var pivotfields = jQuery('#pivotfields');
        var dataFieldsElement = jQuery('#datafields_element');

        rowFields.html(pivotfields.clone().html());
        columnFields.html(pivotfields.clone().html());
        dataFields.html(dataFieldsElement.clone().html());

        var temp;
        for(var index in xfields){
            temp = xfields[index].replace(/\\/g, '\\\\\\\\');
            columnFields.find("option[data-escaped-value='"+temp+"']").remove();
        }

        for(var index1 in yfields){
            temp = yfields[index1].replace(/\\/g, '\\\\\\\\');
            rowFields.find("option[data-escaped-value='"+temp+"']").remove();
        }

        rowFields.attr('multiple', true).select2({maximumSelectionSize: 3}).select2('val','');
        columnFields.attr('multiple', true).select2({maximumSelectionSize: 3}).select2('val','');
        dataFields.attr('multiple', true).select2({maximumSelectionSize: 3}).select2('val','');

        if(xfields && xfields[0]) {
			rowFields.select2("val", xfields);
		}
        if(yfields && yfields[0]) {
			columnFields.select2("val", yfields);
		}
        if(zfields && zfields[0]) {
			dataFields.select2("val", zfields);
		}
		var primaryModule = jQuery('input[name="primary_module"]').val();
		var inventoryModules = ['Invoice', 'Quotes', 'PurchaseOrder', 'SalesOrder'];
		var secodaryModules = jQuery('input[name="secondary_modules"]').val();
		var secondaryIsInventory = false;
		inventoryModules.forEach(function (entry) {
			if (secodaryModules.indexOf(entry) != -1) {
				secondaryIsInventory = true;
			}
		});
		if ((jQuery.inArray(primaryModule, inventoryModules) !== -1 || secondaryIsInventory) && dataFields.val()) {
			var reg = new RegExp(/vtiger_inventoryproductrel*/);
			if (reg.test(dataFields.val())) {
				jQuery('#pivot_datafields option').not('[value^="vtiger_inventoryproductrel"]').remove();
			} else {
				jQuery('#pivot_datafields option[value^="vtiger_inventoryproductrel"]').remove();
			}
		}
	},

    updateSelectElement : function(xfields, fieldsElement){
        var pivotfields = jQuery('#pivotfields'); 
        var yfields = fieldsElement.val(); 
        fieldsElement.html(pivotfields.clone().html()); 
        for(var index in xfields){ 
            xfields[index] = xfields[index].replace(/\\/g, '\\\\\\\\');
            fieldsElement.find('option[data-escaped-value="'+xfields[index]+'"]').remove(); 
        }
        if(yfields && yfields[0]) { 
            var selectData = fieldsElement.select2("data");
            fieldsElement.select2("val", yfields); 
            fieldsElement.select2("data",selectData);
        } 
    }
},{

	step3Container : false,

	init : function() {
		this.initialize();
	},
	/**
	 * Function to get the container which holds all the report elements
	 * @return jQuery object
	 */
	getContainer : function() {
		return this.step3Container;
	},

	/**
	 * Function to set the report step2 container
	 * @params : element - which represents the report step2 container
	 * @return : current instance
	 */
	setContainer : function(element) {
		this.step3Container = element;
		return this;
	},

	/**
	 * Function  to intialize the reports step2
	 */
	initialize : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('#report_step3');
		}
		if(container.is('#report_step3')) {
			this.setContainer(container);
		}else{
			this.setContainer(jQuery('#report_step3'));
		}
	},

    registerUpdateSelectElementEventForRows: function(){
        jQuery('#pivot_rowfields').on('change',function(e) {
            if(!jQuery('#warning').hasClass('hide')){
                jQuery('#warning').addClass(' hide')
            }
            var selectedFields = jQuery(e.currentTarget).val();
            var columnFieldElement = jQuery('#pivot_columnfields');
            Reports_PivotEdit3_Js.updateSelectElement(selectedFields, columnFieldElement);
		});
    },

    registerUpdateSelectElementEventForColumns: function(){
        jQuery('#pivot_columnfields').on('change',function(e) {
            if(!jQuery('#warning').hasClass('hide')){
                jQuery('#warning').addClass(' hide')
            }
            var selectedFields = jQuery(e.currentTarget).val();
            var rowFieldElement = jQuery('#pivot_rowfields');
            Reports_PivotEdit3_Js.updateSelectElement(selectedFields, rowFieldElement);
		});
    },

    calculateValues : function(){
		//handled advanced filters saved values.
		var advfilterlist = jQuery('#advanced_filter','#report_step2').val();// value from step2
		jQuery('input[name="advanced_filter"]','#report_step3').val(advfilterlist);
	},

    registerSubmitEvent : function(){
        var thisInstance = this;
        var form = this.getContainer();
        form.on('submit', function(e) {
            var rowElements = thisInstance.getSelectedColumns(jQuery('#pivot_rowfields'));
            var columnsElements = thisInstance.getSelectedColumns(jQuery('#pivot_columnfields'));
            var dataFields = thisInstance.getSelectedColumns(jQuery('#pivot_datafields'));
            var warningContainer1 = jQuery('#warning1');
            
            if(rowElements.length == 0 || columnsElements.length == 0 || dataFields.length == 0){
                warningContainer1.removeClass('hide');
                e.preventDefault();
            } else {
                jQuery('[name=rowfields]').val(JSON.stringify(rowElements));
                jQuery('[name=columnfields]').val(JSON.stringify(columnsElements));
                jQuery('[name=datafields]').val(JSON.stringify(dataFields));
                return true;
            }
        });
	},

     /**
	 * Function which will get the selected columns with order preserved
     * @param : selectElement
	 * @return : array of selected values in order
	 */
	getSelectedColumns : function(selectElement) {
		var select2Element = app.getSelect2ElementFromSelect(selectElement);

		var selectedValuesByOrder = new Array();
		var selectedOptions = selectElement.find('option:selected');

		var orderedSelect2Options = select2Element.find('li.select2-search-choice').find('div');
		orderedSelect2Options.each(function(index,element){
			var chosenOption = jQuery(element);
			selectedOptions.each(function(optionIndex, domOption){
				var option = jQuery(domOption);
				if(option.html() == chosenOption.html()) {
					selectedValuesByOrder.push(option.val());
					return false;
				}
			});
		});
		return selectedValuesByOrder;
	},
    
    /**
	 * Function which will arrange the select2 element choices in order
	 */
	arrangeSelectChoicesInOrder : function(selectElement, selectedFields) {
		vtUtils.showSelect2ElementView(selectElement);
		var id = jQuery(selectElement).attr('id');
		var chosenElement = jQuery('#s2id_'+id);
		var choicesContainer = chosenElement.find('.select2-choices');
		var choicesList = choicesContainer.find('.select2-search-choice');

		//var coulmnListSelectElement = Vtiger_CustomView_Js.getColumnSelectElement();
		var selectedOptions = selectElement.find('option:selected');
		var selectedOrder = JSON.parse(selectedFields.val());
		var selectedOrderKeys = [];
		for(var key in selectedOrder) {
			if(selectedOrder.hasOwnProperty(key)){
				selectedOrderKeys.push(key);
			}
		}
		for(var index=selectedOrderKeys.length ; index > 0 ; index--) {
			var selectedValue = selectedOrder[selectedOrderKeys[index-1]];
			var option = selectedOptions.filter('[value="'+selectedValue+'"]');
			choicesList.each(function(choiceListIndex,element){
				var liElement = jQuery(element);
				if(liElement.find('div').html() == option.html()){
					choicesContainer.prepend(liElement);
					return false;
				}
			});
		}
	},

	/**
	 * Function is used to limit the calculation for line item fields and inventory module fields.
	 * only one of these fields can be used at a time
	 */
	lineItemCalculationLimit: function () {
		var thisInstance = this;
		var dataFields = jQuery('#pivot_datafields');
		if (thisInstance.isInventoryModule()) {
			dataFields.on('change', function (e) {
				var value = dataFields.val();
				var reg = new RegExp(/vtiger_inventoryproductrel*/);
				if (value && value.length > 0) {
					if (reg.test(value)) {
						// line item field selected remove module fields
						jQuery('#pivot_datafields option').not('[value^="vtiger_inventoryproductrel"]').remove();
					} else {
						jQuery('#pivot_datafields option[value^="vtiger_inventoryproductrel"]').remove();
					}
				} else {
					var dataFieldsElement = jQuery('#datafields_element');
					dataFields.html(dataFieldsElement.clone().html());
				}
				thisInstance.displayLineItemFieldLimitationMessage();
			});
		}
	},

	isInventoryModule: function () {
		var primaryModule = jQuery('input[name="primary_module"]').val();
		var inventoryModules = ['Invoice', 'Quotes', 'PurchaseOrder', 'SalesOrder'];
		// To limit the calculation fields if secondary module contains inventoryModule
		var secodaryModules = jQuery('input[name="secondary_modules"]').val();
		var secondaryIsInventory = false;
		inventoryModules.forEach(function (entry) {
			if (secodaryModules.indexOf(entry) != -1) {
				secondaryIsInventory = true;
			}
		});
		if (jQuery.inArray(primaryModule, inventoryModules) !== -1 || secondaryIsInventory) {
			return true;
		} else {
			return false;
		}
	},

	displayLineItemFieldLimitationMessage: function () {
		var message = app.vtranslate('JS_CALCULATION_LINE_ITEM_FIELDS_SELECTION_LIMITATION');
		if (jQuery('#calculationLimitationMessage').length == 0) {
			jQuery('#pivot_datafields').parent().append('<div id="calculationLimitationMessage" class="alert alert-info">' + message + '</div>');
		} else {
			jQuery('#calculationLimitationMessage').html(message);
		}
	},

	registerEvents : function(){
		var container = this.getContainer();
        this.calculateValues();
		this.arrangeSelectChoicesInOrder(jQuery('#pivot_rowfields'), jQuery('[name=hdnrowfields]'));
        this.arrangeSelectChoicesInOrder(jQuery('#pivot_columnfields'), jQuery('[name=hdncolumnfields]'));
        this.arrangeSelectChoicesInOrder(jQuery('#pivot_datafields'), jQuery('[name=hdndatafields]'));
        Reports_PivotEdit3_Js.registerFieldForChosen();
        Reports_PivotEdit3_Js.initPivotFields();
        this.registerUpdateSelectElementEventForRows();
        this.registerUpdateSelectElementEventForColumns();
        this.registerSubmitEvent();
		this.lineItemCalculationLimit();
        
	}
});


