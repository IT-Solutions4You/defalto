/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/


Vtiger.Class('Vtiger_BasicSearch_Js',{},{
	//stores the module that need to be searched
	searchModule : false,
	
	//stores the module that need to be searched which is selected by the user
	currentSearchModule : false,
	
	/**
	 * Function to get the search module
	 */
	getSearchModule : function() {
		if(this.searchModule === false) {
			//default gives current module
			var module = app.getModuleName();
			if(typeof this.getCurrentSearchModule() != 'undefined') {
				module = this.getCurrentSearchModule();
			}
			if(app.getParentModuleName().length > 0) {
				module = '';
			}
			
			this.setSearchModule(module);
		}
		return this.searchModule;
	},

	/**
	 * Function to set the search module
	 */
	setSearchModule : function(moduleName) {
		this.searchModule = moduleName;
		return this;
	},
	
	/**
	 * Function to get the user selected search module
	 */
	getCurrentSearchModule : function() {
		if(this.currentSearchModule === false) {
			this.currentSearchModule = jQuery('#basicSearchModulesList').val();
		}
		return this.currentSearchModule;
	},
	
	/**
	 * Function which will perform the search
	 */
	_search : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params == 'undefined') {
			params = {};
		}

		params.view = 'ListAjax';
		params.mode = 'showSearchResults';
		params.transformedSearchParams = true;

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
			//if you are in Settings then module should be Vtiger for normal text search
			if(app.getParentModuleName().length > 0) {
				params.module = 'Vtiger';
			}
		}
		app.helper.showProgress();
		app.request.post({data:params}).then(
			function(err, data){
				app.helper.hideProgress();
				aDeferred.resolve(data);
			},

			function(error,err){
				aDeferred.reject(error);
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Helper function whicn invokes search
	 */
	search : function(value) {
		var searchModule = this.getCurrentSearchModule();
		var params = {};
		params.value = value;
		if(typeof searchModule != 'undefined') {
			params.searchModule = searchModule;
		}
		
		return this._search(params);
	},

	/**
	 * Function which shows the search results
	 */
	showSearchResults : function(data){
		var aDeferred = jQuery.Deferred();
		var postLoad = function(data) {
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
		params.data = data ;
		params.cb = postLoad;
		params.css = {'width':'auto','text-align':'left'};
		//not showing overlay
		params.overlayCss = {'opacity':'0.2'};
		app.showModalWindow(params);
		return aDeferred.promise();
	},

    
	addSearchListener : function () {
		jQuery('.search-link .keyword-input').on('VT_SEARCH_INTIATED',function(e,args){
			var val = args.searchValue;
			var url = '?module=Vtiger&view=ListAjax&mode=searchAll&value='+encodeURIComponent(val);
			app.helper.showProgress();
			app.request.get({'url': url}).then(function (error, data) {
				if (error == null) {
					app.helper.hideProgress();
					app.helper.loadPageOverlay(data).then(function (modal) {
						modal.find('.keyword-input').val(jQuery('.keyword-input').val());
						Vtiger_SearchList_Js.intializeListInstances(modal);
					});
				}
			});
		});
	},

	registerEvents : function () {
		this._super();
		this.addSearchListener();
	}

});

