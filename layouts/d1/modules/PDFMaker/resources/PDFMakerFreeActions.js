/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var PDFMaker_FreeActions_Js */
jQuery.Class('PDFMaker_FreeActions_Js', {
    templatesElements: {},
    controlModal: function (container) {
        let aDeferred = jQuery.Deferred();

        if (container.find('.modal-content').length > 0) {
            app.helper.hideModal().then(function () {
                aDeferred.resolve();
            });
        } else {
            aDeferred.resolve();
        }

        return aDeferred.promise();
    },
    getPDFSelectLanguage: function (container) {
        return container.find('#template_language').val();
    },
    getDefaultParams: function (viewtype, pdfLanguage) {
        let params = {
            module: 'PDFMaker',
            source_module: app.getModuleName(),
            formodule: app.getModuleName(),
            view: viewtype,
            record: app.getRecordId()
        };

        if (pdfLanguage) {
            params['language'] = pdfLanguage;
        }

        return params;
    },
    setMaxModalHeight: function (modalContainer, modalType) {
        app.helper.showVerticalScroll(modalContainer.find('.modal-body'), {
            setHeight: '85vh',
        });

        if ('iframe' === modalType) {
            modalContainer.find(modalType).height('81vh');
        }
    },
    checkIfAny: function (modalContainer) {
        let j = 0,
            LineItemCheckboxes = modalContainer.find('.LineItemCheckbox');

        jQuery.each(LineItemCheckboxes, function (i, e) {
            if (jQuery(e).is(":checked")) {
                j++;
            }
        });

        let settingscheckboxes_el = modalContainer.find('.settingsCheckbox');

        if (0 === j) {
            settingscheckboxes_el.removeAttr('checked');
            settingscheckboxes_el.attr("disabled", "disabled");
        } else {
            settingscheckboxes_el.removeAttr('disabled');
        }

    },
    showPDFMakerModal: function (modeType) {
        let self = this;
        let params = {
            module: 'PDFMaker',
            return_id: app.getRecordId(),
            view: 'IndexAjax',
            mode: modeType
        };

        app.helper.showProgress();
        app.request.get({data: params}).then(function (err, response) {

            app.helper.hideProgress();
            app.helper.showModal(response, {
                'cb': function (modalContainer) {
                    if ('PDFBreakline' === modeType) {
                        modalContainer.find('.LineItemCheckbox').on('click', function () {
                            self.checkIfAny(modalContainer);
                        });
                    }

                    modalContainer.find('#js-save-button').on('click', function () {
                        PDFMaker_FreeActions_Js.savePDFMakerModal(modalContainer, modeType);
                    });
                }
            });
        });

    },
    savePDFMakerModal: function (modalContainer, modeType) {
        let form = modalContainer.find('#Save' + modeType + 'Form');
        let params = form.serializeFormData();

        app.helper.hideModal();
        app.helper.showProgress();

        app.request.post({data: params}).then(function (error) {
            if (!error) {
                app.helper.hideProgress();
                app.helper.showSuccessNotification({message: ''});
            } else {
                app.helper.showErrorNotification({message: ''});
            }
        });
    },
    controlPDFSelectInput: function (container, element) {
        let fieldVal = element.val();

        if (fieldVal === null) {
            container.find('.btn-success').attr('disabled', 'disabled');
            container.find('.PDFMakerTemplateAction').hide();
        } else {
            container.find('.btn-success').removeAttr('disabled');
            container.find('.PDFMakerTemplateAction').show();
        }
    },
    registerPDFSelectInput: function (container) {
        let self = this;

        jQuery('[name=use_common_template]', container).change(function () {
            let element = jQuery(this);

            self.controlPDFSelectInput(container, element);
        });
    },
    showPDFPreviewModal: function (pdfLanguage) {
        let self = this;

        let params = this.getDefaultParams('IndexAjax', pdfLanguage);
        params['mode'] = 'getPreview';

        app.helper.showProgress();
        app.request.get({data: params}).then(function (err, data) {

            app.helper.showModal(data, {
                'cb': function (modalContainer) {
                    self.registerPDFPreviewActionsButtons(modalContainer, pdfLanguage);
                    self.setMaxModalHeight(modalContainer, 'iframe');
                }
            });

            app.helper.hideProgress();
        });
    },
    registerPDFPreviewActionsButtons: function (modalContainer) {

        modalContainer.find('.downloadButton').on('click', function (e) {
            window.location.href = jQuery(e.currentTarget).data('desc');
        });

        modalContainer.find('.printButton').on('click', function () {
            let PDF = document.getElementById("PDFMakerPreviewContent");
            PDF.focus();
            PDF.contentWindow.print();
        });
    },

    registerPDFActionsButtons: function (container) {

        let self = this;

        container.find('.PDFMakerDownloadPDF').on('click', function () {
            let pdfLanguage = self.getPDFSelectLanguage(container),
                params = self.getDefaultParams('', pdfLanguage);

            params["action"] = 'CreatePDFFromTemplate';

            window.location.href = 'index.php?' + jQuery.param(params);
        });

        container.find('.PDFModalPreview').on('click', function () {
            let pdfLanguage = self.getPDFSelectLanguage(container);

            self.controlModal(container).then(function () {
                self.showPDFPreviewModal(pdfLanguage);
            });
        });

        container.find('.exportListPDF').on('click', function () {
            let form = container.find('#exportListPDFMakerForm');
            form.submit();
        });

        container.find('.showPDFBreakline').on('click', function () {
            self.showPDFMakerModal('PDFBreakline');
        });

        container.find('.showProductImages').on('click', function () {
            self.showPDFMakerModal('ProductImages');
        });
    }
}, {
    registerEvents: function () {
        let linkDiv = $('#PDFMakerContentDiv');

        linkDiv.find('#template_language').select2();

        PDFMaker_FreeActions_Js.registerPDFActionsButtons(linkDiv);
        PDFMaker_FreeActions_Js.registerPDFSelectInput(linkDiv);

        linkDiv.find('.selectPDFTemplates').on('click', function () {
            let pdfLanguage = PDFMaker_FreeActions_Js.getPDFSelectLanguage(linkDiv);
            PDFMaker_FreeActions_Js.showPDFPreviewModal(pdfLanguage);
        });
    }
});

jQuery(document).ready(function () {
    if (jQuery.inArray(app.getModuleName(), ['Invoice', 'Quotes', 'SalesOrder', 'PurchaseOrder']) !== -1) {
        let instance = new PDFMaker_FreeActions_Js();
        instance.registerEvents();
    }
});

