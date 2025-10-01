/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** @var Quotes_Edit_Js */
Vtiger_Edit_Js("Quotes_Edit_Js", {}, {
    accountsReferenceField: false,
    contactsReferenceField: false,

    init: function () {
        this._super();
        this.initializeVariables();
    },

    initializeVariables: function () {
        this._super();
        const form = this.getForm();
        this.accountsReferenceField = form.find('[name="account_id"]');
        this.contactsReferenceField = form.find('[name="contact_id"]');
    },

    /**
     * Function to get popup params
     */
    getPopUpParams: function (container) {
        const params = this._super(container);
        let sourceFieldElement = jQuery('input[class="sourceField"]', container);
        const referenceModule = jQuery('input[name=popupReferenceModule]', container).val();

        if (!sourceFieldElement.length) {
            sourceFieldElement = jQuery('input.sourceField', container);
        }

        if ((sourceFieldElement.attr('name') === 'contact_id' || sourceFieldElement.attr('name') === 'potential_id') && referenceModule !== 'Leads') {
            const form = this.getForm();
            let parentIdElement = form.find('[name="account_id"]');

            if (parentIdElement.length > 0 && parentIdElement.val() > 0) {
                const closestContainer = parentIdElement.closest('td');
                params['related_parent_id'] = parentIdElement.val();
                params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
            } else if (sourceFieldElement.attr('name') === 'potential_id') {
                parentIdElement = form.find('[name="contact_id"]');
                const relatedParentModule = parentIdElement.closest('td').find('input[name="popupReferenceModule"]').val();

                if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && relatedParentModule !== 'Leads') {
                    let closestContainer = parentIdElement.closest('td');
                    params['related_parent_id'] = parentIdElement.val();
                    params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
                }
            }
        }
        return params;
    },

    /**
     * Function which will register event for Reference Fields Selection
     */
    registerReferenceSelectionEvent: function (container) {
        /*this._super(container);
        const self = this;

        this.accountsReferenceField.on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
            self.referenceSelectionEventHandler(data, container);
        });*/
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

        if (params.search_module === 'Contacts' || params.search_module === 'Potentials') {
            if (this.accountsReferenceField.length > 0 && this.accountsReferenceField.val().length > 0) {
                let closestContainer = this.accountsReferenceField.closest('td');
                params.parent_id = this.accountsReferenceField.val();
                params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
            } else if (params.search_module === 'Potentials') {

                if (this.contactsReferenceField.length > 0 && this.contactsReferenceField.val().length > 0) {
                    closestContainer = this.contactsReferenceField.closest('td');
                    params.parent_id = this.contactsReferenceField.val();
                    params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
                }
            }
        }

        // Added for overlay edit as the module is different
        if (params.search_module === 'Products' || params.search_module === 'Services') {
            params.module = 'Quotes';
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

    registerQuickCreateEvents(container) {
        if (!container.is('#QuickCreate')) {
            return;
        }

        let inventoryItemEdit = InventoryItem_InventoryItemEdit_Js.getInstance();

        inventoryItemEdit.setForm(container)
        inventoryItemEdit.init();
    },
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerReferenceSelectionEvent(this.getForm());
        this.registerQuickCreateEvents(container);
    },
});