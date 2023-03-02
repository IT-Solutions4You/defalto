/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/** @var ITS4YouCalendar_Calendar_Js */
Vtiger_Index_Js('ITS4YouCalendar_Calendar_Js', {
    instance: false,
    getInstance: function () {
        if (!this.instance) {
            this.instance = new ITS4YouCalendar_Calendar_Js();
        }

        return this.instance;
    }
}, {
    eventIds: [],
    events: [],
    displayEvents: [],
    calendar: false,
    registerEvents: function () {
        this._super();
        this.retrieveCalendar();
        this.registerFieldsChange();
        this.registerEditEvents();
        this.registerMassSelect();
        this.registerPopoverClose();
    },
    startDate: '',
    endDate: '',
    setDate: function (start, end) {
        this.startDate = this.convertDateToString(start);
        this.endDate = this.convertDateToString(end);
    },
    retrieveCalendar: function () {
        const self = this,
            is24HourFormat = 24 === parseInt($('#hour_format').val()),
            calendarElement = document.getElementById('calendar'),
            calendarConfig = {
                timeZone: $('#timezone').val(),
                firstDay: $('#day_of_week').val(),
                height: 'calc(100vh - 160px)',
                initialView: $('#calendar_view').val(),
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                scrollTime: $('#start_hour').val() + ':00',
                expandRows: false,
                dayMaxEventRows: true,
                eventTimeFormat: {
                    year: 'numeric',
                    month: 'long',
                    hour: 'numeric',
                    day: 'numeric',
                    minute: '2-digit',
                    meridiem: is24HourFormat ? false : 'short',
                    hour12: !is24HourFormat,
                },
                slotLabelFormat:
                    {
                        hour: 'numeric',
                        minute: '2-digit',
                        meridiem: is24HourFormat ? false : 'short',
                        hour12: !is24HourFormat,
                    },
                views: {
                    dayGridMonth: {
                        dayMaxEventRows: 4,
                    },
                    timeGridWeek: {
                        dayMaxEventRows: 4
                    },
                    timeGridDay: {
                        dayMaxEventRows: 4
                    }
                },
                eventDidMount: function (info) {
                    self.registerEventDidMount(info);
                },
                events: function (date, successCallback, failureCallback) {
                    self.setDate(date.start, date.end);
                    self.retrieveEventsRange();

                    successCallback({
                        message: 'Success',
                        eventDisplay: 'none',
                    })
                    failureCallback({
                        message: 'Failure',
                        eventDisplay: 'none',
                    });
                },
            };

        self.calendar = new FullCalendar.Calendar(calendarElement, calendarConfig);
        self.calendar.render();
    },
    popoverTemplate: '<div class="popover" style="width:400px; max-width: 80vw; z-index: 2000;"><div class="arrow"></div><div style="padding: 0;" class="popover-content"></div></div>',
    popoverContents: [],
    retrievePopoverContent: function (recordId, eventType) {
        let aDeferred = jQuery.Deferred(),
            self = this,
            params = {
                module: app.getModuleName(),
                view: 'Calendar',
                mode: 'PopoverContainer',
                recordId: recordId,
                eventTypeId: eventType,
            },
            record;

        if (self.popoverContents[recordId]) {
            record = self.popoverContents[recordId];

            aDeferred.resolve(record)
        } else {
            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    self.popoverContents[recordId] = data;

                    aDeferred.resolve(record)
                }
            });
        }

        return aDeferred.promise();
    },
    popoverElement: null,
    registerEventDidMount: function (info) {
        let self = this,
            element = $(info.el),
            publicId = info['event']['_def']['publicId'].split('x'),
            eventType = publicId[0],
            recordId = publicId[1];

        self.retrievePopoverContent(recordId, eventType).then(function () {
            element.popover({
                content: self.popoverContents[recordId],
                html: true,
                animation: true,
                template: self.popoverTemplate,
                trigger: 'hover',
                container: 'body',
                placement: 'auto',
            }).on('show.bs.popover', function (event) {
                $('[rel=popover]').not(event.target).popover("destroy");
                $('.popover').remove();
            }).on('hide.bs.popover', function (event) {
                event.preventDefault();

                if (!$('.popover:hover').length && self.popoverElement) {
                    element.popover('hide');
                    $('.popover').remove();
                }
            });
        });
    },
    setCalendarEvents: function (values) {
        const self = this;

        self.displayEvents = [];

        jQuery.each(values, function (index, value) {
            let eventId = value.id;

            self.displayEvents.push(eventId);

            if (-1 === self.eventIds.indexOf(eventId)) {
                self.eventIds.push(eventId);
                self.calendar.addEvent(value);
            }
        });
    },
    updateEventsVisibility: function () {
        const self = this;

        $.each(self.calendar.getEvents(), function (index, event) {
            let eventId = event['_def']['publicId'];

            if (-1 === self.displayEvents.indexOf(eventId)) {
                event.setProp('display', 'none');
            } else {
                event.setProp('display', 'auto');
            }
        });
    },
    retrieveEventsRange: function () {
        const self = this,
            params = {
                module: 'ITS4YouCalendar',
                action: 'Events',
                mode: 'Range',
                start: self.startDate,
                end: self.endDate,
                filter: {
                    'calendar_type': self.getCalendarType(),
                    'users_groups': $('[name="field_users_groups"]').select2('val'),
                    'event_types': self.getEventType(),
                }
            };

        self.retrieveCalendarEvents(params);
    },
    convertDateToString: function (date, separator = '-', format = 'year-month-day') {
        let data = {
            day: date.getDate(),
            month: date.getMonth() + 1,
            year: date.getFullYear(),
        };

        if (data.day < 10) {
            data.day = '0' + data.day;
        }

        if (data.month < 10) {
            data.month = '0' + data.month;
        }

        let formatInfo = format.split(separator)

        return data[formatInfo[0]] + separator + data[formatInfo[1]] + separator + data[formatInfo[2]];
    },
    retrieveCalendarEvents: function (params) {
        const self = this;

        app.request.post({data: params}).then(function (error, data) {
            if (!error) {
                self.setCalendarEvents(data.events);
                self.setRowsHeight();

                self.updateEventsVisibility();
            }
        });
    },
    setRowsHeight: function () {
        $('.fc-scrollgrid-sync-table tr').attr('style', 'height:16%;');
    },
    registerFieldsChange: function () {
        const self = this,
            form = $('#CalendarFilter')

        form.on('change', '[name="field_users_groups"]', function () {
            self.retrieveEventsRange();
        });

        form.on('change', '.fieldCalendarType', function () {
            self.retrieveEventsRange();
        });

        form.on('change', '.fieldEventType', function () {
            self.retrieveEventsRange();
            self.retrieveMassSelect();
        });
    },
    getEventType: function () {
        let types = [];

        $('.fieldEventType').each(function () {
            let checkbox = $(this);

            if (checkbox.is(':checked')) {
                types.push(checkbox.val());
            }
        });

        return types;
    },
    getCalendarType: function () {
        let types = [];

        $('.fieldCalendarType').each(function () {
            let checkbox = $(this);

            if (checkbox.is(':checked')) {
                types.push(checkbox.val());
            }
        });

        return types;
    },
    registerEditEvents: function () {
        this.registerEditEventModal();
        this.registerEditEventDelete();
    },
    registerEditEventModal: function () {
        const self = this;

        $('.eventTypeContainer').on('click', '.editEventType', function () {
            const button = $(this),
                params = {
                    module: app.getModuleName(),
                    view: 'Calendar',
                    mode: 'EditEventType',
                    record: button.val(),
                };

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showModal(data, {
                        'cb': function () {
                            self.registerEditEventModalEvents(button);
                        },
                    });
                }
            })
        });
    },
    registerEditEventDelete: function () {
        const self = this;

        $('.eventTypeContainer').on('click', '.deleteEventType', function () {
            const button = $(this),
                params = {
                    module: app.getModuleName(),
                    action: 'Events',
                    mode: 'DeleteEventType',
                    record: button.val(),
                };

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    if (data['success']) {
                        let eventType = button.parents('.eventType'),
                            eventTypeCheckbox = eventType.find('.fieldEventType');

                        eventTypeCheckbox.removeAttr('checked');
                        eventTypeCheckbox.trigger('change');
                        eventType.remove();

                        app.helper.showSuccessNotification({message: data['message']})
                    } else {
                        app.helper.showErrorNotification({message: data['message']})
                    }
                }
            })
        });
    },
    deleteEvent: function (record, module) {
        const self = this;

        app.helper.showConfirmationBox({message: app.vtranslate('JS_DELETE_QUESTION')}).then(function () {
            const params = {
                module: module,
                action: 'Delete',
                ajaxDelete: true,
                record: record,
            };

            app.request.post({data: params}).then(function (error, data) {
                if (!error && data) {
                    $('.eventRecordId' + record).remove();

                    app.helper.showSuccessNotification({message: app.vtranslate('JS_DELETE_SUCCESS')})
                } else {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_DELETE_ERROR')})
                }
            })
        })
    },
    registerEditEventColorPicker: function (form) {
        let colorElement = form.find('#event_type_color'),
            colorSelectElement = form.find('.event_type_color_select'),
            currentColor = colorElement.val(),
            params = {
                flat: true,
                onChange: function (hsb, hex, rgb) {
                    let selectedColorCode = '#' + hex;
                    colorElement.val(selectedColorCode);
                },
            };

        colorSelectElement.ColorPicker(params);
        colorSelectElement.ColorPickerSetColor(currentColor);
    },
    registerEditEventModuleChange: function (form) {
        form.on('change', '[name="event_type_module"]', function () {
            let module = $(this).val(),
                fieldElement = form.find('[name="event_type_field"]'),
                rangeFieldElement = form.find('[name="event_type_range_field"]'),
                moduleFields = JSON.parse($('.EditEventTypeFields').val()),
                options = moduleFields[module],
                optionsElements = '';

            $.each(options, function (fieldName, fieldLabel) {
                optionsElements += '<option value="' + fieldName + '">' + fieldLabel + '</option>';
            })

            fieldElement.html(optionsElements);
            fieldElement.trigger('change');

            optionsElements = '<option value="">' + app.vtranslate('JS_ONE_DAY_EVENT') + '</option>' + optionsElements

            rangeFieldElement.html(optionsElements);
            fieldElement.trigger('change');
        });
    },
    registerEditEventModalEvents: function () {
        const self = this,
            form = $('#EditEventType');

        self.registerEditEventColorPicker(form);
        self.registerEditEventModuleChange(form);

        form.on('submit', function (e) {
            e.preventDefault();

            const recordElement = form.find('[name="event_type_record"]'),
                params = {
                    module: app.getModuleName(),
                    action: 'Events',
                    mode: 'EditEventType',
                    event_type: {
                        fields: form.find('[name="event_type_field"]').val() + ',' + form.find('[name="event_type_range_field"]').val(),
                        record: recordElement.val(),
                        module: form.find('[name="event_type_module"]').val(),
                        color: form.find('[name="event_type_color"]').val(),
                    },
                };

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    if (data['success']) {
                        let eventType = data['record'],
                            eventTypeId = eventType['id'],
                            eventTypeCheckBox = $('.fieldEventType[value="' + eventTypeId + '"]'),
                            background = {background: eventType['background_color'], color: eventType['text_color']},
                            color = {color: eventType['text_color']},
                            eventTypeElement,
                            eventTypeIdElements;

                        if (eventTypeCheckBox.length) {
                            eventTypeElement = eventTypeCheckBox.closest('.eventType');
                            eventTypeIdElements = eventTypeElement.find('.eventTypeId');
                        } else {
                            eventTypeElement = $('.eventTypeClone').clone(true, true);
                            eventTypeIdElements = eventTypeElement.find('.eventTypeId');

                            eventTypeElement.removeClass('eventTypeClone');
                            eventTypeElement.find('.eventTypeName').text(eventType['name']);
                            eventTypeIdElements.val(eventTypeId);

                            $('.eventTypeContainer').append(eventTypeElement);
                        }

                        if (eventTypeElement) {
                            eventTypeElement.css(background);
                            eventTypeIdElements.css(color);
                            eventTypeElement.find('.fieldEventType').trigger('change');
                        }

                        app.helper.showSuccessNotification({message: data['message']});
                        app.helper.hideModal();
                    } else {
                        app.helper.showErrorNotification({message: data['message']});
                    }
                }
            });
        });
    },
    registerMassSelect: function () {
        const self = this;

        self.retrieveMassSelect();

        $('.eventTypeContainer').on('click', '.massSelectEventType', function () {
            let checkboxes = $('.fieldEventType');
            if ($(this).is(':checked')) {
                checkboxes.attr('checked', 'checked');
            } else {
                checkboxes.removeAttr('checked');
            }

            self.retrieveEventsRange();
        });
    },
    retrieveMassSelect: function () {
        let checked = true,
            massSelect = $('.massSelectEventType');

        $('.fieldEventType').each(function () {
            if (!$(this).is(':checked')) {
                checked = false;
            }
        });

        if (checked) {
            massSelect.attr('checked', 'checked');
        } else {
            massSelect.removeAttr('checked');
        }
    },
    registerPopoverClose: function () {
        const self = this;

        $(document).on('mouseleave', '.popover', function () {
            $(this).remove();

            if (self.openedPopover) {
                self.openedPopover.popover('hide')
                self.openedPopover = null;
            }
        });

        $(document).on('click', '.close-popover-container', function () {
            $(this).closest('.popover').remove();

            if (self.openedPopover) {
                self.openedPopover.popover('hide')
                self.openedPopover = null;
            }
        });
    },
});