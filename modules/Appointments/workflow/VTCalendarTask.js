/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var VTCalendarTask */
Vtiger.Class('VTCalendarTask', {
    instance: false,
    getInstance: function () {
        if (!this.instance) {
            this.instance = new VTCalendarTask();
        }

        return this.instance;
    },
}, {
    registerEvents: function () {
        this.registerAllDayEvents()
    },
    registerAllDayEvents: function () {
        const self = this;

        self.retrieveAllDay();

        $('[name="is_all_day"]').on('click', function () {
            self.retrieveAllDay();
        });
    },
    retrieveAllDay: function () {
        let startTime = $('[name="start_time"]'),
            startTimeRow = startTime.parents('.row'),
            endTime = $('[name="end_time"]'),
            endTimeRow = endTime.parents('.row');

        if ($('[name="is_all_day"]').is(':checked')) {
            startTimeRow.hide();
            endTimeRow.hide();

            startTime.val('00:00');
            endTime.val('23:59');
        } else {
            startTimeRow.show();
            endTimeRow.show();
        }
    },
});

$(function () {
    VTCalendarTask.getInstance().registerEvents();
})