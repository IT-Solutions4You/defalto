/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

jQuery.Class("PDFMaker_FreeActions_Js",{
    templatesElements : {},
    controlModal : function(container) {
        var aDeferred = jQuery.Deferred();
        if (container.find('.modal-content').length > 0) {
            app.helper.hideModal().then(
                function () {
                    aDeferred.resolve();
                }
            );
        } else {
            aDeferred.resolve();
        }
        return aDeferred.promise();
    },
    getPDFSelectLanguage: function(container) {
        return container.find('#template_language').val();
    },
    getDefaultParams: function(viewtype,pdflanguage) {

        var params = {
            module: 'PDFMaker',
            source_module : app.getModuleName(),
            formodule : app.getModuleName(),
            view: viewtype,
            record : app.getRecordId()
        };

        if (pdflanguage != '') {
            params['language'] = pdflanguage;
        }

        return params;
    },
    getModalNewHeight: function (modalContainer){
        return jQuery(window).height() - modalContainer.find('.modal-header').height() - modalContainer.find('.modal-footer').height() - 100;
    },
    setMaxModalHeight : function (modalContainer,modaltype){

        var new_height = this.getModalNewHeight(modalContainer);

        var params1 = {
            setHeight:new_height+'px'
        };

        app.helper.showVerticalScroll(modalContainer.find('.modal-body'), params1);

        if (modaltype == 'iframe'){
            var params2 = {
                setHeight:(new_height-35)+'px'
            };
            app.helper.showVerticalScroll(modalContainer.find(modaltype), params2);
        }
    },
    checkIfAny: function (modalContainer){

        var j = 0;
        var LineItemCheckboxes = modalContainer.find('.LineItemCheckbox');
        jQuery.each(LineItemCheckboxes,function(i,e) {
            if (jQuery(e).is(":checked")) {
                j++;
            }
        });
        var settingscheckboxes_el = modalContainer.find('.settingsCheckbox');
        if (j == 0){
            settingscheckboxes_el.removeAttr('checked');
            settingscheckboxes_el.attr( "disabled" ,"disabled" );
        } else {
            settingscheckboxes_el.removeAttr('disabled');
        }

    },
    showPDFMakerModal : function (modetype) {
        var self = this;
        var params = {
            module: 'PDFMaker',
            return_id:  app.getRecordId(),
            view: 'IndexAjax',
            mode: modetype
        };

        app.helper.showProgress();
        app.request.get({data:params}).then(function(err,response){

            app.helper.hideProgress();
            app.helper.showModal(response, {
                'cb' : function(modalContainer) {
                    if (modetype == "PDFBreakline") {
                        modalContainer.find('.LineItemCheckbox').on('click', function(){
                            self.checkIfAny(modalContainer);
                        });
                    }

                    modalContainer.find('#js-save-button').on('click', function(){
                        PDFMaker_FreeActions_Js.savePDFMakerModal(modalContainer, modetype);
                    });
                }
            });
        });

    },
    savePDFMakerModal: function (modalContainer,modetype) {
        var form = modalContainer.find('#Save' + modetype + 'Form');
        var params = form.serializeFormData();
        app.helper.hideModal();
        app.helper.showProgress();

        app.request.post({"data":params}).then(function (err) {
            if (err == null) {
                app.helper.hideProgress();
                app.helper.showSuccessNotification({"message":''});
            } else {
                app.helper.showErrorNotification({"message":''});
            }
        });
    },
    controlPDFSelectInput : function(container,element) {
        var fieldVal = element.val();
        if (fieldVal === null) {
            container.find('.btn-success').attr('disabled', 'disabled');
            container.find('.PDFMakerTemplateAction').hide();
        } else {
            container.find('.btn-success').removeAttr('disabled');
            container.find('.PDFMakerTemplateAction').show();
        }
    },
    registerPDFSelectInput : function(container) {
        var self = this;

        jQuery("#use_common_template",container).change(function(){
            var element = jQuery(this);

            self.controlPDFSelectInput(container,element);
        });
    },
    showPDFPreviewModal: function (pdflanguage) {
        var self = this;

        var params = this.getDefaultParams('IndexAjax',pdflanguage);
        params['mode'] = 'getPreview';

        app.helper.showProgress();
        app.request.get({data: params}).then(function(err, data) {

            app.helper.showModal(data, {
                'cb' : function(modalContainer) {
                    self.registerPDFPreviewActionsButtons(modalContainer,pdflanguage);
                    self.setMaxModalHeight(modalContainer,'iframe');
                }
            });

            app.helper.hideProgress();
        });
    },
    registerPDFPreviewActionsButtons: function (modalContainer){

        modalContainer.find('.downloadButton').on('click', function(e){
            window.location.href = jQuery(e.currentTarget).data('desc');
        });

        modalContainer.find('.printButton').on('click', function(){
            var PDF = document.getElementById("PDFMakerPreviewContent");
            PDF.focus();
            PDF.contentWindow.print();
        });
    },

    registerPDFActionsButtons: function (container){

        var self = this;

        container.find('.PDFMakerDownloadPDF').on('click', function(){
            var pdflanguage = self.getPDFSelectLanguage(container);

            var params = self.getDefaultParams('',pdflanguage);
            params["action"]  = 'CreatePDFFromTemplate';
            var paramsUrl = jQuery.param(params);
            window.location.href = "index.php?" + paramsUrl;

        });

        container.find('.PDFModalPreview').on('click', function(){
            var pdflanguage = self.getPDFSelectLanguage(container);
            self.controlModal(container).then(function() {
                self.showPDFPreviewModal(pdflanguage);
            });
        });

        container.find('.exportListPDF').on('click', function(){
            var form = container.find('#exportListPDFMakerForm');
            form.submit();
        });

        container.find('.showPDFBreakline').on('click', function(){
            self.showPDFMakerModal('PDFBreakline');
        });

        container.find('.showProductImages').on('click', function(){
            self.showPDFMakerModal('ProductImages');
        });

    }

},{

    registerEvents: function (){
        var self = this;
        var recordId = app.getRecordId();
        var view = app.view();

        var params = {
            module: 'PDFMaker',
            source_module : app.getModuleName(),
            view : 'GetPDFActions',
            record: recordId,
            mode : 'getButtons'
        };

        var detailViewButtonContainerDiv = jQuery('.detailview-header');

        app.request.post({'data' : params}).then(
            function(err,response) {
                
                if(err === null){
                    if (response != ""){
                        detailViewButtonContainerDiv.append(response);
                        detailViewButtonContainerDiv.find('#template_language').select2();

                        var pdfmakercontent = detailViewButtonContainerDiv.find('#PDFMakerContentDiv');
                        PDFMaker_FreeActions_Js.registerPDFActionsButtons(pdfmakercontent);
                        PDFMaker_FreeActions_Js.registerPDFSelectInput(pdfmakercontent);
                        detailViewButtonContainerDiv.find('.selectPDFTemplates').on('click', function(){
                            var pdflanguage = PDFMaker_FreeActions_Js.getPDFSelectLanguage(pdfmakercontent);
                            PDFMaker_FreeActions_Js.showPDFPreviewModal(pdflanguage);
                        });
                    }
                }
            }
        );
    }
});

jQuery(document).ready(function(){
	if(jQuery.inArray( app.getModuleName(), [ 'Invoice','Quotes','SalesOrder','PurchaseOrder' ] ) !== -1){
        var instance = new PDFMaker_FreeActions_Js();
        instance.registerEvents();
    }
});

