/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

Vtiger.Class('Documents_Index_Js', {

	fileObj : false,

	referenceCreateMode : false,
	referenceFieldName : '',

	hierarchyMap : {
		'GoogleDrive' : {},
		'Dropbox' : {}
	},

	getInstance : function() {
		return new Documents_Index_Js();
	},

	authorize : function(serviceName,parentId,relatedModule,mode,referenceFieldName) {
		var instance = Documents_Index_Js.getInstance();
		instance.detectReferenceCreateMode(referenceFieldName);
		instance.authorize(serviceName,parentId,relatedModule,mode,referenceFieldName);
	},

	uploadTo : function(service,parentId,relatedModule,referenceFieldName) {
		var instance = Documents_Index_Js.getInstance();
		instance.detectReferenceCreateMode(referenceFieldName);
		instance.uploadTo(service,parentId,relatedModule);
	},

	createDocument : function(type,parentId,relatedModule,referenceFieldName) {
		var instance = Documents_Index_Js.getInstance();
		instance.detectReferenceCreateMode(referenceFieldName);
		instance.createDocument(type,parentId,relatedModule);
	},

	revokeAccess : function(label,revokeUrl) {
		var instance = Documents_Index_Js.getInstance();
		instance.revokeAccess(label,revokeUrl);
	},

	selectFrom : function(service,parentId,relatedModule, referenceFieldName) {
		var instance = Documents_Index_Js.getInstance();
		instance.detectReferenceCreateMode(referenceFieldName);
		instance.selectFrom(service,parentId,relatedModule);
	}

}, {

	detectReferenceCreateMode : function(referenceFieldName) {
		if(typeof referenceFieldName !== 'undefined') {
			Documents_Index_Js.referenceCreateMode = true;
			Documents_Index_Js.referenceFieldName = referenceFieldName;
		} else {
			Documents_Index_Js.referenceCreateMode = false;
			Documents_Index_Js.referenceFieldName = '';
		}
	},

	revokeAccess : function(label,revokeUrl) {
		app.helper.showConfirmationBox({
			message:app.vtranslate('JS_ARE_YOU_SURE_TO_REVOKE_ACCESS')
		}).then(function() {
			app.helper.showProgress();
			app.request.post({'url' : revokeUrl}).then(function(e,resp) {
				app.helper.hideProgress();
				if(!e) {
					if(resp.accessRevoked) {
						jQuery('#Documents_listview_customsettingaction_'+label).addClass('hide');
						if(jQuery('.settingsIcon').find('ul.dropdown-menu').find('li').not('.hide').length <= 0) {
							jQuery('.settingsIcon').parent('li').addClass('hide');
						}
						var service = 'GoogleDrive'
						if(label !== 'LBL_REVOKE_ACCESS_TO_DRIVE') {
							service = 'Dropbox';
						}
						jQuery('#'+service+'UploadAction > a').attr('href','javascript:Documents_Index_Js.authorize(\''+service+'\', undefined, undefined, "upload")');
						jQuery('#'+service+'SelectAction > a').attr('href','javascript:Documents_Index_Js.authorize(\''+service+'\', undefined, undefined, "select")');
						app.helper.showSuccessNotification({
							'message' : app.vtranslate('JS_ACCESS_REVOKED')
						});
					}
				}
			});
		});
	},

	getFile : function() {
		return Documents_Index_Js.fileObj;
	},

	setFile : function(file) {
		Documents_Index_Js.fileObj = file;
	},

	isRelatedList : function() {
		var relatedModuleNameContainer = jQuery('.relatedContainer').find('.relatedModuleName');
		return relatedModuleNameContainer.length && relatedModuleNameContainer.val() === 'Documents';
	},

	reloadListView : function() {
		var activeFolderEle = jQuery("#folders-list").find('li.documentFolder.active');
		var params = {};
		if(activeFolderEle.length) {
			var activeFolderName = activeFolderEle.find('.filterName').data('folderName');
			params ={
				"folder_id" : 'folderid',
				"folder_value" : activeFolderName
			};
		}

		var list = Vtiger_List_Js.getInstance();
		list.loadListViewRecords(params);
	},

	reloadRelatedListView : function() {
		var parentId = jQuery('#recordId').val();
		var parentModule = app.getModuleName();
		var relatedModuleName = jQuery('.relatedModuleName').val();
		var selectedRelatedTabElement = jQuery('div.related-tabs')
				.find('li').filter('.active');
		var relatedList = Vtiger_RelatedList_Js.getInstance(parentId, parentModule, selectedRelatedTabElement, relatedModuleName);
		relatedList.loadRelatedList();
	},

	isDocumentsSummaryWidgetAvailable : function() {
		return jQuery('.widgetContainer_documents').length;
	},

	reloadSummaryWidget : function() {
		var detailInstance = Vtiger_Detail_Js.getInstance();
		detailInstance.loadWidget(jQuery('.widgetContainer_documents'));
	},

	reloadList : function() {
		if(app.getModuleName() === 'Documents' && app.view() === 'List') {
			this.reloadListView();
		} else if(this.isRelatedList()) {
			this.reloadRelatedListView();
		} else if(this.isDocumentsSummaryWidgetAvailable()) {
			this.reloadSummaryWidget();
		}
	},

	_upload : function(form,extraData) {
		var aDeferred = jQuery.Deferred();
		var formData = new FormData(form[0]);
		var file = this.getFile();
		if(file) {
			if(typeof extraData === 'object') {
				jQuery.each(extraData, function(name,value) {
					formData.append(name,value);
				});
			}
			//append file
			var fileName = form.find('input[type="file"]').attr('name');
			formData.append(fileName,file);

			var params = {
				url: "index.php",
				type: "POST",
				data: formData,
				processData: false,
				contentType: false
			};
			app.helper.showProgress();
			app.request.post(params).then(function(e,res) {
				app.helper.hideProgress();
				if(!e) {
					aDeferred.resolve(res);
				} else {
					aDeferred.reject(e);
				}
			});
		} else {
			aDeferred.reject();
		}
		return aDeferred.promise();
	},

	uploadFileToVtiger : function(container) {
		var self = this;
		var file = this.getFile();
		if(!file) {
			app.helper.showErrorNotification({
				'message' : app.vtranslate('JS_PLEASE_SELECT_A_FILE')
			});
			return;
		}
		var extraData = {
			'filelocationtype' : 'I'
		};
		if(file) {
			extraData['notes_title'] = container.find('form').find('[name="notes_title"]').val();
		}

		this._upload(container.find('form'),extraData).then(function(data) {
			app.helper.showSuccessNotification({
				'message' : app.vtranslate('JS_UPLOAD_SUCCESSFUL')
			});
			app.helper.hideModal();
			self.reloadList();
			var form = container.find('form');
			var folderid = form.find('[name="folderid"]').val();
			app.event.trigger('post.documents.save', {'folderid' : folderid});

			//reference create handling
			if(Documents_Index_Js.referenceCreateMode === true && Documents_Index_Js.referenceFieldName !== '') {
				self.postQuickCreateSave(data);
			}
		}, function(e) {
			app.helper.showErrorNotification({'message' : app.vtranslate('JS_UPLOAD_FAILED')});
		});
	},

	postQuickCreateSave: function (data) {
		var vtigerInstance = Vtiger_Index_Js.getInstance();
		var container = vtigerInstance.getParentElement(jQuery('[name="'+Documents_Index_Js.referenceFieldName+'"]'));
		var module = vtigerInstance.getReferencedModuleName(container);
		var params = {};
		params.name = data._recordLabel;
		params.id = data._recordId;
		params.module = module;
		vtigerInstance.setReferenceFieldValue(container, params);

		var tdElement = vtigerInstance.getParentElement(container.find('[value="' + module + '"]'));
		var sourceField = tdElement.find('input[class="sourceField"]').attr('name');
		var fieldElement = tdElement.find('input[name="' + sourceField + '"]');
		vtigerInstance.autoFillElement = fieldElement;
		var parentModule = jQuery('.editViewContents [name=module]').val();
		if (parentModule != "Events") {
			vtigerInstance.postRefrenceSearch(params, container);
		}
		tdElement.find('input[class="sourceField"]').trigger(Vtiger_Edit_Js.postReferenceQuickCreateSave, {'data': data});
	},

	showFileDetails : function(container) {
		var fileObj = this.getFile();
		if(fileObj) {
			var fileName = fileObj.name;
			var fileSize = fileObj.size;
			fileSize = vtUtils.convertFileSizeInToDisplayFormat(fileSize);
			container.find('.fileDetails').text(fileName + ' (' + fileSize + ')');
			var fileParts = fileName.split('.');
			var fileType = fileParts[fileParts.length - 1];
			container.find('[name="notes_title"]').val(fileName.replace('.'+fileType, ''));
		}
	},

	registerFileDragDropEvent : function(container) {
		var self = this;
		var dragDropElement = container.find("#dragandrophandler");
		dragDropElement.on('dragenter', function(e) {
			e.stopPropagation();
			e.preventDefault();
			jQuery(this).addClass('dragdrop-solid');
		}).on('dragover', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		jQuery(document).on('dragenter', function(e) {
			e.stopPropagation();
			e.preventDefault();
		}).on('dragover', function(e) {
			e.stopPropagation();
			e.preventDefault();
			dragDropElement.removeClass('dragdrop-solid');
		}).on('drop', function(e) {
			e.stopPropagation();
			e.preventDefault();
		});

		dragDropElement.on('drop', function(e) {
			e.preventDefault();

			jQuery(this).removeClass('dragdrop-solid');
			jQuery(this).addClass('dragdrop-dotted');

			var fileObj = e.originalEvent.dataTransfer.files;
			var file = fileObj[0];
			if(self.fileSizeCheck(container, file)) {
				self.setFile(file);
				container.find('input[name="filename"]').val(null);
				self.showFileDetails(container);
			} else {
				app.helper.showAlertNotification({
					'message' : app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
				});
			}
		});
	},

	getMaxUploadLimit : function(container) {
		return container.find('input[name="max_upload_limit"]').val() || 0;
	},

	fileSizeCheck : function(container, file) {
		var maxUploadLimitInBytes = this.getMaxUploadLimit(container);
		return file.size <= maxUploadLimitInBytes;
	},

	registerFileChangeEvent : function(container) {
		var self = this;
		jQuery('input[type="file"]',container).on('change', function(e) {
			var file = e.target.files[0];
			if(self.fileSizeCheck(container, file)) {
				self.setFile(file);
				self.showFileDetails(container);
			} else {
				app.helper.showAlertNotification({
					'message' : app.vtranslate('JS_EXCEEDS_MAX_UPLOAD_SIZE')
				});
			}
		});
	},

	registerFileHandlingEvents : function(container) {
		this.registerFileChangeEvent(container);
		this.registerFileDragDropEvent(container);
		container.find('input[type="file"]').addClass('ignore-validation');
		vtUtils.enableTooltips();
	},

	tabChangeHandler : function(container) {
		jQuery('.tab-item', container).on('click', function() {
			var currentTab = jQuery(this);
			//activate tab
			currentTab.closest('.nav-tabs').find('.tab-item').removeClass('active');
			currentTab.addClass('active');

			//activate tab-pane
			container.find('.tab-content > .tab-pane').removeClass('active in');
			var activatedPane = container.find('.tab-content > '+currentTab.data('tabcontentSelector'));
			activatedPane.addClass('active in');

			//intimate activated tab-pane
			activatedPane.trigger('Documents.Upload.Tab.Active');
		});
	},

	addToHierarchyMap : function(childDir,parentDir,service) {
		Documents_Index_Js.hierarchyMap[service][childDir] = parentDir;
	},

	getParentDirFromHierarchyMap : function(childDir,service) {
		if(childDir == '/') return false;
		return Documents_Index_Js.hierarchyMap[service][childDir];
	},

	updateDirectoryMeta : function(folderId,tab,backwardNavigation) {
		backwardNavigation = (typeof backwardNavigation == "undefined") ? false : true;
		var currentDirElement = jQuery('input[name="currentDir"]',tab);
		var parentDirElement = jQuery('input[name="parentDir"]',tab);
		var service = jQuery('input[name="serviceProvider"]',tab).val();
		var currentDir = currentDirElement.val();
		var parentDir = parentDirElement.val();
		if(!backwardNavigation) {
			this.addToHierarchyMap(folderId,currentDir,service);
			parentDirElement.val(currentDir);
			currentDirElement.val(folderId);
			jQuery('.browseBack',tab).removeAttr('disabled');
			jQuery('.gotoRoot',tab).removeAttr('disabled');
		} else {
			currentDirElement.val(folderId);
			parentDir = this.getParentDirFromHierarchyMap(folderId,service)
			if(!parentDir) {
				jQuery('.browseBack',tab).attr('disabled','disabled');
				jQuery('.gotoRoot',tab).attr('disabled','disabled');
			}
			parentDirElement.val(parentDir);
		}
	},

	registerInlineAuthEvent : function(container) {
		var self = this;
		var authorizeButton = jQuery('.inlineAuth',container);
		if(authorizeButton.length) {
			authorizeButton.on('click', function() {
				var currentTarget = jQuery(this);
				currentTarget.attr('disabled','disabled');
				self._getAuthUrl(currentTarget.data('service'),true).then(function(authUrl) {
					window.resumeAuth = function(status) {
						if(status) {
							app.helper.hideModal();
							var serviceProvider = container.find('input[name="serviceProvider"]').val(),
							parentId, relatedModule;
							if(container.find('.relationDetails').length) {
								var relationDetailsContainer = container.find('.relationDetails');
								parentId = relationDetailsContainer.find('input[name="sourceRecord"]').val();
								relatedModule = relationDetailsContainer.find('input[name="sourceModule"]').val();
							}
							Documents_Index_Js.selectFrom(serviceProvider,parentId,relatedModule);
						}
					};
					window.open(authUrl,'','height=600,width=600,channelmode=1');
				}, function(e) {
					console.log("error : ",e);
				});
			});
		}
	},

	registerExternalStorageTabEvents : function(tab) {
		this.registerSearchFilesEvent(tab);
		this.registerGotoRootFolderEvent(tab);
		this.registerBackBrowseButton(tab);
		this.registerFileSelectionHandler(tab);
		this.registerBrowseFolder(tab);
		this.registerInlineAuthEvent(tab);
	},

	registerPostTabLoadEvents : function(tab) {
		if(tab.data('service') === 'GoogleDrive' || tab.data('service') === 'Dropbox') {
			this.registerExternalStorageTabEvents(tab);
		}
	},

	loadTab : function(tab) {
		var self = this;
		var url = tab.data('url');
		app.helper.showProgress();
		app.request.get({'url':url}).then(function(e,resp) {
			app.helper.hideProgress();
			if(!e) {
				tab.html(resp);
				vtUtils.applyFieldElementsView(tab);
				self.registerPostTabLoadEvents(tab);
			} else {
				console.log("error while loading tab : ",e);
			}
		});
		tab.data('tabLoaded',true);
	},

	registerActiveTabEvent : function(container) {
		var self = this;
		jQuery('.tab-pane',container).on('Documents.Upload.Tab.Active', function() {
			var currentTab = jQuery(this);
			if(!currentTab.data('tabLoaded')) {
				self.loadTab(currentTab);
			}
		});
	},

	registerUploadDocumentEvents : function(container) {
		var self = this;
		container.find('form').vtValidate({
			'submitHandler' : function() {
				self.uploadFileToVtiger(container);
				return false;
			}
		});
		self.registerQuickCreateEvents(container);
		this.registerFileHandlingEvents(container);
	},

	showUploadToVtigerModal : function(parentId,relatedModule) {
		var self = this;
		var url = 'index.php?module=Documents&view=QuickCreateAjax&service=Vtiger&operation=UploadToVtiger&type=I';
		if(typeof parentId !== 'undefined' && typeof relatedModule !== 'undefined') {
			url += '&relationOperation=true&sourceModule='+relatedModule+'&sourceRecord='+parentId;
		}
		var relationField = jQuery('div.related-tabs').find('li').filter('.active').data('relatedfield');
		if (relationField && parentId) {
			url += '&'+relationField+"="+parentId;
		}		
		app.helper.showProgress();
		app.request.get({'url':url}).then(function(e,resp) {
			app.helper.hideProgress();
			if(!e) {
				app.helper.showModal(resp, {
					'cb' : function(modalContainer) {
						self.registerUploadDocumentEvents(modalContainer);
						self.applyScrollToModal(modalContainer);
						self.registerQuickCreateEvents(modalContainer);
					}
				});
			}
		});
	},

	_uploadToExternalStorage : function(container) {
		var self = this;
		if(!self.getFile()) {
			app.helper.showErrorNotification({
				'message' : app.vtranslate('JS_PLEASE_SELECT_A_FILE')
			});
			return;
		}
		var uploadForm = container.find('form[name="uploadToService"]');
		self._upload(uploadForm).then(function(resp) {
			if(resp.uploadFail) {
				app.helper.showErrorNotification({
					'message' : app.vtranslate(resp.msg)
				});
			} else {
				app.helper.showSuccessNotification({
					'message' : app.vtranslate('JS_UPLOAD_SUCCESSFUL')
				});
			}
			app.helper.hideModal();
			self.reloadList();

			if(Documents_Index_Js.referenceCreateMode === true && Documents_Index_Js.referenceFieldName !== '') {
				self.postQuickCreateSave(resp);
			}
		}, function(e) {
			app.helper.showErrorNotification({'message' : app.vtranslate('JS_UPLOAD_FAILED')});
		});
	},

	registerUploadToExternalStorageEvents : function(container) {
		var self = this;
		container.find('form').vtValidate({
			'submitHandler' : function() {
				self._uploadToExternalStorage(container);
				return false;
			}
		});
		this.registerFileHandlingEvents(container);
	},

	applyScrollToModal : function(modalContainer) {
		app.helper.showVerticalScroll(modalContainer.find('.modal-body').css('max-height', '415px'), 
		{'autoHideScrollbar': true});
	},

	uploadTo : function(service,parentId,relatedModule) {
		this.setFile(false);
		this.showUploadToVtigerModal(service,parentId,relatedModule);
	},

	createExternalDocument : function(container) {
		var self = this;
		var selection = jQuery('.selectList', container).find('.selectedFile');
		if(!selection.length) {
			app.helper.showAlertNotification({
				'message' : app.vtranslate('JS_PLEASE_SELECT_A_FILE')
			});
			return;
		}

		var service = jQuery('input[name="serviceProvider"]', container).val();

		var assignedUserId = jQuery('select[name="assigned_user_id"] option:selected', container).val();
		var documentSource = service;
		if(service === 'GoogleDrive') {
			documentSource = 'Google Drive';
		}

		var params = {
			module : 'Documents',
			action : 'SaveAjax',
			service : service,
			notes_title : selection.data('title'),
			filename : selection.data('link'),
			filelocationtype : 'E',
			assigned_user_id : assignedUserId,
			document_source : documentSource,
			externalFileId : selection.data('fileid'),
			folderid : jQuery('input[name="vtigerFolderId"]',container).val()
		};

		if(service === 'Dropbox'){
			params.createDirectLink = "yes";
		}

		var relationDetailsContainer = container.find('.relationDetails');
		if(relationDetailsContainer.length) {
			var relationOperationElement = relationDetailsContainer.find('input[name="relationOperation"]');
			var relationOperator = relationOperationElement.val();
			var sourceModule = relationDetailsContainer.find('input[name="sourceModule"]').val();
			var sourceRecord = relationDetailsContainer.find('input[name="sourceRecord"]').val();
			var relationFieldName = relationDetailsContainer.find('input[name="relationFieldName"]').val();
			params.relationOperation = relationOperator;
			params.sourceModule = sourceModule;
			params.sourceRecord = sourceRecord;
			if(relationFieldName){
				params[relationFieldName] = sourceRecord;
			}
		}

		app.helper.showProgress();
		app.request.post({'data':params}).then(function(e,res) {
			app.helper.hideProgress();
			if(!e) {
				app.helper.showSuccessNotification({
					'message' : app.vtranslate('JS_DOCUMENT_CREATED')
				});
				app.helper.hideModal();
				self.reloadList();

				//reference create handling
				if (Documents_Index_Js.referenceCreateMode === true && Documents_Index_Js.referenceFieldName !== '') {
					self.postQuickCreateSave(res);
				}
			} else {
				app.event.trigger('post.save.failed', e);
			}
		});
	},

	registerLinkExternalDocumentEvent : function(container) {
		var self = this;
		jQuery('#js-select-document',container).on('click',function() {
			self.createExternalDocument(container.find('form'));
		});
	},

	updateFilesList : function(files, container) {
		var tableContainer = container.find('.selectList').find('tbody'),
			listItem = '';
		tableContainer.empty();
		if(typeof files === 'undefined' || !files.length) {
			listItem = '<div style="padding:20px">'+
							'<span class="span">'+
								'<i class="fa fa-info-circle"></i>'+
									app.vtranslate('JS_NO_RESULTS_FOUND')+
							'</span>'+
						'</div>';
			tableContainer.append(listItem);
			return;
		} else {
			for(var i=0;i<files.length;i++) {
				if(files[i].is_dir) {
					listItem = "<tr class='listViewEntries folder' data-fileid='"+files[i].id+"' data-title='"+files[i].title+"' data-link='"+files[i].alternateLink+"'>"+
									"<td class='listViewEntryValue medium fileTitleData' nowrap=''>"+
										"<i class='fa fa-folder'></i>&nbsp;<a>"+files[i].title+"</a>"+
									"</td>";
					if(files[i].owner_name) {
						listItem += "<td class='listViewEntryValue medium fileOwnerData' nowrap=''>"+
										"<a>" + files[i].owner_name + "</a>"+
									"</td>";
					}
					if(files[i].fileSize && !files[i].is_dir) {
						var fileSize = files[i].fileSize;
						if(container.find('input[name="serviceProvider"]').val() === 'GoogleDrive') {
							fileSize = vtUtils.convertFileSizeInToDisplayFormat(fileSize);
						}
						listItem += "<td class='listViewEntryValue medium fileSizeData' nowrap=''>"+
										"<a>" + fileSize + "</a>"+
									"</td>";
					}
					listItem += "</tr>";
				} else {
					listItem = "<tr class='listViewEntries file' data-fileid='"+files[i].id+"' data-title='"+files[i].title+"' data-link='"+files[i].alternateLink+"'>"+
								"<td class='listViewEntryValue medium fileTitleData' nowrap=''>"+
									"<i class='fa fa-file'></i>&nbsp;<a>"+files[i].title+"</a>"+
								"</td>";
					if(files[i].owner_name) {
						listItem += "<td class='listViewEntryValue medium fileOwnerData' nowrap=''>"+
										"<a>" + files[i].owner_name + "</a>"+
									"</td>";
					}
					if(files[i].fileSize) {
						var fileSize = files[i].fileSize;
						if(container.find('input[name="serviceProvider"]').val() === 'GoogleDrive') {
							fileSize = vtUtils.convertFileSizeInToDisplayFormat(fileSize);
						}
						listItem += "<td class='listViewEntryValue medium fileSizeData' nowrap=''>"+
									"<a>" + fileSize + "</a>"+
								"</td>";
					}
					listItem += "</tr>";
				}
				tableContainer.append(listItem);
			}
		}
		this.registerFileSelectionHandler(container);
		this.registerBrowseFolder(container);
	},

	showEmptyDirectoryMessage : function(tab) {
		var container = jQuery('.selectList',tab).find('tbody');
		container.empty();
		var content = '<div style="padding:20px">\n\
						<span class="span">\n\
							<i class="fa fa-info-circle"></i>\n\
							'+app.vtranslate('JS_DIRECTORY_IS_EMPTY')+
						'</span>\n\
					</div>';
		container.append(content);
	},

	openFolder : function(folderId,container,backwardNavigation) {
		var self = this;
		var service = jQuery('input[name="serviceProvider"]',container).val();

		var params = {
			 'module' : 'Documents',
			 'action' : 'ServiceProvidersAjax',
			 'mode' : 'getFilesForDirectory',
			 'service' : service,
			 'folderId' : folderId
		 };
		 app.helper.showProgress(app.vtranslate('JS_PLEASE_WAIT'));
		 app.request.post({'data':params}).then(function(e,res) {
			 app.helper.hideProgress();
			 if(!e) {
				 if(res.is_directory_empty) {
					 self.showEmptyDirectoryMessage(container);
				 } else {
					 self.updateFilesList(res.filesList,container);
				 }
				 self.updateDirectoryMeta(folderId,container,backwardNavigation);
				 self.registerBackBrowseButton(container);
			 }
		 });
	},

	registerBrowseFolder : function(container) {
		var self = this;
		jQuery('.folder', container).on('click', function() {
			jQuery('.folder',container).off('click');
			jQuery('.browseBack',container).off('click');
			var folderId = jQuery(this).data('fileid');
			self.openFolder(folderId,container);
		});
	},

	registerFileSelectionHandler : function(container) {
		jQuery('.file', container).on('click',function() {
			if(typeof prevSelection !== 'undefined') {
				prevSelection.removeClass('selectedFile');
			}
			jQuery(this).addClass('selectedFile');
			prevSelection = jQuery(this);
		});
	},

	registerBackBrowseButton : function(container) {
		var thisInstance = this;
		var currentDirElement = jQuery('input[name="currentDir"]',container);
		jQuery('.browseBack',container).on('click',function() {
			var service = jQuery('input[name="serviceProvider"]',container).val();
			var parentDir = thisInstance.getParentDirFromHierarchyMap(currentDirElement.val(),service);
			if(parentDir) {
				jQuery('.browseBack',container).off('click');
				jQuery('.folder',container).off('click');
				thisInstance.openFolder(parentDir,container,true);
			}
		});
	},

	registerGotoRootFolderEvent : function(container) {
		var self = this;
		jQuery('.gotoRoot', container).on('click', function() {
			jQuery(this).attr('disabled','disabled');
			jQuery('.browseBack',container).attr('disabled','disabled');
			var rootMetaElement = jQuery('input[name="rootDirContents"]',container);
			var filesList = rootMetaElement.val();
			var rootDirId = rootMetaElement.attr('data-rootDirId');
			filesList = JSON.parse(filesList);
			self.updateFilesList(filesList,container);
			jQuery('input[name="currentDir"]',container).val(rootDirId);
			jQuery('input[name="parentDir"]',container).val('');
		});
	},

	registerSearchFilesEvent : function(container) {
		var self = this;
		jQuery('input[name="searchFiles"]', container).on('change', function() {
			var searchKey = jQuery(this).val();
			searchKey = jQuery.trim(searchKey);
			if(searchKey.length === 0) {
				jQuery(this).val('');
				return;
			}

			var params = {
				'module' : 'Documents',
				'action' : 'ServiceProvidersAjax',
				'mode' : 'search',
				'searchKey' : searchKey,
				'service' : jQuery('input[name="serviceProvider"]',container).val()
			};
			app.helper.showProgress(app.vtranslate('JS_PLEASE_WAIT'));
			app.request.post({'data':params}).then(function(e,res) {
				app.helper.hideProgress();
				if(!e) {
					self.updateFilesList(res,container);
					jQuery('.browseBack',container).attr('disabled','disabled');
					jQuery('.gotoRoot',container).removeAttr('disabled');
				}
			});
		});
	},

	registerSelectFromExternalStorageEvents : function(modalContainer) {
		modalContainer.find('form').vtValidate({
			'submitHandler' : function() {
				return false;
			}
		});

		this.registerSearchFilesEvent(modalContainer);
		this.registerGotoRootFolderEvent(modalContainer);
		this.registerBackBrowseButton(modalContainer);
		this.registerFileSelectionHandler(modalContainer);
		this.registerBrowseFolder(modalContainer);
		this.registerInlineAuthEvent(modalContainer);
		this.registerLinkExternalDocumentEvent(modalContainer);

		app.helper.showVerticalScroll(modalContainer.find('.selectList'), 
		{'autoHideScrollbar' : true, 'setHeight' : 270});
	},

	showSelectFromExternalStorageModal : function(service,parentId,relatedModule) {
		var self = this;
		var url = 'index.php?module=Documents&view=ExternalStorage&operation=SelectFrom'+service;
		if(typeof parentId !== 'undefined' && typeof relatedModule !== 'undefined') {
			url += '&relationOperation=true&sourceModule='+relatedModule+'&sourceRecord='+parentId;
		}
		var relationField = jQuery('div.related-tabs').find('li').filter('.active').data('relatedfield');
		if (relationField && parentId) {
			url += '&'+relationField+"="+parentId;
		}
		app.helper.showProgress();
		app.request.get({'url':url}).then(function(e,resp) {
			app.helper.hideProgress();
			if(!e) {
				app.helper.showModal(resp, {
					'cb' : function(modalContainer) {
						self.registerSelectFromExternalStorageEvents(modalContainer);
					}
				});
			}
		});
	},

	selectFrom : function(service,parentId,relatedModule) {
		this.showSelectFromExternalStorageModal(service,parentId,relatedModule);
	},

	_getAuthUrl : function(serviceName,inline) {
		var aDeferred = jQuery.Deferred();
		if(typeof inline === 'undefined') {
			inline = false;
		}
		var params = {
			'module' : 'Documents',
			'action' : 'ServiceProvidersAjax',
			'mode' : 'getOauthUrl',
			'service' : serviceName,
			'inline' : inline
		};
		app.helper.showProgress();
		app.request.post({'data':params}).then(function(e,resp) {
			app.helper.hideProgress();
			if(!e) {
				if(resp.hasOwnProperty('authUrl')) {
					aDeferred.resolve(resp.authUrl);
				} else if(resp.hasOwnProperty('postAuthorization')) {
					aDeferred.reject({postAuthorization:resp.postAuthorization});
				}
			} else {
				aDeferred.reject(e);
			}
		});
		return aDeferred.promise();
	},

	authorize : function(serviceName,parentId,relatedModule,mode,referenceFieldName) {
		if(typeof mode === 'undefined') {
			mode = 'upload';
		}
		this._getAuthUrl(serviceName,true).then(function(authUrl) {
			window.resumeAuth = function(status) {
				if(status) {
					var actionHref = 'javascript:Documents_Index_Js.uploadTo(\''+serviceName+'\'';
					if(typeof parentId !== 'undefined' && typeof relatedModule !== 'undefined') {
						actionHref += ','+parentId+',\''+relatedModule+'\'';
					}
					actionHref += ')';
					jQuery('#'+serviceName+'Action').find('a').attr('href',actionHref);
					if(mode === 'select') {
						Documents_Index_Js.selectFrom(serviceName,parentId,relatedModule,referenceFieldName);
					} else {
						Documents_Index_Js.uploadTo(serviceName,parentId,relatedModule,referenceFieldName);
					}
					var revokeSelector = 'Documents_listview_customsettingaction_LBL_REVOKE_ACCESS_TO_DRIVE';
					if(serviceName === 'Dropbox') {
						revokeSelector = 'Documents_listview_customsettingaction_LBL_REVOKE_ACCESS_TO_DROPBOX';
					}
					jQuery('#'+revokeSelector).removeClass('hide');
					jQuery('.settingsIcon').parent('li').removeClass('hide');
				}
			};
			window.open(authUrl,'','height=600,width=600,channelmode=1');
		}, function(e) {
			if(e.hasOwnProperty('postAuthorization')) {
				if(e.postAuthorization) {
					if(mode === 'select') {
						Documents_Index_Js.selectFrom(serviceName,parentId,relatedModule,referenceFieldName);
					} else {
						Documents_Index_Js.uploadTo(serviceName,parentId,relatedModule,referenceFieldName);
					}
				}
			}
		});
	},

	_createDocument : function(form) {
		var self = this;
		var noteContentElement = form.find('#Documents_editView_fieldName_notecontent_popup');
		if(noteContentElement.length) {
			var noteContent = CKEDITOR.instances.Documents_editView_fieldName_notecontent_popup.getData()
			noteContentElement.val(noteContent);
		}
		var formData = form.serialize();
		app.helper.showProgress();
		app.request.post({'data':formData}).then(function(e,res) {
			app.helper.hideProgress();
			if (e === null) {
				app.helper.hideModal();
				app.helper.showSuccessNotification({
					'message' : app.vtranslate('JS_DOCUMENT_CREATED')
				});
				self.reloadList();
				var folderid = form.find('[name="folderid"]').val();
				app.event.trigger('post.documents.save', {'folderid' : folderid});

				//reference create handling
				if (Documents_Index_Js.referenceCreateMode === true && Documents_Index_Js.referenceFieldName !== '') {
					self.postQuickCreateSave(res);
				}
			} else {
				app.event.trigger('post.save.failed', e);
			}
		});
	},

	registerCreateDocumentEvent : function(container) {
		var self = this;
		jQuery('#js-create-document', container).on('click', function() {
			var form = container.find('form'); 
			if(form.valid()) {
				self._createDocument(form);
			}
		});
	},

	applyEditor : function(element) {
		var cke = new Vtiger_CkEditor_Js();
		cke.loadCkEditor(element, {'height' : 200});
	},

	registerCreateDocumentModalEvents : function(container) {
		container.find('form').vtValidate();
		if(container.find('input[name="type"]').val() === 'W') {
			container.find('.modelContainer').css('width','750px');
			//change id of text area to workaround multiple instances of ckeditor on same element
			this.applyEditor(
				container.find('#Documents_editView_fieldName_notecontent')
				.attr('id','Documents_editView_fieldName_notecontent_popup')
			);
		}
		this.registerCreateDocumentEvent(container);
	},

	showCreateDocumentModal : function(type,parentId,relatedModule) {
		var self = this;
		var url = 'index.php?module=Documents&view=QuickCreateAjax&operation=CreateDocument&type='+type;
		if(typeof parentId !== 'undefined' && typeof relatedModule !== 'undefined') {
			url += '&relationOperation=true&sourceModule='+relatedModule+'&sourceRecord='+parentId;
		}
		var relationField = jQuery('div.related-tabs').find('li').filter('.active').data('relatedfield');
		if (relationField && parentId) {
			url += '&'+relationField+"="+parentId;
		}
		app.helper.showProgress();
		app.request.get({'url':url}).then(function(e,resp) {
			app.helper.hideProgress();
			if(!e) {
				app.helper.showModal(resp, {
					'cb' : function(modalContainer) {
						self.registerCreateDocumentModalEvents(modalContainer);
						self.registerQuickCreateEvents(modalContainer);
						self.applyScrollToModal(modalContainer);
					}
				});
			}
		});
	},

	createDocument : function(type,parentId,relatedModule) {
		this.showCreateDocumentModal(type,parentId,relatedModule);
	},

	registerQuickCreateEvents : function(container) {
		var vtigerInstance = Vtiger_Index_Js.getInstance();
		vtigerInstance.registerReferenceCreate(container);
		vtigerInstance.registerPostReferenceEvent(container);
		vtigerInstance.referenceModulePopupRegisterEvent(container);
		vtigerInstance.registerClearReferenceSelectionEvent(container);
		vtigerInstance.registerAutoCompleteFields(container);
		app.helper.registerModalDismissWithoutSubmit(container.find('form'));
		var moduleInstance = Vtiger_Edit_Js.getInstanceByModuleName('Documents');
		moduleInstance.registerEventForPicklistDependencySetup(container);

		app.event.on('post.documents.save', function(event, data){
			var relatedTabs = jQuery('div.related-tabs');
			if(relatedTabs.length > 0){
				var tabElement = jQuery('div.related-tabs').find('li.active');
				var relatedModuleName = jQuery('.relatedModuleName').val();
				var relatedInstance = new Vtiger_RelatedList_Js(app.getRecordId(), app.getModuleName(), tabElement, relatedModuleName);
				var relatedTab = relatedInstance.selectedRelatedTabElement;
				relatedInstance.updateRelatedRecordsCount(relatedTab.data('relation-id'));
			}
		});
	}

});
