jQuery.validator.addMethod('reminder_required', function (value, element, params) {
    let selectionElement = $(element).parents('#js-reminder-selections')

    return selectionElement.find('#js-reminder-days').val() ||
        selectionElement.find('#js-reminder-hours').val() ||
        selectionElement.find('#js-reminder-minutes').val();
}, jQuery.validator.format(app.vtranslate('JS_REQUIRED_TIME_MORE_THAN_ZERO')));