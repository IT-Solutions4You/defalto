/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
EMAILMaker_Edit_Js("EMAILMaker_EditDisplayConditions_Js", {}, {
    displayContainer: false,
    advanceFilterInstance: false,
    fieldValueMap: false,
    displayActionContainer: false,

    /**
     * Function to get the container which holds all the emailmaker elements
     * @return jQuery object
     */
    getContainer: function () {
        return this.displayContainer;
    },

    /**
     * Function to get the container which holds all the emailmaker elements
     * @return jQuery object
     */
    getActionContainer: function () {
        return this.displayActionContainer;
    },

    /**
     * Function to set the reports container
     * @params : element - which represents the emailmaker container
     * @return : current instance
     */
    setContainer: function (element) {
        this.displayContainer = element;
        return this;
    },

    /**
     * Function to set the reports step1 container
     * @params : element - which represents the reports step1 container
     * @return : current instance
     */
    setActionContainer: function (element) {
        this.displayActionContainer = element;
        return this;
    },

    calculateValues: function () {
        var advfilterlist = this.advanceFilterInstance.getValues();
        jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
    },

    checkExpressionValidation: function (form) {
        var params = {
            'module': app.module(),
            'action': 'ValidateExpression',
            'mode': 'ForEMAILMakerDisplayEdit'
        };
        var serializeForm = form.serializeFormData();
        params = jQuery.extend(serializeForm, params);
        app.request.post({'data': params}).then(function (error, data) {
            if (error == null) {
                form.get(0).submit();
            } else {
                jQuery(form).find('button.saveButton').removeAttr('disabled');
                app.helper.showErrorNotification({'message': app.vtranslate('LBL_EXPRESSION_INVALID')});
            }
        });
    },

    /*
     * Function to register the click event for next button
     */
    registerFormSubmitEvent: function () {
        var self = this;
        var form = jQuery('#EditView');
        var params = {
            submitHandler: function (form) {
                if (jQuery('[name="display_trigger"]').val() == '6' && jQuery('#schtypeid').val() == '3') {
                    if (jQuery('#schdayofweek').val().length <= 0) {
                        app.helper.showErrorNotification({'message': 'Please Select atleast one value'});
                        return false;
                    }
                }
                var form = jQuery(form);
                self.calculateValues();
                window.onbeforeunload = null;
                jQuery(form).find('button.saveButton').attr('disabled', 'disabled');

                self.checkExpressionValidation(form);
                return false;
            }
        };
        form.vtValidate(params);
    },


    getPopUp: function (container) {
        var thisInstance = this;
        if (typeof container == 'undefined') {
            container = thisInstance.getContainer();
        }
        var isPopupShowing = false;
        container.on('click', '.getPopupUi', function (e) {
            // Added to prevent multiple clicks event
            if (isPopupShowing) {
                return false;
            }
            var fieldValueElement = jQuery(e.currentTarget);
            var fieldValue = fieldValueElement.val();
            var fieldUiHolder = fieldValueElement.closest('.fieldUiHolder');
            var valueType = fieldUiHolder.find('[name="valuetype"]').val();
            if (valueType == '' || valueType == 'null') {
                valueType = 'rawtext';
            }
            var conditionsContainer = fieldValueElement.closest('.conditionsContainer');
            var conditionRow = fieldValueElement.closest('.conditionRow');

            var clonedPopupUi = conditionsContainer.find('.popupUi').clone(true, true).removeClass('hide').removeClass('popupUi').addClass('clonedPopupUi');
            clonedPopupUi.find('select').addClass('select2');
            clonedPopupUi.find('.fieldValue').val(fieldValue);
            clonedPopupUi.find('.fieldValue').removeClass('hide');

            if (fieldValueElement.hasClass('date')) {
                clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
                var dataFormat = fieldValueElement.data('date-format');
                if (valueType == 'rawtext') {
                    var value = fieldValueElement.val();
                } else {
                    value = '';
                }
                var clonedDateElement = '<input type="text" style="width: 30%;" class="dateField fieldValue inputElement" value="' + value + '" data-date-format="' + dataFormat + '" data-input="true" >'
                clonedPopupUi.find('.fieldValueContainer div').prepend(clonedDateElement);
            } else if (fieldValueElement.hasClass('time')) {
                clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
                if (valueType == 'rawtext') {
                    var value = fieldValueElement.val();
                } else {
                    value = '';
                }
                var clonedTimeElement = '<input type="text" style="width: 30%;" class="timepicker-default fieldValue inputElement" value="' + value + '" data-input="true" >'
                clonedPopupUi.find('.fieldValueContainer div').prepend(clonedTimeElement);
            } else if (fieldValueElement.hasClass('boolean')) {
                clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
                if (valueType == 'rawtext') {
                    var value = fieldValueElement.val();
                } else {
                    value = '';
                }
                var clonedBooleanElement = '<input type="checkbox" style="width: 30%;" class="fieldValue inputElement" value="' + value + '" data-input="true" >';
                clonedPopupUi.find('.fieldValueContainer div').prepend(clonedBooleanElement);

                var fieldValue = clonedPopupUi.find('.fieldValueContainer input').val();
                if (value == 'true:boolean' || value == '') {
                    clonedPopupUi.find('.fieldValueContainer input').attr('checked', 'checked');
                } else {
                    clonedPopupUi.find('.fieldValueContainer input').removeAttr('checked');
                }
            }
            var callBackFunction = function (data) {
                isPopupShowing = false;
                data.find('.clonedPopupUi').removeClass('hide');
                var moduleNameElement = conditionRow.find('[name="modulename"]');
                if (moduleNameElement.length > 0) {
                    var moduleName = moduleNameElement.val();
                    data.find('.useFieldElement').addClass('hide');
                    jQuery(data.find('[name="' + moduleName + '"]').get(0)).removeClass('hide');
                }

                thisInstance.postShowModalAction(data, valueType);
                thisInstance.registerChangeFieldEvent(data);
                thisInstance.registerSelectOptionEvent(data);
                thisInstance.registerPopUpSaveEvent(data, fieldUiHolder);
                thisInstance.registerRemoveModalEvent(data);
                data.find('.fieldValue').filter(':visible').trigger('focus');
            }
            conditionsContainer.find('.clonedPopUp').html(clonedPopupUi);
            jQuery('.clonedPopupUi').on('shown', function () {
                if (typeof callBackFunction == 'function') {
                    callBackFunction(jQuery('.clonedPopupUi', conditionsContainer));
                }
            });
            isPopupShowing = true;
            app.helper.showModal(jQuery('.clonedPopUp', conditionsContainer).find('.clonedPopupUi'), {cb: callBackFunction});
        });
    },

    registerRemoveModalEvent: function (data) {
        data.on('click', '.closeModal', function (e) {
            data.modal('hide');
        });
    },

    registerPopUpSaveEvent: function (data, fieldUiHolder) {
        jQuery('[name="saveButton"]', data).on('click', function (e) {
            var valueType = jQuery('select.textType', data).val();

            fieldUiHolder.find('[name="valuetype"]').val(valueType);
            var fieldValueElement = fieldUiHolder.find('.getPopupUi');
            if (valueType != 'rawtext') {
                fieldValueElement.addClass('ignore-validation');
            } else {
                fieldValueElement.removeClass('ignore-validation');
            }
            var fieldType = data.find('.fieldValue').filter(':visible').attr('type');
            var fieldValue = data.find('.fieldValue').filter(':visible').val();
            //For checkbox field type, handling fieldValue
            if (fieldType == 'checkbox') {
                if (data.find('.fieldValue').filter(':visible').is(':checked')) {
                    fieldValue = 'true:boolean';
                } else {
                    fieldValue = 'false:boolean';
                }
            }
            fieldValueElement.val(fieldValue);
            data.modal('hide');
        });
    },

    registerSelectOptionEvent: function (data) {
        jQuery('.useField,.useFunction', data).on('change', function (e) {
            var currentElement = jQuery(e.currentTarget);
            var newValue = currentElement.val();
            var oldValue = data.find('.fieldValue').filter(':visible').val();
            var textType = currentElement.closest('.clonedPopupUi').find('select.textType').val();
            if (currentElement.hasClass('useField')) {
                //If it is fieldname mode then we need to allow only one field
                if (oldValue != '' && textType != 'fieldname') {
                    var concatenatedValue = oldValue + ' ' + newValue;
                } else {
                    concatenatedValue = newValue;
                }
            } else {
                concatenatedValue = oldValue + newValue;
            }
            data.find('.fieldValue').val(concatenatedValue);
            currentElement.val('').select2("val", '');
        });
    },
    registerChangeFieldEvent: function (data) {
        jQuery('.textType', data).on('change', function (e) {
            var valueType = jQuery(e.currentTarget).val();
            var useFieldContainer = jQuery('.useFieldContainer', data);
            var useFunctionContainer = jQuery('.useFunctionContainer', data);
            var uiType = jQuery(e.currentTarget).find('option:selected').data('ui');
            jQuery('.fieldValue', data).hide();
            jQuery('[data-' + uiType + ']', data).show();
            if (valueType == 'fieldname') {
                useFieldContainer.removeClass('hide');
                useFunctionContainer.addClass('hide');
            } else if (valueType == 'expression') {
                useFieldContainer.removeClass('hide');
                useFunctionContainer.removeClass('hide');
            } else {
                useFieldContainer.addClass('hide');
                useFunctionContainer.addClass('hide');
            }
            jQuery('.helpmessagebox', data).addClass('hide');
            jQuery('#' + valueType + '_help', data).removeClass('hide');
            data.find('.fieldValue').val('');
        });
    },
    postShowModalAction: function (data, valueType) {
        if (valueType == 'fieldname') {
            jQuery('.useFieldContainer', data).removeClass('hide');
            jQuery('.textType', data).val(valueType).trigger('change');
        } else if (valueType == 'expression') {
            jQuery('.useFieldContainer', data).removeClass('hide');
            jQuery('.useFunctionContainer', data).removeClass('hide');
            jQuery('.textType', data).val(valueType).trigger('change');
        }
        jQuery('#' + valueType + '_help', data).removeClass('hide');
        var uiType = jQuery('.textType', data).find('option:selected').data('ui');
        jQuery('.fieldValue', data).hide();
        jQuery('[data-' + uiType + ']', data).show();
    },

    registerCheckSelectDateEvent: function () {
        jQuery('[name="check_select_date"]').on('change', function (e) {
            if (jQuery(e.currentTarget).is(':checked')) {
                jQuery('#checkSelectDateContainer').removeClass('hide').addClass('show');
            } else {
                jQuery('#checkSelectDateContainer').removeClass('show').addClass('hide');
            }
        });
    },
    VTUpdateFieldsTaskCustomValidation: function () {
        return this.checkDuplicateFieldsSelected();
    },
    VTCreateEntityTaskCustomValidation: function () {
        return this.checkDuplicateFieldsSelected();
    },
    VTCreateEventTaskCustomValidation: function () {
        return this.checkStartAndEndDate();
    },
    checkStartAndEndDate: function () {
        var form = jQuery('#saveTask');
        var params = form.serializeFormData();
        var result = true;
        if (params['taskType'] == 'VTCreateEventTask' && params['startDatefield'] == params['endDatefield']) {
            if (params['startDirection'] == params['endDirection']) {
                if (params['startDays'] > params['endDays'] && params['endDirection'] == 'after') {
                    result = app.vtranslate('JS_CHECK_START_AND_END_DATE');
                    return result;
                } else if (params['startDays'] < params['endDays'] && params['endDirection'] == 'before') {
                    result = app.vtranslate('JS_CHECK_START_AND_END_DATE');
                    return result;
                } else if (params['startDays'] == params['endDays'] && params['startDirection'] == params['endDirection'] && params['endTime'] < params['startTime']) {
                    result = app.vtranslate('JS_CHECK_START_AND_END_DATE');
                    return result;
                }
            }
        }
        return result;
    },
    checkDuplicateFieldsSelected: function () {
        var selectedFieldNames = jQuery('#save_fieldvaluemapping').find('.conditionRow').find('[name="fieldname"]');
        var result = true;
        var failureMessage = app.vtranslate('JS_SAME_FIELDS_SELECTED_MORE_THAN_ONCE');
        jQuery.each(selectedFieldNames, function (i, ele) {
            var fieldName = jQuery(ele).attr("value");
            var taskType = jQuery('#taskType').val();
            if (taskType == "VTUpdateFieldsTask") {
                var fields = jQuery('[data-emailmaker_columnname="' + fieldName + '"]').not(':hidden');
            } else {
                var fields = jQuery('[name="' + fieldName + '"]').not(':hidden');
            }
            if (fields.length > 1) {
                result = failureMessage;
                return false;
            }
        });
        return result;
    },
    /**
     * Function to check if the field selected is empty field
     * @params : select element which represents the field
     * @return : boolean true/false
     */
    isEmptyFieldSelected: function (fieldSelect) {
        var selectedOption = fieldSelect.find('option:selected');
        //assumption that empty field will be having value none
        if (selectedOption.val() == 'none') {
            return true;
        }
        return false;
    },
    getValues: function (tasktype) {
        var thisInstance = this;
        var conditionsContainer = jQuery('#save_fieldvaluemapping');
        var fieldListFunctionName = 'get' + tasktype + 'FieldList';
        if (typeof thisInstance[fieldListFunctionName] != 'undefined') {
            var fieldList = thisInstance[fieldListFunctionName].apply()
        }

        var values = [];
        var conditions = jQuery('.conditionRow', conditionsContainer);
        conditions.each(function (i, conditionDomElement) {
            var rowElement = jQuery(conditionDomElement);
            var fieldSelectElement = jQuery('[name="fieldname"]', rowElement);
            var valueSelectElement = jQuery('[data-value="value"]', rowElement);
            //To not send empty fields to server
            if (thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
                return true;
            }
            var fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo');
            var fieldType = fieldDataInfo.type;
            var rowValues = {};
            if (fieldType == 'owner') {
                for (var key in fieldList) {
                    var field = fieldList[key];
                    if (field == 'value' && valueSelectElement.is('select')) {
                        rowValues[field] = valueSelectElement.find('option:selected').val();
                    } else {
                        rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
                    }
                }
            } else if (fieldType == 'picklist' || fieldType == 'multipicklist') {
                for (var key in fieldList) {
                    var field = fieldList[key];
                    if (field == 'value' && valueSelectElement.is('input')) {
                        var commaSeperatedValues = valueSelectElement.val();
                        var pickListValues = valueSelectElement.data('picklistvalues');
                        var valuesArr = commaSeperatedValues.split(',');
                        var newvaluesArr = [];
                        for (i = 0; i < valuesArr.length; i++) {
                            if (typeof pickListValues[valuesArr[i]] != 'undefined') {
                                newvaluesArr.push(pickListValues[valuesArr[i]]);
                            } else {
                                newvaluesArr.push(valuesArr[i]);
                            }
                        }
                        var reconstructedCommaSeperatedValues = newvaluesArr.join(',');
                        rowValues[field] = reconstructedCommaSeperatedValues;
                    } else if (field == 'value' && valueSelectElement.is('select') && fieldType == 'picklist') {
                        rowValues[field] = valueSelectElement.val();
                    } else if (field == 'value' && valueSelectElement.is('select') && fieldType == 'multipicklist') {
                        var value = valueSelectElement.val();
                        if (value == null) {
                            rowValues[field] = value;
                        } else {
                            rowValues[field] = value.join(',');
                        }
                    } else {
                        rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
                    }
                }

            } else if (fieldType == 'text') {
                for (var key in fieldList) {
                    var field = fieldList[key];
                    if (field == 'value') {
                        rowValues[field] = rowElement.find('textarea').val();
                    } else {
                        rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
                    }
                }
            } else {
                for (var key in fieldList) {
                    var field = fieldList[key];
                    if (field == 'value') {
                        rowValues[field] = valueSelectElement.val();
                    } else {
                        rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
                    }
                }
            }
            if (jQuery('[name="valuetype"]', rowElement).val() == 'false' || (jQuery('[name="valuetype"]', rowElement).length == 0)) {
                rowValues['valuetype'] = 'rawtext';
            }

            values.push(rowValues);
        });
        return values;
    },
    registerAddFieldEvent: function () {
        jQuery('#addFieldBtn').on('click', function (e) {
            var newAddFieldContainer = jQuery('.basicAddFieldContainer').clone(true, true).removeClass('basicAddFieldContainer hide').addClass('conditionRow');
            jQuery('select', newAddFieldContainer).addClass('select2');
            jQuery('#save_fieldvaluemapping').append(newAddFieldContainer);
            vtUtils.showSelect2ElementView(newAddFieldContainer.find('.select2'));
        });
    },
    registerDeleteConditionEvent: function () {
        jQuery('#saveTask').on('click', '.deleteCondition', function (e) {
            jQuery(e.currentTarget).closest('.conditionRow').remove();
        })
    },
    /**
     * Function which will register field change event
     */
    registerFieldChange: function () {
        var thisInstance = this;
        jQuery('#saveTask').on('change', 'select[name="fieldname"]', function (e) {
            var selectedElement = jQuery(e.currentTarget);
            if (selectedElement.val() != 'none') {
                var conditionRow = selectedElement.closest('.conditionRow');
                var moduleNameElement = conditionRow.find('[name="modulename"]');
                if (moduleNameElement.length > 0) {
                    var selectedOptionFieldInfo = selectedElement.find('option:selected').data('fieldinfo');
                    var type = selectedOptionFieldInfo.type;
                    if (type == 'picklist' || type == 'multipicklist') {
                        var moduleName = jQuery('#createEntityModule').val();
                        moduleNameElement.find('option[value="' + moduleName + '"]').attr('selected', true);
                        moduleNameElement.trigger('change');
                        moduleNameElement.select2("disable");
                    }
                }
                thisInstance.loadFieldSpecificUi(selectedElement);
            }
        });
    },
    getModuleName: function () {
        return app.getModuleName();
    },
    getFieldValueMapping: function () {
        var fieldValueMap = this.fieldValueMap;
        if (fieldValueMap != false) {
            return fieldValueMap;
        } else {
            return '';
        }
    },
    fieldValueReMapping: function () {
        var object = JSON.parse(jQuery('#fieldValueMapping').val());
        var fieldValueReMap = {};

        jQuery.each(object, function (i, array) {
            fieldValueReMap[array.fieldname] = {};
            var values = {}
            jQuery.each(array, function (key, value) {
                values[key] = value;
            });
            fieldValueReMap[array.fieldname] = values
        });
        this.fieldValueMap = fieldValueReMap;
    },
    loadFieldSpecificUi: function (fieldSelect) {
        var selectedOption = fieldSelect.find('option:selected');
        var row = fieldSelect.closest('div.conditionRow');
        var fieldUiHolder = row.find('.fieldUiHolder');
        var fieldInfo = selectedOption.data('fieldinfo');
        var fieldValueMapping = this.getFieldValueMapping();
        var fieldValueMappingKey = fieldInfo.name;
        var taskType = jQuery('#taskType').val();
        if (taskType == "VTUpdateFieldsTask") {
            fieldValueMappingKey = fieldInfo.emailmaker_columnname;
        }
        if (fieldValueMapping != '' && typeof fieldValueMapping[fieldValueMappingKey] != 'undefined') {
            fieldInfo.value = fieldValueMapping[fieldValueMappingKey]['value'];
            fieldInfo.emailmaker_valuetype = fieldValueMapping[fieldValueMappingKey]['valuetype'];
        } else {
            fieldInfo.emailmaker_valuetype = 'rawtext';
        }

        if (fieldInfo.type == 'reference') {
            fieldInfo.referenceLabel = fieldUiHolder.find('[name="referenceValueLabel"]').val();
            fieldInfo.type = 'string';
        }

        var moduleName = this.getModuleName();

        var fieldModel = Vtiger_Field_Js.getInstance(fieldInfo, moduleName);
        this.fieldModelInstance = fieldModel;
        var fieldSpecificUi = this.getFieldSpecificUi(fieldSelect);

        //remove validation since we dont need validations for all eleements
        // Both filter and find is used since we dont know whether the element is enclosed in some conainer like currency
        var fieldName = fieldModel.getName();
        if (fieldModel.getType() == 'multipicklist') {
            fieldName = fieldName + "[]";
        }
        fieldSpecificUi.filter('[name="' + fieldName + '"]').attr('data-value', 'value').attr('data-emailmaker_columnname', fieldInfo.emailmaker_columnname);
        fieldSpecificUi.find('[name="' + fieldName + '"]').attr('data-value', 'value').attr('data-emailmaker_columnname', fieldInfo.emailmaker_columnname);
        fieldSpecificUi.filter('[name="valuetype"]').addClass('ignore-validation');
        fieldSpecificUi.find('[name="valuetype"]').addClass('ignore-validation');

        //If the display ValueType is rawtext then only validation should happen
        var displayValueType = fieldSpecificUi.filter('[name="valuetype"]').val();
        if (displayValueType != 'rawtext' && typeof displayValueType != 'undefined') {
            fieldSpecificUi.filter('[name="' + fieldName + '"]').addClass('ignore-validation');
            fieldSpecificUi.find('[name="' + fieldName + '"]').addClass('ignore-validation');
        }

        fieldUiHolder.html(fieldSpecificUi);

        if (fieldSpecificUi.is('input.select2')) {
            var tagElements = fieldSpecificUi.data('tags');
            var params = {tags: tagElements, tokenSeparators: [","]}
            vtUtils.showSelect2ElementView(fieldSpecificUi, params)
        } else if (fieldSpecificUi.is('select')) {
            if (fieldSpecificUi.hasClass('select2')) {
                vtUtils.showSelect2ElementView(fieldSpecificUi)
            } else {
                vtUtils.showSelect2ElementView(fieldSpecificUi);
            }
        } else if (fieldSpecificUi.is('input.dateField')) {
            var calendarType = fieldSpecificUi.data('calendarType');
            if (calendarType == 'range') {
                var customParams = {
                    calendars: 3,
                    mode: 'range',
                    className: 'rangeCalendar',
                    onChange: function (formated) {
                        fieldSpecificUi.val(formated.join(','));
                    }
                }
                app.registerEventForDatePickerFields(fieldSpecificUi, false, customParams);
            } else {
                app.registerEventForDatePickerFields(fieldSpecificUi);
            }
        }
        return this;
    },
    /**
     * Functiont to get the field specific ui for the selected field
     * @prarms : fieldSelectElement - select element which will represents field list
     * @return : jquery object which represents the ui for the field
     */
    getFieldSpecificUi: function (fieldSelectElement) {
        var fieldModel = this.fieldModelInstance;
        return jQuery(fieldModel.getUiTypeSpecificHtml())
    },

    updateAnnualDates: function (annualDatesEle) {
        annualDatesEle.html('');
        var annualDatesJSON = jQuery('#hiddenAnnualDates').val();
        if (annualDatesJSON) {
            var hiddenDates = '';
            var annualDates = JSON.parse(annualDatesJSON);
            for (j in annualDates) {
                hiddenDates += '<option selected value=' + annualDates[j] + '>' + annualDates[j] + '</option>';
            }
            annualDatesEle.html(hiddenDates);
        }
    },

    DateToYMD: function (date) {
        var year, month, day;
        year = String(date.getFullYear());
        month = String(date.getMonth() + 1);
        if (month.length == 1) {
            month = "0" + month;
        }
        day = String(date.getDate());
        if (day.length == 1) {
            day = "0" + day;
        }
        return year + "-" + month + "-" + day;
    },

    registerEnableFilterOption: function () {
        var editViewContainer = this.getEditViewContainer();
        editViewContainer.on('change', '[name="conditionstype"]', function (e) {
            var advanceFilterContainer = jQuery('#advanceFilterContainer');
            var currentRadioButtonElement = jQuery(e.currentTarget);
            if (currentRadioButtonElement.hasClass('recreate')) {
                if (currentRadioButtonElement.is(':checked')) {
                    advanceFilterContainer.removeClass('zeroOpacity');
                    advanceFilterContainer.find('.conditionList').find('[name="columnname"]').find('optgroup:first option:first').attr('selected', 'selected').trigger('change');
                }
            } else {
                advanceFilterContainer.addClass('zeroOpacity');
            }
        });
    },

    addComponents: function () {
        this._super();
        this.addModuleSpecificComponent('Index', 'Vtiger', app.getParentModuleName());
    },

    registerEvents: function () {
        var container = jQuery("#advanceFilterContainer");
        this.setContainer(container);
        this.registerFormSubmitEvent();
        this.registerEnableFilterOption();
        vtUtils.applyFieldElementsView(jQuery('#display_condition'));
        this.advanceFilterInstance = EMAILMaker_AdvanceFilter_Js.getInstance(jQuery('.filterContainer', container));
        this.getPopUp();
    }
});

//http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery
jQuery.fn.extend({
    insertAtCaret: function (myValue) {
        return this.each(function (i) {
            if (document.selection) {
                //For browsers like Internet Explorer
                this.focus();
                var sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            } else if (this.selectionStart || this.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                var startPos = this.selectionStart;
                var endPos = this.selectionEnd;
                var scrollTop = this.scrollTop;
                this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                this.focus();
                this.selectionStart = startPos + myValue.length;
                this.selectionEnd = startPos + myValue.length;
                this.scrollTop = scrollTop;
            } else {
                this.value += myValue;
                this.focus();
            }
        });
    }
});