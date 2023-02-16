/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Vtiger_Edit_Js('ITS4YouCalendar_Edit_Js', {}, {
    registerEvents: function () {
        const container = $('#EditView');

        this._super();
        this.registerDateTimeField(container);
        this.registerAllDayField(container);
        this.registerReminderField(container);
        this.registerRecurringField(container);
        this.registerMultiReference(container);
    },
    registerBasicEvents: function (container) {
        this._super(container);
        this.registerRecordPreSaveEvent(container);
    },
    registerRecordPreSaveEvent: function (form) {
        const self = this;

        if ('undefined' === typeof form) {
            form = this.getForm();
        }

        const InitialFormData = form.serialize();

        app.event.one(Vtiger_Edit_Js.recordPresaveEvent, function (e) {
            self.registerRecurringEditOptions(e, form, InitialFormData);
            self.resetRecurringDetailsIfDisabled(form);
        });
    },
    resetRecurringDetailsIfDisabled: function (form) {
        if (!form.find('input[name="recurringcheck"]').is(':checked')) {
            jQuery('#recurringType').append('<option value="--None--">None</option>').val('--None--');
        }
    },
    registerRecurringEditOptions: function (e, form, InitialFormData) {
        let currentFormData = form.serialize(),
            editViewContainer = form.closest('.editViewPageDiv').length,
            recurringEdit = form.find('.recurringEdit').length,
            recurringEditMode = form.find('[name="recurringEditMode"]'),
            recurringCheck = form.find('input[name="recurringcheck"]').is(':checked');

        if (editViewContainer && InitialFormData === currentFormData && recurringEdit) {
            recurringEditMode.val('current');
        } else if (editViewContainer && recurringCheck && recurringEdit && InitialFormData !== currentFormData) {
            e.preventDefault();

            let recurringEventsUpdateModal = form.find('.recurringRecordUpdate'),
                clonedContainer = recurringEventsUpdateModal.clone(true, true),
                callback = function (data) {
                    let modalContainer = data.find('.recurringRecordUpdate');
                    modalContainer.removeClass('hide');
                    modalContainer.on('click', '.onlyThisEvent', function () {
                        recurringEditMode.val('current');
                        app.helper.hideModal();
                        form.vtValidate({
                            submitHandler: function () {
                                return true;
                            }
                        });
                        form.submit();
                    });
                    modalContainer.on('click', '.futureEvents', function () {
                        recurringEditMode.val('future');
                        app.helper.hideModal();
                        form.vtValidate({
                            submitHandler: function () {
                                return true;
                            }
                        });
                        form.submit();
                    });
                    modalContainer.on('click', '.allEvents', function () {
                        recurringEditMode.val('all');
                        app.helper.hideModal();
                        form.vtValidate({
                            submitHandler: function () {
                                return true;
                            }
                        });
                        form.submit();
                    });
                };

            app.helper.showModal(clonedContainer, {
                'cb': callback
            });
        }
    },
    registerAllDayField: function (container) {
        this.registerAllDayHandlers(container);
    },
    registerRecurringField: function (container) {
        this.registerChangeRecurringType(container);
        this.registerRecurrenceFieldCheckBox(container);
    },
    registerChangeRecurringType: function (container) {
        const self = this;

        container.on('change', '#recurringType', function (e) {
            const currentTarget = $(e.currentTarget),
                recurringType = currentTarget.val();

            self.changeRecurringTypesUIStyles(recurringType);
        });
    },
    registerRecurrenceFieldCheckBox: function (container) {
        container.on('change', 'input[name="recurringcheck"]', function (e) {
            const element = jQuery(e.currentTarget),
                repeatUI = jQuery('#repeatUI');

            if (element.is(':checked')) {
                repeatUI.css('visibility', 'visible');
            } else {
                repeatUI.css('visibility', 'collapse');
            }
        });
    },
    changeRecurringTypesUIStyles: function (recurringType) {
        const self = this,
            week = $('#repeatWeekUI'),
            month = $('#repeatMonthUI');

        if ('Daily' === recurringType || 'Yearly' === recurringType) {
            week.removeClass('show').addClass('hide');
            month.removeClass('show').addClass('hide');
        } else if ('Weekly' === recurringType) {
            week.removeClass('hide').addClass('show');
            month.removeClass('show').addClass('hide');
        } else if ('Monthly' === recurringType) {
            week.removeClass('show').addClass('hide');
            month.removeClass('hide').addClass('show');
        }
    },
    retrieveDateTimeValues(container) {
        const self = this;

        container.find('.dateTimeField').each(function () {
            const dateTimeContainer = $(this),
                dateTime = dateTimeContainer.find('.datetime input').val();

            dateTimeContainer.find('.date input').val(self.getFormattedDate(dateTime));
            dateTimeContainer.find('.time input').val(self.getFormattedTime(dateTime));
        });
    },
    registerReminderField: function (container) {
        this.registerToggleReminderEvent(container);
        this.registerChangeReminderValue(container);
    },
    registerChangeReminderValue: function (container) {
        const self = this;

        container.on('click', '#js-reminder-checkbox', function () {
            self.updateReminderValue();
        });

        container.on('change', '#js-reminder-controls select', function () {
            self.updateReminderValue();
        });
    },
    updateReminderValue: function () {
        let value = 0;

        if ($('#js-reminder-checkbox').is(':checked')) {
            const days = parseInt($('#js-reminder-days').val()) * 24 * 60,
                hours = parseInt($('#js-reminder-hours').val()) * 60,
                minutes = parseInt($('#js-reminder-minutes').val());

            value = days + hours + minutes;
        }

        $('#js-reminder-value').val(value);
    },
    registerDateTimeHandlers: function (container) {
        this.registerDateTimeUpdateFieldValue(container);
        this.registerDateTimeStartChange(container);
        this.registerDateTimeEndChange(container);
    },
    registerDateTimeUpdateFieldValue: function (container) {
        const self = this;

        container.on('focusout change', '.dateTimeField .date input, .dateTimeField .time input', function () {
            const dateTimeContainer = $(this).closest('.dateTimeField'),
                date = dateTimeContainer.find('.date input'),
                time = dateTimeContainer.find('.time input'),
                datetime = dateTimeContainer.find('.datetime input');

            datetime.val(self.getRawDateTime(date.val() + ' ' + time.val()));
            datetime.trigger('change');
        });
    },
    registerDateTimeStartChange: function (container) {
        container.on('change', '[name="datetime_start"]', function () {
            let minutesToAdd = container.find('input[name="defaultOtherEventDuration"]').val();

            if (container.find('[name="calendar_type"]').val() === 'Call') {
                minutesToAdd = container.find('input[name="defaultCallDuration"]').val();
            }

            let m = moment($(this).val());

            m.add(parseInt(minutesToAdd), 'minutes');

            let endDate = m.format(vtUtils.getMomentDateFormat()),
                endTime = m.format(vtUtils.getMomentTimeFormat());

            if ('Invalid date' !== endDate && 'Invalid date' !== endTime) {
                container.find('[name="datetime_end_date"]').val(endDate).trigger('change');
                container.find('[name="datetime_end_time"]').val(endTime).trigger('change');
            }
        });
    },
    registerDateTimeEndChange: function (container) {
        container.on('change', '[name="datetime_end"]', function () {
            const startElement = container.find('input[name="datetime_start"]'),
                endElement = container.find('input[name="datetime_end"]'),
                endDateElement = container.find('[name="datetime_end_date"]'),
                m1 = moment(endElement.val()),
                m2 = moment(startElement.val()),
                diff = m1.unix() - m2.unix();

            if (0 >= diff) {
                vtUtils.showValidationMessage(endDateElement, app.vtranslate('JS_CHECK_START_AND_END_DATE'));
            } else {
                vtUtils.hideValidationMessage(endDateElement);
            }
        });
    },
    registerToggleReminderEvent: function (container) {
        container.on('change', '#js-reminder-checkbox', function (e) {
            const element = jQuery(e.currentTarget),
                reminderContainer = element.closest('#js-reminder-controls'),
                reminderSelectors = reminderContainer.find('#js-reminder-selections'),
                reminderCheckbox = reminderContainer.find('#js-reminder-value');

            if (element.is(':checked')) {
                reminderSelectors.css('visibility', 'visible');
            } else {
                reminderCheckbox.val('0');
                reminderSelectors.css('visibility', 'collapse');
            }
        })
    },
    registerAllDayHandlers: function (container) {
        const self = this,
            allDayElement = container.find('[id*="_editView_fieldName_is_all_day"]'),
            startTime = container.find('[name="datetime_start_time"]'),
            startTimeParent = startTime.parent(),
            endTime = container.find('[name="datetime_end_time"]'),
            endTimeParent = endTime.parent();

        if (allDayElement.is(':checked')) {
            startTimeParent.addClass('hide');
            endTimeParent.addClass('hide');
        }

        allDayElement.on('change', function (e) {
            const element = jQuery(e.currentTarget);

            if (element.is(':checked')) {
                startTimeParent.addClass('hide');
                endTimeParent.addClass('hide');

                startTime.val(self.getFormattedTime('01-01-2001 00:00')).trigger('change')
                endTime.val(self.getFormattedTime('01-01-2001 23:59')).trigger('change')
            } else {
                startTimeParent.removeClass('hide');
                endTimeParent.removeClass('hide');
            }
        })
    },
    getFormattedDate: function (value) {
        let momentFormat = vtUtils.getMomentCompatibleDateTimeFormat(),
            m = moment(value, momentFormat);

        return m.format(vtUtils.getMomentDateFormat());
    },
    getFormattedTime: function (value) {
        let momentFormat = vtUtils.getMomentCompatibleDateTimeFormat(),
            m = moment(value, momentFormat);

        return m.format(vtUtils.getMomentTimeFormat());
    },
    getRawDateTime: function (value) {
        let momentFormat = vtUtils.getMomentCompatibleDateTimeFormat(),
            m = moment(value, momentFormat);

        return m.format('YYYY-MM-DD HH:mm:ss');
    },
    registerDateTimeField: function (container) {
        this.retrieveDateTimeValues(container);
        this.registerDateTimeHandlers(container);
    },
    registerMultiReference: function (container) {
        const self = this;

        $('.multi-reference-field', container).each(function (index, element) {
            let referenceContainer = $(element),
                referenceModule = referenceContainer.find('[name="popupReferenceModule"]').val(),
                referenceSource = referenceContainer.find('.sourceField'),
                selectElement = referenceContainer.find('.select2');

            self.registerMultiReferenceSelect(referenceModule, selectElement);
            self.registerMultiReferenceQuickCreateEvent(referenceSource, selectElement)
            self.registerMultiReferenceSelectionEvent(referenceSource, selectElement);
            self.retrieveMultiReferenceData(referenceSource, selectElement);
            self.registerMultiReferenceChange(referenceSource, selectElement);
        });
    },
    registerMultiReferenceSelect: function (referenceModule, selectElement) {
        let self = this;

        selectElement.select2(self.getMultiSelectConfig(referenceModule));
    },
    registerMultiReferenceChange: function (referenceSource, selectElement) {
        selectElement.on('change', function () {
            let data = selectElement.select2('data'),
                ids = [];

            $.each(data, function (index, value) {
                ids.push(value.id);
            })

            referenceSource.val(ids.join(';'))
        });
    },
    getMultiSelectConfig: function (referenceModule) {
        return {
            tags: true,
            multiple: true,
            ajax: {
                'url': 'index.php?module=' + referenceModule + '&action=BasicAjax&search_module=' + referenceModule,
                'dataType': 'json',
                'data': function (term, page) {
                    return {
                        'search_value': term
                    };
                },
                'results': function (data) {
                    data.results = data.result;

                    for (let index in data.results) {

                        let resultData = data.result[index];
                        resultData.text = resultData.label;
                    }

                    return data
                },
                transport: function (params) {
                    return jQuery.ajax(params);
                }
            },
        };
    },
    retrieveMultiReferenceData: function (referenceSource, selectElement) {
        let data = [],
            displayValue = referenceSource.data('displayvalue');

        if (displayValue && 'object' === typeof displayValue) {
            $.each(displayValue, function (referenceId, referenceLabel) {
                data.push({
                    id: referenceId,
                    text: referenceLabel,
                });
            });
        }

        selectElement.select2('data', data);
    },
    registerMultiReferenceQuickCreateEvent: function (referenceSource, selectElement) {
        referenceSource.on(Vtiger_Edit_Js.postReferenceQuickCreateSave, function (event, result) {
            if ('object' === typeof result['data']) {
                let updateData = selectElement.select2('data');

                updateData.push({
                    id: result['data']['_recordId'],
                    text: result['data']['_recordLabel'],
                })

                selectElement.select2('data', updateData);
                selectElement.trigger('change');
            }
        });
    },
    registerMultiReferenceSelectionEvent: function (referenceSource, selectElement) {
        referenceSource.on(Vtiger_Edit_Js.postReferenceSelectionEvent, function (event, result) {
            if ('object' === typeof result['data']) {
                let updateData = selectElement.select2('data');

                $.each(result['data'], function (referenceId, referenceData) {
                    updateData.push({
                        id: referenceId,
                        text: referenceData.name,
                    })
                });

                selectElement.select2('data', updateData);
                selectElement.trigger('change');
            }
        });
    }
});