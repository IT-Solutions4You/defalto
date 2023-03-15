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
    },
    markAsDone: function (record, module, field, value) {
        const self = this,
            instance = self.getInstance(),
            params = {
                value: value,
                field: field,
                record: record,
                module: module,
                action: 'SaveAjax',
            };

        instance.removePopover();

        app.helper.showConfirmationBox({message: app.vtranslate('JS_MARK_AS_DONE_QUESTION')}).then(function () {
            app.request.post({data: params}).then(function (error, data) {
                if (!error && data['_recordLabel']) {
                    $('.markAsDoneAction' + record).remove();

                    instance.popoverContents[record] = null;

                    app.helper.showSuccessNotification({message: app.vtranslate('JS_MARK_AS_DONE_SUCCESS')})
                } else {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_MARK_AS_DONE_ERROR')})
                }
            });
        })
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
                    $('.fc-daygrid-event[href*="record=' + record + '"]').parent().remove();

                    app.helper.showSuccessNotification({message: app.vtranslate('JS_DELETE_SUCCESS')})
                } else {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_DELETE_ERROR')})
                }
            })
        })
    },
}, {
    updateEvents: {},
    eventIds: [],
    events: [],
    displayEvents: [],
    eventsObject: {},
    eventsData: {},
    calendar: false,
    startDate: '',
    endDate: '',
    popoverTemplate: '<div class="popover" style="width:400px; max-width: 80vw; z-index: 2000;"><div class="arrow"></div><div style="padding: 0;" class="popover-content"></div></div>',
    popoverContents: [],
    popoverElement: null,
    registerEvents: function () {
        this._super();
        this.retrieveCalendar();
        this.registerFieldsChange();
        this.registerEditEvents();
        this.registerMassSelect();
        this.registerPopoverClose();
        this.registerPopoverDetailView();
        this.registerPopoverEditView();
        this.registerPopoverEditSave();
        this.registerQuickEditSave();
    },
    setDate: function (start, end) {
        this.startDate = this.convertToDateString(start);
        this.endDate = this.convertToDateString(end);
    },
    retrieveCalendar: function () {
        const self = this,
            is24HourFormat = 24 === parseInt($('#hour_format').val()),
            calendarElement = document.getElementById('calendar'),
            calendarConfig = {
                editable: true,
                selectable: true,
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
                    hour: 'numeric',
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
                select: function (info) {
                    self.showQuickEdit(info);
                },
                eventResize: function (info) {
                    self.saveEventResizeAndDrop(info);
                },
                eventDrop: function (info) {
                    self.saveEventResizeAndDrop(info);
                },
            };

        self.calendar = new FullCalendar.Calendar(calendarElement, calendarConfig);
        self.calendar.render();
    },
    saveEventResizeAndDrop: function (info) {
        const self = this,
            defaultValues = info.event['_def'],
            recordInfo = defaultValues['publicId'].split('x');

        if (parseInt(recordInfo[0])) {
            info.revert();
        } else {
            let isAllDay = defaultValues['allDay'],
                startDateObject = info.event.start,
                endDateObject = info.event.end ? info.event.end : info.event.start,
                startDate = self.convertToDateString(startDateObject),
                endDate = self.convertToDateString(endDateObject);

            if (isAllDay) {
                startDate += ' 00:00';
                endDate += ' 23:59';

            } else {
                startDate += ' ' + self.convertToTimeString(startDateObject);
                endDate += ' ' + self.convertToTimeString(endDateObject);
            }

            if (startDate === endDate) {
                let endMoment = moment(endDate);
                endMoment.add(1, 'hours');

                endDate = endMoment.format('YYYY-MM-DD HH:mm');
            }

            app.helper.showConfirmationBox({message: app.vtranslate('JS_DRAG_EDIT_CONFIRMATION')}).then(function () {
                const params = {
                    module: 'ITS4YouCalendar',
                    action: 'Calendar',
                    mode: 'UpdateDates',
                    record: recordInfo[1],
                    is_all_day: isAllDay ? 'Yes' : 'No',
                    start_date: startDate,
                    end_date: endDate
                };

                app.request.post({data: params}).then(function (error, data) {
                    if (!error && data['success']) {
                        app.helper.showSuccessNotification({message: data['message']});
                    } else {
                        info.revert();
                    }
                });
            }, function () {
                info.revert();
            });
        }
    },
    convertToTimeString: function (date) {
        const self = this;

        return self.addZeroBefore(date.getUTCHours()) + ':' + self.addZeroBefore(date.getUTCMinutes());
    },
    addZeroBefore: function (number) {
        return (number < 10 ? '0' : '') + number;
    },
    showQuickEdit: function (info) {
        const startDateString = info.startStr,
            endDateString = info.endStr,
            quickCreateNode = jQuery('#quickCreateModules').find('[data-name="ITS4YouCalendar"]'),
            quickCreateParams = {
                'noCache': false,
            };

        if (quickCreateNode.length <= 0) {
            app.helper.showErrorMessage(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'));
        }

        app.event.one('post.QuickCreateForm.show', function (event, form) {
            if (info.allDay) {
                let startDate = moment(startDateString),
                    endDate = moment(endDateString);

                endDate.subtract(1, 'days');

                form.find('[name="datetime_start_date"]').val(startDate.format(vtUtils.getMomentDateFormat())).trigger('change');
                form.find('[name="datetime_end_date"]').val(endDate.format(vtUtils.getMomentDateFormat())).trigger('change');
            } else {
                let startDate = moment(startDateString),
                    endDate = moment(endDateString);

                form.find('[name="datetime_start_date"]').val(startDate.format(vtUtils.getMomentDateFormat())).trigger('change');
                form.find('[name="datetime_start_time"]').val(startDate.format(vtUtils.getMomentTimeFormat())).trigger('change');

                form.find('[name="datetime_end_date"]').val(endDate.format(vtUtils.getMomentDateFormat())).trigger('change');
                form.find('[name="datetime_end_time"]').val(endDate.format(vtUtils.getMomentTimeFormat())).trigger('change');
            }
        });

        quickCreateNode.trigger('click', quickCreateParams);
    },
    registerQuickEditSave: function () {
        const self = this;

        app.event.on('post.QuickCreateForm.save', function (e, data, formData) {
            let recordId = data['_recordId'];

            self.getEventInfo(recordId, 0).then(function (error, data) {
                if (!error && data['info']) {
                    self.setCalendarEvent(data['info'])
                }
            });
        });
    },
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
    registerEventDidMount: function (info) {
        let self = this,
            element = $(info.el),
            publicId = info['event']['_def']['publicId'].split('x'),
            eventType = publicId[0],
            recordId = publicId[1];

        element.popover({
            content: function () {
                self.retrievePopoverContent(recordId, eventType).then(function () {
                    $('.replacePopoverContents' + recordId).replaceWith(self.popoverContents[recordId]);
                });

                if (self.popoverContents[recordId]) {
                    return self.popoverContents[recordId];
                } else {
                    return '<div class="p-2 replacePopoverContents' + recordId + '">' + app.vtranslate('JS_LOADING') + '...</div>';
                }
            },
            html: true,
            animation: true,
            template: self.popoverTemplate,
            trigger: 'hover',
            container: 'body',
            placement: 'bottom auto',
            delay: {show: 500}
        }).on('show.bs.popover', function (event) {
            $('[rel=popover]').not(event.target).popover("destroy");
            self.removePopover();
        }).on('hide.bs.popover', function (event) {
            event.preventDefault();

            setTimeout(function () {
                if (!$('.popover:hover').length && self.popoverElement) {
                    $(event.target).popover('hide');
                    self.removePopover();
                }
            }, 1000);
        });
    },
    removePopover: function () {
        $('.popover').remove();
    },
    setCalendarEvents: function (values) {
        const self = this;

        self.displayEvents = [];

        jQuery.each(values, function (index, value) {
            self.setCalendarEvent(value);
        });
    },
    setCalendarEvent: function (value) {
        let self = this,
            eventId = value.id;

        self.displayEvents.push(eventId);
        self.eventsData[eventId] = value;

        if (!self.eventsObject[eventId]) {
            self.eventsObject[eventId] = self.calendar.addEvent(self.eventsData[eventId]);
        }
    },
    getCalendarEvents: function () {
        return this.calendar.getEvents();
    },
    getCalendarEventId: function (event) {
        return event['_def']['publicId'];
    },
    updateEventsVisibility: function () {
        const self = this;

        $.each(self.getCalendarEvents(), function (index, event) {
            let eventId = self.getCalendarEventId(event);

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
    convertToDateString: function (date, separator = '-', format = 'year-month-day') {
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
    registerPopoverDetailView: function () {
        const self = this;

        $(document).on('click', '.showDetailOverlay', function (e) {
            e.preventDefault();

            const link = jQuery(this),
                recordUrl = link.attr('href'),
                recordData = app.convertUrlToDataParams(recordUrl),
                params = recordData;

            self.updateEvents[recordData['record']] = recordData

            params['mode'] = 'showDetailViewByMode';
            params['requestMode'] = 'full';
            params['displayMode'] = 'overlay';

            app.helper.showProgress();
            app.request.get({data: params}).then(function (err, response) {
                app.helper.hideProgress();
                app.helper.loadPageContentOverlay(response, {
                    'backdrop': 'static', 'keyboard': false
                }).then(function (container) {
                    let detailJS = Vtiger_Detail_Js.getInstanceByModuleName(params.module);
                    detailJS.showScroll(jQuery('.overlayDetail .modal-body'));
                    detailJS.setModuleName(params.module);
                    detailJS.setOverlayDetailMode(true);
                    detailJS.setContentHolder(container.find('.overlayDetail'));
                    detailJS.setDetailViewContainer(container.find('.overlayDetail'));
                    detailJS.registerOverlayEditEvent();
                    detailJS.registerBasicEvents();
                    detailJS.registerClickEvent();
                    detailJS.registerHeaderAjaxEditEvents(container.find('.overlayDetailHeader'));

                    app.event.trigger('post.overlay.load', 0, params);

                    container.find('form#detailView').on('submit', function (e) {
                        e.preventDefault();
                    });
                });
            });
        });
    },
    registerPopoverEditSave: function () {
        const self = this;

        $(document).ajaxComplete(function (event, xhr, settings) {
            let isSaveAction = ('string' === typeof settings['data'] && 0 <= settings['data'].indexOf('action=SaveAjax')) ||
                ('object' === typeof settings['data'] && 'Save' === settings['data'].get('action'));

            if (isSaveAction) {
                $.each(self.getCalendarEvents(), function (index, event) {
                    let publicId = event['_def']['publicId'],
                        eventTypeInfo = publicId.split('x'),
                        eventTypeId = eventTypeInfo[0],
                        recordId = eventTypeInfo[1];

                    if (self.updateEvents[recordId]) {
                        self.getEventInfo(recordId, eventTypeId).then(function (error, data) {
                            if (!error && data['info']) {
                                let info = data['info'];

                                event.setStart(info['start']);
                                event.setEnd(info['end']);
                                event.setProp('title', info['title']);
                                event.setProp('backgroundColor', info['backgroundColor']);
                                event.setProp('borderColor', info['borderColor']);
                                event.setProp('textColor', info['color']);
                            }
                        });
                    }
                })

                self.updateEvents = {};
            }
        });
    },
    getEventInfo: function (recordId, eventTypeId) {
        const params = {
            module: 'ITS4YouCalendar',
            action: 'Events',
            mode: 'EventInfo',
            record_id: recordId,
            event_type_id: eventTypeId,
        }

        return app.request.post({data: params});
    },
    registerPopoverEditView: function () {
        const self = this;

        $(document).on('click', '.showEditOverlay', function (e) {
            e.preventDefault();

            const link = $(this),
                recordUrl = link.attr('href'),
                recordData = app.convertUrlToDataParams(recordUrl),
                params = {
                    module: 'ITS4YouCalendar',
                    action: 'Calendar',
                    mode: 'UIMeta',
                    related_module: recordData['module'],
                };

            self.updateEvents[recordData['record']] = recordData

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    let detailView = Vtiger_Detail_Js.getInstance();

                    window.related_uimeta = (function () {
                        let fieldInfo = data['ui_meta'];

                        return {
                            field: {
                                get: function (name, property) {
                                    if (name && property === undefined) {
                                        return fieldInfo[name];
                                    }
                                    if (name && property) {
                                        return fieldInfo[name][property]
                                    }
                                },
                                isMandatory: function (name) {
                                    if (fieldInfo[name]) {
                                        return fieldInfo[name].mandatory;
                                    }
                                    return false;
                                },
                                getType: function (name) {
                                    if (fieldInfo[name]) {
                                        return fieldInfo[name].type
                                    }
                                    return false;
                                }
                            }
                        };
                    })();

                    detailView.showOverlayEditView(recordUrl);
                }
            });
        });
    },
});