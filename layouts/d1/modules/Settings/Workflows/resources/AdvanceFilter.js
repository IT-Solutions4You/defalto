/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
Vtiger_AdvanceFilter_Js('Workflows_AdvanceFilter_Js',{},{

    validationSupportedFieldConditionMap : {
        'email' : ['e','n'],
        'date' : ['is'],
        'datetime' : ['is']
    },
    //Hols field type for which there is validations always needed
    allConditionValidationNeededFieldList : ['double', 'integer'],

    // comparators which do not have any field Specific UI.
    comparatorsWithNoValueBoxMap : ['has changed','is empty','is not empty', 'is added'],

    getFieldSpecificType : function(fieldSelected) {
        const fieldInfo = fieldSelected.data('fieldinfo');

        return fieldInfo.type;
    },
	
    getModuleName : function() {
        return app.getModuleName();
    },


    /**
	 * Function to add new condition row
	 * @params : condtionGroupElement - group where condtion need to be added
	 * @return : current instance
	 */
    addNewCondition : function(conditionGroupElement){
        var basicElement = jQuery('.basic',conditionGroupElement);
        var newRowElement = basicElement.find('.conditionRow').clone(true,true);
        jQuery('select',newRowElement).addClass('select2');
        var conditionList = jQuery('.conditionList', conditionGroupElement);
        conditionList.append(newRowElement);

        //change in to chosen elements
        vtUtils.showSelect2ElementView(newRowElement.find('select.select2'));
        newRowElement.find('[name="columnname"]').find('optgroup:first option:first').attr('selected','selected').trigger('change');
        return this;
    },

    /**
	 * Function to load condition list for the selected field
     * (overrrided to remove "has changed" condition for related record fields in workflows)
	 * @params : fieldSelect - select element which will represents field list
	 * @return : select element which will represent the condition element
	 */
    loadConditions : function(fieldSelect) {
        var row = fieldSelect.closest('div.conditionRow');
        var conditionSelectElement = row.find('select[name="comparator"]');
        var conditionSelected = conditionSelectElement.val();
        var fieldSelected = fieldSelect.find('option:selected');
        var fieldLabel = fieldSelected.val();
        var match = fieldLabel.match(/\((\w+)\) (\w+)/);
        var fieldSpecificType = this.getFieldSpecificType(fieldSelected)
        var conditionList = this.getConditionListFromType(fieldSpecificType);
        //for none in field name
        if(typeof conditionList == 'undefined') {
            conditionList = {};
            conditionList['none'] = '';
        }
		var options = '';
		for(var key in conditionList) {
			//IE Browser consider the prototype properties also, it should consider has own properties only.
			if(conditionList.hasOwnProperty(key)) {
				var conditionValue = conditionList[key];
				var conditionLabel = this.getConditionLabel(conditionValue);
				if(match != null){
					if(conditionValue != 'has changed'){
						options += '<option value="'+conditionValue+'"';
						if(conditionValue == conditionSelected){
							options += ' selected="selected" ';
						}
						options += '>'+conditionLabel+'</option>';
					}
				}else{
					options += '<option value="'+conditionValue+'"';
					if(conditionValue == conditionSelected){
						options += ' selected="selected" ';
					}
					options += '>'+conditionLabel+'</option>';
				}
			}
		}
        conditionSelectElement.empty().html(options).trigger('change');
        // adding validation to comparator field
        conditionSelectElement.addClass('validate[required]');
        return conditionSelectElement;
    },
    
    /**
	 * Function to retrieve the values of the filter
	 * @return : object
	 */
    getValues : function() {
        var thisInstance = this;
        var filterContainer = this.getFilterContainer();

        var fieldList = new Array('columnname', 'comparator', 'value', 'valuetype', 'column_condition');

        var values = {};
        var columnIndex = 0;
        var conditionGroups = jQuery('.conditionGroup', filterContainer);
        conditionGroups.each(function(index,domElement){
            var groupElement = jQuery(domElement);

            var conditions = jQuery('.conditionList .conditionRow',groupElement);
            if(conditions.length <=0) {
                return true;
            }

            var iterationValues = {};
            conditions.each(function(i, conditionDomElement){
                var rowElement = jQuery(conditionDomElement);
                var fieldSelectElement = jQuery('[name="columnname"]', rowElement);
                var valueSelectElement = jQuery('[data-value="value"]',rowElement);
                //To not send empty fields to server
                if(thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
                    return true;
                }
                var fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo');
                var fieldType = fieldDataInfo.type;
                var rowValues = {};
				if (fieldType == 'picklist' || fieldType == 'multipicklist') {
                    for(var key in fieldList) {
                        var field = fieldList[key];
                        if(field == 'value' && valueSelectElement.is('input')) {
                            var commaSeperatedValues = valueSelectElement.val();
                            var pickListValues = valueSelectElement.data('picklistvalues');
                            var valuesArr = commaSeperatedValues.split(',');
                            var newvaluesArr = [];
                            for(i=0;i<valuesArr.length;i++){
                                if(typeof pickListValues[valuesArr[i]] != 'undefined'){
                                    newvaluesArr.push(pickListValues[valuesArr[i]]);
                                } else {
                                    newvaluesArr.push(valuesArr[i]);
                                }
                            }
                            var reconstructedCommaSeperatedValues = newvaluesArr.join(',');
                            rowValues[field] = reconstructedCommaSeperatedValues;
                        } else if(field == 'value' && valueSelectElement.is('select') && fieldType == 'picklist'){
                            rowValues[field] = valueSelectElement.val();
                        } else if(field == 'value' && valueSelectElement.is('select') && fieldType == 'multipicklist'){
                            var value = valueSelectElement.val();
                            if(value == null){
                                rowValues[field] = value;
                            } else {
                                rowValues[field] = value.join(',');
                            }
                        } else {
                            rowValues[field] = jQuery('[name="'+field+'"]', rowElement).val();
                        }
                    }
                 } else {
                    for(var key in fieldList) {
                        var field = fieldList[key];
                        if(field == 'value'){
                            if((fieldType == 'date' || fieldType == 'datetime') && valueSelectElement.length > 0) {
                                var value = valueSelectElement.val();
                                var dateFormat = app.getDateFormat();
                                var dateFormatParts = dateFormat.split("-");
                                var valueArray = value.split(',');
                                for(i = 0; i < valueArray.length; i++) {
                                    var valueParts = valueArray[i].split("-");
                                    var dateInstance = new Date(valueParts[dateFormatParts.indexOf('yyyy')], parseInt(valueParts[dateFormatParts.indexOf('mm')]) - 1, valueParts[dateFormatParts.indexOf('dd')]);
                                    if(!isNaN(dateInstance.getTime())) {
                                        valueArray[i] = app.getDateInVtigerFormat('yyyy-mm-dd', dateInstance);
                                    }
                                }
                                rowValues[field] = valueArray.join(',');
                            } else {
                                rowValues[field] = valueSelectElement.val();
                            }
						}  else {
                            rowValues[field] = jQuery('[name="'+field+'"]', rowElement).val();
                        }
                    }
                }

                if(jQuery('[name="valuetype"]', rowElement).val() == 'false' || (jQuery('[name="valuetype"]', rowElement).length == 0)) {
                    rowValues['valuetype'] = 'rawtext';
                }

                if(index == '0') {
                    rowValues['groupid'] = '0';
                } else {
                    rowValues['groupid'] = '1';
                }

                if(rowElement.is(":last-child")) {
                    rowValues['column_condition'] = '';
                }
                iterationValues[columnIndex] = rowValues;
                columnIndex++;
            });

            if(!jQuery.isEmptyObject(iterationValues)) {
                values[index+1] = {};
                //values[index+1]['columns'] = {};
                values[index+1]['columns'] = iterationValues;
            }
            if(groupElement.find('div.groupCondition').length > 0 && !jQuery.isEmptyObject(values[index+1])) {
                values[index+1]['condition'] = conditionGroups.find('div.groupCondition [name="condition"]').val();
            }
        });
        return values;

    },

    /**
	 * Functiont to get the field specific ui for the selected field
	 * @prarms : fieldSelectElement - select element which will represents field list
	 * @return : jquery object which represents the ui for the field
	 */
    getFieldSpecificUi : function(fieldSelectElement) {
        var fieldSelected = fieldSelectElement.find('option:selected');
        var fieldInfo = fieldSelected.data('fieldinfo');
        if(jQuery.inArray(fieldInfo.comparatorElementVal,this.comparatorsWithNoValueBoxMap) != -1){
            return jQuery('');
        } else {
            return this._super(fieldSelectElement);
        }
    }
});

Vtiger_Field_Js('Workflows_Field_Js',{},{

    getUiTypeSpecificHtml : function() {
        var uiTypeModel = this.getUiTypeModel();       
        return uiTypeModel.getUi();
    },

    getModuleName : function() {
        var currentModule = app.getModuleName();
        return currentModule;
    },

    /**
	 * Funtion to get the ui for the field  - generally this will be extend by the child classes to
	 * give ui type specific ui
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
    getUi : function() {
        let html = '<input type="text" class="WorkflowField getPopupUi inputElement form-control" name="'+ this.getName() +'"  /><input type="hidden" name="valuetype" value="'+this.get('workflow_valuetype')+'" />';
        html = jQuery(html);
        html.filter('.getPopupUi').val(app.htmlDecode(this.getValue()));

        return this.addValidationToElement(html);
    }
});

Workflows_Field_Js('Workflows_Text_Field_Js', {}, {
    getUi : function() {
        var html = '<textarea class="WorkflowTextField getPopupUi textarea inputElement form-control" name="'+this.getName()+'" value="">'+this.getValue()+'</textarea>'+
        '<input type="hidden" name="valuetype" value="'+this.get('workflow_valuetype')+'" />';
        html = jQuery(html);
        return this.addValidationToElement(html);
    }
});

Vtiger_Date_Field_Js('Workflows_Date_Field_Js',{},{

    /**
	 * Function to get the user date format
	 */
    getDateFormat : function(){
        return this.get('date-format');
    },

    /**
	 * Function to get the ui
	 * @return string|object input text field
	 */
    getUi: function () {
        let comparatorSelectedOptionVal = this.get('comparatorElementVal'),
            dateSpecificConditions = this.get('dateSpecificConditions'),
            html,
            element;

        if (comparatorSelectedOptionVal.length > 0) {
            if (comparatorSelectedOptionVal == 'between' || comparatorSelectedOptionVal == 'custom') {
                html = '<div class="date"><input class="dateField inputElement form-control" data-calendar-type="range" name="' + this.getName() + '" data-date-format="' + this.getDateFormat() + '" type="text" value="' + this.getValue() + '"></div>';
                element = jQuery(html);

                return this.addValidationToElement(element);
            } else if (this._specialDateComparator(comparatorSelectedOptionVal)) {
                html = '<input class="inputElement form-control" name="' + this.getName() + '" type="text" value="' + this.getValue() + '" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator="[{"name":"PositiveNumber"}]">\n\
							<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';

                return jQuery(html);
            } else if (comparatorSelectedOptionVal in dateSpecificConditions) {
                let startValue = dateSpecificConditions[comparatorSelectedOptionVal]['startdate'],
                    endValue = dateSpecificConditions[comparatorSelectedOptionVal]['enddate'];

                html = '<input class="WorkflowDateField form-control" name="' + this.getName() + '"  type="text" ReadOnly="true" value="' + startValue + ',' + endValue + '">'

                return jQuery(html);
            } else if (comparatorSelectedOptionVal == 'is today' || comparatorSelectedOptionVal == 'is tomorrow' || comparatorSelectedOptionVal == 'is yesterday') {
                //show nothing
            } else {
                return this._super();
            }
        } else {
            html = '<input type="text" class="getPopupUi date inputElement form-control" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
                '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
            element = jQuery(html);

            return this.addValidationToElement(element);
        }
    },

    _specialDateComparator : function(comp) {
        var specialComparators = ['less than days ago', 'more than days ago', 'in less than', 'in more than', 'days ago', 'days later', 'less than days later', 'more than days later'];
        for(var index in specialComparators) {
            if(comp == specialComparators[index]) {
                return true;
            }
        }
        return false;
    }
});

Vtiger_Date_Field_Js('Workflows_Datetime_Field_Js', {}, {
    /**
     * Function to get the user date format
     */
    getDateFormat: function () {
        return this.get('date-format');
    },

    /**
     * Function to get the ui
     * @return - input text field
     */
    getUi: function () {
        let comparatorSelectedOptionVal = this.get('comparatorElementVal'),
            html,
            element;

        if (this._specialDateTimeComparator(comparatorSelectedOptionVal)) {
            html = '<input name="' + this.getName() + '" class="inputElement form-control" type="text" value="' + this.getValue() + '" data-validator="[{name:PositiveNumber}]"><input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
            element = jQuery(html);
        } else if (comparatorSelectedOptionVal == 'is today' || comparatorSelectedOptionVal == 'is tomorrow' || comparatorSelectedOptionVal == 'is yesterday') {
            // show nothing
        } else {
            html = '<input type="text" class="getPopupUi datetime inputElement form-control" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
                '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />'
            element = jQuery(html);
        }

        return element;
    },

    _specialDateTimeComparator: function (comp) {
        let specialComparators = ['less than hours before', 'less than hours later', 'more than hours later', 'more than hours before', 'less than days ago', 'less than days later', 'more than days ago', 'more than days later', 'days ago', 'days later'];

        for (let index in specialComparators) {
            if (comp === specialComparators[index]) {
                return true;
            }
        }

        return false;
    }
});

Vtiger_Currency_Field_Js('Workflows_Currency_Field_Js', {}, {
    getUi: function () {
        let html = '<input type="text" class="WorkflowCurrencyField getPopupUi marginLeftZero currency inputElement form-control" name="' + this.getName() + '" value="' + this.getValue() + '"  />' + '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />', element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Time_Field_Js('Workflows_Time_Field_Js',{},{

    /**
	 * Function to get the ui
	 * @return - input text field
	 */
    getUi : function() {
        let html = '<input type="text" class="getPopupUi time inputElement" name="' + this.getName() + '" data-time-format="' + this.getTimeFormat() + '" value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
        const element = jQuery(html);

        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Percentage_Field_Js',{},{

    /**
	 * Function to get the ui
	 * @return - input percentage field
	 */
    getUi: function () {
        let html = '<input type="text" class="getPopupUi percent inputElement form-control" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
                '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />',
            element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Text_Field_Js',{},{

    /**
	 * Function to get the ui
	 * @return - input text field
	 */
    getUi : function() {
        var html = '<input type="text" class="getPopupUi text inputElement" name="'+ this.getName() +'" value="'+  this.getValue() + '" />'+
        '<input type="hidden" name="valuetype" value="'+this.get('workflow_valuetype')+'" />';
        var element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Boolean_Field_Js',{},{

    /**
	 * Function to get the ui
	 * @return - input text field
	 */
    getUi : function() {
        var html = '<input type="text" class="getPopupUi boolean inputElement" name="'+ this.getName() +'" value="'+  this.getValue() + '" />'+
        '<input type="hidden" name="valuetype" value="'+this.get('workflow_valuetype')+'" />';
        var element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Owner_Field_Js('Workflows_Owner_Field_Js',{},{

    getUi : function() {
        var html = '<select class="inputElement select2" name="'+ this.getName() +'">';
        html += '<option value="">&nbsp;</option>';
        var pickListValues = this.getPickListValues();
        var selectedOption = this.getValue();
        for(var optGroup in pickListValues){
            html += '<optgroup label="'+ optGroup +'">';
            var optionGroupValues = pickListValues[optGroup];
            for(var option in optionGroupValues) {
                html += '<option value="'+option+'" ';
                if(option == selectedOption) {
                    html += ' selected ';
                }
                html += '>'+optionGroupValues[option]+'</option>';
            }
            html += '</optgroup>'
        }

        html +='</select>';
        var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
        return selectContainer;
    }
});

Vtiger_Owner_Field_Js('Workflows_Ownergroup_Field_Js',{},{
	getUi : function() {
		var html = '<select class="select2 inputElement" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
		var pickListValues = this.getPickListValues();
		var selectedOption = this.getValue();
        html += '<option value=""></option>';
			for(var option in pickListValues) {
				html += '<option value="'+option+'" ';
				if(option == selectedOption) {
					html += ' selected ';
				}
				html += '>'+pickListValues[option]+'</option>';
			}
		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

AdvanceFilter_Picklist_Field_Js('Workflows_Picklist_Field_Js', {}, {
    getUi: function () {
        let html = '<select type="hidden" data-close-on-select="true" data-placeholder="' + app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION') + '" class="inputElement select2 form-select" name="' + this.getName() + '" id="' + this.getName() + '">' + this.getOptions() +  '</select>',
            selectContainer = jQuery(html);

        this.setSelected(selectContainer);
        this.addValidationToElement(selectContainer);

        return selectContainer;
    }
});

Vtiger_Multipicklist_Field_Js('Workflows_Multipicklist_Field_Js', {}, {

	getUi : function () {
		var selectedOptions = new Array();
		var selectedRawOption = app.htmlDecode(this.getValue());
		if (selectedRawOption) {
			var selectedOptions = selectedRawOption.split(',');
		}
		var pickListValues = this.getPickListValues();

		var tagsArray = new Array();
		var pickListValuesArrayFlip = {};
		var selectedOption = '';
		jQuery.map(pickListValues, function (pickListValue, key) {
			(jQuery.inArray(key, selectedOptions) !== -1);
			if (jQuery.inArray(key, selectedOptions) !== -1) {
				selectedOption += pickListValue+',';
			}
			tagsArray.push(pickListValue);
			pickListValuesArrayFlip[pickListValue] = key;
		})
		selectedOption = selectedOption.substring(0,selectedOption.lastIndexOf(','));

		var html = '<input type="hidden" class="col-lg-12 select2" name="'+this.getName()+'[]" id="'+this.getName()+'" data-fieldtype="multipicklist" >';
		var selectContainer = jQuery(html).val(selectedOption);
		selectContainer.data('tags', tagsArray).data('picklistvalues', pickListValuesArrayFlip);
		selectContainer.data('placeholder', app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')).data('closeOnSelect', true);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

Vtiger_Reference_Field_Js("Workflows_Reference_Field_Js",{},{
    getUi : function(){
        var referenceModules = this.getReferenceModules();
        var value = this.getValue();
        var html = '<div class="referencefield-wrapper';
        if(value){
            html += ' selected';
        }
        html += '">';
        html += '<input name="popupReferenceModule" type="hidden" value="'+referenceModules[0]+'"/>';
        html += '<div class="input-group">'; 
        html += '<input class="sourceField" name="'+this.getName()+'" type="hidden" value="'+value+'"/>';
        html += '<span class="clearReferenceSelectionWrapper"><input class="autoComplete inputElement ui-autocomplete-input text-truncate" type="text" data-fieldtype="reference" data-fieldmodule="'+this.get('workflow_field_modulename')+'" name="'+this.getName()+'_display" placeholder="Type to Search"';
        var reset = false;
        if(value){
            html += ' value="'+value+'" disabled="disabled"';
            reset = true;
        }
        html += '/>';
        
        if(reset){
            html += '<a href="#" class="clearReferenceSelection"><i class="fa fa-close p-l-8"></i></a>';
        }else {
            html += '<a href="#" class="clearReferenceSelection hide"><i class="fa fa-close p-l-8"></i></a>';
        }
        html += '</span>';
		html += '<div class="referenceLoadingWrapper hide"><svg class="referenceSpinner"><circle cx="20" cy="20" r="8"></circle><circle class="small" cx="20" cy="20" r="5"></circle></svg></div>';
        //popup search element
        html += '<span class="input-group-addon relatedPopup cursorPointer textAlignCenter" title="Select">';
        html += '<i class="fa fa-search p-l-8"></i>';
        html += '</span>';
        
        html += '</div>';
        html += '</div>'; 
        return this.addValidationToElement(html);
    }
});

Workflows_Reference_Field_Js("Workflows_Multireference_Field_Js",{},{});

Workflows_Field_Js('Workflows_Integer_Field_Js',{},{
	getUi : function() {
		if(this.getName() === 'profile_rating') {
			//Special handling for profile_rating field to show dropdown instead of input box as its integer field.
			var html = '<select class="select2 inputElement inlinewidth" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
			var pickListValues = {1 : 1, 2 : 2, 3 : 3, 4 : 4, 5 : 5};
			var selectedOption = parseInt(this.getValue());
			html += '<option value="">Select an Option</option>';
			for(var option in pickListValues) {
				html += '<option value="'+option+'" ';
				if(option == selectedOption) {
					html += ' selected ';
				}
				html += '>'+option+'</option>';
			}
			html +='</select>';
			var selectContainer = jQuery(html);
			this.addValidationToElement(selectContainer);
			return selectContainer;
		} else {
            let value = app.htmlDecode(this.getValue());
            value = value.replace(/"/g, "&quot;");

            let html = '<input value="'+value+'" type="text" class="WorkflowIntegerField getPopupUi inputElement form-control" name="'+ this.getName() +'"  /><input type="hidden" name="valuetype" value="'+this.get('workflow_valuetype')+'" />';

            return this.addValidationToElement(jQuery(html));
		}
	}
});

