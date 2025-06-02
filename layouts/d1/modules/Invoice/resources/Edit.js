/**
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (c) vtiger.
 * Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
 * All Rights Reserved.
 */

/** @var Invoice_Edit_Js */
Vtiger_Edit_Js("Invoice_Edit_Js", {}, {

    accountReferenceField: false,

    initializeVariables: function () {
        this._super();
        const form = this.getForm();
        this.accountReferenceField = form.find('[name="account_id"]');
    },

    /**
     * Function which will register event for Reference Fields Selection
     */
    registerReferenceSelectionEvent: function (container) {
        this._super(container);
        const self = this;

        this.accountReferenceField.on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
            self.referenceSelectionEventHandler(data, container);
        });
    },

    /**
     * Function to get popup params
     */
    getPopUpParams: function (container) {
        const params = this._super(container);
        let sourceFieldElement = jQuery('input[class="sourceField"]', container);

        if (!sourceFieldElement.length) {
            sourceFieldElement = jQuery('input.sourceField', container);
        }

        if (sourceFieldElement.attr('name') === 'contact_id') {
            const form = this.getForm();
            const parentIdElement = form.find('[name="account_id"]');

            if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
                const closestContainer = parentIdElement.closest('td');
                params['related_parent_id'] = parentIdElement.val();
                params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
            }
        }

        return params;
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

        if (params.search_module === 'Contacts') {
            if (this.accountReferenceField.length > 0 && this.accountReferenceField.val().length > 0) {
                const closestContainer = this.accountReferenceField.closest('td');
                params.parent_id = this.accountReferenceField.val();
                params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
            }
        }

        // Added for overlay edit as the module is different
        if (params.search_module === 'Products' || params.search_module === 'Services') {
            params.module = 'Invoice';
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

    registerBasicEvents: function (container) {
        this._super(container);
        this.registerForTogglingBillingandShippingAddress();
        this.registerEventForCopyAddress();
    },
});