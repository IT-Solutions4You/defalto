/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Install_Index_Js */
Vtiger_Index_Js('Install_Index_Js', {}, {
    registerEventForStep3: function () {
        jQuery('#recheck').on('click', function () {
            window.location.reload();
        });

        jQuery('input[name="step4"]').off('click').on('click', function (e) {
            let elements = jQuery('.no')

            if (elements.length > 0) {
                elements.addClass('text-danger');

                let msg = app.vtranslate('JS_REQUIRED_PARAMETER_CONFIRMATION');

                app.helper.showConfirmationBox({message: msg}).then(function () {
                    jQuery('form[name="step3"]').submit();
                });
            } else {
                jQuery('form[name="step3"]').submit();
            }
        });
    },

    registerEventForStep4: function () {
        let self = this;

        jQuery('input[name="create_db"]').on('click', function () {
            let userName = jQuery('#root_user'),
                password = jQuery('#root_password'),
                classU = userName.attr('class');

            if ($(this).is(':checked')) {
                userName.removeClass('hide');
                password.removeClass('hide');
            } else {
                userName.addClass('hide');
                password.addClass('hide');
            }
        });

        if (jQuery('input[name="create_db"]').prop('checked')) {
            jQuery('#root_user').removeClass("hide");
            jQuery('#root_password').removeClass("hide");
        }

        function clearPasswordError() {
            jQuery('#passwordError').html('');
        }

        function setPasswordError() {
            jQuery('#passwordError').html(app.vtranslate('JS_WARNING_RETYPE_PASSWORD'));
        }

        //This is not an event, we check if create_db is checked
        jQuery('input[name="retype_password"]').on('blur', function (e) {
            var element = jQuery(e.currentTarget);
            var password = jQuery('input[name="password"]').val();
            if (password !== element.val()) {
                setPasswordError();
            }
        });

        jQuery('input[name="password"]').on('blur', function (e) {
            var retypePassword = jQuery('input[name="retype_password"]');
            if (retypePassword.val() != '' && retypePassword.val() !== jQuery(e.currentTarget).val()) {
                jQuery('#passwordError').html(app.vtranslate('JS_WARNING_RETYPE_PASSWORD'));
            } else {
                clearPasswordError();
            }
        });

        jQuery('input[name="retype_password"]').on('keypress', function (e) {
            clearPasswordError();
        });

        jQuery('input[name="admin_name"]').on('keyup', function (e) {
            let element = $(this),
                oldValue = element.val(),
                newValue = element.val().replace(/[^0-9a-z]/gi, '');

            if (newValue !== oldValue) {
                element.val(newValue);
            }
        });

        jQuery('input[name="step5"]').on('click', function () {
            let error = false,
                validateFieldNames = ['db_hostname', 'db_username', 'db_name', 'password', 'retype_password', 'lastname', 'admin_email'];

            for (let fieldName in validateFieldNames) {
                let field = jQuery('input[name="' + validateFieldNames[fieldName] + '"]');

                if (!field.val()) {
                    field.addClass('error').focus();
                    error = true;
                    break;
                } else {
                    field.removeClass('error');
                }
            }

            let createDatabase = jQuery('input[name="create_db"]:checked');

            if (createDatabase.length > 0) {
                let dbRootUser = jQuery('input[name="db_root_username"]');

                if (!dbRootUser.val()) {
                    dbRootUser.addClass('error').focus();
                    error = true;
                } else {
                    dbRootUser.removeClass('error');
                }
            }

            let password = jQuery('#passwordError'),
                passwordField = jQuery('input[name="password"]'),
                passwordNotStrong = !passwordField.val() || !vtUtils.isPasswordStrong(passwordField.val()),
                emailField = jQuery('input[name="admin_email"]'),
                invalidEmailAddress = !emailField.val() || !vtUtils.isEmailValidated(emailField.val()),
                decimalSeparator = jQuery('[name="currency_decimal_separator"]').val(),
                groupingSeparator = jQuery('[name="currency_grouping_separator"]').val(),
                invalidDecimalGroupingSeparator = !decimalSeparator || !groupingSeparator || decimalSeparator === groupingSeparator;

            if (password.html()) {
                error = true;
            }

            if (passwordNotStrong) {
                error = true;
            }

            if (invalidEmailAddress) {
                emailField.addClass('error').focus();
                error = true;
            } else {
                emailField.removeClass('error');
            }

            if (invalidDecimalGroupingSeparator) {
                error = true;
            }

            if (error) {
                let content;

                if (invalidDecimalGroupingSeparator) {
                    content = self.getErrorContent(app.vtranslate('JS_WARNING_DECIMALS'));
                } else if (invalidEmailAddress) {
                    content = self.getErrorContent(app.vtranslate('JS_WARNING_EMAIL'));
                } else if (passwordNotStrong) {
                    content = self.getErrorContent(app.vtranslate('JS_WARNING_PASSWORD'));
                } else {
                    content = self.getErrorContent(app.vtranslate('JS_WARNING_VALUES'));
                }

                jQuery('#errorMessage').html(content).removeClass('hide')
            } else {
                jQuery('form[name="step4"]').submit();
            }
        });
    },
    getErrorContent(message) {
        return '<div class="col-sm-12"><div class="alert alert-danger errorMessageContent"><button class="btn btn-close me-2" data-bs-dismiss="alert" type="button"></button>' + message + '</div></div>';
    },
    registerEventForStep5: function () {
        jQuery('input[name="step6"]').on('click', function () {
            var error = jQuery('#errorMessage');
            if (error.length) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_RESOLVE_ERROR')});
                return false;
            } else {
                jQuery('form[name="step5"]').submit().hide();
            }
        });
    },

    registerEventForStep6: function () {
        jQuery('input[name="step7"]').on('click', function () {
            jQuery('#progressIndicator').removeClass('hide').addClass('show');
            jQuery('form[name="step6"]').submit();
            jQuery('#formContainer').addClass('hide');
        });
    },
    registerEventForStep7: function () {
        let form = jQuery('form[name="step7"]');

        if (form.length) {
            form.submit();
        }
    },

    registerEvents: function () {
        jQuery('input[name="back"]').on('click', function () {
            var createDatabase = jQuery('input[name="create_db"]:checked');
            if (createDatabase.length > 0) {
                jQuery('input[name="create_db"]').removeAttr('checked');
            }
            window.history.back();
        });
        this.registerEventForStep3();
        this.registerEventForStep4();
        this.registerEventForStep5();
        this.registerEventForStep6();
        this.registerEventForStep7();
    }
});
jQuery(document).ready(function () {
    let indexInstance = new Install_Index_Js();
    indexInstance.registerEvents();
});
