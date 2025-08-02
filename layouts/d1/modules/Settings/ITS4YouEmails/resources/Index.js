/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Settings_Vtiger_Index_Js("Settings_ITS4YouEmails_Index_Js", {}, {
    registerEvents: function () {
        this._super();
        this.registerModuleSearch();
        this.registerModuleStatus();
    },
    registerModuleSearch: function () {
        const container = $('.emailsIntegration'),
            modules = container.find('.updateModuleTd');

        $.each(modules, function () {
            $(this).attr('module-label', $(this).text().trim().toLowerCase());
        });

        container.on('keyup', '.searchModule', function () {
            let value = $(this).val().trim();

            if (value) {
                modules.addClass('hide');
                modules.filter('[module-label*="' + value.toLowerCase() + '"]').removeClass('hide');
            } else {
                modules.removeClass('hide');
            }
        });
    },
    registerModuleStatus: function () {
        $(document).on('click', '.updateModule', function () {
            const field = $(this),
                params = {
                    module: 'ITS4YouEmails',
                    parent: 'Settings',
                    action: 'Index',
                    reference_module: field.attr('data-module'),
                    reference_activate: field.prop('checked'),
                }

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showSuccessNotification(data);
                }
            });
        });
    },
});