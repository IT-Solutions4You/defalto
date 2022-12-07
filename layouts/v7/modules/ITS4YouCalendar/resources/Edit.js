/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Vtiger_Edit_Js('ITS4YouCalendar_Edit_Js', {}, {
    registerEvents: function() {
        this._super();
        this.registerDateTimeField();
    },
    retrieveDateTimeValues() {
        $('.dateTimeField').each(function () {
            let dateTimeContainer = $(this),
                dateTime = dateTimeContainer.find('.datetime input').val().split(' ');

            dateTimeContainer.find('.date input').val(dateTime[0])
            dateTimeContainer.find('.time input').val(dateTime[1] + ' ' + dateTime[2])
        });
    },
    registerDateTimeHandlers: function() {
        $('.dateTimeField input').on('focusout change', function () {
            let dateTimeContainer = $(this).closest('.dateTimeField'),
                date = dateTimeContainer.find('.date input'),
                time = dateTimeContainer.find('.time input');

            dateTimeContainer.find('.datetime input').val(date.val() + ' ' + time.val());
        })
    },
    registerDateTimeField: function () {
        this.retrieveDateTimeValues();
        this.registerDateTimeHandlers();
    },
});