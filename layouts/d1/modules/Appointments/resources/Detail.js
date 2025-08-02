/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Appointments_Detail_Js */
Vtiger_Detail_Js('Appointments_Detail_Js', {}, {
    registerEvents: function () {
        this._super();
    },

    _delete: function (deleteRecordActionUrl) {
        let params = app.convertUrlToDataParams(deleteRecordActionUrl + '&ajaxDelete=true');

        app.helper.showProgress();
        app.request.post({data: params}).then(function (error, data) {
            app.helper.hideProgress();

            if (null === error) {
                if ('object' !== typeof data) {
                    window.location.href = data;
                } else {
                    app.helper.showAlertBox({'message': data.prototype.message});
                }
            } else {
                app.helper.showAlertBox({'message': error});
            }
        });
    },

    remove: function (deleteRecordActionUrl) {
        let self = this,
            isRecurringEvent = jQuery('#addEventRepeatUI').data('recurringEnabled');

        if (isRecurringEvent) {
            app.helper.showConfirmationForRepeatEvents().then(function (postData) {
                deleteRecordActionUrl += '&' + jQuery.param(postData);

                self._delete(deleteRecordActionUrl);
            });
        } else {
            this._super(deleteRecordActionUrl);
        }
    },
})