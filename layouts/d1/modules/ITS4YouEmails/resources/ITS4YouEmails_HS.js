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
    registerChangeLinks: function() {
        let moduleLinks = $('.module-breadcrumb').find('a'),
            firstLi = $('.nav.nav-tabs li:nth-child(1)'),
            secondLi = $('.nav.nav-tabs li:nth-child(2)'),
            url = 'index.php?module=Emails&view=Detail&record=' + app.getRecordId(),
            urlActivity = url + '&mode=showRecentActivities';

        moduleLinks.attr('href', 'index.php?module=ITS4YouEmails&view=List&targetModule=Emails');

        firstLi.attr('data-url', url)
        firstLi.find('a').attr('href', url);

        secondLi.attr('data-url', urlActivity);
        secondLi.find('a').attr('href', urlActivity);
    },
    registerEmailsDescription: function () {
        let html = '<iframe style="border: 0; width: 100%; height: 60vh; " sandbox="" src="index.php?module=ITS4YouEmails&view=Body&record=' + app.getRecordId() + '&field=description"></iframe>';

        $('#Emails_detailView_fieldValue_description').html(html);
    },
    isEmailsDetail: function() {
        return 'Emails' === app.getModuleName() && 'Detail' === app.getViewName();
    },
    registerEvents: function () {
        if(this.isEmailsDetail()) {
            this.registerEmailsDescription();
            this.registerChangeLinks();
        }
    },
    registerButtons: function() {
        $('.detailview-header').append('<div class="pull-right" style="padding-left: 5px;"><button class="btn btn-default sendITS4YouEmails"><i class="fa fa-envelope"></i>&nbsp;Send Emails</button></div>');
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
