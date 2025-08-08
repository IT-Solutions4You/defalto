/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_Edit_Js("Products_Edit_Js", {
        getMessageForChildProductDeletionOrInActivation: function (params) {
            var aDeferred = jQuery.Deferred();
            var message = '';
            if (params['module'] == 'Products') {
                params['action'] = 'Mass';
                params['mode'] = 'isChildProduct';

                app.request.post({data: params}).then(function (err, data) {
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
        getUnitPrice: function () {
            if (this.unitPrice == false) {
                this.unitPrice = jQuery('input.unitPrice', this.getForm());
            }
            return this.unitPrice;
        },
        /**
         * Function to get more currencies container
         */
        getMoreCurrenciesContainer: function () {
            if (this.multiCurrencyContainer == false) {
                this.multiCurrencyContainer = jQuery('.multiCurrencyEditUI');
            }
            return this.multiCurrencyContainer;
        },
        /**
         * Function to get current Element
         */
        getCurrentElem: function (e) {
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
        getMoreCurrenciesUI: function () {
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
                    function (err, data) {
                        if (data) {
                            moreCurrenciesContainer.html(data);
                            aDeferred.resolve(data);
                        }
                    });
            } else {
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
                            self.updateCurrencyElements(form);
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

                        app.helper.showModal(moreCurrenciesUi, {cb: callback, modalName: 'currencyModal'});
                    }
                });
            });
        },
        saveCurrencies: function () {
            let thisInstance = this,
                errorMessage,
                form = jQuery('#currencyContainer'),
                editViewForm = thisInstance.getForm(),
                modalContainer = jQuery('#currencyModal'),
                enabledBaseCurrency = modalContainer.find('.enableCurrency').filter(':checked');

            if (enabledBaseCurrency.length < 1) {
                errorMessage = app.vtranslate('JS_PLEASE_SELECT_BASE_CURRENCY_FOR_PRODUCT');
                app.helper.showErrorNotification({message: errorMessage});
                form.removeData('submit');
                return;
            }

            enabledBaseCurrency.attr('checked', "checked");
            modalContainer.find('.enableCurrency').filter(":not(:checked)").removeAttr('checked');
            let selectedBaseCurrency = modalContainer.find('.baseCurrency').filter(':checked');

            if (selectedBaseCurrency.length < 1) {
                errorMessage = app.vtranslate('JS_PLEASE_ENABLE_BASE_CURRENCY_FOR_PRODUCT');
                app.helper.showErrorNotification({message: errorMessage});
                form.removeData('submit');
                return;
            }

            selectedBaseCurrency.attr('checked', "checked");
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
            modalContainer.find('input[name^=curname]').each(function (index, element) {
                let dataValue = jQuery(element).val(),
                    dataId = jQuery(element).attr('id');

                moreCurrenciesContainer.find('.currencyContent').find('#' + dataId).val(dataValue);
            });
            app.helper.hideModal({modalName: 'currencyModal'});
        },

        calculateConversionRate: function () {
            let container = jQuery('.multiCurrencyEditUI:visible'),
                baseCurrencyRow = container.find('.baseCurrency').filter(':checked').closest('tr'),
                baseCurrencyConvestationRate = baseCurrencyRow.find('.conversionRate');
            //if basecurrency has conversation rate as 1 then you dont have calculate conversation rate
            if (baseCurrencyConvestationRate.val() == "1") {
                return;
            }

            let baseCurrencyRatePrevValue = baseCurrencyConvestationRate.val();

            container.find('.conversionRate').each(function (key, domElement) {
                let element = jQuery(domElement);

                if (!element.is(baseCurrencyConvestationRate)) {
                    let prevValue = element.val();
                    element.val((prevValue / baseCurrencyRatePrevValue).toFixed(5));
                }
            });

            baseCurrencyConvestationRate.val("1");
        },
        updateEnableCurrency: function (element) {
            let self = this,
                parentRow = element.closest('tr');

            if (element.is(':checked')) {
                element.prop('checked', true);

                let conversionRate = jQuery('.conversionRate', parentRow).val(),
                    unitPriceFieldData = self.getUnitPrice().data(),
                    unitPrice = self.getDataBaseFormatUnitPrice(),
                    price = parseFloat(unitPrice) * parseFloat(conversionRate);

                price = price.toFixed(unitPriceFieldData['numberOfDecimalPlaces']);

                jQuery('input.convertedPrice', parentRow).val(price);

                self.unsetDisabled(jQuery('input[type="text"]', parentRow));
                self.unsetDisabled(jQuery('button', parentRow));
            } else {
                let baseCurrency = jQuery('.baseCurrency', parentRow);

                if (baseCurrency.is(':checked')) {
                    let currencyName = jQuery('.currencyName', parentRow).text(),
                        errorMessage = app.vtranslate('JS_BASE_CURRENCY_CHANGED_TO_DISABLE_CURRENCY') + '"' + currencyName + '"';

                    app.helper.showErrorNotification({message: errorMessage});
                    element.prop('checked', true);

                    return;
                }

                self.setDisabled(jQuery('input[type="text"]', parentRow));
                self.setDisabled(jQuery('button', parentRow));
            }
        },
        updateCurrencyElements(container) {
            const self = this;

            container.find('.enableCurrency').each(function() {
                self.updateEnableCurrency($(this));
            });
        },
        /**
         * Function to register event for enabling currency on checkbox checked
         */
        registerEventForEnableCurrency: function () {
            let container = this.getMoreCurrenciesContainer(),
                self = this;

            jQuery(container).on('change', '.enableCurrency', function () {
                self.updateEnableCurrency($(this));
            });

            return this;
        },
        setReadOnly(element) {
            element.prop('readonly', true);
            element.attr('readonly', 'readonly');
            element.addClass('bg-body-secondary');
        },
        unsetReadOnly(element) {
            element.removeProp('readonly');
            element.removeAttr('readonly');
            element.removeClass('bg-body-secondary');
        },
        setDisabled(element) {
            element.prop('disabled', true);
            element.attr('disabled', 'disabled');
            element.addClass('bg-body-secondary');
        },
        unsetDisabled(element) {
            element.removeProp('disabled');
            element.removeAttr('disabled');
            element.removeClass('bg-body-secondary');
        },
        /**
         * Function to register event for enabling base currency on radio button clicked
         */
        registerEventForEnableBaseCurrency: function () {
            let container = this.getMoreCurrenciesContainer(),
                self = this;

            $(container).on('change', '.baseCurrency', function (e) {
                let elem = self.getCurrentElem(e),
                    parentElem = elem.closest('tr');

                if (elem.is(':checked')) {
                    self.baseCurrencyName = parentElem.data('currencyId');
                    self.baseCurrency = $('.convertedPrice', parentElem).val();
                    self.calculateConversionRate();
                    self.activateCurrency(parentElem);
                }
            });

            return this;
        },
        activateCurrency(element) {
            $('.enableCurrency', element).attr('checked', 'checked').trigger('change');
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
        triggerForBaseCurrencyCalc: function () {
            let multiCurrencyEditUI = this.getMoreCurrenciesContainer(),
                baseCurrency = multiCurrencyEditUI.find('.enableCurrency');

            jQuery.each(baseCurrency, function (key, val) {
                if (jQuery(val).is(':checked')) {
                    let baseCurrencyRow = jQuery(val).closest('tr'),
                        unitPrice = jQuery('.unitPrice'),
                        isPriceChanged = unitPrice.data('isPriceChanged');

                    if (isPriceChanged) {
                        let changedUnitPrice = unitPrice.val();
                        baseCurrencyRow.find('.convertedPrice').val(changedUnitPrice);
                        baseCurrencyRow.find('.currencyReset').trigger('click');
                    }

                    if (parseFloat(baseCurrencyRow.find('.convertedPrice').val()) == 0) {
                        baseCurrencyRow.find('.currencyReset').trigger('click');
                    }
                } else {
                    let baseCurrencyRow = jQuery(val).closest('tr');
                    baseCurrencyRow.find('.convertedPrice').val('');
                }
            });
        },
        /**
         * Function to register onchange event for unit price
         */
        registerEventForUnitPrice: function () {
            var unitPrice = this.getUnitPrice();
            unitPrice.on('focusout', function () {
                var oldValue = unitPrice.data('oldValue');
                if (oldValue != unitPrice.val()) {
                    unitPrice.data('isPriceChanged', true);
                }
            })
        },
        issetInActivationMessage: false,
        registerRecordPreSaveEvent: function (form) {
            var self = this;
            if (typeof form == 'undefined') {
                form = this.getForm();
            }
            app.event.on(Vtiger_Edit_Js.recordPresaveEvent, function (e, data) {
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

                        Products_Edit_Js.getMessageForChildProductDeletionOrInActivation(params).then(function (message) {
                            if (message != '') {
                                app.helper.showConfirmationBox({'message': message}).then(
                                    function (data) {
                                        self.checkMoreCurrenciesUI(e, form);
                                        self.issetInActivationMessage = true;
                                    },
                                    function (error, err) {
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
        checkMoreCurrenciesUI: function (e, form) {
            var thisInstance = this;
            var multiCurrencyContent = jQuery('#moreCurrenciesContainer').find('.currencyContent');
            var unitPrice = thisInstance.getUnitPrice();
            if ((multiCurrencyContent.length < 1) && (unitPrice.length > 0)) {
                e.preventDefault();
                thisInstance.getMoreCurrenciesUI().then(function (data) {
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
        preSaveConfigOfForm: function (form) {
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
                    container.find('#' + taxIdSelector).removeClass('hide');
                } else {
                    container.find('#' + taxIdSelector).addClass('hide');
                }
            });
        },
        registerImageChangeEvent: function () {

        },
        registerBasicEvents: function (container) {
            this._super(container);
            this.registerTaxEvents(container);
            this.registerEventForMoreCurrencies();
            this.registerEventForUnitPrice();
            this.registerRecordPreSaveEvent();
        },
    })

