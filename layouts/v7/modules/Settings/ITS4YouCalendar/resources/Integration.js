/** @var Settings_ITS4YouCalendar_Integration_Js */
Settings_Vtiger_Index_Js('Settings_ITS4YouCalendar_Integration_Js', {}, {
    registerEvents: function() {
        this._super();
        this.registerSearch();
        this.registerField();

        console.log('Settings_ITS4YouCalendar_Integration_Js');
    },
    registerField: function() {
        const self = this;

        $('.fieldModule').on('click', function() {
            const element = $(this),
                params = {
                    module: app.getModuleName(),
                    action: app.getViewName(),
                    mode: 'Field',
                    parent: 'Settings',
                    field_module: element.val(),
                    field_checked: element.is(':checked'),
                };

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    if (data['success']) {
                        app.helper.showSuccessNotification({message: data['message']});
                    } else {
                        app.helper.showErrorNotification({message: data['message']});
                    }
                }
            });
        });
    },
    registerSearch: function() {
        $('.searchInput').on('keyup', function() {
            let value = $(this).val().toLowerCase(),
                modules = $('.searchModule');

            if(value) {
                modules.hide();
                modules.filter('[data-module*="' + value + '"]').show();
                modules.filter('[data-label*="' + value + '"]').show();
            } else {
                modules.show();
            }
        });
    },
})