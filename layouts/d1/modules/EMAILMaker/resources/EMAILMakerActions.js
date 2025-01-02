/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var EMAILMaker_Actions_Js */
jQuery.Class("EMAILMaker_Actions_Js", {
    templatesElements: {},
    massEmailForm: false,

    showOtherEmailsSelect: function (container, type) {
        container.find('#' + type + 'ccLinkContent').addClass('hide');
        container.find('.' + type + 'ccContent').removeClass('hide');
    },
    showComposeEmailForm: function (params) {
        let aDeferred = jQuery.Deferred();

        app.request.post({data: params}).then(function (error, data) {
            app.helper.hideProgress();
            if (!error) {
                let modalContainer = app.helper.showModal(data, {
                    cb: function () {
                        let emailEditInstance = new ITS4YouEmails_MassEdit_Js();
                        emailEditInstance.registerEvents();
                    },
                    focus: false,
                });

                return aDeferred.resolve(modalContainer);
            }
        });
        return aDeferred.promise();
    },
    registerEmailFieldSelectionEvent: function (container) {
        let self = this,
            selectEmailForm = container.find("#SendEmailFormStep1");

        selectEmailForm.on('submit', function (e) {
            e.preventDefault();
            let form = jQuery(e.currentTarget);
            self.setSelectedPDFTemplates(form);

            let params = form.serialize();

            params = self.addEmailsToParams(params, form, '');
            params = self.addEmailsToParams(params, form, 'cc');
            params = self.addEmailsToParams(params, form, 'bcc');

            app.helper.showProgress();
            app.helper.hideModal().then(function () {
                self.showComposeEmailForm(params);
            });
        });
    },
    addEmailsToParams: function (params, form, type) {
        let fieldLists = [],
            listType = type ? 'field_lists_' + type : 'field_lists';

        form.find('#email' + type + 'Field').find('option:selected').each(function (i, ob) {
            fieldLists.push(jQuery(ob).val());
        });

        return params + '&' + listType + '=' + JSON.stringify(fieldLists);
    },
    setSelectedPDFTemplates: function (form) {
        let self = this,
            isPDFActive = form.find('#ispdfactive').val();

        if ('1' === isPDFActive) {
            self.updatePDFTemplateIds(form);
        }
    },
    getSelectElement: function(modalContainer, id) {
        return $('#' + id, modalContainer);
    },
    registerPDFTemplateInput: function (modalContainer) {
        let self = this,
            selectElement = self.getSelectElement(modalContainer, 'use_common_pdf_template');

        if (selectElement.length) {
            selectElement.select2();

            self.sortSelect2Element(modalContainer, 'use_common_pdf_template', $('#pdftemplateid').val().split(';'));

            $('#s2id_use_common_pdf_template ul.select2-choices', modalContainer).sortable();
        }
    },
    sortSelect2Element: function (modalContainer, selectId, sortValues) {
        let self = this,
            selectElement = self.getSelectElement(modalContainer, selectId),
            selectData = selectElement.select2('data'),
            selectDataUpdate = [];

        $.each(sortValues, function (sortIndex, sortId) {
            $.each(selectData, function (optionIndex, optionData) {
                if (sortId === optionData.id) {
                    selectDataUpdate.push(optionData);
                }
            });
        });

        selectElement.select2('data', selectDataUpdate);
    },
    getPDFTemplateIds: function (modalContainer) {
        let self = this,
            selectElement = self.getSelectElement(modalContainer, 'use_common_pdf_template'),
            select2Data = selectElement.select2('data'),
            templateIds = [];

        $.each(select2Data, function (index, data) {
            templateIds.push(data.id);
        });

        return templateIds;
    },
    inArray: function (value, values) {
        return -1 !== $.inArray(value, values)
    },
    updatePDFTemplateIds: function (modalContainer) {
        let self = this;

        $('#pdftemplateid').val(self.getPDFTemplateIds(modalContainer).join(';'));
    },
    registerPDFMakerEvents: function (modalContainer) {
        modalContainer.find('#addPDFMakerTemplate').on('click', function () {
            modalContainer.find('#EMAILMakerPDFTemplatesContainer').removeClass('hide');
            modalContainer.find('#EMAILMakerPDFTemplatesContainer').show();
            modalContainer.find('#EMAILMakerPDFTemplatesBtn').hide();
            modalContainer.find('#ispdfactive').val('1');
        });
        modalContainer.find('#removePDFMakerTemplate').on('click', function () {
            modalContainer.find('#EMAILMakerPDFTemplatesContainer').hide();
            modalContainer.find('#EMAILMakerPDFTemplatesBtn').show();
            modalContainer.find('#EMAILMakerPDFTemplatesBtn').removeClass('hide');
            modalContainer.find('#ispdfactive').val('0');
        });
    },

    getListViewPopup: function () {
        this.emailmaker_sendMail();
    },
    getDetailViewPopup: function () {
        this.emailmaker_sendMail('', '', '', false);
    },
    getRelationViewPopup: function () {
        this.emailmaker_sendMail('', '', '', true);
    },
    getMoreParams: function () {
        let forview_val = app.view(),
            params;

        if ('Detail' === forview_val) {
            params = {
                selected_ids: [app.getRecordId()]
            };

        } else if ('List' === forview_val) {
            let listInstance = this.getListInstance();

            if ('object' === typeof listInstance) {
                if (500 < listInstance.getSelectedRecordCount()) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_MASS_EDIT_LIMIT')});
                }

                params = listInstance.getListSelectAllParams(true);
            } else {
                params = {};
            }
        }
        return params;
    },
    getSelectedTab: function () {
        var tabContainer = this.getTabContainer();
        return tabContainer.find('li.active');
    },
    getAllTabs: function () {
        var tabContainer = this.getTabContainer();
        return tabContainer.find('li');
    },
    getTabContainer: function () {
        return jQuery('div.related-tabs');
    },
    getRelatedModuleName: function () {
        return jQuery('.relatedModuleName').val();
    },
    emailmaker_sendMail: function (pdftemplateid, pdflanguage, pid, forCampaigns) {
        let self = this,
            source_module = app.getModuleName(),
            forview_val = app.view();

        app.helper.checkServerConfig('EMAILMaker').then(function (data) {
            if (data === true) {
                let postData = {
                    'module': 'EMAILMaker',
                    'view': 'IndexAjax',
                    'mode': 'showComposeEmailForm',
                    'step': 'step1',
                    'pid': pid,
                    'sourceModule': source_module,
                    'selecttemplates': 'true',
                    'forview': forview_val
                };

                let moreParams = self.getMoreParams();

                jQuery.extend(postData, moreParams);

                if (forCampaigns) {
                    let selectedTabElement = self.getSelectedTab(),
                        relatedModuleName = self.getRelatedModuleName(),
                        relatedController = new Campaigns_RelatedList_Js(app.getRecordId(), app.getModuleName(), selectedTabElement, relatedModuleName),
                        selectedIds = relatedController.readSelectedIds();

                    if (selectedIds.length) {
                        postData['cid'] = app.getRecordId();
                        postData['sourceModule'] = relatedModuleName;
                        postData['forview'] = 'List';
                        postData['selected_ids'] = selectedIds;
                    } else {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_PLEASE_SELECT_ONE_RECORD')});
                        return false;
                    }
                }

                if (pdftemplateid) {
                    postData['pdftemplateid'] = pdftemplateid;
                }
                if (pdflanguage) {
                    postData['pdflanguage'] = pdflanguage;
                }

                if ('function' === typeof Vtiger_List_Js) {
                    let listViewInstance = new Vtiger_List_Js();

                    if ('function' === typeof listViewInstance.getListSearchParams) {
                        postData['search_params'] = JSON.stringify(listViewInstance.getListSearchParams());
                    }
                }

                app.helper.showProgress();
                app.request.post({'data': postData}).then(
                    function (error, response) {
                        if (!error) {
                            app.helper.hideProgress();
                            app.helper.showModal(response, {
                                'cb': function (modalContainer) {
                                    let templateElement = modalContainer.find('#use_common_email_template');

                                    if (templateElement.length > 0) {
                                        if (templateElement.is("select")) {
                                            vtUtils.showSelect2ElementView(templateElement);
                                        }
                                    }

                                    let emailTemplateLanguageElement = modalContainer.find('#email_template_language');

                                    if (emailTemplateLanguageElement.length > 0) {
                                        if (emailTemplateLanguageElement.is('select')) {
                                            vtUtils.showSelect2ElementView(emailTemplateLanguageElement);
                                        }
                                    }

                                    vtUtils.showSelect2ElementView(modalContainer.find('.emailFieldSelects'));

                                    modalContainer.find('#ccLink').on('click', function () {
                                        self.showOtherEmailsSelect(modalContainer, '');
                                    });
                                    modalContainer.find('#bccLink').on('click', function () {
                                        self.showOtherEmailsSelect(modalContainer, 'b');
                                    });

                                    self.registerEmailFieldSelectionEvent(modalContainer);
                                    self.registerPDFTemplateInput(modalContainer);
                                    self.registerPDFMakerEvents(modalContainer);
                                }
                            });
                        }
                    }
                );
            } else {
                alert(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
            }
        });
    },
    getListInstance: function () {
        return window.app.controller();
    },
}, {
    getLinkKey: function () {
        var link_key = '';
        var tabContainer = jQuery('div.related-tabs');
        if (typeof tabContainer != 'undefined') {
            var active_tab = tabContainer.find('li.active');
            if (typeof active_tab != 'undefined') {
                var link_key = active_tab.data('link-key');
                if (typeof link_key == 'undefined') {
                    link_key = '';
                }
            }
        }
        return link_key;
    },

    addButtons: function (container, forCampaigns) {
        if (!container.find('#EMAILMakerContentDiv').length) {
            let recordId = app.getRecordId(),
                source_module = app.getModuleName(),
                view = app.view(),
                params = {
                    module: 'EMAILMaker',
                    source_module: source_module,
                    view: 'GetEMAILActions',
                    record: recordId,
                    mode: 'getButtons',
                    forview: view
                };

            app.request.post({'data': params}).then(function (error, data) {
                if (!error) {
                    if (data) {
                        container.append(data);
                        container.find('.selectEMAILTemplates').on('click', function () {
                            EMAILMaker_Actions_Js.emailmaker_sendMail('', '', '', forCampaigns);
                        });
                    }
                }
            });
        }
    },

    addRelatedButtons: function () {
        if ('Campaigns' === app.getModuleName()) {
            const sendEmailCampaignContainer = jQuery('.container-related-list-actions');

            if (sendEmailCampaignContainer.length > 0) {
                this.addButtons(sendEmailCampaignContainer, true);
            }
        }
    },

    registerRelatedListLoad: function () {
        var self = this;

        app.event.on('post.relatedListLoad.click', function (event, searchRow) {
            var linkKey = self.getLinkKey();

            if (linkKey != 'LBL_RECORD_DETAILS' && linkKey != 'LBL_RECORD_SUMMARY') {
                self.addRelatedButtons();
            }

        });
    },
    registerResizeable: function () {
        const element = this.getEmailActionContainer();

        if (element.length) {
            element.resizable({
                handles: 'e, w'
            });
        }
    },
    getEmailActionContainer: function() {
        return $('#sendEmailContainer, #composeEmailContainer');
    },
    registerDraggable: function () {
        const element = this.getEmailActionContainer();

        if (element.length) {
            element.draggable({handle: '.modal-header'});
        }
    },
    registerAjaxCompleteEvents: function() {
        const self = this;

        $(document).ajaxComplete(function (event, request, settings) {
            self.registerDraggable();
            self.registerResizeable();
        });
    },
    registerEvents: function () {
        this.addRelatedButtons();
        this.registerRelatedListLoad();
        this.registerAjaxCompleteEvents();
    }
});
jQuery(document).ready(function () {
    let instance = new EMAILMaker_Actions_Js();
    instance.registerEvents();
});
