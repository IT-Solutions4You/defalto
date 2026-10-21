/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/* Test of the outgoing server (SMTP) — the wizard's step 3, the admin's
   mailbox edit and My Settings. The box (.its4you-smtp-box) holds the
   smtp_* fields, optionally "same password as IMAP" and data-record (an
   existing mailbox: an empty password tests the stored one) */
Vtiger.Class('MailManager_ProSmtpTest_Js', {}, {

    box: null,
    options: null,

    /**
     * @param {jQuery} box .its4you-smtp-box
     * @param {Object} options {module, parent, onChange: function(tested)}
     */
    init: function (box, options) {
        this.box = box;
        this.options = options || {};
        if (box.length) {
            this.register();
        }
    },

    /**
     * @param {boolean} tested the current settings passed the test
     */
    setTested: function (tested) {
        this.box.find('.its4you-smtp-status').toggleClass('hide', !tested);
        if (typeof this.options.onChange === 'function') {
            this.options.onChange(tested);
        }
    },

    register: function () {
        let self = this,
            box = this.box;

        box.on('change', 'input[name="same_password"]', function () {
            box.find('.its4you-smtp-password-row').toggleClass('hide', jQuery(this).is(':checked'));
        });
        // a changed setting needs a new test
        box.on('input change', 'input[name^="smtp_"], select[name="smtp_encryption"], input[name="same_password"]', function () {
            self.setTested(false);
        });
        box.on('click', '.its4you-test-smtp', function () {
            self.test(jQuery(this));
        });
    },

    /**
     * @param {jQuery} button
     */
    test: function (button) {
        let self = this,
            box = this.box,
            params = {
                module: this.options.module,
                action: ('Settings' === app.getParentModuleName() ? 'TestSmtp' : 'ProTestSmtp'),
                record: box.data('record') || 0,
                smtp_server: box.find('input[name="smtp_server"]').val().trim(),
                smtp_port: box.find('input[name="smtp_port"]').val().trim(),
                smtp_encryption: box.find('select[name="smtp_encryption"]').val(),
                smtp_username: box.find('input[name="smtp_username"]').val().trim(),
                smtp_password: box.find('input[name="smtp_password"]').val(),
                same_password: box.find('input[name="same_password"]').is(':checked') ? '1' : '0'
            };
        if (this.options.parent) {
            params.parent = this.options.parent;
        }

        button.prop('disabled', true).find('i').addClass('fa-spin');
        app.request.post({data: params}).then(function (err, data) {
            button.prop('disabled', false).find('i').removeClass('fa-spin');
            if (err || !data || !data.success) {
                self.setTested(false);
                app.helper.showErrorNotification({message: app.htmlEncode(data && data.message) || app.vtranslate('JS_ERROR')});
                return;
            }
            self.setTested(true);
        });
    }
});
