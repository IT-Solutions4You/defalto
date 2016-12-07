/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Reports_Detail_Js("Reports_PivotDetail_Js",{},{

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

    registerSaveEvent : function(){
        var thisInstance = this;
        jQuery('.generateReportPivot').on('click',function(e){
            var rowElements = thisInstance.getSelectedColumns(jQuery('#pivot_rowfields'));
            var columnsElements = thisInstance.getSelectedColumns(jQuery('#pivot_columnfields'));
            var dataFields = thisInstance.getSelectedColumns(jQuery('#pivot_datafields'));
            var warningContainer1 = jQuery('#warning1');
            if(rowElements.length == 0 || columnsElements.length == 0 || dataFields.length == 0){
                warningContainer1.removeClass('hide');
                e.preventDefault();
            } else{
                warningContainer1.addClass('hide');
                var advFilterCondition = thisInstance.calculateValues();
                var recordId = thisInstance.getRecordId();
                var currentMode = jQuery(e.currentTarget).data('mode');
                var postData = {
                    'advanced_filter': advFilterCondition,
                    'rows': JSON.stringify(rowElements),
                    'columns': JSON.stringify(columnsElements),
                    'data_fields': JSON.stringify(dataFields),
                    'record' : recordId,
                    'view' : "PivotSaveAjax",
                    'module' : app.getModuleName(),
                    'mode' : currentMode
                };
				
				app.helper.showProgress();
                app.request.post({data:postData}).then(function(error,data){
						app.helper.hideProgress();
						jQuery('.reportActionButtons').addClass('hide');
                        window.location.reload(true);
                });
            }
        });
    },
    
    /**
	 * Function which will get the selected columns with order preserved
     * @param : selectElement
	 * @return : array of selected values in order
	 */
	getSelectedColumns : function(selectElement) {
		var id = jQuery(selectElement).attr('id');
		vtUtils.showSelect2ElementView(selectElement);
		var select2Element = jQuery('#s2id_'+id);

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

    registerPivotTableEvent : function(){
        var response =jQuery.parseJSON(jQuery('#reportdata').val());
        var columns = {};
        //left columns
        var xfields = [];
        for (var i=0,len=response.leftColumns.length; i<len; i++){
            var column = response.leftColumns[i].toLowerCase();
            var sort = 'asc';
            if (column.indexOf( "_month" ) > -1 ) {
                sort = 'month_order';
             } else if ( column.indexOf( "_dateorder" ) > -1 ) {
                column = column.replace(/_dateorder/, '')
                sort = 'date_order';
            } else if(column.indexOf( "_week" ) > -1) {
                sort = 'week_order';
            }
            var leftColumn = {
                field : column,
                sort : sort,
                showAll : false,
                agregateType : "distinct",
                label : column.replace(/_/g, ' ')
            };
            columns[column] = leftColumn;
            xfields.push(column);
        }
        //top columns
        var yfields = [];
        for (var i=0,len=response.topColumns.length; i<len; i++){
            var column = response.topColumns[i].toLowerCase();
            var sort = 'asc';
            if ( column.indexOf( "_month" ) > -1 ) {
                sort = 'month_order';
            } else if ( column.indexOf( "_dateorder" ) > -1 ) {
                column = column.replace(/_dateorder/, '')
                sort = 'date_order';
            } else if(column.indexOf( "_week" ) > -1) {
                sort = 'week_order';
            }
            var topColumn = {
                field : column,
                sort : sort,
                showAll: false,
                agregateType: "distinct",
                label: column.replace(/_/g, ' ')
            };
            columns[column] = topColumn;
            yfields.push(column)
        }
        //resultant columns
        var zfields = [];
        for (var i=0,len=response.resultColumns.length; i<len; i++){
            var column = response.resultColumns[i].toLowerCase();
            var dataFunctions = column.split('_');
            if(jQuery.inArray('sum', dataFunctions) != -1) {
                var agregateType = 'sum';
            } else if(jQuery.inArray('min', dataFunctions) != -1) {
                var agregateType = 'min';
            } else if(jQuery.inArray('max', dataFunctions) != -1) {
                var agregateType = 'max';
            } else if(jQuery.inArray('avg', dataFunctions) != -1) {
                var agregateType = 'average';
            } else if(jQuery.inArray('count', dataFunctions) != -1) {
                var agregateType = 'count';
            }
            var resultColumn = {
                field : column.replace(/&/g, 'and'),
                agregateType: agregateType,
                groupType: "none",
                label: column.replace(/_/g, ' ')
            };
            columns[column] = resultColumn;
            zfields.push(column);
        }
        var options = {
            fields : columns,
            xfields : xfields,
            yfields : yfields,
            zfields : zfields,
            data    : response.data,
            formatter: "VtPivotDataFormatter",
            copyright: false
        }
        jQuery('#pivot1').jbPivot(options);
    },

    registerEvents : function(){
        this._super();
        jQuery("#reportsRowsList").select2({
            maximumSelectionSize: 3
        });
        jQuery("#reportsColumnsList").select2({
            maximumSelectionSize: 3
        });
        jQuery("#reportsDataList").select2({
            maximumSelectionSize: 3
        });
        this.registerUpdateSelectElementEventForRows();
        this.registerUpdateSelectElementEventForColumns();
        Reports_PivotEdit3_Js.registerFieldForChosen();
        Reports_PivotEdit3_Js.initPivotFields();
		var pivotEditInstance = new Reports_PivotEdit3_Js();
		pivotEditInstance.lineItemCalculationLimit();
        this.registerSaveEvent();
        this.registerPivotTableEvent();
        this.arrangeSelectChoicesInOrder(jQuery('#pivot_rowfields'), jQuery('[name=hdnrowfields]'));
        this.arrangeSelectChoicesInOrder(jQuery('#pivot_columnfields'), jQuery('[name=hdncolumnfields]'));
        this.arrangeSelectChoicesInOrder(jQuery('#pivot_datafields'), jQuery('[name=hdndatafields]'));
    }
});
