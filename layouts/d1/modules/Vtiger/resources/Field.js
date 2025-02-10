/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
/** @var Vtiger_Field_Js */
jQuery.Class("Vtiger_Field_Js",{

	/**
	 * Function to get Instance of the class based on moduleName
	 * @param data,data to set
	 * @param moduleName module for which Instance should be created
	 * @return Instance of field class
	 */
	getInstance: function (data, moduleName) {
		if (typeof moduleName == 'undefined') {
			moduleName = app.getModuleName();
		}

		let moduleField = moduleName + "_Field_Js",
			moduleFieldObj = window[moduleField],
			fieldClass

		if (typeof moduleFieldObj != 'undefined') {
			fieldClass = moduleFieldObj;
		} else {
			fieldClass = Vtiger_Field_Js;
		}

		let fieldObj = new fieldClass();

		if (typeof data == 'undefined') {
			data = {};
		}

		fieldObj.setData(data);

		return fieldObj;
	}
},{
	data : {},
	/**
	 * Function to check whether field is mandatory or not
	 * @return true if feld is madatory
	 * @return false if field is not mandatory
	 */
	isMandatory : function(){
		return this.get('mandatory');
	},


	/**
	 * Function to get the value of particular key in object
	 * @return value for the passed key
	 */

	get : function(key){
		if(key in this.data){
			return this.data[key];
		}
		return '';
	},


	/**
	 * Function to get type attribute of the object
	 * @return type attribute of the object
	 */
	getType : function(){
		return this.get('type');
	},

	/**
	 * Function to get name of the field
	 * @return <String> name of the field
	 */
	getName : function() {
		return this.get('name');
	},

	/**
	 * Function to get value of the field
	 * @return <Object> value of the field or empty of there is not value
	 */
	getValue : function() {
		if('value' in this.getData()){
			return this.get('value');
		} else if('defaultValue' in this.getData()){
			return this.get('defaultValue');
		}
		return '';
	},

	/**
	 * Function to get the whole data
	 * @return <object>
	 */
	getData : function() {
		return this.data;
	},

	/**
	 * Function to set data attribute of the class
	 * @return Instance of the class
	 */
	setData : function(fieldInfo){
		this.data = fieldInfo;
		return this;
	},

	getModuleName : function() {
		return app.getModuleName();
	},

	/**
	 * Function to get the ui type specific model
	 */
	getUiTypeModel : function() {
		var currentModule = this.getModuleName();

		var type = this.getType();
		var typeClassName = type.charAt(0).toUpperCase() + type.slice(1).toLowerCase();

		var moduleUiTypeClassName = window[currentModule + "_" + typeClassName+"_Field_Js"];
		var BasicUiTypeClassName = window["Vtiger_"+ typeClassName + "_Field_Js"];

		if(typeof moduleUiTypeClassName != 'undefined') {
			var instance = new moduleUiTypeClassName();
			return instance.setData(this.getData());
		}else if (typeof BasicUiTypeClassName != 'undefined') {
			var instance = new BasicUiTypeClassName();
			return instance.setData(this.getData());
		}
		return this;
	},

	/**
	 * Funtion to get the ui for the field - generally this will be extend by the child classes to
	 * give ui type specific ui
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUi: function () {
		let html = '<input class="inputElement form-control" type="text" name="' + this.getName() + '" data-label="' + this.get('label') + '" data-rule-' + this.getType() + '=true />';
		html = jQuery(html).val(app.htmlDecode(this.getValue()));

		return this.addValidationToElement(html);
	},

	/**
	 * Function to get the ui for a field depending on the ui type
	 * this will get the specific ui depending on the field type
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUiTypeSpecificHtml : function() {
		var uiTypeModel = this.getUiTypeModel();
		return uiTypeModel.getUi();
	},

	/**
	 * Function to add the validation for the element
	 */
	addValidationToElement: function (element) {
		element = jQuery(element);

		let addValidationToElement = element,
			elementInStructure = element.find('[name="' + this.getName() + '"]'),
			type = this.getType();

		if (elementInStructure.length > 0) {
			addValidationToElement = elementInStructure;
		}

		if (this.isMandatory()) {
			addValidationToElement.attr('data-rule-required', 'true');

			if ('reference' === type) {
				addValidationToElement.attr('data-rule-reference_required', 'true');
			}
		}

		addValidationToElement.attr('data-fieldinfo', JSON.stringify(this.getData())).attr('data-specific-rules', JSON.stringify(this.getData().specialValidator));

		return element;
	},

	getNewFieldInfo : function() {
		return this.get('newfieldinfo');
	},

})

/** @var Vtiger_Reference_Field_Js */
Vtiger_Field_Js('Vtiger_Reference_Field_Js',{},{

	getReferenceModules : function(){
		return this.get('referencemodules');
	},

	getUi: function () {
		let referenceModules = this.getReferenceModules(),
			value = this.getValue(),
			html = '<div class="ReferenceField w-100 ';

		if (value) {
			html += 'selected';
		}

		html += '">';
		html += '<input name="popupReferenceModule" type="hidden" value="' + referenceModules[0] + '"/>';
		html += '<div class="input-group">'
		html += '<input class="autoComplete form-control inputElement sourceField" type="search" data-fieldtype="reference" name="' + this.getName() + '"';

		let reset = false;

		if (value) {
			html += ' value="' + value + '" readonly="readonly"';
			reset = true;
		}

		html += '/>';

		if (reset) {
			html += '<a href="#" class="input-group-text clearReferenceSelection"> X </a>';
		} else {
			html += '<a href="#" class="input-group-text clearReferenceSelection hide"> X </a>';
		}
		//popup search element
		html += '<span class="input-group-addon input-group-text relatedPopup cursorPointer" title="' + referenceModules[0] + '">';
		html += '<i class="fa fa-search"></i>';
		html += '</span>';

		html += '</div>';
		html += '</div>';

		return this.addValidationToElement(html);
	}
});

/** @var Vtiger_Picklist_Field_Js */
Vtiger_Field_Js('Vtiger_Picklist_Field_Js',{},{

	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('editablepicklistvalues');
	},

	/**
	 * Function to get all pick list values
	 * @return <object> key value options pair
	 */
	getAllPickListValues : function() {
		return this.get('picklistvalues');
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi: function () {
		//added class inlinewidth
		let html = '<select class="PicklistField select2 inputElement inlinewidth" data-width="100%" name="' + this.getName() + '" id="field_' + this.getModuleName() + '_' + this.getName() + '">',
			pickListValues = this.getPickListValues(),
			allPickListValues = this.getAllPickListValues(),
			selectedOption = app.htmlDecode(this.getValue()),
			selectedOptionfound = false,
			data = this.getData(),
			picklistColors = data['picklistColors'],
			fieldName = this.getName();

		if (typeof pickListValues[' '] === 'undefined' || pickListValues[' '].length <= 0 || pickListValues[' '] !== 'Select an Option') {
			html += '<option value="">' + app.vtranslate('JS_SELECT_OPTION') + '</option>';
		}

		for (let option in pickListValues) {
			html += '<option value="' + option + '" ';

			if (picklistColors) {
				let className = '';
				if (picklistColors[option]) {
					className = 'picklistColor_' + fieldName + '_' + option.replace(' ', '_');
					html += 'class="' + className + '"';
				}
			}

			if (option === selectedOption) {
				html += ' selected ';
				selectedOptionfound = true;
			}

			html += '>' + pickListValues[option] + '</option>';
		}

		if (!selectedOptionfound && allPickListValues[selectedOption] !== undefined) {
			html += '<option value="' + selectedOption + '" selected>' + allPickListValues[selectedOption] + '</option>';
		}

		html += '</select>';

		if (picklistColors) {
			html += '<style type="text/css">';

			for (let option in picklistColors) {
				const picklistColor = picklistColors[option];
				if (picklistColor) {
					className = '.picklistColor_' + fieldName + '_' + option.replace(' ', '_');
					html += className + '{background-color: ' + picklistColor + ' !important;}';

					className = className + '.select2-highlighted';
					html += className + '{white: #ffffff !important; background-color: #337ab7 !important;}';
				}
			}

			html += '</style>';
		}

		const selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);

		return selectContainer;
	}
});

/** @var Vtiger_Documentsfolder_Field_Js */
Vtiger_Field_Js('Vtiger_Documentsfolder_Field_Js',{},{

	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('documentFolders');
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi: function () {
		//added class inlinewidth
		let html = '<select class="DocumentsFolderField select2 inputElement inlinewidth" name="' + this.getName() + '" id="field_' + this.getModuleName() + '_' + this.getName() + '">',
			pickListValues = this.getPickListValues(),
			selectedOption = app.htmlDecode(this.getValue());

		if (typeof pickListValues[' '] == 'undefined' || pickListValues[' '].length <= 0 || pickListValues[' '] !== 'Select an Option') {
			html += '<option value="">' + app.vtranslate('JS_SELECT_OPTION') + '</option>';
		}

		$.each(pickListValues, function(option, value) {
			html += '<option value="' + value + '" ' + (value === selectedOption ? 'selected': '') + ' >' + value + '</option>';
		})

		html += '</select>';

		let selectContainer = jQuery(html);

		this.addValidationToElement(selectContainer);

		return selectContainer;
	}
});

/** @var Vtiger_Currencylist_Field_Js */
Vtiger_Field_Js('Vtiger_Currencylist_Field_Js',{},{

	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getCurrencyList : function() {
		return this.get('currencyList');
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi: function () {
		let html = '<select class="CurrencyListField select2 inputElement form-select" name="' + this.getName() + '" id="field_' + this.getModuleName() + '_' + this.getName() + '">',
			currencyLists = this.getCurrencyList(),
			selectedOption = app.htmlDecode(this.getValue());

		for (let option in currencyLists) {
			html += '<option value="' + option + '" ';

			if (option === selectedOption) {
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

/** @var Vtiger_Multipicklist_Field_Js */
Vtiger_Field_Js('Vtiger_Multipicklist_Field_Js',{},{
	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},

	getSelectedOptions : function(selectedOption){
		var valueArray = selectedOption.split('|##|');
		var selectedOptionsArray = [];
		for(var i=0;i<valueArray.length;i++){
			selectedOptionsArray.push(valueArray[i].trim());
		}
		return selectedOptionsArray;
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi : function() {
		let html = '<select class="MultiPicklistField select2 inputElement form-select" multiple name="'+ this.getName() +'[]" id="field_'+this.getModuleName()+'_'+this.getName()+'">',
			pickListValues = this.getPickListValues(),
			selectedOption = app.htmlDecode(this.getValue()),
			selectedOptionsArray = this.getSelectedOptions(selectedOption),
			data = this.getData(),
			picklistColors = data['picklistColors'],
			fieldName = this.getName(),
			option

		for (option in pickListValues) {
			html += '<option value="' + option + '" ';

			if (picklistColors) {
				let className = '';

				if (picklistColors[option]) {
					className = 'picklistColor_' + fieldName + '_' + option.replace(' ', '_');
					html += 'class="' + className + '"';
				}
			}

			if (jQuery.inArray(option, selectedOptionsArray) !== -1) {
				html += ' selected ';
			}

			html += '>' + pickListValues[option] + '</option>';
		}

		html +='</select>';

		if (picklistColors) {
			html += '<style type="text/css">';

			for (option in picklistColors) {
				let picklistColor = picklistColors[option];

				if (picklistColor) {
					className = '.picklistColor_' + fieldName + '_' + option.replace(' ', '_');
					html += className + '{background-color: ' + picklistColor + ' !important;}';
				}
			}

			html += '<\style>';
		}

		let selectContainer = jQuery(html);

		this.addValidationToElement(selectContainer);

		return selectContainer;
	}
});

/** @var Vtiger_Boolean_Field_Js */
Vtiger_Field_Js('Vtiger_Boolean_Field_Js',{},{

	/**
	 * Function to check whether the field is checked or not
	 * @return <Boolean>
	 */
	isChecked : function() {
		var value = this.getValue();
		if(value==1 || value == '1' || (value && (value.toLowerCase() == 'on' || value.toLowerCase() == 'yes'))){
			return true;
		}
		return false;
	},

	/**
	 * Function to get the ui
	 * @return - checkbox element
	 */
	getUi : function() {
		let	html = '<div class="input-group-text"><input type="hidden" name="'+this.getName() +'"/><input class="BooleanField inputElement form-check-input m-0" type="checkbox" name="'+ this.getName() +'" ';

		if(this.isChecked()) {
			html += 'checked';
		}

		html += ' /></div>';

		return this.addValidationToElement(html);
	}
});

/** @var Vtiger_Date_Field_Js */
Vtiger_Field_Js('Vtiger_Date_Field_Js',{},{

	/**
	 * Function to get the user date format
	 */
	getDateFormat : function(){
		return this.get('date-format');
	},

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi: function () {
		//wrappig with another div for consistency
		let html = '<div class="DateField w-100">' +
				'<div class="input-group date">' +
				'<input class="inputElement dateField form-control" type="text" data-rule-date="true" data-format="' + this.getDateFormat() + '" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
				'<span class="input-group-addon input-group-text"><i class="fa fa-calendar"></i></span>' +
				'</div>' +
				'</div>',
			element = jQuery(html);

		return this.addValidationToElement(element);
	}
});

/** @var Vtiger_Currency_Field_Js */
Vtiger_Field_Js('Vtiger_Currency_Field_Js',{},{

	/**
	 * get the currency symbol configured for the user
	 */
	getCurrencySymbol : function() {
		return this.get('currency_symbol');
	},
	getValue: function() {
		let value = this._super();

		if(isNaN(value)) {
			value = '';
		}

		return value;
	},
	getUi: function () {
		let html = '<div class="CurrencyField w-100">' +
				'<div class="input-group">' +
				'<span class="input-group-addon input-group-text" id="basic-addon1">' + this.getCurrencySymbol() + '</span>' +
				'<input class="inputElement form-control currencyField replaceCommaWithDot" type="text" name="' + this.getName() + '" data-rule-currency="true" value="' + this.getValue() + '" />' +
				'</div>' +
				'</div>',
			element = jQuery(html);

		return this.addValidationToElement(element);
	}
});

/** @var Vtiger_Owner_Field_Js */
Vtiger_Field_Js('Vtiger_Owner_Field_Js',{},{

	/**
	 * Function to get the picklist values
	 */
	getPickListValues : function() {
            let pickListValues = this.get('editablepicklistvalues');

            if (pickListValues === ''){
                pickListValues = this.get('picklistvalues');
            }

            return pickListValues;
	},

	getUi : function() {
		var html = '<select class="OwnerField select2 inputElement" data-width="100%" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">';
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
			html += '</optgroup>';
		}

		html +='</select>';
		var selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);
		return selectContainer;
	}
});

/** @var Vtiger_Datetime_Field_Js */
Vtiger_Date_Field_Js('Vtiger_Datetime_Field_Js',{},{

});

/** @var Vtiger_Time_Field_Js */
Vtiger_Field_Js('Vtiger_Time_Field_Js',{},{

	/**
	 * Function to get the user date format
	 */
	getTimeFormat : function(){
		return this.get('time-format');
	},

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi: function () {
		let html = '<div class="TimeField"><div class="input-group time">' +
				'<input class="timepicker-default form-control inputElement" type="text" data-rule-time="true" data-format="' + this.getTimeFormat() + '" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
				'<span class="input-group-addon input-group-text"><i class="fa fa-clock-o"></i></span>' +
				'</div></div>',
			element = jQuery(html);

		return this.addValidationToElement(element);
	}
});

/** @var Vtiger_Text_Field_Js */
Vtiger_Field_Js('Vtiger_Text_Field_Js',{},{

	/**
	 * Function to get the ui
	 * @return - input text field
	 */
	getUi : function() {
		let html = '<textarea class="TextField form-control inputElement" name="'+ this.getName() +'" value="'+ this.getValue() + '" >'+ this.getValue() + '</textarea>',
			element = jQuery(html);

		return this.addValidationToElement(element);
	}
});

/** @var Vtiger_Percentage_Field_Js */
Vtiger_Field_Js('Vtiger_Percentage_Field_Js',{},{

	getValue: function() {
		let value = this._super();

		if(isNaN(value)) {
			value = '';
		}

		return value;
	},
	/**
	 * Function to get the ui
	 * @return - input percentage field
	 */
	getUi : function() {
		let html = '<div class="PercentageField w-100"><div class="input-group percentage-input-group flex-nowrap">'+
						'<input type="text" class="form-control inputElement percentage-input-element replaceCommaWithDot" name="'+this.getName() +'" value="'+ this.getValue() + '" step="any" data-rule-'+this.getType()+'=true/>'+
						'<span class="input-group-addon input-group-text">%</span>'+
					'</div></div>',
			element = jQuery(html);

		return this.addValidationToElement(element);
	}
});

/** @var Vtiger_Recurrence_Field_Js */
Vtiger_Field_Js('Vtiger_Recurrence_Field_Js',{},{

	/**
	 * Function to get the pick list values
	 * @return <object> key value pair of options
	 */
	getPickListValues : function() {
		return this.get('picklistvalues');
	},

	/**
	 * Function to get the ui
	 * @return - select element and chosen element
	 */
	getUi : function() {
		let html = '<select class="RecurrenceField select2 inputElement form-select" name="'+ this.getName() +'" id="field_'+this.getModuleName()+'_'+this.getName()+'">',
			pickListValues = this.getPickListValues(),
			selectedOption = app.htmlDecode(this.getValue()),
			option;

		for(option in pickListValues) {
			html += '<option value="'+option+'" ';

			if(option === selectedOption) {
				html += ' selected ';
			}

			html += '>'+pickListValues[option]+'</option>';
		}

		html +='</select>';
		let selectContainer = jQuery(html);
		this.addValidationToElement(selectContainer);

		return selectContainer;
	}
});

/** @var Vtiger_Email_Field_Js */
Vtiger_Field_Js('Vtiger_Email_Field_Js', {}, {

	/**
	 * Funtion to get the ui for the email field
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUi: function () {
		let html = '<input class="EmailField getPopupUi form-control inputElement" type="text" name="' + this.getName() + '" data-label="' + this.get('label') + '" data-rule-email="true" data-rule-illegal="true"/>';
		html = jQuery(html).val(app.htmlDecode(this.getValue()));

		this.addValidationToElement(html);

		return jQuery(html);
	}
});

/** @var Vtiger_Image_Field_Js */
Vtiger_Field_Js('Vtiger_Image_Field_Js',{},{

	/**
	 * Funtion to get the ui for the Image field
	 * return <String or Jquery> it can return either plain html or jquery object
	 */
	getUi : function() {
		let html = '';

		return jQuery(html);
	}
});

/** @var Vtiger_Integer_Field_Js */
Vtiger_Field_Js('Vtiger_Integer_Field_Js', {}, {
	getValue: function() {
		let value = this._super();

		if(isNaN(value)) {
			value = '';
		}

		return value;
	},
	getUi: function () {
		let html = '<input class="IntegerField form-control inputElement replaceCommaWithDot" type="text" name="' + this.getName() + '" data-label="' + this.get('label') + '" data-rule-' + this.getType() + '=true />';
		html = jQuery(html).val(app.htmlDecode(this.getValue()));

		return this.addValidationToElement(html);
	}
});

/** @var Vtiger_Double_Field_Js */
Vtiger_Field_Js('Vtiger_Double_Field_Js', {}, {
	getValue: function() {
		let value = this._super();

		if(isNaN(value)) {
			value = '';
		}

		return value;
	},
	getUi: function () {
		let html = '<input class="DoubleField form-control inputElement replaceCommaWithDot" type="text" name="' + this.getName() + '" data-label="' + this.get('label') + '" data-rule-' + this.getType() + '=true />';
		html = jQuery(html).val(app.htmlDecode(this.getValue()));

		return this.addValidationToElement(html);
	},
});

/** @var Vtiger_Double_Field_Js */
Vtiger_Picklist_Field_Js('Vtiger_Region_Field_Js', {}, {});

/** @var Vtiger_Double_Field_Js */
Vtiger_Field_Js('Vtiger_Country_Field_Js', {}, {
	getOptions: function () {
		let selectedValue = this.get('value'),
			values = this.get('picklistvalues'),
			options = '<option value="">' + app.vtranslate('JS_SELECT_OPTION') + '</option>',
			selected = '';

		$.each(values, function (key, value) {
			selected = key === selectedValue ? ' selected="selected" ' : '';

			options += '<option value="' + key + '" ' + selected + '>' + value + '</option>';
		})

		return options;
	},
	getUi: function () {
		let html = '<select class="CountryField form-select inputElement select2" data-width="100%" name="' + this.getName() + '" data-label="' + this.get('label') + '" data-rule-' + this.getType() + '=true /></select>';
		html = jQuery(html);
		html.append(this.getOptions());

		return this.addValidationToElement(html);
	},
});
