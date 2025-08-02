/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_List_Js("MailManager_List_Js", {}, {

    getContainer: function () {
        return jQuery('.main-container');
    },

    loadFolders: function (folder) {
        app.helper.showProgress(app.vtranslate('JSLBL_Loading_Folders_Please_Wait') + '...');

        let self = this,
            params = {
                'module': app.getModuleName(),
                'view': 'Index',
                '_operation': 'folder',
                '_operationarg': 'getFoldersList'
            }

        app.request.post({"data": params}).then(function (error, responseData) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification(error)
                return;
            }

            self.getContainer().find('#folders_list').html(responseData);
            self.getContainer().find('#folders_list').mCustomScrollbar({
                setHeight: 550,
                autoExpandScrollbar: true,
                scrollInertia: 200,
                autoHideScrollbar: true,
                theme: "dark-3"
            });
            self.registerFolderClickEvent();
            if (folder) {
                self.openFolder(folder);
            } else {
                self.openFolder('INBOX');
            }
            self.registerAutoRefresh();
        });
    },

    registerAutoRefresh: function () {
        var self = this;
        var container = self.getContainer();
        var timeout = parseInt(container.find('#refresh_timeout').val());
        var folder = container.find('.mm_folder.active').data('foldername');
        if (timeout > 0) {
            setTimeout(function () {
                var thisInstance = new MailManager_List_Js();
                if (folder && typeof folder != "undefined") {
                    thisInstance.loadFolders(folder);
                } else {
                    thisInstance.loadFolders();
                }
            }, timeout);
        }
    },

    registerFolderClickEvent: function () {
        let self = this,
            container = self.getContainer();

        container.find('.mm_folder').click(function (e) {
            let folderElement = jQuery(e.currentTarget),
                folderName = folderElement.data('foldername');

            container.find('.mm_folder').each(function (i, ele) {
                jQuery(ele).removeClass('active');
            });
            folderElement.addClass('active');
            self.openFolder(folderName);
        });
    },

    registerComposeEmail: function () {
        var self = this;
        var container = self.getContainer();
        container.find('#mail_compose').click(function () {
            var params = {
                step: "step1",
                module: "MailManager",
                view: "MassActionAjax",
                mode: "showComposeEmailForm",
                selected_ids: "[]",
                excluded_ids: "[]"
            };
            self.openComposeEmailForm(null, params);
        });
    },

    registerSettingsEdit: function () {
        let self = this,
            container = this.getContainer();

        container.find('.mailbox_setting').click(function () {
            app.helper.showProgress(app.vtranslate('JSLBL_Loading_Please_Wait') + '...');
            let params = {
                    'module': 'MailManager',
                    'view': 'Index',
                    '_operation': 'settings',
                    '_operationarg': 'edit'
                },
                popupInstance = Vtiger_Popup_Js.getInstance();

            popupInstance.showPopup(params, '', function (data) {
                app.helper.hideProgress();
                self.handleSettingsEvents(data);
                self.registerDeleteMailboxEvent(data);
                self.registerSaveMailboxEvent(data);
                self.registerClientTokenActions(data);
            });
        });
    },

    handleSettingsEvents: function (data) {
        var settingContainer = jQuery(data);
        settingContainer.find('#serverType').on('change', function (e) {
            let element = jQuery(e.currentTarget),
                serverType = element.val(),
                useServer = '', useProtocol = '', useSSLType = '', useCert = '',
                oauth2Element = settingContainer.find('.oauth2_settings'),
                detailsElement = settingContainer.find('.settings_details'),
                additionalElement = settingContainer.find('.additional_settings'),
                passwordElement = settingContainer.find('.settings_password')

            oauth2Element.addClass('hide');
            additionalElement.addClass('hide');
            detailsElement.addClass('hide');
            passwordElement.addClass('hide');

            switch (serverType) {
                case 'gmail':
                    useServer = 'imap.gmail.com';
                    useProtocol = 'IMAP4';
                    useSSLType = 'ssl';
                    useCert = 'novalidate-cert';
                    detailsElement.removeClass('hide');
                    oauth2Element.removeClass('hide');
                    break;
                case 'yahoo':
                    useServer = 'imap.mail.yahoo.com';
                    useProtocol = 'IMAP4';
                    useSSLType = 'ssl';
                    useCert = 'novalidate-cert';
                    detailsElement.removeClass('hide');
                    passwordElement.removeClass('hide');
                    break;
                case 'fastmail':
                    useServer = 'mail.messagingengine.com';
                    useProtocol = 'IMAP4';
                    useSSLType = 'ssl';
                    useCert = 'novalidate-cert';
                    detailsElement.removeClass('hide');
                    passwordElement.removeClass('hide');
                    break;
                case 'other':
                    useServer = '';
                    useProtocol = 'IMAP4';
                    useSSLType = 'ssl';
                    useCert = 'novalidate-cert';
                    detailsElement.removeClass('hide');
                    additionalElement.removeClass('hide');
                    passwordElement.removeClass('hide');
                    break;
            }


            settingContainer.find('.refresh_settings').show();
            settingContainer.find('#_mbox_user').val('');
            settingContainer.find('#_mbox_pwd').val('');
            settingContainer.find('[name="_mbox_sent_folder"]').val('');
            settingContainer.find('[name="_mbox_proxy"]').val('');
            settingContainer.find('[name="_mbox_client_id"]').val('');
            settingContainer.find('[name="_mbox_client_secret"]').val('');
            settingContainer.find('[name="_mbox_client_token"]').val('');
            settingContainer.find('[name="_mbox_client_access_token"]').val('');
            settingContainer.find('.selectFolderValue').addClass('hide');
            settingContainer.find('.selectFolderDesc').removeClass('hide');
            if (useProtocol != '') {
                settingContainer.find('#_mbox_server').val(useServer);
                settingContainer.find('.mbox_protocol').each(function (i, node) {
                    if (jQuery(node).val() == useProtocol) {
                        jQuery(node).attr('checked', true);
                    }
                });
                settingContainer.find('.mbox_ssltype').each(function (i, node) {
                    if (jQuery(node).val() == useSSLType) {
                        jQuery(node).attr('checked', true);
                    }
                });
                settingContainer.find('.mbox_certvalidate').each(function (i, node) {
                    if (jQuery(node).val() == useCert) {
                        jQuery(node).attr('checked', true);
                    }
                });
            }
        });
    },

    registerDeleteMailboxEvent: function (data) {
        let settingContainer = jQuery(data);

        settingContainer.find('#deleteMailboxBtn').click(function (e) {
            e.preventDefault();
            app.helper.showProgress(app.vtranslate('JSLBL_Deleting') + '...');
            let params = {
                'module': 'MailManager',
                'view': 'Index',
                '_operation': 'settings',
                '_operationarg': 'remove'
            };

            app.request.post({"data": params}).then(function (error, responseData) {
                app.helper.hideProgress();

                if (responseData.status) {
                    window.location.reload();
                }
            });
        });
    },

    registerSaveMailboxEvent: function (data) {
        let settingContainer = jQuery(data);
        settingContainer.find('#saveMailboxBtn').click(function (e) {
            e.preventDefault();
            let form = settingContainer.find('#EditView'),
                data = form.serializeFormData(),
                params = {
                    position: {
                        'my': 'bottom left',
                        'at': 'top left',
                        'container': jQuery('#EditView')
                    }
                };
            let errorMsg = app.vtranslate('JS_REQUIRED_FIELD');

            if (!data['_mbox_server']) {
                vtUtils.showValidationMessage(settingContainer.find('#_mbox_server'), errorMsg, params);
                return false;
            } else {
                vtUtils.hideValidationMessage(settingContainer.find('#_mbox_server'));
            }
            if (!data['_mbox_user']) {
                vtUtils.showValidationMessage(settingContainer.find('#_mbox_user'), errorMsg, params);
                return false;
            } else {
                vtUtils.hideValidationMessage(settingContainer.find('#_mbox_user'));
            }
            if (!data['_mbox_pwd'] && !data['_mbox_client_id']) {
                vtUtils.showValidationMessage(settingContainer.find('#_mbox_pwd'), errorMsg, params);
                vtUtils.showValidationMessage(settingContainer.find('#_mbox_client_id'), errorMsg, params);
                return false;
            } else {
                vtUtils.hideValidationMessage(settingContainer.find('#_mbox_pwd'));
            }

            app.helper.showProgress(app.vtranslate('JSLBL_Saving_And_Verifying') + '...');

            params = {
                'module': 'MailManager',
                'view': 'Index',
                '_operation': 'settings',
                '_operationarg': 'save'
            };

            jQuery.extend(params, data);
            app.request.post({"data": params}).then(function (error, responseData) {
                app.helper.hideModal();
                app.helper.hideProgress();
                if (error) {
                    app.helper.showAlertNotification({'message': error.message});
                } else if (responseData.mailbox) {
                    window.location.reload();
                }
            });
        });
    },
    openFolder: function (folderName, page, query, type) {
        app.helper.showProgress(app.vtranslate('JSLBL_Loading_Mails_Please_Wait') + '...');

        let self = this,
            container = self.getContainer();

        page = page ?? 1;

        vtUtils.hideValidationMessage(container.find('#mailManagerSearchbox'));

        let params = {
            'module': 'MailManager',
            'view': 'Index',
            '_operation': 'folder',
            '_operationarg': 'open',
            '_folder': folderName,
            '_page': page
        };

        if (query) {
            params['q'] = query;
        }

        if (type) {
            params['type'] = type;
        }

        app.request.post({"data": params}).then(function (error, responseData) {
            container.find('#mailPreviewContainer').removeClass('hide');
            container.find('#mails_container').html(responseData);
            app.helper.hideProgress();
            self.registerMoveMailDropdownClickEvent();
            self.registerMailCheckBoxClickEvent();

            self.registerMainCheckboxClickEvent();
            self.registerPrevPageClickEvent();
            self.registerNextPageClickEvent();
            self.registerSearchEvent();
            self.registerFolderMailDeleteEvent();
            self.registerMoveMailToFolder();
            self.registerMarkMessageAsUnread();
            self.registerMailClickEvent();
            self.registerMarkMessageAsRead();
            self.clearPreviewContainer();
            container.find('#searchType').trigger('change');
        });
    },
    registerFolderMailDeleteEvent: function () {
        let self = this,
            container = self.getContainer();

        container.find('#mmDeleteMail').click(function (e) {
            let folder = jQuery(e.currentTarget).data('folder'),
                mUIds = [];

            container.find('.mailCheckBox').each(function (i, ele) {
                let element = jQuery(ele);
                if (element.is(":checked")) {
                    mUIds.push(self.getUid(element.closest('.mailEntry')));
                }
            });

            if (mUIds.length <= 0) {
                app.helper.showAlertBox({message: app.vtranslate('JSLBL_NO_EMAILS_SELECTED')});
                return false;
            } else {
                app.helper.showConfirmationBox({'message': app.vtranslate('LBL_DELETE_CONFIRMATION')}).then(function () {
                    app.helper.showProgress(app.vtranslate("JSLBL_Deleting") + "...");
                    let params = {
                        'module': 'MailManager',
                        'view': 'Index',
                        '_operation': 'mail',
                        '_operationarg': 'delete',
                        '_folder': folder,
                        '_muid': mUIds.join(',')
                    };

                    app.request.post({data: params}).then(function (err, data) {
                        self.openFolder(folder);
                        if (data.status) {
                            app.helper.showSuccessNotification({'message': app.vtranslate('JSLBL_MAILS_DELETED')});

                            self.updateUnreadCount('-', self.getUnreadCount(mUIds), folder);
                            self.updatePagingCount(mUIds.length);

                            for (let i = 0; i < mUIds.length; i++) {
                                container.find('#mmMailEntry_' + mUIds[i]).remove();
                            }

                            let openedUid = self.getUid(container);

                            if (jQuery.inArray(openedUid, mUIds) !== -1) {
                                self.clearPreviewContainer();
                            }
                        }
                    });
                });
            }
        });
    },

    updatePagingCount: function (deletedCount) {
        var pagingDataElement = jQuery('.pageInfoData');
        var pagingElement = jQuery('.pageInfo');
        if (pagingDataElement.length != 0) {
            var total = pagingDataElement.data('total');
            var start = pagingDataElement.data('start');
            var end = pagingDataElement.data('end');
            var labelOf = pagingDataElement.data('label-of');
            total = total - deletedCount;
            pagingDataElement.data('total', total);
            pagingElement.html(start + ' ' + '-' + ' ' + end + ' ' + labelOf + ' ' + total + '&nbsp;&nbsp;');
        }
    },

    registerMoveMailToFolder: function () {
        let self = this,
            container = self.getContainer(),
            moveToDropDown = container.find('#mmMoveToFolder');

        moveToDropDown.on('click', 'a', function (e) {
            let element = jQuery(e.currentTarget),
                moveToFolder = element.closest('li').data('movefolder'),
                folder = element.closest('li').data('folder'),
                mUIds = self.getUIds(container);

            if (!self.validateUIds(mUIds)) {
                container.find('.moveToFolderDropDown').removeClass('open');
                return false;
            }

            let params = {
                'module': 'MailManager',
                'view': 'Index',
                '_operation': 'mail',
                '_operationarg': 'move',
                '_folder': folder,
                '_moveFolder': moveToFolder,
                '_muid': mUIds.join(',')
            };

            app.helper.showProgress(app.vtranslate("JSLBL_MOVING") + "...");
            app.request.post({data: params}).then(function (err, data) {
                self.openFolder(folder);
                if (data.status) {
                    app.helper.showSuccessNotification({'message': app.vtranslate('JSLBL_MAIL_MOVED')});

                    let unreadCount = self.getUnreadCount(mUIds);
                    self.updateUnreadCount('-', unreadCount, folder);
                    self.updateUnreadCount('+', unreadCount, moveToFolder);

                    for (var i = 0; i < mUIds.length; i++) {
                        container.find('#mmMailEntry_' + mUIds[i]).remove();
                    }

                    container.find('.moveToFolderDropDown').removeClass('open');
                }
            });
        });
    },
    getUIds(container) {
        let self = this,
            mUIds = [];

        container.find('.mailCheckBox').each(function (i, ele) {
            let element = jQuery(ele);

            if (element.is(":checked")) {
                mUIds.push(self.getUid(element.closest('.mailEntry')));
            }
        });

        return mUIds;
    },
    validateUIds(mUIds) {
        if (mUIds.length <= 0) {
            app.helper.showAlertBox({message: app.vtranslate('JSLBL_NO_EMAILS_SELECTED')});
            return false;
        }

        return true;
    },
    getMarkParams(mUIds, folder, type = 'unread') {
        return {
            'module': 'MailManager',
            'view': 'Index',
            '_operation': 'mail',
            '_operationarg': 'mark',
            '_folder': folder,
            '_muid': mUIds.join(','),
            '_markas': type
        }
    },
    registerMarkMessageAsUnread: function () {
        let self = this,
            container = self.getContainer();

        container.find('#mmMarkAsUnread').click(function (e) {
            let folder = jQuery(e.currentTarget).data('folder'),
                mUIds = self.getUIds(container);

            if (!self.validateUIds(mUIds)) {
                return false;
            }

            let params = self.getMarkParams(mUIds, folder, 'unread');

            app.helper.showProgress(app.vtranslate('JSLBL_Updating') + '...');
            app.request.post({data: params}).then(function (err, data) {
                app.helper.hideProgress();

                if (data.status) {
                    app.helper.showSuccessNotification({'message': app.vtranslate('JSLBL_MAILS_MARKED_UNREAD')});
                    self.updateUnreadCount('+', self.getReadCount(mUIds), folder);
                    self.markMessageUnread(mUIds);
                }
            });
        });
    },

    registerMarkMessageAsRead: function () {
        let self = this,
            container = self.getContainer();

        container.find('#mmMarkAsRead').click(function (e) {
            let folder = jQuery(e.currentTarget).data('folder'),
                mUIds = self.getUIds(container);

            if (!self.validateUIds(mUIds)) {
                return false;
            }

            let params = self.getMarkParams(mUIds, folder, 'read');

            app.helper.showProgress(app.vtranslate('JSLBL_Updating') + '...');
            app.request.post({data: params}).then(function (err, data) {
                app.helper.hideProgress();

                if (data.status) {
                    app.helper.showSuccessNotification({'message': app.vtranslate('JSLBL_MAILS_MARKED_READ')});

                    self.updateUnreadCount('-', self.getUnreadCount(mUIds), folder);
                    self.markMessageRead(mUIds);
                }
            });
        });
    },

    registerSearchEvent: function () {
        var self = this;
        var container = self.getContainer();
        container.find('#mm_searchButton').click(function () {
            var query = container.find('#mailManagerSearchbox').val();
            if (query.trim() == '') {
                vtUtils.showValidationMessage(container.find('#mailManagerSearchbox'), app.vtranslate('JSLBL_ENTER_SOME_VALUE'));
                return false;
            } else {
                vtUtils.hideValidationMessage(container.find('#mailManagerSearchbox'));
            }
            var folder = container.find('#mailManagerSearchbox').data('foldername');
            var type = container.find('#searchType').val();
            self.openFolder(folder, 1, query, type);
        });
    },

    markMessageUnread(mUIds) {
        if (typeof mUIds !== 'object') {
            return console.error('mUIds is not object');
        }

        let self = this,
            container = self.getContainer();

        for (let i = 0; i < mUIds.length; i++) {
            let mUId = mUIds[i];
            let messageElement = container.find('#mmMailEntry_' + mUId);

            messageElement.data('read', '0');
            messageElement.removeClass('mmReadEmail');
            messageElement.addClass('fw-bold')
        }
    },

    markMessageRead(mUIds) {
        if (typeof mUIds !== 'object') {
            return console.error('mUIds is not object');
        }

        let self = this,
            container = self.getContainer();

        for (let i = 0; i < mUIds.length; i++) {
            let mUId = mUIds[i],
                messageElement = container.find('#mmMailEntry_' + mUId);

            messageElement.addClass('mmReadEmail');
            messageElement.data('read', '1');
            messageElement.removeClass('fw-bold');
        }
    },

    getUnreadCount(mUIds) {
        return this.getReadCount(mUIds, 'unread');
    },
    getReadCount(mUIds, type = 'read') {
        let count = 0,
            self = this,
            container = self.getContainer();

        for (let i = 0; i < mUIds.length; i++) {
            let isRead = parseInt(container.find('#mmMailEntry_' + mUIds[i]).data('read'));

            if (1 === isRead && 'read' === type) {
                count++;
            }

            if (0 === isRead && 'unread' === type) {
                count++;
            }
        }

        return count;
    },
    registerMailCheckBoxClickEvent: function () {
        var self = this;
        var container = self.getContainer();
        container.find('.mailCheckBox').click(function (e) {
            var element = jQuery(e.currentTarget);
            if (element.is(":checked")) {
                element.closest('.mailEntry').addClass('highLightMail');
                element.closest('.mailEntry').removeClass('fontBlack');
                element.closest('.mailEntry').addClass('whiteFont');
                element.closest('.mailEntry').removeClass('mmReadEmail');
                element.closest('.mailEntry').find('.mmDateTimeValue').addClass('mmListDateDivSelected');
            } else {
                var isRead = element.closest('.mailEntry').data('read');
                if (parseInt(isRead)) {
                    element.closest('.mailEntry').addClass('mmReadEmail');
                    element.closest('.mailEntry').removeClass('highLightMail');
                } else {
                    element.closest('.mailEntry').removeClass('highLightMail');
                }
                element.closest('.mailEntry').find('.mmDateTimeValue').removeClass('mmListDateDivSelected');
                element.closest('.mailEntry').addClass('fontBlack');
            }
        });
    },

    registerMoveMailDropdownClickEvent: function () {
        var self = this;
        var container = self.getContainer();
        container.find('.moveToFolderDropDown').click(function (e) {
            e.stopImmediatePropagation();
            var element = jQuery(e.currentTarget);
            element.addClass('open');
        });
    },
    registerMainCheckboxClickEvent: function () {
        var self = this;
        var container = self.getContainer();
        container.find('#mainCheckBox').click(function (e) {
            var element = jQuery(e.currentTarget);
            if (element.is(":checked")) {
                container.find('.mailCheckBox').each(function (i, ele) {
                    jQuery(ele).prop('checked', true);
                    jQuery(ele).closest('.mailEntry').addClass('highLightMail');
                    jQuery(ele).closest('.mailEntry').removeClass('fontBlack');
                    jQuery(ele).closest('.mailEntry').addClass('whiteFont');
                    jQuery(ele).closest('.mailEntry').removeClass('mmReadEmail');
                    jQuery(ele).closest('.mailEntry').find('.mmDateTimeValue').addClass('mmListDateDivSelected');
                });
            } else {
                container.find('.mailCheckBox').each(function (i, ele) {
                    jQuery(ele).prop('checked', false);
                    var isRead = jQuery(ele).closest('.mailEntry').data('read');
                    if (parseInt(isRead)) {
                        jQuery(ele).closest('.mailEntry').addClass('mmReadEmail');
                        jQuery(ele).closest('.mailEntry').removeClass('highLightMail');
                    } else {
                        jQuery(ele).closest('.mailEntry').removeClass('highLightMail');
                    }
                    jQuery(ele).closest('.mailEntry').find('.mmDateTimeValue').removeClass('mmListDateDivSelected');
                    jQuery(ele).closest('.mailEntry').addClass('fontBlack');
                });
            }
        });
    },

    registerPrevPageClickEvent: function () {
        var self = this;
        var container = self.getContainer();
        container.find('#PreviousPageButton').click(function (e) {
            var element = jQuery(e.currentTarget);
            var folder = element.data('folder');
            var page = element.data('page');
            self.openFolder(folder, page, jQuery('#mailManagerSearchbox').val(), jQuery('#searchType').val());
        });
    },

    registerNextPageClickEvent: function () {
        var self = this;
        var container = self.getContainer();
        container.find('#NextPageButton').click(function (e) {
            var element = jQuery(e.currentTarget);
            var folder = element.data('folder');
            var page = element.data('page');
            self.openFolder(folder, page, jQuery('#mailManagerSearchbox').val(), jQuery('#searchType').val());
        });
    },
    getUid(container) {
        return container.find('.mm_uid').val();
    },
    registerMailClickEvent: function () {
        let self = this,
            container = self.getContainer();

        container.find('.mmfolderMails').click(function (e) {
            let emailElement = jQuery(e.currentTarget),
                parentEle = emailElement.closest('.mailEntry'),
                mUid = self.getUid(emailElement),
                params = {
                    'module': 'MailManager',
                    'view': 'Index',
                    'attachments': parentEle.data('attachments'),
                    '_operation': 'mail',
                    '_operationarg': 'open',
                    '_folder': parentEle.data('folder'),
                    '_muid': mUid,
                };
            app.helper.showProgress(app.vtranslate("JSLBL_Opening") + "...");
            app.request.post({data: params}).then(function (error, data) {
                if (!error && data['ui']) {
                    app.helper.hideProgress();
                    let uiContent = data.ui;

                    self.updateUnreadCount('-', self.getUnreadCount([mUid]), jQuery(parentEle).data('folder'));
                    self.markMessageRead([mUid]);
                    self.setMailPreview(uiContent)
                    self.highLightMail(mUid);
                    self.registerMailDeleteEvent();
                    self.registerPrintEvent();
                    self.showRelatedActions();
                    self.loadContentsInIframe(container.find('#mmBody'));
                    self.updateMailPaginationEvent();
                } else {
                    app.helper.showErrorNotification({message: app.vtranslate('Email not loaded')})
                }
            });
        });
    },

    loadContentsInIframe: function (element) {
        let bodyContent = element.html();
        element.html('<iframe id="bodyFrame" class="h-100 w-100 border-0"></iframe>');

        let frameElement = jQuery("#bodyFrame")[0].contentWindow.document;
        frameElement.open();
        frameElement.close();

        let bodyFrame = jQuery('#bodyFrame');
        bodyFrame.contents().find('html').html(bodyContent);
        bodyFrame.contents().find('html').find('a').on('click', function (e) {
            e.preventDefault();
            let url = jQuery(e.currentTarget).attr('href');
            window.open(url, '_blank');
        });
    },

    highLightMail: function (msgUid) {
        let self = this,
            container = self.getContainer();

        container.find('.mailEntry').each(function (i, ele) {
            let element = jQuery(ele),
                isRead = element.data('read');

            if (parseInt(isRead)) {
                element.addClass('mmReadEmail');
                element.removeClass('highLightMail');
            } else {
                element.removeClass('highLightMail');
            }

            element.find('.mmDateTimeValue').removeClass('mmListDateDivSelected');
            element.addClass('fontBlack');
        });
        let selectedMailEle = container.find('#mmMailEntry_' + msgUid);

        selectedMailEle.addClass('highLightMail');
        selectedMailEle.removeClass('fontBlack');
        selectedMailEle.addClass('whiteFont');
        selectedMailEle.removeClass('mmReadEmail');
        selectedMailEle.find('.mmDateTimeValue').addClass('mmListDateDivSelected');
    },

    registerMailPaginationEvent: function () {
        let self = this,
            container = self.getContainer();

        self.updateMailPaginationEvent();

        container.on('click', '.mailPagination', function (e) {
            let type = $(this).data('type'),
                elements = self.updateMailPaginationEvent(),
                element = null;

            if ('next' === type && 'object' === typeof elements.next) {
                element = elements.next;
            } else if ('prev' === type && 'object' === typeof elements.prev) {
                element = elements.prev;
            }

            if (element) {
                element.find('.mmfolderMails').trigger('click');
            }
        });
    },
    updateMailPaginationEvent() {
        let value = $('#mailPreviewContainer').find('#mmMsgUid').val()

        if (value) {
            let currentMail = $('#mmMailEntry_' + value),
                prevMail = currentMail.prev('.mailEntry'),
                nextMail = currentMail.next('.mailEntry'),
                pagination = $('.mailPagination');

            pagination.removeAttr('disabled');

            if (!prevMail.length) {
                pagination.filter('[data-type="prev"]').prop('disabled', 'disabled');
            }

            if (!nextMail.length) {
                pagination.filter('[data-type="next"]').prop('disabled', 'disabled');
            }

            return {prev: prevMail, next: nextMail}
        }
    },
    registerMailDeleteEvent: function () {
        let self = this,
            container = self.getContainer();

        container.find('#mmDelete').click(function () {
            let msgUid = jQuery('#mmMsgUid').val(),
                folder = jQuery('#mmFolder').val();

            app.helper.showConfirmationBox({'message': app.vtranslate('LBL_DELETE_CONFIRMATION')}).then(function () {
                app.helper.showProgress(app.vtranslate("JSLBL_Deleting") + "...");
                let params = {
                    'module': 'MailManager',
                    'view': 'Index',
                    '_operation': 'mail',
                    '_operationarg': 'delete',
                    '_folder': folder,
                    '_muid': msgUid
                };
                app.request.post({data: params}).then(function (err, data) {
                    app.helper.hideProgress();
                    if (data.status) {
                        container.find('#mmMailEntry_' + msgUid).remove();
                        self.setMailPreview();
                    }
                });
            });
        });
    },
    setMailPreview(value) {
        const self = this;

        if (!value) {
            value = '<div class="mmListMainContainer fw-bold text-center">' + app.vtranslate('JSLBL_NO_MAIL_SELECTED_DESC') + '</div>';
        }

        $('#mailPreviewContainer', self.getContainer()).html(value);
    },
    registerPrintEvent: function () {
        var self = this;
        var container = self.getContainer();
        container.find('#mmPrint').click(function () {
            var subject = JSON.parse(container.find('#mmSubject').val());
            var from = container.find('#mmFrom').val();
            var to = container.find('#mmTo').val();
            var cc = container.find('#mmCc').val();
            var date = container.find('#mmDate').val();
            var body = jQuery('#mmBody').find('iframe#bodyFrame').contents().find('html').html();

            var content = window.open();
            content.document.write("<b>" + subject + "</b><br>");
            content.document.write(app.vtranslate("JSLBL_FROM") + " " + from + "<br>");
            content.document.write(app.vtranslate("JSLBL_TO") + " " + to + "<br>");
            if (cc) {
                content.document.write(app.vtranslate("JSLBL_CC") + " " + cc + "<br>");
            }
            content.document.write(app.vtranslate("JSLBL_DATE") + " " + date + "<br>");
            content.document.write("<br><br>" + body);
            content.print();
        });
    },
    showRelatedActions: function () {
        let self = this,
            container = self.getContainer(),
            from = container.find('#mmFrom').val(),
            to = container.find('#mmTo').val(),
            folder = container.find('#mmFolder').val(),
            msgUid = container.find('#mmMsgUid').val(),
            params = {
                'module': 'MailManager',
                'view': 'Index',
                '_operation': 'relation',
                '_operationarg': 'find',
                '_mfrom': from,
                '_mto': to,
                '_folder': folder,
                '_msguid': msgUid
            };

        app.request.post({data: params}).then(function (err, data) {
            container.find('#relationBlock').html(data.ui);
            self.handleRelationActions();
        });
    },
    updateUnreadCount: function (operation, count, folder) {
        let self = this,
            container = self.getContainer();

        if (!folder) {
            folder = container.find('.mm_folder.active').data('foldername');
        }

        let countElements = container.find('.mm_folder[data-foldername="' + folder + '"]').find('.mmUnreadCountBadge');

        $(countElements).each(function () {
            let countElement = $(this),
                newCount = 0,
                oldCount = countElement.text();

            if ('+' === operation) {
                newCount = parseFloat(oldCount) + parseFloat(count);
            } else if ('-' === operation) {
                newCount = parseFloat(oldCount) - parseFloat(count);
            } else {
                newCount = count;
            }

            countElement.text(newCount)

            if (newCount > 0) {
                countElement.removeClass("hide");
            } else {
                countElement.addClass("hide");
            }
        })
    },
    handleRelationActions: function () {
        let self = this,
            container = self.getContainer(),
            containerActions = self.getContainerActions(),
            mLinkToType = container.find('#_mlinktotype');

        mLinkToType.on('change', function (e) {
            let element = jQuery(e.currentTarget),
                actionType = element.data('action'),
                module = element.val(),
                relatedRecord = self.getRecordForRelation();

            if (relatedRecord !== false) {
                if (actionType === 'associate') {
                    if (module === 'ITS4YouEmails') {
                        self.associateEmail(relatedRecord);
                    } else if (module === 'ModComments') {
                        self.associateComment(relatedRecord);
                    } else if (module) {
                        self.createRelatedRecord(module);
                    }
                } else if (module) {
                    self.createRelatedRecord(module);
                }
            }

            self.resetRelationDropdown();
        });

        container.on('click', '[data-change-module]', function () {
            mLinkToType.val($(this).data('changeModule'));
            mLinkToType.trigger('change');
        });

        containerActions.on('click', '.allowRemoteContent', function () {
            let mailEntry = $('.mailEntry.highLightMail')

            mailEntry.data('attachments', '1');
            mailEntry.find('.mmfolderMails').trigger('click');
        });
    },
    getContainerActions() {
        return $('#mailManagerActions');
    },
    associateEmail: function (relatedRecord) {
        let self = this,
            container = self.getContainer(),
            params = {
                'module': 'MailManager',
                'view': 'Index',
                '_operation': 'relation',
                '_operationarg': 'link',
                '_mlinkto': relatedRecord,
                '_mlinktotype': 'ITS4YouEmails',
                '_folder': container.find('#mmFolder').val(),
                '_msguid': container.find('#mmMsgUid').val(),
            }

        app.helper.showProgress(app.vtranslate('JSLBL_Associating') + '...');
        app.request.post({data: params}).then(function (error, data) {
            if (!error && data['success']) {
                app.helper.showSuccessNotification({'message': ''});
                app.helper.hideProgress();

                self.showRelatedActions();
            } else {
                app.helper.showErrorNotification({"message": err});
            }
        });
    },

    associateComment: function (relatedRecord) {
        let self = this,
            container = self.getContainer(),
            params = {
                'module': 'MailManager',
                'view': 'Index',
                '_operation': 'relation',
                '_operationarg': 'commentwidget',
                '_mlinkto': relatedRecord,
                '_mlinktotype': 'ModComments',
                '_folder': container.find('#mmFolder').val(),
                '_msguid': container.find('#mmMsgUid').val(),
            }
        app.helper.showProgress(app.vtranslate('JSLBL_Loading') + '...');
        app.request.post({data: params}).then(function (err, data) {
            app.helper.hideProgress();
            app.helper.showModal(data, {
                'cb': function (data) {
                    self.registerCommentModal(data)
                }
            });
        });
    },
    registerCommentModal(data) {
        const self = this,
            indexInstance = Vtiger_Index_Js.getInstance();

        indexInstance.referenceModulePopupRegisterEvent(data);
        indexInstance.registerClearReferenceSelectionEvent(data);

        $('[name="saveButton"]', data).on('click', function (e) {
            e.preventDefault();

            self.saveComment(data);
        });
    },
    createRelatedRecord: function (module) {
        let self = this,
            container = self.getContainer(),
            relatedRecord = self.getRecordForRelation(),
            folder = container.find('#mmFolder').val(),
            params = {
                'module': 'MailManager',
                'view': 'Index',
                '_operation': 'relation',
                '_operationarg': 'create_wizard',
                '_mlinktotype': module,
                '_folder': folder,
                '_msguid': container.find('#mmMsgUid').val(),
            };
        if (relatedRecord && relatedRecord !== null) {
            params['_mlinkto'] = relatedRecord;
        }
        app.helper.showProgress(app.vtranslate('JSLBL_Loading') + '...');
        app.request.post({data: params}).then(function (err, data) {
            app.helper.hideProgress();
            app.helper.showModal(data);
            let form = jQuery('form[name="QuickCreate"]');
            app.event.trigger('post.QuickCreateForm.show', form);
            vtUtils.applyFieldElementsView(form);
            let moduleName = form.find('[name="module"]').val(),
                targetClass = app.getModuleSpecificViewClass('Edit', moduleName),
                targetInstance = new window[targetClass]();
            targetInstance.registerBasicEvents(form);
            let newParams = {};
            newParams.callbackFunction = function () {
                app.helper.hideModal();
                self.showRelatedActions();
            };
            newParams.requestParams = params;
            self.quickCreateSave(form, newParams);
            app.helper.hideProgress();
        });
    },

    /**
     * Register Quick Create Save Event
     * @param {type} form
     * @returns {undefined}
     */
    quickCreateSave: function (form, invokeParams) {
        let container = this.getContainer(),
            params = {
                submitHandler: function (form) {
                    // to Prevent submit if already submitted
                    jQuery("button[name='saveButton']").attr("disabled", "disabled");
                    if (this.numberOfInvalids() > 0) {
                        return false;
                    }
                    let formData = jQuery(form).serializeFormData(),
                        requestParams = invokeParams.requestParams;

                    formData['xmodule'] = formData['module'];
                    formData['xaction'] = formData['action'];
                    delete formData['module'];
                    delete formData['action'];

                    if (requestParams) {
                        requestParams['_operationarg'] = 'create';

                        jQuery.each(requestParams, function (key, value) {
                            formData[key] = value;
                        });
                    }

                    app.request.post({data: formData}).then(function (error, data) {
                        if (!error) {
                            if (!data.error) {
                                jQuery('.vt-notification').remove();
                                app.event.trigger("post.QuickCreateForm.save", data, jQuery(form).serializeFormData());
                                app.helper.hideModal();
                                app.helper.showSuccessNotification({"message": app.vtranslate('JS_RECORD_CREATED')});
                                invokeParams.callbackFunction(data, error);
                            } else {
                                jQuery("button[name='saveButton']").removeAttr('disabled');
                                app.event.trigger('post.save.failed', data);
                            }
                        } else {
                            app.event.trigger("post.QuickCreateForm.save", data, jQuery(form).serializeFormData());
                            app.helper.showErrorNotification({"message": error});
                        }
                    });
                }
            };
        form.vtValidate(params);
    },

    saveComment: function (data) {
        let self = this,
            _mlinkto = jQuery('[name="_mlinkto"]', data).val(),
            _mlinktotype = jQuery('[name="_mlinktotype"]', data).val(),
            _msguid = jQuery('[name="_msguid"]', data).val(),
            _folder = jQuery('[name="_folder"]', data).val(),
            commentContent = jQuery('[name="commentcontent"]', data).val(),
            relatedTo = jQuery('[name="related_to"]', data).val();

        if (!commentContent.trim()) {
            let validationParams = {
                    position: {
                        'my': 'bottom left',
                        'at': 'top left',
                        'container': jQuery('#commentContainer', data)
                    }
                },
                errorMsg = app.vtranslate('JSLBL_CANNOT_ADD_EMPTY_COMMENT');
            vtUtils.showValidationMessage(jQuery('[name="commentcontent"]', data), errorMsg, validationParams);
            return false;
        } else {
            vtUtils.hideValidationMessage(jQuery('[name="commentcontent"]', data));
        }

        let params = {
            'module': 'MailManager',
            'view': 'Index',
            '_operation': 'relation',
            '_operationarg': 'create',
            '_mlinkto': _mlinkto,
            '_mlinktotype': _mlinktotype,
            '_msguid': _msguid,
            '_folder': _folder,
            'commentcontent': commentContent,
            'related_to': relatedTo,
        }

        app.helper.showProgress(app.vtranslate('JSLBL_Saving') + '...');
        app.request.post({'data': params}).then(function (error, data) {
            app.helper.hideProgress();

            if (data['success']) {
                app.helper.showSuccessNotification({'message': ''});
                app.helper.hideModal();

                self.showRelatedActions();
            } else {
                app.helper.showAlertBox({'message': app.vtranslate('JSLBL_FAILED_ADDING_COMMENT')});
            }
        });
    },

    getRecordForRelation: function () {
        var self = this;
        var container = self.getContainer();
        var element = container.find('[name="_mlinkto"]');
        if (element.length > 0) {
            if (element.length == 1) {
                element.attr('checked', true);
                return element.val();
            } else {
                selected = false;
                element.each(function (i, ele) {
                    if (jQuery(ele).is(":checked")) {
                        selected = true;
                    }
                });
                if (selected) {
                    return container.find('[name="_mlinkto"]:checked').val();
                } else {
                    app.helper.showAlertBox({'message': app.vtranslate("JSLBL_PLEASE_SELECT_ATLEAST_ONE_RECORD")});
                    return false;
                }
            }
        } else {
            return null;
        }
    },

    resetRelationDropdown: function () {
        this.getContainer().find('#_mlinktotype').val("");
    },

    openComposeEmailForm: function (type, params, data) {
        Vtiger_Index_Js.showComposeEmailPopup(params, function (response) {
            var descEle = jQuery(response).find('#description');
            if (type == "reply" || type == "forward") {
                jQuery('#subject', response).val(data.subject);
                descEle.val(data.body);
                jQuery('[name="cc"]', response).val("");
                jQuery('.ccContainer', response).addClass("hide");
                jQuery('#ccLink', response).css("display", "");
            } else if (type == "replyall") {
                jQuery('#subject', response).val(data.subject);
                descEle.val(data.body);
                var mailIds = data.ids;
                if (mailIds) {
                    jQuery('.ccContainer', response).removeClass("hide");
                    jQuery('#ccLink', response).css("display", "none");
                    jQuery('[name="cc"]', response).val(mailIds);
                }
            } else {
                jQuery('#subject', response).val("");
                descEle.val("");
                jQuery('[name="cc"]', response).val("");
                jQuery('.ccContainer', response).addClass("hide");
                jQuery('#ccLink', response).css("display", "");
            }
        });
    },

    clearPreviewContainer: function () {
        this.setMailPreview()
    },

    registerRefreshFolder: function () {
        let self = this,
            container = self.getContainer();

        container.find('.mailbox_refresh').click(function () {
            let folder = container.find('.mm_folder.active').data('foldername');

            self.openFolder(folder);
        });
    },

    registerSearchTypeChangeEvent: function () {
        let container = this.getContainer();

        container.on('change', '#searchType', function (e) {
            let element = jQuery(e.currentTarget),
                searchBox = jQuery('#mailManagerSearchbox');

            if ('ON' === element.val()) {
                searchBox.addClass('dateField');
                searchBox.parent().append('<span class="date-addon input-group-text"><i class="fa fa-calendar"></i></span>');
                vtUtils.registerEventForDateFields(searchBox);
            } else if (searchBox.is('.dateField')) {
                searchBox.datepicker('destroy');
                searchBox.removeClass('dateField');
                searchBox.parent().find('.date-addon').remove();
            }
        });
    },

    registerPostMailSentEvent: function () {
        app.event.on('post.mail.sent', function (event, data) {
            var resultEle = jQuery(data);
            var success = resultEle.find('.mailSentSuccessfully');
            if (success.length > 0) {
                app.helper.showModal(data);
            }
        });
    },

    registerClientTokenActions(data) {
        const self = this,
            form = $(data).find('form'),
            tokenElement = form.find('[name="_mbox_client_token"]'),
            accessTokenElement = form.find('[name="_mbox_client_access_token"]');

        form.on('click', '.retrieveToken', function () {
            let formData = form.serializeFormData();

            app.getOAuth2Url(formData['_mbox_server'], formData['_mbox_client_id'], formData['_mbox_client_secret'], formData['_mbox_client_token']).then(function (error, data) {
                if (!error) {
                    if (data['url']) {
                        tokenElement.val('');
                        accessTokenElement.val('');

                        window.open(data['url'], '_blank')
                    }

                    if (data['message']) {
                        app.helper.showErrorNotification({message: data['message']});
                    }
                }
            });
        });

        form.on('click', '.refreshToken', function () {
            self.loadAccessToken(form);
        });
    },

    loadAccessToken(form) {
        const self = this,
            clientId = form.find('[name="_mbox_client_id"]').val(),
            tokenElement = form.find('[name="_mbox_client_token"]'),
            accessTokenElement = form.find('[name="_mbox_client_access_token"]'),
            token = tokenElement.val();

        if (!clientId || token) {
            return false;
        }

        app.getOAuth2Tokens(clientId).then(function (error, data) {
            if (!error) {
                tokenElement.val(data['token']);
                accessTokenElement.val(data['access_token']);
            }
        })
    },
    registerEvents: function () {
        const self = this;

        self.loadFolders();
        self.registerComposeEmail();
        self.registerSettingsEdit();
        self.registerRefreshFolder();
        self.registerSearchTypeChangeEvent();
        self.registerPostMailSentEvent();
        self.registerMailPaginationEvent();
    }
});
