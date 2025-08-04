/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger.Class('Migration_Index_Js', {
    startMigrationEvent: function () {
        let params = {
            module: 'Migration',
            view: 'Index',
            mode: 'applyDBChanges',
        };

        app.request.post({data: params}).then(function (err, data) {
            jQuery('#running').addClass('hide').removeClass('show');
            jQuery('#success').addClass('show').removeClass('hide');
            jQuery('#nextButton').addClass('show').removeClass('hide');
            jQuery('#showDetails').addClass('show').removeClass('hide').html(data);
        });
    },

    registerEvents: function () {
        this.startMigrationEvent();
        this.registerMigrateData();
    },
    registerMigrateData() {
        $('main').on('click', '.migrateData', function () {
            let params = {
                module: 'Migration',
                view: 'Index',
                mode: 'migrateData',
            };

            app.request.post({data: params}).then(function (err, data) {
                jQuery('#running').addClass('hide').removeClass('show');
                jQuery('#success').addClass('show').removeClass('hide');
                jQuery('#showDetails').addClass('show').removeClass('hide').html(data);
            });
        });
    }
});
