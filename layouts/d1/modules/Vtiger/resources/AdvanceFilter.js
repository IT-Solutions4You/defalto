/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
/** @var Vtiger_AdvanceFilter_Js */
jQuery.Class("Vtiger_AdvanceFilter_Js",{

	getInstance: function(container){
		var module = app.getModuleName();
		var moduleClassName = module+"_AdvanceFilter_Js";
		var fallbackClassName = "Vtiger_AdvanceFilter_Js";
		if(typeof window[moduleClassName] != 'undefined'){
			var instance = new window[moduleClassName](container);
		}else{
			instance = new window[fallbackClassName](container);
		}
		return instance;
	}
},{

	filterContainer : false,
	//Hold the conditions for a particular field type
	fieldTypeConditionMapping : false,
	//Hold the condition and their label translations
	conditonOperatorLabelMapping : false,

    dateConditionInfo : false,

	fieldModelInstance : false,
	//Holds fields type and conditions for which it needs validation
	validationSupportedFieldConditionMap : {
				'email' : ['e','n']
	},
	//Hols field type for which there is validations always needed
	allConditionValidationNeededFieldList : ['double', 'integer', 'currency'],
	//used to eliminate mutiple times validation registrations
	validationForControlsRegistered : false,


	init : function(container) {
		if(typeof container == 'undefined') {
			container = jQuery('.filterContainer');
		}

		if(container.is('.filterContainer')) {
			this.setFilterContainer(container);
		}else{
			this.setFilterContainer(jQuery('.filterContainer',container));
		}
		this.initialize();
	},

	getModuleName : function() {
		return 'AdvanceFilter';
	},

	/**
	 * Function  to initialize the advance filter
	 */
	initialize : function() {
		this.registerEvents();
        this.changeFieldElementsView(this.getFilterContainer());
		this.initializeOperationMappingDetails();
		this.loadFieldSpecificUiForAll();
	},

    changeFieldElementsView : function(elementsContainer){
        vtUtils.showSelect2ElementView(elementsContainer.find('select.select2'));
        vtUtils.registerEventForDateFields(elementsContainer.find('.dateField'));
        vtUtils.registerEventForTimeFields(elementsContainer.find('.timepicker-default'));
    },

	/**
	 * Function which will save the field condition mapping condition label mapping
	 */
	initializeOperationMappingDetails : function() {
		var filterContainer = this.getFilterContainer();
		this.fieldTypeConditionMapping = jQuery('input[name="advanceFilterOpsByFieldType"]',filterContainer).data('value');
		this.conditonOperatorLabelMapping = jQuery('input[name="advanceFilterOptions"]',filterContainer).data('value');
        this.dateConditionInfo = jQuery('[name="date_filters"]').data('value');;
		return this;
	},

	/**
	 * Function to get the container which holds all the filter elements
	 * @return jQuery object
	 */
	getFilterContainer : function() {
		return this.filterContainer;
	},

	/**
	 * Function to set the filter container
	 * @params : element - which represents the filter container
	 * @return : current instance
	 */
	setFilterContainer : function(element) {
		this.filterContainer = element;
		return this;
	},

    getDateSpecificConditionInfo : function () {
        return this.dateConditionInfo;
    },

	/**
	 * Function which will return set of condition for the given field type
	 * @return array of conditions
	 */
	getConditionListFromType : function(fieldType){
		var fieldTypeConditions = this.fieldTypeConditionMapping[fieldType];
        if(fieldType == 'D' || fieldType == 'DT'){
            fieldTypeConditions = fieldTypeConditions.concat(this.getDateConditions(fieldType));
        }
        return fieldTypeConditions;
	},

    getDateConditions : function(fieldType) {
      if(fieldType != 'D' && fieldType != 'DT') {
          return new Array();
      }
      var filterContainer = this.getFilterContainer();
      var dateFilters = this.getDateSpecificConditionInfo();
      return Object.keys(dateFilters);
    },

	/**
	 * Function to get the condition label
	 * @param : key - condition key
	 * @reurn : label for the condition or key if it doest not contain in the condition label mapping
	 */
	getConditionLabel : function(key) {
		if(key in this.conditonOperatorLabelMapping){
			return this.conditonOperatorLabelMapping[key];
		}
        if(key in this.getDateSpecificConditionInfo()){
            return this.getDateSpecificConditionInfo()[key]['label'];
        }
		return key;
	},

	/**
	 * Function to check if the field selected is empty field
	 * @params : select element which represents the field
	 * @return : boolean true/false
	 */
	isEmptyFieldSelected : function(fieldSelect) {
		var selectedOption = fieldSelect.find('option:selected');
		//assumption that empty field will be having value none
		if(selectedOption.val() == 'none'){
			return true;
		}
		return false;
	},

	/**
	 * Function to get the add condition elements
	 * @returns : jQuery object which represents the add conditions elements
	 */
	getAddConditionElement : function() {
		var filterContainer = this.getFilterContainer();
		return jQuery('.addCondition button', filterContainer);
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
        newRowElement.addClass('op0');
        newRowElement.appendTo(conditionList);
        setTimeout(function(){
            newRowElement.addClass('fadeInx');
        },100)
		//change in to chosen elements
        vtUtils.showSelect2ElementView(newRowElement.find('select.select2'));

		return this;
	},

	/**
	 * Function/Handler  which will triggered when user clicks on add condition
	 */
	addConditionHandler : function(e) {
		var element = jQuery(e.currentTarget);
		var conditionGroup = element.closest('div.conditionGroup');
		this.addNewCondition(conditionGroup);
	},

	getFieldSpecificType: function (fieldSelected) {
		let fieldInfo = fieldSelected.data('fieldinfo');

		if ('undefined' !== typeof fieldInfo) {
			let type = fieldInfo.type;

			if (type === 'reference' || type === 'multireference') {
				return 'V';
			}
		}

		return fieldSelected.data('fieldtype');
	},

	/**
	 * Function to load condition list for the selected field
	 * @params : fieldSelect - select element which will represents field list
	 * @return : select element which will represent the condition element
	 */
	loadConditions : function(fieldSelect) {
		var row = fieldSelect.closest('div.conditionRow');
		var conditionSelectElement = row.find('select[name="comparator"]');
		var conditionSelected = conditionSelectElement.val();
		var fieldSelected = fieldSelect.find('option:selected');
		var fieldSpecificType = this.getFieldSpecificType(fieldSelected)
		var conditionList = this.getConditionListFromType(fieldSpecificType);

		var fieldInfo = fieldSelected.data('fieldinfo');
		if(typeof fieldInfo != 'undefined') {
			fieldType = fieldInfo.type;
		}
		//for none in field name
		if(typeof conditionList == 'undefined') {
			conditionList = {};
			conditionList['none'] = 'None';
		}

		var options = '';
		for(var key in conditionList) {
			if (fieldType == 'multipicklist' && (conditionList[key] == "e" || conditionList[key] == "n" )) { continue; }

			//IE Browser consider the prototype properties also, it should consider has own properties only.
			if(conditionList.hasOwnProperty(key)) {
				var conditionValue = conditionList[key];
				var conditionLabel = this.getConditionLabel(conditionValue);
				options += '<option value="'+conditionValue+'"';
				if(conditionValue == conditionSelected){
					options += ' selected="selected" ';
				}
				options += '>'+conditionLabel+'</option>';
			}
		}
		conditionSelectElement.empty().html(options).trigger("change");
		return conditionSelectElement;
	},

	/**
	 * Functiont to get the field specific ui for the selected field
	 * @prarms : fieldSelectElement - select element which will represents field list
	 * @return : jquery object which represents the ui for the field
	 */
	getFieldSpecificUi: function (fieldSelectElement) {
		let selectedOption = fieldSelectElement.find('option:selected'),
			fieldModel = this.fieldModelInstance;

		if (fieldModel.getType().toLowerCase() === "boolean") {
			let conditionRow = fieldSelectElement.closest('.conditionRow'),
				selectedValue = conditionRow.find('[data-value="value"]').val(),
				html = '<select class="select2 col-lg-12" name="' + fieldModel.getName() + '">';
			html += '<option value="0"';

			if (selectedValue == '0') {
				html += ' selected="selected" ';
			}

			html += '>' + app.vtranslate('JS_IS_DISABLED') + '</option>';

			html += '<option value="1"';

			if (selectedValue == '1') {
				html += ' selected="selected" ';
			}

			html += '>' + app.vtranslate('JS_IS_ENABLED') + '</option>';
			html += '</select>'

			return jQuery(html);
		} else if (fieldModel.getType().toLowerCase() == "reference") {
			let html = '<input class="form-control inputElement" type="text" name="' + fieldModel.getName() + '" data-label="' + fieldModel.get('label') + '" data-rule-' + fieldModel.getType() + '=true />';
			html = jQuery(html).val(app.htmlDecode(fieldModel.getValue()));

			return jQuery(html);
		} else {
			return jQuery(fieldModel.getUiTypeSpecificHtml())
		}
	},

	/**
	 * Function which will load the field specific ui for a selected field
	 * @prarms : fieldSelect - select element which will represents field list
	 * @return : current instance
	 */
	loadFieldSpecificUi: function (fieldSelect) {
		let selectedOption = fieldSelect.find('option:selected'),
			row = fieldSelect.closest('div.conditionRow'),
			fieldUiHolder = row.find('.fieldUiHolder'),
			conditionSelectElement = row.find('select[name="comparator"]'),
			fieldInfo = selectedOption.data('fieldinfo'),
			fieldType = 'string';

		if (typeof fieldInfo != 'undefined') {
			fieldType = fieldInfo.type;
		} else {
			fieldInfo = {};
		}

		let comparatorElementVal = fieldInfo.comparatorElementVal = conditionSelectElement.val();

		if (fieldType === 'date' || fieldType === 'datetime') {
			fieldInfo.dateSpecificConditions = this.getDateSpecificConditionInfo();
		}

		let moduleName = this.getModuleName(),
			fieldModel = Vtiger_Field_Js.getInstance(fieldInfo, moduleName);

		this.fieldModelInstance = fieldModel;

		let fieldSpecificUi = this.getFieldSpecificUi(fieldSelect);

		//remove validation since we dont need validations for all eleements
		// Both filter and find is used since we dont know whether the element is enclosed in some conainer like currency
		let fieldName = fieldModel.getName();

		if (fieldModel.getType() === 'multipicklist') {
			fieldName = fieldName + "[]";
		}

		if ((fieldModel.getType() === 'picklist' || fieldModel.getType() === 'owner') && fieldSpecificUi.is('select') && (comparatorElementVal === 'e' || comparatorElementVal === 'n')) {
			fieldName = fieldName + "[]";
		}

		if (fieldSpecificUi.find('.add-on').length > 0) {
			fieldSpecificUi.filter('.input-append').addClass('row-fluid');
			fieldSpecificUi.find('.input-append').addClass('row-fluid');
			fieldSpecificUi.filter('.input-prepend').addClass('row-fluid');
			fieldSpecificUi.find('.input-prepend').addClass('row-fluid');
			fieldSpecificUi.find('input[type="text"]').css('width', '79%');
		} else {
			fieldSpecificUi.filter('[name="' + fieldName + '"]').addClass('row-fluid');
			fieldSpecificUi.find('[name="' + fieldName + '"]').addClass('row-fluid');
		}

		fieldSpecificUi.filter('[name="' + fieldName + '"]').attr('data-value', 'value').removeAttr('data-validation-engine').addClass('ignore-validation');
		fieldSpecificUi.find('[name="' + fieldName + '"]').attr('data-value', 'value').removeAttr('data-validation-engine').addClass('ignore-validation');
		fieldSpecificUi.find('[name="' + fieldName + '"]').addClass('form-control');

		if (fieldModel.getType() === 'currency') {
			fieldSpecificUi.filter('[name="' + fieldName + '"]').attr('data-decimal-separator', fieldInfo.decimal_separator).attr('data-group-separator', fieldInfo.group_separator);
			fieldSpecificUi.find('[name="' + fieldName + '"]').attr('data-decimal-separator', fieldInfo.decimal_separator).attr('data-group-separator', fieldInfo.group_separator);
		}

		fieldUiHolder.html(fieldSpecificUi);

		if (fieldSpecificUi.is('input.select2')) {
			let tagElements = fieldSpecificUi.data('tags'),
				params = {tags: tagElements, tokenSeparators: [","]}

			vtUtils.showSelect2ElementView(fieldSpecificUi, params);
		} else if (fieldSpecificUi.is('select')) {
			if (fieldSpecificUi.hasClass('chzn-select')) {
				app.changeSelectElementView(fieldSpecificUi)
			} else {
				vtUtils.showSelect2ElementView(fieldSpecificUi);
			}
		} else if (fieldSpecificUi.has('input.dateField').length > 0) {
			vtUtils.registerEventForDateFields(fieldSpecificUi);
		} else if (fieldSpecificUi.has('input.timepicker-default').length > 0) {
			vtUtils.registerEventForTimeFields(fieldSpecificUi);
		}
		this.addValidationToFieldIfNeeded(fieldSelect);

		let comparatorContainer = conditionSelectElement.closest('[class^="conditionComparator"]');
		//if it is check box then we need hide the comprator
		if (fieldModel.getType().toLowerCase() === 'boolean') {
			//making the compator as equal for check box
			conditionSelectElement.find('option[value="e"]').attr('selected', 'selected');
			comparatorContainer.hide();
		} else {
			comparatorContainer.show();
		}

		// Is Empty, today, tomorrow, yesterday conditions does not need any field input value - hide the UI
		// re-enable if condition element is chosen.
		let specialConditions = ["y", "today", "tomorrow", "yesterday", "ny"];

		if (specialConditions.indexOf(conditionSelectElement.val()) !== -1) {
			fieldUiHolder.hide();
		} else {
			fieldUiHolder.show();
		}

		return this;
	},

	/**
	 * Function to load field specific ui for all the select elements - this is used on load
	 * to show field specific ui for all the fields
	 */
	loadFieldSpecificUiForAll : function() {
		var conditionsContainer = jQuery('.conditionList');
		var fieldSelectElement = jQuery('select[name="columnname"]',conditionsContainer);
		jQuery.each(fieldSelectElement,function(i,elem){
			var currentElement = jQuery(elem);
			if(currentElement.val() != 'none'){
				currentElement.trigger('change', {'_intialize': true});
			}
		});
		return this;
	},

	/**
	 * Function to add the validation if required
	 * @prarms : selectFieldElement - select element which will represents field list
	 */
	addValidationToFieldIfNeeded: function (selectFieldElement) {
		let selectedOption = selectFieldElement.find('option:selected'),
			row = selectFieldElement.closest('div.conditionRow'),
			fieldSpecificElement = row.find('[data-value="value"]'),
			validator = selectedOption.attr('data-validator');

		if (this.isFieldSupportsValidation(selectFieldElement)) {
			//data attribute will not be present while attaching validation engine events . so we are
			//depending on the fallback option which is class
			//TODO : remove the hard coding and get it from field element data-validation-engine
			if ('undefined' !== typeof Vtiger_Base_Validator_Js) {
				fieldSpecificElement.addClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]').attr('data-validation-engine', 'validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]').attr('data-fieldinfo', JSON.stringify(selectedOption.data('fieldinfo')));
			}

			if (typeof validator != 'undefined') {
				fieldSpecificElement.attr('data-validator', validator);
			}

			fieldSpecificElement.removeClass('ignore-validation');
		} else {
			if ('undefined' !== typeof Vtiger_Base_Validator_Js) {
				fieldSpecificElement.removeClass('validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]').removeAttr('data-validation-engine').removeAttr('data-fieldinfo');
			}

			fieldSpecificElement.addClass('ignore-validation');
		}

		return this;
	},

	/**
	 * Check if field supports validation
	 * @prarms : selectFieldElement - select element which will represents field list
	 * @return - boolen true/false
	 */
	isFieldSupportsValidation : function(fieldSelect) {
		var selectedOption = fieldSelect.find('option:selected');

		var fieldModel = this.fieldModelInstance;
		var type = fieldModel.getType();

		if(jQuery.inArray(type,this.allConditionValidationNeededFieldList) >= 0) {
			return true;
		}

		var row = fieldSelect.closest('div.conditionRow');
		var conditionSelectElement = row.find('select[name="comparator"]');
		var selectedCondition = conditionSelectElement.find('option:selected');

		var conditionValue = conditionSelectElement.val();

		if(type in this.validationSupportedFieldConditionMap) {
			if( jQuery.inArray(conditionValue, this.validationSupportedFieldConditionMap[type]) >= 0 ){
				return true;
			}
		}
		return false;
	},

	/**
	 * Function to retrieve the values of the filter
	 * @return : object
	 */
	getValues : function() {
		var thisInstance = this;
		var filterContainer = this.getFilterContainer();

		var fieldList = new Array('columnname', 'comparator', 'value', 'column_condition');

		var values = {};
		var columnIndex = 0;
		var conditionGroups = jQuery('.conditionGroup', filterContainer);
		conditionGroups.each(function(index,domElement){
			var groupElement = jQuery(domElement);
			values[index+1] = {};
			var conditions = jQuery('.conditionList .conditionRow',groupElement);
			values[index+1]['columns'] = {};
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
				if(fieldType == 'owner' || fieldType == 'ownergroup'){
					for(var key in fieldList) {
						var field = fieldList[key];
						if(field == 'value' && valueSelectElement.is('select')){
							var selectedOptions = valueSelectElement.find('option:selected');
							var newvaluesArr = [];
							jQuery.each(selectedOptions,function(i,e) {
								newvaluesArr.push(jQuery.trim(jQuery(e).text()));
							});
							if(selectedOptions.length == 0){
								rowValues[field] = '';
							} else {
								rowValues[field] = newvaluesArr.join(',');
							}

						} else if(field == 'value' && valueSelectElement.is('input')) {
							rowValues[field] = valueSelectElement.val();
						} else {
							rowValues[field] = jQuery('[name="'+field+'"]', rowElement).val();
						}
					}
				} else if (fieldType == 'picklist' || fieldType == 'multipicklist') {
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
							var value = valueSelectElement.val();
							if(value == null){
								rowValues[field] = value;
							} else {
								rowValues[field] = value.join(',');
							}
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
							rowValues[field] = valueSelectElement.val();
						}  else {
							rowValues[field] = jQuery('[name="'+field+'"]', rowElement).val();
						}
					}
				}

				if(rowElement.is(":last-child")) {
					rowValues['column_condition'] = '';
				}
				values[index+1]['columns'][columnIndex] = rowValues;
				columnIndex++;
			});
			if(groupElement.find('div.groupCondition').length > 0) {
				values[index+1]['condition'] = conditionGroups.find('div.groupCondition [name="condition"]').val();
			}
		});
		return values;

	},

	/**
	 * Event handle which will be triggred on deletion of a condition row
	 */
	deleteConditionHandler : function(e) {
		var element = jQuery(e.currentTarget);
		var row = element.closest('.conditionRow');
		row.remove();
	},

	/**
	 * Event handler which is invoked on add condition
	 */
	registerAddCondition : function() {
		var thisInstance = this;
		this.getAddConditionElement().on('click',function(e){
			thisInstance.addConditionHandler(e);
		});
	},

	/**
	 * Function which will register field change event
	 */
	registerFieldChange : function() {
		var filterContainer = this.getFilterContainer();
		var thisInstance = this;
		filterContainer.on('change','select[name="columnname"]',function(e,data){
            var currentElement = jQuery(e.currentTarget);
            if(typeof data == 'undefined' || data._intialize != true){
                var row = currentElement.closest('div.conditionRow');
                var conditionSelectElement = row.find('select[name="comparator"]');
                conditionSelectElement.empty();
            }
			thisInstance.loadConditions(currentElement);
			thisInstance.loadFieldSpecificUi(currentElement);
		});
	},

	/**
	 * Function which will register condition change
	 */
	registerConditionChange : function() {
		var filterContainer = this.getFilterContainer();
		var thisInstance = this;
		filterContainer.on('change','select[name="comparator"]', function(e){
			var comparatorSelectElement = jQuery(e.currentTarget);
			var row = comparatorSelectElement.closest('div.conditionRow');
			var fieldSelectElement = row.find('select[name="columnname"]');
			var selectedOption = fieldSelectElement.find('option:selected');
			//To handle the validation depending on condtion
			thisInstance.loadFieldSpecificUi(fieldSelectElement);
			thisInstance.addValidationToFieldIfNeeded(fieldSelectElement);
		});
	},

	/**
	 * Function to regisgter delete condition event
	 */
	registerDeleteCondition : function() {
		var thisInstance = this;
		var filterContainer = this.getFilterContainer();
		filterContainer.on('click', '.deleteCondition', function(e){
			thisInstance.deleteConditionHandler(e);
		});
	},

	/**
	 * Function which will regiter all events for this page
	 */
	registerEvents : function(){
		this.registerAddCondition();
		this.registerFieldChange();
		this.registerDeleteCondition();
		this.registerConditionChange();
	}
});

/** @var AdvanceFilter_Field_Js */
Vtiger_Field_Js('AdvanceFilter_Field_Js', {}, {

	getUiTypeSpecificHtml: function () {
		let uiTypeModel = this.getUiTypeModel();

		return uiTypeModel.getUi();
	},

	getModuleName: function () {
		let currentModule = app.getModuleName(),
			type = this.getType();

		if (type === 'picklist' || type === 'multipicklist' || type === 'owner' || type === 'ownergroup' || type === 'date' || type === 'datetime' || type === 'currencyList') {
			currentModule = 'AdvanceFilter';
		}

		return currentModule;
	}
});

/** @var AdvanceFilter_Currencylist_Field_Js */
Vtiger_Currencylist_Field_Js('AdvanceFilter_Currencylist_Field_Js',{},{
	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi: function () {
		let html = '<select class="CurrencyListField select2 inputElement form-control" name="' + this.getName() + '" id="field_' + this.getModuleName() + '_' + this.getName() + '">',
			currencyLists = this.getCurrencyList(),
			selectedOption = app.htmlDecode(this.getValue());

		for (let option in currencyLists) {
			html += '<option value="' + currencyLists[option] + '" ';

			if (option == selectedOption) {
				html += ' selected ';
			}

			html += '>' + currencyLists[option] + '</option>';
		}

		html += '</select>';
		let selectContainer = jQuery(html);

		this.addValidationToElement(selectContainer);

		return selectContainer;
	}
});

/** @var AdvanceFilter_Picklist_Field_Js */
Vtiger_Picklist_Field_Js('AdvanceFilter_Picklist_Field_Js',{},{

    /**
	 * Function to get the picklist values
	 */
	getPickListValues : function() {
            return this.get('picklistvalues');
	},
	getOptions: function () {
		let html = '',
			pickListValues = this.getPickListValues();

		for (let picklistKey in pickListValues) {
			html += '<option value="' + picklistKey + '">' + pickListValues[picklistKey] + '</option>';
		}

		return html;
	},
	setSelected: function (element) {
		let selectedOption = app.htmlDecode(this.getValue()),
			selectedOptions = selectedOption.split(',');

		$.each(selectedOptions, function (key, value) {
			let option = element.find('option[value="' + value + '"]');

			if (value) {
				if (option.length) {
					option.attr('selected', 'selected');
				} else {
					element.append('<option value="' + value + '" selected="selected">' + value + '</option>');
				}
			}
		});

		return element;
	},
	getUi: function () {
		let comparatorSelectedOptionVal = this.get('comparatorElementVal');

		if (comparatorSelectedOptionVal === 'e' || comparatorSelectedOptionVal === 'n') {
			let html = '<select class="PicklistField select2 inputElement form-control" multiple name="' + this.getName() + '[]">' + this.getOptions() + '</select>';

			let selectContainer = jQuery(html);

			this.setSelected(selectContainer);
			this.addValidationToElement(selectContainer);

			return selectContainer;
		} else {
			let html = '<select multiple data-tags="true" data-multiple="true" class="form-control col-lg-12 select2" name="' + this.getName() + '">' + this.getOptions() + '</select>',
				selectContainer = jQuery(html);

			this.setSelected(selectContainer);
			this.addValidationToElement(selectContainer);

			return selectContainer;
		}
	}
});

/** @var AdvanceFilter_Multipicklist_Field_Js */
Vtiger_Multipicklist_Field_Js('AdvanceFilter_Multipicklist_Field_Js',{},{

    /**
    * Function to get the picklist values
    */
    getPickListValues : function() {
       return this.get('picklistvalues');
    },
    getSelectedOptions : function(selectedOption){
        var valueArray = selectedOption.split(',');
        var selectedOptionsArray = [];
        for(var i=0;i<valueArray.length;i++){
            selectedOptionsArray.push(valueArray[i].trim());
        }
        return selectedOptionsArray;
    },
	getOptions: function () {
		let html = '',
			pickListValues = this.getPickListValues();

		for (let picklistKey in pickListValues) {
			html += '<option value="' + option + '">' + pickListValues[picklistKey] + '</option>';
		}

		return html;
	},
	setSelected: function (element) {
		let selectedOption = app.htmlDecode(this.getValue()),
			selectedOptions = selectedOption.split(',');

		$.each(selectedOptions, function (key, value) {
			let option = element.find('option[value="' + value + '"]');

			if (option.length) {
				option.attr('selected', 'selected');
			} else {
				element.append('<option value="' + value + '" selected="selected">' + value + '</option>');
			}
		});

		return element;
	},
	getUi: function () {
		let comparatorSelectedOptionVal = this.get('comparatorElementVal');

		if (comparatorSelectedOptionVal !== 'e' && comparatorSelectedOptionVal !== 'n') {
			let html = '<select data-tags="true" data-multiple="true" class="row-fluid inputElement form-control select2" name="' + this.getName() + '[]">'+ this.getOptions() + '</select>',
				selectContainer = jQuery(html);

			this.setSelected(selectContainer);
			this.addValidationToElement(selectContainer);

			return selectContainer;
		} else {
			return this._super();
		}
	}
});

/** @var AdvanceFilter_Owner_Field_Js */
Vtiger_Owner_Field_Js('AdvanceFilter_Owner_Field_Js',{},{
	getOptions: function () {
		let html = '',
			pickListValues = this.getPickListValues();

		for (let optGroup in pickListValues) {
			html += '<optgroup label="' + optGroup + '">'
			let optionGroupValues = pickListValues[optGroup];

			for (let option in optionGroupValues) {
				html += '<option value="' + option + '">' + optionGroupValues[option] + '</option>';
			}

			html += '</optgroup>'
		}

		return html;
	},
	setSelected: function (element) {
		let selectedOption = app.htmlDecode(this.getValue()),
			selectedOptions = selectedOption.split(',');

		$.each(selectedOptions, function (key, value) {
			let option = element.find('option[value="' + value + '"]');

			if (option.length) {
				option.attr('selected', 'selected');
			} else {
				element.append('<option value="' + value + '" selected="selected">' + value + '</option>');
			}
		});

		return element;
	},
	getUi: function () {
		let comparatorSelectedOptionVal = this.get('comparatorElementVal'),
			html = '';

		if (comparatorSelectedOptionVal === 'e' || comparatorSelectedOptionVal === 'n') {
			html = '<select class="OwnerField select2 inputElement form-control row-fluid" multiple name="' + this.getName() + '[]">' + this.getOptions() + '</select>';
		} else {
			html = '<select class="OwnerField row-fluid col-lg-12 select2" data-tags="true" data-multiple="true" multiple type="hidden"  name="' + this.getName() + '">' + this.getOptions() + '</select>';
		}

		let selectContainer = jQuery(html);

		this.setSelected(selectContainer);
		this.addValidationToElement(selectContainer);

		return selectContainer;
	}
});

/** @var Vtiger_Ownergroup_Field_Js */
Vtiger_Owner_Field_Js('Vtiger_Ownergroup_Field_Js',{},{
	getUi: function () {
		let html = '<select class="OwnerGroupField select2 inputElement form-control" name="' + this.getName() + '" multiple id="field_' + this.getModuleName() + '_' + this.getName() + '">',
			pickListValues = this.getPickListValues(),
			selectedOption = this.getValue(),
			selectedOptionsArray = selectedOption.split(',');

		for (let option in pickListValues) {
			html += '<option value="' + option + '" ';
			//comparing with the value instead of key , because saved value is giving username instead of id.
			if (jQuery.inArray(jQuery.trim(app.htmlDecode(pickListValues[option])), selectedOptionsArray) !== -1) {
				html += ' selected ';
			}

			html += '>' + pickListValues[option] + '</option>';
		}

		html += '</select>';
		let selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);

		return selectContainer;
	}
});

/** @var AdvanceFilter_Date_Field_Js */
Vtiger_Date_Field_Js('AdvanceFilter_Date_Field_Js', {}, {
	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi: function () {
		let comparatorSelectedOptionVal = this.get('comparatorElementVal'),
			dateSpecificConditions = this.get('dateSpecificConditions');

		if (comparatorSelectedOptionVal === 'bw' || comparatorSelectedOptionVal === 'custom') {
			let html = '<div class="input-group date"><input class="form-control inputElement dateField" data-calendar-type="range" name="' + this.getName() + '" data-date-format="' + this.getDateFormat() + '" type="text" value="' + this.getValue() + '"></div>',
				element = jQuery(html),
				dateFieldUi = element.find('.dateField');

			if (dateFieldUi.val().indexOf(',') !== -1) {
				let valueArray = this.getValue().split(','),
					startDateTime = valueArray[0],
					endDateTime = valueArray[1];

				if (startDateTime.indexOf(' ') !== -1) {
					let dateTime = startDateTime.split(' ');
					startDateTime = dateTime[0];
				}

				if (endDateTime.indexOf(' ') !== -1) {
					let dateTimeValue = endDateTime.split(' ');
					endDateTime = dateTimeValue[0];
				}

				dateFieldUi.val(startDateTime + ',' + endDateTime);
			} else {
				// while changing to between/custom from equal/notequal/... we'll only have one value
				let value = this.getValue().split(' '),
					startDate = value[0],
					endDate = value[0];

				if (startDate != '' && endDate != '') {
					dateFieldUi.val(startDate + ',' + endDate);
				}
			}

			return this.addValidationToElement(element);
		} else if (this._specialDateComparator(comparatorSelectedOptionVal)) {
			let html = '<input name="' + this.getName() + '" type="text" value="' + this.getValue() + '" />';

			return jQuery(html);
		} else if (comparatorSelectedOptionVal in dateSpecificConditions) {
			let startValue = dateSpecificConditions[comparatorSelectedOptionVal]['startdate'],
				endValue = dateSpecificConditions[comparatorSelectedOptionVal]['enddate'],
				html = '';

			if (comparatorSelectedOptionVal === 'today' || comparatorSelectedOptionVal === 'tomorrow' || comparatorSelectedOptionVal === 'yesterday') {
				html = '<input name="' + this.getName() + '" type="text" ReadOnly="true" value="' + startValue + '">';
			} else {
				html = '<input name="' + this.getName() + '" type="text" ReadOnly="true" value="' + startValue + ',' + endValue + '">';
			}

			return jQuery(html);
		} else {
			let fieldUi = this._super(),
				dateTimeFieldValue = fieldUi.find('.dateField').val(),
				dateValue = dateTimeFieldValue.split(' ');

			if (dateValue[1] === '00:00:00') {
				dateTimeFieldValue = dateValue[0];
			} else if (comparatorSelectedOptionVal === 'e' || comparatorSelectedOptionVal === 'n' || comparatorSelectedOptionVal === 'b' || comparatorSelectedOptionVal === 'a') {
				let dateTimeArray = dateTimeFieldValue.split(' ');
				dateTimeFieldValue = dateTimeArray[0];
			}

			fieldUi.find('.dateField').val(dateTimeFieldValue);

			return fieldUi;
		}
	},

	_specialDateComparator: function (comp) {
		var specialComparators = ['lessthandaysago', 'lessthandayslater', 'morethandaysago', 'morethandayslater', 'inlessthan', 'inmorethan', 'daysago', 'dayslater', 'lessthanhoursbefore', 'lessthanhourslater', 'morethanhoursbefore', 'morethanhourslater'];
		for (var index in specialComparators) {
			if (comp == specialComparators[index]) {
				return true;
			}
		}
		return false;
	}
});

/** @var AdvanceFilter_Datetime_Field_Js */
AdvanceFilter_Date_Field_Js('AdvanceFilter_Datetime_Field_Js',{},{

});