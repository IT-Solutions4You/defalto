/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
/** @var Portal_List_Js */
Vtiger_List_Js("Portal_List_Js", {
    getDefaultParams: function () {
        return {
            'module': app.getModuleName(),
            'view': 'List',
            'page': jQuery('#pageNumber').val(),
            'orderby': jQuery('[name="orderBy"]').val(),
            'sortorder': jQuery('[name="sortOrder"]').val(),
            'search_value': jQuery('#alphabetValue').val()
        };
    },
    editBookmarkAction: function () {
        let params = {
            'module': app.getModuleName(), 'parent': app.getParentModuleName(), 'view': 'EditAjax'
        };
        Portal_List_Js.editBookmark(params);
    },
    editBookmark: function (params) {
        app.request.get({data: params}).then(function (err, data) {
            var callBackFunction = function (data) {
                Portal_List_Js.saveBookmark();
            };
            app.helper.showModal(data, params);
            if (typeof callBackFunction == 'function') {
                callBackFunction(data);
            }
        });
    },
    saveBookmark: function () {
        let form = jQuery('#saveBookmark');

        form.on('submit', function (e) {
            e.preventDefault();
        });

        let params = {
            submitHandler: function (form) {
                form = jQuery(form);
                form.find('[type="submit"]').attr('disabled', true);

                let params = form.serializeFormData();

                app.request.post({data: params}).then(function (error, data) {
                    if (!error) {
                        let url = Portal_List_Js.getDefaultParams();

                        Portal_List_Js.loadListViewContent(url);
                    }
                });
            }
        };
        form.vtValidate(params);

    },
    massDeleteRecords: function () {
        let listInstance = app.controller(),
            deleteURL = 'index.php?module=' + app.getModuleName() + '&action=MassDelete';

        listInstance.performMassDeleteRecords(deleteURL);
    },
    loadListViewContent: function (url) {
        let thisInstance = Portal_List_Js.getInstance();
        thisInstance.loadListViewRecords(url);
    },
    updatePagination: function () {
        let previousPageExist = jQuery('#previousPageExist').val(),
            nextPageExist = jQuery('#nextPageExist').val(),
            previousPageButton = jQuery('#PreviousPageButton'),
            nextPageButton = jQuery('#nextPageButton'),
            listViewEntriesCount = parseInt(jQuery('#noOfEntries').val()),
            pageStartRange = parseInt(jQuery('#pageStartRange').val()),
            pageEndRange = parseInt(jQuery('#pageEndRange').val()),
            pages = jQuery('#totalPageCount').text(),
            totalNumberOfRecords = jQuery('.totalNumberOfRecords'),
            pageNumbersTextElem = jQuery('.pageNumbersText'),
            currentPage = parseInt(jQuery('#pageNumber').val());

        jQuery('#pageToJump').val(currentPage);

        if (previousPageExist) {
            previousPageButton.removeAttr('disabled');
        } else if (!previousPageExist) {
            previousPageButton.attr("disabled", "disabled");
        }

        if ((nextPageExist) && (pages > 1)) {
            nextPageButton.removeAttr('disabled');
        } else if ((!nextPageExist) || (pages == 1)) {
            nextPageButton.attr("disabled", "disabled");
        }

        if (listViewEntriesCount) {
            let pageNumberText = pageStartRange + " " + app.vtranslate('to') + " " + pageEndRange;
            pageNumbersTextElem.html(pageNumberText);
            totalNumberOfRecords.removeClass('hide');
        } else {
            pageNumbersTextElem.html("<span>&nbsp;</span>");
            if (!totalNumberOfRecords.hasClass('hide')) {
                totalNumberOfRecords.addClass('hide');
            }
        }
    }
}, {
    registerAddBookmark: function () {
        jQuery('.addBookmark').on('click', function () {
            var params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'view': 'EditAjax'
            };
            Portal_List_Js.editBookmark(params);
        });
    },
    registerEditBookmark: function () {
        var container = this.getListViewContainer();
        jQuery('body').on('click', '.editPortalRecord', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var id = currentTarget.closest('ul').data('id');
            var params = {
                'module': app.getModuleName(),
                'parent': app.getParentModuleName(),
                'view': 'EditAjax',
                'record': id
            };
            Portal_List_Js.editBookmark(params);
        });
    },
    registerDeleteBookmark: function () {
        jQuery('body').on('click','.deleteRecord', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var id = currentTarget.closest('ul').data('id');
            var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
            app.helper.showConfirmationBox({'message': message}).then(function (e) {
                var params = {
                    'module': app.getModuleName(),
                    'parent': app.getParentModuleName(),
                    'action': 'DeleteAjax',
                    'record': id
                };
                app.request.post({data: params}).then(function (error, data) {
                    if (!error) {
                        var url = Portal_List_Js.getDefaultParams();
                        Portal_List_Js.loadListViewContent(url);
                    }
                });
            });
        });
    },
    registerListViewSort: function () {
        const container = this.getListViewContainer();

        container.on('click', '.listViewContentHeaderValues', function (e) {

            let currentTarget = jQuery(e.currentTarget),
                orderBy = currentTarget.data('columnname'),
                sortOrder = currentTarget.data('nextsortorderval');

            if (sortOrder === 'ASC') {
                jQuery('i', e.currentTarget).addClass('fa-sort-asc');
            } else {
                jQuery('i', e.currentTarget).addClass('fa-sort-desc');
            }

            let url = Portal_List_Js.getDefaultParams();
            container.find('[name="sortOrder"]').val(sortOrder);
            container.find('[name="orderBy"]').val(orderBy);
            url['orderby'] = orderBy;
            url['sortorder'] = sortOrder;

            Portal_List_Js.loadListViewContent(url);
        });
    },
    
    registerRowClickEvent: function () {
        var container = this.getListViewContainer();
        container.on('click', '.listViewEntries', function (e) {
            var selection = window.getSelection().toString();
            if (selection.length == 0) {
                if (jQuery(e.target, jQuery(e.currentTarget)).is(':first-child'))
                    return;
                if (jQuery(e.target).is('input[type="checkbox"]'))
                    return;
                var elem = jQuery(e.currentTarget);
                var recordUrl = elem.data('recordurl');
                if (typeof recordUrl == 'undefined') {
                    return;
                }
                window.location.href = recordUrl;
            }
        });
    },
    registerRemoveSortingPortal: function () {
        var container = this.getListViewContainer();
        container.on('click', '.removeSortingPortal', function (e) {
            e.stopPropagation();
            e.preventDefault();
            var params = {
                'module': app.getModuleName(),
                'view': 'List',
                'page': jQuery('#pageNumber').val(),
                'mode': 'removeSorting'
            }
            Portal_List_Js.loadListViewContent(params);
        });
    },
    loadListViewRecords: function (url) {
        let aDeferred = jQuery.Deferred(),
            defaultUrl = Portal_List_Js.getDefaultParams();

        if (!jQuery.isEmptyObject(url)) {
            jQuery.extend(defaultUrl, url);
        }

        app.helper.showProgress();
        app.request.pjax({data: defaultUrl}).then(function (error, data) {
            app.helper.hideProgress();

            if (error === null) {
                aDeferred.resolve(data);
                app.helper.hideModal();
                jQuery('#listViewContent').html(data);
                app.event.trigger('post.listViewFilter.click');
            }

            Portal_List_Js.updatePagination();
        });

        return aDeferred.promise();
    },
    getRecordsCount: function () {
        let aDeferred = jQuery.Deferred(),
            module = this.getModuleName(),
            defaultParams = this.getDefaultParams(),
            postData = {
                "module": module,
                "view": "ListAjax",
                "mode": "getRecordCount"
            };
        postData = jQuery.extend(defaultParams, postData);

        let params = {};
        params.data = postData;
        app.request.get(params).then(
                function (err, response) {
                    aDeferred.resolve(response);
                }
        );
        return aDeferred.promise();
    },
    enableListViewActions : function(){
        let element = jQuery('.listViewActionsContainer');

        element.find('button').removeAttr('disabled');
    },
    
    disableListViewActions : function(){
        let element = jQuery('.listViewActionsContainer');

        element.find('button').attr('disabled', 'disabled');
    },
    registerEvents: function () {
        this._super();
        this.registerAddBookmark();
        this.registerEditBookmark();
        this.registerDeleteBookmark();
        this.updatePagination();
    }
});
