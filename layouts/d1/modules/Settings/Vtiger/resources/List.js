/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger_List_Js("Settings_Vtiger_List_Js", {

    triggerDelete: function (event, url) {
        var instance = Vtiger_List_Js.getInstance();
        instance.DeleteRecord(url);
    }
}, {

    /*
     * Function to register the list view delete record click event
     */
    DeleteRecord: function (url) {
        var thisInstance = this;
        var css = jQuery.extend({'text-align': 'left'}, css);
        app.helper.showProgress();
        app.request.get({'url': url}).then(
            function (err, data) {
                app.helper.hideProgress();
                if (err === null) {
                    var params = {};
                    params.cb = function (container) {
                        thisInstance.postDeleteAction(container);
                    };

                    app.helper.showModal(data, params);
                }
            });
    },

    /**
     * Function to load list view after deletion of record from list view
     */
    postDeleteAction: function (container) {
        var thisInstance = this;
        var deleteConfirmForm = jQuery(container).find('#DeleteModal');
        deleteConfirmForm.on('submit', function (e) {
            e.preventDefault();
            app.helper.showProgress();
            var deleteActionUrl = deleteConfirmForm.serializeFormData();

            app.request.post({'data': deleteActionUrl}).then(
                function (err, data) {
                    app.helper.hideModal();
                    app.helper.hideProgress();
                    if (err === null) {
                        app.helper.showSuccessNotification({'message': app.vtranslate('JS_RECORD_DELETED_SUCCESSFULLY')});
                        jQuery('#recordsCount').val('');
                        jQuery('#totalPageCount').text('');
                        jQuery('#pageNumber').val(1);
                        thisInstance.loadListViewRecords();
                    }
                });
        });
    },

    registerEvents: function () {
        this.registerRowClickEvent();
        this.initializePaginationEvents();
        this.registerEmailFieldClickEvent();
        this.registerDynamicDropdownPosition('table-actions', 'listview-table');

    }
});