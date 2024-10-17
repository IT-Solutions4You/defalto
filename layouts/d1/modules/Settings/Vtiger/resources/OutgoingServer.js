/**
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (c) vtiger.
* Portions created by IT-Solutions4You (ITS4You) are Copyright (c) IT-Solutions4You s.r.o
* All Rights Reserved.
*/

Vtiger.Class("Settings_Vtiger_OutgoingServer_Js",{},{
	
    init : function() {
       this.addComponents();
    },
   
    addComponents : function (){
        this.addModuleSpecificComponent('Index',app.module(), app.getParentModuleName());
    },

	/*
	 * function to Save the Outgoing Server Details
	 */
	saveOutgoingDetails: function (form) {
		let self = this,
			aDeferred = jQuery.Deferred(),
			data = form.serializeFormData(),
			params = {
				'module': app.getModuleName(),
				'parent': app.getParentModuleName(),
				'action': 'OutgoingServerSaveAjax'
			};

		jQuery.extend(params, data);

		app.request.post({data: params}).then(function (error, data) {
			app.helper.showProgress();
			if (!error) {
				let detailUrl = form.data('detailUrl');

				self.loadContents(detailUrl).then(function (data) {
					self.setSettingsContainer(data)
					self.registerDetailViewEvents();
					app.helper.hideProgress();
				});

				aDeferred.resolve(data);
			} else {
				app.helper.hideProgress();
				jQuery('.errorMessage', form).removeClass('hide');
				aDeferred.reject();
			}
		});

		return aDeferred.promise();
	},
	
	/*
	 * function to load the contents from the url through pjax
	 */
    registerFilters: function(){
        var filters = jQuery('#outgoingServer');
        filters.find('.cursorPointer').on('click',function(e){
            var currentTarget = jQuery(e.currentTarget);
            if(currentTarget.attr('data-toggletext')=== 'Show more') {
                currentTarget.attr('data-toggletext','Show less');
                currentTarget.html('Show less');
            }else{
                currentTarget.attr('data-toggletext','Show more');
                currentTarget.html('Show more');
            }
        });
    },


	loadContents: function (url) {
		let aDeferred = jQuery.Deferred(),
			self = this;

		app.request.post({data: app.convertUrlToDataParams(url)}).then(function (error, data) {
			if (!error) {
				self.setSettingsContainer(data)
				aDeferred.resolve(data);
			} else {
				aDeferred.reject();
			}
		});

		return aDeferred.promise();
	},
	
	/*
	 * function to register the events in editView
	 */
	registerEditViewEvents : function(e) {
		var thisInstance = this;
		var form = jQuery('#OutgoingServerForm');
		var resetButton = jQuery('.resetButton', form);
		var cancelLink = jQuery('.cancelLink', form);
        
		//register validation engine
		var params = {
            submitHandler : function(form) {
                  app.helper.showProgress();
                var form = jQuery(form);
				thisInstance.saveOutgoingDetails(form);
            }
		};
		if (form.length) {
        	form.vtValidate(params);
		 	form.on('submit', function(e){
            	e.preventDefault();
            	return false;
        	});
		}
		
		//register click event for resetToDefault Button
		resetButton.click(function(e) {
			jQuery('[name="default"]', form).val('true');
			var message = app.vtranslate('JS_CONFIRM_DEFAULT_SETTINGS');
			app.helper.showConfirmationBox({'message' : message}).then(
				function(e) {
                   app.helper.showProgress();
					thisInstance.saveOutgoingDetails(form);
				}
			);
		});
		
		//register click event for cancelLink
		cancelLink.click(function(e) {
			var OutgoingServerDetailUrl = form.data('detailUrl');
			thisInstance.loadContents(OutgoingServerDetailUrl).then(
				function(data) {
                     jQuery('.editViewPageDiv').html(data);
					//after loading contents, register the events
					thisInstance.registerDetailViewEvents();
				}
			);
		});
	},
	
	/*
	 * function to register the events in DetailView
	 */
	registerDetailViewEvents : function() {
		let self = this,
			container = self.getDetailContainer();

		//register click event for edit button
		container.on('click', '.editButton', function(e) {
			app.helper.showProgress();
			let url = $(this).data('url');

			self.loadContents(url).then(function(data) {
				app.helper.hideProgress();
				self.setSettingsContainer(data);
				//after load the contents register the edit view events
				self.registerEditViewEvents();
				self.registerOnChangeEventOfserverType();
				self.updateFieldsVisibility();
				self.retrieveTokenButton();
				vtUtils.showSelect2ElementView(jQuery('select[name="serverType"]'));
			});
		});

		self.updateFieldsVisibility();
	},
	getSettingsContainer() {
		return $('.settingsPageDiv');
	},
	setSettingsContainer(value) {
		this.getSettingsContainer().html(value);
	},
	registerOnChangeEventOfserverType: function (e) {
		let self = this,
			form = self.getForm();

		form.find('[name="serverType"]').on('change', function (e) {

			let servertypevalue = form.find('[name="serverType"]').val();
			form.find('[name="server"]').val(servertypevalue);
			form.find('[name="server_username"]').val('');
			form.find('[name="server_password"]').val('');
			form.find('[name="from_email_field"]').val('');
			form.find('[name="client_id"]').val('');
			form.find('[name="client_secret"]').val('');
			form.find('[name="client_token"]').val('');

			self.updateFieldsVisibility();
		});
	},
	getDetailContainer() {
		return $('#OutgoingServerDetails');
	},
	getEditContainer() {
		return $('#EditViewOutgoing');
	},
	updateFieldsVisibility() {
		let self = this,
			container = self.getEditContainer().length ? self.getEditContainer() : self.getDetailContainer(),
			serverType = container.find('[name="serverType"]'),
			loginOAuth = container.find('.oauthLogin'),
			loginServer = container.find('.serverLogin');

		loginOAuth.addClass('hide');
		loginServer.addClass('hide');

		if ('ssl://smtp.gmail.com:465' === serverType.val()) {
			loginOAuth.removeClass('hide');
		} else {
			loginServer.removeClass('hide');
		}
	},
	registerEvents: function () {
		let self = this;
		self.registerEditViewEvents();
		self.registerOnChangeEventOfserverType();
		self.registerDetailViewEvents();
		self.registerFilters();
		self.retrieveTokenButton();
		self.updateFieldsVisibility();
	},
	getForm() {
		return $('#OutgoingServerForm')
	},
	retrieveTokenButton() {
		const self = this,
			form = self.getForm();

		form.on('click', '.retrieveToken', function () {
			let params = form.serializeFormData()

			app.getOAuth2Url(params['server'], params['client_id'], params['client_secret']).then(function (error, data) {
				if (!error) {
					if(data['url']) {
						self.getTokenElement().val('');

						window.open(data['url'], '_blank')
					}

					if(data['message']) {
						app.helper.showErrorNotification({message: data['message']});
					}
				}
			});
		});

		form.on('click', '.refreshToken', function () {
			self.loadToken();
		});
	},
	getTokenElement() {
		return this.getForm().find('[name="client_token"]');
	},
	loadToken() {
		const self = this,
			clientId = self.getForm().find('[name="client_id"]').val(),
			token = self.getTokenElement().val();

		if (!clientId || token) {
			return false;
		}

		app.getOAuth2Tokens(clientId).then(function (error, data) {
			if (!error && data['token']) {
				self.getForm().find('[name="client_token"]').val(data['token']);
			}
		});
	},
});


Settings_Vtiger_OutgoingServer_Js("Settings_Vtiger_OutgoingServerEdit_Js",{},{});

Settings_Vtiger_OutgoingServer_Js("Settings_Vtiger_OutgoingServerDetail_Js",{},{});
