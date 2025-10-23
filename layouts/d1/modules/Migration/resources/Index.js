/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger.Class('Migration_Index_Js', {
    startDatabaseMigration: function () {
        let params = {
            module: 'Migration',
            view: 'Index',
            mode: 'applyDBChanges',
        };

        app.request.post({data: params}).then(function (err, data) {
            jQuery('#nextButton').addClass('show').removeClass('hide');
            jQuery('#showDetails').addClass('show').removeClass('hide').html(data);
        });
    },
    isRecordsMigration: function () {
        return $('.recordsChanges').length
    },
    isDatabaseMigration: function () {
        return $('.databaseChanges').length;
    },
    registerEvents: function () {
        if (this.isDatabaseMigration()) {
            this.startDatabaseMigration();
        }

        if (this.isRecordsMigration()) {
            this.startRecordsMigration()
        }
    },
    startRecordsMigration() {
        let params = {
            module: 'Migration',
            view: 'Index',
            mode: 'migrateData',
        };

        app.request.post({data: params}).then(function (err, data) {
            jQuery('#nextButton').addClass('show').removeClass('hide');
            jQuery('#showDetails').addClass('show').removeClass('hide').html(data);
        });
    },
});
