/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger.Class('Google_List_Js', {}, {
    getContainer: function () {
        return jQuery('#googleSync');
    },

    requestSync: function (url) {
        const container = this.getContainer(),
            button = container.find('#googleSyncButton'),
            details = container.find('#googleSyncDetails'),
            params = app.convertUrlToDataParams(url);

        app.helper.showProgress();
        app.request.post({data: params}).then(function (error, data) {
            app.helper.hideProgress();
            button.prop('disabled', false);

            if (error) {
                if (error.code === 401) {
                    container.find('#googleSyncFirstTime').val('yes');
                    container.find('#googleRemoveSyncButton').addClass('d-none');
                    button.trigger('click');
                }

                return;
            }

            details.html(data);
            container.find('#googleSyncFirstTime').val('no');
            container.find('#googleRemoveSyncButton').removeClass('d-none');

            if (!container.find('#googleSyncNoRefresh').length && typeof Vtiger_List_Js !== 'undefined') {
                Vtiger_List_Js.getInstance().getListViewRecords();
            }
        });
    },

    registerSyncButton: function () {
        const self = this,
            container = this.getContainer();

        container.on('click', '#googleSyncButton', function (event) {
            const button = jQuery(event.currentTarget),
                url = button.data('url');

            button.prop('disabled', true);
            container.find('#googleSyncTime').remove();

            if (container.find('#googleSyncFirstTime').val() === 'yes') {
                const popup = window.open(url, '', 'height=600,width=600,channelmode=1');

                if (!popup) {
                    button.prop('disabled', false);

                    return;
                }

                const popupWatcher = window.setInterval(function () {
                    if (popup.closed) {
                        window.clearInterval(popupWatcher);
                        button.prop('disabled', false);
                    }
                }, 500);

                window.sync = function () {
                    window.clearInterval(popupWatcher);
                    self.requestSync(url);
                };

                return;
            }

            self.requestSync(url);
        });
    },

    registerRemoveButton: function () {
        const container = this.getContainer();

        container.on('click', '#googleRemoveSyncButton', function (event) {
            const button = jQuery(event.currentTarget),
                params = app.convertUrlToDataParams(button.data('url'));

            app.request.post({data: params}).then(function (error) {
                if (error) {
                    return;
                }

                container.find('#googleSyncFirstTime').val('yes');
                button.addClass('d-none');
                app.helper.showSuccessNotification({message: app.vtranslate('SYNC_REMOVED_SUCCESSFULLY')});
            });
        });
    },

    registerEvents: function () {
        this.registerSyncButton();
        this.registerRemoveButton();
    }
});

jQuery(function () {
    const instance = new Google_List_Js();

    instance.registerEvents();
});
