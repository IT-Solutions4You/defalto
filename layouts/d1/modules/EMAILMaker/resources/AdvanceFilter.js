/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
Vtiger_AdvanceFilter_Js('EMAILMaker_AdvanceFilter_Js', {}, {
    validationSupportedFieldConditionMap: {
        'email': ['e', 'n'],
        'date': ['is'],
        'datetime': ['is']
    },
    allConditionValidationNeededFieldList: ['double', 'integer'],
    comparatorsWithNoValueBoxMap: ['is empty', 'is not empty'],
    getFieldSpecificType: function (fieldSelected) {
        let fieldInfo = fieldSelected.data('fieldinfo');

        if ('undefined' !== typeof fieldInfo) {
            return fieldInfo['type'];
        }

        return fieldSelected.data('fieldtype');
    },
    getModuleName: function () {
        return app.getModuleName();
    },
    addNewCondition: function (conditionGroupElement) {
        let basicElement = jQuery('.basic', conditionGroupElement);
        let newRowElement = basicElement.find('.conditionRow').clone(true, true);
        jQuery('select', newRowElement).addClass('select2');
        let conditionList = jQuery('.conditionList', conditionGroupElement);
        conditionList.append(newRowElement);
        //change in to chosen elements
        vtUtils.showSelect2ElementView(newRowElement.find('select.select2'));
        newRowElement.find('[name="columnname"]').find('optgroup:first option:first').attr('selected', 'selected').trigger('change');
        return this;
    },
    loadConditions: function (fieldSelect) {
        let row = fieldSelect.closest('div.conditionRow'),
            conditionSelectElement = row.find('select[name="comparator"]'),
            conditionSelected = conditionSelectElement.val(),
            fieldSelected = fieldSelect.find('option:selected'),
            fieldLabel = fieldSelected.val(),
            match = fieldLabel.match(/\((\w+)\) (\w+)/),
            fieldSpecificType = this.getFieldSpecificType(fieldSelected),
            conditionList = this.getConditionListFromType(fieldSpecificType);

        if (typeof conditionList == 'undefined') {
            conditionList = {};
            conditionList['none'] = '';
        }

        let options = '';

        for (let key in conditionList) {
            if (conditionList.hasOwnProperty(key)) {
                let conditionValue = conditionList[key],
                    conditionLabel = this.getConditionLabel(conditionValue);

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
        let options = '';
        let fieldDataInfo = fieldSelected.data('fieldinfo');
        let fieldModel = Vtiger_Field_Js.getInstance(fieldDataInfo, this.getModuleName());
        let picklistValues = fieldModel.data.picklistvalues;
        for (let key in conditionList) {
            if (conditionList.hasOwnProperty(key)) {
                let conditionValue = conditionList[key];
                let conditionLabel = this.getConditionLabel(conditionValue);
                if (match != null) {
                    if (conditionValue != 'has changed') {
                        if (conditionValue.indexOf("hours since") != -1) {
                            for (let key in picklistValues) {
                                let picklistvalue = conditionValue.replace(/%s/i, picklistValues[key]);
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
                        for (let key in picklistValues) {
                            let picklistvalue = conditionValue.replace(/%s/i, picklistValues[key]);
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
        let thisInstance = this,
            filterContainer = this.getFilterContainer(),
            fieldList = ['columnname', 'comparator', 'value', 'valuetype', 'column_condition'],
            values = {},
            columnIndex = 0,
            conditionGroups = jQuery('.conditionGroup', filterContainer);

        conditionGroups.each(function (index, domElement) {
            let groupElement = jQuery(domElement),
                conditions = jQuery('.conditionList .conditionRow', groupElement);

            if (conditions.length <= 0) {
                return true;
            }

            let iterationValues = {};

            conditions.each(function (i, conditionDomElement) {
                let rowElement = jQuery(conditionDomElement),
                    fieldSelectElement = jQuery('[name="columnname"]', rowElement),
                    valueSelectElement = jQuery('[data-value="value"]', rowElement);

                if (thisInstance.isEmptyFieldSelected(fieldSelectElement)) {
                    return true;
                }

                let fieldDataInfo = fieldSelectElement.find('option:selected').data('fieldinfo'),
                    fieldType = fieldDataInfo.type,
                    rowValues = {};

                if (fieldType === 'picklist' || fieldType === 'multipicklist') {
                    for (let key in fieldList) {
                        let field = fieldList[key];

                        if (field === 'value' && valueSelectElement.is('input')) {
                            let commaSeperatedValues = valueSelectElement.val(),
                                pickListValues = valueSelectElement.data('picklistvalues'),
                                valuesArr = commaSeperatedValues.split(','),
                                newValuesArr = [];

                            for (i = 0; i < valuesArr.length; i++) {
                                if (typeof pickListValues[valuesArr[i]] != 'undefined') {
                                    newValuesArr.push(pickListValues[valuesArr[i]]);
                                } else {
                                    newValuesArr.push(valuesArr[i]);
                                }
                            }

                            rowValues[field] = newValuesArr;
                        } else if (field === 'value' && valueSelectElement.is('select') && fieldType === 'picklist') {
                            rowValues[field] = valueSelectElement.val();
                        } else if (field === 'value' && valueSelectElement.is('select') && fieldType === 'multipicklist') {
                            rowValues[field] = valueSelectElement.val();
                        } else {
                            rowValues[field] = jQuery('[name="' + field + '"]', rowElement).val();
                        }

                        if ('value' === field && 'object' === typeof rowValues[field] && rowValues[field]) {
                            rowValues[field] = rowValues[field].join(',');
                        }
                    }
                } else {
                    for (let key in fieldList) {
                        let field = fieldList[key];

                        if (field === 'value') {
                            if ((fieldType === 'date' || fieldType === 'datetime') && valueSelectElement.length > 0) {
                                let value = valueSelectElement.val(),
                                    dateFormat = app.getDateFormat(),
                                    dateFormatParts = dateFormat.split("-"),
                                    valueArray = value.split(',');

                                for (i = 0; i < valueArray.length; i++) {
                                    let valueParts = valueArray[i].split("-"),
                                        dateInstance = new Date(valueParts[dateFormatParts.indexOf('yyyy')], parseInt(valueParts[dateFormatParts.indexOf('mm')]) - 1, valueParts[dateFormatParts.indexOf('dd')]);

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
        let fieldSelected = fieldSelectElement.find('option:selected'),
            fieldInfo = fieldSelected.data('fieldinfo');

        if ('undefined' !== typeof fieldInfo && jQuery.inArray(fieldInfo.comparatorElementVal, this.comparatorsWithNoValueBoxMap) != -1) {
            return jQuery('');
        } else {
            return this._super(fieldSelectElement);
        }
    }
});
Vtiger_Field_Js('EMAILMaker_Field_Js', {}, {
    getUiTypeSpecificHtml: function () {
        let uiTypeModel = this.getUiTypeModel();

        return uiTypeModel.getUi();
    },
    getModuleName: function () {
        return app.getModuleName();
    },
    getUi: function () {
        let html = '<input type="text" class="getPopupUi inputElement form-control" name="' + this.getName() + '"  /><input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        html = jQuery(html);
        html.filter('.getPopupUi').val(app.htmlDecode(this.getValue()));

        return this.addValidationToElement(html);
    }
});
EMAILMaker_Field_Js('EMAILMaker_Text_Field_Js', {}, {
    getUi: function () {
        let html = '<textarea class="getPopupUi form-control" name="' + this.getName() + '" value="">' + this.getValue() + '</textarea>' +
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
        let comparatorSelectedOptionVal = this.get('comparatorElementVal');
        let dateSpecificConditions = this.get('dateSpecificConditions');
        if (comparatorSelectedOptionVal.length > 0) {
            if (comparatorSelectedOptionVal == 'between' || comparatorSelectedOptionVal == 'custom') {
                let html = '<div class="date"><input class="dateField" data-calendar-type="range" name="' + this.getName() + '" data-date-format="' + this.getDateFormat() + '" type="text" ReadOnly="true" value="' + this.getValue() + '"></div>';
                let element = jQuery(html);
                return this.addValidationToElement(element);
            } else if (this._specialDateComparator(comparatorSelectedOptionVal)) {
                let html = '<input name="' + this.getName() + '" type="text" value="' + this.getValue() + '" data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" data-validator="[{"name":"PositiveNumber"}]">\n\
                                                <input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
                return jQuery(html);
            } else if (comparatorSelectedOptionVal in dateSpecificConditions) {
                let startValue = dateSpecificConditions[comparatorSelectedOptionVal]['startdate'];
                let endValue = dateSpecificConditions[comparatorSelectedOptionVal]['enddate'];
                let html = '<input name="' + this.getName() + '"  type="text" ReadOnly="true" value="' + startValue + ',' + endValue + '">'
                return jQuery(html);
            } else if (comparatorSelectedOptionVal == 'is today' || comparatorSelectedOptionVal == 'is tomorrow' || comparatorSelectedOptionVal == 'is yesterday') {
            } else {
                return this._super();
            }
        } else {
            let html = '<input type="text" class="getPopupUi date inputElement form-control" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
                '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />'
            let element = jQuery(html);
            return this.addValidationToElement(element);
        }
    },

    _specialDateComparator: function (comp) {
        let specialComparators = ['less than days ago', 'more than days ago', 'in less than', 'in more than', 'days ago', 'days later', 'less than days later', 'more than days later'];
        for (let index in specialComparators) {
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
        let comparatorSelectedOptionVal = this.get('comparatorElementVal'),
            html = '';

        if (this._specialDateTimeComparator(comparatorSelectedOptionVal)) {
            html = '<input name="' + this.getName() + '" type="text" value="' + this.getValue() + '" data-validator="[{name:PositiveNumber}]"><input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        } else if (comparatorSelectedOptionVal == 'is today' || comparatorSelectedOptionVal == 'is tomorrow' || comparatorSelectedOptionVal == 'is yesterday') {

        } else {
            html = '<input type="text" class="getPopupUi date form-control" name="' + this.getName() + '"  data-date-format="' + this.getDateFormat() + '"  value="' + this.getValue() + '" />' +
                '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        }

        return jQuery(html);
    },

    _specialDateTimeComparator: function (comp) {
        let specialComparators = ['less than hours before', 'less than hours later', 'more than hours later', 'more than hours before'];
        for (let index in specialComparators) {
            if (comp == specialComparators[index]) {
                return true;
            }
        }
        return false;
    }
});

Vtiger_Currency_Field_Js('EMAILMaker_Currency_Field_Js', {}, {
    getUi: function () {
        let html = '<input type="text" class="getPopupUi marginLeftZero inputElement form-control" name="' + this.getName() + '" value="' + this.getValue() + '"  />' +
            '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
        let element = jQuery(html);
        return this.addValidationToElement(element);
    }
});
Vtiger_Time_Field_Js('EMAILMaker_Time_Field_Js', {}, {
    getUi: function () {
        let html = '<input type="text" class="getPopupUi time inputElement form-control" name="' + this.getName() + '"  value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('workflow_valuetype') + '" />';
        let element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Percentage_Field_Js', {}, {
    getUi: function () {
        let html = '<input type="text" class="getPopupUi inputElement form-control" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        let element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Text_Field_Js', {}, {
    getUi: function () {
        let html = '<input type="text" class="getPopupUi inputElement form-control" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        let element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Field_Js('Vtiger_Boolean_Field_Js', {}, {
    getUi: function () {
        let html = '<input type="text" class="getPopupUi boolean inputElement form-control" name="' + this.getName() + '" value="' + this.getValue() + '" />' +
            '<input type="hidden" name="valuetype" value="' + this.get('emailmaker_valuetype') + '" />';
        let element = jQuery(html);
        return this.addValidationToElement(element);
    }
});

Vtiger_Owner_Field_Js('EMAILMaker_Owner_Field_Js', {}, {

    getUi: function () {
        let html = '<select class="select2 form-select" name="' + this.getName() + '">';
        html += '<option value="">&nbsp;</option>';
        let pickListValues = this.getPickListValues();
        let selectedOption = this.getValue();
        for (let optGroup in pickListValues) {
            html += '<optgroup label="' + optGroup + '">';
            let optionGroupValues = pickListValues[optGroup];
            for (let option in optionGroupValues) {
                html += '<option value="' + option + '" ';
                if (option == selectedOption) {
                    html += ' selected ';
                }
                html += '>' + optionGroupValues[option] + '</option>';
            }
            html += '</optgroup>'
        }

        html += '</select>';
        let selectContainer = jQuery(html);
        this.addValidationToElement(selectContainer);
        return selectContainer;
    }
});

Vtiger_Owner_Field_Js('EMAILMaker_Ownergroup_Field_Js', {}, {
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
            html = '<select class="OwnerField select2 inputElement form-select" data-value="value" multiple name="' + this.getName() + '[]">' + this.getOptions() + '</select>';
        } else {
            html = '<select class="OwnerField select2 inputElement form-select" data-value="value" data-tags="true" data-multiple="true" multiple type="hidden"  name="' + this.getName() + '">' + this.getOptions() + '</select>';
        }

        let selectContainer = jQuery(html);

        this.setSelected(selectContainer);
        this.addValidationToElement(selectContainer);

        return selectContainer;
    }
});

Vtiger_Picklist_Field_Js('EMAILMaker_Picklist_Field_Js', {}, {
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
        let html = '<select class="PicklistField select2 inputElement form-select" data-value="value" multiple data-maximum-selection-length="1" name="' + this.getName() + '[]">' + this.getOptions() + '</select>',
            selectContainer = jQuery(html);

        this.setSelected(selectContainer);
        this.addValidationToElement(selectContainer);

        return selectContainer;
    }
});
