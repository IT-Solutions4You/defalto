/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
Vtiger_Index_Js('ProjectTask_ChartSettings_Js', {}, {
    registerEvents: function () {
        const form = jQuery('.project-task-chart-settings');

        if (form.data('chartSettingsEvents')) {
            return;
        }

        form.data('chartSettingsEvents', true);
        this._super();
        form.off('submit.projectChartSettings').on('submit.projectChartSettings', function (event) {
            event.preventDefault();

            if (form.data('chartSaving')) {
                return;
            }

            const data = form.serializeFormData(),
                button = form.find('[type="submit"]');
            data.module = 'ProjectTask';
            data.action = 'ChartSettings';
            data.closed_statuses = form.find('[name="closed_statuses[]"]:checked').map(function () { return this.value; }).get();
            button.prop('disabled', true);
            form.data('chartSaving', true);
            app.request.post({data}).then(function (error) {
                form.data('chartSaving', false);
                button.prop('disabled', false);

                if (error) {
                    app.helper.showErrorNotification({message: error.message || app.vtranslate('JS_ERROR')});
                    return;
                }

                app.helper.showSuccessNotification({message: app.vtranslate('JS_RECORD_UPDATED')});
            }, function (error) {
                form.data('chartSaving', false);
                button.prop('disabled', false);
                app.helper.showErrorNotification({message: error.message || error.statusText || app.vtranslate('JS_ERROR')});
            });
        });
    }
});
