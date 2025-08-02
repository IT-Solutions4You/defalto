/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
jQuery.validator.addMethod('reminder_required', function (value, element, params) {
    let selectionElement = $(element).parents('#js-reminder-selections')

    return selectionElement.find('#js-reminder-days').val() ||
        selectionElement.find('#js-reminder-hours').val() ||
        selectionElement.find('#js-reminder-minutes').val();
}, jQuery.validator.format(app.vtranslate('JS_REQUIRED_TIME_MORE_THAN_ZERO')));