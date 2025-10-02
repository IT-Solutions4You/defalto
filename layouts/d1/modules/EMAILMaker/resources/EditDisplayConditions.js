/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
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
        let advfilterlist = this.advanceFilterInstance.getValues();
        jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
    },

    checkExpressionValidation: function (form) {
        let params = {
            'module': app.module(),
            'action': 'ValidateExpression',
            'mode': 'ForEMAILMakerDisplayEdit'
        };
        let serializeForm = form.serializeFormData();
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
        let self = this;
        let form = jQuery('#EditView');
        let params = {
            submitHandler: function (form) {
                if (jQuery('[name="display_trigger"]').val() == '6' && jQuery('#schtypeid').val() == '3') {
                    if (jQuery('#schdayofweek').val().length <= 0) {
                        app.helper.showErrorNotification({'message': 'Please Select atleast one value'});
                        return false;
                    }
                }
                form = jQuery(form);
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
        let thisInstance = this;
        if (typeof container == 'undefined') {
            container = thisInstance.getContainer();
        }
        let isPopupShowing = false;
        container.on('click', '.getPopupUi', function (e) {
            // Added to prevent multiple clicks event
            if (isPopupShowing) {
                return false;
            }
            let fieldValueElement = jQuery(e.currentTarget);
            let fieldValue = fieldValueElement.val();
            let fieldUiHolder = fieldValueElement.closest('.fieldUiHolder');
            let valueType = fieldUiHolder.find('[name="valuetype"]').val();
            if (valueType == '' || valueType == 'null') {
                valueType = 'rawtext';
            }
            let conditionsContainer = fieldValueElement.closest('.conditionsContainer');
            let conditionRow = fieldValueElement.closest('.conditionRow');

            let clonedPopupUi = conditionsContainer.find('.popupUi').clone(true, true).removeClass('hide').removeClass('popupUi').addClass('clonedPopupUi');
            clonedPopupUi.find('select').addClass('select2');
            clonedPopupUi.find('.fieldValue').val(fieldValue);
            clonedPopupUi.find('.fieldValue').removeClass('hide');

            if (fieldValueElement.hasClass('date')) {
                clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
                let dataFormat = fieldValueElement.data('date-format'),
                    value;

                if (valueType == 'rawtext') {
                    value = fieldValueElement.val();
                } else {
                    value = '';
                }
                let clonedDateElement = '<input type="text" style="width: 30%;" class="dateField fieldValue inputElement form-control" value="' + value + '" data-date-format="' + dataFormat + '" data-input="true" >'
                clonedPopupUi.find('.fieldValueContainer div').prepend(clonedDateElement);
            } else if (fieldValueElement.hasClass('time')) {
                clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
                let value

                if (valueType == 'rawtext') {
                    value = fieldValueElement.val();
                } else {
                    value = '';
                }
                let clonedTimeElement = '<input type="text" style="width: 30%;" class="timepicker-default fieldValue inputElement form-control" value="' + value + '" data-input="true" >'
                clonedPopupUi.find('.fieldValueContainer div').prepend(clonedTimeElement);
            } else if (fieldValueElement.hasClass('boolean')) {
                clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
                let value

                if (valueType == 'rawtext') {
                    value = fieldValueElement.val();
                } else {
                    value = '';
                }
                let clonedBooleanElement = '<input type="checkbox" style="width: 30%;" class="fieldValue inputElement" value="' + value + '" data-input="true" >';
                clonedPopupUi.find('.fieldValueContainer div').prepend(clonedBooleanElement);

                let fieldValue = clonedPopupUi.find('.fieldValueContainer input').val();
                if (value == 'true:boolean' || value == '') {
                    clonedPopupUi.find('.fieldValueContainer input').attr('checked', 'checked');
                } else {
                    clonedPopupUi.find('.fieldValueContainer input').removeAttr('checked');
                }
            }
            let callBackFunction = function (data) {
                isPopupShowing = false;
                data.find('.clonedPopupUi').removeClass('hide');
                let moduleNameElement = conditionRow.find('[name="modulename"]');
                if (moduleNameElement.length > 0) {
                    let moduleName = moduleNameElement.val();
                    data.find('.useFieldElement').addClass('hide');
                    jQuery(data.find('[name="' + moduleName + '"]').get(0)).removeClass('hide');
                }

                thisInstance.postShowModalAction(data, valueType);
                thisInstance.registerChangeFieldEvent(data);
                thisInstance.registerSelectOptionEvent(data);
                thisInstance.registerPopUpSaveEvent(data, fieldUiHolder);
                thisInstance.registerRemoveModalEvent(data);
                data.find('.fieldValue').filter(':visible').trigger('focus');
                vtUtils.registerNumberFormating(data);
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
            let valueType = jQuery('select.textType', data).val();

            fieldUiHolder.find('[name="valuetype"]').val(valueType);
            let fieldValueElement = fieldUiHolder.find('.getPopupUi');
            if (valueType != 'rawtext') {
                fieldValueElement.addClass('ignore-validation');
            } else {
                fieldValueElement.removeClass('ignore-validation');
            }
            let fieldType = data.find('.fieldValue').filter(':visible').attr('type');
            let fieldValue = data.find('.fieldValue').filter(':visible').val();
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
            let currentElement = jQuery(e.currentTarget);
            let newValue = currentElement.val();
            let oldValue = data.find('.fieldValue').filter(':visible').val();
            let textType = currentElement.closest('.clonedPopupUi').find('select.textType').val();
            if (currentElement.hasClass('useField')) {
                //If it is fieldname mode then we need to allow only one field
                if (oldValue != '' && textType != 'fieldname') {
                    let concatenatedValue = oldValue + ' ' + newValue;
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
            let valueType = jQuery(e.currentTarget).val();
            let useFieldContainer = jQuery('.useFieldContainer', data);
            let useFunctionContainer = jQuery('.useFunctionContainer', data);
            let uiType = jQuery(e.currentTarget).find('option:selected').data('ui');
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
        let uiType = jQuery('.textType', data).find('option:selected').data('ui');
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
        let form = jQuery('#saveTask');
        let params = form.serializeFormData();
        let result = true;
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
        let selectedFieldNames = jQuery('#save_fieldvaluemapping').find('.conditionRow').find('[name="fieldname"]');
        let result = true;
        let failureMessage = app.vtranslate('JS_SAME_FIELDS_SELECTED_MORE_THAN_ONCE');
        jQuery.each(selectedFieldNames, function (i, ele) {
            let fieldName = jQuery(ele).attr("value");
            let taskType = jQuery('#taskType').val();
            if (taskType == "VTUpdateFieldsTask") {
                let fields = jQuery('[data-emailmaker_columnname="' + fieldName + '"]').not(':hidden');
            } else {
                let fields = jQuery('[name="' + fieldName + '"]').not(':hidden');
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
        let selectedOption = fieldSelect.find('option:selected');
        //assumption that empty field will be having value none
        if (selectedOption.val() == 'none') {
            return true;
        }
        return false;
    },
    registerAddFieldEvent: function () {
        jQuery('#addFieldBtn').on('click', function (e) {
            let newAddFieldContainer = jQuery('.basicAddFieldContainer').clone(true, true).removeClass('basicAddFieldContainer hide').addClass('conditionRow');
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
        let thisInstance = this;
        jQuery('#saveTask').on('change', 'select[name="fieldname"]', function (e) {
            let selectedElement = jQuery(e.currentTarget);
            if (selectedElement.val() != 'none') {
                let conditionRow = selectedElement.closest('.conditionRow');
                let moduleNameElement = conditionRow.find('[name="modulename"]');
                if (moduleNameElement.length > 0) {
                    let selectedOptionFieldInfo = selectedElement.find('option:selected').data('fieldinfo');
                    let type = selectedOptionFieldInfo.type;
                    if (type == 'picklist' || type == 'multipicklist') {
                        let moduleName = jQuery('#createEntityModule').val();
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
        let fieldValueMap = this.fieldValueMap;
        if (fieldValueMap != false) {
            return fieldValueMap;
        } else {
            return '';
        }
    },
    fieldValueReMapping: function () {
        let object = JSON.parse(jQuery('#fieldValueMapping').val());
        let fieldValueReMap = {};

        jQuery.each(object, function (i, array) {
            fieldValueReMap[array.fieldname] = {};
            let values = {}
            jQuery.each(array, function (key, value) {
                values[key] = value;
            });
            fieldValueReMap[array.fieldname] = values
        });
        this.fieldValueMap = fieldValueReMap;
    },
    loadFieldSpecificUi: function (fieldSelect) {
        let selectedOption = fieldSelect.find('option:selected');
        let row = fieldSelect.closest('div.conditionRow');
        let fieldUiHolder = row.find('.fieldUiHolder');
        let fieldInfo = selectedOption.data('fieldinfo');
        let fieldValueMapping = this.getFieldValueMapping();
        let fieldValueMappingKey = fieldInfo.name;
        let taskType = jQuery('#taskType').val();
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

        let moduleName = this.getModuleName();

        let fieldModel = Vtiger_Field_Js.getInstance(fieldInfo, moduleName);
        this.fieldModelInstance = fieldModel;
        let fieldSpecificUi = this.getFieldSpecificUi(fieldSelect);

        //remove validation since we dont need validations for all eleements
        // Both filter and find is used since we dont know whether the element is enclosed in some conainer like currency
        let fieldName = fieldModel.getName();
        if (fieldModel.getType() == 'multipicklist') {
            fieldName = fieldName + "[]";
        }
        fieldSpecificUi.filter('[name="' + fieldName + '"]').attr('data-value', 'value').attr('data-emailmaker_columnname', fieldInfo.emailmaker_columnname);
        fieldSpecificUi.find('[name="' + fieldName + '"]').attr('data-value', 'value').attr('data-emailmaker_columnname', fieldInfo.emailmaker_columnname);
        fieldSpecificUi.filter('[name="valuetype"]').addClass('ignore-validation');
        fieldSpecificUi.find('[name="valuetype"]').addClass('ignore-validation');

        //If the display ValueType is rawtext then only validation should happen
        let displayValueType = fieldSpecificUi.filter('[name="valuetype"]').val();
        if (displayValueType != 'rawtext' && typeof displayValueType != 'undefined') {
            fieldSpecificUi.filter('[name="' + fieldName + '"]').addClass('ignore-validation');
            fieldSpecificUi.find('[name="' + fieldName + '"]').addClass('ignore-validation');
        }

        fieldUiHolder.html(fieldSpecificUi);

        if (fieldSpecificUi.is('input.select2')) {
            let tagElements = fieldSpecificUi.data('tags');
            let params = {tags: tagElements, tokenSeparators: [","]}
            vtUtils.showSelect2ElementView(fieldSpecificUi, params)
        } else if (fieldSpecificUi.is('select')) {
            if (fieldSpecificUi.hasClass('select2')) {
                vtUtils.showSelect2ElementView(fieldSpecificUi)
            } else {
                vtUtils.showSelect2ElementView(fieldSpecificUi);
            }
        } else if (fieldSpecificUi.is('input.dateField')) {
            let calendarType = fieldSpecificUi.data('calendarType');
            if (calendarType == 'range') {
                let customParams = {
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
        let fieldModel = this.fieldModelInstance;
        return jQuery(fieldModel.getUiTypeSpecificHtml())
    },

    updateAnnualDates: function (annualDatesEle) {
        annualDatesEle.html('');
        let annualDatesJSON = jQuery('#hiddenAnnualDates').val();
        if (annualDatesJSON) {
            let hiddenDates = '';
            let annualDates = JSON.parse(annualDatesJSON);
            for (j in annualDates) {
                hiddenDates += '<option selected value=' + annualDates[j] + '>' + annualDates[j] + '</option>';
            }
            annualDatesEle.html(hiddenDates);
        }
    },

    DateToYMD: function (date) {
        let year, month, day;
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
        let editViewContainer = this.getEditViewContainer();
        editViewContainer.on('change', '[name="conditionstype"]', function (e) {
            let advanceFilterContainer = jQuery('#advanceFilterContainer');
            let currentRadioButtonElement = jQuery(e.currentTarget);
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
        let container = jQuery("#advanceFilterContainer");
        this.setContainer(container);
        this.registerFormSubmitEvent();
        this.registerEnableFilterOption();
        vtUtils.applyFieldElementsView(jQuery('#display_condition'));
        this.advanceFilterInstance = EMAILMaker_AdvanceFilter_Js.getInstance(jQuery('.filterContainer', container));
        this.getPopUp();
        vtUtils.registerNumberFormating(container)
    }
});

//http://stackoverflow.com/questions/946534/insert-text-into-textarea-with-jquery
jQuery.fn.extend({
    insertAtCaret: function (myValue) {
        return this.each(function (i) {
            if (document.selection) {
                //For browsers like Internet Explorer
                this.focus();
                let sel = document.selection.createRange();
                sel.text = myValue;
                this.focus();
            } else if (this.selectionStart || this.selectionStart == '0') {
                //For browsers like Firefox and Webkit based
                let startPos = this.selectionStart;
                let endPos = this.selectionEnd;
                let scrollTop = this.scrollTop;
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