/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var ITS4YouEmails_MassEdit_Js */
jQuery.Class('ITS4YouEmails_MassEdit_Js', {
    init: function () {
        this.preloadAllData = [];
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
            type = 'to' === emailType ? '' : emailType,
            fieldElement = container.find('#email' + type + 'Field');

        fieldElement.select2({
            dropdownParent: container.parents('.modal'),
            minimumInputLength: 3,
            theme: 'bootstrap-5',
            closeOnSelect: false,
            tokenSeparators: [','],
            dropdownCss: {'z-index': '10001'},
            multiple: true,
            tags: true,
            ajax: {
                url: 'index.php?module=EMAILMaker&action=IndexAjax&mode=SearchEmails',
                dataType: 'json',
                data: function (term, page) {
                    return {
                        'searchValue': term['term'],
                    };
                },
                processResults: function (data) {
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
                                childrenInfo.id = recordId + '|' + emailInfo[i].value + '|' + emailInfo[i].module;
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
            },
            insertTag: function (data, tag) {
                if (!lastResults.length) {
                    data.push(tag);
                }
            },
        }).on('select2:select', function (e) {
            let addedElement = e.params.data;

            if ('undefined' !== typeof addedElement) {
                let data = self.getSelect2ElementData(addedElement);

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
                        'id': data.emailid,
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
        }).on('select2:unselect select2:clear', function (e) {
            let removedElement = e.params.data;

            if (typeof removedElement != 'undefined') {
                let data = self.getSelect2ElementData(removedElement);

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

        self.actualizeSelect2El(container, emailType);
    },
    getSelect2ElementData: function (elementData) {
        let optionId = elementData.id,
            emailInfo = optionId ? elementData.id.split('|') : [],
            emailAddress = 1 === emailInfo.length ? emailInfo[0] : emailInfo[1];

        if (!elementData.recordId && $.isNumeric(emailInfo[0])) {
            elementData.recordId = emailInfo[0];
        }

        if (!elementData.module && emailInfo[2]) {
            elementData.module = emailInfo[2];
        }

        let data = {
            'eid': elementData.recordId + "|" + emailAddress + "|" + elementData.module,
            'id': elementData.recordId,
            'name': elementData.text,
            'emailid': emailAddress,
        }

        if (typeof elementData.recordId == 'undefined') {
            data.eid = "email|" + emailAddress + "|";
        }

        return data;
    },
    removeFromEmailAddressData: function (emailType, mailInfo) {
        let self = this,
            sid = self.getEmailsSourceId(),
            mailInfoElement = self.getMassEmailForm().find('[name="' + sid + emailType + 'emailinfo"]'),
            previousValue = self.getObjectFromString(mailInfoElement.val()),
            elementSize = previousValue[mailInfo.eid] ? previousValue[mailInfo.eid].length : 0,
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
    },

    removeFromEmails: function (mailType, mailInfo) {
        let element = this.getMassEmailForm().find('[name="' + mailType + '"]'),
            previousValue = JSON.parse(element.val()),
            updatedValue = {};

        $.each(previousValue, function (index, value) {
            if (index !== mailInfo.eid) {
                updatedValue[index] = value;
            }
        })

        element.val(JSON.stringify(updatedValue));
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
    },
    checkHiddenStatusofCcandBcc: function () {
        let ccLink = jQuery('#ccLink');
        let bccLink = jQuery('#bccLink');
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
                    'src_module': 'ITS4YouEmails',
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
            'text': object.name + ' (' + object.emailid + ')'
        };

        preloadData.push(emailInfo);

        self.setPreloadAllData('to', preloadData);
        container.find('#emailField').select2('data', preloadData);

        let toEmailField = container.find('.sourceField'),
            toEmailFields = toEmailField.val() ? toEmailField.val().split(',') : [];

        toEmailFields.push(object.emailid)
        toEmailField.val(toEmailFields.join(','));
    },
    showPDFPreviewModal: function (templateId, templateLanguage) {
        let self = this,
            view = app.view(),
            recordId = 'Detail' === view ? app.getRecordId() : self.getEmailsSourceId(),
            forView = 'Detail';

        if (recordId) {
            let params = {
                    module: 'PDFMaker',
                    source_module: app.getModuleName(),
                    formodule: app.getModuleName(),
                    forview: forView,
                    pdftemplateid: templateId,
                    language: templateLanguage,
                    view: 'IndexAjax',
                    mode: 'getPreview',
                    hidebuttons: 'true',
                    record: recordId
                },
                popupInstance = Vtiger_Popup_Js.getInstance();

            popupInstance.showPopup(params, '', function (data) {
                data.find('.btn-success').hide();
            }, 'previewPDFMaker');
        }
    },
    registerPDFMakerEvents: function (modalContainer) {
        let self = this,
            languageElement = modalContainer.find('[name="pdf_template_language"]');

        if (languageElement.length > 0) {

            let templateLanguage = languageElement.val();

            modalContainer.find('.generatePreviewPDF').on('click', function (e) {
                let templateId = jQuery(e.currentTarget).data('templateid');

                self.showPDFPreviewModal(templateId, templateLanguage);
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
                                    'text': emailNamesList[key][i].label + ' (' + emailId + ')',
                                    'selected': true,
                                }

                            preloadData.push(preloadEmailInfo);
                        }
                    } else {
                        let emailId = emailInfo[key],
                            preloadEmailInfo = {
                                'eid': key,
                                'id': key,
                                'text': emailId,
                                'selected': true,
                            }

                        preloadData.push(preloadEmailInfo);
                    }
                }
            }

            self.setPreloadAllData(emailType, preloadData);
        }

        let element = container.find('#email' + type + 'Field');

        element.empty();

        $.each(preloadData, function (index, data) {
            let preloadDataId = data.eid,
                newOption = new Option(data.text, preloadDataId, true, true);

            element.append(newOption);
        });

        element.trigger('change');
    },
    registerEmailSourcesList: function (container) {
        const self = this;

        container.on('change', '.emailSourcesList', function (e) {
            let newSourceId = jQuery(e.currentTarget).val(),
                composeEmailForm = self.getMassEmailForm();

            composeEmailForm.find('[name="selected_sourceid"]').val(newSourceId);

            self.actualizeSelect2El(composeEmailForm, 'to');
            self.actualizeSelect2El(composeEmailForm, 'cc');
            self.actualizeSelect2El(composeEmailForm, 'bcc');

            self.actualizeCCVisibility();
            self.actualizeBCCVisibility();
            self.checkHiddenStatusofCcandBcc();
        });
    },
    actualizeBCCVisibility: function(container) {
        let self = this,
            bccLink = container.find('#bccLink'),
            bccContainer = container.find('.bccContainer'),
            emailBCCFieldData = self.getPreloadAllData('bcc'),
            bccHide = 'undefined' !== typeof emailBCCFieldData && emailBCCFieldData.length > 0;

        if (bccHide) {
            bccContainer.removeClass('hide');
            bccLink.hide();
        } else {
            bccContainer.addClass('hide');
            bccLink.removeClass('hide');
            bccLink.show();
        }
    },
    actualizeCCVisibility: function (container) {
        let self = this,
            ccLink = container.find('#ccLink'),
            ccContainer = container.find('.ccContainer'),
            emailCCFieldData = self.getPreloadAllData('cc'),
            ccHide = 'undefined' !== typeof emailCCFieldData && emailCCFieldData.length > 0;

        if (ccHide) {
            ccContainer.removeClass('hide');
            ccLink.hide();
        } else {
            ccContainer.addClass('hide');
            ccLink.removeClass('hide');
            ccLink.show();
        }
    },
    registerIncludeSignatureEvent: function (container) {
        let self = this,
            ckEditorInstance = self.getCkEditorInstance(),
            CkEditor = ckEditorInstance.getCkEditorInstanceFromName(),
            params = {
                module: 'ITS4YouEmails',
                action: 'IndexAjax',
                mode: 'getUserSignature'
            };

        container.find('.includeSignature').on('click', function (e) {
            app.helper.showProgress();
            app.request.post({'data': params}).then(
                function (err, response) {
                    app.helper.hideProgress();
                    if (err === null) {
                        let result = response.success;
                        if (result == true) {
                            CkEditor.insertHtml(response.signature);
                        }
                    }
                }
            );
        });
    },
    getModalNewHeight: function (modalContainer) {
        let modalHeaderHeight = modalContainer.find('.modal-header').height(),
            windowHeight = jQuery(window).height(),
            modalFooterHeight = modalContainer.find('.modal-footer').height();

        return windowHeight - modalHeaderHeight - modalFooterHeight - 100;
    },
    loadCkEditor: function (textAreaElement, container) {
        let ckEditorInstance = this.getCkEditorInstance(),
            new_height = this.getModalNewHeight(container),
            topContentHeight = container.find('.topContent').height();

        new_height = new_height - topContentHeight - 180;

        ckEditorInstance.loadCkEditor(textAreaElement, {'height': (new_height)});
    },
    registerSaveDraftOrSendEmailEvent: function () {
        let self = this,
            form = this.getMassEmailForm();

        form.on('click', '#sendEmail, #saveDraft', function (e) {
            let targetName = jQuery(e.currentTarget).attr('name');

            if ('savedraft' === targetName) {
                jQuery('#flag').val(self.saved);
            } else {
                jQuery('#flag').val(self.sent);
            }

            let params = {
                submitHandler: function (form) {
                    form = jQuery(form);

                    if (CKEDITOR.instances['description']) {
                        form.find('#description').val(CKEDITOR.instances['description'].getData());
                    }

                    let data = new FormData(form[0]),
                        postParams = {
                            data: data,
                            // jQuery will set contentType = multipart/form-data based on data we are sending
                            contentType: false,
                            // we don’t want jQuery trying to transform file data into a huge query string, we want raw data to be sent to server
                            processData: false
                        };

                    app.helper.hideModal();
                    app.helper.showProgress();
                    app.request.post(postParams).then(function (error, data) {
                        app.helper.hideProgress();
                        if (!error) {
                            let element = jQuery(data);

                            if (element.is('.mailSentSuccessfully') || element.find('.mailSentSuccessfully').length) {
                                app.helper.showModal(data);
                            } else {
                                app.event.trigger('post.mail.sent', data);
                            }
                        } else {
                            app.helper.showErrorNotification({'message': error['message']});
                        }
                    });
                }
            };
            form.vtValidate(params);
        });
    },
    registerMultiFile: function () {
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
    removeAttachmentFileSizeByElement : function(element) {
        this.attachmentsFileSize -= element.get(0).files[0].size;
    },
    registerLoadCKEditor(container) {
        let descriptionElement = $('#description'),
            isCkeditorApplied = descriptionElement.data('isCkeditorApplied');

        if (true !== isCkeditorApplied) {
            descriptionElement.data('isCkeditorApplied', true);

            this.loadCkEditor(descriptionElement, container);
        }
    },
    registerEventDocumentsListClick: function () {
        const self = this;

        app.event.off('post.DocumentsList.click');
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

                    jQuery(attachmentElement).appendTo(jQuery('#attachments'));
                    jQuery('.MultiFile-applied,.MultiFile').addClass('removeNoFileChosen');
                    self.setDocumentsFileSize(selectedFileSize);
                }
            }
        });
    },
    registerEventEmailTemplateListClick: function () {
        const self = this;

        app.event.on('post.EmailTemplateList.click', function (event, data) {
            let responseData = JSON.parse(data);
            jQuery('.popupModal').modal('hide');

            let ckEditorInstance = self.getCkEditorInstance(),
                selectedTemplateBody;

            for (let id in responseData) {
                let data = responseData[id],
                    DataInfo = data['info'],
                    subject = jQuery('<div/>').html(DataInfo['subject']).text(),
                    body = DataInfo['body'];

                ckEditorInstance.loadContentsInCkeditor(body);
                $('#subject').val(subject);
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
    registerEmailTemplateWarning: function () {
        jQuery('#emailTemplateWarning .alert-warning .close').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            jQuery('#emailTemplateWarning').addClass('hide');
        });
    },
    registerModalHeight: function (container) {
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
            this.registerBrowseRecordEvent();
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
        let mailInfoElement = this.getMassEmailForm().find('[name="selected_sourceid"]');
        return mailInfoElement.val();
    },
    addCCEmailAddressData: function (mailInfo) {
        let sid = this.getEmailsSourceId(),
            mailInfoElement = this.getMassEmailForm().find('[name="' + sid + 'ccemailinfo"]'),
            existingCCMailInfo = JSON.parse(mailInfoElement.val());

        if (typeof existingCCMailInfo.length != 'undefined') {
            existingCCMailInfo = {};
        }
        //If same record having two different email id's then it should be appended to
        //existing email id
        if (existingCCMailInfo.hasOwnProperty(mailInfo.eid) === true) {
            let existingValues = existingCCMailInfo[mailInfo.eid],
                newValue = new Array(mailInfo.name);

            existingCCMailInfo[mailInfo.eid] = jQuery.merge(existingValues, newValue);
        } else {
            existingCCMailInfo[mailInfo.eid] = new Array(mailInfo.name);
        }

        mailInfoElement.val(JSON.stringify(existingCCMailInfo));
    },
    addBCCEmailAddressData: function (mailInfo) {
        let sid = this.getEmailsSourceId(),
            mailInfoElement = this.getMassEmailForm().find('[name="' + sid + 'bccemailinfo"]'),
            existingBCCMailInfo = JSON.parse(mailInfoElement.val());

        if (typeof existingBCCMailInfo.length != 'undefined') {
            existingBCCMailInfo = {};
        }
        //If same record having two different email id's then it should be appended to
        //existing email id
        if (existingBCCMailInfo.hasOwnProperty(mailInfo.eid) === true) {
            let existingValues = existingBCCMailInfo[mailInfo.eid];
            let newValue = new Array(mailInfo.name);
            existingBCCMailInfo[mailInfo.eid] = jQuery.merge(existingValues, newValue);
        } else {
            existingBCCMailInfo[mailInfo.eid] = new Array(mailInfo.name);
        }

        mailInfoElement.val(JSON.stringify(existingBCCMailInfo));
    },
    registerBrowseRecordEvent: function () {
        const self = this;

        jQuery('#browseRecord').on('click', function (e) {
            let url = jQuery(e.currentTarget).data('url'),
                postParams = app.convertUrlToDataParams('index.php?' + url);

            app.request.post({data: postParams}).then(function (error, data) {
                self.showPopupModal(data);
                self.registerSelectRecordDocuments();
            });
        });
    },
    registerSelectRecordDocuments: function () {
        $('#recordDocuments').on('click', '.selectDocument', function () {
            let recordData = $(this).data(),
                data = {};

            data[recordData.id] = {info: recordData};

            app.event.trigger('post.DocumentsList.click', JSON.stringify(data));
        });
    },
    showPopupModal: function (data) {
        let popupModal = jQuery('.popupModal'),
            modal = jQuery('.myModal');

        if (data) {
            popupModal.remove();
            popupModal.unbind();

            jQuery('body').append(jQuery('<div id="popupModal" class="modal popupModal">' + data + '</div>'));
        }

        vtUtils.applyFieldElementsView(popupModal);

        popupModal = jQuery('.popupModal');
        popupModal.one('shown.bs.modal', function () {
            modal.css('opacity', .5);
            modal.unbind();
        });
        popupModal.one('hidden.bs.modal', function () {
            this.remove();

            modal.css('opacity', 1);
            modal.removeData("modal").modal(app.helper.defaultModalParams());
            modal.bind();
        });
        popupModal.modal('show');
    },
    getMassEmailForm: function () {
        if (false === this.massEmailForm) {
            this.massEmailForm = jQuery("#massEmailForm");
        }

        return this.massEmailForm;
    },
    registerCcAndBccEvents: function () {
        let thisInstance = this;
        jQuery('#ccLink').on('click', function (e) {
            jQuery('.ccContainer').removeClass("hide");
            jQuery(e.currentTarget).hide();
        });
        jQuery('#bccLink').on('click', function (e) {
            jQuery('.bccContainer').removeClass("hide");
            jQuery(e.currentTarget).hide();
        });
    },
    registerPreventFormSubmitEvent: function () {
        let form = this.getMassEmailForm();
        form.on('submit', function (e) {
            e.preventDefault();
        }).on('keypress', function (e) {
            if (e.which == 13) {
                e.preventDefault();
            }
        });
    },
    registerBrowseCrmEvent: function () {
        let self = this;

        jQuery('#browseCrm').on('click', function (e) {
            let url = jQuery(e.currentTarget).data('url'),
                postParams = app.convertUrlToDataParams('index.php?' + url);

            app.helper.showProgress();
            app.request.post({"data": postParams}).then(function (error, data) {
                app.helper.hideProgress();

                if (!error) {
                    self.showPopupModal(data);

                    app.event.trigger("post.Popup.Load", {"eventToTrigger": "post.DocumentsList.click"});
                }
            });
        });
    },
    calculateUploadFileSize: function () {
        let self = this,
            composeEmailForm = this.getMassEmailForm(),
            attachmentsList = composeEmailForm.find('#attachments'),
            attachments = attachmentsList.find('.customAttachment');

        jQuery.each(attachments, function () {
            let element = jQuery(this),
                fileSize = element.data('fileSize'),
                fileType = element.data('fileType');

            if ('file' === fileType) {
                self.setAttachmentsFileSizeBySize(fileSize);
            } else if ('document' === fileType) {
                self.setDocumentsFileSize(fileSize);
            }
        })
    },
    registerRemoveAttachmentEvent: function () {
        let thisInstance = this;
        this.getMassEmailForm().on('click', '.removeAttachment', function (e) {
            let currentTarget = jQuery(e.currentTarget);
            let id = currentTarget.data('id');
            let fileSize = currentTarget.data('fileSize');
            currentTarget.closest('.MultiFile-label').remove();
            thisInstance.removeDocumentsFileSize(fileSize);
            thisInstance.removeDocumentIds(id);
            if (jQuery('#attachments').is(':empty')) {
                jQuery('.MultiFile,.MultiFile-applied').removeClass('removeNoFileChosen');
            }
        });
    },
    getCkEditorInstance: function () {
        if (false === this.ckEditorInstance) {
            this.ckEditorInstance = new Vtiger_CkEditor_Js();
        }

        return this.ckEditorInstance;
    },
    registerSelectEmailTemplateEvent: function () {
        let thisInstance = this;
        jQuery("#selectEmailTemplate").on("click", function (e) {
            let url = "index.php?" + jQuery(e.currentTarget).data('url');
            let postParams = app.convertUrlToDataParams(url);
            app.request.post({data: postParams}).then(function (err, data) {
                if (err === null) {
                    thisInstance.showPopupModal(data);
                    app.event.trigger("post.Popup.Load", {"eventToTrigger": "post.EmailTemplateList.click"})
                }
            });
        });
    },
    registerEventForRemoveCustomAttachments: function () {
        let self = this,
            composeEmailForm = self.getMassEmailForm();

        composeEmailForm.on('click', '.removeAttachment', function () {
            let attachmentsContainer = composeEmailForm.find('[name="attachments"]'),
                attachmentsInfo = JSON.parse(attachmentsContainer.val()),
                documentsContainer = composeEmailForm.find('[name="documentids"]'),
                documentsInfo = JSON.parse(documentsContainer.val()),
                element = jQuery(this),
                imageContainer = element.closest('.MultiFile-label'),
                imageContainerData = imageContainer.data(),
                fileType = imageContainerData['fileType'],
                fileSize = imageContainerData['fileSize'],
                fileId = parseInt(imageContainerData['fileId']),
                fileDocumentId = parseInt(imageContainerData['documentId']);

            if ('document' === fileType) {
                self.removeDocumentsFileSize(fileSize);
            } else if ('file' === fileType) {
                self.removeAttachmentFileSizeBySize(fileSize);
            }

            attachmentsInfo = attachmentsInfo.filter(function (attachmentInfo) {
                return parseInt(attachmentInfo['fileid']) !== fileId
            });
            attachmentsContainer.val(JSON.stringify(attachmentsInfo));

            if (fileDocumentId) {
                documentsInfo = documentsInfo.filter(function (documentInfo) {
                    return parseInt(documentInfo) !== fileDocumentId
                });
                documentsContainer.val(JSON.stringify(documentsInfo));
            }

            imageContainer.remove();
        });
    },
    fileAfterSelectHandler: function (element, value, master_element) {
        let thisInstance = this;
        let mode = jQuery('[name="emailMode"]').val();
        let existingAttachment = JSON.parse(jQuery('[name="attachments"]').val());
        element = jQuery(element);
        thisInstance.setAttachmentsFileSizeByElement(element);
        let totalAttachmentsSize = thisInstance.getTotalAttachmentsSize();
        let maxUploadSize = thisInstance.getMaxUploadSize();
        if (totalAttachmentsSize > maxUploadSize) {
            app.helper.showAlertBox({message: app.vtranslate('JS_MAX_FILE_UPLOAD_EXCEEDS')});
            this.removeAttachmentFileSizeByElement(jQuery(element));
            master_element.list.find('.MultiFile-label:last').find('.MultiFile-remove').trigger('click');
        } else if ((mode != "") && (existingAttachment != "")) {
            let pattern = /\\/;
            let fileuploaded = value;
            jQuery.each(existingAttachment, function (key, value) {
                if ((value['attachment'] == fileuploaded) && !(value.hasOwnProperty("docid"))) {
                    let errorMsg = app.vtranslate("JS_THIS_FILE_HAS_ALREADY_BEEN_SELECTED") + fileuploaded;
                    app.helper.showAlertBox({message: app.vtranslate(errorMsg)});
                    thisInstance.removeAttachmentFileSizeByElement(jQuery(element), value);
                    master_element.list.find('.MultiFile-label:last').find('.MultiFile-remove').trigger('click');
                    return false;
                }
            })
        }
        return true;
    },
    setAttachmentsFileSizeByElement: function (element) {
        this.attachmentsFileSize += element.get(0).files[0].size;
    },
    writeDocumentIds: function (selectedDocumentId) {
        let thisInstance = this,
            selectedDocumentIds = jQuery('#documentIds').val();

        if (selectedDocumentIds) {
            selectedDocumentIds = JSON.parse(selectedDocumentIds);
        }

        let existingAttachment = thisInstance.checkIfExisitingAttachment(selectedDocumentId);

        if (!existingAttachment) {
            selectedDocumentIds.push(selectedDocumentId);
            jQuery('#documentIds').val(JSON.stringify(selectedDocumentIds));

            return true;
        } else {
            return false;
        }
    },
    checkIfExisitingAttachment: function (selectedDocumentId) {
        let documentExist,
            documentPresent,
            mode = jQuery('[name="emailMode"]').val(),
            selectedDocumentIds = jQuery('#documentIds').val(),
            existingAttachment = JSON.parse(jQuery('[name="attachments"]').val());

        if (mode && existingAttachment) {
            jQuery.each(existingAttachment, function (key, value) {
                if (value.hasOwnProperty('docid')) {
                    if (parseInt(value['docid']) === parseInt(selectedDocumentId)) {
                        documentExist = 1;
                        return false;
                    }
                }
            })

            if (selectedDocumentIds) {
                selectedDocumentIds = JSON.parse(selectedDocumentIds);
            }

            if (documentExist === 1 || jQuery.inArray(selectedDocumentId, selectedDocumentIds) !== -1) {
                documentPresent = 1;
            } else {
                documentPresent = 0;
            }
        } else if (selectedDocumentIds) {
            selectedDocumentIds = JSON.parse(selectedDocumentIds);

            if ((jQuery.inArray(selectedDocumentId, selectedDocumentIds) !== -1)) {
                documentPresent = 1;
            } else {
                documentPresent = 0;
            }
        }

        if (documentPresent === 1) {
            let errorMsg = app.vtranslate("JS_THIS_DOCUMENT_HAS_ALREADY_BEEN_SELECTED");
            app.helper.showErrorNotification({message: errorMsg});
            return true;
        } else {
            return false;
        }
    },
    getDocumentAttachmentElement: function (selectedFileName, id, selectedFileSize) {
        return '<div class="MultiFile-label" data-id=' + id + ' data-file-size=' + selectedFileSize + '><a href="#" class="removeAttachment cursorPointer me-2" ><i class="fa-solid fa-xmark"></i></a><span>' + selectedFileName + '</span></div>';
    },
    setDocumentsFileSize: function (documentSize) {
        this.documentsFileSize += parseFloat(documentSize);
    },
    removeDocumentsFileSize: function (documentSize) {
        this.documentsFileSize -= parseFloat(documentSize);
    },
    removeDocumentIds: function (removedDocumentId) {
        let documentIdsContainer = jQuery('#documentIds');
        let documentIdsArray = JSON.parse(documentIdsContainer.val());
        documentIdsArray.splice(jQuery.inArray('"' + removedDocumentId + '"', documentIdsArray), 1);
        documentIdsContainer.val(JSON.stringify(documentIdsArray));
    },
    getTotalAttachmentsSize: function () {
        return parseFloat(this.getAttachmentsFileSize()) + parseFloat(this.getDocumentsFileSize());
    },
    getAttachmentsFileSize: function () {
        return this.attachmentsFileSize;
    },
    getDocumentsFileSize: function () {
        return this.documentsFileSize;
    },
    getMaxUploadSize: function () {
        return jQuery('#maxUploadSize').val();
    },
});