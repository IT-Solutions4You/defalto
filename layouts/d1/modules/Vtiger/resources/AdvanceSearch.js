/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_AdvanceSearch_Js */
Vtiger_BasicSearch_Js("Vtiger_AdvanceSearch_Js", {

    //cache will store the search data
    cache: {}

}, {
    //container which will store the search elements
    elementContainer: false,
    //instance which represents advance filter
    advanceFilter: false,

    //states whether the user must select a module before building filter conditions
    moduleSelectionRequired: false,

    //states whether the validation is registred for filter elements
    filterValidationRegistered: false,

    //contains the filter form element
    filterForm: false,

    /**
     * Function which will give the container
     */
    getContainer: function () {
        return this.elementContainer;
    },

    /**
     *Function which is used to set the continaer
     *@params : container - element which represent the container
     *@return current instance
     */
    setContainer: function (container) {
        this.elementContainer = container;
        return this;
    },

    setModuleSelectionRequired: function (required) {
        this.moduleSelectionRequired = required;
        return this;
    },

    isModuleSelectionRequired: function () {
        return this.moduleSelectionRequired;
    },

    getFilterForm: function () {
        return jQuery('form[name="advanceFilterForm"]', this.getContainer());
    },

    isSearchShown: function () {
        return jQuery('#advanceSearchHolder').hasClass('slideDown');
    },

    hideSearch: function () {
        jQuery('#advanceSearchHolder').removeClass('slideDown');
    },

    isSearchHidden: function () {
        var advanceSearchHolder = jQuery('#advanceSearchHolder');
        return (advanceSearchHolder.children().length > 0 && (!advanceSearchHolder.hasClass('slideDown'))) ? true : false;
    },

    showSearch: function () {
        var advanceSearchHolder = jQuery('#advanceSearchHolder');
        advanceSearchHolder.addClass('slideDown');
    },


    /**
     * Function used to get the advance search ui
     * @return : deferred promise
     */
    getAdvanceSearch: function () {
        let aDeferred = jQuery.Deferred(),
            moduleName = app.getModuleName(),
            searchModule = this.getSearchModule(),
            moduleSelectionRequired = this.isModuleSelectionRequired() && !searchModule,
            cacheKey = moduleSelectionRequired ? '__module_selection__' : searchModule;

        //Exists in the cache
        if (cacheKey in Vtiger_AdvanceSearch_Js.cache) {
            aDeferred.resolve(Vtiger_AdvanceSearch_Js.cache[cacheKey]);

            return aDeferred.promise();
        }

        //if you are in settings then module should be vtiger
        if (app.getParentModuleName().length > 0) {
            moduleName = 'Vtiger';
        }

        const searchableModulesParams = {
            "module": moduleName,
            "view": "BasicAjax",
            "mode": "showAdvancedSearch",
            "source_module": searchModule,
            "module_selection_required": moduleSelectionRequired ? 1 : 0
        };

        app.helper.showProgress();
        app.request.post({data: searchableModulesParams}).then(function (err, data) {
            app.helper.hideProgress();

            if (err) {
                aDeferred.reject(err);
                return;
            }

            //add to cache
            Vtiger_AdvanceSearch_Js.cache[cacheKey] = data;
            aDeferred.resolve(data);
        }, function (error, err) {
            app.helper.hideProgress();
            aDeferred.reject(error);
        });

        return aDeferred.promise();
    },

    showAdvanceSearch: function (data) {
        let aDeferred = jQuery.Deferred(),
            advanceSearchHolder = jQuery('#advanceSearchHolder');

        if (advanceSearchHolder.length > 0) {
            advanceSearchHolder.removeClass('slideDown');

            if (typeof data !== 'undefined') {
                data = jQuery(data).find('#advanceSearchHolder').html();
                advanceSearchHolder.html(data);
            }

            advanceSearchHolder.addClass('slideDown');
            aDeferred.resolve(advanceSearchHolder.find('#advanceSearchContainer'));
        } else {
            app.helper.loadPageOverlay(data, {ignoreScroll: true}).then(function (container) {
                const overlay = jQuery(container),
                    loadedAdvanceSearchHolder = overlay.find('#advanceSearchHolder');

                loadedAdvanceSearchHolder.addClass('slideDown');
                aDeferred.resolve(loadedAdvanceSearchHolder.find('#advanceSearchContainer'));
            }, function (error) {
                aDeferred.reject(error);
            });
        }

        return aDeferred.promise();
    },

    /**
     * Function which intializes search
     */
    initiateSearch: function () {
        const aDeferred = jQuery.Deferred(),
            self = this;

        this.getAdvanceSearch().then(function (data) {
            self.showAdvanceSearch(data).then(function (container) {
                const filterContainer = container.find('.filterContainer');

                self.setContainer(container);
                vtUtils.showSelect2ElementView(self.getContainer().find('select.select2'));
                self.registerEvents();
                self.advanceFilter = filterContainer.length
                    ? new Vtiger_SearchAdvanceFilter_Js(filterContainer)
                    : false;
                aDeferred.resolve();
            }, function (error) {
                aDeferred.reject(error);
            });

        }, function (error) {
            aDeferred.reject(error);
        });

        return aDeferred.promise();
    },

    getSearchFieldNames: function () {
        const form = this.getFilterForm();

        return form.find('[name="searchFields"]').data('value') || [];
    },

    selectBasicSearchValue: function () {
        const value = jQuery('#search-keyword-input').val() || '';

        if (!value.length) {
            return;
        }

        const form = this.getFilterForm(),
            searchFieldNames = this.getSearchFieldNames(),
            anyConditionContainer = form.find('.anyConditionContainer'),
            basicFieldSelect = anyConditionContainer.find('.basic select[name="columnname"]');

        searchFieldNames.forEach(function (fieldName) {
            if (!basicFieldSelect.find('option[data-field-name="' + fieldName + '"]').length) {
                return;
            }

            anyConditionContainer.find('.addCondition button').trigger('click');

            const conditionRow = anyConditionContainer.find('.conditionList .conditionRow:last'),
                fieldSelectElement = conditionRow.find('select[name="columnname"]'),
                comparatorSelectElement = conditionRow.find('select[name="comparator"]');

            fieldSelectElement.find('option[data-field-name="' + fieldName + '"]').prop('selected', true);
            fieldSelectElement.trigger('change').trigger('liszt:updated');

            comparatorSelectElement.find('option[value="c"]').prop('selected', true);
            comparatorSelectElement.trigger('change').trigger('liszt:updated');

            conditionRow.find('.fieldUiHolder [data-value="value"]').first().val(value).trigger('change');
        });
    },

    /**
     * Function which invokes search
     */
    search: function () {
        let conditionValues = this.advanceFilter.getValues(),
            params = {
                module: this.getSearchModule()
            },
            searchParams = new Array();

        for (var index in conditionValues) {
            let conditionSpecificValues = conditionValues[index]['columns'],
                conditionSpecificParams = new Array();

            for (var i in conditionSpecificValues) {
                let params1 = new Array(),
                    fieldName = conditionSpecificValues[i]['columnname'].split(":")[2];

                params1.push(fieldName);
                params1.push(conditionSpecificValues[i]['comparator']);
                params1.push(conditionSpecificValues[i]['value']);
                conditionSpecificParams.push(params1);
            }

            searchParams.push(conditionSpecificParams);
        }

        params.search_params = JSON.stringify(searchParams);
        params.nolistcache = 1;

        return this._search(params);
    },

    /**
     * Function which shows search results in proper manner
     * @params : data to be shown
     */
    showSearchResults: function (data) {
        var thisInstance = this;
        var aDeferred = jQuery.Deferred();
        var postLoad = function (data) {
            var blockMsg = jQuery(data).closest('.blockMsg');
            app.showScrollBar(jQuery(data).find('.contents'));
            aDeferred.resolve(data);
        }

        var unblockcd = function () {
            thisInstance.getContainer().remove();
        }

        var html = '<div class="row-fluid">' +
            '<span class="span4 searchHolder" style="width:280px;"></span>' +
            '<span class="span8 filterHolder  marginLeftZero hide"></span>' +
            '</div>';
        var jQhtml = jQuery(html);
        jQuery('.searchHolder', jQhtml).html(data);

        data = jQhtml;

        var params = {};
        params.data = data;
        params.cb = postLoad;
        params.css = {'width': '20%', 'text-align': 'left'};
        params.overlayCss = {'opacity': '0.2'};
        params.unblockcb = unblockcd;
        app.showModalWindow(params);

        return aDeferred.promise();
    },

    /**
     * Function which will save the filter
     */
    saveFilter: function (params) {
        var aDeferred = jQuery.Deferred();
        params.source_module = this.getSearchModule();
        params.status = 1;
        params.advfilterlist = JSON.stringify(this.advanceFilter.getValues(false));

        params.module = 'CustomView';
        params.action = 'Save';

        app.request.post({data: params}).then(function (error, data) {
            aDeferred.resolve(data);
        })
        return aDeferred.promise();
    },

    /**
     * Function which will save the filter and show the list view of new custom view
     */
    saveAndViewFilter: function (params) {
        app.helper.showProgress();
        this.saveFilter(params).then(
            function (response) {
                app.helper.hideProgress();
                var url = response['listviewurl'];
                window.location.href = url;
            },
            function (error) {

            }
        );
    },

    initiateListInstance: function (container) {
        var listInstance = new Vtiger_AdvanceSearchList_Js();
        listInstance.setListViewContainer(container.find('.moduleResults-container')).setModuleName(this.getSearchModule());
        listInstance.registerEvents();
    },


    /**
     * Function which will perform search and other operaions
     */
    performSearch: function () {
        let self = this;

        this.search().then(function (data) {
            let searchResultContainer = jQuery('#searchResults-container');

            if (searchResultContainer.find('.searchResults').length > 0) {
                searchResultContainer.find('.searchResults').html(data);
            } else {
                searchResultContainer.append(data);
            }
            self.initiateListInstance(jQuery('.searchResults'));
            self.registerShowFiler();
            self.hideSearch();
        });
    },

    /**
     * Function which will perform the validation for the advance filter fields
     * @return : deferred promise - resolves if validation succeded if not failure
     */
    performValidation: function () {
        let self = this;

        self.formValidationDeferred = jQuery.Deferred();
        self.formValidationDeferred.resolve();

        let controlForm = self.getFilterForm(),
            validationDone = function (form, status) {
                if (status) {
                    self.formValidationDeferred.resolve();
                } else {
                    self.formValidationDeferred.reject();
                }
            };

        //To perform validation registration only once
        if (!self.filterValidationRegistered) {
            self.filterValidationRegistered = true;

            controlForm.validationEngine({
                'onValidationComplete': validationDone
            });
        }
        //This will trigger the validation
        controlForm.submit();

        return self.formValidationDeferred.promise();
    },

    advanceSearchTriggerIntiatorHandler: function () {
        const self = this;

        if (self.isSearchShown()) {
            self.hideSearch();
            return;
        }

        if (self.isSearchHidden()) {
            self.showSearch();
            return;
        }

        self.initiateSearch().then(function () {
            self.selectBasicSearchValue();
        });
    },

    /**
     * Function which will register the show filer invocation
     */
    registerShowFiler: function () {
        const thisInstance = this,
            searchResultsContainer = this.getContainer().closest('#searchResults-container');

        searchResultsContainer.off('click.advanceSearch', '#showFilter');
        searchResultsContainer.on('click.advanceSearch', '#showFilter', function (e) {
            thisInstance.showAdvanceSearch();
        });
    },

    /**
     * Function which will register events
     */
    registerEvents: function () {
        const self = this,
            container = this.getContainer(),
            searchResultsContainer = container.closest('#searchResults-container');

        container.on('change', '#searchModuleList', function (e) {
            const selectElement = jQuery(e.currentTarget),
                selectedModuleName = selectElement.val();

            self.setSearchModule(selectedModuleName);

            self.initiateSearch().then(function () {
                self.selectBasicSearchValue();
            });
        });

        searchResultsContainer.find('#advanceSearchButton').on('click', function (e) {
            //If no module is selected
            if (!self.getSearchModule().length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_MODULE')});
                return;
            }

            self.performValidation().then(function () {
                self.performSearch();
            }, function () {

            });
        });

        searchResultsContainer.find('#advanceIntiateSave').on('click', function (e) {
            //If no module is selected
            if (!self.getSearchModule().length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_MODULE')});
                return;
            }

            const currentElement = jQuery(e.currentTarget);
            currentElement.addClass('hide');
            const actionsContainer = currentElement.closest('.actions');

            jQuery('input[name="viewname"]', actionsContainer).removeClass('hide').addClass('slideRight');
            searchResultsContainer.find('#advanceSave').removeClass('hide');
        });

        searchResultsContainer.find('#advanceSave').on('click', function (e) {
            const actionsContainer = jQuery(e.currentTarget).closest('.actions'),
                filterNameField = jQuery('input[name="viewname"]', actionsContainer),
                value = filterNameField.val();

            if (value.length <= 0) {
                vtUtils.showValidationMessage(filterNameField, app.vtranslate('JS_REQUIRED_FIELD'), {
                    position: {
                        my: 'bottom left',
                        at: 'top left',
                        container: container.closest('.data')
                    }
                });
                return;
            }

            //If no module is selected
            if (!self.getSearchModule().length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_MODULE')});
                return;
            }

            self.performValidation().then(function () {
                const params = {};
                params.viewname = value;
                self.saveAndViewFilter(params);
            });
        });

        //DO nothing on submit of filter form
        self.getFilterForm().on('submit', function (e) {
            e.preventDefault();
        })

        //To set the search module with the currently selected values.
        self.setSearchModule(container.find('#searchModuleList').val() || '');
    }
})
