/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Settings_Workflows_Edit_Js */
Settings_Vtiger_Edit_Js('Settings_Workflows_Edit_Js', {}, {
    workFlowsContainer: false,
    advanceFilterInstance: false,
    ckEditorInstance: false,
    fieldValueMap: false,
    workFlowsActionContainer: false,

    /**
     * Function to get the container which holds all the workflow elements
     * @return jQuery object
     */
    getContainer: function () {
        return this.workFlowsContainer;
    },

    /**
     * Function to get the container which holds all the workflow elements
     * @return jQuery object
     */
    getActionContainer: function () {
        return this.workFlowsActionContainer;
    },

    /**
     * Function to set the reports container
     * @params : element - which represents the workflow container
     * @return : current instance
     */
    setContainer: function (element) {
        this.workFlowsContainer = element;
        return this;
    },

    /**
     * Function to set the reports step1 container
     * @params : element - which represents the reports step1 container
     * @return : current instance
     */
    setActionContainer: function (element) {
        this.workFlowsActionContainer = element;
        return this;
    },

    calculateValues: function () {
        //handled advanced filters saved values.
        var enableFilterElement = jQuery('#enableAdvanceFilters');
        if (enableFilterElement.length > 0 && enableFilterElement.is(':checked') == false) {
            jQuery('#advanced_filter').val(jQuery('#olderConditions').val());
        } else {
            jQuery('[name="filtersavedinnew"]').val("6");
            var advfilterlist = this.advanceFilterInstance.getValues();
            jQuery('#advanced_filter').val(JSON.stringify(advfilterlist));
        }
    },

    checkExpressionValidation: function (form) {
        var params = {
            'module': app.module(),
            'parent': app.getParentModuleName(),
            'action': 'ValidateExpression',
            'mode': 'ForWorkflowEdit'
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
        var form = jQuery('#workflow_edit');
        var params = {
            submitHandler: function (form) {
                if (jQuery('[name="workflow_trigger"]').val() == '6' && jQuery('#schtypeid').val() == '3') {
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
                //form.get(0).submit();
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
                var clonedDateElement = '<input type="text" class="dateField fieldValue inputElement form-control" value="' + value + '" data-date-format="' + dataFormat + '" data-input="true" >'
                clonedPopupUi.find('.fieldValueContainer div').prepend(clonedDateElement);
            } else if (fieldValueElement.hasClass('time')) {
                clonedPopupUi.find('.textType').find('option[value="rawtext"]').attr('data-ui', 'input');
                const timeFormat = fieldValueElement.data('time-format');
                let value = '';

                if (valueType === 'rawtext') {
                    value = fieldValueElement.val();
                }

                let clonedTimeElement = '<input type="text" style="width: 30%;" class="timepicker-default fieldValue inputElement" value="' + value + '" data-format="' + timeFormat + '" data-input="true" >'
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
            let valueType = jQuery(e.currentTarget).val();
            valueType = app.helper.purifyContent(valueType);
            const useFieldContainer = jQuery('.useFieldContainer', data);
            const useFunctionContainer = jQuery('.useFunctionContainer', data);
            const uiType = jQuery(e.currentTarget).find('option:selected').data('ui');
            jQuery('.fieldValue', data).hide();
            jQuery('[data-' + uiType + ']', data).show();

            if (valueType === 'fieldname') {
                useFieldContainer.removeClass('hide');
                useFunctionContainer.addClass('hide');
            } else if (valueType === 'expression') {
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
    registerEventForShowModuleFilterCondition: function () {
        var thisInstance = this;
        jQuery('#module_name').on('change', function (e) {
            var currentElement = jQuery(e.currentTarget);
            var selectedOption = currentElement.find('option:selected');
            jQuery('#workflowTriggerCreate').html(selectedOption.data('create-label'));
            jQuery('#workflowTriggerUpdate').html(selectedOption.data('update-label'));
            var params = {
                'module': 'Workflows',
                'parent': 'Settings',
                'view': 'EditAjax',
                'mode': 'getWorkflowConditions',
                'record': jQuery("input[name='record']").val(),
                'module_name': currentElement.val()
            }

            app.helper.showProgress();
            app.request.get({data: params}).then(function (error, data) {
                app.helper.hideProgress();
                jQuery('#workflow_condition').html(data);
                var advanceFilterContainer = jQuery('#advanceFilterContainer');
                vtUtils.applyFieldElementsView(jQuery('#workflow_condition'));
                thisInstance.advanceFilterInstance = Workflows_AdvanceFilter_Js.getInstance(jQuery('.filterContainer', advanceFilterContainer));
                thisInstance.getPopUp(advanceFilterContainer);

                //Workflows actions
                thisInstance.setActionContainer(jQuery('#workflow_action'));
                thisInstance.registerEditTaskEvent();
                thisInstance.registerTaskStatusChangeEvent();
                thisInstance.registerTaskDeleteEvent();

                app.helper.registerLeavePageWithoutSubmit(jQuery('#workflow_edit'));
            });
        });
        jQuery('#module_name').trigger('change');
    },

    //Workflow action related api's
    registerEditTaskEvent: function () {
        let self = this,
            container = this.getActionContainer();

        container.on('click', '[data-url]', function (e) {
            let currentElement = jQuery(e.currentTarget),
                url = currentElement.data('url') + '&module_name=' + jQuery('#module_name').val();

            app.helper.showProgress();
            app.request.get({url: url}).then(function (error, data) {
                app.helper.hideProgress();
                app.helper.loadPageContentOverlay(data, {focus: false}).then(function (container) {
                    container = jQuery(container);

                    app.helper.showVerticalScroll(container.find('.modal-body.editTaskBody'), {
                        setHeight: ($(window).height() - jQuery('.app-fixed-navbar').height() - container.find('.modal-header').height()) + 'px'
                    });

                    let taskType = jQuery('#taskType').val(),
                        functionName = 'register' + taskType + 'Events';

                    if (typeof self[functionName] != 'undefined') {
                        self[functionName].apply(self);
                    }

                    self.registerSaveTaskSubmitEvent(taskType);
                    self.registerFillTaskFieldsEvent();
                    self.registerCheckSelectDateEvent();
                });
            });
        });

        container.on('click', '.editTask', function (e) {
            var currentElement = jQuery(e.currentTarget);
            var params = {
                module: 'Workflows',
                parent: 'Settings',
                view: 'EditV7Task',
                type: currentElement.data('taskType'),
                module_name: jQuery('#module_name').val()
            }
            var parentElement = currentElement.closest('tr');
            var taskData = parentElement.find('.taskData').val();
            if (taskData) {
                params.taskData = taskData;
            }
            app.helper.showProgress();
            app.request.post({data: params}).then(function (error, data) {
                app.helper.hideProgress();
                app.helper.loadPageContentOverlay(data).then(function (container) {
                    var overlayPageContent = $('#overlayPageContent');
                    overlayPageContent.css('margin-left', '230px');
                    var viewPortHeight = $(window).height();
                    var params = {
                        setHeight: (viewPortHeight - jQuery('.app-fixed-navbar').height() - container.find('.modal-header').height()) + 'px'
                    };
                    app.helper.showVerticalScroll(container.find('.modal-body.editTaskBody'), params);
                    var taskType = jQuery('#taskType').val();
                    var functionName = 'register' + taskType + 'Events';
                    if (typeof thisInstance[functionName] != 'undefined') {
                        thisInstance[functionName].apply(thisInstance);
                    }
                    thisInstance.registerSaveTaskSubmitEvent(taskType);
                    thisInstance.registerFillTaskFieldsEvent();
                    thisInstance.registerCheckSelectDateEvent();
                });
            });
        });
        container.on('click', '.deleteTaskTemplate', function (e) {
            var currentElement = jQuery(e.currentTarget);
            var parentElement = currentElement.closest('tr');
            var tableDiv = jQuery('#table-content');
            var table = tableDiv.find('#listview-table');
            parentElement.remove();
            var visibleRows = table.find('tbody').find('tr:visible');
            if (visibleRows.length == 0) {
                tableDiv.find('.emptyRecordsDiv').removeClass('hide');
            }
        });
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
    /**
     * Function to add event on signature popover
     */
    registerTooltipEventForSignatureField: function () {
        jQuery("#signaturePopover").on('mouseover', function (e) {
            jQuery('#signaturePopover').popover({
                'html': true
            });
        });
    },

    getParams: function (form, taskType) {
        let preSaveActionFunctionName = 'preSave' + taskType;
        if (typeof this[preSaveActionFunctionName] !== 'undefined') {
            this[preSaveActionFunctionName].apply(this, [taskType]);
        }
        let params = form.serializeFormData();

        //when using the VTCreateEntityTask
        //we avoid sending individual fieldmapping inputs as part of the request because
        //they will be already present as json in the "field_value_mapping" hidden input
        //and we want to avoid requests conflicts when doing server-side validation of individual fields such as parent_id in HelpDesk module.
        if (taskType === 'VTCreateEntityTask') {
            let mappingInputs = jQuery('#save_fieldvaluemapping').find('input.inputElement');
            mappingInputs.each(function (index) {
                let fieldName = $(this).attr('name');
                delete params[fieldName];
            });
        }

        return params;
    },

    registerSaveTaskSubmitEvent: function (taskType) {
        var thisInstance = this;
        var form = jQuery('#saveTask');
        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                // to Prevent submit if already submitted
                jQuery("button[name='saveButton']", form).attr("disabled", "disabled");
                var record = jQuery('#record').val();
                const params = thisInstance.getParams(form, taskType);

                if (!record) {
                    var clonedParams = jQuery.extend({}, params);
                    clonedParams.action = 'ValidateExpression';
                    clonedParams.mode = 'ForTaskEdit';
                    app.request.post({'data': clonedParams}).then(function (error, data) {
                        if (error != null) {
                            app.helper.showErrorNotification({'message': app.vtranslate('LBL_EXPRESSION_INVALID')});
                            return;
                        }
                        app.helper.hidePageContentOverlay();
                        if (!params.tmpTaskId) {
                            params.tmpTaskId = thisInstance.getUniqueNumber();
                        }
                        var templateData = $('<input>').attr({
                            type: 'hidden',
                            name: 'tasks[]'
                        }).addClass('taskData').val(JSON.stringify(params));
                        var tableDiv = jQuery('#table-content');
                        var table = tableDiv.find('#listview-table');
                        var tableBody = table.find('tbody');
                        var taskTemplate = tableBody.find('.taskTemplate').clone(true, true);
                        taskTemplate.removeClass('hide taskTemplate');
                        taskTemplate.find('.taskType').text(app.vtranslate(params.taskType));
                        taskTemplate.find('.taskName').text(params.summary);
                        taskTemplate.find('.editTask').data('taskType', params.taskType);
                        taskTemplate.append(templateData);
                        taskTemplate.addClass('tmpTaskId-' + params.tmpTaskId);
                        if (params.active == 'false') {
                            taskTemplate.find('.tmpTaskStatus').val('off').prop('checked', false);
                        }
                        taskTemplate.find('.tmpTaskStatus').addClass('taskStatus');
                        tableDiv.find('.emptyRecordsDiv').addClass('hide');
                        if (table.find('.tmpTaskId-' + params.tmpTaskId).length != 0) {
                            table.find('.tmpTaskId-' + params.tmpTaskId).replaceWith(taskTemplate);
                        } else {
                            tableBody.append(taskTemplate);
                        }
                    });

                } else {
                    form.find('[name="saveButton"]').attr('disabled', 'disabled');
                    app.helper.showProgress();
                    app.request.post({data: params}).then(function (error, data) {
                        app.helper.hideProgress();
                        if (data) {
                            thisInstance.getTaskList();
                            app.helper.hidePageContentOverlay();
                        } else {
                            app.helper.showErrorNotification({'message': app.vtranslate('LBL_EXPRESSION_INVALID')});
                            form.find('[name="saveButton"]').removeAttr('disabled');
                        }
                    });
                }
            },
            ignore: ".ignore-validation"
        };
        form.vtValidate(params);
    },

    getUniqueNumber: function () {
        var date = new Date();
        var components = [
            date.getYear(),
            date.getMonth(),
            date.getDate(),
            date.getHours(),
            date.getMinutes(),
            date.getSeconds(),
            date.getMilliseconds()
        ];

        var id = components.join("");

        return id;
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
                var fields = jQuery('[data-workflow_columnname="' + fieldName + '"]').not(':hidden');
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
    preSaveVTUpdateFieldsTask: function (tasktype) {
        var values = this.getValues(tasktype);
        jQuery('[name="field_value_mapping"]').val(JSON.stringify(values));
    },
    preSaveVTCreateEntityTask: function (tasktype) {
        var values = this.getValues(tasktype);
        jQuery('[name="field_value_mapping"]').val(JSON.stringify(values));
    },
    preSaveVTEmailTask: function (tasktype) {
        var textAreaElement = jQuery('#content');
        //To keep the plain text value to the textarea which need to be
        //sent to server
        textAreaElement.val(CKEDITOR.instances['content'].getData());
    },
    /**
     * Function to check if the field selected is empty field
     * @params : select element which represents the field
     * @return : boolean true/false
     */
    isEmptyFieldSelected: function (fieldSelect) {
        const selectedOption = fieldSelect.find('option:selected');
        //assumption that empty field will be having value none

        return selectedOption.val() === 'none' || selectedOption.val() === '';
    },
    getVTCreateEntityTaskFieldList: function () {
        return new Array('fieldname', 'value', 'valuetype', 'modulename');
    },
    getVTUpdateFieldsTaskFieldList: function () {
        return new Array('fieldname', 'value', 'valuetype');
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

    getTaskList: function () {
        var params = {
            module: app.getModuleName(),
            parent: app.getParentModuleName(),
            view: 'TasksList',
            record: jQuery('[name="record"]').val()
        }
        app.helper.showProgress();
        app.request.get({data: params}).then(function (error, data) {
            jQuery('#taskListContainer').html(data);
            app.helper.hideProgress();
        });
    },

    /**
     * Function to get ckEditorInstance
     */
    getckEditorInstance: function () {
        if (this.ckEditorInstance == false) {
            this.ckEditorInstance = new Vtiger_CkEditor_Js();
        }
        return this.ckEditorInstance;
    },
    registerTaskStatusChangeEvent: function () {
        var container = this.getActionContainer();
        container.on('change', '.taskStatus', function (e) {
            var currentStatusElement = jQuery(e.currentTarget);
            var url = currentStatusElement.data('statusurl');
            if (currentStatusElement.is(':checked')) {
                url = url + '&status=true';
            } else {
                url = url + '&status=false';
            }
            app.helper.showProgress();
            app.request.post({data: url}).then(function (error, data) {
                if (data.result == "ok") {
                    app.helper.showSuccessNotification({message: 'JS_STATUS_CHANGED_SUCCESSFULLY'})
                }
                app.helper.hideProgress();
            });
            e.stopImmediatePropagation();
        });
    },
    registerTaskDeleteEvent: function () {
        var thisInstance = this;
        var container = this.getActionContainer();
        container.on('click', '.deleteTask', function (e) {
            var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
            app.helper.showConfirmationBox({
                'message': message
            }).then(
                function () {
                    var currentElement = jQuery(e.currentTarget);
                    var deleteUrl = currentElement.data('deleteurl');
                    app.helper.showProgress();
                    app.request.post({url: deleteUrl}).then(function (error, data) {
                        app.helper.hideProgress();
                        if (data == 'ok') {
                            thisInstance.getTaskList();
                            app.helper.showSuccessNotification({message: app.vtranslate('JS_TASK_DELETED_SUCCESSFULLY')});
                        }
                    });
                });
        });
    },
    registerFillTaskFromEmailFieldEvent: function () {
        jQuery('#saveTask').on('change', '#fromEmailOption', function (e) {
            var currentElement = jQuery(e.currentTarget);
            var inputElement = currentElement.closest('.row').find('.fields');
            inputElement.val(currentElement.val());
        })
    },
    registerFillTaskFieldsEvent: function () {
        jQuery('#saveTask').on('change', '.task-fields', function (e) {
            var currentElement = jQuery(e.currentTarget);
            var inputElement = currentElement.closest('.row').find('.fields');
            if (currentElement.hasClass('overwriteSelection')) {
                inputElement.val(currentElement.val());
            } else {
                var oldValue = inputElement.val();
                var newValue = oldValue + currentElement.val();
                inputElement.val(newValue);
            }
        });
    },
    registerFillMailContentEvent: function () {
        jQuery('#task-fieldnames,#task_timefields,#task-templates,#task-emailtemplates').change(function (e) {
            var textarea = CKEDITOR.instances.content;
            var value = jQuery(e.currentTarget).val();
            if (textarea != undefined) {
                textarea.insertHtml(value);
            } else if (jQuery('textarea[name="content"]')) {
                var textArea = jQuery('textarea[name="content"]');
                textArea.insertAtCaret(value);
            }
        });
    },
    registerVTEmailTaskEvents: function () {
        var textAreaElement = jQuery('#content');
        var ckEditorInstance = this.getckEditorInstance();
        ckEditorInstance.loadCkEditor(textAreaElement, {height: '30vh'});
        this.registerFillMailContentEvent();
        this.registerTooltipEventForSignatureField();
        this.registerFillTaskFromEmailFieldEvent();
        this.registerCcAndBccEvents();
    },
    registerVTUpdateFieldsTaskEvents: function () {
        var thisInstance = this;
        this.registerAddFieldEvent();
        this.registerDeleteConditionEvent();
        this.registerFieldChange();
        this.fieldValueMap = false;
        if (jQuery('#fieldValueMapping').val() != '') {
            this.fieldValueReMapping();
        }
        var fields = jQuery('#save_fieldvaluemapping').find('select[name="fieldname"]');
        jQuery.each(fields, function (i, field) {
            thisInstance.loadFieldSpecificUi(jQuery(field));
        });
        this.getPopUp(jQuery('#saveTask'));
    },
    registerVTPushNotificationTaskEvents: function () {
        this.registerFillMailContentEvent();
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
        let thisInstance = this;

        jQuery('#saveTask').on('change', 'select[name="fieldname"]', function (e) {
            let selectedElement = jQuery(e.currentTarget);

            if (selectedElement.val() != 'none') {
                let conditionRow = selectedElement.closest('.conditionRow'),
                    moduleNameElement = conditionRow.find('[name="modulename"]');

                if (moduleNameElement.length > 0) {
                    let selectedOptionFieldInfo = selectedElement.find('option:selected').data('fieldinfo'),
                        type = selectedOptionFieldInfo.type;

                    if (type == 'picklist' || type == 'multipicklist') {
                        let moduleName = jQuery('#createEntityModule').val();

                        if (moduleNameElement.is('select').length) {
                            moduleNameElement.find('option[value="' + moduleName + '"]').attr('selected', true);
                            moduleNameElement.trigger('change');
                            moduleNameElement.select2("disable");
                        }
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
            fieldValueMappingKey = fieldInfo.workflow_columnname;
            if (fieldValueMappingKey === undefined || fieldValueMappingKey === null) {
                fieldValueMappingKey = selectedOption.val();
            }
        }
        if (fieldValueMapping != '' && typeof fieldValueMapping[fieldValueMappingKey] != 'undefined') {
            fieldInfo.value = fieldValueMapping[fieldValueMappingKey]['value'];
            fieldInfo.workflow_valuetype = fieldValueMapping[fieldValueMappingKey]['valuetype'];
        } else {
            fieldInfo.workflow_valuetype = 'rawtext';
        }

        if (fieldInfo.type == 'date') {
            fieldInfo.value = fieldUiHolder.find('input').val();
        }

        if (fieldInfo.type == 'reference' || fieldInfo.type == 'multireference') {
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
        fieldSpecificUi.filter('[name="' + fieldName + '"]').attr('data-value', 'value').attr('data-workflow_columnname', fieldInfo.workflow_columnname);
        fieldSpecificUi.find('[name="' + fieldName + '"]').attr('data-value', 'value').attr('data-workflow_columnname', fieldInfo.workflow_columnname);
        fieldSpecificUi.filter('[name="valuetype"]').addClass('ignore-validation');
        fieldSpecificUi.find('[name="valuetype"]').addClass('ignore-validation');

        //If the workflowValueType is rawtext then only validation should happen
        var workflowValueType = fieldSpecificUi.filter('[name="valuetype"]').val();
        if (workflowValueType != 'rawtext' && typeof workflowValueType != 'undefined') {
            fieldSpecificUi.filter('[name="' + fieldName + '"]').addClass('ignore-validation');
            fieldSpecificUi.find('[name="' + fieldName + '"]').addClass('ignore-validation');
        }

        fieldUiHolder.html(fieldSpecificUi);

        if (fieldInfo.type === 'picklist' || fieldInfo.type === 'multipicklist') {
            const editablePicklistValues = Object.values(fieldInfo.editablepicklistvalues);
            fieldSpecificUi.val(fieldInfo.value);
            jQuery('.btn-success').on('click', function (event) {
                const enteredValue = fieldSpecificUi.val().trim();

                if (!editablePicklistValues.includes(enteredValue)) {
                    const message = app.vtranslate('INVALID PICKLIST');
                    app.helper.showErrorNotification({'message': message})
                    event.preventDefault();
                }
            });
        }

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
    registerVTCreateEventTaskEvents: function () {
        vtUtils.registerEventForTimeFields(jQuery('#saveTask'));
        this.registerRecurrenceFieldCheckBox();
        this.repeatMonthOptionsChangeHandling();
        this.registerRecurringTypeChangeEvent();
        this.registerRepeatMonthActions();
    },
    registerVTCreateEntityTaskEvents: function () {
        this.registerChangeCreateEntityEvent();
        this.registerVTUpdateFieldsTaskEvents();
    },
    registerChangeCreateEntityEvent: function () {
        var thisInstance = this;
        jQuery('#createEntityModule').on('change', function (e) {
            var relatedModule = jQuery(e.currentTarget).val();
            var module_name = jQuery('#module_name').val();
            if (relatedModule == module_name) {
                jQuery(e.currentTarget).closest('.taskTypeUi').find('.sameModuleError').removeClass('hide');
            } else {
                jQuery(e.currentTarget).closest('.taskTypeUi').find('.sameModuleError').addClass('hide');
            }
            var params = {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                view: 'CreateEntity',
                relatedModule: jQuery(e.currentTarget).val(),
                for_workflow: jQuery('[name="for_workflow"]').val(),
                module_name: jQuery('#module_name').val()
            }
            app.helper.showProgress();
            app.request.get({data: params}).then(function (error, data) {
                app.helper.hideProgress();
                var createEntityContainer = jQuery('#addCreateEntityContainer');
                createEntityContainer.html(data);
                vtUtils.showSelect2ElementView(createEntityContainer.find('.select2'));
                thisInstance.registerAddFieldEvent();
                thisInstance.fieldValueMap = false;
                if (jQuery('#fieldValueMapping').val() != '') {
                    thisInstance.fieldValueReMapping();
                }
                var fields = jQuery('#save_fieldvaluemapping').find('select[name="fieldname"]');
                jQuery.each(fields, function (i, field) {
                    thisInstance.loadFieldSpecificUi(jQuery(field));
                });
            });
        });
    },
    /**
     * Function which will register change event on recurrence field checkbox
     */
    registerRecurrenceFieldCheckBox: function () {
        var thisInstance = this;
        jQuery('#saveTask').find('input[name="recurringcheck"]').on('change', function (e) {
            var element = jQuery(e.currentTarget);
            var repeatUI = jQuery('#repeatUI');
            if (element.is(':checked')) {
                repeatUI.removeClass('hide');
            } else {
                repeatUI.addClass('hide');
            }
        });
    },
    /**
     * Function which will register the change event for recurring type
     */
    registerRecurringTypeChangeEvent: function () {
        var thisInstance = this;
        jQuery('#recurringType').on('change', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var recurringType = currentTarget.val();
            thisInstance.changeRecurringTypesUIStyles(recurringType);

        });
    },
    /**
     * Function which will register the change event for repeatMonth radio buttons
     */
    registerRepeatMonthActions: function () {
        var thisInstance = this;
        jQuery('#saveTask').find('input[name="repeatMonth"]').on('change', function (e) {
            //If repeatDay radio button is checked then only select2 elements will be enable
            thisInstance.repeatMonthOptionsChangeHandling();
        });
    },
    /**
     * Function which will change the UI styles based on recurring type
     * @params - recurringType - which recurringtype is selected
     */
    changeRecurringTypesUIStyles: function (recurringType) {
        var thisInstance = this;
        if (recurringType == 'Daily' || recurringType == 'Yearly') {
            jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
            jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
        } else if (recurringType == 'Weekly') {
            jQuery('#repeatWeekUI').removeClass('hide').addClass('show');
            jQuery('#repeatMonthUI').removeClass('show').addClass('hide');
        } else if (recurringType == 'Monthly') {
            jQuery('#repeatWeekUI').removeClass('show').addClass('hide');
            jQuery('#repeatMonthUI').removeClass('hide').addClass('show');
        }
    },
    /**
     * This function will handle the change event for RepeatMonthOptions
     */
    repeatMonthOptionsChangeHandling: function () {
        //If repeatDay radio button is checked then only select2 elements will be enable
        if (jQuery('#repeatDay').is(':checked')) {
            jQuery('#repeatMonthDate').attr('disabled', true);
            jQuery('#repeatMonthDayType').select2("enable");
            jQuery('#repeatMonthDay').select2("enable");
        } else {
            jQuery('#repeatMonthDate').removeAttr('disabled');
            jQuery('#repeatMonthDayType').select2("disable");
            jQuery('#repeatMonthDay').select2("disable");
        }
    },
    /*
     * Function to register the events for bcc and cc links
     */
    registerCcAndBccEvents: function () {
        jQuery('[data-show-container]').on('click', function (e) {
            let link = $(this),
                container = jQuery(link.data('showContainer')),
                select = container.find('select');

            container.removeClass('hide');
            link.addClass('hide');
            vtUtils.showSelect2ElementView(select);

        });
    },

    updateDatepickerSelected: function (datepicker) {
        let values = $('#annualDates').val(),
            style = '';

        jQuery.each(values, function (index, value) {
            let dateInfo = value.split('-');

            style += ' .bg-' + dateInfo[0] + '-' + parseInt(dateInfo[1]) + '-' + parseInt(dateInfo[2]) + ' a { border: 1px solid #5E81F4 !important; } '
        });

        $('#annualDatePickerStyle').html(style);
    },
    /**
     * Function to register event for scheduled workflows UI
     */
    registerEventForScheduledWorkflow: function () {
        let thisInstance = this;

        jQuery('input[name="workflow_trigger"]').on('click', function (e) {
            let element = jQuery(e.currentTarget),
                scheduleBoxContainer = jQuery('#scheduleBox'),
                recurrenceBoxContainer = jQuery('.workflowRecurrenceBlock');

            if (element.is(':checked') && element.val() == '6') {
                scheduleBoxContainer.removeClass('hide');
                recurrenceBoxContainer.addClass('hide');
            } else if (element.is(':checked') && element.val() == '3') {
                recurrenceBoxContainer.removeClass('hide');
                recurrenceBoxContainer.find('input[type="radio"]').click();
                scheduleBoxContainer.addClass('hide');
            } else {
                scheduleBoxContainer.addClass('hide');
                recurrenceBoxContainer.addClass('hide');
            }
        });

        vtUtils.registerEventForTimeFields('#schtime', true);
        vtUtils.registerEventForDateFields(jQuery('#scheduleByDate'));

        let weekDaySelect = jQuery(".weekDaySelect");

        weekDaySelect.bind('mousedown', function (e) {
            e.metaKey = true;
        }).selectable();

        weekDaySelect.on('selectableselected selectableunselected', function (event, ui) {
            let inputElement = jQuery('#schdayofweek'),
                weekDaySelect = jQuery('.weekDaySelect'),
                selectedArray = new Array();

            weekDaySelect.find('.ui-selected').each(function () {
                let value = jQuery(this).data('value');
                selectedArray.push(value);
            });

            let selected = selectedArray.join(',');
            inputElement.val(selected);
        });

        let currentYear = (new Date()).getFullYear(),
            datePicker = jQuery('#annualDatePicker');

        datePicker.datepicker({
            firstDay: vtUtils.getFirstDayId(),
            minDate: '01/01/' + currentYear,
            maxDate: '12/31/' + currentYear,
            yearRange: currentYear + ':' + currentYear,
            numberOfMonths: 2,
            showButtonPanel: false,
            onSelect: function (value, element) {
                let date = new Date(value),
                    annualDatesElement = jQuery('#annualDates'),
                    newValue = thisInstance.DateToYMD(date),
                    newOption = new Option(newValue, newValue, false, true),
                    option = annualDatesElement.find('[value="' + newValue + '"]');

                if (option.length) {
                    option.remove();
                } else {
                    annualDatesElement.append(newOption)
                }

                annualDatesElement.trigger('change');
            },
            beforeShowDay: function (date) {
                let backgroundClass = 'bg-' + date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();

                return [true, backgroundClass];
            }
        });

        jQuery('#annualDates').on('change', function () {
            thisInstance.updateDatepickerSelected(datePicker);
        })

        thisInstance.updateDatepickerSelected(datePicker);
    },

    DateToYMD: function (date) {
        let year, month, day;
        year = String(date.getFullYear());
        month = String(date.getMonth() + 1);
        month = 1 === month.length ? '0' + month : month;
        day = String(date.getDate());
        day = 1 === day.length ? '0' + day : day;

        return year + '-' + month + '-' + day;
    },

    registerEventForChangeInScheduledType: function () {
        var thisInstance = this;
        jQuery('#schtypeid').on('change', function (e) {
            var element = jQuery(e.currentTarget);
            var value = element.val();

            thisInstance.showScheduledTime();
            thisInstance.hideScheduledWeekList();
            thisInstance.hideScheduledMonthByDateList();
            thisInstance.hideScheduledSpecificDate();
            thisInstance.hideScheduledAnually();

            if (value == '1') {	//hourly
                thisInstance.hideScheduledTime();
            } else if (value == '3') {	//weekly
                thisInstance.showScheduledWeekList();
            } else if (value == '4') {	//specific date
                thisInstance.showScheduledSpecificDate();
            } else if (value == '5') {	//monthly by day
                thisInstance.showScheduledMonthByDateList();
            } else if (value == '7') {
                thisInstance.showScheduledAnually();
            }
        });
    },

    hideScheduledTime: function () {
        jQuery('#scheduledTime').addClass('hide');
    },

    showScheduledTime: function () {
        jQuery('#scheduledTime').removeClass('hide');
    },

    hideScheduledWeekList: function () {
        jQuery('#scheduledWeekDay').addClass('hide');
    },

    showScheduledWeekList: function () {
        jQuery('#scheduledWeekDay').removeClass('hide');
    },

    hideScheduledMonthByDateList: function () {
        jQuery('#scheduleMonthByDates').addClass('hide');
    },

    showScheduledMonthByDateList: function () {
        jQuery('#scheduleMonthByDates').removeClass('hide');
    },

    hideScheduledSpecificDate: function () {
        jQuery('#scheduleByDate').addClass('hide');
    },

    showScheduledSpecificDate: function () {
        jQuery('#scheduleByDate').removeClass('hide');
    },

    hideScheduledAnually: function () {
        jQuery('#scheduleAnually').addClass('hide');
    },

    showScheduledAnually: function () {
        jQuery('#scheduleAnually').removeClass('hide');
    },

    registerEventForChangeWorkflowState: function () {
        var editViewContainer = this.getEditViewContainer();
        var thisInstance = this;
        jQuery(editViewContainer).on('click', ".taskStatus", function (e) {
            var currentElement = jQuery(e.currentTarget);
            var status = 'true';
            if (currentElement.val() == 'on') {
                status = 'false';
                currentElement.attr('value', 'off');
            } else {
                currentElement.attr('value', 'on');
            }
            if (currentElement.data('statusurl')) {
                var url = currentElement.data('statusurl') + "&status=" + status;
                app.helper.showProgress();
                app.request.post({url: url}).then(function (error, data) {
                    app.helper.hideProgress();
                    if (data) {
                        app.helper.showSuccessNotification({message: app.vtranslate('JS_TASK_STATUS_CHANGED')});
                        thisInstance.getTaskList();
                    }
                });
            } else {
                var parent = currentElement.closest('.listViewEntries');
                var taskElement = parent.find('.taskData');
                var taskData = JSON.parse(taskElement.val());
                taskData.active = status;
                taskElement.val(JSON.stringify(taskData));
                app.helper.showSuccessNotification({message: app.vtranslate('JS_TASK_STATUS_CHANGED')});
            }
        });
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
        this.registerEventForShowModuleFilterCondition();
        this.registerFormSubmitEvent();
        this.registerEnableFilterOption();
        this.registerEventForScheduledWorkflow();
        this.registerEventForChangeInScheduledType();
        this.registerEventForChangeWorkflowState();
        vtUtils.registerNumberFormating($(document));
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