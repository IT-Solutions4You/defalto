/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/** @var Vtiger_AdvanceSearch_Js */
Vtiger_BasicSearch_Js("Vtiger_AdvanceSearch_Js",{

	//cache will store the search data
	cache : {}

},{
	//container which will store the search elements
	elementContainer : false,
	//instance which represents advance filter
	advanceFilter : false,

	//states whether the validation is registred for filter elements
	filterValidationRegistered : false,

	//contains the filter form element
	filterForm : false,

	/**
	 * Function which will give the container
	 */
	getContainer : function() {
		return this.elementContainer;
	},

	/**
	 *Function which is used to set the continaer
	 *@params : container - element which represent the container
	 *@return current instance
	 */
	setContainer : function(container) {
		this.elementContainer = container;
		return this;
	},

	getFilterForm : function() {
		return jQuery('form[name="advanceFilterForm"]',this.getContainer());
	},
    
    isSearchShown : function() {
        return jQuery('#advanceSearchHolder').hasClass('slideDown');
    },
    
    hideSearch : function() {
        jQuery('#advanceSearchHolder').removeClass('slideDown');
    },
    
    isSearchHidden : function() {
       var advanceSearchHolder = jQuery('#advanceSearchHolder');
       return (advanceSearchHolder.children().length > 0 && (!advanceSearchHolder.hasClass('slideDown'))) ? true : false;
    },
    
    showSearch : function() {
         var advanceSearchHolder = jQuery('#advanceSearchHolder');
         advanceSearchHolder.addClass('slideDown');
    },
    

	/**
	 * Function used to get the advance search ui
	 * @return : deferred promise
	 */
	getAdvanceSearch : function() {
		let aDeferred = jQuery.Deferred(),
			moduleName = app.getModuleName(),
			searchModule = this.getSearchModule();

		//Exists in the cache
		if(searchModule in Vtiger_AdvanceSearch_Js.cache) {
			aDeferred.resolve(Vtiger_AdvanceSearch_Js.cache[searchModule]);

			return aDeferred.promise();
		}
        
        //if you are in settings then module should be vtiger
        if(app.getParentModuleName().length > 0) {
            moduleName = 'Vtiger';
        }
        
        let searchableModulesParams = {
			"module":moduleName,
			"view"	: "BasicAjax",
			"mode"	: "showAdvancedSearch",
			"source_module": searchModule
        };
        
        app.helper.showProgress();
		app.request.post({data: searchableModulesParams}).then(function (err, data) {
			app.helper.hideProgress();
			//add to cache
			Vtiger_AdvanceSearch_Js.cache[searchModule] = data;
			aDeferred.resolve(data);
		}, function (error, err) {
			aDeferred.reject(error);
		});

		return aDeferred.promise();
	},
    
    showAdvanceSearch : function (data) {
        let aDeferred = jQuery.Deferred(),
			advanceSearchHolder = jQuery('#advanceSearchHolder');

        if(advanceSearchHolder.length >0) {
			advanceSearchHolder.removeClass('slideDown');
            data = jQuery(data).find('#advanceSearchHolder').html();
			advanceSearchHolder.html(data).addClass('slideDown');
        }else{
            app.helper.loadPageOverlay(data, {ignoreScroll: true}).then(function(container){
                jQuery('#advanceSearchHolder').addClass('slideDown');
            });
        }
        aDeferred.resolve();
        return aDeferred.promise();
    },

	/**
	 * Function which intializes search
	 */
	initiateSearch: function () {
		let aDeferred = jQuery.Deferred(),
			self = this;

		this.getAdvanceSearch().then(function (data) {
			self.showAdvanceSearch(data).then(function () {
				self.setContainer(jQuery('#advanceSearchContainer'));
				vtUtils.showSelect2ElementView(self.getContainer().find('select.select2'));
				self.registerEvents();
				self.advanceFilter = new Vtiger_SearchAdvanceFilter_Js(jQuery('.filterContainer'));
				aDeferred.resolve();
			})

		}, function (error) {
			aDeferred.reject();
		});

		return aDeferred.promise();
	},
    
    getNameFields : function() {
        var form = this.getFilterForm();
        return form.find('[name="labelFields"]').data('value');
    },
    
    selectBasicSearchValue : function() {
      let value = jQuery('.keyword-input').val();
      if(value.length > 0 ) {
		  let form = this.getFilterForm(),
			  labelFieldList = this.getNameFields();

          if(typeof labelFieldList == 'undefined' || labelFieldList.length == 0) {
              return;
          }

          let anyConditionContainer = form.find('.anyConditionContainer');

          for(let index in labelFieldList){
            let labelFieldName = labelFieldList[index];

            if(0 !== index) {
                //By default one condition exits , only if you have multiple label fields you have add one more condition
                anyConditionContainer.find('.addCondition').find('button').trigger('click');
            }

            let conditionRow = anyConditionContainer.find('.conditionList').find('.conditionRow:last'),
				fieldSelectElemnt = conditionRow.find('select[name="columnname"]');

            fieldSelectElemnt.find('option[data-field-name="'+ labelFieldName +'"]').attr('selected','selected');
            fieldSelectElemnt.trigger('change').trigger('liszt:updated');

            let comparatorSelectElemnt = conditionRow.find('select[name="comparator"]');
            //select the contains value
            comparatorSelectElemnt.find('option[value="c"]').attr('selected','selected');
            comparatorSelectElemnt.trigger('liszt:updated');

            let valueElement = conditionRow.find('[name="'+labelFieldName+'"]');
            valueElement.val(value);
          }
          
      }
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
	showSearchResults : function(data){
		var thisInstance = this;
		var aDeferred = jQuery.Deferred();
		var postLoad = function(data) {
			var blockMsg = jQuery(data).closest('.blockMsg');
			app.showScrollBar(jQuery(data).find('.contents'));
			aDeferred.resolve(data);
		}

		var unblockcd = function(){
			thisInstance.getContainer().remove();
		}

		var html = '<div class="row-fluid">'+
						'<span class="span4 searchHolder" style="width:280px;"></span>'+
						'<span class="span8 filterHolder  marginLeftZero hide"></span>'+
					'</div>';
		var jQhtml = jQuery(html);
		jQuery('.searchHolder',jQhtml).html(data);

		data = jQhtml;

		var params = {};
		params.data = data;
		params.cb = postLoad;
		params.css = {'width':'20%','text-align':'left'};
		params.overlayCss = {'opacity':'0.2'};
		params.unblockcb = unblockcd;
		app.showModalWindow(params);

		return aDeferred.promise();
	},

	/**
	 * Function which will save the filter
	 */
	saveFilter : function(params) {
		var aDeferred = jQuery.Deferred();
		params.source_module = this.getSearchModule();
		params.status = 1;
		params.advfilterlist = JSON.stringify(this.advanceFilter.getValues(false));

		params.module = 'CustomView';
		params.action = 'Save';

		app.request.post({data:params}).then(function(error,data){
			aDeferred.resolve(data);
		})
		return aDeferred.promise();
	},

	/**
	 * Function which will save the filter and show the list view of new custom view
	 */
	saveAndViewFilter : function(params){
        app.helper.showProgress();
		this.saveFilter(params).then(
			function(response){
                app.helper.hideProgress();
				var url = response['listviewurl'];
				window.location.href=url;
			},
			function(error) {

			}
		);
	},
    
    initiateListInstance : function(container)   {
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
	registerShowFiler : function() {
		var thisInstance = this;
		jQuery('#showFilter').on('click',function(e){
			thisInstance.showAdvanceSearch();
		});
	},

	/**
	 * Function which will register events
	 */
	registerEvents : function() {
		let self = this,
			container = this.getContainer();
        
		container.on('change','#searchModuleList', function(e){
			let selectElement = jQuery(e.currentTarget),
				selectedModuleName = selectElement.val();

			self.setSearchModule(selectedModuleName);

			self.initiateSearch().then(function(){
				self.selectBasicSearchValue();
            });
		});

		jQuery('#advanceSearchButton').on('click', function(e){
			let searchModule = self.getSearchModule();
               //If no module is selected
			if(searchModule.length <= 0) {
				app.getChosenElementFromSelect(jQuery('#searchModuleList')).validationEngine('showPrompt', app.vtranslate('JS_SELECT_MODULE'), 'error','topRight',true)
				return;
			}
			self.performValidation().then(function () {
				self.performSearch();
			}, function () {

			});
		});

		jQuery('#advanceIntiateSave').on('click', function(e){
			let currentElement = jQuery(e.currentTarget);
			currentElement.addClass('hide');
			let actionsContainer = currentElement.closest('.actions');

			jQuery('input[name="viewname"]',actionsContainer).removeClass('hide').addClass('slideRight');
			jQuery('#advanceSave').removeClass('hide');
		});

		jQuery('#advanceSave').on('click',function(e){
			let actionsContainer = jQuery(e.currentTarget).closest('.actions'),
				filterNameField = jQuery('input[name="viewname"]',actionsContainer),
				value = filterNameField.val();

			if(value.length <= 0) {
                vtUtils.showValidationMessage(filterNameField, app.vtranslate('JS_REQUIRED_FIELD'), {
                    position: {
                        my: 'bottom left',
                        at: 'top left',
                        container: container.closest('.data')
                    }
                });
				return;
			}

			let searchModule = self.getSearchModule();
			//If no module is selected
			if(searchModule.length <= 0) {
				app.getChosenElementFromSelect(jQuery('#searchModuleList')).validationEngine('showPrompt', app.vtranslate('JS_SELECT_MODULE'), 'error','topRight',true)
				return;
			}

			self.performValidation().then(function(){
				let params = {};
				params.viewname = value;
				self.saveAndViewFilter(params);
			});
		});

		//DO nothing on submit of filter form
		self.getFilterForm().on('submit',function(e){
			e.preventDefault();
		})

		//To set the search module with the currently selected values.
		self.setSearchModule(jQuery('#searchModuleList').val());
	}
})
