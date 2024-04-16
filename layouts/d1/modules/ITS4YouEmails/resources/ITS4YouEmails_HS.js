/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var ITS4YouEmails_HS_Js */
jQuery.Class('ITS4YouEmails_HS_Js', {
    instance: false,
    getInstance: function () {
        if (!this.instance) {
            this.instance = new ITS4YouEmails_HS_Js();
        }

        return this.instance;
    }
}, {
    registerEvents: function () {
        this.registerSendEmail();
    },
    registerSendEmail: function() {
        $(document).on('click', '.sendITS4YouEmails', function() {
            app.helper.showProgress();

            let params = {
                selected_ids: app.getRecordId(),
                excluded_ids: '',
                viewname: '',
                module: 'ITS4YouEmails',
                view: 'ComposeEmail',
                search_key: '',
                operator: '',
                search_value: '',
                fieldModule: '',
                to: '',
                source_module: '',
                sourceModule: app.getModuleName(),
                sourceRecord: '',
                parentModule: app.getModuleName(),
                pdf_template_ids: '',
                pdf_template_language: '',
                email_template_ids: '',
                email_template_language: '',
                field_lists: '',
                field_lists_cc: '',
                field_lists_bcc: '',
                is_merge_templates: '',
            }

            app.request.post({data: params}).then(function (error, data) {
                app.helper.hideProgress();

                if (!error) {
                    app.helper.showModal(data, {
                        cb: function () {
                            let emailEditInstance = new ITS4YouEmails_MassEdit_Js();
                            emailEditInstance.registerEvents();
                        }
                    });
                }
            });
        });
    }
});

$(document).ready(function () {
    ITS4YouEmails_HS_Js.getInstance().registerEvents();
});
