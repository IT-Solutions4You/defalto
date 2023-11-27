/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/** @var EMAILMaker_MassEdit_Js */
Emails_MassEdit_Js('EMAILMaker_MassEdit_Js', {

    init: function () {
        this.preloadAllData = [];
        //this.preloadAllData["to"] = new Array();
        //this.preloadAllData["cc"] = new Array();
        //this.preloadAllData["bcc"] = new Array();
    },

    ckEditorInstance: false,
    massEmailForm: false,
    saved: 'SAVED',
    sent: 'SENT',
    attachmentsFileSize: 0,
    documentsFileSize: 0,

    getPreloadAllData: function (type) {
        let sid = this.getEmailsSourceId();

        if (!type) type = 'to';

        if ('undefined' === typeof this.preloadAllData[sid]) {
            return null;
        }

        return this.preloadAllData[sid][type];
    },

    setPreloadAllData: function (type, dataInfo) {
        let sid = this.getEmailsSourceId();

        if (!type) type = 'to';

        if ('undefined' === typeof this.preloadAllData[sid]) {
            this.preloadAllData[sid] = [];
        }

        this.preloadAllData[sid][type] = dataInfo;

        return this;
    },
    /**
     * Function which will handle the reference auto complete event registrations
     * @params - container <jQuery> - element in which auto complete fields needs to be searched
     */

    registerAutoCompleteFields: function (container, emailType) {
        let self = this,
            lastResults = [],
            type = 'to' === emailType ? '' : emailType;

        container.find('#email' + type + 'Field').select2({
            minimumInputLength: 3,
            closeOnSelect: false,
            tags: [],
            tokenSeparators: [','],
            ajax: {
                'url': 'index.php?module=EMAILMaker&action=IndexAjax&mode=SearchEmails',
                'dataType': 'json',
                'data': function (term, page) {
                    return {
                        'searchValue': term,
                    };
                },
                'results': function (data) {
                    let finalResult = [],
                        results = data.result,
                        resultData = [];

                    for (let moduleName in results) {
                        let moduleResult = [];
                        moduleResult.text = moduleName;

                        let children = [];

                        for (let recordId in data.result[moduleName]) {
                            let emailInfo = data.result[moduleName][recordId];

                            for (let i in emailInfo) {
                                let childrenInfo = [];
                                childrenInfo.recordId = recordId;
                                childrenInfo.id = emailInfo[i].value;
                                childrenInfo.text = emailInfo[i].label;
                                childrenInfo.module = emailInfo[i].module;
                                children.push(childrenInfo);
                            }
                        }
                        moduleResult.children = children;
                        resultData.push(moduleResult);
                    }
                    finalResult.results = resultData;
                    lastResults = resultData;
                    return finalResult;
                },
                transport: function (params) {
                    return jQuery.ajax(params);
                }
            },
            createSearchChoice: function (term) {
                //checking for results if there is any if not creating as value
                if (!lastResults.length) {
                    return {id: term, text: term};
                }
            },
            escapeMarkup: function (m) {
                // Do not escape HTML in the select options text
                return m;
            },

        }).on('change', function (selectedData) {
            let addedElement = selectedData.added;

            if ('undefined' !== typeof addedElement) {
                let data = {
                    'eid': addedElement.recordId + '|' + addedElement.id + '|' + addedElement.module,
                    'id': addedElement.recordId,
                    'name': addedElement.text,
                    'emailid': addedElement.id
                }

                if ('undefined' === typeof addedElement.recordId) {
                    data.eid = 'email|' + addedElement.id + '|';
                }

                if ('cc' === emailType) {
                    self.addCCEmailAddressData(data);
                } else if ('bcc' === emailType) {
                    self.addBCCEmailAddressData(data);
                } else {
                    self.addToEmailAddressData(data);
                }

                self.addEmails(emailType, data);

                let preloadData = self.getPreloadAllData(type),
                    emailInfo = {
                    'id': addedElement.id,
                    'eid': data.eid
                }

                if ('undefined' !== typeof addedElement.recordId) {
                    emailInfo['text'] = addedElement.text;
                    emailInfo['module'] = addedElement.module;
                    emailInfo['recordId'] = addedElement.recordId;
                } else {
                    emailInfo['text'] = addedElement.id;
                }

                preloadData.push(emailInfo);
                self.setPreloadAllData(type, preloadData);
            }

            let removedElement = selectedData.removed;

            if (typeof removedElement != 'undefined') {
                let data = {
                    'eid': removedElement.recordId + "|" + removedElement.id + "|" + removedElement.module,
                    'id': removedElement.recordId,
                    'name': removedElement.text,
                    'emailid': removedElement.id
                }

                if (typeof removedElement.recordId == 'undefined') {
                    data.eid = "email|" + removedElement.id + "|";
                }

                self.removeFromEmails(emailType, data);
                self.removeFromEmailAddressData(emailType, data);

                let preloadData = self.getPreloadAllData(emailType),
                    updatedPreloadData = [];

                for (let i in preloadData) {
                    let preloadDataInfo = preloadData[i],
                        skip = false;

                    if (data.eid === preloadDataInfo.eid) {
                        skip = true;
                    }
                    if (skip === false) {
                        updatedPreloadData.push(preloadDataInfo);
                    }
                }

                self.setPreloadAllData(emailType, updatedPreloadData);
                container.find('#emailField').select2('data', updatedPreloadData);
            }
        });

        container.find('#email' + type + 'Field').select2("container").find("ul.select2-choices").sortable({
            containment: 'parent',
            start: function () {
                container.find('#email' + type + 'Field').select2("onSortStart");
            },
            update: function () {
                container.find('#email' + type + 'Field').select2("onSortEnd");
            }
        });

        self.actualizeSelect2El(container, emailType);
    },

    removeFromEmailAddressData: function (emailType, mailInfo) {
        let self = this,
            sid = self.getEmailsSourceId(),
            mailInfoElement = self.getMassEmailForm().find('[name="' + sid + emailType + 'emailinfo"]'),
            previousValue = self.getObjectFromString(mailInfoElement.val()),
            elementSize = previousValue[mailInfo.eid].length,
            emailAddress = mailInfo.emailid,
            selectedId = mailInfo.eid;

        //If element length is not more than two delete existing record.
        if (elementSize < 2) {
            delete previousValue[selectedId];
        } else {
            // Update toemailinfo hidden element value
            let newValue,
                reserveValue = previousValue[selectedId];

            delete previousValue[selectedId];
            //Remove value from an array and return the resultant array
            newValue = jQuery.grep(reserveValue, function (value) {
                return value !== emailAddress;
            });
            previousValue[selectedId] = newValue;
            //update toemailnameslist hidden element value
        }

        mailInfoElement.val(JSON.stringify(previousValue));
    },

    removeFromSelectedIds: function (etype, selectedId) {
        /*
        var selectedIdElement = this.getMassEmailForm().find('[name="selected_ids"]');
        var previousValue = JSON.parse(selectedIdElement.val());
        var mailInfoElement = this.getMassEmailForm().find('[name="'+etype+'emailinfo"]');
        var mailAddress = JSON.parse(mailInfoElement.val());
        var elements = mailAddress[selectedId];
        var noOfEmailAddress = elements.length;

        //Don't remove id from selected_ids if element is having more than two email id's
        if(noOfEmailAddress < 2){
            var updatedValue = [];
            for (var i in previousValue) {
                var id = previousValue[i];
                var skip = false;
                if (id == selectedId) {
                    skip = true;
                }
                if (skip == false) {
                    updatedValue.push(id);
                }
            }
            selectedIdElement.val(JSON.stringify(updatedValue));
        }*/
    },

    removeFromEmails: function (etype, mailInfo) {
        var Emails = this.getMassEmailForm().find('[name="' + etype + '"]');
        var previousValue = JSON.parse(Emails.val());

        var updatedValue = {};
        for (var i in previousValue) {
            var email = previousValue[i];

            if (i != mailInfo.eid) {
                updatedValue[i] = email;
            }
        }
        Emails.val(JSON.stringify(updatedValue));
    },
    isEmptyObject: function (value) {
        return !value || '[]' === value;
    },
    getObjectFromString: function (value) {
        return this.isEmptyObject(value) ? {} : JSON.parse(value);
    },
    addEmails: function (type, mailInfo) {
        let emailsElement = this.getMassEmailForm().find('[name="' + type + '"]'),
            emailsValue = this.getObjectFromString(emailsElement.val());

        emailsValue[mailInfo['eid']] = mailInfo['name'];
        emailsElement.val(JSON.stringify(emailsValue));
    },

    addToEmails: function (mailInfo) {
        this.addEmails('to', mailInfo);
    },
    getMailInfoElement(type) {
        let sid = this.getEmailsSourceId();

        return this.getMassEmailForm().find('[name="' + sid + type + 'emailinfo"]');
    },
    addToEmailAddressData: function (mailInfo) {
        let mailInfoElement = this.getMailInfoElement('to'),
            existingMailInfo = this.getEmailAddressData(mailInfoElement.val(), mailInfo);

        mailInfoElement.val(existingMailInfo);
    },
    getEmailAddressData(mailInfoValue, mailInfo) {
        let existingMailInfo = this.getObjectFromString(mailInfoValue);

        if (existingMailInfo.hasOwnProperty(mailInfo.eid) === true) {
            let existingValues = existingMailInfo[mailInfo.eid],
                newValue = [mailInfo.name];

            existingMailInfo[mailInfo.eid] = jQuery.merge(existingValues, newValue);
        } else {
            existingMailInfo[mailInfo.eid] = [mailInfo.name];
        }

        return JSON.stringify(existingMailInfo);
    },
    appendToSelectedIds: function (selectedId) {
        /*
        var selectedIdElement = this.getMassEmailForm().find('[name="selected_ids"]');
        var previousValue = '';
        if(JSON.parse(selectedIdElement.val()) != '') {
            previousValue = JSON.parse(selectedIdElement.val());
            //If value doesn't exist then insert into an array
            if(jQuery.inArray(selectedId,previousValue) === -1){
                previousValue.push(selectedId);
            }
        } else {
            previousValue = new Array(selectedId);
        }
        selectedIdElement.val(JSON.stringify(previousValue));
*/
    },

    checkHiddenStatusofCcandBcc: function () {
        var ccLink = jQuery('#ccLink');
        var bccLink = jQuery('#bccLink');
        if (ccLink.is(':hidden') && bccLink.is(':hidden')) {
            ccLink.closest('div.row').addClass('hide');
        }
    },

    registerEventsForToField: function () {
        const self = this;

        self.getMassEmailForm().on('click', '.selectEmail', function (e) {
            let moduleSelected = jQuery('.emailModulesList').select2('val'),
                parentElem = jQuery(e.target).closest('.toEmailField'),
                sourceModule = jQuery('[name=module]').val(),
                params = {
                    'module': moduleSelected,
                    'src_module': 'Emails',
                    'view': 'EmailsRelatedModulePopup'
                },
                popupInstance = Vtiger_Popup_Js.getInstance();

            popupInstance.showPopup(params, function (data) {
                let responseData = JSON.parse(data);

                for (let id in responseData) {
                    let data = {
                        'eid': id + "|" + responseData[id].email + "|" + moduleSelected,
                        'name': responseData[id].name,
                        'id': id,
                        'module': moduleSelected,
                        'emailid': responseData[id].email
                    }

                    self.setReferenceFieldValue(parentElem, data);
                    self.addToEmailAddressData(data);
                    self.addToEmails(data);
                }
            }, 'relatedEmailModules');
        });


        self.getMassEmailForm().on('click', '[name="clearToEmailField"]', function (e) {
            let element = jQuery(e.currentTarget),
                sid = self.getEmailsSourceId(),
                preloadData = [];

            element.closest('div.toEmailField').find('.sourceField').val('');

            self.getMassEmailForm().find('[name="' + sid + 'toemailinfo"]').val(JSON.stringify([]));
            self.getMassEmailForm().find('[name="selected_ids"]').val(JSON.stringify([]));
            self.getMassEmailForm().find('[name="to"]').val(JSON.stringify([]));

            self.setPreloadAllData('to', preloadData);
            self.getMassEmailForm().find('#emailField').select2('data', preloadData);
        });

    },

    setReferenceFieldValue: function (container, object) {
        let self = this,
            preloadData = self.getPreloadAllData('to');

        if ('undefined' === typeof preloadData || null === preloadData) {
            preloadData = [];
        }

        let emailInfo = {
            'eid': object.id + "|" + object.emailid + "|" + object.module,
            'recordId': object.id,
            'id': object.emailid,
            'module': object.module,
            'text': object.name + ' <b>(' + object.emailid + ')</b>'
        }

        preloadData.push(emailInfo);

        self.setPreloadAllData('to', preloadData);
        container.find('#emailField').select2('data', preloadData);

        let toEmailField = container.find('.sourceField'),
            toEmailFieldExistingValue = toEmailField.val(),
            toEmailFieldNewValue;

        if (!toEmailFieldExistingValue) {
            toEmailFieldNewValue = toEmailFieldExistingValue + "," + object.emailid;
        } else {
            toEmailFieldNewValue = object.emailid;
        }

        toEmailField.val(toEmailFieldNewValue);
    },
    showPDFPreviewModal: function (templateid, pdflanguage) {
        var self = this;
        var view = app.view();
        if (view == 'Detail') {
            var recordId = app.getRecordId();
        } else {
            var recordId = self.getEmailsSourceId();
        }
        ;
        forview_val = 'Detail';

        if (recordId) {
            var params = {
                module: 'PDFMaker',
                source_module: app.getModuleName(),
                formodule: app.getModuleName(),
                forview: forview_val,
                pdftemplateid: templateid,
                language: pdflanguage,
                view: 'IndexAjax',
                mode: 'getPreview',
                hidebuttons: 'true',
                record: recordId
            };

            var popupInstance = Vtiger_Popup_Js.getInstance();
            popupInstance.showPopup(params, '', function (data) {
                data.find('.btn-success').hide();
            }, 'previewPDFMaker');
        }

        /*
                app.request.get({data: params}).then(function(err, data) {

                    app.helper.showModal(data, {
                        'cb' : function(modalContainer) {
                            //modalContainer.find('#use_common_template').select2();
                            //self.registerPDFPreviewActionsButtons(modalContainer,templateids,pdflanguage);
                            self.setMaxModalHeight(modalContainer,'iframe');
                        }
                    });

                    //app.helper.hideProgress();
                });*/
    },
    registerPDFMakerEvents: function (modalContainer) {
        var self = this;
        pdflanguageElement = modalContainer.find('[name=pdflanguage]');

        if (pdflanguageElement.length > 0) {

            var pdflanguage = pdflanguageElement.val();

            modalContainer.find('.generatePreviewPDF').on('click', function (e) {
                var element = jQuery(e.currentTarget);
                var templateid = element.data('templateid');
                self.showPDFPreviewModal(templateid, pdflanguage);
            });
        }
    },
    actualizeSelect2El: function (container, emailType) {
        let self = this,
            type = 'to' === emailType ? '' : emailType,
            sourceId = self.getEmailsSourceId(),
            preloadData = self.getPreloadAllData(emailType);

        if ('undefined' === typeof preloadData || null === preloadData) {
            let emailNamesList = self.getObjectFromString(container.find('[name="' + sourceId + emailType + 'MailNamesList"]').val()),
                emailInfo = self.getObjectFromString(container.find('[name="' + sourceId + emailType + 'emailinfo"]').val());

            preloadData = [];

            if (typeof emailInfo != 'undefined') {
                for (let key in emailInfo) {
                    if (emailNamesList.hasOwnProperty(key)) {
                        for (let i in emailNamesList[key]) {
                            let emailModule = emailNamesList[key][i].module,
                                emailId = emailNamesList[key][i].value,
                                recordId = emailNamesList[key][i].recordid,
                                preloadEmailInfo = {
                                    'eid': recordId + "|" + emailId + "|" + emailModule,
                                    'module': emailModule,
                                    'recordId': recordId,
                                    'id': emailId,
                                    'text': emailNamesList[key][i].label + ' <b>(' + emailId + ')</b>'
                                }

                            preloadData.push(preloadEmailInfo);
                        }
                    } else {
                        let emailId = emailInfo[key],
                            preloadEmailInfo = {
                                'eid': key,
                                'id': key,
                                'text': emailId
                            }

                        preloadData.push(preloadEmailInfo);
                    }
                }
            }

            self.setPreloadAllData(emailType, preloadData);
        }

        container.find('#email' + type + 'Field').select2('data', preloadData);
    },
    registerEmailSourcesList: function (container) {
        const self = this;

        container.find('.emailSourcesList').on('change', function (e) {
            let new_sourceid = jQuery(e.currentTarget).val(),
                composeEmailForm = self.getMassEmailForm();

            composeEmailForm.find('[name="selected_sourceid"]').val(new_sourceid);

            self.actualizeSelect2El(composeEmailForm, 'to');
            self.actualizeSelect2El(composeEmailForm, 'cc');
            self.actualizeSelect2El(composeEmailForm, 'bcc');

            let ccLink = container.find('#ccLink'),
                ccContainer = container.find('.ccContainer'),
                bccLink = container.find('#bccLink'),
                bccContainer = container.find('.bccContainer'),
                emailCCFieldData = self.getPreloadAllData('cc'),
                ccHide = false;

            if ('undefined' !== typeof emailCCFieldData) {
                if (emailCCFieldData.length > 0) {
                    ccHide = true;
                }
            }

            if (ccHide) {
                ccContainer.removeClass('hide');
                ccLink.hide();
            } else {
                ccContainer.addClass('hide');
                ccLink.removeClass('hide');
                ccLink.show();
            }

            let emailBCCFieldData = self.getPreloadAllData('bcc'),
                bccHide = false;

            if ('undefined' !== typeof emailBCCFieldData) {
                if (emailBCCFieldData.length > 0) {
                    ccHide = true;
                }
            }

            if (bccHide) {
                bccContainer.removeClass('hide');
                bccLink.hide();
            } else {
                bccContainer.addClass('hide');
                bccLink.removeClass('hide');
                bccLink.show();
            }

            self.checkHiddenStatusofCcandBcc();
        });
    },
    registerIncludeSignatureEvent: function (container) {
        var self = this;

        var ckEditorInstance = self.getckEditorInstance();
        var CkEditor = ckEditorInstance.getCkEditorInstanceFromName();

        var params = {
            module: 'EMAILMaker',
            action: 'IndexAjax',
            mode: 'getUserSignature'
        };

        container.find('.includeSignature').on('click', function (e) {
            app.helper.showProgress();
            app.request.post({'data': params}).then(
                function (err, response) {
                    app.helper.hideProgress();
                    if (err === null) {
                        var result = response.success;
                        if (result == true) {
                            CkEditor.insertHtml(response.signature);
                        }
                    }
                }
            );
        });
    },
    getModalNewHeight: function (modalContainer) {

        var modalHeaderHeight = modalContainer.find('.modal-header').height();
        var windowHeight = jQuery(window).height();
        var modalFooterHeight = modalContainer.find('.modal-footer').height();
        return windowHeight - modalHeaderHeight - modalFooterHeight - 100;
    },
    loadCkEditor: function (textAreaElement, container) {
        var ckEditorInstance = this.getckEditorInstance();
        var new_height = this.getModalNewHeight(container);

        var topContentHeight = container.find('.topContent').height();
        new_height = new_height - topContentHeight - 180;

        ckEditorInstance.loadCkEditor(textAreaElement, {'height': (new_height)});
    },
    registerSaveDraftOrSendEmailEvent: function () {
        var self = this;
        var form = this.getMassEmailForm();
        form.on('click', '#sendEmail, #saveDraft', function (e) {
            var targetName = jQuery(e.currentTarget).attr('name');
            if (targetName === 'savedraft') {
                jQuery('#flag').val(self.saved);
            } else {
                jQuery('#flag').val(self.sent);
            }
            var params = {
                submitHandler: function (form) {
                    form = jQuery(form);
                    app.helper.hideModal();
                    app.helper.showProgress();
                    if (CKEDITOR.instances['description']) {
                        form.find('#description').val(CKEDITOR.instances['description'].getData());
                    }
                    var data = new FormData(form[0]);
                    var postParams = {
                        data: data,
                        // jQuery will set contentType = multipart/form-data based on data we are sending
                        contentType: false,
                        // we don’t want jQuery trying to transform file data into a huge query string, we want raw data to be sent to server
                        processData: false
                    };

                    app.request.post(postParams).then(function (err, data) {
                        app.helper.hideProgress();
                        if (typeof data != 'undefined') {
                            var ele = jQuery(data);
                            var success = ele.find('.mailSentSuccessfully');
                            if (success.length <= 0) {
                                app.helper.showModal(data);
                            } else {
                                app.event.trigger('post.mail.sent', data);
                            }
                        } else {
                            app.helper.showErrorNotification({'message': err['message']});
                        }
                    });
                }
            };
            form.vtValidate(params);
        });
    },
    registerMultiFile: function() {
        const self = this;

        jQuery('#multiFile').MultiFile({
            list: '#attachments',
            'afterFileSelect': function (element, value, master_element) {
                let masterElement = master_element,
                    newElement = jQuery(masterElement.current);

                newElement.addClass('removeNoFileChosen');
                self.fileAfterSelectHandler(element, value, master_element);
            },
            'afterFileRemove': function (element, value, master_element) {
                if (jQuery('#attachments').is(':empty')) {
                    jQuery('.MultiFile,.MultiFile-applied').removeClass('removeNoFileChosen');
                }

                self.removeAttachmentFileSizeByElement(jQuery(element));
            }
        });
    },
    registerLoadCKEditor(container) {
        let descriptionElement = $('#description'),
            isCkeditorApplied = descriptionElement.data('isCkeditorApplied');

        if (true !== isCkeditorApplied) {
            this.loadCkEditor(descriptionElement.data('isCkeditorApplied', true), container);
        }
    },
    registerEventDocumentsListClick: function () {
        const self = this;

        app.event.on('post.DocumentsList.click', function (event, data) {
            let responseData = JSON.parse(data);

            jQuery('.popupModal').modal('hide');

            for (let id in responseData) {
                let selectedDocumentId = id,
                    selectedFileName = responseData[id].info['filename'],
                    selectedFileSize = responseData[id].info['filesize'],
                    response = self.writeDocumentIds(selectedDocumentId)

                if (response) {
                    let attachmentElement = self.getDocumentAttachmentElement(selectedFileName, id, selectedFileSize);
                    //TODO handle the validation if the size exceeds 5mb before appending.
                    jQuery(attachmentElement).appendTo(jQuery('#attachments'));
                    jQuery('.MultiFile-applied,.MultiFile').addClass('removeNoFileChosen');
                    self.setDocumentsFileSize(selectedFileSize);
                }
            }
        });
    },
    registerEventEmailTemplateListClick: function() {
        const self = this;

        app.event.on('post.EmailTemplateList.click', function (event, data) {
            let responseData = JSON.parse(data);
            jQuery('.popupModal').modal('hide');

            let ckEditorInstance = self.getckEditorInstance(),
                selectedTemplateBody;

            for (let id in responseData) {
                let data = responseData[id],
                    DataInfo = data['info'],
                    subject = jQuery('<div/>').html(DataInfo['subject']).text();

                ckEditorInstance.loadContentsInCkeditor(DataInfo['body']);
                jQuery('#subject').val(subject);
                selectedTemplateBody = responseData[id].info;
            }

            let sourceModule = jQuery('[name=source_module]').val(),
                showWarning = false;

            if (typeof selectedTemplateBody === 'string') {
                let tokenDataPair = selectedTemplateBody.split('$');

                for (let i = 0; i < tokenDataPair.length; i++) {
                    let module = tokenDataPair[i].split('-'),
                        pattern = /^[A-z]+$/;

                    if (pattern.test(module[0])) {
                        if (!(module[0] === sourceModule.toLowerCase() || 'users' === module[0] || 'custom' === module[0])) {
                            showWarning = true;
                        }
                    }
                }
            }

            if (showWarning) {
                jQuery('#emailTemplateWarning').removeClass('hide');
            } else {
                jQuery('#emailTemplateWarning').addClass('hide');
            }
        });

    },
    registerEmailTemplateWarning: function() {
        jQuery('#emailTemplateWarning .alert-warning .close').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            jQuery('#emailTemplateWarning').addClass('hide');
        });
    },
    registerModalHeight: function(container) {
        let params = {
            setHeight: (jQuery(window).height() - container.find('.modal-header').height() - container.find('.modal-footer').height() - 100) + 'px'
        };

        app.helper.showVerticalScroll(container.find('.modal-body'), params);
    },
    registerEvents: function () {
        const container = this.getMassEmailForm();

        if (container.length) {
            this.registerCcAndBccEvents();
            this.registerPDFMakerEvents(container);
            this.registerPreventFormSubmitEvent();
            this.registerAutoCompleteFields(container, 'to');
            this.registerAutoCompleteFields(container, 'cc');
            this.registerAutoCompleteFields(container, 'bcc');
            this.registerEmailSourcesList(container);
            this.registerMultiFile();
            this.registerRemoveAttachmentEvent();
            this.registerBrowseCrmEvent();
            this.calculateUploadFileSize();
            this.registerSaveDraftOrSendEmailEvent();
            this.registerLoadCKEditor(container);
            this.registerSelectEmailTemplateEvent();
            this.registerEventsForToField();
            this.registerEventForRemoveCustomAttachments();
            this.registerIncludeSignatureEvent(container);
            this.registerEventDocumentsListClick();
            this.registerEmailTemplateWarning();
            this.registerEventEmailTemplateListClick();
            this.registerModalHeight(container);
        }
    },
    getEmailsSourceId: function (mailInfo) {
        var mailInfoElement = this.getMassEmailForm().find('[name="selected_sourceid"]');
        return mailInfoElement.val();
    },
    addCCEmailAddressData: function (mailInfo) {
        var sid = this.getEmailsSourceId();
        var mailInfoElement = this.getMassEmailForm().find('[name="' + sid + 'ccemailinfo"]');
        var existingCCMailInfo = JSON.parse(mailInfoElement.val());
        if (typeof existingCCMailInfo.length != 'undefined') {
            existingCCMailInfo = {};
        }
        //If same record having two different email id's then it should be appended to
        //existing email id
        if (existingCCMailInfo.hasOwnProperty(mailInfo.eid) === true) {
            var existingValues = existingCCMailInfo[mailInfo.eid];
            var newValue = new Array(mailInfo.name);
            existingCCMailInfo[mailInfo.eid] = jQuery.merge(existingValues, newValue);
        } else {
            existingCCMailInfo[mailInfo.eid] = new Array(mailInfo.name);
        }
        mailInfoElement.val(JSON.stringify(existingCCMailInfo));
    },
    addBCCEmailAddressData: function (mailInfo) {
        var sid = this.getEmailsSourceId();
        var mailInfoElement = this.getMassEmailForm().find('[name="' + sid + 'bccemailinfo"]');
        var existingBCCMailInfo = JSON.parse(mailInfoElement.val());
        if (typeof existingBCCMailInfo.length != 'undefined') {
            existingBCCMailInfo = {};
        }
        //If same record having two different email id's then it should be appended to
        //existing email id
        if (existingBCCMailInfo.hasOwnProperty(mailInfo.eid) === true) {
            var existingValues = existingBCCMailInfo[mailInfo.eid];
            var newValue = new Array(mailInfo.name);
            existingBCCMailInfo[mailInfo.eid] = jQuery.merge(existingValues, newValue);
        } else {
            existingBCCMailInfo[mailInfo.eid] = new Array(mailInfo.name);
        }
        mailInfoElement.val(JSON.stringify(existingBCCMailInfo));
    }
});


