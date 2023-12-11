/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
/** @var EMAILMaker_CustomLabels_Js */
Vtiger_Index_Js('EMAILMaker_CustomLabels_Js', {

    getInstance: function () {
        return new EMAILMaker_CustomLabels_Js();
    },

}, {
    duplicateCheckCache: [],
    registerSaveCustomLabel: function (form, currentTrElement) {
        const self = this;

        jQuery('#js-save-cl', form).on('click', function () {
            if (form.valid()) {
                self.saveCustomLabelDetails(form, currentTrElement);
            }
        });
    },
    editCustomLabel: function (url, currentTrElement) {
        const aDeferred = jQuery.Deferred(),
            self = this;

        app.helper.showProgress();
        app.request.get({'url': url}).then(function (error, response) {
            app.helper.hideProgress();

            if (!error) {
                let callback = function () {
                    let form = jQuery('#editCustomLabel');
                    self.registerSaveCustomLabel(form, currentTrElement);

                };
                let data = {};
                data['cb'] = callback;
                app.helper.hideProgress();
                app.helper.showModal(response, data);
            }
        });

        return aDeferred.promise();
    },
    deleteCustomLabel: function (url, currentTrElement) {
        let self = this,
            message = app.vtranslate('LBL_DELETE_CONFIRMATION');

        app.helper.showConfirmationBox({'message': message}).then(function () {
            app.helper.showProgress();
            app.request.get({'url': url}).then(function (error, response) {
                app.helper.hideProgress();

                if (!error) {
                    currentTrElement.hide();
                    self.updateNoItemFoundTr();
                    app.helper.showSuccessNotification({'message': ''});
                }
            });
        });
    },
    registerSaveCustomLabelValues: function (container, form) {
        const self = this;

        jQuery('#js-save-cl', container).on('click', function () {
            if (form.valid()) {
                self.saveCustomLabelValues(form);
            }
        });
    },
    showCustomLabelValues: function (url) {
        const self = this;

        app.helper.showProgress();
        app.request.get({'url': url}).then(function (error, response) {
            app.helper.hideProgress();

            if (!error) {
                let callback = function (container) {
                    //cache should be empty when modal opened
                    let form = jQuery('#showCustomLabelValues');
                    self.registerSaveCustomLabelValues(container, form);
                };
                let data = {};
                data['cb'] = callback;
                app.helper.hideProgress();
                app.helper.showModal(response, data);
            }
        });
    },
    updateNoItemFoundTr: function () {
        const noItemFoundTr = $('#noItemFoundTr');

        if ($('.CustomLabel:visible').length) {
            noItemFoundTr.hide();
        } else {
            noItemFoundTr.show();
        }
    },
    addCustomLabelDetails: function (data) {
        const self = this,
            CustomLabelTable = $('#CustomLabelsContainer').find('.CustomLabelTable tbody'),
            CustomLabel = self.getCustomLabel(data);

        CustomLabelTable.append(CustomLabel);

        self.updateNoItemFoundTr();
    },
    getCustomLabel: function (data) {
        const CustomLabel = $('.cloneCustomLabel').clone(false);

        CustomLabel.removeClass('cloneCustomLabel');
        CustomLabel.removeClass('hide');
        CustomLabel.find('.CustomLabelKey').html(data['lblkey'])
        CustomLabel.find('.CustomLabelValue').html(data['lblval'])
        CustomLabel.find('.editCustomLabel').attr('data-url', 'index.php?module=EMAILMaker&view=IndexAjax&mode=editCustomLabel&labelid=' + data['labelid'] + '&langid=' + data['langid'] + '');
        CustomLabel.find('.deleteCustomLabel').attr('data-url', 'index.php?module=EMAILMaker&action=IndexAjax&mode=deleteCustomLabel&labelid=' + data['labelid']);
        CustomLabel.find('.showCustomLabelValues').attr('data-url', 'index.php?module=EMAILMaker&view=IndexAjax&mode=showCustomLabelValues&labelid=' + data['labelid'] + '&langid=' + data['langid']);

        return CustomLabel;
    },
    updateCustomLabelDetails: function (data, currentTrElement) {
        currentTrElement.find('.CustomLabelValue').text(data['lblval']);
    },
    saveCustomLabelValues: function (form) {
        let params = form.serializeFormData();

        if (typeof params == 'undefined') {
            params = {};
        }

        app.hideModalWindow();
        app.helper.showProgress();

        params.module = app.getModuleName();
        params.action = 'IndexAjax';
        params.mode = 'SaveCustomLabelValues';

        app.request.post({'data': params}).then(function (error) {
            app.helper.hideProgress();

            if (!error) {
                app.helper.showSuccessNotification({
                    'message': app.vtranslate('JS_CUSTOM_LABEL_VALUES_SAVED_SUCCESSFULLY')
                });
            }
        });
    },
    saveCustomLabelDetails: function (form, currentTrElement) {
        const aDeferred = jQuery.Deferred(),
            self = this;

        let params = form.serializeFormData();

        if (typeof params == 'undefined') {
            params = {};
        }

        let editViewForm = jQuery('#editCustomLabel');

        this.formValidatorInstance = editViewForm.vtValidate({
            submitHandler: function () {
                self.checkDuplicateKey(params).then(
                    function (result) {
                        if (result.success) {
                            app.helper.showErrorNotification({"message": result.message});
                        } else {
                            app.helper.showProgress();

                            params.module = app.getModuleName();
                            params.action = 'IndexAjax';
                            params.mode = 'SaveCustomLabel';
                            app.request.post({'data': params}).then(
                                function (err, response) {
                                    app.helper.hideProgress();
                                    app.helper.hideModal();
                                    if (err === null) {
                                        if (form.find('.addCustomLabelView').val() == "true") {
                                            self.addCustomLabelDetails(response);
                                        } else {
                                            self.updateCustomLabelDetails(response, currentTrElement);
                                        }
                                        app.helper.showSuccessNotification({"message": app.vtranslate('JS_CUSTOM_LABEL_SAVED_SUCCESSFULLY')});

                                    }
                                }
                            );
                        }
                    }
                );
            }
        });

        return aDeferred.promise();
    },
    checkDuplicateKey: function (details) {
        let aDeferred = jQuery.Deferred(),
            LblKey = details.LblKey,
            params = {
                'module': 'EMAILMaker',
                'action': 'IndexAjax',
                'mode': 'checkDuplicateKey',
                'lblkey': LblKey
            };

        app.request.get({'data': params}).then(function (error, response) {
            if (!error) {
                aDeferred.resolve(response);
            }
        });

        return aDeferred.promise();
    },
    registerActions: function () {
        const self = this,
            container = $('#CustomLabelsContainer');

        container.find('.addCustomLabel').click(function (e) {
            let addTaxButton = $(e.currentTarget),
                createTaxUrl = addTaxButton.data('url') + '&type=' + addTaxButton.data('type');

            self.editCustomLabel(createTaxUrl);
        });

        container.on('click', '.editCustomLabel', function (e) {
            let editTaxButton = $(e.currentTarget),
                currentTrElement = editTaxButton.closest('tr');

            self.editCustomLabel(editTaxButton.data('url'), currentTrElement);
        });

        container.on('click', '.deleteCustomLabel', function (e) {
            let deleteButton = $(e.currentTarget),
                currentTrElement = deleteButton.closest('tr');

            self.deleteCustomLabel(deleteButton.data('url'), currentTrElement);
        });

        container.on('click', '.showCustomLabelValues', function (e) {
            let editTaxButton = $(e.currentTarget);

            self.showCustomLabelValues(editTaxButton.data('url'));
        });
    },
    registerEvents: function () {
        this.registerActions();
        this.updateNoItemFoundTr()
    }
});