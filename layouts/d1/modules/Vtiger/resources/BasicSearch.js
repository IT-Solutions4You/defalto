/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_BasicSearch_Js */
Vtiger.Class('Vtiger_BasicSearch_Js', {}, {
    //stores the module that need to be searched
    searchModule: false,

    //stores the module that need to be searched which is selected by the user
    currentSearchModule: false,

    /**
     * Function to get the search module
     */
    getSearchModule: function () {
        if (this.searchModule === false) {
            //default gives current module
            var module = app.getModuleName();
            if (typeof this.getCurrentSearchModule() != 'undefined') {
                module = this.getCurrentSearchModule();
            }
            if (app.getParentModuleName().length > 0) {
                module = '';
            }

            this.setSearchModule(module);
        }
        return this.searchModule;
    },

    /**
     * Function to set the search module
     */
    setSearchModule: function (moduleName) {
        this.searchModule = moduleName;
        return this;
    },

    /**
     * Function to get the user selected search module
     */
    getCurrentSearchModule: function () {
        if (this.currentSearchModule === false) {
            this.currentSearchModule = jQuery('#basicSearchModulesList').val();
        }
        return this.currentSearchModule;
    },

    /**
     * Function which will perform the search
     */
    _search: function (params) {
        var aDeferred = jQuery.Deferred();

        if (typeof params == 'undefined') {
            params = {};
        }

        params.view = 'ListAjax';
        params.mode = 'showSearchResults';
        params.transformedSearchParams = true;

        if (typeof params.module == 'undefined') {
            params.module = app.getModuleName();
            //if you are in Settings then module should be Vtiger for normal text search
            if (app.getParentModuleName().length > 0) {
                params.module = 'Vtiger';
            }
        }
        app.helper.showProgress();
        app.request.post({data: params}).then(
            function (err, data) {
                app.helper.hideProgress();
                aDeferred.resolve(data);
            },

            function (error, err) {
                aDeferred.reject(error);
            }
        );
        return aDeferred.promise();
    },

    /**
     * Helper function whicn invokes search
     */
    search: function (value) {
        var searchModule = this.getCurrentSearchModule();
        var params = {};
        params.value = value;
        if (typeof searchModule != 'undefined') {
            params.searchModule = searchModule;
        }

        return this._search(params);
    },

    /**
     * Function which shows the search results
     */
    showSearchResults: function (data) {
        var aDeferred = jQuery.Deferred();
        var postLoad = function (data) {
            var blockMsg = jQuery(data).closest('.blockMsg');
            app.showScrollBar(jQuery(data).find('.contents'));
            blockMsg.position({
                my: "left bottom",
                at: "left bottom",
                of: "#globalSearchValue",
                offset: "1 -29"
            });
            aDeferred.resolve(data);
        }
        var params = {};
        params.data = data;
        params.cb = postLoad;
        params.css = {'width': 'auto', 'text-align': 'left'};
        //not showing overlay
        params.overlayCss = {'opacity': '0.2'};
        app.showModalWindow(params);
        return aDeferred.promise();
    },


    addSearchListener: function () {
        jQuery('.search-link .keyword-input').on('VT_SEARCH_INTIATED', function (e, args) {
            const element = jQuery(this),
                val = args.searchValue.trim(),
                minimumLength = Number(element.data('minLength')) || 2,
                searchModule = jQuery('#global-search-module').val(),
                params = {
                    module: 'Vtiger',
                    view: 'ListAjax',
                    mode: 'searchAll',
                    value: val,
                };

            if (val.length < minimumLength) {
                app.helper.showErrorNotification({message: element.data('minLengthMessage')});
                return;
            }

            if (searchModule) {
                params.searchModule = searchModule;
            }

            app.helper.showProgress();
            app.request.get({'url': '?' + jQuery.param(params)}).then(function (error, data) {
                app.helper.hideProgress();

                if (error != null) {
                    app.helper.showErrorNotification({message: error.message || error});
                    return;
                }

                app.helper.loadPageOverlay(data).then(function (modal) {
                    Vtiger_SearchList_Js.intializeListInstances(modal);
                });
            });
        });
    },

    registerModuleSelector: function () {
        const picker = jQuery('.global-search-module-picker'),
            moduleInput = jQuery('#global-search-module');

        if (!picker.length) {
            return;
        }

        picker.on('show.bs.dropdown', function () {
            const searchContainer = picker.closest('.search-link');

            picker.find('.dropdown-menu').css('width', searchContainer.outerWidth());
        });

        picker.on('click', '.global-search-module-option', function () {
            const option = jQuery(this),
                moduleName = option.attr('data-module') || '',
                moduleLabel = option.attr('data-label') || '',
                button = picker.find('.global-search-module-button'),
                options = picker.find('.global-search-module-option'),
                selectedIcon = picker.find('.global-search-selected-icon'),
                optionIcon = moduleName
                    ? option.find('.global-search-module-option-icon').contents().clone()
                    : jQuery('<i>', {class: 'fa fa-chevron-down'});

            moduleInput.val(moduleName).trigger('change');
            selectedIcon.empty().append(optionIcon);
            button.attr('title', moduleLabel);
            button.attr('aria-label', picker.attr('data-module-label') + ': ' + moduleLabel);
            options.removeClass('active').removeAttr('aria-current');
            option.addClass('active').attr('aria-current', 'true');
        });

        picker.on('click', '.global-search-filter-open', function () {
            const advanceSearchInstance = new Vtiger_AdvanceSearch_Js(),
                moduleName = jQuery('#global-search-module').val();

            advanceSearchInstance
                .setSearchModule(moduleName)
                .setModuleSelectionRequired(!moduleName)
                .initiateSearch().then(function () {
                    if (moduleName) {
                        jQuery('#searchModuleList').val(moduleName).trigger('change.select2');
                    }

                    advanceSearchInstance.selectBasicSearchValue();
                });
        });

        picker.on('click', '.global-search-module-search', function (event) {
            event.stopPropagation();
        });

        picker.on('input', '.global-search-module-search', function () {
            const searchValue = jQuery(this).val().toLocaleLowerCase().trim();

            picker.find('.global-search-module-option[data-module!=""]').each(function () {
                const option = jQuery(this),
                    searchableValues = (option.attr('data-search-values') || option.attr('data-label') || '')
                        .toLocaleLowerCase();

                option.closest('li').toggleClass('d-none', searchableValues.indexOf(searchValue) === -1);
            });
        });

        picker.on('hidden.bs.dropdown', function () {
            picker.find('.global-search-module-search').val('');
            picker.find('.global-search-module-option[data-module!=""]').closest('li').removeClass('d-none');
        });
    },

    registerEvents: function () {
        this._super();
        this.registerModuleSelector();
        this.addSearchListener();
    }

});

