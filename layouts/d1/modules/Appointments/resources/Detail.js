/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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