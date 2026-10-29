/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_List_Js("RecycleBin_List_Js", {
    recordSelectTrackerInstance: false,
    emptyRecycleBin: function (url) {
        let message = app.vtranslate('JS_MSG_EMPTY_RB_CONFIRMATION');
        app.helper.showConfirmationBox({'message': message}).then(
            function (e) {
                let deleteURL = url + '&mode=emptyRecycleBin',
                    instance = new RecycleBin_List_Js();

                app.request.post({url: deleteURL}).then(
                    function (error, data) {
                        if (!error) {
                            instance.recycleBinActionPostOperations(data);
                        } else {
                            app.helper.showErrorNotification({message: error});
                        }
                    }
                );
            },
            function (error, err) {
            }
        )
    },
    deleteRecords: function (url) {
        let listInstance = Vtiger_List_Js.getInstance(),
            validationResult = listInstance.checkListRecordSelected();

        if (validationResult != true) {
            let selectedIds = listInstance.readSelectedIds(true),
                excludedIds = listInstance.readExcludedIds(true),
                cvId = listInstance.getCurrentCvId(),
                message = app.vtranslate('LBL_MASS_DELETE_CONFIRMATION');

            app.helper.showConfirmationBox({'message': message}).then(
                function (e) {
                    let sourceModule = jQuery('#sourceModule').val(),
                        postData = {
                            'viewname': cvId,
                            'selected_ids': selectedIds,
                            'excluded_ids': excludedIds,
                            'mode': 'deleteRecords',
                            'sourceModule': sourceModule,
                            'search_params': JSON.stringify(listInstance.getListSearchParams())
                        };

                    app.helper.showProgress();
                    app.request.post({url: url, data: postData}).then(
                        function (error, data) {
                            if (data) {
                                app.helper.hideProgress();
                                let instance = new RecycleBin_List_Js();

                                instance.recycleBinActionPostOperations(data);
                            }
                        }
                    );
                },
                function (error, err) {
                })
        } else {
            listInstance.noRecordSelectedAlert();
        }

    },
    restoreRecords: function (url) {
        let listInstance = Vtiger_List_Js.getInstance(),
            validationResult = listInstance.checkListRecordSelected();

        if (validationResult != true) {
            let selectedIds = listInstance.readSelectedIds(true),
                excludedIds = listInstance.readExcludedIds(true),
                cvId = listInstance.getCurrentCvId(),
                message = app.vtranslate('JS_LBL_RESTORE_RECORDS_CONFIRMATION');

            app.helper.showConfirmationBox({'message': message}).then(
                function (e) {
                    let sourceModule = jQuery('#sourceModule').val(),
                        postData = {
                            'viewname': cvId,
                            'selected_ids': selectedIds,
                            'excluded_ids': excludedIds,
                            'mode': 'restoreRecords',
                            'sourceModule': sourceModule,
                            'search_params': JSON.stringify(listInstance.getListSearchParams())
                        };

                    app.helper.showProgress();
                    app.request.post({url: url, data: postData}).then(function (error, data) {
                        app.helper.hideProgress();
                        if (error === null) {
                            jQuery('.vt-notification').remove();
                            let moduleLabel = data.modulelabel;

                            if (!moduleLabel) {
                                moduleLabel = app.vtranslate('SINGLE_' + sourceModule);
                            }

                            let instance = new RecycleBin_List_Js(),
                                successNote = app.vtranslate('JS_RECORDS_RESTORED', selectedIdsArray.length, moduleLabel);

                            instance.recycleBinActionPostOperations(data);
                            app.helper.showSuccessNotification({'message': successNote});
                        } else {
                            app.event.trigger('post.save.failed', error);
                        }
                    });
                },
                function (error, err) {
                })
        } else {
            listInstance.noRecordSelectedAlert();
        }
    },
    /**
     * Function to convert id into json string
     * @param <integer> id
     * @return <string> json string
     */
    convertToJsonString: function (id) {
        let jsonObject = [];

        jsonObject.push(id);
        return JSON.stringify(jsonObject);
    },
    /**
     * Function to delete a record
     */
    deleteRecord: function (recordId) {
        let selectedRecordId = RecycleBin_List_Js.convertToJsonString(recordId),
            listInstance = Vtiger_List_Js.getInstance(),
            message = app.vtranslate('LBL_DELETE_CONFIRMATION'),
            sourceModule = jQuery('#sourceModule').val(),
            cvId = listInstance.getCurrentCvId();

        app.helper.showConfirmationBox({'message': message}).then(
            function (e) {
                let module = app.getModuleName(),
                    postData = {
                    "module": module,
                    "viewname": cvId,
                    "selected_ids": selectedRecordId,
                    "action": "RecycleBinAjax",
                    "sourceModule": sourceModule,
                    "mode": "deleteRecords"
                };

                app.helper.showProgress();
                app.request.post({data: postData}).then(
                    function (error, data) {
                        if (data) {
                            app.helper.hideProgress();
                            let instance = new RecycleBin_List_Js();

                            instance.recycleBinActionPostOperations(data);
                        }
                    }
                );
            },
            function (error, err) {
            });
    },
    restoreAction: function (recordId, restoreExternalFile) {
        let aDeferred = jQuery.Deferred(),
            selectedRecordId = RecycleBin_List_Js.convertToJsonString(recordId),
            listInstance = Vtiger_List_Js.getInstance(),
            sourceModule = jQuery('#sourceModule').val(),
            cvId = listInstance.getCurrentCvId(),
            module = app.getModuleName(),
            postData = {
            "module": module,
            "action": "RecycleBinAjax",
            "viewname": cvId,
            "selected_ids": selectedRecordId,
            "mode": "restoreRecords",
            "sourceModule": sourceModule
        };

        if (restoreExternalFile) {
            postData.restoreExternalFiles = true;
        }

        app.helper.showProgress();
        app.request.post({data: postData}).then(
            function (error, data) {
                app.helper.hideProgress();
                if (error === null) {
                    jQuery('.vt-notification').remove();
                    let instance = new RecycleBin_List_Js();

                    instance.recycleBinActionPostOperations(data);
                    aDeferred.resolve(data);
                } else {
                    app.event.trigger('post.save.failed', error);
                    aDeferred.resolve(data);
                }
            }
        );
        return aDeferred.promise();
    },
    /**
     * Function to restore a record
     */
    restoreRecord: function (recordId) {
        let message = app.vtranslate('JS_LBL_RESTORE_RECORD_CONFIRMATION');

        app.helper.showConfirmationBox({'message': message}).then(
            function (e) {
                RecycleBin_List_Js.restoreAction(recordId, false);
            },
            function (error, err) {
            });
    }
}, {
    // Overiding the parent function
    registerDynamicListHeaders: function () {
    },
    getRecordSelectTrackerInstance: function () {
        if (RecycleBin_List_Js.recordSelectTrackerInstance === false) {
            RecycleBin_List_Js.recordSelectTrackerInstance = Vtiger_RecordSelectTracker_Js.getInstance();
        }
        return RecycleBin_List_Js.recordSelectTrackerInstance;
    },
    //Fix for empty Recycle bin
    //Change Button State ("Enable or Disable")
    listViewPostOperation: function () {
        if (!jQuery('#isRecordsDeleted').val()) {
            jQuery(".clearRecycleBin").attr('disabled', 'disabled');
        } else {
            jQuery(".clearRecycleBin").removeAttr('disabled');
        }
    },
    getDefaultParams: function () {
        let pageNumber = jQuery('#pageNumber').val(),
            module = app.getModuleName(),
            parent = app.getParentModuleName(),
            orderBy = jQuery('#orderBy').val(),
            sortOrder = jQuery("#sortOrder").val(),
            params = {
            'module': module,
            'parent': parent,
            'page': pageNumber,
            'view': "List",
            'orderby': orderBy,
            'sortorder': sortOrder,
            'sourceModule': jQuery('#sourceModule').val()
        };

        return params;
    },
    /*
     * Function to perform the operations after the Empty RecycleBin
     */
    recycleBinActionPostOperations: function (data) {
        jQuery('#recordsCount').val('');
        jQuery('#totalPageCount').text('');
        let thisInstance = this,
            listInstance = Vtiger_List_Js.getInstance();

        if (data) {
            let params = thisInstance.getDefaultParams();

            app.request.post({data: params}).then(function (error, data) {
                app.helper.hideModal();
                let listViewContainer = thisInstance.getListViewContainer();

                listViewContainer.html(data);
                vtUtils.applyFieldElementsView(listViewContainer.find('.searchRow'));
                jQuery('#deSelectAllMsg').trigger('click');
                thisInstance.listViewPostOperation();
                thisInstance.updatePagination();
                app.event.trigger('post.listViewFilter.click', listViewContainer);
                listInstance.clearList();
                listInstance.markSelectedIdsCheckboxes();
            });
        }
    },
    getRecordsCount: function () {
        let aDeferred = jQuery.Deferred(),
            count = '',
            module = app.getModuleName(),
            sourceModule = jQuery('#sourceModule').val(),
            postData = {
            "module": module,
            "sourceModule": sourceModule,
            "view": "ListAjax",
            "mode": "getRecordsCount"
        };

        app.request.post({data: postData}).then(
            function (error, data) {
                jQuery("#recordsCount").val(data['count']);
                aDeferred.resolve(data);
            },
            function (error, err) {
            }
        );

        return aDeferred.promise();
    },
    /**
     * Function to get Page Jump Params
     */
    getPageJumpParams: function () {
        let module = app.getModuleName(),
            pageCountParams = {
            'module': module,
            'view': "ListAjax",
            'mode': "getPageCount",
            'sourceModule': jQuery('#sourceModule').val(),
            'search_params': JSON.stringify(this.getListSearchParams())
        };

        return pageCountParams;
    },
    /*
     * Function to register the list view delete record click event
     */
    registerDeleteRecordClickEvent: function () {
        let listViewContentDiv = this.getListViewContainer();

        listViewContentDiv.on('click', '.deleteRecordButton', function (e) {
            let elem = jQuery(e.currentTarget),
                recordId = elem.closest('tr').data('id');

            RecycleBin_List_Js.deleteRecord(recordId);
            e.stopPropagation();
        });
    },
    /*
     * Function to register the list view restore record click event
     */
    registerRestoreRecordClickEvent: function () {
        let listViewContentDiv = this.getListViewContainer();

        listViewContentDiv.on('click', '.restoreRecordButton', function (e) {
            let elem = jQuery(e.currentTarget),
                recordId = elem.closest('tr').data('id');

            RecycleBin_List_Js.restoreRecord(recordId);
            e.stopPropagation();
        });
    },
    registerRowDoubleClickEvent: function () {
        return;
    },
    disableListViewActions: function () {
        jQuery('.recordDependentListActions').find('button').attr('disabled', "disabled");
    },
    enableListViewActions: function () {
        jQuery('.recordDependentListActions').find('button').removeAttr('disabled');
    },
    registerSearchModules() {
        let self = this,
            mainContainer = jQuery('.main-container'),
            modules = mainContainer.find('.listViewFilter');

        mainContainer.on('keyup', '.search-list', function() {
            let searchText = self.simplifyString($(this).val());

            modules.removeClass('hide');

            if (!searchText.length) {
                return;
            }

            modules.filter(function() {
                let label = self.simplifyString($(this).data('label')),
                    module = self.simplifyString($(this).data('module'));

                console.log(searchText, label, module);

                return label.indexOf(searchText) < 0 && module.indexOf(searchText) < 0;
            }).addClass('hide');
        });
    },
    simplifyString(value) {
        if (!value.length) {
            return value;
        }

        value = value.trim().toLowerCase();
        value = value.replace(/[^a-z0-9\s]/g, '');
        value = value.replace(/\s+/g, '').trim();

        return value;
    },
    registerEvents: function () {
        this._super();
        this.registerRestoreRecordClickEvent();
        this.registerSearchModules();

        // Added to overide default list view siderbar event
        jQuery('.list-menu-content').on('click', '.listViewFilter', function (e) {
            e.stopImmediatePropagation();
        });
    }
});
