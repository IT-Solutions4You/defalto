/*********************************************************************************
 * The content of this file is subject to the EMAIL Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/

Vtiger_AdvanceFilter_Js('EMAILMaker_AdvanceFilter_Js', {}, {
    validationSupportedFieldConditionMap: {
        'email': ['e', 'n'],
        'date': ['is'],
        'datetime': ['is']
    },
    allConditionValidationNeededFieldList: ['double', 'integer'],
    comparatorsWithNoValueBoxMap: ['is empty', 'is not empty'],
    getFieldSpecificType: function (fieldSelected) {
        var fieldInfo = fieldSelected.data('fieldinfo');
        return fieldInfo.type;
    },
    getModuleName: function () {
        return app.getModuleName();
    },
    addNewCondition: function (conditionGroupElement) {
        var basicElement = jQuery('.basic', conditionGroupElement);
        var newRowElement = basicElement.find('.conditionRow').clone(true, true);
        jQuery('select', newRowElement).addClass('select2');
        var conditionList = jQuery('.conditionList', conditionGroupElement);
        conditionList.append(newRowElement);
        //change in to chosen elements
        vtUtils.showSelect2ElementView(newRowElement.find('select.select2'));
        newRowElement.find('[name="columnname"]').find('optgroup:first option:first').attr('selected', 'selected').trigger('change');
        return this;
    },
    loadConditions: function (fieldSelect) {
        var row = fieldSelect.closest('div.conditionRow');
        var conditionSelectElement = row.find('select[name="comparator"]');
        var conditionSelected = conditionSelectElement.val();
        var fieldSelected = fieldSelect.find('option:selected');
        var fieldLabel = fieldSelected.val();
        var match = fieldLabel.match(/\((\w+)\) (\w+)/);
        var fieldSpecificType = this.getFieldSpecificType(fieldSelected)
        var conditionList = this.getConditionListFromType(fieldSpecificType);

        if (typeof conditionList == 'undefined') {
            conditionList = {};
            conditionList['none'] = '';
        }

        var options = '';
        for (var key in conditionList) {
            if (conditionList.hasOwnProperty(key)) {
                var conditionValue = conditionList[key];
                var conditionLabel = this.getConditionLabel(conditionValue);
                if (match != null) {
                    if (conditionValue != 'has changed') {
                        options += '<option value="' + conditionValue + '"';
                        if (conditionValue == conditionSelected) {
                            options += ' selected="selected" ';
                        }
                        options += '>' + conditionLabel + '</option>';
                    }
                } else {
                    options += '<option value="' + conditionValue + '"';
                    if (conditionValue == conditionSelected) {
                        options += ' selected="selected" ';
                    }
                    options += '>' + conditionLabel + '</option>';
                }
            }
        }
        conditionSelectElement.empty().html(options).trigger('change');
        conditionSelectElement.addClass('validate[required]');
        return conditionSelectElement;
    },
    getMetricFieldSpecificConditionList: function (conditionList, conditionSelected, match, fieldSelected) {
        var options = '';
        var fieldDataInfo = fieldSelected.data('fieldinfo');
        var fieldModel = Vtiger_Field_Js.getInstance(fieldDataInfo, this.getModuleName());
        var picklistValues = fieldModel.data.picklistvalues;
        for (var key in conditionList) {
            if (conditionList.hasOwnProperty(key)) {
                var conditionValue = conditionList[key];
                var conditionLabel = this.getConditionLabel(conditionValue);
                if (match != null) {
                    if (conditionValue != 'has changed') {
                        if (conditionValue.indexOf("hours since") != -1) {
                            for (var key in picklistValues) {
                                var picklistvalue = conditionValue.replace(/%s/i, picklistValues[key]);
                                options += '<option value="' + picklistvalue + '"';
                                if (picklistvalue == conditionSelected) {
                                    options += ' selected="selected" ';
                                }
                                options += '>' + conditionValue.replace(/%s/i, key) + '</option>';
                            }
                        } else {
                            options += '<option value="' + conditionValue + '"';
                            if (conditionValue == conditionSelected) {
                                options += ' selected="selected" ';
                            }
                            options += '>' + conditionLabel + '</option>';
                        }
                    }
                } else {
                    if (conditionValue.indexOf("hours since %s") != -1) {
                        for (var key in picklistValues) {
                            var picklistvalue = conditionValue.replace(/%s/i, picklistValues[key]);
                            options += '<option value="' + picklistvalue + '"';
                            if (picklistvalue == conditionSelected) {
                                options += ' selected="selected" ';
                            }
                            options += '>' + conditionValue.replace(/%s/i, key) + '</option>';
                        }
                    } else {
                        options += '<option value="' + conditionValue + '"';
                        if (conditionValue == conditionSelected) {
                            options += ' selected="selected" ';
                        }
                        options += '>' + conditionLabel + '</option>';
                    }
                }
            }
        }
        return options;
    },
    getValues: function () {
        var thisInstance = this;
        var filterContainer = this.getFilterContainer();

        var fieldList = ['columnname', 'comparator', 'value', 'valuetype', 'column_condition'];

        var values = {};
        var columnIndex = 0;
        var conditionGroups = jQuery('.conditionGroup', filterContainer);
        conditionGroups.each(function (index, domElement) {
            var groupElement = jQuery(domElement);

            var conditions = jQuery('.conditionList .conditionRow', groupElement);
            if (conditions.length <= 0) {
                return true;
            }
            var iterationValues = {};
            conditions.each(function (i, conditionDomElement) {
                var rowElement = jQuery(conditionDomElement);
                var fieldSelectElement = jQuery('[name="columnname"]', rowElement);
                var valueSelectElement = jQuery('[data-value="value"]', rowElement);
                if (thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
                    return true;
                }
                var fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo');
                var fieldType = fieldDataInfo.type;
                var rowValues = {};
                if (fieldType == 'picklist' || fieldType == 'multipicklist') {
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
                } else {
                    for (var key in fieldList) {
                        var field = fieldList[key];
                        if (field == 'value') {
                            if ((fieldType == 'date' || fieldType == 'datetime') && valueSelectElement.length > 0) {
                                var value = valueSelectElement.val();
                                var dateFormat = app.getDateFormat();
                                var dateFormatParts = dateFormat.split("-");
                                var valueArray = value.split(',');
                                for (i = 0; i < valueArray.length; i++) {
                                    var valueParts = valueArray[i].split("-");
                                    var dateInstance = new Date(valueParts[dateFormatParts.indexOf('yyyy')], parseInt(valueParts[dateFormatParts.indexOf('mm')]) - 1, valueParts[dateFormatParts.indexOf('dd')]);
                                    if (!isNaN(dateInstance.getTime())) {
                                        valueArray[i] = app.getDateInVtigerFormat('yyyy-mm-dd', dateInstance);
                                    }
                                }
                                rowValues[field] = valueArray.join(',');
                            } else {
                                rowValues[field] = valueSelectElement.val();
                            }
                        } else {
                            rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
                        }
                    }
                }
                if (jQuery('[name="valuetype"]', rowElement).val() == 'false' || (jQuery('[name="valuetype"]', rowElement).length == 0)) {
                    rowValues['valuetype'] = 'rawtext';
                }
                if (index == '0') {
                    rowValues['groupid'] = '0';
                } else {
                    rowValues['groupid'] = '1';
                }
                if (rowElement.is(":last-child")) {
                    rowValues['column_condition'] = '';
                }
                iterationValues[columnIndex] = rowValues;
                columnIndex++;
            });

            if (!jQuery.isEmptyObject(iterationValues)) {
                values[index + 1] = {};
                values[index + 1]['columns'] = iterationValues;
            }
            if (groupElement.find('div.groupCondition').length > 0 && !jQuery.isEmptyObject(values[index + 1])) {
                values[index + 1]['condition'] = conditionGroups.find('div.groupCondition [name="condition"]').val();
            }
        });
        return values;

    },
    getFieldSpecificUi: function (fieldSelectElement) {
        var fieldSelected = fieldSelectElement.find('option:selected');
        var fieldInfo = fieldSelected.data('fieldinfo');
        if (jQuery.inArray(fieldInfo.comparatorElementVal, this.comparatorsWithNoValueBoxMap) != -1) {
            return jQuery('');
        } else {
            return this._super(fieldSelectElement);
        }
    }
});
Vtiger_Field_Js('EMAILMaker_Field_Js', {}, {
    getUiTypeSpecificHtml: function () {
        var uiTypeModel = this.getUiTypeModel();
        return uiTypeModel.getUi();
    },
    getModuleName: function () {
        var currentModule = app.getModuleName();
        return currentModule;
    },
    getUi: function () {
        var html = '<input type="text" class="getPopupUi inputElement" name="' + this.getName() + '"  /><input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        html = jQuery(html);
        html.filter('.getPopupUi').val(app.htmlDecode(this.getValue()));
        return this.addValidationToElement(html);
    }
});
EMAILMaker_Field_Js('EMAILMaker_Text_Field_Js', {}, {
    getUi: function () {
        var html = '<textarea class="getPopupUi" name="' + this.getName() + '" value="">' + this.getValue() + '</textarea>' +
            '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
        html = jQuery(html);
        return this.addValidationToElement(html);
    }
});
Vtiger_Date_Field_Js('EMAILMaker_Date_Field_Js', {}, {
    getDateFormat: function () {
        return this.get('date-format');
    },
    getUi: function () {
        var comparatorSelectedOptionVal = this.get('comparatorElementVal');
        var dateSpecificConditions = this.get('dateSpecificConditions');
        if (comparatorSelectedOptionVal.length > 0) {
            if (comparatorSelectedOptionVal == 'between' || comparatorSelectedOptionVal == 'custom') {
                var html = '<div class="date"><input class="dateField" data-calendar-type="range" name="' + this.getName() + '" data-date-format="' + this.getDateFormat() + '" type="text" ReadOnly="true" value="' + this.getValue() + '"></div>';
                var element = jQuery(html);
                return this.addValidationToElement(element);
            } else if (this._specialDateComparator(comparatorSelectedOptionVal)) {
                var html = '<input name="' + this.getName() + '" type="text" value="' + this.getValue() + '" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator="[{"name":"PositiveNumber"}]">\n\
                                                <input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
                return jQuery(html);
            } else if (comparatorSelectedOptionVal in dateSpecificConditions) {
                var startValue = dateSpecificConditions[comparatorSelectedOptionVal]['startdate'];
                var endValue = dateSpecificConditions[comparatorSelectedOptionVal]['enddate'];
                var html = '<input name="' + this.getName() + '"  type="text" ReadOnly="true" value="' + startValue + ',' + endValue + '">'
                return jQuery(html);
            } else if (comparatorSelectedOptionVal == 'is today' || comparatorSelectedOptionVal == 'is tomorrow' || comparatorSelectedOptionVal == 'is yesterday') {
            } else {
                return this._super();
            }
        } else {
            var html = '<input type="text" class="getPopupUi date inputElement" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
                '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />'
            var element = jQuery(html);
            return this.addValidationToElement(element);
        }
    },

    _specialDateComparator: function (comp) {
        var specialComparators = ['less than days ago', 'more than days ago', 'in less than', 'in more than', 'days ago', 'days later', 'less than days later', 'more than days later'];
        for (var index in specialComparators) {
            if (comp == specialComparators[index]) {
                return true;
            }
        }
        return false;
    }
});

Vtiger_Date_Field_Js('EMAILMaker_Datetime_Field_Js', {}, {
    getDateFormat: function () {
        return this.get('date-format');
    },
    getUi: function () {
        var comparatorSelectedOptionVal = this.get('comparatorElementVal');
        if (this._specialDateTimeComparator(comparatorSelectedOptionVal)) {
            var html = '<input name="' + this.getName() + '" type="text" value="' + this.getValue() + '" data-validator="[{name:PositiveNumber}]"><input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
            var element = jQuery(html);
        } else if (comparatorSelectedOptionVal == 'is today' || comparatorSelectedOptionVal == 'is tomorrow' || comparatorSelectedOptionVal == 'is yesterday') {
        } else {
            var html = '<input type="text" class="getPopupUi date" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
                '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />'
            var element = jQuery(html);
        }
        return element;
    },

    _specialDateTimeComparator: function (comp) {
        var specialComparators = ['less than hours before', 'less than hours later', 'more than hours later', 'more than hours before'];
        for (var index in specialComparators) {
            if (comp == specialComparators[index]) {
                return true;
            }
        }
        return false;
    }
});

Vtiger_Currency_Field_Js('EMAILMaker_Currency_Field_Js', {}, {
    getUi: function () {
        var html = '<input type="text" class="getPopupUi marginLeftZero inputElement" name="' + this.getName() + '" value="' + this.getValue() + '"  />' +
            '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
        var element = jQuery(html);
        return this.addValidationToElement(element);
    }
});
Vtiger_Time_Field_Js('EMAILMaker_Time_Field_Js', {}, {
    getUi: function () {
        var html = '<input type="text" class="getPopupUi time inputElement" name="' + this.getName() + '"  value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
        var element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Percentage_Field_Js', {}, {
    getUi: function () {
        var html = '<input type="text" class="getPopupUi" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        var element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Text_Field_Js', {}, {
    getUi: function () {
        var html = '<input type="text" class="getPopupUi inputElement" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        var element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Boolean_Field_Js', {}, {
    getUi: function () {
        var html = '<input type="text" class="getPopupUi boolean inputElement" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        var element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Owner_Field_Js('EMAILMaker_Owner_Field_Js', {}, {

    getUi: function () {
        var html = '<select class="col-lg-12 select2" name="' + this.getName() + '">';
        html += '<option value="">&nbsp;</option>';
        var pickListValues = this.getPickListValues();
        var selectedOption = this.getValue();
        for (var optGroup in pickListValues) {
            html += '<optgroup label="' + optGroup + '">';
            var optionGroupValues = pickListValues[optGroup];
            for (var option in optionGroupValues) {
                html += '<option value="' + option + '" ';
                if (option == selectedOption) {
                    html += ' selected ';
                }
                html += '>' + optionGroupValues[option] + '</option>';
            }
            html += '</optgroup>'
        }

        html += '</select>';
        var selectContainer = jQuery(html);
        this.addValidationToElement(selectContainer);
        return selectContainer;
    }
});

Vtiger_Owner_Field_Js('EMAILMaker_Ownergroup_Field_Js', {}, {
    getUi: function () {
        var html = '<select class="select2 inputElement" name="' + this.getName() + '" id="field_' + this.getModuleName() + '_' + this.getName() + '">';
        var pickListValues = this.getPickListValues();
        var selectedOption = this.getValue();
        html += '<option value=""></option>';
        for (var option in pickListValues) {
            html += '<option value="' + option + '" ';
            if (option == selectedOption) {
                html += ' selected ';
            }
            html += '>' + pickListValues[option] + '</option>';
        }
        html += '</select>';
        var selectContainer = jQuery(html);
        this.addValidationToElement(selectContainer);
        return selectContainer;
    }
});

Vtiger_Picklist_Field_Js('EMAILMaker_Picklist_Field_Js', {}, {

    getUi: function () {
        var selectedOption = app.htmlDecode(this.getValue());
        var pickListValues = this.getPickListValues();
        var tagsArray = [];
        jQuery.map(pickListValues, function (val, i) {
            tagsArray.push(val);
        });
        var pickListValuesArrayFlip = {};
        for (var key in pickListValues) {
            var pickListValue = pickListValues[key];
            pickListValuesArrayFlip[pickListValue] = key;
        }
        var html = '<input type="hidden" class="col-lg-12 select2" name="' + this.getName() + '" id="' + this.getName() + '">';
        var selectContainer = jQuery(html).val(selectedOption);
        selectContainer.data('tags', tagsArray).data('picklistvalues', pickListValuesArrayFlip).data('maximumSelectionSize', 1);
        selectContainer.data('placeholder', app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')).data('closeOnSelect', true);
        this.addValidationToElement(selectContainer);
        return selectContainer;
    }
});
