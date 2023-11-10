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
                    app.event.trigger('ITS4YouCalendar.Event.Delete', {id: record})
                    app.helper.showSuccessNotification({message: app.vtranslate('JS_DELETE_SUCCESS')})
                } else {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_DELETE_ERROR')})
                }
            })
        })
    },
    getInstance: function () {
        if (!this.instance) {
            this.instance = new ITS4YouCalendar_Calendar_Js();
        }

        return this.instance;
    },
    instance: false,
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
}, {
    addZeroBefore: function (number) {
        return (number < 10 ? '0' : '') + number;
    },
    calendar: false,
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
    convertToTimeString: function (date) {
        const self = this;

        return self.addZeroBefore(date.getUTCHours()) + ':' + self.addZeroBefore(date.getUTCMinutes());
    },
    displayEvents: [],
    endDate: '',
    eventIds: [],
    events: [],
    eventsData: {},
    eventsObject: {},
    getCalendarEventId: function (event) {
        return event['_def']['publicId'];
    },
    getCalendarEvents: function () {
        return this.calendar.getEvents();
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
    getEventData: function (id) {
        return this.eventsData[id];
    },
    getEventIdByRecord: function (record) {
        let self = this,
            eventId = null;

        $.each(self.displayEvents, function (index, value) {
            let data = value.split('x');

            if (parseInt(data[1]) === parseInt(record)) {
                eventId = value;

                return eventId;
            }
        });

        return eventId;
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
    getGroupSelected: function () {
        let selected = $('#users_groups_group_selected'),
            selectedText = selected.text()

        return selectedText ? JSON.parse(selectedText) : [];
    },
    getSelectedUserImages: function (element) {
        let self = this,
            selectedUsers = self.getUsersAndGroups(),
            images = JSON.parse($('#users_and_groups_images').val()),
            selectUsers = {};

        $.each(selectedUsers, function (selectIndex, selectValue) {
            if (images[selectValue]) {
                $.each(images[selectValue], function (index, selectImages) {
                    selectUsers[selectImages['id']] = selectImages;
                });
            }
        });

        return selectUsers;
    },
    getSlotDuration: function () {
        let value = $('#slot_duration').val(),
            slotDuration = {
                'slotDuration': '00:30:00',
                'slotLabelInterval': 30,
            }

        if ('15 minutes' === value) {
            slotDuration['slotDuration'] = '00:15:00';
            slotDuration['slotLabelInterval'] = 15;
        }

        return slotDuration;
    },
    getUserSelected: function () {
        let selected = $('#users_groups_user_selected'),
            selectedText = selected.text()

        return selectedText ? JSON.parse(selectedText) : [];
    },
    getUsersAndGroups: function () {
        let self = this,
            select = $('#field_users_groups'),
            value = select.val();

        if (!value || !value.length) {
            let valueSelected = self.getUserSelected();

            self.setUsersGroups(valueSelected);
            value = select.val();
        }

        return value;
    },
    getUsersSelected: function () {
        let selected = $('#users_groups_users_selected'),
            selectedText = selected.text()

        return selectedText ? JSON.parse(selectedText) : [];
    },
    popoverContents: [],
    popoverElement: null,
    popoverTemplate: '<div class="popover popoverTemplate" style=""><div class="popover-arrow"></div><div class="popover-content popoverContent"></div></div>',
    registerCloseUsersGroupsButton: function () {
        $('#selected_user_and_groups_images').on('click', '[data-remove-user]', function () {
            let element = $('#field_users_groups'),
                value = $(this).data('remove-user');

            element.find('option[value="' + value + '"]').remove();
            element.trigger('change');
        });
    },
    registerDeleteEvent: function () {
        const self = this;

        app.event.on('ITS4YouCalendar.Event.Delete', function (event, data) {
            let eventId = self.getEventIdByRecord(data['id'])

            if (eventId) {
                self.unsetDisplayEvent(eventId)
                self.updateEventsVisibility();
            }
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
    registerEditEventModal: function () {
        const self = this;

        $('#CalendarFilter').on('click', '.editEventType', function () {
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
                            eventTypeElement.find('.eventTypeName').html(eventType['name']);
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
    registerEditEvents: function () {
        this.registerEditEventModal();
        this.registerEditEventDelete();
    },
    registerEventDidMount: function (info) {
        let self = this,
            element = $(info.el),
            publicId = info['event']['_def']['publicId'].split('x'),
            eventType = publicId[0],
            recordId = publicId[1];

        let popover = new bootstrap.Popover(element, {
            content: function () {
                return '<div class="text-muted">' + app.vtranslate('JS_LOADING') + '...</div>';
            },
            html: true,
            template: self.popoverTemplate,
            animation: true,
            container: 'body',
            placement: 'bottom',
        });

        element.on('mouseenter', function () {
            setTimeout(function () {
                if ($('.eventRecordId' + recordId + ':hover').length) {
                    self.removePopover();
                    popover.show();

                    self.retrievePopoverContent(recordId, eventType).then(function () {
                        if (self.popoverContents[recordId]) {
                            $('.popoverContent').html(self.popoverContents[recordId]);
                        }
                    });
                }
            }, 1000);
        });

        element.on('hide.bs.popover', function (event) {
            event.preventDefault();

            setTimeout(function () {
                if (!$('.popover:hover').length) {
                    popover.hide();
                }
            }, 1000);
        });
    },
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
        this.registerSelectUsers();
        this.registerSelectedUserImages();
        this.registerCloseUsersGroupsButton();
        this.registerDeleteEvent();
    },
    registerFieldsChange: function () {
        const self = this,
            form = $('#CalendarFilter');

        form.on('change', '#field_users_groups', function () {
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
    registerMassSelect: function () {
        const self = this;

        self.registerMassSelectClick('.massSelectCalendars', '.fieldCalendarsType');
    },
    registerMassSelectClick: function (massSelect, fieldType) {
        const self = this;

        self.retrieveMassSelect(massSelect, fieldType);

        $(fieldType).on('change', function () {
            self.retrieveMassSelect(massSelect, fieldType);
        });

        $(massSelect).on('click', function () {
            let checkboxes = $(fieldType);

            if ($(this).is(':checked')) {
                checkboxes.attr('checked', 'checked');
            } else {
                checkboxes.removeAttr('checked');
            }

            self.retrieveEventsRange();
            self.retrieveMassSelect(massSelect, fieldType);
        });
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
                ('object' === typeof settings['data'] && 'Save' === settings['data'].get('action')) ||
                ('object' === typeof settings['data'] && 'SaveOverlay' === settings['data'].get('action'));

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
                                event.setProp('borderColor', info['borderColor'])
                                event.setProp('color', info['backgroundColor']);
                                event.setProp('textColor', info['color']);
                            }
                        });
                    }
                })

                self.updateEvents = {};
            }
        });
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

        app.event.on('post.overLayEditView.loaded', function (event, element) {
            element.find('input[type="hidden"][name*="return"]').remove();
            element.find('input[type="hidden"][name="action"]').val('SaveOverlay');
        });
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
    registerSelectUsers: function () {
        const self = this,
            tabsContainer = $('#users_groups_tabs'),
            selectedGroup = self.getGroupSelected();

        if (selectedGroup && selectedGroup.length) {
            let selectedUser = self.getUserSelected(),
                selectedGroupLength = selectedGroup.length;

            if (selectedGroupLength) {
                let firstValue = selectedGroup[0],
                    firstType = firstValue.split('::::')[0]

                if ('Groups' === firstType) {
                    tabsContainer.find('select.select_group').val(firstValue);

                    self.setButtonActive(tabsContainer.find('.select_groups'));
                } else if (1 < selectedGroupLength) {
                    self.setButtonActive(tabsContainer.find('.select_users'));
                }

                if (!(1 === selectedGroupLength && selectedGroup[0] === selectedUser[0])) {
                    self.setUsersGroups(selectedGroup);
                }
            }
        }

        tabsContainer.on('click', '.select_users_and_groups', function () {
            const params = {
                module: app.getModuleName(),
                view: 'Calendar',
                mode: 'UsersGroupsModal',
                selected: self.getUsersAndGroups(),
            };

            app.request.post({data: params}).then(function (error, data) {
                if (!error) {
                    app.helper.showModal(data, {
                        'backdrop': 'static',
                        'keyboard': false,
                        'cb': function () {
                            self.registerSelectUsersGroupsButton();
                        }
                    });
                }
            })
        });

        tabsContainer.on('click', '.select_user', function () {
            let values = self.getUserSelected();

            self.setUsersGroups(values);
            self.setButtonActive($(this));
        });

        tabsContainer.on('click', '.select_users', function () {
            let values = self.getUsersSelected();

            self.setUsersGroups(values);
            self.setButtonActive($(this));
        });

        tabsContainer.on('click', '.select_group', function () {
            let element = $(this),
                data = element.data();

            if (data['values'] && data['values'].length) {
                self.setUsersGroups(data['values']);
            }
        });

        tabsContainer.on('click', '.select_groups', function () {
            self.setButtonActive($(this));

            let selectValue = tabsContainer.find('select.select_group').val();

            tabsContainer.find('option.select_group[value="' + selectValue + '"]').trigger('click');
        });
    },
    registerSelectUsersGroupsButton: function () {
        const self = this;

        $('.selectUsersGroups').on('click', function () {
            let values = $('[name="field_users_groups_modal"]').val();

            self.setUsersGroups(values);

            app.helper.hideModal();
        });
    },
    registerSelectedUserImages: function () {
        const self = this,
            selectElement = $('#field_users_groups'),
            plusElement = $('.selected_user_and_groups_toggle'),
            hiddenElement = $('.selected_user_and_groups_hidden');

        self.updateSelectedUserImages(selectElement);

        selectElement.on('change', function () {
            self.updateSelectedUserImages($(this));
        });

        plusElement.on('click', function () {
            plusElement.addClass('hide');
            hiddenElement.removeClass('hide')
        });
    },
    removePopover: function () {
        $('.popover').remove();
    },
    retrieveCalendar: function () {
        const self = this,
            is24HourFormat = 24 === parseInt($('#hour_format').val()),
            hideDays = JSON.parse($('#hide_days').val()),
            slotDuration = self.getSlotDuration(),
            monthNames = [
                app.vtranslate('LBL_JANUARY'),
                app.vtranslate('LBL_FEBRUARY'),
                app.vtranslate('LBL_MARCH'),
                app.vtranslate('LBL_APRIL'),
                app.vtranslate('LBL_MAY'),
                app.vtranslate('LBL_JUNE'),
                app.vtranslate('LBL_JULY'),
                app.vtranslate('LBL_AUGUST'),
                app.vtranslate('LBL_SEPTEMBER'),
                app.vtranslate('LBL_OCTOBER'),
                app.vtranslate('LBL_NOVEMBER'),
                app.vtranslate('LBL_DECEMBER')
            ],
            monthNamesShort = [
                app.vtranslate('LBL_JAN'),
                app.vtranslate('LBL_FEB'),
                app.vtranslate('LBL_MAR'),
                app.vtranslate('LBL_APR'),
                app.vtranslate('LBL_MAY'),
                app.vtranslate('LBL_JUN'),
                app.vtranslate('LBL_JUL'),
                app.vtranslate('LBL_AUG'),
                app.vtranslate('LBL_SEP'),
                app.vtranslate('LBL_OCT'),
                app.vtranslate('LBL_NOV'),
                app.vtranslate('LBL_DEC')
            ],
            dayNames = [
                app.vtranslate('LBL_SUNDAY'),
                app.vtranslate('LBL_MONDAY'),
                app.vtranslate('LBL_TUESDAY'),
                app.vtranslate('LBL_WEDNESDAY'),
                app.vtranslate('LBL_THURSDAY'),
                app.vtranslate('LBL_FRIDAY'),
                app.vtranslate('LBL_SATURDAY')
            ],
            dayNamesShort = [
                app.vtranslate('LBL_SUN'),
                app.vtranslate('LBL_MON'),
                app.vtranslate('LBL_TUE'),
                app.vtranslate('LBL_WED'),
                app.vtranslate('LBL_THU'),
                app.vtranslate('LBL_FRI'),
                app.vtranslate('LBL_SAT')
            ],
            calendarElement = document.getElementById('calendar'),
            calendarConfig = {
                themeSystem: 'bootstrap5',
                dayHeaderContent: function (arg) {
                    return dayNamesShort[arg.date.getDay()]
                },
                titleFormat: function (arg) {
                    return monthNames[arg.date['month']] + ' ' + arg.date['year'];
                },
                buttonText: {
                    'today': app.vtranslate('LBL_TODAY'),
                    'month': app.vtranslate('LBL_MONTH'),
                    'week': app.vtranslate('LBL_WEEK'),
                    'day': app.vtranslate('LBL_DAY'),
                    'listWeek': app.vtranslate('LBL_AGENDA')
                },
                weekText: '',
                allDayText: app.vtranslate('LBL_ALL_DAY'),
                editable: true,
                selectable: true,
                timeZone: $('#timezone').val(),
                firstDay: $('#day_of_week').val(),
                height: '100%',
                initialView: $('#calendar_view').val(),
                eventDisplay: 'block',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
                },
                scrollTime: $('#start_hour').val() + ':00',
                expandRows: false,
                dayMaxEventRows: true,
                weekNumbers: true,
                slotDuration: slotDuration['slotDuration'],
                slotLabelInterval: slotDuration['slotLabelInterval'],
                slotLabelFormat: function (info) {
                    let minute = info.date.minute,
                        hour = info.date.hour,
                        am_pm = '';

                    if (0 !== minute) {
                        return '';
                    }

                    if (!is24HourFormat) {
                        if (12 < hour) {
                            hour -= 10;
                            am_pm = ' PM';
                        } else {
                            am_pm = ' AM';
                        }
                    }

                    return hour + am_pm;
                },
                moreLinkContent: function (args) {
                    return app.vtranslate('More');
                },
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: is24HourFormat ? false : 'short',
                    hour12: !is24HourFormat,
                },
                views: {
                    dayGridMonth: {
                        dayMaxEventRows: 4,
                        hiddenDays: hideDays,
                    },
                    timeGridWeek: {
                        dayHeaderContent: function (arg) {
                            return dayNamesShort[arg.date.getDay()] + ' ' + arg.date.getDate();
                        },
                        titleFormat: function (arg) {
                            arg.endMonth = '';

                            if (arg.start['month'] !== arg.end['month']) {
                                arg.endMonth = monthNames[arg.end.month] + ' ';
                            }

                            return monthNames[arg.start.month] + ' ' + arg.start.day + arg.defaultSeparator + arg.endMonth + arg.end.day + ' ' + arg.date.year;
                        },
                        dayMaxEventRows: 4,
                        hiddenDays: hideDays,
                    },
                    timeGridDay: {
                        titleFormat: function (arg) {
                            return monthNames[arg.date['month']] + ' ' + arg.date.day + ', ' + arg.date['year'];
                        },
                        dayMaxEventRows: 4,
                    },
                    listWeek: {
                        titleFormat: function (arg) {
                            arg.endMonth = '';

                            if (arg.start['month'] !== arg.end['month']) {
                                arg.endMonth = monthNames[arg.end.month] + ' ';
                            }

                            return monthNames[arg.start.month] + ' ' + arg.start.day + arg.defaultSeparator + arg.endMonth + arg.end.day + ' ' + arg.date.year;
                        },
                        hiddenDays: hideDays,
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
    retrieveCalendarEvents: function (params) {
        const self = this;

        app.helper.showProgress();
        app.request.post({data: params}).then(function (error, data) {
            app.helper.hideProgress();

            if (!error) {
                self.setCalendarEvents(data.events);
                self.setRowsHeight();

                self.updateEventsVisibility();
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
                    'users_groups': self.getUsersAndGroups(),
                    'event_types': self.getEventType(),
                }
            };

        self.retrieveCalendarEvents(params);
    },
    retrieveMassSelect: function (massSelect, fieldType) {
        let checked = 0,
            length = $(fieldType).length;

        $(fieldType).each(function () {
            if ($(this).is(':checked')) {
                checked += 1;
            }
        });

        if (length === checked) {
            $(massSelect).attr('checked', 'checked');
        } else {
            $(massSelect).removeAttr('checked');
        }
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

                let endMoment = moment(endDate);
                endMoment.subtract(1, 'days');

                endDate = endMoment.format('YYYY-MM-DD HH:mm');
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
    setButtonActive: function (buttonElement) {
        let tabsContainer = $('#users_groups_tabs'),
            groupsActions = tabsContainer.find('.select_groups_actions'),
            usersActions = tabsContainer.find('.select_users_actions');

        tabsContainer.find('.active').removeClass('active');

        buttonElement.addClass('active');

        groupsActions.addClass('hide');
        usersActions.addClass('hide');

        if(buttonElement.is('.select_users')) {
            usersActions.removeClass('hide');
        }

        if (buttonElement.is('.select_groups')) {
            groupsActions.removeClass('hide');
        }
    },
    setCalendarEvent: function (value) {
        let self = this,
            eventId = value.id;

        self.setDisplayEvent(eventId);
        self.setEventData(eventId, value);

        if (!self.eventsObject[eventId]) {
            self.eventsObject[eventId] = self.calendar.addEvent(self.getEventData(eventId));
        }

        if (self.eventsObject[eventId]) {
            let event = self.eventsObject[eventId];

            event.setProp('borderColor', value['borderColor'])
            event.setProp('color', value['backgroundColor']);
            event.setProp('textColor', value['color']);
        }
    },
    setCalendarEvents: function (values) {
        const self = this;

        self.displayEvents = [];

        jQuery.each(values, function (index, value) {
            self.setCalendarEvent(value);
        });
    },
    setDate: function (start, end) {
        this.startDate = this.convertToDateString(start);
        this.endDate = this.convertToDateString(end);
    },
    setDisplayEvent: function (id) {
        this.displayEvents.push(id);
    },
    setEventData: function (id, info) {
        this.eventsData[id] = info;
    },
    setRowsHeight: function () {
        $('.fc-scrollgrid-sync-table tr').attr('style', 'height:16%;');
    },
    setUsersGroups: function (values) {
        const field = $('#field_users_groups')

        field.html('');

        $.each(values, function (valueKey, valueData) {
            field.append('<option selected="selected" value="' + valueData + '">' + valueData + '</option>');
        });

        field.trigger('change');
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

                form.find('[name="is_all_day"]').trigger('click');
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
    startDate: '',
    unsetDisplayEvent: function (id) {
        let index = this.displayEvents.indexOf(id);

        if (index > -1) {
            this.displayEvents[index] = '0x0';
        }
    },
    updateEvents: {},
    updateEventsVisibility: function () {
        const self = this;

        $.each(self.getCalendarEvents(), function (index, event) {
            let eventId = self.getCalendarEventId(event);

            if (-1 === self.displayEvents.indexOf(eventId)) {
                event.setProp('display', 'none');
            } else {
                event.setProp('display', 'block');
            }
        });
    },
    updateSelectedUserImages: function (element) {
        let self = this,
            visibleElement = $('.selected_user_and_groups_visible'),
            hiddenElement = $('.selected_user_and_groups_hidden'),
            imagesPlusElement = $('.selected_user_and_groups_toggle'),
            selectUsers = self.getSelectedUserImages(element);

        visibleElement.html('');
        hiddenElement.html('');

        let appendElement = null,
            index = 0;

        $.each(selectUsers, function (userName, imageInfo) {
            if (index < 20) {
                appendElement = visibleElement;
                imagesPlusElement.addClass('hide');
            } else {
                appendElement = hiddenElement;

                if (hiddenElement.is('.hide')) {
                    imagesPlusElement.removeClass('hide');
                }
            }

            if (imageInfo['image']) {
                appendElement.append('<div class="selected_image selected_image_img py-2 border rounded row mb-2 text-truncate"><div class="col-auto pe-0"><img class="selected_img" src="' + imageInfo['image'] + '" /></div><div class="col selected_name text-truncate">' + imageInfo['name'] + '</div></div>');
            } else {
                appendElement.append('<div class="selected_image selected_image_text py-2 border rounded row mb-2"><div class="col-auto pe-0"><i class="selected_i rounded-circle bg-opacity-10 bg-primary text-center fa fa-user"></i></div><div class="col selected_name text-truncate">' + imageInfo['name'] + '</div></div>');
            }

            index++;
        });

        let plusNumber = index - 5;

        imagesPlusElement.find('span').text('+' + plusNumber);
    },
});