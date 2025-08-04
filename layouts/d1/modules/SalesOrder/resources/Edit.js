/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** @var SalesOrder_Edit_Js */
Vtiger_Edit_Js("SalesOrder_Edit_Js", {}, {

    /**
     * Function to get popup params
     */
    getPopUpParams: function (container) {
        const params = this._super(container);
        let sourceFieldElement = jQuery('input[class="sourceField"]', container);

        if (!sourceFieldElement.length) {
            sourceFieldElement = jQuery('input.sourceField', container);
        }

        if (sourceFieldElement.attr('name') === 'contact_id' || sourceFieldElement.attr('name') === 'potential_id') {
            const form = this.getForm();
            let parentIdElement = form.find('[name="account_id"]');

            if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
                const closestContainer = parentIdElement.closest('td');
                params['related_parent_id'] = parentIdElement.val();
                params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
            } else if (sourceFieldElement.attr('name') === 'potential_id') {
                parentIdElement = form.find('[name="contact_id"]');
                if (parentIdElement.length > 0 && parentIdElement.val().length > 0) {
                    closestContainer = parentIdElement.closest('td');
                    params['related_parent_id'] = parentIdElement.val();
                    params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
                }
            }
        }
        return params;
    },

    /**
     * Function to register event for enabling recurrence
     * When recurrence is enabled some of the fields need
     * to be check for mandatory validation
     */
    registerEventForEnablingRecurrence: function () {
        const thisInstance = this;
        const form = this.getForm();
        const enableRecurrenceField = form.find('[name="enable_recurring"]');
        const fieldNamesForValidation = ['recurring_frequency', 'start_period', 'payment_duration'];
        const selectors = [];

        for (let index in fieldNamesForValidation) {
            selectors.push('[name="' + fieldNamesForValidation[index] + '"]');
        }

        const selectorString = selectors.join(',');
        const validationToggleFields = form.find(selectorString);
        enableRecurrenceField.on('change', function (e) {
            const element = jQuery(e.currentTarget);
            let addValidation;

            if (element.is(':checked')) {
                addValidation = true;
            } else {
                addValidation = false;
            }

            //If validation needs to be added for new elements, then we need to detach and attach validation to the form
            if (addValidation) {
                thisInstance.AddOrRemoveRequiredValidation(validationToggleFields, true);
            } else {
                thisInstance.AddOrRemoveRequiredValidation(validationToggleFields, false);
            }
        });
        if (!enableRecurrenceField.is(":checked")) {
            thisInstance.AddOrRemoveRequiredValidation(validationToggleFields, false);
        } else if (enableRecurrenceField.is(":checked")) {
            thisInstance.AddOrRemoveRequiredValidation(validationToggleFields, true);
        }
    },

    AddOrRemoveRequiredValidation: function (dependentFieldsForValidation, addValidation) {
        jQuery(dependentFieldsForValidation).each(function (key, value) {
            const relatedField = jQuery(value);

            if (addValidation) {
                relatedField.removeClass('ignore-validation').data('rule-required', true);

                if (relatedField.is("select")) {
                    relatedField.attr('disabled', false);
                } else {
                    relatedField.removeAttr('disabled');
                }
            } else if (!addValidation) {
                relatedField.addClass('ignore-validation').removeAttr('data-rule-required');

                if (relatedField.is("select")) {
                    relatedField.attr('disabled', true).trigger("change");
                    const select2Element = app.helper.getSelect2FromSelect(relatedField);
                    select2Element.trigger('Vtiger.Validation.Hide.Messsage');
                    select2Element.find('a').removeClass('input-error');
                } else {
                    relatedField.attr('disabled', 'disabled').trigger('Vtiger.Validation.Hide.Messsage').removeClass('input-error');
                }
            }
        });
    },

    /**
     * Function to search module names
     */
    searchModuleNames: function (params) {
        const aDeferred = jQuery.Deferred();

        if (typeof params.module == 'undefined') {
            params.module = app.getModuleName();
        }

        if (typeof params.action == 'undefined') {
            params.action = 'BasicAjax';
        }

        if (typeof params.base_record == 'undefined') {
            const record = jQuery('[name="record"]');
            const recordId = app.getRecordId();

            if (record.length) {
                params.base_record = record.val();
            } else if (recordId) {
                params.base_record = recordId;
            } else if (app.view() === 'List') {
                const editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');

                if (editRecordId) {
                    params.base_record = editRecordId;
                }
            }
        }

        // Added for overlay edit as the module is different
        if (params.search_module === 'Products' || params.search_module === 'Services') {
            params.module = 'SalesOrder';
        }

        app.request.get({'data': params}).then(
            function (error, data) {
                if (error == null) {
                    aDeferred.resolve(data);
                }
            },
            function (error) {
                aDeferred.reject();
            }
        );

        return aDeferred.promise();
    },

    /**
     * Function which will register event for Reference Fields Selection
     */
    registerReferenceSelectionEvent: function (container) {
        this._super(container);
        const self = this;

        jQuery('input[name="account_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
            self.referenceSelectionEventHandler(data, container);
        });
    },
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerEventForEnablingRecurrence();
    },

});