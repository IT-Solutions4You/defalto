/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_List_Js("Documents_List_Js", {

    massMove: function (url) {
        var self = new Documents_List_Js();
        self.massMove(url);
    }

}, {

    registerSearchEvent: function (container) {
        container.find('#searchFolders').on('keydown', function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        });

        container.find('#searchFolders').on('keyup', function () {
            var searchKey = jQuery(this).val();
            searchKey = searchKey.toLowerCase();
            jQuery('.folder', container).removeClass('selectedFolder');
            container.find('#foldersList').find('.folder').removeClass('hide');
            container.find('#foldersList').find('.folder').filter(function () {
                var currentElement = jQuery(this);
                var folderName = currentElement.find('.foldername').text();
                folderName = folderName.toLowerCase();
                var status = folderName.indexOf(searchKey);
                if (status === -1) return true;
                return false;
            }).addClass('hide');
        });
    },

    registerFolderSelectionEvent: function (container) {
        jQuery('.folder', container).on('click', function () {
            jQuery('.folder', container).removeClass('selectedFolder');
            var currentSelection = jQuery(this);
            currentSelection.addClass('selectedFolder');
            var folderId = currentSelection.data('folderId');
            jQuery('input[name="folderid"]').val(folderId);
        });
    },

    registerMoveDocumentsEvent: function (container) {
        var self = this;
        container.find('#moveDocuments').on('submit', function (e) {
            e.preventDefault();
            if (container.find('.folder').filter('.selectedFolder').length) {
                var formData = jQuery(e.currentTarget).serializeFormData();
                app.helper.showProgress();
                app.request.post({'data': formData}).then(function (e, res) {
                    app.helper.hideProgress();
                    if (!e) {
                        app.helper.showSuccessNotification({
                            'message': res.message
                        });
                    } else {
                        app.helper.showErrorNotification({
                            'message': app.vtranslate('JS_OPERATION_DENIED')
                        });
                    }
                    app.helper.hideModal();
                    self.loadListViewRecords();
                });
            } else {
                app.helper.showAlertNotification({
                    'message': app.vtranslate('JS_SELECT_A_FOLDER')
                });
            }
        });
    },

    registerMoveDocumentsEvents: function (container) {
        this.registerSearchEvent(container);
        this.registerFolderSelectionEvent(container);
        this.registerMoveDocumentsEvent(container);
    },

    massMove: function (url) {
        var self = this;
        var listInstance = Vtiger_List_Js.getInstance();
        var validationResult = listInstance.checkListRecordSelected();
        if (!validationResult) {
            var selectedIds = listInstance.readSelectedIds(true);
            var excludedIds = listInstance.readExcludedIds(true);
            var cvId = listInstance.getCurrentCvId();
            var postData = {
                "selected_ids": selectedIds,
                "excluded_ids": excludedIds,
                "viewname": cvId
            };

            if (app.getModuleName() === 'Documents') {
                var defaultparams = listInstance.getDefaultParams();
                postData['folder_id'] = defaultparams['folder_id'];
                postData['folder_value'] = defaultparams['folder_value'];
            }
            var params = {
                "url": url,
                "data": postData
            };

            app.helper.showProgress();
            app.request.get(params).then(function (e, res) {
                app.helper.hideProgress();
                if (!e && res) {
                    app.helper.showModal(res, {
                        'cb': function (modalContainer) {
                            self.registerMoveDocumentsEvents(modalContainer);
                        }
                    });
                }
            });
        } else {
            listInstance.noRecordSelectedAlert();
        }
    },

    unMarkAllFilters: function () {
        jQuery('.customViewsContainer').find('.dropdown-item').removeClass('text-primary bg-body-secondary fw-bold');
    },

    unMarkAllFolders: function () {
        let element = jQuery('.documentFolder');

        element.removeClass('active');
        element.find('.fa-folder-open')
            .removeClass('fa-folder-open')
            .addClass('fa-folder');
    },

    registerFoldersClickEvent: function () {
        let self = this,
            filters = jQuery('#module-filters');

        filters.on('click', '.documentFolder', function (e) {
            let targetElement = jQuery(e.target);

            if (targetElement.is('[data-bs-toggle]') || targetElement.parents('[data-bs-toggle]').length) {
                return;
            }

            let element = jQuery(e.currentTarget), el = jQuery('a[data-filter-id]', element);

            self.resetData();
            self.unMarkAllFilters();
            self.unMarkAllFolders();
            el.closest('li').addClass('active');
            el.closest('li').find('.fa-folder')
                .removeClass('fa-folder')
                .addClass('fa-folder-open');

            self.loadFilter(jQuery('input[name="allCvId"]').val(), {
                folder_id: 'folderid', folder_value: el.data('folderName')
            });
        });
    },
    addFolderToList: function (folderDetails) {
        let clone = $('.documentFolderClone').clone(true);
        clone.find('.filterName').attr({
            'data-filter-id': folderDetails['folderid'],
            'data-folder-name': folderDetails['folderName'],
            'title': folderDetails['folderDesc'],
        });
        clone.find('.foldername').text(folderDetails['folderName']);
        clone.find('[data-folder-id]').attr('data-folder-id', folderDetails['folderid'])
        clone.find('.folderDropdown').removeClass('invisible');

        jQuery('#folders-list').append(clone);
    },

    registerAddFolderModalEvents: function (container) {
        var self = this;
        var addFolderForm = jQuery('#addDocumentsFolder');
        addFolderForm.vtValidate({
            submitHandler: function (form) {
                var formData = addFolderForm.serializeFormData();
                app.helper.showProgress();
                app.request.post({'data': formData}).then(function (e, res) {
                    app.helper.hideProgress();
                    if (!e) {
                        app.helper.hideModal();
                        app.helper.showSuccessNotification({
                            'message': res.message
                        });
                        var folderDetails = res.info;
                        self.addFolderToList(folderDetails);
                    }
                    if (e) {
                        app.helper.showErrorNotification({
                            'message': e
                        });
                    }
                });
            }
        });
    },

    registerAddFolderEvent: function () {
        var self = this;
        var filters = jQuery('#module-filters');
        filters.find('#createFolder').on('click', function () {
            var params = {
                'module': app.getModuleName(),
                'view': 'AddFolder'
            };
            app.helper.showProgress();
            app.request.get({'data': params}).then(function (e, res) {
                app.helper.hideProgress();
                if (!e) {
                    app.helper.showModal(res, {
                        'cb': function (modalContainer) {
                            self.registerAddFolderModalEvents(modalContainer);
                        }
                    });
                }
            });
        });
    },

    registerFoldersSearchEvent: function () {
        let filters = jQuery('#module-filters');
        filters.find('.search-folders').on('keyup', function (e) {
            let element = jQuery(e.currentTarget),
                val = element.val().toLowerCase();

            jQuery('li.documentFolder', filters).each(function () {
                let filterEle = jQuery(this),
                    folderName = filterEle.find('.foldername').text();

                folderName = folderName.toLowerCase();

                if (folderName.indexOf(val) === -1) {
                    filterEle.addClass('hide');
                } else {
                    filterEle.removeClass('hide');
                }
            });

            if (jQuery('li.documentFolder', filters).not('.hide').length > 0) {
                jQuery('#folders-list', filters).find('.noFolderText').hide();
            } else {
                jQuery('#folders-list', filters).find('.noFolderText').show();
            }
        });
    },

    registerDeleteFolderEvent: function () {
        let filters = jQuery('#module-filters');

        filters.on('click', 'li.deleteFolder', function (e) {
            let element = jQuery(e.currentTarget),
                deletable = parseInt(element.data('deletable'));

            if (1 === deletable) {
                app.helper.showConfirmationBox({'message': app.vtranslate('JS_LBL_ARE_YOU_SURE_YOU_WANT_TO_DELETE')}).then(function () {
                    let folderId = element.data('folderId'),
                        params = {
                            module: app.getModuleName(),
                            mode: 'delete',
                            action: 'Folder',
                            folderid: folderId
                        };
                    app.helper.showProgress();
                    app.request.post({data: params}).then(function (error, data) {
                        app.helper.hideProgress();
                        if (!error) {
                            element.closest('.documentFolder').remove();

                            app.helper.showSuccessNotification({
                                'message': data.message
                            });
                        }
                    });
                });
            } else {
                app.helper.showAlertNotification({
                    'message': app.vtranslate('JS_FOLDER_IS_NOT_EMPTY')
                });
            }
        });
    },

    updateFolderInList: function (folderDetails) {
        jQuery('#folders-list').find('a.filterName[data-filter-id="' + folderDetails.folderid + '"]')
            .attr('title', folderDetails.folderDesc)
            .find('.foldername').text(folderDetails.folderName);
    },

    registerEditFolderModalEvents: function (container) {
        var self = this;
        container.find('#addDocumentsFolder').on('submit', function (e) {
            e.preventDefault();
            var formData = jQuery(this).serializeFormData();
            app.helper.showProgress();
            app.request.post({'data': formData}).then(function (e, res) {
                app.helper.hideProgress();
                if (!e) {
                    app.helper.hideModal();
                    app.helper.showSuccessNotification({
                        'message': res.message
                    });
                    var folderDetails = res.info;
                    self.updateFolderInList(folderDetails);
                } else {
                    app.helper.showAlertNotification({
                        'message': e
                    });
                }
            });
        });
    },

    registerFolderEditEvent: function () {
        var self = this;
        var filters = jQuery('#module-filters');
        filters.on('click', 'li.editFolder', function (e) {
            var element = jQuery(e.currentTarget);
            var folderId = element.data('folderId');
            var params = {
                'module': app.getModuleName(),
                'view': 'AddFolder',
                'folderid': folderId,
                'mode': 'edit'
            };
            app.helper.showProgress();
            app.request.get({'data': params}).then(function (e, res) {
                app.helper.hideProgress();
                if (!e) {
                    app.helper.showModal(res, {
                        'cb': function (modalContainer) {
                            self.registerEditFolderModalEvents(modalContainer);
                        }
                    });
                }
            });
        });
    },

    registerRowDoubleClickEvent: function () {
        return true;
    },

    getDefaultParams: function () {
        var search_value = jQuery('.sidebar-menu').find('.documentFolder.active').find('.filterName').data('folder-name');
        var customParams = {
            'folder_id': 'folderid',
            'folder_value': search_value
        };
        var params = this._super();
        if (search_value) {
            jQuery.extend(params, customParams);
        }
        return params;
    },

    registerEvents: function () {
        this._super();

        this.registerFoldersClickEvent();
        this.registerAddFolderEvent();
        this.registerFoldersSearchEvent();
        this.registerFolderEditEvent();
        this.registerDeleteFolderEvent();

        //To make folder non-deletable if a document is uploaded
        app.event.on('post.documents.save', function (event, data) {
            var folderid = data.folderid;
            var folder = jQuery('#folders-list').find('[data-folder-id="' + folderid + '"]').filter('.deleteFolder');
            if (folder.length) {
                folder.attr('data-deletable', '0');
            }
        })
    }
});