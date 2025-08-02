/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Settings_MailConverter_Edit_Js */
Settings_Vtiger_Index_Js('Settings_MailConverter_Edit_Js', {
    firstStep: function (e) {
        var form = jQuery('#mailBoxEditView');
        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                form.find('[name="saveButton"]').attr('disabled', 'disabled');
                Settings_MailConverter_Edit_Js.saveMailBox(form);
            }
        }
        form.vtValidate(params);

        form.submit(function (e) {
            e.preventDefault();
        });


    },

    saveMailBox: function (form) {
        var params = form.serializeFormData();
        params.scannername = jQuery('input[name="scannername"]').val();
        params.module = app.getModuleName();
        params.parent = app.getParentModuleName();
        params.action = 'SaveMailBox';

        app.helper.showProgress();
        app.request.post({'data': params}).then(function (err, data) {
            app.helper.hideProgress();
            if (typeof data != 'undefined') {
                var create = jQuery("#create").val();
                window.location.href = 'index.php?module=' + app.getModuleName() + '&parent=' + app.getParentModuleName() + '&view=Edit&mode=step2&create=' + create + '&record=' + data.id;
            } else {
                app.helper.showErrorNotification({'message': err['message']});
            }
        });
    },

    secondStep: function (e) {
        var form = jQuery('#mailBoxEditView');
        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                var checked = jQuery('input[type=checkbox][name=folders]:checked').length;
                if (checked < 1) {
                    app.helper.showAlertNotification({'message': app.vtranslate('JS_SELECT_ONE_FOLDER')});
                    return false;
                } else {
                    form.find('[name="saveButton"]').attr('disabled', 'disabled');
                    var selectedFolders = jQuery('input[name=folders]:checked').map(function () {
                        return jQuery(this).val();
                    }).get();
                    Settings_MailConverter_Edit_Js.saveFolders(selectedFolders);
                }
            }
        }
        form.vtValidate(params);

        form.submit(function (e) {
            e.preventDefault();
        });
    },

    saveFolders: function (selectedFolders) {
        var create = jQuery('#create').val();
        var id = jQuery('#recordId').val();
        var url = 'module=' + app.getModuleName() + '&parent=' + app.getParentModuleName() + '&action=SaveFolders&folders=' + selectedFolders + '&create=' + create + '&record=' + id;

        app.helper.showProgress();
        app.request.post({'url': url}).then(function (err, data) {
            app.helper.hideProgress();
            if (typeof data != 'undefined') {
                var fallbackUrl = 'index.php?module=' + app.getModuleName() + '&parent=' + app.getParentModuleName() + '&view=List&record=' + data.id;
                if (create == 'new') {
                    fallbackUrl = 'index.php?module=' + app.getModuleName() + '&parent=' + app.getParentModuleName() + '&view=Edit&mode=step3&create=' + create + '&record=' + data.id;
                }
                window.location.href = fallbackUrl;
            } else {
                app.helper.showErrorNotification({'message': err['message']});
            }
        });
    },

    thirdStep: function (e) {
        var form = jQuery('#ruleSave');
        var params = {
            submitHandler: function (form) {
                var form = jQuery(form);
                form.find('[name="saveButton"]').attr('disabled', 'disabled');
                Settings_MailConverter_Edit_Js.saveRule(form);
            }
        }
        form.vtValidate(params);

        form.submit(function (e) {
            e.preventDefault();
        });
    },

    saveRule: function (form) {
        app.helper.showProgress();
        var params = form.serializeFormData();
        params.record = '';
        app.request.post({'data': params}).then(function (err, data) {
            app.helper.hideProgress();
            if (typeof data != 'undefined') {
                window.location.href = 'index.php?module=' + app.getModuleName() + '&parent=' + app.getParentModuleName() + '&view=List&record=' + data.scannerId;
            } else {
                app.helper.showErrorNotification({'message': err['message']});
            }
        });
    },

    /*
     * Function to activate the header based on the class
     * @params class name
     */
    activateHeader: function () {
        var step = jQuery('#step').val();
        jQuery('#' + step).addClass('active');
    }

}, {
    registerEvents: function () {
        this._super();
        Settings_MailConverter_Edit_Js.firstStep();
        Settings_MailConverter_Edit_Js.activateHeader();

        this.registerUpdateVisibility();
        this.registerClientTokenActions();
    },
    registerClientTokenActions() {
        const self = this,
            form = self.getForm(),
            tokenElement = form.find('[name="client_token"]'),
            accessTokenElement = form.find('[name="client_access_token"]');

        form.on('click', '.retrieveToken', function () {
            let formData = form.serializeFormData();

            app.getOAuth2Url(formData['server'], formData['client_id'], formData['client_secret'], formData['client_token']).then(function (error, data) {
                if (!error) {
                    if (data['url']) {
                        tokenElement.val('');
                        accessTokenElement.val('');

                        window.open(data['url'], '_blank')
                    }

                    if (data['message']) {
                        app.helper.showErrorNotification({message: data['message']});
                    }
                }
            });
        });

        form.on('click', '.refreshToken', function () {
            self.loadAccessToken(form);
        });
    },
    loadAccessToken() {
        const self = this,
            form = self.getForm(),
            clientId = form.find('[name="client_id"]').val(),
            tokenElement = form.find('[name="client_token"]'),
            accessTokenElement = form.find('[name="client_access_token"]'),
            token = tokenElement.val();

        if (!clientId || token) {
            return false;
        }

        app.getOAuth2Tokens(clientId).then(function (error, data) {
            if (!error) {
                tokenElement.val(data['token']);
                accessTokenElement.val(data['access_token']);
            }
        })
    },
    registerUpdateVisibility() {
        const self = this;

        self.updateFieldsVisibility();

        self.getForm().on('focusout', '[name="server"]', function () {
            self.updateFieldsVisibility();
        });
    },
    updateFieldsVisibility() {
        let self = this,
            form = self.getForm(),
            hideFields = ['mail_proxy', 'client_id', 'client_secret', 'client_token', 'client_access_token'];

        form.find('.fieldContainer').removeClass('hide');

        if (0 <= form.find('[name=server]').val().indexOf('imap.gmail.com')) {
            hideFields = ['password'];
        }

        $.each(hideFields, function (index, fieldName) {
            form.find('[name="' + fieldName + '"]').parents('.fieldContainer').addClass('hide');
        })
    },
    getForm() {
        return jQuery('#mailBoxEditView');
    }
});