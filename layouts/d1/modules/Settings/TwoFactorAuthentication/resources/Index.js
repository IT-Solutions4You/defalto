/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Settings_TwoFactorAuthentication_Index_Js */
Settings_Vtiger_Index_Js('Settings_TwoFactorAuthentication_Index_Js', {}, {
    getContainer: function () {
        return jQuery('.two-factor-authentication-settings');
    },

    saveStatus: function (toggle) {
        const self = this,
            state = toggle.is(':checked'),
            params = {
                module: 'TwoFactorAuthentication',
                parent: 'Settings',
                action: 'Activate',
                mode: state ? 'activate' : 'deactivate'
            },
            message = state ? toggle.data('activate-confirm') : toggle.data('deactivate-confirm');

        app.helper.showConfirmationBox({message: message}).then(function () {
            app.request.post({data: params}).then(function (error) {
                if (error === null) {
                    //window.location.reload();
                    app.helper.showSuccessNotification({message: app.vtranslate('JS_SAVED')});
                    return;
                }

                app.helper.showErrorNotification({message: error});
                self.revertCheckbox(toggle, !state);
            });
        }, function () {
            self.revertCheckbox(toggle, !state);
        });
    },

    revertCheckbox: function (checkbox, state) {
        checkbox.prop('checked', state);
    },

    saveExempt: function (checkbox) {
        const self = this,
            params = {
                module: 'TwoFactorAuthentication',
                parent: 'Settings',
                action: 'Users',
                mode: 'setExempt',
                userid: checkbox.data('userid'),
                exempt: checkbox.is(':checked') ? 0 : 1
            };

        app.request.post({data: params}).then(function (error) {
            if (error === null) {
                app.helper.showSuccessNotification({message: app.vtranslate('JS_SAVED')});
                return;
            }

            app.helper.showErrorNotification({message: error});
            self.revertCheckbox(checkbox, !checkbox.is(':checked'));
        });
    },

    saveMethods: function (changed) {
        const container = this.getContainer(),
            email = container.find('.two-factor-authentication-method[data-method="email"]'),
            totp = container.find('.two-factor-authentication-method[data-method="totp"]');

        if (!email.is(':checked') && !totp.is(':checked')) {
            changed.prop('checked', true);
            app.helper.showErrorNotification({message: app.vtranslate('JS_AT_LEAST_ONE_METHOD')});
            return;
        }

        const params = {
            module: 'TwoFactorAuthentication',
            parent: 'Settings',
            action: 'Methods',
            mode: 'save',
            allow_email: email.is(':checked') ? 1 : 0,
            allow_totp: totp.is(':checked') ? 1 : 0
        };

        app.request.post({data: params}).then(function (error) {
            if (error === null) {
                app.helper.showSuccessNotification({message: app.vtranslate('JS_METHODS_SAVED')});
                return;
            }

            app.helper.showErrorNotification({message: error});
            changed.prop('checked', !changed.is(':checked'));
        });
    },

    resetUser: function (button) {
        const params = {
            module: 'TwoFactorAuthentication',
            parent: 'Settings',
            action: 'Users',
            mode: 'reset',
            userid: button.data('record')
        };

        app.helper.showConfirmationBox({message: button.data('confirm')}).then(function () {
            app.request.post({data: params}).then(function (error) {
                if (error === null) {
                    window.location.reload();
                    return;
                }

                app.helper.showErrorNotification({message: error});
            });
        });
    },

    editMethod: function (button) {
        const cell = button.closest('.two-factor-authentication-method-cell'),
            select = cell.find('.two-factor-authentication-method-select');

        select.data('saving', false);
        cell.find('.two-factor-authentication-method-text, .two-factor-authentication-method-edit').addClass('d-none');
        select.removeClass('d-none').trigger('focus');
    },

    restoreMethod: function (select) {
        select.addClass('d-none');
        select.closest('.two-factor-authentication-method-cell')
            .find('.two-factor-authentication-method-text, .two-factor-authentication-method-edit')
            .removeClass('d-none');
    },

    saveUserMethod: function (select) {
        const self = this,
            cell = select.closest('.two-factor-authentication-method-cell'),
            params = {
                module: 'TwoFactorAuthentication',
                parent: 'Settings',
                action: 'Users',
                mode: 'setMethod',
                userid: select.data('userid'),
                method: select.val()
            };

        app.request.post({data: params}).then(function (error) {
            if (error === null) {
                cell.find('.two-factor-authentication-method-text').text(select.find('option:selected').text());
                self.restoreMethod(select);
                app.helper.showSuccessNotification({message: app.vtranslate('JS_METHOD_SAVED')});
                return;
            }

            select.data('saving', false);
            app.helper.showErrorNotification({message: error});
        });
    },

    initializeEmailEditor: function (pane) {
        pane.find('.two-factor-authentication-template-body').each(function () {
            const textarea = jQuery(this);

            if (textarea.data('ckeditor-initialized')) {
                return;
            }

            textarea.data('ckeditor-initialized', true);
            new Vtiger_CkEditor_Js().loadCkEditor(textarea, {height: '220px'});
        });
    },

    saveEmailTemplate: function (button) {
        const block = button.closest('.two-factor-authentication-template'),
            body = block.find('.two-factor-authentication-template-body'),
            editor = CKEDITOR.instances[body.attr('id')],
            params = {
                module: 'TwoFactorAuthentication',
                parent: 'Settings',
                action: 'EmailTemplate',
                mode: 'save',
                key: block.data('key'),
                subject: block.find('.two-factor-authentication-template-subject').val(),
                body: editor ? editor.getData() : body.val()
            };

        app.request.post({data: params}).then(function (error) {
            if (error === null) {
                app.helper.showSuccessNotification({message: app.vtranslate('JS_SAVED')});
                return;
            }

            app.helper.showErrorNotification({message: error});
        });
    },

    resetEmailTemplate: function (button) {
        const block = button.closest('.two-factor-authentication-template'),
            body = block.find('.two-factor-authentication-template-body'),
            params = {
                module: 'TwoFactorAuthentication',
                parent: 'Settings',
                action: 'EmailTemplate',
                mode: 'reset',
                key: block.data('key')
            };

        app.helper.showConfirmationBox({message: app.vtranslate('JS_RESET_TPL_CONFIRM')}).then(function () {
            app.request.post({data: params}).then(function (error, data) {
                if (error !== null) {
                    app.helper.showErrorNotification({message: error});
                    return;
                }

                block.find('.two-factor-authentication-template-subject').val(data.subject);

                if (CKEDITOR.instances[body.attr('id')]) {
                    CKEDITOR.instances[body.attr('id')].setData(data.body);
                } else {
                    body.val(data.body);
                }

                app.helper.showSuccessNotification({message: app.vtranslate('JS_SAVED')});
            });
        });
    },

    registerEvents: function () {
        const self = this,
            container = this.getContainer();

        this._super();

        container.on('change', '.two-factor-authentication-toggle', function () {
            self.saveStatus(jQuery(this));
        });
        container.on('change', '.two-factor-authentication-exempt-toggle', function () {
            self.saveExempt(jQuery(this));
        });
        container.on('change', '.two-factor-authentication-method', function () {
            self.saveMethods(jQuery(this));
        });
        container.on('click', '.two-factor-authentication-reset', function () {
            self.resetUser(jQuery(this));
        });
        container.on('click', '.two-factor-authentication-method-edit', function () {
            self.editMethod(jQuery(this));
        });
        container.on('change', '.two-factor-authentication-method-select', function () {
            jQuery(this).data('saving', true);
            self.saveUserMethod(jQuery(this));
        });
        container.on('blur', '.two-factor-authentication-method-select', function () {
            const select = jQuery(this);

            if (!select.data('saving')) {
                self.restoreMethod(select);
            }
        });
        container.on('shown.bs.tab', '.two-factor-authentication-email-tab', function () {
            self.initializeEmailEditor(jQuery(jQuery(this).attr('data-bs-target')));
        });
        container.on('click', '.two-factor-authentication-template-save', function () {
            self.saveEmailTemplate(jQuery(this));
        });
        container.on('click', '.two-factor-authentication-template-reset', function () {
            self.resetEmailTemplate(jQuery(this));
        });
    }
});
