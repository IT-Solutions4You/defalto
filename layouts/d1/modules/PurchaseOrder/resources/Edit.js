/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/** @var PurchaseOrder_Edit_Js */
Vtiger_Edit_Js("PurchaseOrder_Edit_Js", {
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
            'region_id': 'region_id',
            'currency_id': 'currency_id',
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
        'Leads': {
            'bill_street': 'lane',
            'ship_street': 'lane',
            'bill_pobox': 'pobox',
            'ship_pobox': 'pobox',
            'bill_city': 'city',
            'ship_city': 'city',
            'bill_state': 'state',
            'ship_state': 'state',
            'bill_code': 'code',
            'ship_code': 'code',
            'bill_country_id': 'country_id',
            'ship_country_id': 'country_id'
        }
    },

    //Address field mapping between modules specific for billing and shipping
    addressFieldsMappingBetweenModules: {
        'AccountsBillMap': {
            'bill_street': 'bill_street',
            'bill_pobox': 'bill_pobox',
            'bill_city': 'bill_city',
            'bill_state': 'bill_state',
            'bill_code': 'bill_code',
            'bill_country_id': 'bill_country_id'
        },
        'AccountsShipMap': {
            'ship_street': 'ship_street',
            'ship_pobox': 'ship_pobox',
            'ship_city': 'ship_city',
            'ship_state': 'ship_state',
            'ship_code': 'ship_code',
            'ship_country_id': 'ship_country_id'
        },
        'ContactsBillMap': {
            'bill_street': 'mailingstreet',
            'bill_pobox': 'mailingpobox',
            'bill_city': 'mailingcity',
            'bill_state': 'mailingstate',
            'bill_code': 'mailingzip',
            'bill_country_id': 'mailingcountry_id'
        },
        'ContactsShipMap': {
            'ship_street': 'otherstreet',
            'ship_pobox': 'otherpobox',
            'ship_city': 'othercity',
            'ship_state': 'otherstate',
            'ship_code': 'otherzip',
            'ship_country_id': 'othercountry_id'
        },
        'LeadsBillMap': {
            'bill_street': 'lane',
            'bill_pobox': 'pobox',
            'bill_city': 'city',
            'bill_state': 'state',
            'bill_code': 'code',
            'bill_country_id': 'country_id'
        },
        'LeadsShipMap': {
            'ship_street': 'lane',
            'ship_pobox': 'pobox',
            'ship_city': 'city',
            'ship_state': 'state',
            'ship_code': 'code',
            'ship_country_id': 'country_id'
        }

    },

    //Address field mapping within module
    addressFieldsMappingInModule: {
        'bill_street': 'ship_street',
        'bill_pobox': 'ship_pobox',
        'bill_city': 'ship_city',
        'bill_state': 'ship_state',
        'bill_code': 'ship_code',
        'bill_country_id': 'ship_country_id'
    },
}, {


    // To change the drop down when user select to copy address from reference field
    billingAddress: false,
    shippingAddress: false,

    billingShippingFields: {
        'bill': {
            'street': '',
            'pobox': '',
            'city': '',
            'state': '',
            'code': '',
            'country': ''
        },
        'ship': {
            'street': '',
            'pobox': '',
            'city': '',
            'state': '',
            'code': '',
            'country': ''
        }

    },
    companyDetails: false,

    addressDetails: {
        'billaccountDetails': false,
        'shipaccountDetails': false,
        'billvendorDetails': false,
        'shipvendorDetails': false,
        'billcompanyDetails': false,
        'shipcompanyDetails': false,
        'billcontactDetails': false
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
            const parentIdElement = form.find('[name="vendor_id"]');

            if (parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
                const closestContainer = parentIdElement.closest('td');
                params['related_parent_id'] = parentIdElement.val();
                params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
            }
        }
        return params;
    },

    copyAddressFields: function (addressType, targetType) {
        const form = this.getForm();
        const company = this.addressDetails[targetType];
        const container = jQuery('.editViewContents', form);
        const fields = this.billingShippingFields[addressType];

        for (let key in fields) {
            container.find('[name="' + addressType + '_' + key + '"]').val(company[key]);
            container.find('[name="' + addressType + '_' + key + '"]').trigger('change');
        }
    },

    registerSwapAddress: function (selectedElement) {
        const thisInstance = this;

        if (selectedElement === 'bill' || selectedElement === 'ship') {
            let swapMode;

            if (selectedElement === "ship") {
                swapMode = "false";
            } else if (selectedElement === "bill") {
                swapMode = "true";
            }

            thisInstance.copyAddress(swapMode);

            return false;
        }
    },

    registerSelectAddress: function () {
        const self = this;
        const editViewForm = this.getForm();

        jQuery('#ShippingAddress,#BillingAddress', editViewForm).change(function (e) {
            const selectedElement = jQuery(e.currentTarget).val();
            const addressType = jQuery(e.currentTarget).data('target');

            if (selectedElement === 'bill' || selectedElement === 'ship') {
                self.registerSwapAddress(selectedElement);
                return;
            }

            const moduleName = app.getModuleName();
            let mode, moduleIdElement, recordId;

            if (selectedElement === 'vendor' || selectedElement === 'contact') {
                moduleIdElement = selectedElement + '_id';
                mode = 'getAddressDetails';
            } else if (selectedElement === 'account') {
                moduleIdElement = 'accountid';
                mode = 'getAddressDetails';
            } else if (selectedElement === 'company') {
                mode = 'getCompanyDetails';
            }

            if (moduleIdElement) {
                recordId = jQuery('input[name=' + moduleIdElement + ']').val();
            }

            if ((selectedElement === 'account' || selectedElement === 'vendor' || selectedElement === 'contact') && (recordId === '' || recordId == undefined)) {
                app.helper.showErrorNotification({'message': app.vtranslate('JS_' + selectedElement.toUpperCase() + '_NOT_FOUND_MESSAGE')});

                return;
            }

            const url = {
                'mode': mode,
                'action': 'CompanyDetails',
                'recordId': recordId,
                'type': addressType,
                'module': moduleName
            };

            if (!self.addressDetails[addressType + selectedElement + 'Details']) {
                app.request.get({'data': url}).then(function (error, data) {
                    if (error == null) {
                        const response = data;
                        self.addressDetails[addressType + selectedElement + 'Details'] = response;
                        self.copyAddressFields(addressType, addressType + selectedElement + 'Details');
                    }
                });
            } else {
                self.copyAddressFields(addressType, addressType + selectedElement + 'Details');
            }
        });
    },

    registerReferenceSelectionEvent: function (container) {
        this._super(container);
        const self = this;

        jQuery('input[name="vendor_id"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
            self.referenceSelectionEventHandler(data, container);
        });

        jQuery('input[name="accountid"]', container).on(Vtiger_Edit_Js.referenceSelectionEvent, function (e, data) {
            self.referenceSelectionEventHandler(data, container);
        });
    },

    /**
     * Function to register event for copying addresses
     */
    registerEventForCopyAddress: function () {
        const self = this;
        jQuery('[name="copyAddressFromRight"],[name="copyAddressFromLeft"]').change(function () {
            const element = jQuery(this);
            const elementClass = element.attr('class');
            const targetCopyAddress = element.data('copyAddress');
            let objectToMapAddress;

            if (elementClass === "accountAddress") {
                const recordRelativeAccountId = jQuery('[name="account_id"]').val();

                if (typeof recordRelativeAccountId == 'undefined') {
                    app.helper.showErrorNotification({'message': app.vtranslate('JS_RELATED_ACCOUNT_IS_NOT_AVAILABLE')});
                    return;
                }

                if (recordRelativeAccountId == "" || recordRelativeAccountId == "0") {
                    app.helper.showErrorNotification({'message': app.vtranslate('JS_PLEASE_SELECT_AN_ACCOUNT_TO_COPY_ADDRESS')});
                } else {
                    const recordRelativeAccountName = jQuery('#account_id_display').val();
                    const data = {
                        'record': recordRelativeAccountId,
                        'selectedName': recordRelativeAccountName,
                        'source_module': "Accounts"
                    };

                    if (targetCopyAddress === "billing") {
                        objectToMapAddress = self.addressFieldsMappingBetweenModules['AccountsBillMap'];
                    } else if (targetCopyAddress === "shipping") {
                        objectToMapAddress = self.addressFieldsMappingBetweenModules['AccountsShipMap'];
                    }

                    inventoryItemEdit_Instance.copyAddressDetails(data, element.closest('table'), objectToMapAddress);
                    element.attr('checked', 'checked');
                }
            } else if (elementClass === "contactAddress") {
                const recordRelativeContactId = jQuery('[name="contact_id"]').val();
                if (typeof recordRelativeContactId == 'undefined') {
                    app.helper.showErrorNotification({'message': app.vtranslate('JS_RELATED_CONTACT_IS_NOT_AVAILABLE')});
                    return;
                }
                if (recordRelativeContactId == "" || recordRelativeContactId == "0") {
                    app.helper.showErrorNotification({'message': app.vtranslate('JS_PLEASE_SELECT_AN_RELATED_TO_COPY_ADDRESS')});
                } else {
                    const recordRelativeContactName = jQuery('#contact_id_display').val();
                    const editViewLabel = jQuery('#contact_id_display').closest('td');
                    const editViewSelection = jQuery(editViewLabel).find('input[name="popupReferenceModule"]').val();
                    const data = {
                        'record': recordRelativeContactId,
                        'selectedName': recordRelativeContactName,
                        source_module: editViewSelection
                    };

                    if (targetCopyAddress === "billing") {
                        objectToMapAddress = self.addressFieldsMappingBetweenModules[editViewSelection + 'BillMap'];
                    } else if (targetCopyAddress === "shipping") {
                        objectToMapAddress = self.addressFieldsMappingBetweenModules[editViewSelection + 'ShipMap'];
                    }
                    inventoryItemEdit_Instance.copyAddressDetails(data, element.closest('table'), objectToMapAddress);
                    element.attr('checked', 'checked');
                }
            } else if (elementClass === "shippingAddress") {
                const target = element.data('target');
                let swapMode = "false";

                if (target === "shipping") {
                    swapMode = "true";
                }

                self.copyAddress(swapMode);
            } else if (elementClass === "billingAddress") {
                const target = element.data('target');
                let swapMode = "true";

                if (target === "billing") {
                    swapMode = "false";
                }

                self.copyAddress(swapMode);
            }
        });

        jQuery('[name="copyAddress"]').on('click', function (e) {
            const element = jQuery(e.currentTarget);
            const target = element.data('target');
            let swapMode;

            if (target === "billing") {
                swapMode = "false";
            } else if (target === "shipping") {
                swapMode = "true";
            }

            self.copyAddress(swapMode);
        });
    },

    /**
     * Function to copy address between fields
     * @param strings which accepts value as either odd or even
     */
    copyAddress: function (swapMode) {
        let self = this,
            formElement = this.getForm(),
            addressMapping = this.addressFieldsMappingInModule,
            fromElement,
            toElement;

        if (swapMode == "false") {
            for (let key in addressMapping) {
                fromElement = formElement.find('[name="' + key + '"]');
                toElement = formElement.find('[name="' + addressMapping[key] + '"]');
                toElement.val(fromElement.val());
                toElement.trigger('change');
            }
        } else if (swapMode) {
            let swappedArray = self.swapObject(addressMapping);

            for (let key in swappedArray) {
                fromElement = formElement.find('[name="' + key + '"]');
                toElement = formElement.find('[name="' + swappedArray[key] + '"]');
                toElement.val(fromElement.val());
                toElement.trigger('change');
            }
        }
    },

    /**
     * Function to swap array
     * @param Array that need to be swapped
     */
    swapObject: function (objectToSwap) {
        const swappedArray = {};
        let newKey, newValue;

        for (let key in objectToSwap) {
            newKey = objectToSwap[key];
            newValue = key;
            swappedArray[newKey] = newValue;
        }

        return swappedArray;
    },

    registerBasicEvents: function (container) {
        this._super(container);
        this.registerEventForCopyAddress();
        this.registerSelectAddress();
    }
});