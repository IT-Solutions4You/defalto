/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/
/** @var Vtiger_Detail_Js */
Vtiger.Class("Vtiger_Detail_Js",{

	detailInstance : false,
	changeAssignedUserEvent: 'change.assigned.user',
	PreAjaxSaveEvent : 'PreAjaxSaveEvent',
	PostAjaxSaveEvent : 'PostAjaxSaveEvent',
	getInstance: function(){
		if( Vtiger_Detail_Js.detailInstance == false ){
			var module = app.getModuleName();
			var view = app.view;
			var moduleClassName = module+"_"+view+"_Js";
			var fallbackClassName = Vtiger_Detail_Js;
			if(typeof window[moduleClassName] != 'undefined'){
				var instance = new window[moduleClassName]();
			}else{
				var instance = new fallbackClassName();
			}
			Vtiger_Detail_Js.detailInstance = instance;
		}
		return Vtiger_Detail_Js.detailInstance;
	},

		getInstanceByModuleName : function(moduleName){
		if(typeof moduleName == "undefined"){
			moduleName = app.getModuleName();
		}
		var parentModule = app.getParentModuleName();
		if(parentModule == 'Settings'){
			var moduleClassName = parentModule+"_"+moduleName+"_Detail_Js";
			if(typeof window[moduleClassName] == 'undefined'){
				moduleClassName = moduleName+"_Detail_Js";
			}
			var fallbackClassName = parentModule+"_Vtiger_Detail_Js";
			if(typeof window[fallbackClassName] == 'undefined') {
				fallbackClassName = "Vtiger_Detail_Js";
			}
		} else {
			moduleClassName = moduleName+"_Detail_Js";
			fallbackClassName = "Vtiger_Detail_Js";
		}
		if(typeof window[moduleClassName] != 'undefined'){
			var instance = new window[moduleClassName]();
		}else{
			var instance = new window[fallbackClassName]();
		}
		return instance;
	},

	triggerSendSms: function(detailActionUrl, module) {
		var self = this.getInstance();
		self.sendSMS(detailActionUrl,module);
	},

	showUpdates : function(element){
		jQuery(".historyButtons").find("button").removeAttr("disabled").removeClass("btn-success");
		var currentElement = jQuery(element);
		currentElement.attr("disabled","disabled").addClass("btn-success");

		var params = [];
		var recordId = jQuery('#recordId').val();
		params.url = "index.php?view=Detail&module="+app.getModuleName()+"&mode=showRecentActivities&record="+recordId;

		app.helper.showProgress();
		app.request.get(params).then(function(error,response){
			app.helper.hideProgress();
			jQuery(".HistoryContainer").find(".data-body").html(response);
		});
	},


	checkSMSStatus: function(url) {
		app.request.post({url: url}).then(
				function(err, data) {
					var status = data['status'];
					if(status == 'Failed'){
						var message = data['message'];
						app.helper.showErrorNotification({title: status, message: message});
					}
					else if (status == null) {
						app.helper.showErrorNotification({title: 'Error', message: 'Failed to send SMS.'});
					}
					else {
						var message = data['message'];
						app.helper.showErrorNotification({title:status, message:message});
					}
				});
	},

	/*
	 * function to trigger delete record action
	 * @params: delete record url.
	 */
	deleteRecord : function(deleteRecordActionUrl) {
		var detailInstance = window.app.controller();
		detailInstance.remove(deleteRecordActionUrl);
	},


	/**
	 * Function to trigger Transfer Ownership
	 * @param {type} massActionUrl
	 * @returns {undefined}
	 */
	triggerTransferOwnership : function(massActionUrl){
		var thisInstance = this;
		var params = app.convertUrlToDataParams(massActionUrl);
		app.helper.showProgress();
		app.request.post({data:params}).then(
			function(error, data) {
				app.helper.hideProgress();
				app.helper.showModal(data);
				var form = jQuery('form#changeOwner');
				var isFormExists = form.length;
				if(isFormExists){
					thisInstance.transferOwnershipSave(form);
				}
			}
		);
	},

	/**
	 * Saving transfer ownership 
	 * @param {type} form
	 * @returns {undefined}
	 */
	transferOwnershipSave : function (form){
		 form.on("click","button[name='saveButton']",function(e){
			e.preventDefault();
			var rules = {};
			rules["related_modules"] = {'required' : true};
			rules["transferOwnerId"] = {'required' : true};
			var params = {
				rules : rules,
				submitHandler: function(form) {
					// to Prevent submit if already submitted
					jQuery(form).find("button[name='saveButton']").attr("disabled","disabled");
					if(this.numberOfInvalids() > 0) {
						return false;
					}
					var transferOwner = jQuery('#transferOwnerId').val();
					var relatedModules = jQuery('#related_modules').val();
					var recordId = jQuery('#recordId').val();
					var reqParams = {
						'module': app.getModuleName(),
						'action' : 'TransferOwnership',
						'record':recordId,
						'transferOwnerId' : transferOwner,
						'related_modules' : relatedModules
					};
					app.request.post({data:reqParams}).then(
						function(error,data) {
							if(error === null){
								jQuery('.vt-notification').remove();
								app.helper.hideModal();
                                app.helper.showSuccessNotification({message:app.vtranslate('JS_RECORDS_TRANSFERRED_SUCCESSFULLY')});
                            } else {
								app.event.trigger('post.save.failed', error);
								jQuery(form).find("button[name='saveButton']").removeAttr('disabled');
							}
						}
					);
				}
			};
			validateAndSubmitForm(form,params);
		 });
	},

	/*
	 * function to trigger send Email
	 * @params: send email url , module name.
	 */
	triggerSendEmail : function(detailActionUrl, module){
		var currentInstance = window.app.controller();
		var parentRecord = new Array();
		var params = {};
		parentRecord.push(currentInstance.getRecordId());
		var urlParams = app.convertUrlToDataParams(detailActionUrl);
		urlParams['selected_ids'] = parentRecord;
		Vtiger_Index_Js.showComposeEmailPopup(urlParams);
	},

	/**
	 * Function to show the content of a file in an iframe
	 * @param {type} e
	 * @param {type} recordId
	 * @returns {undefined}
	 */
	previewFile: function (e, recordId,attachmentId) {
		Vtiger_Index_Js.previewFile(e, recordId,attachmentId);
	},
	openDetail() {
		$('[data-link-key="LBL_RECORD_DETAILS"]').trigger('click');
	},
},{
	registerSummaryHandlers: true,
	detailViewSummaryTabLabel : 'LBL_RECORD_SUMMARY',
	detailViewDetailTabLabel : 'LBL_RECORD_DETAILS',
	detailViewHistoryTabLabel : 'LBL_HISTORY',
	detailViewRecentCommentsTabLabel : 'ModComments',
	detailViewRecentActivitiesTabLabel : 'Appointments',
	detailViewRecentDocumentsLabel : 'Documents',
	widgetPostLoad : 'Vtiger.Widget.PostLoad',
	_moduleName : false,
	targetPicklistChange : false,
	targetPicklist : false,
	sourcePicklistname : false,

	getModuleName : function() {
		if(this._moduleName != false){
			return this._moduleName;
		}
		return app.module();
	},

	setModuleName : function(module){
		this._moduleName = module;
		return this;
	},

		registerOverlayEditEvents: function(module, container) {
				var editInstance = Vtiger_Edit_Js.getInstanceByModuleName(module);
				editInstance.setModuleName(module);
				var editContainer = container.find('.overlayEdit');
				editInstance.setEditViewContainer(editContainer);
				editInstance.registerEvents(false);
		},

		setContentHolder: function(container){
			this.detailViewContentHolder = container;
		},

		overlayMode: false,
		setOverlayDetailMode: function(option){
			this.overlayMode = option;
		},
		getOverlayDetailMode: function(){
			return this.overlayMode;
		},
		quickPreviewMode: false,
		setQuickPreviewDetailMode: function (option) {
			this.quickPreviewMode = option;
		},
		getQuickPreviewDetailMode: function () {
			return this.quickPreviewMode;
		},
		registerRelatedRecordSave: function(){
			var thisInstance = this;
			app.event.on('post.overLayEditView.loaded',function(e, container){
				jQuery('#EditView').vtValidate({
					submitHandler : function(form){
						window.onbeforeunload = null;
						var e = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
						app.event.trigger(e);
						if(e.isDefaultPrevented()) {
							return false;
						}
						var formData = new FormData(form);
						var postParams = {
							data: formData,
							contentType: false,
							processData: false
						};
						app.helper.showProgress();
						app.request.post(postParams).then(function(err,data){
							app.helper.hideProgress();
							if (err === null) {
								jQuery('.vt-notification').remove();
								app.helper.hidePageContentOverlay();
								var relatedModuleName = formData.module;
								let relatedController = thisInstance.getRelatedController(relatedModuleName);

								if(relatedController) {
									relatedController.loadRelatedList();
								}
							} else {
								app.event.trigger('post.save.failed', err);
							}
					});
					return false;
					}
				});

				jQuery('#EditView').find('.saveButton').on('click', function(e){
					window.onbeforeunload = null;
				});
			});
		},

	referenceFieldNames : {
		'Accounts' : 'parent_id',
		'Contacts' : 'contact_id',
		'Leads' : 'parent_id',
		'Potentials' : 'parent_id',
		'HelpDesk' : 'parent_id',
		'Project'  : 'projectid'
	},

	init : function() {
		this.addComponents();
	},

	addComponents : function() {
		var emailPreviewClassName = 'ITS4YouEmails_EmailPreview_Js';
	this.addIndexComponent();
		this.addComponent(emailPreviewClassName);
		this.addComponent('Vtiger_Tag_Js');
	},



	addIndexComponent : function() {
		this.addModuleSpecificComponent('Index','Vtiger',app.getParentModuleName());
	},

	/**
	 * Function which will give the detail view form
	 * @return : jQuery element
	 */
	detailViewForm : false,
	getForm : function() {
		if(this.detailViewForm == false) {
			this.detailViewForm = jQuery('#detailView');
		}
		return this.detailViewForm;
	},

	detailViewContainer : false,
	getDetailViewContainer : function(){
		if(this.detailViewContainer === false){
			this.detailViewContainer = jQuery('.detailViewContainer');
		}
		return this.detailViewContainer;
	},
	setDetailViewContainer : function(container){
		this.detailViewContainer = container;
	},

	detailViewContentHolder : false,
	getContentHolder : function() {
		if(this.detailViewContentHolder == false) {
			this.detailViewContentHolder = jQuery('div.details');
		}
		return this.detailViewContentHolder;
	},

	/**
	 * Function to load related list
	 */
	loadRelatedListRecords : function(urlParams) {
		var self = this;
		var aDeferred = jQuery.Deferred();
		var defParams = self.getDefaultParams();
		urlParams = jQuery.extend(defParams, urlParams);
		app.helper.showProgress();
		app.request.get({data:urlParams}).then(function(err, res){
			aDeferred.resolve(res);
			var container = jQuery('.relatedContainer');
			container.html(res);
			app.helper.hideProgress();
			app.event.trigger("post.relatedListLoad.click",container.find(".searchRow"));
		});
		return aDeferred.promise();
	},
	sendSMS: function(detailActionUrl, module){
		var self = this;
		app.helper.checkServerConfig(module).then(function(data) {
			if (data == true) {
				var cb = function(container) {
					$('#phoneFormatWarningPop').popover();
				}
				self.sendSMSAction(detailActionUrl, cb);
			} else {
				app.helper.showAlertBox({message:app.vtranslate('JS_SMS_SERVER_CONFIGURATION')})
			}
		});
	},
	sendSMSAction: function(detailActionUrl, callBackFunction) {
		var self = this;
		var selectedIds = new Array();
		selectedIds.push(self.getRecordId());
		var postData = {
			"selected_ids": JSON.stringify(selectedIds)
		};
		app.request.post({url:detailActionUrl, data:postData, dataType:"html"}).then(
				function(err, data) {
					if (data) {
						app.helper.showModal(data);
						if (typeof callBackFunction == 'function') {
							callBackFunction(data);
						}
					}
				});
	},

	/**
	 * Function to fetch default params 
	 */
	getDefaultParams : function() {
		var module = app.module();

		var activeModule = jQuery(".related-tabs li.active");
		var relatedModule = activeModule.attr("data-module")
		var label = activeModule.attr("data-label-key");
		var relationId = activeModule.attr("data-relation-id");
		var orderBy = jQuery('#orderBy').val();
		var sortOrder = jQuery("#sortOrder").val();
		var pageNumber = jQuery('#pageNumber').val();

		var recordId = jQuery('#recordId').val();
		var params = {
			'module': module,
			'relatedModule': relatedModule,
			'view' : "Detail",
			'record' : recordId,
			'page' : pageNumber,
			'mode' : 'showRelatedList',
			'relationId' : relationId,
			'tab_label' : label,
			'orderby' : orderBy,
			'sortorder' : sortOrder
		};
		params.search_params = JSON.stringify(this.getRelatedListSearchParams());
		params.nolistcache = (jQuery('#noFilterCache').val() == 1) ? 1 : 0;
		return params;
	},

	/**
	 * Function to fetch search params
	 */
	getRelatedListSearchParams : function() {
		var detailViewContainer = this.getDetailViewContainer();
		var relatedListTable = detailViewContainer.find('.searchRow');
		var searchParams = [];
		var currentSearchParams = [];
		if(jQuery('#currentSearchParams').val()) {
			currentSearchParams = JSON.parse(jQuery('#currentSearchParams').val());
		}
		relatedListTable.find('.listSearchContributor').each(function(index,domElement){
			var searchInfo = [];
			var searchContributorElement = jQuery(domElement);
			var fieldName = searchContributorElement.attr('name');
			var fieldInfo = related_uimeta.field.get(fieldName);

			if(fieldName in currentSearchParams) {
				delete currentSearchParams[fieldName];
			}

			var searchValue = searchContributorElement.val();

			if(typeof searchValue == "object") {
				if(searchValue == null) {
					searchValue = "";
				}else{
					searchValue = searchValue.join(',');
				}
			}
			searchValue = searchValue.trim();
			if(searchValue.length <=0 ) {
				//continue
				return true;
			}
			var searchOperator = 'c';
			if(fieldInfo.type == "date" || fieldInfo.type == "datetime") {
				searchOperator = 'bw';
			}else if (fieldInfo.type == 'percentage' || fieldInfo.type == "double" || fieldInfo.type == "integer"
				|| fieldInfo.type == 'currency' || fieldInfo.type == "number" || fieldInfo.type == "boolean" ||
				fieldInfo.type == "picklist") {
				searchOperator = 'e';
			}
			var storedOperator = searchContributorElement.parent().parent().find('.operatorValue').val();
			if(storedOperator) {
				searchOperator = storedOperator;
				storedOperator = false;
			}
			searchInfo.push(fieldName);
			searchInfo.push(searchOperator);
			searchInfo.push(searchValue);
			searchInfo.push(fieldInfo.type);
			searchParams.push(searchInfo);
		});
		for(var i in currentSearchParams) {
			var fieldName = currentSearchParams[i]['fieldName'];
			var searchValue = currentSearchParams[i]['searchValue'];
			var searchOperator = currentSearchParams[i]['comparator'];
			if(fieldName== null || fieldName.length <=0 ){
				continue;
			}
			var searchInfo = [];
			searchInfo.push(fieldName);
			searchInfo.push(searchOperator);
			searchInfo.push(searchValue);
			searchParams.push(searchInfo);
		}
		var params = [];
		params.push(searchParams);
		return params;
	},

	getTabContainer : function(){
		return jQuery('div.related-tabs');
	},

	getRecordId : function(){
		return app.getRecordId();
	},

	getRelatedTabs : function() {
		return this.getTabContainer().find('li');
	},

	deSelectAllrelatedTabs : function() {
		this.getRelatedTabs().removeClass('active');
	},

	markRelatedTabAsSelected : function(tabElement){
		tabElement.addClass('active');
	},

	/*
	 * Function to register the submit event for Send Sms
	 */
	registerSendSmsSubmitEvent: function() {
		var thisInstance = this;
		jQuery('body').on('submit', '#massSave', function(e) {
			var form = jQuery(e.currentTarget);
			form.vtValidate({onsubmit : false});
			if(!form.valid()) {
				return false;
			}
			var smsTextLength = form.find('#message').val().length;
			if (smsTextLength > 160) {
				app.helper.showErrorNotification({message:app.vtranslate('LBL_SMS_MAX_CHARACTERS_ALLOWED')});
				return false;
			}
			var submitButton = form.find(':submit');
			submitButton.attr('disabled', 'disabled');
			thisInstance.SendSmsSave(form);
			e.preventDefault();
		});
	},
	/*
	 * Function to Save and sending the Sms and hide the modal window of send sms
	 */
	SendSmsSave: function(form) {
		app.helper.showProgress();
		var formData = form.serializeFormData();
		app.request.post({data: formData}).then(
				function(err, data) {
					app.helper.hideProgress();
					app.helper.hideModal();
					if(err){
						app.helper.showErrorNotification({message:app.vtranslate('JS_PHONEFORMAT_ERROR')});
						return;
					}
					var statusDetails = data.statusdetails;
					var status = statusDetails.status;
					if(status == 'Failed') {
						var errorMsg = statusDetails.statusmessage+'<br>'+app.vtranslate('JS_PHONEFORMAT_ERROR');
						app.helper.showErrorNotification({'title' : status, 'message' : errorMsg});
					} else {
						var msg = statusDetails.statusmessage;
						app.helper.showSuccessNotification({'title' : status, 'message' : msg});
					}
				}
		);
	},
	/**
	 * To load Related List Contents
	 * @returns {undefined}
	 */
	registerEventForRelatedTabClick : function(){
		let self = this,
			detailViewContainer = this.getDetailViewContainer();

		jQuery('.related-tabs', detailViewContainer).on('click', 'li.tab-item a', function(e, urlAttributes) {
			e.preventDefault();
		});

		jQuery('.related-tabs', detailViewContainer).on('click', 'li.more-tab', function(e,urlAttributes){
			if(0 !== jQuery('.moreTabElement').length){
				jQuery('.moreTabElement').remove();
			}

			let moreTabElement = jQuery(e.currentTarget).clone();
			moreTabElement.find('.content').text('');
			moreTabElement.addClass('moreTabElement');
			moreTabElement.addClass('active');
			let moreElementTitle = moreTabElement.find('a').attr('displaylabel')
			moreTabElement.attr('title',moreElementTitle);
			moreTabElement.find('.tab-icon').removeClass('text-truncate');
			jQuery('.related-tab-more-element').before(moreTabElement);
			self.loadSelectedTabContents(moreTabElement, urlAttributes);
			self.registerQtipevent(moreTabElement);
		});

		jQuery('.related-tabs', detailViewContainer).on('click', 'li.tab-item', function(e,urlAttributes){
			let tabElement = jQuery(e.currentTarget);
			self.loadSelectedTabContents(tabElement, urlAttributes);
		});
	},

	loadSelectedTabContents: function(tabElement, urlAttributes){
			let self = this,
				url = tabElement.data('url');

			self.loadContents(url,urlAttributes).then(function(data){
				self.deSelectAllrelatedTabs();
				self.markRelatedTabAsSelected(tabElement);
				let container = jQuery('.relatedContainer');
				app.event.trigger("post.relatedListLoad.click",container.find(".searchRow"));
				// Added this to register pagination events in related list
				let relatedModuleInstance = self.getRelatedController();
				//Summary tab is clicked
				if(tabElement.data('linkKey') == self.detailViewSummaryTabLabel) {
					self.registerSummaryViewContainerEvents(self.getDetailViewContainer());
					self.registerEventForPicklistDependencySetup(self.getForm());
				}

				//Detail tab is clicked
				if(tabElement.data('linkKey') == self.detailViewDetailTabLabel) {
					self.registerEventForPicklistDependencySetup(self.getForm());
				}

				relatedModuleInstance.initializePaginationEvents();
				//prevent detail view ajax form submissions
				jQuery('form#detailView').on('submit', function(e) {
					e.preventDefault();
				});
			});
	},
	isRollUpComments: function () {
		return 1 === parseInt(this.getRollUpComments().attr('rollup-status'))
	},
	getRollUpComments: function () {
		return jQuery('#rollupcomments');
	},
	registerRollupCommentsSwitchEvent: function () {
		let self = this,
			commentsRelatedContainer = jQuery('.commentsRelatedContainer');

		if (self.getRollUpComments().length > 0 && commentsRelatedContainer.length) {
			app.helper.hideProgress();

			commentsRelatedContainer.off('change').on('change', '#rollupcomments', function (e) {
				app.helper.showProgress();
				self.toggleRollupComments(e);
			});

			if (self.isRollUpComments()) {
				self.setRollUpComments(true);
			} else {
				self.setRollUpComments(false);
			}
		}
	},

	/**
	 * To handle related record delete confirmation message
	 */
	getDeleteMessageKey : function() {
		return 'LBL_DELETE_CONFIRMATION';
	},

	/**
	 * Funtion to register Related List Events
	 * @returns {undefined}
	 */
	registerEventsForRelatedList : function(){
		var self = this;
		var detailContentsHolder = this.getContentHolder();
		this.registerRelatedRecordEdit();

		this.registerEventForRelatedTabClick();
		this.registerRelatedListSearch();
		this.registerRelatedListSort();
		this.registerRemoveRelatedListSort();
		this.registerEventForEmailsRelatedRecord();
		this.registerRelatedListPageNavigationEvents();
		this.registerEventForAddingRelatedRecord();
		this.registerEventForSelectingRelatedRecord();
		self.registerScrollForRollupEvents();

		app.event.on("post.relatedListLoad.click",function(event, container){
			vtUtils.applyFieldElementsView(container);
			vtUtils.enableTooltips();
			Vtiger_Index_Js.getInstance().registerMultiUpload();
			//For Rollup Comments
			self.registerRollupCommentsSwitchEvent();
			self.registerScrollForRollupEvents();
			//END
		});

		var vtigerInstance = Vtiger_Index_Js.getInstance();
		vtigerInstance.registerMultiUpload();

		detailContentsHolder.on('click', 'a.relationDelete', function(e){
			e.stopImmediatePropagation();
			var element = jQuery(e.currentTarget);
			var key = self.getDeleteMessageKey();
			var message = app.vtranslate(key);
			var relatedModuleName = self.getRelatedModuleName();
			var row = element.closest('tr');
			var relatedRecordid = row.data('id');
			var relatedController = self.getRelatedController();
			if(relatedController){
				app.helper.showConfirmationBox({'message' : message}).then(
					function(e) {
						if(relatedModuleName == 'ITS4YouEmails') {
							var parentId = row.find('.parentId').data('parent-id');
							if(typeof parentId != 'undefined')
							relatedController.parentId = parentId;
						}
						relatedController.deleteRelation([relatedRecordid]).then(function(response){
							relatedController.loadRelatedList().then(function() {
								relatedController.triggerRelationAdditionalActions();
							});
						});
					},
					function(error, err){
					}
				);
			}
		});
	},

	registerEventForEmailsRelatedRecord : function(){
		var detailContentsHolder = this.getContentHolder();
		var parentId = this.getRecordId();

		var params = {};
		params['module'] = "ITS4YouEmails";
		params['view'] = "ComposeEmail";
		params['parentId'] = parentId;
		params['relatedLoad'] = true;

		detailContentsHolder.on('click','[name="emailsRelatedRecord"], [name="emailsDetailView"]',function(e){
			e.stopPropagation();
			var element = jQuery(e.currentTarget);
			var recordId = element.data('id');
			if(element.data('emailflag') == 'SAVED') {
				var mode = 'emailEdit';
			} else {
				mode = 'emailPreview';
				params['parentModule'] = app.getModuleName();
			}
			params['mode'] = mode;
			params['record'] = recordId;
			app.helper.showProgress();
			app.request.post({data:params}).then(function(err,data){
				app.helper.hideProgress();
				if(err === null){
					var dataObj = jQuery(data);
					var descriptionContent = dataObj.find('#iframeDescription').val();
					app.helper.showModal(data,{cb:function(){
						if(mode === 'emailEdit'){
							var editInstance = new Emails_MassEdit_Js();
							editInstance.registerEvents();
						}else {
							app.event.trigger('post.EmailPreview.load',null);
						}
						jQuery('#emailPreviewIframe').contents().find('html').html(descriptionContent);
						jQuery("#emailPreviewIframe").height(jQuery('.email-body-preview').height());
						jQuery('#emailPreviewIframe').contents().find('html').find('a').on('click', function(e) {
							e.preventDefault();
							var url = jQuery(e.currentTarget).attr('href');
							window.open(url, '_blank');
						});
						//jQuery("#emailPreviewIframe").height(jQuery('#emailPreviewIframe').contents().find('html').height());
					}});
				}
			});
		})

		detailContentsHolder.on('click','[name="emailsEditView"]',function(e){
			e.stopPropagation();
			var module = "ITS4YouEmails";
			app.helper.checkServerConfig(module).then(function(data){
				if(data == true){
					var element = jQuery(e.currentTarget);
					var closestROw = element.closest('tr');
					var recordId = closestROw.data('id');
					var parentRecord = new Array();
					parentRecord.push(parentId);

					params['mode'] = "emailEdit";
					params['record'] = recordId;
					params['selected_ids'] = parentRecord;
					app.helper.showProgress();
					app.request.post({'data':params}).then(function(err,data){
						app.helper.hideProgress();
						if(err === null){
							app.helper.showModal(data);
							var editInstance = new Emails_MassEdit_Js();
							editInstance.registerEvents();
						}
					});
				} else {
					app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
				}
			})
		})
	},

	/**
	* To Delete Record from detail View
	* @param {type} deleteRecordActionUrl
	* @returns {undefined}
	*/
	remove : function(deleteRecordActionUrl){
		var message = app.vtranslate('LBL_DELETE_CONFIRMATION');
		app.helper.showConfirmationBox({'message' : message}).then(function(data) {
				var params = app.convertUrlToDataParams(deleteRecordActionUrl+"&ajaxDelete=true");
				app.request.post({data:params}).then(
				function(err,data){
					if(err === null){
						if(typeof data !== 'object'){
							var appName = app.getAppName();
							window.location.href = data+'&app='+appName;
						}else {
							app.helper.showAlertBox({'message' : data.prototype.message});
						}
					} else {
						app.helper.showAlertBox({'message' : err});
					}
				});
			}
		);
	},

	/**
	 * Function to register the related list search event
	 */
	registerRelatedListSearch : function() {
		var thisInstance = this;
		var detailViewContainer = thisInstance.getDetailViewContainer();
		detailViewContainer.on('click','[data-trigger="relatedListSearch"]',function(e){
			var params = {'page' : '1'};
			thisInstance.getRelatedController().loadRelatedList(params);
		});
		detailViewContainer.on('keypress','input.listSearchContributor',function(e){
			if(e.keyCode == 13){
				var element = jQuery(e.currentTarget);
				var parentElement = element.closest('tr');
				var searchTriggerElement = parentElement.find('[data-trigger="relatedListSearch"]');
				searchTriggerElement.trigger('click');
			}
		});
	},

	/**
	 * Function to register the related list sort event
	 */
	registerRelatedListSort : function() {
		var thisInstance = this;
		var detailViewContainer = thisInstance.getDetailViewContainer();
		detailViewContainer.on('click','.listViewContentHeaderValues,.relatedListHeaderValues',function(e){
			var fieldName = jQuery(e.currentTarget).attr('data-fieldname');
			var sortOrderVal = jQuery(e.currentTarget).attr('data-nextsortorderval');
			if(sortOrderVal === 'ASC'){
				jQuery('i',e.currentTarget).addClass('fa-sort-asc');
			}else{
				jQuery('i',e.currentTarget).addClass('fa-sort-desc');
			}
			jQuery('#sortOrder').val(sortOrderVal);
			jQuery('#orderBy').val(fieldName);
			var params = [];
			thisInstance.getRelatedController().loadRelatedList(params);
		});
	},

	/**
	 * Function to register remove related list sorting
	 */
	registerRemoveRelatedListSort : function() {
		var thisInstance = this;
		var detailViewContainer = thisInstance.getDetailViewContainer();
		detailViewContainer.on('click','.removeSorting',function(e){
			jQuery('#sortOrder').val(null);
			jQuery('#orderBy').val(null);
			var params = [];
			thisInstance.getRelatedController().loadRelatedList(params);
		});
	},

	/**
	 * Function to register Related List View Pagination
	 * @returns {undefined}
	 */
	registerRelatedListPageNavigationEvents : function(){
		var thisInstance = this;
		var detailViewContainer = thisInstance.getDetailViewContainer();
		var urlParams = {};
		detailViewContainer.on('click','#listViewNextPageButton',function(e){
			var pageLimit = jQuery('#pageLimit').val();
			var noOfEntries = jQuery('#noOfEntries').val();
			var nextPageExist = jQuery('#nextPageExist').val();

			if(noOfEntries == pageLimit && nextPageExist){
				var pageNumber = jQuery('#pageNumber').val();
				var nextPageNumber = parseInt(parseFloat(pageNumber)) + 1;
				jQuery('#pageNumber').val(nextPageNumber);
				var params = [];
				thisInstance.loadRelatedListRecords(params);
			}
		});

		detailViewContainer.on('click','#listViewPreviousPageButton',function(e){
			var pageNumber = jQuery('#pageNumber').val();
			if(pageNumber > 1){
				var previousPageNumber = parseInt(parseFloat(pageNumber)) - 1;
				jQuery('#pageNumber').val(previousPageNumber);
				var params = [];
				thisInstance.loadRelatedListRecords(params);
			}
		});
	},

	/**
	 * Function to register event for adding related record for module
	 */
	registerEventForAddingRelatedRecord : function(){
		var thisInstance = this;
		var detailViewContainer = thisInstance.getDetailViewContainer();
		detailViewContainer.on('click','[name="addButton"]',function(e){
			var element = jQuery(e.currentTarget);
			var relatedModuleName = element.attr('module');
			var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ relatedModuleName +'"]');
			if(quickCreateNode.length <= 0) {
				window.location.href = element.data('url');
				return;
			}

			var relatedController = thisInstance.getRelatedController(relatedModuleName);
			if(relatedController){
				relatedController.addRelatedRecord(element);
			}
		})
	},

	/**
	 * Function to register event for selecting related record for module
	 */
	registerEventForSelectingRelatedRecord : function() {
		var thisInstance = this;
		var detailViewContainer = thisInstance.getDetailViewContainer();
		detailViewContainer.on('click', 'button.selectRelation', function(e){
			var relatedController = thisInstance.getRelatedController();
			if(relatedController){
				relatedController.showSelectRelationPopup();
			}
		});
	},

	getRelatedModuleName : function() {
		return jQuery('.relatedModuleName').val();
	},

	getRelatedController : function(relatedModuleName) {
		var thisInstance = this;
		var recordId = thisInstance.getRecordId();
		var moduleName = app.getModuleName();
		var selectedTabElement = thisInstance.getSelectedTab();

		if (typeof relatedModuleName == 'undefined') {
			var relatedModuleName = thisInstance.getRelatedModuleName();
		}
		var relatedListClass = 'Vtiger_RelatedList_Js';
		if(typeof window[relatedListClass] != 'undefined'){
			return Vtiger_RelatedList_Js.getInstance(recordId, moduleName, selectedTabElement, relatedModuleName);
		}
		return null;
	},

	getSelectedTab : function() {
		var tabContainer = this.getTabContainer();
		return tabContainer.find('li.active');
	},

	/**
	 * To Register Ajax Edit Event
	 * @returns {undefined}
	 */
	registerAjaxEditEvent : function(){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click','.detailview-table .fieldValue .editAction', function(e) {
			var editedLength = jQuery('table.detailview-table .fieldValue .ajaxEdited').length;
			if(editedLength === 0) { 
				var selection = window.getSelection().toString(); 
				if(selection.length == 0) {
					var currentTdElement = jQuery(e.currentTarget).closest('.fieldValue');
					thisInstance.ajaxEditHandling(currentTdElement);
				}
			}
		});
	},

	/**
	 * To Save Ajax Edited field
	 * @param {type} fieldDetailList
	 * @returns {unresolved}
	 */
	saveFieldValues: function (fieldDetailList) {
		let aDeferred = jQuery.Deferred(),
			data = {
				record: this.getRecordId(),
				module: this.getModuleName(),
				action: 'SaveAjax',
			};

		if (typeof fieldDetailList != 'undefined') {
			data = $.extend(data, fieldDetailList);
		}

		app.request.post({data: data}).then(
			function (err, reponseData) {
				if (err === null) {
					app.helper.showSuccessNotification({"message": app.vtranslate('JS_RECORD_UPDATED')});
				}
				aDeferred.resolve(err, reponseData);
			}
		);

		return aDeferred.promise();
	},
	registerSaveOnEnterEvent: function (editElement) {
		editElement.find('.inputElement:not(textarea)').on('keyup', function (e) {
			let textArea = editElement.find('textarea'),
				ignoreList = ['reference', 'picklist', 'multipicklist', 'owner'],
				fieldType = jQuery(e.target).closest('.ajaxEdited').find('.fieldBasicData').data('type');

			if (ignoreList.indexOf(fieldType) !== -1) return;
			if (!textArea.length) {
				(e.keyCode || e.which) === 13 && editElement.find('.inlineAjaxSave').trigger('click');
			}
		});
	},
	registerCancelOnEscEvent: function (editElement) {
		$(document).on('keyup', function (e) {
			if (27 === e.which) {
				editElement.find('.inlineAjaxCancel').trigger('click');
			}
		});
	},

	/**
	 * Handling Ajax Edit 
	 * @param {type} currentTdElement
	 * @returns {undefined}
	 */
	ajaxEditHandling: function (currentTdElement) {
		let thisInstance = this,
			detailViewValue = jQuery('.value', currentTdElement),
			editElement = jQuery('.edit', currentTdElement),
			fieldBasicData = jQuery('.fieldBasicData', editElement),
			fieldName = fieldBasicData.data('name'),
			fieldType = fieldBasicData.data('type'),
			value = fieldBasicData.data('displayvalue'),
			rawValue = fieldBasicData.data('value'),
			self = this,
			fieldElement = jQuery('[name="' + fieldName + '"]', editElement);

		// If Reference field has value, then we are disabling the field by default
		if (fieldElement.attr('disabled') == 'disabled' && fieldType != 'reference') {
			return;
		}

		if (editElement.length <= 0) {
			return;
		}

		if (editElement.is(':visible')) {
			return;
		}

		if (fieldType === 'multipicklist') {
			let multiPicklistFieldName = fieldName.split('[]');
			fieldName = multiPicklistFieldName[0];
		}

		let customHandlingFields = ['owner', 'ownergroup', 'picklist', 'multipicklist', 'reference', 'currencyList', 'text', 'documentsFolder', 'country'];

		if (jQuery.inArray(fieldType, customHandlingFields) !== -1) {
			value = rawValue;
		}

		let numberHandlingFields = ['currency', 'percentage', 'integer'];

		if (jQuery.inArray(fieldType, numberHandlingFields) !== -1) {
			value = parseFloat(rawValue).toFixed(app.getNumberOfDecimals());

			if ('integer' === fieldType) {
				value = parseInt(value);
			}
		}

		if (jQuery('.editElement', editElement).length === 0) {
			let fieldInfo;

			if (true === self.getQuickPreviewDetailMode()) {
				fieldInfo = quick_preview_uimeta.field.get(fieldName);
			} else if (true === self.getOverlayDetailMode()) {
				fieldInfo = related_uimeta.field.get(fieldName);
			} else {
				fieldInfo = uimeta.field.get(fieldName);
			}

			if ('boolean' === fieldType) {
				if (0 == rawValue) {
					fieldInfo['value'] = "No";
				} else {
					fieldInfo['value'] = "Yes";
				}
			} else {
				fieldInfo['value'] = value;
			}

			if('currency' === fieldType) {
				fieldInfo['currency_symbol'] = detailViewValue.find('[data-currency-symbol]').attr('data-currency-symbol');
			}

			let fieldObject = Vtiger_Field_Js.getInstance(fieldInfo),
				fieldModel = fieldObject.getUiTypeModel(),
				ele = jQuery('<div class="editElement d-flex align-items-start w-100"></div>'),
				actionButtons = '<span class="pointerCursorOnHover btn btn-success input-group-addon input-group-addon-save inlineAjaxSave ms-2"><i class="fa fa-check"></i></span>';

			actionButtons += '<span class="pointerCursorOnHover btn btn-danger input-group-addon input-group-addon-cancel inlineAjaxCancel ms-2"><i class="fa-solid fa-xmark"></i></span>';
			// we should have atleast one submit button for the form to submit which is required for validation
			ele.append(fieldModel.getUi()).append(actionButtons);
			ele.find('.inputElement');
			editElement.append(ele);
		}

		// for reference fields, actual value will be ID but we need to show related name of that ID
		if (fieldType === 'reference') {
			if (value !== 0) {
				jQuery('input[name="' + fieldName + '"]', editElement).prop('value', jQuery.trim(detailViewValue.text()));
				let referenceElement = jQuery('input[name="' + fieldName + '"]', editElement);

				if (referenceElement.val()) {
					referenceElement.attr('disabled', 'disabled');
					editElement.find('.clearReferenceSelection').removeClass('hide')
				}
			}
		}

		detailViewValue.css('display', 'none');
		editElement.removeClass('hide').show().children().filter('input[type!="hidden"]input[type!="image"],select').filter(':first').focus();
		vtUtils.applyFieldElementsView(currentTdElement);
		let contentHolder = this.getDetailViewContainer(),
			vtigerInstance = Vtiger_Index_Js.getInstance();

		vtigerInstance.registerAutoCompleteFields(contentHolder);
		vtigerInstance.referenceModulePopupRegisterEvent(contentHolder);
		editElement.addClass('ajaxEdited');
		thisInstance.registerSaveOnEnterEvent(editElement);
		thisInstance.registerCancelOnEscEvent(editElement);
		jQuery('.editAction').addClass('hide');

		if (fieldType == 'picklist' || fieldType == 'ownergroup' || fieldType == 'owner') {
			var sourcePicklistFieldName = thisInstance.getDependentSourcePicklistName(fieldName);
			if (sourcePicklistFieldName) {
				thisInstance.handlePickListDependencyMap(sourcePicklistFieldName);
			}
		}
	},

	getDependentSourcePicklistName : function(fieldName) {
		var container = this.getForm();
		var picklistDependcyElemnt = jQuery('[name="picklistDependency"]',container);
		if(picklistDependcyElemnt.length <= 0) {
			return '';
		}

		var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());
		var sourcePicklists = Object.keys(picklistDependencyMapping);
		if(sourcePicklists.length <= 0){
			return '';
		}
		var sourcePicklistFieldName = '';
		jQuery.each(picklistDependencyMapping, function(sourcePicklistName, configuredDependencyObject) {
			var picklistmap = configuredDependencyObject["__DEFAULT__"];
			jQuery.each(picklistmap,function(targetPickListName,targetPickListValues){
				if(targetPickListName == fieldName){
					sourcePicklistFieldName = sourcePicklistName;
				}
			});
		});

		return sourcePicklistFieldName;
	},

	getInlineWrapper: function (element) {
		let wrapperElement = element.closest('td');

		if (!wrapperElement.length) {
			wrapperElement = element.closest('.td');
		}

		if (!wrapperElement.length) {
			wrapperElement = element.closest('.fieldValue');
		}

		return wrapperElement;
	},

	/**
	 * Ajax Edit Save Event
	 * @param {type} currentTdElement
	 * @returns {undefined}
	 */
	registerAjaxEditSaveEvent : function(contentHolder){
		var thisInstance = this;
		if(typeof contentHolder === 'undefined') {
			contentHolder = this.getContentHolder();
		}

		contentHolder.on('click','.inlineAjaxSave',function(e){
			e.preventDefault();
			e.stopPropagation();
			var currentTarget = jQuery(e.currentTarget);
			var currentTdElement = thisInstance.getInlineWrapper(currentTarget); 
			var detailViewValue = jQuery('.value',currentTdElement);
			var editElement = jQuery('.edit',currentTdElement);
			var actionElement = jQuery('.editAction', currentTdElement);
			var fieldBasicData = jQuery('.fieldBasicData', editElement);
			var fieldName = fieldBasicData.data('name');
			var fieldType = fieldBasicData.data("type");
			var previousValue = jQuery.trim(fieldBasicData.data('displayvalue'));

			var fieldElement = jQuery('[name="'+ fieldName +'"]', editElement);
			var ajaxEditNewValue = fieldElement.val();

			 // ajaxEditNewValue should be taken based on field Type
			if(fieldElement.is('input:checkbox')) {
				if(fieldElement.is(':checked')) {
					ajaxEditNewValue = '1';
				} else {
					ajaxEditNewValue = '0';
				}
				fieldElement = fieldElement.filter('[type="checkbox"]');
			} else if(fieldType == 'reference'){
				ajaxEditNewValue = fieldElement.data('value');
			}

			// prev Value should be taken based on field Type
			var customHandlingFields = ['owner','ownergroup','picklist','multipicklist','reference','boolean']; 
			if(jQuery.inArray(fieldType, customHandlingFields) !== -1){
				previousValue = fieldBasicData.data('value');
			}

			// Field Specific custom Handling
			if(fieldType === 'multipicklist'){
				var multiPicklistFieldName = fieldName.split('[]');
				fieldName = multiPicklistFieldName[0];
			} 

			var fieldValue = ajaxEditNewValue;

			//Before saving ajax edit values we need to check if the value is changed then only we have to save
			if(previousValue == ajaxEditNewValue) {
				detailViewValue.css('display', 'inline-block');
				editElement.addClass('hide');
				editElement.removeClass('ajaxEdited');
				jQuery('.editAction').removeClass('hide');
				actionElement.show();
			}else{
				let fieldNameValueMap = {},
					form = currentTarget.closest('form'),
					recordElement = form.find('[name="record"]'),
					moduleElement = form.find('[name="module"]');

				fieldNameValueMap['value'] = fieldValue;
				fieldNameValueMap['field'] = fieldName;

				if (recordElement.length) {
					fieldNameValueMap['record'] = recordElement.val();
				}

				if (moduleElement.length) {
					fieldNameValueMap['module'] = moduleElement.val();
				}

				var params = {
					'ignore' : 'span.hide .inputElement,input[type="hidden"]',
					submitHandler : function(form){
						var preAjaxSaveEvent = jQuery.Event(Vtiger_Detail_Js.PreAjaxSaveEvent);
						app.event.trigger(preAjaxSaveEvent,{form:jQuery(form),triggeredFieldInfo:fieldNameValueMap});
						if(preAjaxSaveEvent.isDefaultPrevented()) {
							return false;
						}

						jQuery(currentTdElement).find('.input-group-addon').addClass('disabled');
						app.helper.showProgress();
						thisInstance.saveFieldValues(fieldNameValueMap).then(function(err, response) {
							app.helper.hideProgress();
							if (err !== null) {
								app.event.trigger('post.save.failed', err);
								jQuery(currentTdElement).find('.input-group-addon').removeClass('disabled');
								return true;
							}
							jQuery('.vt-notification').remove();
							let postSaveRecordDetails = response;

							if ('picklist' === fieldBasicData.data('type') && 'Users' !== app.getModuleName()) {
								let style = '',
									color = 'undefined' !== typeof postSaveRecordDetails[fieldName].colormap ? postSaveRecordDetails[fieldName].colormap[postSaveRecordDetails[fieldName].value] : false,
									picklistHtml;

								if (color) {
									let contrast = app.helper.getColorContrast(color),
										textColor = (contrast === 'dark') ? 'white' : 'black';

									style = 'style="background-color: ' + color + '; color: ' + textColor + ';"';
								}

								picklistHtml = '<span class="py-1 px-2 rounded picklist-color" ' + style + '>' + postSaveRecordDetails[fieldName].display_value + '</span>';
								detailViewValue.html(picklistHtml);
							} else if ('multipicklist' === fieldBasicData.data('type') && 'Users' !== app.getModuleName()) {
								let picklistHtml = '',
									rawPicklistValues = postSaveRecordDetails[fieldName].value;

								rawPicklistValues = rawPicklistValues.split('|##|');

								let picklistValues = postSaveRecordDetails[fieldName].display_value;
								picklistValues = picklistValues.split(',');

								for (let i = 0; i < rawPicklistValues.length; i++) {
									let style = '',
										color = 'undefined' !== typeof postSaveRecordDetails[fieldName].colormap ? postSaveRecordDetails[fieldName].colormap[rawPicklistValues[i].trim()] : false;

									if (color) {
										let contrast = app.helper.getColorContrast(color),
											textColor = (contrast === 'dark') ? 'white' : 'black';

										style = 'style="background-color: ' + color + '; color: ' + textColor + ';"';
									}

									picklistHtml += '<span class="me-1 py-1 px-2 rounded picklist-color" ' + style + '>' + picklistValues[i] + '</span>';
								}

								detailViewValue.html(picklistHtml);
							} else if(fieldBasicData.data('type') == 'currency' && app.getModuleName() != 'Users') {
								detailViewValue.find('.currencyValue').html(postSaveRecordDetails[fieldName].display_value);
								contentHolder.closest('.detailViewContainer').find('.detailview-header-block').find('.'+fieldName).html(postSaveRecordDetails[fieldName].display_value);
							}else {
								detailViewValue.html(postSaveRecordDetails[fieldName].display_value);
								//update namefields displayvalue in header
								if(contentHolder.hasClass('overlayDetail')) {
									contentHolder.find('.overlayDetailHeader').find('.'+fieldName)
									.html(postSaveRecordDetails[fieldName].display_value);
								} else {
									contentHolder.closest('.detailViewContainer').find('.detailview-header-block')
									.find('.'+fieldName).html(postSaveRecordDetails[fieldName].display_value);
							}
							}
							fieldBasicData.data('displayvalue',postSaveRecordDetails[fieldName].display_value);
							fieldBasicData.data('value',postSaveRecordDetails[fieldName].value);
							jQuery(currentTdElement).find('.input-group-addon').removeClass("disabled");

							detailViewValue.css('display', 'inline-block');
							editElement.addClass('hide');
							editElement.removeClass('ajaxEdited');
							jQuery('.editAction').removeClass('hide');
							actionElement.show();
							var postAjaxSaveEvent = jQuery.Event(Vtiger_Detail_Js.PostAjaxSaveEvent);
							app.event.trigger(postAjaxSaveEvent, fieldBasicData, postSaveRecordDetails, contentHolder);
							//After saving source field value, If Target field value need to change by user, show the edit view of target field.
							if(thisInstance.targetPicklistChange) {
								var sourcePicklistname = thisInstance.sourcePicklistname;
								thisInstance.targetPicklist.find('.editAction').trigger('click');
								thisInstance.targetPicklistChange = false;
								thisInstance.targetPicklist = false;
								thisInstance.handlePickListDependencyMap(sourcePicklistname);
								thisInstance.sourcePicklistname = false;
							}
						});
					}
				};
				validateAndSubmitForm(form,params);
			}
		});
	},

	handlePickListDependencyMap : function(sourcePicklistName) {
		var container = this.getForm();
		var picklistDependcyElemnt = jQuery('[name="picklistDependency"]',container);
		if(picklistDependcyElemnt.length <= 0) {
			return;
		}
		var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());
		var sourcePicklists = Object.keys(picklistDependencyMapping);
		if(sourcePicklists.length <= 0){
			return;
		}

		var configuredDependencyObject = picklistDependencyMapping[sourcePicklistName];
		var selectedValue = container.find('[data-name='+sourcePicklistName+']').data('value');
		var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
		var picklistmap = configuredDependencyObject["__DEFAULT__"];
		if(typeof targetObjectForSelectedSourceValue == 'undefined'){
			targetObjectForSelectedSourceValue = picklistmap;
		}
		jQuery.each(picklistmap,function(targetPickListName,targetPickListValues){
			var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
			if(typeof targetPickListMap == "undefined"){
				targetPickListMap = targetPickListValues;
			}
			var targetPickList = jQuery('[name="'+targetPickListName+'"]',container);
			if(targetPickList.length <= 0){
				return;
			}

			var listOfAvailableOptions = targetPickList.data('available-options');
			if(typeof listOfAvailableOptions == "undefined"){
				listOfAvailableOptions = jQuery('option',targetPickList);
				targetPickList.data('available-options', listOfAvailableOptions);
			}

			var targetOptions = new jQuery();
			var optionSelector = [];
			optionSelector.push('');
			for(var i=0; i<targetPickListMap.length; i++){
				optionSelector.push(targetPickListMap[i]);
			}

			jQuery.each(listOfAvailableOptions, function(i,e) {
				var picklistValue = jQuery(e).val();
				if(jQuery.inArray(picklistValue, optionSelector) != -1) {
					targetOptions = targetOptions.add(jQuery(e));
				}
			})
			var targetPickListSelectedValue = '';
			targetPickListSelectedValue = targetOptions.filter('[selected]').val();
			if(targetPickListMap.length == 1) { 
				targetPickListSelectedValue = targetPickListMap[0]; // to automatically select picklist if only one picklistmap is present.
			}
			if((targetPickListName == 'group_id' || targetPickListName == 'assigned_user_id') && jQuery("[data-name="+ sourcePicklistName +"]").data('value') == ''){
				return false;
			}
			targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("change");
		})

	},

	/**
	 * Ajax Edit Calcel Event
	 * @param {type} currentTdElement
	 * @returns {undefined}
	 */
	registerAjaxEditCancelEvent : function(contentHolder){
		var thisInstance = this;
		if(typeof contentHolder === 'undefined') {
			contentHolder = this.getContentHolder();
		}
		contentHolder.on('click','.inlineAjaxCancel',function(e){
			e.preventDefault();
			e.stopPropagation();
			var currentTarget = jQuery(e.currentTarget);
			var currentTdElement = thisInstance.getInlineWrapper(currentTarget);
			var detailViewValue = jQuery('.value',currentTdElement);
			var editElement = jQuery('.edit',currentTdElement);
			var actionElement = jQuery('.editAction', currentTdElement);
			detailViewValue.css('display', 'inline-block');
			editElement.addClass('hide');
			editElement.find('.inputElement').trigger('Vtiger.Validation.Hide.Messsage')
			editElement.removeClass('ajaxEdited');
			jQuery('.editAction').removeClass('hide');
			actionElement.show();
		});
	},

	registerClearReferenceSelectionEvent : function(contentHolder) {
		var thisInstance = this;
		if(typeof contentHolder === 'undefined') {
			contentHolder = this.getContentHolder();
		}
		contentHolder.off('click', '.clearReferenceSelection');
		contentHolder.on('click','.clearReferenceSelection',function(e){
			e.preventDefault();
			var element = jQuery(e.currentTarget);
			var parentTdElement = thisInstance.getInlineWrapper(element);
			var inputElement = parentTdElement.find('.inputElement');
			parentTdElement.find('.referencefield-wrapper').removeClass('selected');
			inputElement.removeAttr("disabled");
			inputElement.attr("value","");
			inputElement.data('value','');
			inputElement.val("");
			element.addClass('hide');
		});
	},

	searchModuleNames : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}

		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}

		if(typeof params.base_record == 'undefined') {
			var record = jQuery('[name="record"]');
			var recordId = app.getRecordId();
			if(record.length) {
				params.base_record = record.val();
			} else if(recordId) {
				params.base_record = recordId;
			} else if(app.view() == 'List') {
				var editRecordId = jQuery('#listview-table').find('tr.listViewEntries.edited').data('id');
				if(editRecordId) {
					params.base_record = editRecordId;
				}
			}
		}

		app.request.get({data:params}).then(
			function(err, res){
				aDeferred.resolve(res);
			},
			function(error){
				//TODO : Handle error
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},

	/**
	 * Function to get reference search params
	 */
	getReferenceSearchParams : function(element){
		var tdElement = jQuery(element).closest('td');
		var params = {};
		var referenceModuleElement = jQuery('input[name="referenceModule"]',tdElement).length ? 
			jQuery('input[name="referenceModule"]',tdElement) : jQuery('input.referenceModule',tdElement);
		var searchModule =  referenceModuleElement.val();
		params.search_module = searchModule;
		return params;
	},

	/**
	 * Load Detail View Contents
	 * @param {type} url
	 * @returns {unresolved}
	 */
	loadContents : function(url,data){
		var thisInstance = this;
		var detailContentsHolder = this.getContentHolder();
		var aDeferred = jQuery.Deferred();
		if(url.indexOf('index.php') < 0) {
			url = 'index.php?' + url;
		}
		var params = [];
		params.url = url;
		if(typeof data != 'undefined'){
			params.data = data;
		}
		app.helper.showProgress();
		app.request.pjax(params).then(function(error,response){
			detailContentsHolder.html(response);
			thisInstance.detailViewForm = jQuery('#detailView');
			thisInstance.registerBlockStatusCheckOnLoad();
			aDeferred.resolve(response);
			app.helper.hideProgress();
		});
		return aDeferred.promise();
	},

	registerBlockAnimationEvent: function () {
		let detailContentsHolder = this.getContentHolder();

		detailContentsHolder.on('click', '.blockToggle', function (e) {
			let currentTarget = jQuery(e.currentTarget),
				blockId = currentTarget.data('id'),
				closestBlock = currentTarget.parents('.block'),
				bodyContents = closestBlock.find('.blockData'),
				data = currentTarget.data(),
				module = app.getModuleName(),
				hideHandler = function () {
					bodyContents.hide();
					app.storage.set(module + '.' + blockId, 0);
				},
				showHandler = function () {
					bodyContents.removeClass('hide').show();
					app.storage.set(module + '.' + blockId, 1);
				};

			if (data.mode == 'show') {
				hideHandler();
				currentTarget.hide();
				closestBlock.find("[data-mode='hide']").removeClass('hide').show();
			} else {
				showHandler();
				currentTarget.hide();
				closestBlock.find("[data-mode='show']").removeClass('hide').show();
			}
		});
	},

	registerBlockStatusCheckOnLoad: function () {
		let blocks = this.getContentHolder().find('.block'),
			module = app.getModuleName();

		blocks.each(function (index, block) {
			let currentBlock = jQuery(block),
				headerAnimationElement = currentBlock.find('.blockToggle').not('.hide'),
				bodyContents = currentBlock.find('.blockData'),
				blockId = headerAnimationElement.data('id'),
				cacheKey = module + '.' + blockId,
				value = app.storage.get(cacheKey);

			if (value != null) {
				if (value === 1) {
					headerAnimationElement.hide();
					currentBlock.find("[data-mode='show']").removeClass('hide').show();
					bodyContents.removeClass('hide').show();
				} else {
					headerAnimationElement.hide();
					currentBlock.find("[data-mode='hide']").removeClass('hide').show();
					bodyContents.hide();
				}
			} else {
				if (bodyContents.hasClass("hide")) {
					headerAnimationElement.hide();
					currentBlock.find("[data-mode='hide']").show();
					bodyContents.hide();
				}
			}
		});
	},
	registerPostLoadWidget(container) {
		const self = this;

		jQuery('.widget_contents', container).on(self.widgetPostLoad, function() {
			let selects = $(this).find('.select2');

			if(selects.length) {
				vtUtils.showSelect2ElementView($(this).find('.select2'))
			}
		})
	},
	registerSummaryViewContainerEvents: function (summaryViewContainer) {
		const self = this;

		if (!summaryViewContainer.is('.detailViewContainer')) {
			console.error('Missing or wrong summary view container');
		}

		self.loadWidgets();
		/**
		 * Function to handle the ajax edit for summary view fields
		 */

		if (!self.registerSummaryHandlers) {
			return;
		}

		self.registerSummaryHandlers = false;

		summaryViewContainer.on('click', '.summary-table .fieldValue .editAction', function (e) {
			let currentTarget = jQuery(e.currentTarget),
				currentTdElement = currentTarget.closest('.fieldValue');

			currentTarget.hide();
			self.ajaxEditHandling(currentTdElement);
		});

		summaryViewContainer.on('click', '.createRecord', function (e) {
			let currentElement = jQuery(e.currentTarget),
				form = currentElement.closest('form'),
				recordElement = form.find('[name=record]'),
				moduleElement = form.find('[name=module]'),
				summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer'),
				referenceModuleName = summaryWidgetContainer.find('[name="relatedModule"]').val(),
				referenceFieldName = summaryWidgetContainer.find('[name="relatedField"]').val(),
				recordId = recordElement.length ? recordElement.val() : self.getRecordId(),
				module = moduleElement.length ? moduleElement.val() : self.getModuleName(),
				quickCreateNode = jQuery('#quickCreateModules').find('[data-name="' + referenceModuleName + '"]'),
				fieldName = referenceFieldName ?? self.referenceFieldNames[module],
				customParams = {};

			customParams[fieldName] = recordId;

			if (quickCreateNode.length <= 0) {
				app.helper.showErrorMessage(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'));
			}

			app.event.on('post.QuickCreateForm.save', function (event, data) {
				let idList = new Array();
				idList.push(data._recordId);

				self.addRelationBetweenRecords(referenceModuleName, idList).then(function (data) {
					self.loadWidget(summaryWidgetContainer.find('[class^="widgetContainer_"]'));
				});
			});

			let QuickCreateParams = {};
			QuickCreateParams['data'] = customParams;
			QuickCreateParams['noCache'] = false;
			quickCreateNode.trigger('click', QuickCreateParams);
		});

		/*
		 * Register the event to edit the status for for related activities
		 */
		summaryViewContainer.on('change', '.activityStatus .select2', function (e) {
			let currentTarget = jQuery(e.currentTarget),
				currentDiv = currentTarget.closest('.activityStatus'),
				editElement = currentDiv.find('.edit');

			currentDiv.attr('data-calendar-status', currentTarget.val());

			let callbackFunction = function () {
				let fieldnameElement = jQuery('.fieldname', editElement),
					fieldName = fieldnameElement.val(),
					fieldElement = jQuery('[name="' + fieldName + '"]', editElement),
					previousValue = fieldnameElement.data('prevValue'),
					ajaxEditNewValue = fieldElement.find('option:selected').val(),
					translatedValue = fieldElement.find('option:selected').text(),
					select2Element = fieldElement.parent().find('.select2-container');

				if (!ajaxEditNewValue) {
					vtUtils.showValidationMessage(select2Element, app.vtranslate('JS_REQUIRED_FIELD'));
					app.helper.addClickOutSideEvent(currentDiv, callbackFunction);
					return;
				} else {
					vtUtils.hideValidationMessage(select2Element);
				}

				if (previousValue !== ajaxEditNewValue) {
					let activityDiv = currentDiv.closest('.activityEntries'),
						activityId = activityDiv.find('.activityId').val(),
						moduleName = activityDiv.find('.activityModule').val(),
						activityType = activityDiv.find('.activityType').val();

					app.helper.showProgress();

					let params = {
						action: 'SaveAjax',
						record: activityId,
						field: fieldName,
						value: ajaxEditNewValue,
						module: moduleName,
						activitytype: activityType,
						calendarModule: moduleName,
						origin: 'SummaryWidget'
					};

					app.request.post({"data": params}).then(function (error, data) {
						app.helper.hideProgress();

						if (!error) {
							jQuery('.vt-notification').remove();
							fieldnameElement.data('prevValue', ajaxEditNewValue);
						} else {
							app.event.trigger('post.save.failed', error);
							fieldElement.select2('val', previousValue);
						}
					});
				}
			}
			app.helper.addClickOutSideEvent(currentDiv, callbackFunction);
		});

		self.registerPostLoadWidget(summaryViewContainer);
	},

	addRelationBetweenRecords : function(relatedModule, relatedModuleRecordId){
		var aDeferred = jQuery.Deferred();
		var thisInstance = this;
		var relatedController = thisInstance.getRelatedController(relatedModule);
				if(thisInstance.getOverlayDetailMode() == true){
					relatedController.parentModuleName = thisInstance.getModuleName();
					relatedController.setSelectedTabElement('');
				}
		if(relatedController){
			relatedController.addRelations(relatedModuleRecordId).then(
				function(data){
					aDeferred.resolve(data);
				},

				function(textStatus, errorThrown){
					aDeferred.reject(textStatus, errorThrown);
				}
			)
		}
		return aDeferred.promise();
	},

	loadWidgets : function(){
		var self = this;
		var widgetList = jQuery('[class^="widgetContainer_"]');
		widgetList.each(function(index,widgetContainerELement){
			var widgetContainer = jQuery(widgetContainerELement);
			self.loadWidget(widgetContainer).then(function(){
				app.event.trigger('post.summarywidget.load',widgetContainer);
			});
		});
	},

	loadWidget : function(widgetContainer) {
		let aDeferred = jQuery.Deferred(),
			thisInstance = this,
			contentContainer = jQuery('.widget_contents',widgetContainer),
			urlParams = widgetContainer.data('url'),
			params = {
				'type' : 'GET',
				'dataType': 'html',
				'data' : urlParams
			};

		app.helper.showProgress();
		app.request.post(params).then(function(error,data){
			app.helper.hideProgress();
			contentContainer.html(data);
			contentContainer.trigger(thisInstance.widgetPostLoad);

			aDeferred.resolve(params);
		}, function(){
			aDeferred.reject();
		});

		return aDeferred.promise();
	},


	getTabs : function() {
		return this.getTabContainer().find('li');
	},

	/**
	 * Function to return related tab.
	 * @return : jQuery Object.
	 */
	getTabByLabel : function(tabLabel) {
		var tabs = this.getTabs();
		var targetTab = false;
		tabs.each(function(index,element){
			var tab = jQuery(element);
			var labelKey = tab.data('labelKey');
			if(labelKey == tabLabel){
				targetTab = tab;
				return false;
			}
		});
		return targetTab;
	},

	/**
	 * function to save comment
	 * return json response
	 */
	saveComment: function (e) {
		let self = this,
			aDeferred = jQuery.Deferred(),
			currentTarget = jQuery(e.currentTarget),
			form = jQuery(e.currentTarget).closest('form'),
			commentMode = currentTarget.data('mode'),
			closestCommentBlock = currentTarget.closest('.addCommentBlock'),
			commentContent = closestCommentBlock.find('.commentcontent'),
			commentContentValue = commentContent.val(),
			isPrivate = closestCommentBlock.find('#is_private').is(':checked') ? 1 : 0,
			errorMsg,
			editCommentReason = '';

		if (!commentContentValue.trim()) {
			errorMsg = app.vtranslate('JS_LBL_COMMENT_VALUE_CANT_BE_EMPTY');
			vtUtils.showValidationMessage(commentContent, errorMsg);
			aDeferred.reject();
			return aDeferred.promise();
		}

		vtUtils.hideValidationMessage(commentContent);

		if (commentMode === 'edit') {
			editCommentReason = closestCommentBlock.find('[name="reasonToEdit"]').val();
			isPrivate = closestCommentBlock.find('[name="is_private"]').val();
		}

		app.helper.showProgress();
		let element = jQuery(e.currentTarget);
		element.attr('disabled', 'disabled');

		let commentInfoHeader = closestCommentBlock.closest('.commentDetails').find('.commentInfoHeader'),
			commentId = commentInfoHeader.data('commentid'),
			parentCommentId = commentInfoHeader.data('parentcommentid'),
			commentRelatedTo = commentInfoHeader.data('relatedto');

		if (!commentRelatedTo) commentRelatedTo = form.find('[name="record"]').val() ?? self.getRecordId();

		let incrementCount = false,
			formData = new FormData();

		formData.append('commentcontent', commentContentValue);
		formData.append('related_to', commentRelatedTo);
		formData.append('module', 'ModComments');
		formData.append('is_private', isPrivate);

		if (commentMode === 'edit') {
			formData.append('record', commentId);
			formData.append('reasontoedit', editCommentReason);
			formData.append('parent_comments', parentCommentId);
			formData.append('mode', 'edit');
			formData.append('action', 'Save');
		} else if (commentMode === 'add') {
			formData.append('parent_comments', 'undefined' === typeof commentId ? 0 : commentId);
			formData.append('action', 'SaveAjax');

			$.each(Vtiger_Index_Js.files, function (fileName, fileValue) {
				formData.append('filename[]', fileValue);
			});

			incrementCount = true;
		}

		app.request.post({data: formData, processData: false, contentType: false}).then(function (err, data) {
			Vtiger_Index_Js.files = {};
			jQuery('.MultiFile-remove').trigger('click');
			app.helper.hideProgress();

			if (incrementCount) {
				// to increment related records count when we add comment from related tab / summary view widget
				let tabElement = self.getTabByLabel("ModComments"),
					relatedController = new Vtiger_RelatedList_Js(self.getRecordId(), app.getModuleName(), tabElement, self.getRelatedModuleName());

				relatedController.updateRelatedRecordsCount(jQuery(tabElement).data('relation-id'), [1], true);
			}

			aDeferred.resolve(data);
		}, function (textStatus, errorThrown) {
			app.helper.hideProgress();
			element.removeAttr('disabled');
			aDeferred.reject(textStatus, errorThrown);
		});

		return aDeferred.promise();
	},

	/**
	 * function to remove comment block if its exists.
	 */
	removeCommentBlockIfExists : function() {
		var detailContentsHolder = this.getContentHolder();
		var Commentswidget = jQuery('.commentsBody',detailContentsHolder);
		jQuery('.addCommentBlock',Commentswidget).remove();
	},

	/**
	 * function to return cloned edit comment block
	 * return jQuery Obj.
	 */
	getEditCommentBlock : function(){ 
		var detailContentsHolder = this.getContentHolder();
		var clonedCommentBlock = jQuery('.basicEditCommentBlock',detailContentsHolder).clone(true,true).removeClass('basicEditCommentBlock hide').addClass('addCommentBlock');
		clonedCommentBlock.find('.commentcontenthidden').removeClass('commentcontenthidden').addClass('commentcontent');
		return clonedCommentBlock;
	},

	/**
	 * function to return cloned add comment block
	 * return jQuery Obj.
	 */
	getCommentBlock : function(){
		var detailContentsHolder = this.getContentHolder();
		var clonedCommentBlock = jQuery('.basicAddCommentBlock',detailContentsHolder).clone(true,true).removeClass('basicAddCommentBlock hide').addClass('addCommentBlock');
		clonedCommentBlock.find('.commentcontenthidden').removeClass('commentcontenthidden').addClass('commentcontent');
		return clonedCommentBlock;
	},


	/**
	 * function to get the Comment thread for the given parent.
	 * params: Url to get the Comment thread
	 */
	getCommentThread : function(url) {
		var aDeferred = jQuery.Deferred();
		app.request.post({"url":url}).then(function(err,data) {
			aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},


	/**
	 * Function to get child comments
	 */
	getChildComments: function (commentId) {
		let aDeferred = jQuery.Deferred(),
			url = 'module=' + app.getModuleName() + '&view=Detail&record=' + this.getRecordId() + '&mode=showChildComments&commentid=' + commentId,
			dataObj = this.getCommentThread(url);

		dataObj.then(function (data) {
			aDeferred.resolve(data);
		});

		return aDeferred.promise();
	},

	/**
	 * function to return the UI of the comment.
	 * return html
	 */
	getCommentUI : function(commentId){
		var aDeferred = jQuery.Deferred();
		var postData = {
			'view' : 'DetailAjax',
			'module' : 'ModComments',
			'record' : commentId
		}
		app.request.post({"data":postData}).then(
			function(err,data){
				aDeferred.resolve(data);
		});
		return aDeferred.promise();
	},


	getRelatedRecordsCount : function(recordId, moduleName){
		var aDeferred = jQuery.Deferred();
		var params = {
			'type' : 'GET',
			'data' : {
				'module'	: moduleName,
				'recordId'	: recordId,
				'action'	: 'RelatedRecordsAjax',
				'mode'		: 'getRelatedRecordsCount'
			}
		};
		app.request.get(params).then(function(err,data){
			if(err == null){
				aDeferred.resolve(data);
			}
		});
		return aDeferred.promise();
	},

	updateRelatedRecordsCount : function(){
		var self = this;
		var recordId = self.getRecordId();
		var moduleName = app.getModuleName();
		self.getRelatedRecordsCount(recordId, moduleName).then(function(data){
			jQuery.each(data, function(key, value){
				var element = new Object(jQuery("a","li[data-relation-id="+key+"]"));
				var numberEle = element.find('.numberCircle');
				numberEle.text(value);
				if(parseInt(value) > 0){
					numberEle.removeClass('hide');
				} else{
					numberEle.addClass('hide');
				}
				element.attr("recordscount",value);
			});
		});
	},

	toggleCommentContent: function (e) {
		var currentTarget = jQuery(e.currentTarget);
		var commentContentBlock = currentTarget.closest('.commentInfoContentBlock');
		var commentContentInfo = commentContentBlock.find('.commentInfoContent');
		var toggleElement = jQuery('<div><a class="pull-right toggleComment text-secondary"><small></small></a><div>');
		var fullComment = vtUtils.linkifyStr(commentContentInfo.data('fullcomment'));

		if (currentTarget.hasClass('showMore')) {
			toggleElement.find('small').text(commentContentInfo.data('less'));
			commentContentInfo.html(fullComment+toggleElement.clone().html());
		} else {
			var maxLength = commentContentInfo.data('maxlength');
			toggleElement.find('small').text(commentContentInfo.data('more'));
			toggleElement.find('.toggleComment').addClass('showMore');
			commentContentInfo.html(vtUtils.htmlSubstring(fullComment, maxLength)+"..."+toggleElement.clone().html());
		}
	},

	toggleRollupComments : function (e) {
		e.stopPropagation();
		e.preventDefault();
		var self = this;
		var currentTarget = jQuery(e.currentTarget);
		var moduleName = currentTarget.attr('module');
		var recordId = currentTarget.attr('record');
		var rollupId = currentTarget.attr('rollupid');
		var rollup_status = currentTarget.attr('rollup-status');
		var rollupstatus = 0;
		if (rollup_status == 0) {
			rollupstatus = 1;
		}
		var viewtype = currentTarget.data('view');
		var contents, url, params;

		if(viewtype == 'relatedlist') {
			url = 'index.php?module=Vtiger&view=ModCommentsDetailAjax&parent='+moduleName+'&parentId='+recordId+
								'&rollupid='+rollupId+'&rollup_status='+rollupstatus+'&mode=saveRollupSettings';
			params = {
				'type' : 'GET',
				'url' : url
			};
			app.request.get(params).then(function(err, data){
				currentTarget.attr('rollup-status', !rollupstatus);
				jQuery('div.related-tabs li[data-label-key="ModComments"]').trigger('click');
			});
		} else {
			url = 'index.php?module='+moduleName+'&relatedModule=ModComments&view=Detail&record='+
					recordId+'&mode=showRecentComments'+'&rollupid='+rollupId
					+'&rollup_status='+rollupstatus+'&parent='+moduleName+'&rollup-toggle=1&limit=5';
			contents = jQuery('div[data-name="ModComments"] div.widget_contents');
			params = {
				'type' : 'GET',
				'url' : url
			};
			app.request.get(params).then(function(err, data){
				app.helper.hideProgress();
				contents.html(data);
				vtUtils.enableTooltips();
				self.registerRollupCommentsSwitchEvent();
				self.setRollUpComments(rollupstatus);
			});
		}
	},
	setRollUpComments: function (value) {
		let self = this,
			rollUpComments = self.getRollUpComments();

		if (value) {
			rollUpComments.attr('checked', 'checked');
			rollUpComments.prop('checked', 'checked');
		} else {
			rollUpComments.removeAttr('checked');
			rollUpComments.removeProp('checked');
		}
	},
	registerScrollForRollupEvents: function () {
		let relatedController = this.getRelatedController();

		if (relatedController) {
			relatedController.registerScrollForRollupComments();
		}
	},

	registerStarToggle: function () {
		let self = this;

		jQuery('#starToggle').on('click', function (e) {
			let element = jQuery(e.currentTarget);

			if (element.hasClass('processing')) return;
			element.addClass('processing');

			let record = self.getRecordId(),
				params = {};

			params.module = app.getModuleName();
			params.action = 'SaveStar';
			params.record = record;

			if (element.hasClass('markStarActive')) {
				params.value = 0;
			} else {
				params.value = 1;
			}

			element.toggleClass('markStarActive');

			app.request.post({data: params}).then(function (err, data) {
				element.removeClass('processing');
			})

			if (element.hasClass('markStarActive')) {
				app.helper.showSuccessNotification({'message': app.vtranslate('JS_FOLLOW_RECORD')});
			} else {
				app.helper.showSuccessNotification({'message': app.vtranslate('JS_UNFOLLOW_RECORD')});
			}
		});
	},

	saveTag : function(callerParams) {
		var self = this;
		var aDeferred = jQuery.Deferred();
		var params = {
			'module'	: app.getModuleName(),
			'action'	: 'TagCloud',
			'mode'		: 'saveTags',
			'record'	: this.getRecordId()

		};
		var params = jQuery.extend(params, callerParams);
		app.request.post({'data': params}).then(
			function(error, data) {
				if(error == null) {
					var tagContainer = jQuery('.tagContainer');
					var tagInstance = self.getComponentInstance('Vtiger_Tag_Js');
					tagInstance.addTagsToShowAllTagContianer(data.tags);
					self.addTagsToSummaryTag(data.tags);
					if(parseInt(data.moreTagCount) > 0) {
						tagContainer.find('.tagMoreCount').text(data.moreTagCount).closest('.moreTags').removeClass('hide');
					}
					aDeferred.resolve(data);
				}else{
					aDeferred.reject(data);
				}
			}
		);
		return aDeferred.promise();
	},

	deleteTag : function(callerParams) {
		var aDeferred = jQuery.Deferred();

		var params = {
			'module' : app.getModuleName(),
			'action' : 'TagCloud',
			'mode' : 'delete',
			'record' : this.getRecordId()
		}

		var params = jQuery.extend(params, callerParams);
		app.request.post({'data': params}).then(
			function(error, data) {
				if(error == null) {
					aDeferred.resolve(data);
				}else{
					aDeferred.reject(data);
				}
			}
		);

		return aDeferred.promise();
	},

	constructTagElement : function (params) {
		var tagElement = jQuery(jQuery('#dummyTagElement').html()).clone(true);
		tagElement.attr('data-id',params.id).attr('data-type',params.type);
		tagElement.find('.tagLabel').html(params.name);
		return tagElement
	},

	showAllTags : function(container) {
		var self = this;
		var showTagModal = container.find('.showAllTagContainer').clone(true);
		app.helper.showModal(showTagModal.find('.modal-dialog'),{'cb' : function(modalContainer){

				var registerShowAllTagEvents = function(modalContainer) {
					var currentTagsSelected = new Array();
					var currentTagHolder = modalContainer.find('.currentTag');

					modalContainer.find('.dropdown-menu').on('click',function(e){
						e.stopPropagation();
					});

					modalContainer.find('.currentTagMenu > li > a ').on('click', function(e){
						var element = jQuery(e.currentTarget);
						var selectedTag = jQuery(element.html());
						currentTagsSelected.push(selectedTag.data('id'));
						element.remove();
						currentTagHolder.append(selectedTag);
					});

					app.helper.showVerticalScroll(currentTagHolder);

					modalContainer.find('.currentTagSelector').instaFilta({
						targets : '.currentTagMenu > li',
						sections : '.currentTagMenu',
						scope : '.detailShowAllModal', 
						hideEmptySections : true,
						beginsWith : false, 
						caseSensitive : false, 
						typeDelay : 0
					 });

					var tagInputEle = modalContainer.find('input[name="createNewTag"]');
					var params = {tags : [], tokenSeparators: [","]};
					vtUtils.showSelect2ElementView(tagInputEle, params);

					var form = modalContainer.find('form');
					form.on('submit',function(e){
						e.preventDefault();
						var modalContainerClone = modalContainer.clone(true);
						app.helper.hideModal();
						var saveParams = {};
						var saveTagList = {};
						saveTagList['existing'] = currentTagsSelected;
						saveTagList['new'] = tagInputEle.val().split(',')
						saveParams['tagsList'] = saveTagList;

						var formData = form.serializeFormData();
						saveParams['newTagType'] = formData['visibility'];
						self.saveTag(saveParams).then(function(data){
							jQuery('.showAllTagContainer').find('.currentTag').html(modalContainerClone.find('.currentTag').html());
							jQuery('.showAllTagContainer').find('.currentTagMenu').html(modalContainerClone.find('.currentTagMenu').html());
						})
						return false;
					})

				}
				registerShowAllTagEvents(modalContainer);
		}});
	},

	addTagsToSummaryTag: function (tagsList) {
		let self = this,
            summaryTagList = jQuery('.detailTagList'),
			index;

		for (index in tagsList) {
			let tagInfo = tagsList[index],
				tagId = tagInfo.id;

            if (summaryTagList.find('[data-id="' + tagId + '"]').length <= 0) {
                let newTagEle = self.constructTagElement(tagInfo);
                summaryTagList.append(newTagEle);
            }
		}

		if (summaryTagList.find('.tag').length > 0) {
			summaryTagList.closest('.tag-contents').removeClass('hide');
		}
	},

    removeDeletedTagsFromSummaryTag: function (deletedTags) {
        let summaryTagContainer = jQuery('.detailTagList');
        for (let index in deletedTags) {
            let tag = summaryTagContainer.find('.tag[data-id="' + deletedTags[index] + '"]');

            if (tag.length > 0) {
                let showAllTagContainer = jQuery('.showAllTagContainer'),
                    currentTagHolder = showAllTagContainer.find('.currentTag'),
                    summaryLastTag = summaryTagContainer.find('.tag').filter(':last'),
                    nextTag = currentTagHolder.find('[data-id="' + summaryLastTag.data('id') + '"]').next();

                summaryTagContainer.find('.moreTags').before(nextTag.clone(true));
                tag.remove();
            }
        }
    },

	registerTagSearch: function () {
		let tagSearch = jQuery('#tag-search')

		if (tagSearch.length) {
			tagSearch.instaFilta({
				targets: '#addTagContainer .existingTag .tag-item',
				sections: '#addTagContainer .existingTag',
				hideEmptySections: true,
				beginsWith: false,
				caseSensitive: false,
				typeDelay: 0
			});
		}
	},

	postTagDeleteActions : function(deletedTagClone) {
		var summaryTagContainer = jQuery('.detailTagList');
		var tagInstance = this.getComponentInstance('Vtiger_Tag_Js');
		var tagInfo = {
			'id' : deletedTagClone.data('id')
		};
		tagInstance.removeTagsFromShowTagContainer(new Array(tagInfo));

		if(summaryTagContainer.find('.tag').length <= 0 ) {
			summaryTagContainer.find('.noTagsPlaceHolder').removeClass('hide');
		}else{
			var moreTagsEle = summaryTagContainer.find('.tagMoreCount');
			if(!moreTagsEle.closest('.moreTags').hasClass('hide')) {
				var moreTagsCount = parseInt(moreTagsEle.text());
				moreTagsCount--;
				moreTagsEle.text(moreTagsCount);
				if(moreTagsCount <=0 ){
					moreTagsEle.closest('.moreTags').addClass('hide');
				} 
			}
		}
	},


	registerTagEvents : function() {
		var self = this;
		var tagContainer = jQuery('.tagContainer');

		tagContainer.find('#addTagContainer .dropdown-menu').on('click',function(e){
			e.stopPropagation();
		});
		var tagInputEle = tagContainer.find('.newTags');
		var params = {tags : [], tokenSeparators: [","]};
		vtUtils.showSelect2ElementView(tagInputEle, params);

		var existinTagContainer = tagContainer.find('.existingTag');
		app.helper.showVerticalScroll(existinTagContainer);

		this.registerTagSearch();

		jQuery('#saveTag').on('click', function(e){
			tagContainer.find('.dropdown-toggle').dropdown('toggle');
			var element = jQuery(e.currentTarget);
			var createTagContainer = element.closest('.createTagContainer');
			var existingTagContainer = createTagContainer.find('.existingTag');
			var selectedExistingTags = new Array();
			var selectedTagElement = existingTagContainer.find('.tagSelector').filter(':checked').closest('li.tag-item');
			selectedTagElement.each(function(index, domEle){
				var ele = jQuery(domEle);
				selectedExistingTags.push(ele.find('.tag').data('id'));
			});
			var newTagEle = createTagContainer.find('input.newTags');
			var newTags = newTagEle.val();
			var tagsList = {};
			tagsList['new'] = newTags.split(',');
			tagsList['existing'] = selectedExistingTags;
			var visibility = createTagContainer.find('[name="visibility"]').val();
			var visibilityCheckBox = createTagContainer.find('[type="checkbox"][name="visibility"]');
			if(visibilityCheckBox.is(':checked')){
				visibility = visibilityCheckBox.val();
			}
			self.saveTag({'tagsList':tagsList,'newTagType': visibility}).then(function(data){
				selectedTagElement.remove();
				newTagEle.select2('val','');
			});
		});

		tagContainer.find('.cancelLink').on('click', function(e){
			tagContainer.find('.dropdown-toggle').dropdown('toggle');
		});

		app.event.on('post.MassTag.save',function(e, modalContainerClone, data){
			 let moreTagCount = parseInt(data.moreTagCount);

			 if(moreTagCount === 0) {
				 tagContainer.find('.tagMoreCount').closest('.moreTags').addClass('hide');
			 } else if(moreTagCount > 0){
				 tagContainer.find('.tagMoreCount').text(data.moreTagCount).closest('.moreTags').removeClass('hide');
			 }

			 jQuery('.showAllTagContainer .currentTag').html(modalContainerClone.find('.currentTag').html());
			 jQuery('.viewAllTagsContainer .currentTag').html(modalContainerClone.find('.currentTag').html());
			 jQuery('.showAllTagContainer .currentTagMenu').html(modalContainerClone.find('.currentTagMenu').html());

			 let tagInstance = self.getComponentInstance('Vtiger_Tag_Js');
			 tagInstance.addTagsToShowAllTagContianer(data.tags);
			 self.removeDeletedTagsFromSummaryTag(data.deleted);
			 self.addTagsToSummaryTag(data.tags);
		})

		tagContainer.find('.moreTags').on('click',function(e){
			//self.showAllTags(tagContainer);
			app.event.trigger('Request.AllTag.show',tagContainer, {'record' : self.getRecordId()});
		});

		tagContainer.on('click', '.deleteTag', function(e){
			var tag = jQuery(e.currentTarget).closest('.tag');
			self.deleteTag({'tag_id':tag.data('id')}).then(function(){
				var summaryTagContainer = jQuery('.detailTagList');
				var showAllTagContainer = jQuery('.showAllTagContainer');
				var currentTagHolder = showAllTagContainer.find('.currentTag');
				var summaryLastTag = summaryTagContainer.find('.tag').filter(':last');
				var nextTag = currentTagHolder.find('[data-id="'+ summaryLastTag.data('id') +'"]').next();

				summaryTagContainer.find('.moreTags').before(nextTag.clone(true));
				tag.remove();

				self.postTagDeleteActions(tag.clone(true));
				if(summaryTagContainer.find('.tag').length == 0){
					summaryTagContainer.closest('.tag-contents').addClass('hide');
				}
			})
		});

		tagContainer.on('click', '#addTagTriggerer', function(e){
			app.event.trigger('Request.MassTag.show',tagContainer, {'record' : self.getRecordId()});
		});
	},

	/**
	 * Function to register event for related list row click
	 */
	registerRelatedRowClickEvent: function() {
		var detailContentsHolder = this.getContentHolder();
		detailContentsHolder.on('click','.relatedListEntryValues a',function(e){
			e.preventDefault();
		});
		detailContentsHolder.on('click','.listViewEntries',function(e){
				var selection = window.getSelection().toString();
			if(selection.length == 0) { 
				var targetElement = jQuery(e.target, jQuery(e.currentTarget));
				if(targetElement.hasClass('js-reference-display-value')) return;
				if(targetElement.is('td:first-child') && (targetElement.children('input[type="checkbox"]').length > 0)) return;
				if(jQuery(e.target).is('input[type="checkbox"]')) return;
					var elem = jQuery(e.currentTarget);
					var recordUrl = elem.data('recordurl');
				if(typeof recordUrl != "undefined"){
						var params = app.convertUrlToDataParams(recordUrl);
						//Display Mode to show details in overlay
						params['mode'] = 'showDetailViewByMode';
						params['requestMode'] = 'full';
						params['displayMode'] = 'overlay';
						var parentRecordId = app.getRecordId();
						app.helper.showProgress();
						app.request.post({data: params}).then(function(err, response) {
							app.helper.hideProgress();
							var overlayParams = {'backdrop' : 'static', 'keyboard' : false};
							app.helper.loadPageContentOverlay(response, overlayParams).then(function(container) {
								var detailjs = Vtiger_Detail_Js.getInstanceByModuleName(params.module);
								detailjs.showScroll(jQuery('.overlayDetail .modal-body'));
								detailjs.setModuleName(params.module);
								detailjs.setOverlayDetailMode(true);
								detailjs.setContentHolder(container.find('.overlayDetail'));
								detailjs.setDetailViewContainer(container.find('.overlayDetail'));
								detailjs.registerOverlayEditEvent();
								detailjs.registerBasicEvents();
								detailjs.registerClickEvent();
								detailjs.registerHeaderAjaxEditEvents(container.find('.overlayDetailHeader'));
								detailjs.registerEventToReloadRelatedListOnCloseOverlay(parentRecordId);
								app.event.trigger('post.overlay.load', parentRecordId, params); 
								container.find('form#detailView').on('submit', function(e) {
									e.preventDefault();
							});
						});
						});
					}
					}
		});
	},

	registerEventToReloadRelatedListOnCloseOverlay: function(parentId) {
		var self = this;
		var overlayContainer = jQuery('#overlayPageContent');
		overlayContainer.one("click", ".close", function(e) {
			self.loadRelatedListOfParent(parentId);
		});
	},

	loadRelatedListOfParent: function(parentRecordId) {
		var self = this;
		var relatedController = self.getRelatedController();
		relatedController.setParentId(parentRecordId);
		if (relatedController) {
			relatedController.loadRelatedList();
		}
	},


	showOverlayEditView: function (recordUrl) {
		let self = this,
			params = app.convertUrlToDataParams(recordUrl);

		params['displayMode'] = 'overlay';

		let postData = self.getDefaultParams(),
			key;

		for (key in postData) {
			if (postData[key]) {
				if (key === 'relatedModule') {
					params['returnrelatedModuleName'] = postData[key];
				} else {
					params['return' + key] = postData[key];
				}
				delete postData[key];
			} else {
				delete postData[key];
			}
		}

		params['returnrecord'] = jQuery('[name="record_id"]').val();

		app.helper.showProgress();
		app.request.get({data: params}).then(function (err, response) {
			app.helper.hideProgress();
			let overlayParams = {'backdrop': 'static', 'keyboard': false};

			app.helper.loadPageContentOverlay(response, overlayParams).then(function (container) {
				let height = jQuery(window).height() - jQuery('.app-fixed-navbar').height() - jQuery('.overlayFooter').height() - 80,
					scrollParams = {
						setHeight: height,
						alwaysShowScrollbar: 2,
						autoExpandScrollbar: true,
						setTop: 0,
						scrollInertia: 70
					}

				app.helper.showVerticalScroll(jQuery('.modal-body'), scrollParams);
				self.registerOverlayEditEvents(params.module, container);
				self.registerRelatedRecordSave();
				app.event.trigger('post.overLayEditView.loaded', jQuery('.overlayEdit'));
			});
		});
	},
	registerOverlayEditEvent: function () {
		let self = this;

		jQuery('.editRelatedRecord').on('click', function () {
			let editUrl = jQuery('.editRelatedRecord').val();
			self.showOverlayEditView(editUrl);
		});
	},

	registerRelatedRecordEdit: function(){
		let self = this,
			detailViewContainer = this.getContentHolder();

		detailViewContainer.on('click', 'a[name="relationEdit"]', function(e) {
			e.stopImmediatePropagation();
			let element = jQuery(e.currentTarget),
				editUrl = element.data('url');

			self.showOverlayEditView(editUrl);
		});
	},

	getDetails: function() {
		return jQuery('.details');
	},

	registerClickEvent: function () {
		this.getContentHolder().on('click', '.inventoryLineItemDetails', function (e) {
			let popoverElements = document.querySelectorAll('[data-bs-toggle="popover"]')

			for (const popoverEl of popoverElements) {
				new bootstrap.Popover(popoverEl)
			}
		});
	},
	showScroll: function(container) {
		var params = {
			setHeight: container.height,
			alwaysShowScrollbar: 2,
			autoExpandScrollbar: true,
			setTop: 0,
			scrollInertia: 70,
			mouseWheel: {preventDefault: true}
		};
		app.helper.showVerticalScroll(container, params);
	},

	recordImageRandomColors: function(){
		var color=  jQuery('.recordImage').css('background-color');
		if(color === "rgba(0, 0, 0, 0)"){
			jQuery('.recordImage').css('background-color', app.helper.getRandomColor());
		}
	},

	getFieldValue : function(fieldName,sourceElement){
		var form = this.getForm();
		var fieldBasicData = form.find('.fieldBasicData').filter('[data-name="'+fieldName+'"]');
		return fieldBasicData.attr('data-value');
	},

	registerQtipevent: function (tabItem) {
		if(typeof tabItem == 'undefined'){
		var container = jQuery('.related-tabs.row');
		var scrollContent = container.find('.dropdown #relatedmenuList');
		app.helper.showVerticalScroll(scrollContent,{autoHideScrollbar: true});
			tabItem = container.find('.tab-item, .more-tab');
		}
		var title;
		jQuery(tabItem).each(function () {
			title = jQuery(this).attr('title');
			jQuery(this).qtip({
				content: title,
				hide: {
					event:'click mouseleave',
				},
				position: {
					my: 'bottom center',
					at: 'top left',
					adjust: {
						x: 30,
						y: 10
					}
				},
				style: {
					classes: 'qtip-dark'
				}
			});
		});
	},

	registerDetailViewThread() {
		this.getContentHolder().on('click', '.detailViewThread', function (e) {
			let recentCommentsTab = self.getTabByLabel(self.detailViewRecentCommentsTabLabel),
				commentId = jQuery(e.currentTarget).closest('.singleComment').find('.commentInfoHeader').data('commentid');

			if (recentCommentsTab) {
				e.preventDefault();
				recentCommentsTab.trigger('click', {'commentid': commentId});
			}
		});
	},
	registerEvents : function() {
		this._super();
		this.registerEventsForRelatedList();
		var detailContentsHolder = this.getContentHolder();
		var self = this;
		this.registerSendSmsSubmitEvent();
		this.registerDetailViewThread();
		this.registerStarToggle();
		this.registerTagEvents();
		app.event.on("post.mail.sent",function(event,data){
			var resultEle = jQuery(data);
			var success = resultEle.find('.mailSentSuccessfully');
			if(success.length > 0){
				var relatedLoad = success.data("relatedload");
				if(relatedLoad == 1){
					var pageNumber = jQuery('[name="currentPageNum"]').val();
					window.app.controller().loadRelatedListRecords({page: pageNumber});
				} else {
					app.helper.showModal(data);
				}
			}
		});
		detailContentsHolder.on('click', '.moreRecentUpdates', function (e) {
			app.helper.showProgress();

			e.preventDefault();

			let currentPage = jQuery("#updatesCurrentPage").val(),
				form = $(this).closest('form'),
				recordElement = form.find('[name=record]'),
				moduleElement = form.find('[name=module]'),
				nextPage = parseInt(currentPage) + 1,
				postParams = {
					module: moduleElement.length ? moduleElement.val() : app.getModuleName(),
					view: 'Detail',
					record: recordElement.length ? recordElement.val() : jQuery("#recordId").val(),
					mode: 'showRecentActivities',
					page: nextPage,
					limit: 5,
					tab_label: 'LBL_UPDATES',
				};

			app.request.post({data: postParams}).then(function (err, data) {
				jQuery('#updatesCurrentPage').remove();
				jQuery('#moreLink').remove();
				jQuery('#more_button').remove();
				data = jQuery(data).removeClass("recentActivitiesContainer");
				jQuery('#updates .history').append($(data).find('.history').html());
				app.helper.hideProgress();
			});
		});

		this.updateRelatedRecordsCount();
		//RegisterBasicEvents for Related-List overlay's
		this.registerBasicEvents();
		this.registerHeaderAjaxEditEvents();
		this.registerAssignedUserChange();
		this.registerAssignedUserSearch();
		this.registerChangeDetailAssignedUser();

		detailContentsHolder.on('click','.detailViewSaveComment', function(e){
			let element = jQuery(e.currentTarget);

			if(!element.is(":disabled")) {
				self.saveComment(e).then(function(){
					let commentsContainer = detailContentsHolder.find("[data-name='ModComments']");

					self.loadWidget(commentsContainer).then(function () {
						element.removeAttr('disabled');
						app.event.trigger('post.summarywidget.load', commentsContainer);

						Vtiger_Index_Js.getInstance().registerMultiUpload();
					});
				});
			}
		});

		detailContentsHolder.on('click', '.saveComment', function (e) {
			let element = jQuery(e.currentTarget);

			if (!element.is(":disabled")) {
				let currentTarget = jQuery(e.currentTarget);

				self.saveComment(e).then(function (data) {
					let commentElement = currentTarget.parents('.commentDiv'),
						commentId = commentElement.find('.commentInfoHeader').data('commentid'),
						commentNew = false;

					if ('undefined' === typeof commentId) {
						commentId = data.id;
						commentNew = true
					}

					self.getChildComments(commentId).then(function (childCommentsData) {
						if (commentNew) {
							detailContentsHolder.find('.commentsList').prepend(childCommentsData)
							detailContentsHolder.find('.commentcontent').val('');
						} else {
							commentElement.html(childCommentsData);
							commentElement.find('.childComments').addClass('show');
						}
					});

					element.removeAttr('disabled');
					Vtiger_Index_Js.getInstance().registerMultiUpload();
				});
			}
		});

		detailContentsHolder.on('click', '.editComment', function (e) {
			self.removeCommentBlockIfExists();
			let currentTarget = jQuery(e.currentTarget),
				commentInfoBlock = currentTarget.closest('.singleComment'),
				commentInfoContent = commentInfoBlock.find('.commentInfoContent'),
				commentReason = commentInfoBlock.find('[name="editReason"]'),
				editCommentBlock = self.getEditCommentBlock(),
				fullComment = commentInfoContent.data('fullcomment');

			if (fullComment) {
				fullComment = app.helper.getDecodedValue(fullComment);
			} else {
				fullComment = commentInfoContent.text();
			}

			editCommentBlock.find('.commentcontent').text(fullComment.trim());
			editCommentBlock.find('[name="reasonToEdit"]').val(commentReason.text());
			editCommentBlock.find('[name="is_private"]').val(commentInfoBlock.find('[name="is_private"]').val());
			editCommentBlock.appendTo(commentInfoBlock).show();
		});

		detailContentsHolder.on('click','.closeCommentBlock', function(e){
			var currentTarget = jQuery(e.currentTarget);
			var commentInfoBlock = currentTarget.closest('.singleComment');
			commentInfoBlock.find('.commentActionsContainer').show();
			commentInfoBlock.find('.commentInfoContent').show();
			self.removeCommentBlockIfExists();
		});

		detailContentsHolder.on('click','.replyComment', function(e){
			self.removeCommentBlockIfExists();
			var currentTarget = jQuery(e.currentTarget);
			var commentInfoBlock = currentTarget.closest('.singleComment');
			var message = commentInfoBlock.find('.commentInfoContent').text();
			var commentInfoHeader = commentInfoBlock.find('.commentInfoHeader');
			var commentId = commentInfoHeader.data('commentid');
			var addCommentBlock = self.getCommentBlock();
			/*commentInfoBlock.find('.commentActionsContainer').hide();*/
			addCommentBlock.appendTo(commentInfoBlock).show();

			var params = {
				'module': app.getModuleName(),
				'action': 'MentionedUsers',
				'message':message,
				'crmid':commentId
			};

			app.request.post({data: params}).then(
				function(err, data) {
					if (data) {
						var commentArea = commentInfoBlock.find('.commentcontent');
						commentArea.val(data.usersString);
						commentArea.focus();
						var strLength= commentArea.val().length * 2;
						commentArea[0].setSelectionRange(strLength, strLength);
					}
				});
		});

		detailContentsHolder.on('click', '.moreRecentRecords', function (e) {
			let recentData = app.convertUrlToDataParams($(this).attr('href')),
				recentTab = self.getTabByLabel(recentData['tab_label']);

			if (recentTab) {
				e.preventDefault();
				recentTab.trigger('click');
			}
		});

		detailContentsHolder.on('click','.moreRecentComments', function(e){
			let recentCommentsTab = self.getTabByLabel(self.detailViewRecentCommentsTabLabel);

			if(recentCommentsTab) {
				e.preventDefault();
				recentCommentsTab.trigger('click');
			}
		});

		detailContentsHolder.on('click','.moreRecentActivities', function(e){
			let recentActivitiesTab = self.getTabByLabel(self.detailViewRecentActivitiesTabLabel);

			if(recentActivitiesTab) {
				e.preventDefault();
				recentActivitiesTab.trigger('click');
			}
		});

		detailContentsHolder.on('click', '.moreRecentDocuments', function (e) {
			let recentDocumentsTab = self.getTabByLabel(self.detailViewRecentDocumentsLabel);

			if(recentDocumentsTab) {
				e.preventDefault();
				recentDocumentsTab.trigger('click');
			}
		});

		detailContentsHolder.off('.toggleComment').on('click', '.toggleComment', function (e) {
			self.toggleCommentContent(e);
		});

		app.event.on('post.summarywidget.load',function(event,widgetContainer){
			vtUtils.applyFieldElementsView(widgetContainer);

			//For Rollup Comments
			if(self.getRollUpComments().length > 0 && widgetContainer.data('name') == 'ModComments') {
				widgetContainer.off('change').on('change', '#rollupcomments', function(e){
					app.helper.showProgress();
					self.toggleRollupComments(e);
				});

				if (self.isRollUpComments()) {
					self.setRollUpComments(true);
				} else {
					self.setRollUpComments(false);
				}
			}
			var vtigerInstance = Vtiger_Index_Js.getInstance();
			vtUtils.enableTooltips();
			//END
		});		
		//For Rollup Comments
		if(self.getRollUpComments().length > 0) {
			detailContentsHolder.on('change', '#rollupcomments', function(e){
				app.helper.showProgress();
				self.toggleRollupComments(e);
			});

			if (self.isRollUpComments()) {
				self.setRollUpComments(true);
			} else {
				self.setRollUpComments(false);
			}
		}
		//END

		this.registerRelatedRowClickEvent();
		this.registerSummaryViewContainerEvents(self.getDetailViewContainer());

		//prevent detail view ajax form submissions
		jQuery('form#detailView, form#headerForm').on('submit', function(e) {
			e.preventDefault();
		});

		if(typeof jQuery.fn.sadropdown === 'function') {
			jQuery('.widgetContainer_documents').find('.dropdown-toggle').sadropdown({
				relativeTo: '#detailView'
			});
		}

		vtUtils.registerReplaceCommaWithDot($(document));
	},
	registerChangeDetailAssignedUser() {
		app.event.on(Vtiger_Detail_Js.changeAssignedUserEvent, function (event, userData) {
			$('.related-tabs').find('.tab-item.active').trigger('click');
		});
	},
	registerAssignedUserSearch() {
		let self = this,
			container = self.getDetailViewContainer(),
			timeout;

		container.on('keyup', '.assignedUsersSearch', function () {
			let searchElement = $(this);

			if(timeout) {
				clearTimeout(timeout);
			}

			timeout = setTimeout(function() {
				console.log('Test 0122', Math.random());

				self.loadAssignedUsers(searchElement)
			}, 500);
		});
	},
	loadAssignedUsers(searchElement) {
		let params = {
			module: app.getModuleName(),
			view: 'DetailAjax',
			mode: 'searchAssignedUsers',
			search: searchElement.val()
		};

		app.request.post({data: params}).then(function (error, data) {
			if (!error) {
				searchElement.parents('.assignedUsersSearchContainer').next('.assignedUsersContainer').html(data);
			}
		});
	},
	registerAssignedUserChange() {
		let self = this,
			message = app.vtranslate('JS_ASSIGNED_USER_HAS_CHANGE'),
			container = self.getDetailViewContainer();

		container.on('click', '[data-change-assigned-user]', function () {
			let userData = $(this).data(),
				params = {
					record: app.getRecordId(),
					module: app.getModuleName(),
					action: 'SaveAjax',
					value: userData['id'],
					field: 'assigned_user_id',
				};

			app.request.post({data: params}).then(function (error, data) {
				if (!error) {
					if (parseInt(userData['id']) === parseInt(data['assigned_user_id']['value'])) {
						let css = {
							'background-image': 'url("layouts/d1/modules/Users/resources/user.svg")',
							'background-size': '50%',
						}

						if ('group' === userData['changeAssignedUser']) {
							css = {
								'background-image': 'url("layouts/d1/modules/Users/resources/users.svg")',
								'background-size': '50%',
							}
						}

						if (userData['image']) {
							css = {
								'background-image': 'url("' + userData['image'] + '")',
								'background-size': 'cover',
							};
						}

						container.find('[data-assigned-user-image]').css(css);
						container.find('[data-assigned-user-name]').text(userData['name']);
						container.find('[data-assigned-user-id]').val(userData['id']);

						app.event.trigger(Vtiger_Detail_Js.changeAssignedUserEvent, userData);
						app.helper.showSuccessNotification({message: message});
					} else {
						app.helper.showErrorNotification({message: message});
					}
				}
			})
		});
	},

	/**
	 * Function to register event for setting up picklistdependency
	 * for a module if exist on change of picklist value
	 */
	registerEventForPicklistDependencySetup : function(container){
		var thisInstance = this;
		var picklistDependcyElemnt = jQuery('[name="picklistDependency"]',container);
		if(picklistDependcyElemnt.length <= 0) {
			return;
		}
		var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());
		var sourcePicklists = Object.keys(picklistDependencyMapping);
		if(sourcePicklists.length <= 0){
			return;
		}

		var sourcePickListNames = "";
		for(var i=0;i<sourcePicklists.length;i++){
			sourcePickListNames += '[name="'+sourcePicklists[i]+'"],';
		}
		sourcePickListNames = sourcePickListNames.replace(/(^,)|(,$)/g, "");
		container.on('change', sourcePickListNames, function(e) {
			var currentElement = jQuery(e.currentTarget);
			var sourcePicklistname = currentElement.attr('name');

			var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
			var selectedValue = currentElement.val();
			var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
			var picklistmap = configuredDependencyObject["__DEFAULT__"];
			if(typeof targetObjectForSelectedSourceValue == 'undefined'){
				targetObjectForSelectedSourceValue = picklistmap;
			}
			jQuery.each(picklistmap,function(targetPickListName,targetPickListValues){
				var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
				if(typeof targetPickListMap == "undefined"){
					targetPickListMap = targetPickListValues;
				}
				var targetPickList = jQuery('[data-name="'+targetPickListName+'"]',container);
				if(targetPickList.length <= 0){
					return;
				}

				//On change of SourceField value, If TargetField value is not there in mapping, make user to select the new target value also.
				var selectedValue = targetPickList.data('value');
				if(jQuery.inArray(selectedValue, targetPickListMap) == -1) {
					thisInstance.targetPicklistChange = true;
					thisInstance.targetPicklist = targetPickList.closest('td');
					thisInstance.sourcePicklistname = sourcePicklistname;
				} else {
					thisInstance.targetPicklistChange = false;
					thisInstance.targetPicklist = false;
					thisInstance.sourcePicklistname = false;
				}
			})
		});
	},

	registerPostAjaxSaveEvent: function () {
		var _this = this;
		app.event.on(Vtiger_Detail_Js.PostAjaxSaveEvent, function (e, fieldBasicData, postSaveRecordDetails,contentHolder) {
			if(typeof contentHolder == 'undefined'){
				return;
			}
			var isHeaderAjax = contentHolder.find('.headerAjaxEdit').length;
			if (fieldBasicData.length && isHeaderAjax) {
				var detailViewContainer = _this.getDetailViewContainer();
				var activeTabItem = jQuery('.related-tabs', detailViewContainer).find('li.tab-item').filter('.active');
				if (!activeTabItem.length) {
					activeTabItem = jQuery('.related-tabs', detailViewContainer).find('li.tab-item:first');
				}
				var activeTabName = activeTabItem.data('label-key');
				if (activeTabName == 'Details' || activeTabName == 'Summary') {
					activeTabItem.trigger('click');
				}
			}
		});
	},

	registerHeaderAjaxEditEvents : function(contentHolder) {
		var self = this;

		if(typeof contentHolder === 'undefined') {
			contentHolder = jQuery('.detailview-header');
		} 

		contentHolder.on('click','.recordBasicInfo .fieldLabel .editAction', function(e){
			var currentTarget = jQuery(e.currentTarget);
			currentTarget.hide();
			var currentContainerElement = currentTarget.closest('.headerAjaxEdit');
			self.ajaxEditHandling(currentContainerElement);
		});

		this.registerAjaxEditSaveEvent(contentHolder);
		this.registerAjaxEditCancelEvent(contentHolder);
		this.registerClearReferenceSelectionEvent(contentHolder);
		this.registerPostAjaxSaveEvent();
	},

	//Events common for DetailView and OverlayDetailView
	registerBasicEvents: function(){
		var self = this;
		this.registerAjaxEditEvent();
		this.registerAjaxEditSaveEvent();
		this.registerAjaxEditCancelEvent();
		this.recordImageRandomColors();
		this.registerQtipevent();

		app.event.on("post.RecordList.click", function(event, data) {
			var responseData = JSON.parse(data);
			var idList = new Array();
			for (var id in responseData) {
				idList.push(id);
			}
			app.helper.hideModal();
			var relatedController = self.getRelatedController();
			if (relatedController) {
				relatedController.addRelations(idList).then(function() {
					relatedController.loadRelatedList();
				});
			}
		});
		this.registerBlockAnimationEvent();
		this.registerBlockStatusCheckOnLoad();
		this.registerClearReferenceSelectionEvent();
		//register event for picklist dependency setup
		this.registerEventForPicklistDependencySetup(this.getForm());
		vtUtils.enableTooltips();
	},
});
