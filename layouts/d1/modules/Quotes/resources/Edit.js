/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger_Edit_Js("Quotes_Edit_Js",{},{

	addressFieldsMapping : {
		'Contacts' : {
			'bill_street' :  'mailingstreet',
			'ship_street' : 'otherstreet',
			'bill_pobox' : 'mailingpobox',
			'ship_pobox' : 'otherpobox',
			'bill_city' : 'mailingcity',
			'ship_city'  : 'othercity',
			'bill_state' : 'mailingstate',
			'ship_state' : 'otherstate',
			'bill_code' : 'mailingzip',
			'ship_code' : 'otherzip',
			'bill_country_id' : 'mailingcountry_id',
			'ship_country_id' : 'othercountry_id'
		} ,

		'Accounts' : {
			'bill_street' :  'bill_street',
			'ship_street' : 'ship_street',
			'bill_pobox' : 'bill_pobox',
			'ship_pobox' : 'ship_pobox',
			'bill_city' : 'bill_city',
			'ship_city'  : 'ship_city',
			'bill_state' : 'bill_state',
			'ship_state' : 'ship_state',
			'bill_code' : 'bill_code',
			'ship_code' : 'ship_code',
			'bill_country_id' : 'bill_country_id',
			'ship_country_id' : 'ship_country_id',
			'region_id' : 'region_id',
			'currency_id' : 'currency_id',
		},

		'Vendors' : {
			'bill_street' : 'street',
			'ship_street' : 'street',
			'bill_pobox' : 'pobox',
			'ship_pobox' : 'pobox',
			'bill_city' : 'city',
			'ship_city'  : 'city',
			'bill_state' : 'state',
			'ship_state' : 'state',
			'bill_code' : 'postalcode',
			'ship_code' : 'postalcode',
			'bill_country_id' : 'country_id',
			'ship_country_id' : 'country_id'
		},
		'Leads' : {
			'bill_street' :  'lane',
			'ship_street' : 'lane',
			'bill_pobox' : 'pobox',
			'ship_pobox' : 'pobox',
			'bill_city' : 'city',
			'ship_city'  : 'city',
			'bill_state' : 'state',
			'ship_state' : 'state',
			'bill_code' : 'code',
			'ship_code' : 'code',
			'bill_country_id' : 'country_id',
			'ship_country_id' : 'country_id'
		}
	},

	//Address field mapping between modules specific for billing and shipping
	addressFieldsMappingBetweenModules:{
		'AccountsBillMap' : {
			'bill_street' :  'bill_street',
			'bill_pobox' : 'bill_pobox',
			'bill_city' : 'bill_city',
			'bill_state' : 'bill_state',
			'bill_code' : 'bill_code',
			'bill_country_id' : 'bill_country_id'
		},
		'AccountsShipMap' : {
			'ship_street' : 'ship_street',
			'ship_pobox' : 'ship_pobox',
			'ship_city'  : 'ship_city',
			'ship_state' : 'ship_state',
			'ship_code' : 'ship_code',
			'ship_country_id' : 'ship_country_id'
		},
		'ContactsBillMap' : {
			'bill_street' :  'mailingstreet',
			'bill_pobox' : 'mailingpobox',
			'bill_city' : 'mailingcity',
			'bill_state' : 'mailingstate',
			'bill_code' : 'mailingzip',
			'bill_country_id' : 'mailingcountry_id'
		},
		'ContactsShipMap' : {
			'ship_street' : 'otherstreet',
			'ship_pobox' : 'otherpobox',
			'ship_city'  : 'othercity',
			'ship_state' : 'otherstate',
			'ship_code' : 'otherzip',
			'ship_country_id' : 'othercountry_id'
		},
		'LeadsBillMap' : {
			'bill_street' :  'lane',
			'bill_pobox' : 'pobox',
			'bill_city' : 'city',
			'bill_state' : 'state',
			'bill_code' : 'code',
			'bill_country_id' : 'country_id'
		},
		'LeadsShipMap' : {
			'ship_street' : 'lane',
			'ship_pobox' : 'pobox',
			'ship_city'  : 'city',
			'ship_state' : 'state',
			'ship_code' : 'code',
			'ship_country_id' : 'country_id'
		}

	},

	//Address field mapping within module
	addressFieldsMappingInModule : {
		'bill_street':'ship_street',
		'bill_pobox':'ship_pobox',
		'bill_city'	:'ship_city',
		'bill_state':'ship_state',
		'bill_code'	:'ship_code',
		'bill_country_id':'ship_country_id'
	},

    accountsReferenceField : false,
    contactsReferenceField : false,

    initializeVariables : function() {
      this._super();
      var form = this.getForm();
      this.accountsReferenceField = form.find('[name="account_id"]');
      this.contactsReferenceField = form.find('[name="contact_id"]');
    },

	init : function() {
		this._super();
		this.initializeVariables();
	},

    /**
	 * Function to get popup params
	 */
	getPopUpParams : function(container) {
		var params = this._super(container);
        var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		var referenceModule = jQuery('input[name=popupReferenceModule]', container).val();
		if(!sourceFieldElement.length) {
			sourceFieldElement = jQuery('input.sourceField',container);
		}

		if((sourceFieldElement.attr('name') == 'contact_id' || sourceFieldElement.attr('name') == 'potential_id') && referenceModule != 'Leads') {
			var form = this.getForm();
			var parentIdElement  = form.find('[name="account_id"]');
			if(parentIdElement.length > 0 && parentIdElement.val().length > 0 && parentIdElement.val() != 0) {
				var closestContainer = parentIdElement.closest('td');
				params['related_parent_id'] = parentIdElement.val();
				params['related_parent_module'] = closestContainer.find('[name="popupReferenceModule"]').val();
			} else if(sourceFieldElement.attr('name') == 'potential_id') {
				parentIdElement  = form.find('[name="contact_id"]');
				var relatedParentModule = parentIdElement.closest('td').find('input[name="popupReferenceModule"]').val()
				if(parentIdElement.length > 0 && parentIdElement.val().length > 0 && relatedParentModule != 'Leads') {
					closestContainer = parentIdElement.closest('td');
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
	registerReferenceSelectionEvent : function(container) {
		this._super(container);
		const self = this;

		this.accountsReferenceField.on(Vtiger_Edit_Js.referenceSelectionEvent, function(e, data){
			self.referenceSelectionEventHandler(data, container);
		});
	},

    /**
	 * Function to search module names
	 */
	searchModuleNames : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}
		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}

		if(typeof params.base_record == 'undefined') {
			var record = jQuery('[name="record"]');
			var recordId = app.getRecordId();
			if(record.length) {
				params.base_record = record.val();
			} else if(recordId) {
				params.base_record = recordId;
			} else if(app.view() == 'List') {
				var editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');
				if(editRecordId) {
					params.base_record = editRecordId;
				}
			}
		}

		if (params.search_module == 'Contacts' || params.search_module == 'Potentials') {
			var form = this.getForm();
			if(this.accountsReferenceField.length > 0 && this.accountsReferenceField.val().length > 0) {
				var closestContainer = this.accountsReferenceField.closest('td');
				params.parent_id = this.accountsReferenceField.val();
				params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
			} else if(params.search_module == 'Potentials') {

				if(this.contactsReferenceField.length > 0 && this.contactsReferenceField.val().length > 0) {
					closestContainer = this.contactsReferenceField.closest('td');
					params.parent_id = this.contactsReferenceField.val();
					params.parent_module = closestContainer.find('[name="popupReferenceModule"]').val();
				}
			}
		}

        // Added for overlay edit as the module is different
        if(params.search_module == 'Products' || params.search_module == 'Services') {
            params.module = 'Quotes';
        }

		app.request.get({'data':params}).then(
			function(error, data){
                if(error == null) {
                    aDeferred.resolve(data);
                }
			},
			function(error){
				aDeferred.reject();
			}
		);

		return aDeferred.promise();
	},

	/**
	 * Function to toggle shipping and billing address according to layout
	 */
	registerForTogglingBillingAndShippingAddress: function () {
		const billingAddressPosition = jQuery('[name="bill_street"]').closest('td').index();
		const copyAddress1Block = jQuery('[name="copyAddress1"]');
		const copyAddress2Block = jQuery('[name="copyAddress2"]');
		const copyHeader1 = jQuery('[name="copyHeader1"]');
		const copyHeader2 = jQuery('[name="copyHeader2"]');
		const copyAddress1toggleAddressLeftContainer = copyAddress1Block.find('[name="togglingAddressContainerLeft"]');
		const copyAddress1toggleAddressRightContainer = copyAddress1Block.find('[name="togglingAddressContainerRight"]');
		const copyAddress2toggleAddressLeftContainer = copyAddress2Block.find('[name="togglingAddressContainerLeft"]');
		const copyAddress2toggleAddressRightContainer = copyAddress2Block.find('[name="togglingAddressContainerRight"]');
		const headerText1 = copyHeader1.html();
		const headerText2 = copyHeader2.html();

		if (billingAddressPosition == 3) {
			if (copyAddress1toggleAddressLeftContainer.hasClass('hide')) {
				copyAddress1toggleAddressLeftContainer.removeClass('hide');
			}

			copyAddress1toggleAddressRightContainer.addClass('hide');

			if (copyAddress2toggleAddressRightContainer.hasClass('hide')) {
				copyAddress2toggleAddressRightContainer.removeClass('hide');
			}

			copyAddress2toggleAddressLeftContainer.addClass('hide');
			copyHeader1.html(headerText2);
			copyHeader2.html(headerText1);

			copyAddress1Block.find('[data-copy-address]').each(function () {
				jQuery(this).data('copyAddress', 'shipping');
			});
			copyAddress2Block.find('[data-copy-address]').each(function () {
				jQuery(this).data('copyAddress', 'billing');
			});
		}
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

					if (targetCopyAddress == "billing") {
						objectToMapAddress = self.addressFieldsMappingBetweenModules['AccountsBillMap'];
					} else if (targetCopyAddress == "shipping") {
						objectToMapAddress = self.addressFieldsMappingBetweenModules['AccountsShipMap'];
					}

					self.copyAddressDetails(data, element.closest('table'), objectToMapAddress);
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

					if (targetCopyAddress == "billing") {
						objectToMapAddress = self.addressFieldsMappingBetweenModules[editViewSelection + 'BillMap'];
					} else if (targetCopyAddress == "shipping") {
						objectToMapAddress = self.addressFieldsMappingBetweenModules[editViewSelection + 'ShipMap'];
					}

					self.copyAddressDetails(data, element.closest('table'), objectToMapAddress);
					element.attr('checked', 'checked');
				}
			} else if (elementClass === "shippingAddress") {
				const target = element.data('target');
				let swapMode;

				if (target === "shipping") {
					swapMode = "true";
				}

				self.copyAddress(swapMode);
			} else if (elementClass === "billingAddress") {
				const target = element.data('target');
				let swapMode;

				if (target === "billing") {
					swapMode = "false";
				}

				self.copyAddress(swapMode);
			}
		});

		jQuery('[name="copyAddress"]').on('click', function (e) {
			const element = jQuery(e.currentTarget);
			let swapMode;
			const target = element.data('target');

			if (target === "billing") {
				swapMode = "false";
			} else if (target === "shipping") {
				swapMode = "true";
			}

			self.copyAddress(swapMode);
		});
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
			function (error, err) {});
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
        }
    },

	registerBasicEvents: function (container) {
		this._super(container);
		this.registerForTogglingBillingAndShippingAddress();
		this.registerEventForCopyAddress();
		this.registerReferenceSelectionEvent(this.getForm());
	},
});