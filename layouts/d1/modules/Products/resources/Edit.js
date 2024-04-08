/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger_Edit_Js("Products_Edit_Js", {
	getMessageForChildProductDeletionOrInActivation: function(params) {
		var aDeferred = jQuery.Deferred();
		var message = '';
		if (params['module'] == 'Products') {
			params['action'] = 'Mass';
			params['mode'] = 'isChildProduct';

			app.request.post({data:params}).then(function(err, data) {
				var responseData = data.result;
				for (var id in responseData) {
					if (responseData[id] == true) {
						message = app.vtranslate('JS_DELETION_OR_IN_ACTIVATION_CHILD_PRODUCT_MESSAGE');
					}
				}
				aDeferred.resolve(message);
			});
		} else {
			aDeferred.resolve(message);
		}
		return aDeferred.promise();
	}
},
{
	baseCurrency: '',
	baseCurrencyName: '',
	//Container which stores the multi currency element
	multiCurrencyContainer: false,
	//Container which stores unit price
	unitPrice: false,
	/**
	 * Function to get unit price
	 */
	getUnitPrice: function() {
		if (this.unitPrice == false) {
			this.unitPrice = jQuery('input.unitPrice', this.getForm());
		}
		return this.unitPrice;
	},
	/**
	 * Function to get more currencies container
	 */
	getMoreCurrenciesContainer: function() {
		if (this.multiCurrencyContainer == false) {
			this.multiCurrencyContainer = jQuery('.multiCurrencyEditUI');
		}
		return this.multiCurrencyContainer;
	},
	/**
	 * Function to get current Element
	 */
	getCurrentElem: function(e) {
		return jQuery(e.currentTarget);
	},
	/**
	 * Function to return stripped unit price
	 */
	getDataBaseFormatUnitPrice: function () {
		let container = jQuery('.multiCurrencyEditUI:visible'),
			baseCurrencyElement = container.find('.baseCurrency').filter(':checked'),
			baseCurrencyParentElement = baseCurrencyElement.closest('tr'),
			baseCurrencyPrice = jQuery('.convertedPrice', baseCurrencyParentElement),
			unitPrice = baseCurrencyPrice.val();

		if (!unitPrice) {
			unitPrice = 0;
		}

		return unitPrice;
	},
	/**
	 * Function to get more currencies UI
	 */
	getMoreCurrenciesUI: function() {
		var aDeferred = jQuery.Deferred();
		var moduleName = this.getModuleName();
		var baseCurrency = jQuery('input[name="base_currency"]').val();
		var recordId = jQuery('input[name="record"]').val();

		var moreCurrenciesContainer = jQuery('#moreCurrenciesContainer');
		var moreCurrenciesUi;
		moreCurrenciesUi = moreCurrenciesContainer.find('.multiCurrencyEditUI');

		if (moreCurrenciesUi.length == 0) {
			var params = {
				'module': moduleName,
				'view': "MoreCurrenciesList",
				'currency': baseCurrency,
				'record': recordId
			};

			app.request.get({data: params}).then(
				function(err, data) {
					if (data) {
						moreCurrenciesContainer.html(data);
						aDeferred.resolve(data);
					}
				});
		}
		else {
			aDeferred.resolve();
		}
		return aDeferred.promise();
	},
	/*
	 * function to register events for more currencies link
	 */
	registerEventForMoreCurrencies: function () {
		let self = this;

		jQuery('#moreCurrencies').on('click', function (e) {
			app.helper.showProgress();
			self.getMoreCurrenciesUI().then(function (data) {
				app.helper.hideProgress();
				let moreCurrenciesUi = jQuery('#moreCurrenciesContainer').find('.multiCurrencyEditUI');

				if (moreCurrenciesUi.length > 0) {
					moreCurrenciesUi = moreCurrenciesUi.clone();
					let callback = function (data) {
						let form = data.find('#currencyContainer');
						form.vtValidate({
							submitHandler: function (form) {
								self.saveCurrencies();
								return false;
							}
						});
						self.baseCurrency = self.getUnitPrice().val();
						self.multiCurrencyContainer = jQuery('.multiCurrencyEditUI');
						self.calculateConversionRate();
						self.registerEventForEnableCurrency();
						self.registerEventForEnableBaseCurrency();
						self.registerEventForResetCurrency();
						self.triggerForBaseCurrencyCalc();
						vtUtils.registerReplaceCommaWithDot(form);
					};

					let moreCurrenciesContainer = jQuery('#moreCurrenciesContainer').find('.multiCurrencyEditUI'),
						contentInsideForm = moreCurrenciesUi.find('.multiCurrencyContainer').html(),
						form = '<form id="currencyContainer"></form>';

					moreCurrenciesUi.find('.multiCurrencyContainer').remove();

					jQuery(form).insertAfter(moreCurrenciesUi.find('.modal-header'));

					moreCurrenciesUi.find('form').html(contentInsideForm);
					moreCurrenciesContainer.find('input[name^=curname]').each(function (index, element) {
						let dataValue = jQuery(element).val(),
							dataId = jQuery(element).attr('id');

						moreCurrenciesUi.find('#' + dataId).val(dataValue);
					});

					app.helper.showModal(moreCurrenciesUi, {cb: callback});
				}
			});
		});
	},
	saveCurrencies: function() {
		let thisInstance = this,
			errorMessage,
			form = jQuery('#currencyContainer'),
			editViewForm = thisInstance.getForm(),
			modalContainer = jQuery('.myModal'),
			enabledBaseCurrency = modalContainer.find('.enableCurrency').filter(':checked');

		if(enabledBaseCurrency.length < 1){
			errorMessage = app.vtranslate('JS_PLEASE_SELECT_BASE_CURRENCY_FOR_PRODUCT');
			app.helper.showErrorNotification({message: errorMessage});
			form.removeData('submit');
			return;
		}

		enabledBaseCurrency.attr('checked',"checked");
		modalContainer.find('.enableCurrency').filter(":not(:checked)").removeAttr('checked');
		let selectedBaseCurrency = modalContainer.find('.baseCurrency').filter(':checked');

		if(selectedBaseCurrency.length < 1){
			errorMessage = app.vtranslate('JS_PLEASE_ENABLE_BASE_CURRENCY_FOR_PRODUCT');
			app.helper.showErrorNotification({message: errorMessage});
			form.removeData('submit');
			return;
		}

		selectedBaseCurrency.attr('checked',"checked");
		modalContainer.find('.baseCurrency').filter(":not(:checked)").removeAttr('checked');

		let parentElem = selectedBaseCurrency.closest('tr'),
			currencySymbol = jQuery('.currencySymbol', parentElem).text(),
			convertedPrice = jQuery('.convertedPrice', parentElem).val();

		thisInstance.baseCurrencyName = parentElem.data('currencyId');
		thisInstance.baseCurrency = convertedPrice;

		thisInstance.getUnitPrice().val(thisInstance.baseCurrency);
		jQuery('input[name="base_currency"]', editViewForm).val(thisInstance.baseCurrencyName);
		jQuery('.currencyUITypeSymbol', editViewForm).text(currencySymbol);

		let savedValuesOfMultiCurrency = modalContainer.find('.currencyContent').html(),
			moreCurrenciesContainer = jQuery('#moreCurrenciesContainer');

		moreCurrenciesContainer.find('.currencyContent').html(savedValuesOfMultiCurrency);
		modalContainer.find('input[name^=curname]').each(function(index, element) {
			let dataValue = jQuery(element).val(),
				dataId = jQuery(element).attr('id');

			moreCurrenciesContainer.find('.currencyContent').find('#' + dataId).val(dataValue);
		});
		app.helper.hideModal();
	},

	calculateConversionRate: function() {
		var container = jQuery('.multiCurrencyEditUI:visible');
		var baseCurrencyRow = container.find('.baseCurrency').filter(':checked').closest('tr');
		var baseCurrencyConvestationRate = baseCurrencyRow.find('.conversionRate');
		//if basecurrency has conversation rate as 1 then you dont have calculate conversation rate
		if (baseCurrencyConvestationRate.val() == "1") {
			return;
		}
		var baseCurrencyRatePrevValue = baseCurrencyConvestationRate.val();

		container.find('.conversionRate').each(function(key, domElement) {
			var element = jQuery(domElement);
			if (!element.is(baseCurrencyConvestationRate)) {
				var prevValue = element.val();
				element.val((prevValue / baseCurrencyRatePrevValue));
			}
		});
		baseCurrencyConvestationRate.val("1");
	},
	/**
	 * Function to register event for enabling currency on checkbox checked
	 */
	registerEventForEnableCurrency: function () {
		let container = this.getMoreCurrenciesContainer(),
			thisInstance = this;

		jQuery(container).on('change', '.enableCurrency', function (e) {
			let elem = thisInstance.getCurrentElem(e),
				parentRow = elem.closest('tr');

			if (elem.is(':checked')) {
				elem.prop('checked', true);
				let conversionRate = jQuery('.conversionRate', parentRow).val(),
					unitPriceFieldData = thisInstance.getUnitPrice().data(),
					unitPrice = thisInstance.getDataBaseFormatUnitPrice(),
					price = parseFloat(unitPrice) * parseFloat(conversionRate);

				price = price.toFixed(unitPriceFieldData['numberOfDecimalPlaces']);

				jQuery('input', parentRow).prop('disabled', true).removeAttr('disabled');
				jQuery('button.currencyReset', parentRow).prop('disabled', true).removeAttr('disabled');
				jQuery('input.convertedPrice', parentRow).val(price)
			} else {
				let baseCurrency = jQuery('.baseCurrency', parentRow);

				if (baseCurrency.is(':checked')) {
					let currencyName = jQuery('.currencyName', parentRow).text(),
						errorMessage = app.vtranslate('JS_BASE_CURRENCY_CHANGED_TO_DISABLE_CURRENCY') + '"' + currencyName + '"';

					app.helper.showErrorNotification({message: errorMessage});
					elem.prop('checked', true);

					return;
				}

				jQuery('input', parentRow).prop('disabled', true);
				jQuery('input.enableCurrency', parentRow).removeAttr('disabled');
				jQuery('button.currencyReset', parentRow).attr('disabled', 'disabled');
			}
		});

		return this;
	},
	/**
	 * Function to register event for enabling base currency on radio button clicked
	 */
	registerEventForEnableBaseCurrency: function() {
		var container = this.getMoreCurrenciesContainer();
		var thisInstance = this;
		jQuery(container).on('change', '.baseCurrency', function(e) {
			var elem = thisInstance.getCurrentElem(e);
			var parentElem = elem.closest('tr');
			if (elem.is(':checked')) {
				var convertedPrice = jQuery('.convertedPrice', parentElem).val();
				thisInstance.baseCurrencyName = parentElem.data('currencyId');
				thisInstance.baseCurrency = convertedPrice;

				var elementsList = jQuery('.enableCurrency', container);
				jQuery.each(elementsList, function(index, element) {
					var ele = jQuery(element);
					var parentRow = ele.closest('tr');

					if (ele.is(':checked')) {
						jQuery('button.currencyReset', parentRow).removeAttr('disabled');
					}
				});
				jQuery('button.currencyReset', parentElem).attr('disabled', 'disabled');
				thisInstance.calculateConversionRate();
			}
		});

		var baseCurrencyEle = container.find('.baseCurrency').filter(':checked');
		var parentElem = baseCurrencyEle.closest('tr');
		jQuery('button.currencyReset', parentElem).attr('disabled', 'disabled');

		return this;
	},
	/**
	 * Function to register event for reseting the currencies
	 */
	registerEventForResetCurrency: function () {
		let container = this.getMoreCurrenciesContainer(),
			self = this;

		jQuery(container).on('click', '.currencyReset', function (e) {
			let parentElem = self.getCurrentElem(e).closest('tr'),
				unitPriceFieldData = self.getUnitPrice().data(),
				unitPrice = self.getDataBaseFormatUnitPrice(),
				conversionRate = jQuery('.conversionRate', parentElem).val(),
				price = parseFloat(unitPrice) * parseFloat(conversionRate),
				userPreferredDecimalPlaces = unitPriceFieldData['numberOfDecimalPlaces'];

			price = price.toFixed(userPreferredDecimalPlaces);

			jQuery('.convertedPrice', parentElem).val(price);
		});
		return this;
	},
	/**
	 * Function to calculate base currency price value if unit
	 * present on click of more currencies
	 */
	triggerForBaseCurrencyCalc: function() {
		var multiCurrencyEditUI = this.getMoreCurrenciesContainer();
		var baseCurrency = multiCurrencyEditUI.find('.enableCurrency');
		jQuery.each(baseCurrency, function(key, val) {
			if (jQuery(val).is(':checked')) {
				var baseCurrencyRow = jQuery(val).closest('tr');
				var unitPrice = jQuery('.unitPrice');
				var isPriceChanged = unitPrice.data('isPriceChanged');
				if (isPriceChanged) {
					var changedUnitPrice = unitPrice.val();
					baseCurrencyRow.find('.convertedPrice').val(changedUnitPrice);
					baseCurrencyRow.find('.currencyReset').trigger('click');
				}
				if (parseFloat(baseCurrencyRow.find('.convertedPrice').val()) == 0) {
					baseCurrencyRow.find('.currencyReset').trigger('click');
				}
			} else {
				var baseCurrencyRow = jQuery(val).closest('tr');
				baseCurrencyRow.find('.convertedPrice').val('');
			}
		});
	},
	/**
	 * Function to register onchange event for unit price
	 */
	registerEventForUnitPrice: function() {
		var unitPrice = this.getUnitPrice();
		unitPrice.on('focusout', function() {
			var oldValue = unitPrice.data('oldValue');
			if (oldValue != unitPrice.val()) {
				unitPrice.data('isPriceChanged', true);
			}
		})
	},
	issetInActivationMessage: false,
	registerRecordPreSaveEvent: function(form) {
		var self = this;
		if (typeof form == 'undefined') {
			form = this.getForm();
		}
		app.event.on(Vtiger_Edit_Js.recordPresaveEvent, function(e, data) {
			var isActiveEle = form.find('input[name="discontinued"]');
			var recordId = jQuery('input[name="record"]').val();

			if (isActiveEle.length > 0 && recordId.length > 0 && self.issetInActivationMessage == false) {
				var selectedIds = new Array();
				selectedIds.push(recordId);

				var isActive = isActiveEle.is(':checked');
				if (isActive == false) {
					e.preventDefault();
					var params = {
						'module': self.getModuleName(),
						'selected_ids': selectedIds
					};

					Products_Edit_Js.getMessageForChildProductDeletionOrInActivation(params).then(function(message) {
						if (message != '') {
							app.helper.showConfirmationBox({'message': message}).then(
								function(data) {
									self.checkMoreCurrenciesUI(e, form);
									self.issetInActivationMessage = true;
								},
								function(error, err) {
									self.issetInActivationMessage = false;
									form.removeData('submit');
								}
								);
						} else {
							self.checkMoreCurrenciesUI(e, form);
							self.issetInActivationMessage = true;
						}
					});
				} else {
					self.checkMoreCurrenciesUI(e, form);
				}
			} else {
				self.checkMoreCurrenciesUI(e, form);
			}
		})
	},
	checkMoreCurrenciesUI: function(e, form) {
		var thisInstance = this;
		var multiCurrencyContent = jQuery('#moreCurrenciesContainer').find('.currencyContent');
		var unitPrice = thisInstance.getUnitPrice();
		if ((multiCurrencyContent.length < 1) && (unitPrice.length > 0)) {
			e.preventDefault();
			thisInstance.getMoreCurrenciesUI().then(function(data) {
				thisInstance.preSaveConfigOfForm(form);
				form.submit();
			})
		} else if (multiCurrencyContent.length > 0) {
			thisInstance.preSaveConfigOfForm(form);
		}
	},
	/**
	 * Function to handle settings before save of record
	 */
	preSaveConfigOfForm: function(form) {
		var unitPrice = this.getUnitPrice();
		if (unitPrice.length > 0) {
			var unitPriceValue = unitPrice.val();
			var baseCurrencyName = form.find('[name="base_currency"]').val();
			form.find('[name="' + baseCurrencyName + '"]').val(unitPriceValue);
			form.find('#requstedUnitPrice').attr('name', baseCurrencyName).val(unitPriceValue);
		}
	},
	registerTaxEvents: function (container) {
		container.on('change', '.taxes', function (e) {
			let element = jQuery(e.currentTarget),
				taxIdSelector = element.data('taxName');

			if (element.is(":checked")) {
				container.find('#' + taxIdSelector).removeClass('hide').addClass('show');
			} else {
				container.find('#' + taxIdSelector).removeClass('show').addClass('hide');
			}
		});
	},
	registerImageChangeEvent: function () {

	},
	registerBasicEvents : function(container) {
            this._super(container);
            this.registerTaxEvents(container);
            this.registerEventForMoreCurrencies();
            this.registerEventForUnitPrice();
            this.registerRecordPreSaveEvent();
	},
})

