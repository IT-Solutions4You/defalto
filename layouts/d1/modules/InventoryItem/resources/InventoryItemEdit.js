/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** @var InventoryItem_InventoryItemEdit_Js */
Vtiger.Class('InventoryItem_InventoryItemEdit_Js', {}, {
    formElement: false,

    mandatoryFieldMapping: {
        'Accounts': {
            'region_id': 'region_id',
            'currency_id': 'currency_id',
            'pricebookid': 'pricebookid',
        },
    },

    addressFieldsMapping: {
        'Contacts': {
            'bill_street': 'mailingstreet',
            'ship_street': 'otherstreet',
            'bill_pobox': 'mailingpobox',
            'ship_pobox': 'otherpobox',
            'bill_city': 'mailingcity',
            'ship_city': 'othercity',
            'bill_state': 'mailingstate',
            'ship_state': 'otherstate',
            'bill_code': 'mailingzip',
            'ship_code': 'otherzip',
            'bill_country_id': 'mailingcountry_id',
            'ship_country_id': 'othercountry_id'
        },

        'Accounts': {
            'bill_street': 'bill_street',
            'ship_street': 'ship_street',
            'bill_pobox': 'bill_pobox',
            'ship_pobox': 'ship_pobox',
            'bill_city': 'bill_city',
            'ship_city': 'ship_city',
            'bill_state': 'bill_state',
            'ship_state': 'ship_state',
            'bill_code': 'bill_code',
            'ship_code': 'ship_code',
            'bill_country_id': 'bill_country_id',
            'ship_country_id': 'ship_country_id',
        },

        'Vendors': {
            'bill_street': 'street',
            'ship_street': 'street',
            'bill_pobox': 'pobox',
            'ship_pobox': 'pobox',
            'bill_city': 'city',
            'ship_city': 'city',
            'bill_state': 'state',
            'ship_state': 'state',
            'bill_code': 'postalcode',
            'ship_code': 'postalcode',
            'bill_country_id': 'country_id',
            'ship_country_id': 'country_id'
        },
    },

    accountsReferenceField: false,
    contactsReferenceField: false,

    init: function () {
        this.initializeVariables();
        this.registerEvents();
        this.registerBasicEvents(this.getForm());
    },

    initializeVariables: function () {
        const form = this.getForm();
        this.accountsReferenceField = form.find('[name="account_id"]');
        this.contactsReferenceField = form.find('[name="contact_id"]');
    },

    getForm: function () {
        if (this.formElement === false) {
            this.formElement = jQuery('#EditView');
        }
        return this.formElement;
    },

    /**
     * Function which will copy the address details
     */
    copyAddressDetails: function (data, container, addressMap) {
        const self = this;
        const sourceModule = data.source_module;
        let noAddress = true;
        let errorMsg;

        this.getRecordDetails(data).then(
            function (data) {
                const response = data;

                if (typeof addressMap != "undefined") {
                    const result = response.data;

                    for (let key in addressMap) {
                        if (result[addressMap[key]] != "") {
                            noAddress = false;
                            break;
                        }
                    }

                    if (noAddress) {
                        if (sourceModule === "Accounts") {
                            errorMsg = 'JS_SELECTED_ACCOUNT_DOES_NOT_HAVE_AN_ADDRESS';
                        } else if (sourceModule === "Contacts") {
                            errorMsg = 'JS_SELECTED_CONTACT_DOES_NOT_HAVE_AN_ADDRESS';
                        } else if (sourceModule === "Leads") {
                            errorMsg = 'JS_SELECTED_LEAD_DOES_NOT_HAVE_AN_ADDRESS';
                        }

                        app.helper.showErrorNotification({'message': app.vtranslate(errorMsg)});
                    } else {
                        self.mapAddressDetails(addressMap, result, container);
                    }
                } else {
                    self.mapAddressDetails(self.addressFieldsMapping[sourceModule], response.data, container);

                    if (sourceModule === "Accounts") {
                        container.find('.accountAddress').attr('checked', 'checked');
                    } else if (sourceModule === "Contacts") {
                        container.find('.contactAddress').attr('checked', 'checked');
                    }
                }
            },
            function (error, err) {
            });
    },

    /**
     * Function which will copy the address details of the selected record
     */
    mapAddressDetails: function (addressDetails, result, container) {
        for (let key in addressDetails) {
            container.find('[name="' + key + '"]').val(result[addressDetails[key]]);
            container.find('[name="' + key + '"]').trigger('change');
        }
    },

    /**
     * Reference Fields Selection Event Handler
     */
    referenceSelectionEventHandler: function (data, container) {
        const self = this;

        if (data.selectedName) {
            const message = app.vtranslate('OVERWRITE_EXISTING_MSG1') + app.vtranslate('SINGLE_' + data.source_module) + ' (' + data.selectedName + ') ' + app.vtranslate('OVERWRITE_EXISTING_MSG2');
            app.helper.showConfirmationBox({'message': message}).then(
                function (e) {
                    self.copyAddressDetails(data, container);
                },
                function (error, err) {
                });
            self.copyMandatoryDetails(data, container);
        }
    },

    registerReferenceSelectionEvent: function (container) {
        const self = this;

        this.accountsReferenceField.on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
            self.referenceSelectionEventHandler(data, container);
        });
    },

    /**
     * Function which will give you all details of the selected record
     * @params - an Array of values like {'record' : recordId, 'source_module' : searchModule, 'selectedName' : selectedRecordName}
     */
    getRecordDetails: function (params) {
        const aDeferred = jQuery.Deferred();
        const url = "index.php?module=" + app.getModuleName() + "&action=GetData&record=" + params.record + "&source_module=" + params.source_module;
        app.request.get({'url': url}).then(
            function (error, data) {
                if (error == null) {
                    aDeferred.resolve(data);
                //} else {
                    //aDeferred.reject(data.message);
                }
            },
            function (error) {
                aDeferred.reject();
            }
        );

        return aDeferred.promise();
    },

    copyMandatoryDetails: function (data, container) {
        const self = this;
        const sourceModule = data.source_module;

        this.getRecordDetails(data).then(
            function (response) {
                self.mapAddressDetails(self.mandatoryFieldMapping[sourceModule], response.data, container);
            },
            function (error, err) {
            }
        );
    },

    registerBasicEvents: function (container) {
        this.registerReferenceSelectionEvent(container);
    },

});

let inventoryItemEdit_Instance;
document.addEventListener('DOMContentLoaded', function () {
    inventoryItemEdit_Instance = new InventoryItem_InventoryItemEdit_Js();
});