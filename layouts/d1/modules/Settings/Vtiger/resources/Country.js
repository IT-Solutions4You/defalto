/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var Settings_Vtiger_Country_Js */
Settings_Vtiger_Index_Js("Settings_Vtiger_Country_Js", {}, {
    registerEvents: function () {
        this._super();
        this.registerModuleSearch();
        this.registerModuleStatus();
        this.registerButtons();
    },
    registerButtons() {
        let params = {
            module: app.getModuleName(),
            action: app.getViewName(),
            parent: 'Settings',
        }

        $('.activateAll').on('click', function() {
            params['mode'] = 'activateAll';

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showSuccessNotification(data);
                    $('.updateValue').attr('checked', 'checked');
                }
            });
        });
        $('.deactivateAll').on('click', function() {
            params['mode'] = 'deactivateAll';

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showSuccessNotification(data);
                    $('.updateValue').removeAttr('checked');
                }
            });
        });
    },
    registerModuleSearch: function () {
        const container = $('.searchContainer'),
            values = container.find('[data-search-value]');

        container.on('keyup', '.searchValues', function () {
            let value = $(this).val().trim();

            if (value) {
                values.addClass('hide');
                values.filter('[data-search-value*="' + value.toLowerCase() + '"]').removeClass('hide');
            } else {
                values.removeClass('hide');
            }
        });
    },
    registerModuleStatus: function () {
        $(document).on('click', '.updateValue', function () {
            const field = $(this),
                params = {
                    module: app.getModuleName(),
                    action: app.getViewName(),
                    parent: 'Settings',
                    mode: 'update',
                    value: field.attr('data-value'),
                    is_active: field.prop('checked'),
                }

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showSuccessNotification(data);
                }
            });
        });
    },
});