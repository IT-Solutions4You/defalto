/*********************************************************************************
 * The content of this file is subject to the PDF Maker license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 ********************************************************************************/
Vtiger_List_Js("EMAILMaker_List_Js", {

    uploadAndParse: function () {

        if (Vtiger_Import_Js.validateFilePath()) {

            var form = jQuery("form[name='importBasic']");
            var container = form.closest('.modal');

            var data = new FormData(form[0]);
            var postParams = {
                data: data,
                contentType: false,
                processData: false
            };
            app.helper.showProgress();

            app.request.post(postParams).then(function (err, response) {
                Vtiger_Import_Js.loadListRecords();
                app.helper.hideProgress();
                container.modal('hide');
            });
        }
        return false;
    }

}, {
    getRecordsCount: function () {
        let aDeferred = jQuery.Deferred(),
            self = this,
            cvId = self.getCurrentCvId(),
            recordsCount = $('.listViewEntriesCheckBox').filter(':checked').length;

        aDeferred.resolve({
            module: 'EMAILMaker', viewname: cvId, count: recordsCount
        });

        return aDeferred.promise();
    },
    registerEvents: function () {
        this.registerListViewSort();
        this.registerListViewSearch();

        this.registerDeleteRecordClickEvent();
        this.registerCheckBoxClickEvent();
        this.registerSelectAllClickEvent();
        this.registerDeSelectAllClickEvent();
        this.registerListViewMainCheckBoxClickEvent();

        var recordSelectTrackerObj = this.getRecordSelectTrackerInstance();
        recordSelectTrackerObj.registerEvents();

        this.registerPostListLoadListener();
        this.registerHeaderReflowOnListSearchSelections();
        this.registerSearchField();
    },
    registerSearchField: function () {
        $('#listViewContent').on('keydown', '.listSearchContributor', function (event) {
            if (13 === event.which) {
                event.preventDefault();
            }
        });
    },
    performMassDeleteRecords: function (url) {
        var listInstance = this;
        var params = {};
        var paramArray = url.slice(url.indexOf('?') + 1).split('&');
        for (var i = 0; i < paramArray.length; i++) {
            var param = paramArray[i].split('=');
            params[param[0]] = param[1];
        }
        var listSelectParams = listInstance.getListSelectAllParams(true);
        listSelectParams = jQuery.extend(listSelectParams, params);
        if (listSelectParams) {
            var message = app.vtranslate('LBL_MASS_DELETE_CONFIRMATION');
            app.helper.showConfirmationBox({'message': message}).then(function (e) {
                listSelectParams['module'] = app.getModuleName();
                listSelectParams['action'] = 'MassDelete';
                listSelectParams['search_params'] = JSON.stringify(listInstance.getListSearchParams());
                app.helper.showProgress();
                app.request.post({data: listSelectParams}).then(
                    function (error, result) {
                        app.helper.hideProgress();
                        if (error) {
                            app.helper.showErrorNotification();
                            return;
                        }
                        listInstance.clearList();
                        var params = {};
                        listInstance.loadListViewRecords(params);
                    }
                );
            });
        } else {
            listInstance.noRecordSelectedAlert();
        }
    }

})

