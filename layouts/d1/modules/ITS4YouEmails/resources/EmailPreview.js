/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

jQuery.Class('ITS4YouEmails_EmailPreview_Js',{},{
	
	/**
	 * Function to get email actions params
	 */
	getEmailActionsParams : function(mode){
		var parentRecord = new Array();
		var parentRecordId = jQuery('[name="parentRecord"]').val();
		parentRecord.push(parentRecordId);
		var recordId = jQuery('[name="recordId"]').val();
		var params = {};
		params['module'] = 'ITS4YouEmails';
		params['view'] = "ComposeEmail";
		if(mode != "emailForward"){
			params['selected_ids'] = parentRecord;
		}
		params['record'] = recordId;
		params['mode'] = mode;
		params['parentId'] = parentRecordId;
		params['relatedLoad'] = true;
		
		return params;
	},
	
	/**
	 * Function to register events for action buttons of email preview
	 */
	registerEventsForActionButtons : function(){
		var thisInstance = this;
		app.helper.showVerticalScroll(jQuery('#toAddressesDropdown'));
        jQuery('[name="previewReplyAll"], [name="previewReply"], [name="previewForward"], [name="previewEdit"]').on('click',function(e){
            let module = 'ITS4YouEmails';
			app.helper.checkServerConfig(module).then(function(data){
				if(data === true){
					var mode = jQuery(e.currentTarget).data('mode');
					var params = thisInstance.getEmailActionsParams(mode);
					var container = jQuery(e.currentTarget).closest('.modal');
					container.one('hidden.bs.modal',function()
					{	
						app.helper.hidePopup();
						app.helper.showProgress();
						app.request.post({data:params}).then(function(err,data){
							app.helper.hideProgress();
							if(err === null){
								app.helper.showModal(data);
								var emailEditInstance = new ITS4YouEmails_MassEdit_Js();
								emailEditInstance.registerEvents();
							}
						});
						
					});
					container.modal('hide');
					
				} else {
					app.helper.showErrorMessage(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
				}
			});
        });
        jQuery('[name="previewPrint"]').on('click',function(e){
            app.helper.hideModal();
            var mode = jQuery(e.currentTarget).data('mode');
            var params = thisInstance.getEmailActionsParams(mode);
            var urlString = (typeof params == 'string') ? params : jQuery.param(params);
            var url = 'index.php?'+urlString;
            window.open(url,'_blank');
        });
	},
	
	registerEvents : function(){
        var thisInstance = this;
        app.event.on('post.EmailPreview.load',function(event,args){
            thisInstance.registerEventsForActionButtons();
        });
	}
});