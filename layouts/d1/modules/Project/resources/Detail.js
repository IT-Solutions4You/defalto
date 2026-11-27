/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Project_Detail_Js */
Vtiger_Detail_Js('Project_Detail_Js', {

    gantt: false,

    showEditColorModel: function (url, e) {
        var element = jQuery(e);
        app.helper.showProgress();
        app.request.post({url: url}).then(function (error, data) {
            if (data) {
                app.helper.hideProgress();
                var callback = function (data) {
                    Project_Detail_Js.registerEditColorPreSaveEvent(data, element);
                    var form = jQuery('#editColor');
                    var params = {
                        submitHandler: function (form) {
                            Project_Detail_Js.saveColor(jQuery(form));
                        }
                    };
                    form.vtValidate(params);
                }
                app.helper.showModal(data, {cb: callback});
            }
        });
    },

    registerEditColorPreSaveEvent: function (data, element) {
        var selectedColorField = data.find('.selectedColor');
        var color = element.data('color');

        if (color) {
            selectedColorField.val(color);
            var customParams = {
                color: color
            };
        } else {
            //if color is not present select random color
            var randomColor = '#' + (0x1000000 + (Math.random()) * 0xffffff).toString(16).substr(1, 6);
            selectedColorField.val(randomColor);
            //color picker params for add calendar view
            var customParams = {
                color: randomColor
            };
        }

        //register color picker
        var params = {
            flat: true,
            onChange: function (hsb, hex, rgb) {
                var selectedColor = '#' + hex;
                selectedColorField.val(selectedColor);
            }
        };

        if (typeof customParams != 'undefined') {
            params = jQuery.extend(params, customParams);
        }

        data.find('.colorPicker').ColorPicker(params);

        //on change of status, update color picker with the status color
        data.off('change.projectColor', '[name=taskstatus]').on('change.projectColor', '[name=taskstatus]', function (event) {
            var selectedOption = jQuery(event.currentTarget).find('option:selected');
            var color = selectedOption.data('color');
            selectedColorField.val(color);
            data.find('.colorPicker').ColorPickerSetColor(color);
        });
    },

    saveColor: function (form) {
        var color = form.find('.selectedColor').val();
        var status = form.find('[name=taskstatus]').val();

        app.helper.showProgress();
        var params = {
            'module': app.getModuleName(),
            'action': 'SaveAjax',
            'mode': 'saveColor',
            'color': color,
            'status': status
        }
        app.request.post({data: params}).then(
            function (error, data) {
                app.helper.hideProgress();
                app.helper.hideModal();
                if (!error) {
                    app.helper.showSuccessNotification({message: app.vtranslate('JS_COLOR_SAVED_SUCESSFULLY')});
                    // to reload chart
                    jQuery('[data-label-key=Chart]').click();
                } else {
                    app.helper.showErrorNotification({message: error});
                }
            }
        );

    }
}, {

    detailViewRecentTicketsTabLabel: 'HelpDesk',
    detailViewRecentTasksTabLabel: 'Project Tasks',
    detailViewRecentMileStonesLabel: 'Project Milestones',

    /**
     * Function to register event for create related record
     * in summary view widgets
     */
    registerSummaryViewContainerEvents: function () {
        this._super();
        this.registerStatusChangeEventForWidget();
        this.registerEventsForTasksWidget();
        this.registerTaskSummaryEvents();
    },

    createSummaryRecord: function (element, module, url) {
        const self = this,
            button = jQuery(element),
            container = this.getContainer(),
            parentWidget = button.closest('[class^="widgetContainer_"]'),
            widget = parentWidget.length ? parentWidget : container.find('.project-task-summary [data-name="LBL_TASKS"]'),
            quickCreate = jQuery('#quickCreateModules').find('[data-name="' + module + '"]');

        if (!quickCreate.length) {
            app.helper.showErrorMessage(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'));
            return;
        }

        // The standard Quick Create saves the project reference without an additional relation write.
        this.setWidgetContainer(false);
        quickCreate.trigger('click', {
            data: app.convertUrlToDataParams(url),
            noCache: false,
            callbackFunction: function (data, error) {
                if (error) {
                    return;
                }

                if (widget.closest('body').length) {
                    self.loadWidget(widget);
                }

                if (Project_Detail_Js.gantt) {
                    Project_Detail_Js.gantt.refreshChart();
                }
            }
        });
    },

    registerTaskSummaryEvents: function () {
        const self = this,
            container = this.getContainer();

        container.off('click.projectSummaryRecordPreview', '.project-summary-task-name, .project-summary-milestone')
            .on('click.projectSummaryRecordPreview', '.project-summary-task-name, .project-summary-milestone', function (event) {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                    return;
                }

                event.preventDefault();
                const link = jQuery(event.currentTarget);

                Vtiger_Index_Js.getInstance().showQuickPreviewForId(link.data('record'), link.data('module'), app.getAppName());
            });
        container.off('click.projectSummaryCreate', '.project-summary-create')
            .on('click.projectSummaryCreate', '.project-summary-create', function (event) {
                const button = jQuery(event.currentTarget);

                self.createSummaryRecord(button, button.data('module'), button.data('url'));
            });
        container.off('click.projectTaskTabs', '.project-summary-status-tabs [role="tab"]')
            .on('click.projectTaskTabs', '.project-summary-status-tabs [role="tab"]', function (event) {
                self.setTaskSummaryActiveTab(jQuery(event.currentTarget));
            });
        container.off('keydown.projectTaskTabs', '.project-summary-status-tabs [role="tab"]')
            .on('keydown.projectTaskTabs', '.project-summary-status-tabs [role="tab"]', function (event) {
                const button = jQuery(event.currentTarget),
                    tabs = button.closest('[role="tablist"]').find('[role="tab"]'),
                    index = tabs.index(button),
                    indexes = {ArrowRight: (index + 1) % tabs.length, ArrowLeft: (index + tabs.length - 1) % tabs.length, Home: 0, End: tabs.length - 1};

                if (Object.prototype.hasOwnProperty.call(indexes, event.key)) {
                    event.preventDefault();
                    tabs.eq(indexes[event.key]).trigger('click').trigger('focus');
                }
            });
        container.off(this.widgetPostLoad + '.projectSummary', '.widget_contents')
            .on(this.widgetPostLoad + '.projectSummary', '.widget_contents', function () {
                self.updateTaskSummaryAvatars(jQuery(this));
                self.restoreTaskSummaryTabs(jQuery(this));
            });
        this.registerTaskSummaryFilterEvents(container);
        this.updateTaskSummaryAvatars(container);
        this.restoreTaskSummaryTabs(container);
    },

    getTaskSummaryTabStorageKey: function (container) {
        const record = container.closest('.project-task-summary').data('record');

        return ['Project', 'taskSummary', 'activeStatusOpen', app.getUserId(), record].join('.');
    },

    isTaskSummaryOverviewGroup: function (group) {
        return group === 'all' || group === 'overdue';
    },

    setTaskSummaryActiveTab: function (button) {
        const self = this,
            content = button.closest('.project-task-summary-content'),
            group = button.data('summary-group'),
            overview = this.isTaskSummaryOverviewGroup(group);

        content.find('.project-summary-status-tabs [role="tab"]').each(function () {
            const tab = jQuery(this),
                selected = this === button[0];

            tab.toggleClass('active', selected).attr('aria-selected', String(selected)).attr('tabindex', selected ? '0' : '-1');
        });
        content.find('.project-summary-group-rows').each(function () {
            this.hidden = !self.isTaskSummaryOverviewGroup(group) && this.id !== group;
        });
        content.find('[data-summary-all-only]').prop('hidden', !overview);
        content.find('.project-summary-table').toggleClass('project-summary-all', overview);
        content.find('[role="tabpanel"]').attr('aria-labelledby', button.attr('id'));
        this.updateTaskSummaryFilters(content);
        app.storage.set(this.getTaskSummaryTabStorageKey(content), group);
    },

    restoreTaskSummaryTabs: function (container) {
        const self = this;

        vtUtils.enableTooltips('.project-task-summary [data-bs-toggle="tooltip"]');
        container.find('.project-summary-status-tabs').each(function () {
            if (jQuery(this).closest('.project-task-summary-content').data('summary-initialized')) {
                return;
            }

            jQuery(this).closest('.project-task-summary-content').data('summary-initialized', true);
            let tabs = jQuery(this).find('[role="tab"]'),
                selected = app.storage.get(self.getTaskSummaryTabStorageKey(jQuery(this))),
                matching = tabs.filter(function () { return jQuery(this).data('summary-group') === selected; }),
                defaultTab = selected === 'overdue' ? tabs.filter('[data-summary-group="all"]') : tabs.filter('[data-summary-status="Open"]');

            if (!defaultTab.length) {
                defaultTab = tabs.filter('[data-summary-status="In Progress"]');
            }

            if (tabs.length) {
                self.restoreTaskSummaryFilters(jQuery(this).closest('.project-task-summary-content'));
                self.setTaskSummaryActiveTab(matching.length ? matching.first() : (defaultTab.length ? defaultTab.first() : tabs.first()));
            }
        });
    },

    registerTaskSummaryFilterEvents: function (container) {
        const self = this;

        container.off('click.projectSummaryLoadMore', '[data-summary-load-more]')
            .on('click.projectSummaryLoadMore', '[data-summary-load-more]', function (event) {
                self.loadTaskSummaryPage(jQuery(event.currentTarget).closest('.project-task-summary-content'), true);
            });
        container.off('click.projectSummaryFilterPanel', '[data-filter-open]')
            .on('click.projectSummaryFilterPanel', '[data-filter-open]', function (event) {
                const button = jQuery(event.currentTarget),
                    menu = button.closest('.project-summary-filter-menu'),
                    panel = button.attr('data-filter-open');

                menu.find('[data-filter-panel]').each(function () { this.hidden = this.dataset.filterPanel !== panel; });
                menu.find('[data-filter-open]').attr('aria-expanded', 'false');
                button.attr('aria-expanded', 'true');
                menu.find('[data-filter-panel]').filter(function () { return this.dataset.filterPanel === panel; }).find('button, input').first().trigger('focus');
            });
        container.off('change.projectSummaryFilters', '[data-filter-key], [data-filter-date]')
            .on('change.projectSummaryFilters', '[data-filter-key], [data-filter-date]', function (event) {
                const content = jQuery(event.currentTarget).closest('.project-task-summary-content');

                self.updateTaskSummaryFilters(content);

                if (event.currentTarget.hasAttribute('data-filter-date')) {
                    event.currentTarget.reportValidity();

                    if (!event.currentTarget.checkValidity()) {
                        return;
                    }
                }

                app.storage.set(self.getTaskSummaryFilterStorageKey(content), self.getTaskSummaryFilters(content));
            });
        container.off('click.projectSummaryFilterClear', '[data-filter-clear], [data-filter-remove]')
            .on('click.projectSummaryFilterClear', '[data-filter-clear], [data-filter-remove]', function (event) {
                const button = jQuery(event.currentTarget),
                    content = button.closest('.project-task-summary-content'),
                    key = button.attr('data-filter-remove'),
                    value = button.attr('data-filter-value');

                if (key) {
                    content.find('[data-filter-key]').filter(function () { return this.dataset.filterKey === key && this.value === value; }).prop('checked', false);
                    content.find('[data-filter-date]').filter(function () { return this.dataset.filterDate === key; }).val('');
                } else {
                    content.find('[data-filter-key]').prop('checked', false);
                    content.find('[data-filter-date]').val('');
                }

                self.updateTaskSummaryFilters(content);
                app.storage.set(self.getTaskSummaryFilterStorageKey(content), self.getTaskSummaryFilters(content));
            });
        container.off('click.projectSummaryFilterDone', '[data-filter-done]')
            .on('click.projectSummaryFilterDone', '[data-filter-done]', function (event) {
                jQuery(event.currentTarget).closest('.project-summary-filter-dropdown').find('.project-summary-filter-toggle').trigger('click').trigger('focus');
            });
        container.off('hide.bs.dropdown.projectSummaryDates', '.project-summary-filter-dropdown')
            .on('hide.bs.dropdown.projectSummaryDates', '.project-summary-filter-dropdown', function (event) {
                if (jQuery(event.clickEvent?.target).closest('#ui-datepicker-div').length) {
                    event.preventDefault();
                }
            });
    },

    getTaskSummaryFilterStorageKey: function (content) {
        return this.getTaskSummaryTabStorageKey(content) + '.filters';
    },

    getTaskSummaryFilters: function (content) {
        const filters = {values: {}, from: this.getTaskSummaryDateValue(content.find('[data-filter-date="from"]')), to: this.getTaskSummaryDateValue(content.find('[data-filter-date="to"]'))};

        content.find('[data-filter-key]:checked').each(function () {
            const key = this.dataset.filterKey;

            if (!filters.values[key]) {
                filters.values[key] = [];
            }

            filters.values[key].push(this.value);
        });
        return filters;
    },

    restoreTaskSummaryFilters: function (content) {
        const saved = app.storage.get(this.getTaskSummaryFilterStorageKey(content), {}),
            format = app.getDateFormat();

        content.find('[data-filter-key]').each(function () {
            this.checked = Array.isArray(saved.values?.[this.dataset.filterKey]) && saved.values[this.dataset.filterKey].includes(this.value);
        });
        content.find('[data-filter-date]').each(function () {
            const input = jQuery(this),
                value = saved[this.dataset.filterDate],
                date = moment(value || '', 'YYYY-MM-DD', true);

            input.attr('data-date-format', format).attr('placeholder', format);

            if (!input.hasClass('hasDatepicker')) {
                vtUtils.registerEventForDateFields(input);
            }

            input.datepicker('option', 'onClose', function () { input.trigger('change'); });
            this.value = date.isValid() ? date.format(format.toUpperCase()) : '';
        });
    },

    getTaskSummaryDateValue: function (input) {
        const value = String(input.val() || '').trim(),
            format = input.attr('data-date-format') || app.getDateFormat(),
            date = moment(value, format.toUpperCase(), true);

        return value === '' ? '' : (date.isValid() ? date.format('YYYY-MM-DD') : null);
    },

    loadTaskSummaryPage: function (content, append) {
        if (append && (content.data('summary-loading') || !content.data('summary-has-more'))) {
            return;
        }

        const self = this,
            filters = this.getTaskSummaryFilters(content),
            group = content.find('.project-summary-status-tabs [aria-selected="true"]').data('summary-group') || 'all',
            sequence = (content.data('summary-sequence') || 0) + 1,
            offset = append ? Number(content.data('summary-next-offset') || 0) : 0;

        // Superseded requests must never append results to a newer filter or replacement widget.
        content.data('summary-sequence', sequence);

        if (!append) {
            content.data('summary-next-offset', 0).data('summary-has-more', false);
            content.find('.project-summary-group-rows').empty();
            content.find('[data-summary-load-more-container]').prop('hidden', true);
            content.find('.project-summary-filter-empty').prop('hidden', true);
        }

        if (filters.from === null || filters.to === null || (filters.from && filters.to && filters.from > filters.to)) {
            content.data('summary-loading', false).attr('aria-busy', 'false');
            content.find('[data-summary-load-more]').prop('disabled', true);
            content.find('.project-summary-filter-empty').prop('hidden', false);
            return;
        }

        content.data('summary-loading', true).attr('aria-busy', 'true');
        content.find('[data-summary-load-more]').prop('disabled', true);
        app.request.post({data: {
            module: 'Project', view: 'Detail', mode: 'showRelatedRecords', relatedModule: 'ProjectTask',
            record: content.closest('.project-task-summary').data('record'),
            summaryPage: 1, summaryFilters: JSON.stringify(filters), summaryGroup: group, summaryOffset: offset
        }}).then(function (error, data) {
            if (!content[0].isConnected || content.data('summary-sequence') !== sequence) {
                return;
            }

            content.data('summary-loading', false).attr('aria-busy', 'false');
            content.find('[data-summary-load-more]').prop('disabled', false);

            if (error) {
                app.helper.showErrorNotification({message: error.message || error});
                return;
            }

            const response = jQuery('<div>').html(data).find('.project-task-summary-content'),
                bodies = content.find('.project-summary-group-rows'),
                overdueTab = response.find('[data-summary-group="overdue"]'),
                currentOverdueTab = content.find('[data-summary-group="overdue"]');

            if (!overdueTab.length) {
                currentOverdueTab.remove();

                if (group === 'overdue') {
                    self.setTaskSummaryActiveTab(content.find('[data-summary-group="all"]'));
                    return;
                }
            } else if (!currentOverdueTab.length) {
                content.find('.project-summary-status-tabs').append(overdueTab.clone());
            }

            if (!append) {
                bodies.empty();
            }

            response.find('.project-summary-group-rows').each(function () {
                const source = this,
                    target = bodies.filter(function () { return this.id === source.id; });

                target.append(jQuery(source).children());
            });
            content.find('.project-summary-status-tabs [role="tab"]').each(function () {
                const tab = jQuery(this),
                    match = response.find('[data-summary-group]').filter(function () { return this.dataset.summaryGroup === tab.attr('data-summary-group'); });

                tab.find('.badge').text(match.find('.badge').text() || '0');
            });
            content.data('summary-next-offset', Number(response.attr('data-summary-next-offset') || 0))
                .data('summary-has-more', response.attr('data-summary-has-more') === '1');
            content.find('[data-summary-load-more-container]').prop('hidden', !content.data('summary-has-more'));
            content.find('.project-summary-group-rows').each(function () { this.hidden = !self.isTaskSummaryOverviewGroup(group) && this.id !== group; });
            content.find('[data-summary-all-only]').prop('hidden', !self.isTaskSummaryOverviewGroup(group));
            content.find('.project-summary-filter-empty').prop('hidden', content.find('[data-summary-filter-row]').length > 0)
                .find('td').attr('colspan', content.find('thead th').filter(function () { return !this.hidden; }).length);
            self.updateTaskSummaryAvatars(content);
            vtUtils.enableTooltips('.project-task-summary [data-bs-toggle="tooltip"]');
        });
    },

    updateTaskSummaryFilters: function (content) {
        let self = this,
            filters = this.getTaskSummaryFilters(content),
            chips = content.find('.project-summary-filter-values'),
            selectedCount = 0;

        chips.empty();
        content.find('[data-filter-key]:checked').each(function () {
            const option = jQuery(this),
                label = option.closest('[data-filter-panel]').attr('data-filter-label');

            self.addTaskSummaryFilterChip(chips, this.dataset.filterKey, this.value, label + ': ' + option.next('span').text());
            selectedCount++;
        });
        content.find('[data-filter-date]').each(function () {
            const input = jQuery(this),
                invalid = self.getTaskSummaryDateValue(input) === null,
                reversed = this.dataset.filterDate === 'to' && filters.from && filters.to && filters.from > filters.to;

            this.setCustomValidity(invalid ? app.vtranslate('JS_PLEASE_ENTER_VALID_DATE') : (reversed ? app.vtranslate('JS_CHECK_START_AND_END_DATE') : ''));
            input.toggleClass('is-invalid', invalid || Boolean(reversed));

            if (this.value) {
                self.addTaskSummaryFilterChip(chips, this.dataset.filterDate, this.value, jQuery(this).closest('label').text().trim() + ': ' + this.value);
                selectedCount++;
            }
        });
        content.find('.project-summary-filter-chips').prop('hidden', selectedCount === 0);
        content.find('.project-summary-filter-count').text(selectedCount).prop('hidden', selectedCount === 0);
        content.find('[data-filter-date]').each(function () {
            const input = jQuery(this),
                rawValue = input.val(),
                boundary = this.dataset.filterDate === 'from' ? filters.to : filters.from,
                option = this.dataset.filterDate === 'from' ? 'maxDate' : 'minDate';

            if (input.hasClass('hasDatepicker')) {
                input.datepicker('option', option, boundary ? moment(boundary, 'YYYY-MM-DD', true).toDate() : null).val(rawValue);
            }
        });
        this.loadTaskSummaryPage(content, false);
    },

    addTaskSummaryFilterChip: function (container, key, value, label) {
        const button = jQuery('<button>', {type: 'button', class: 'btn btn-sm btn-outline-secondary rounded-pill', 'data-filter-remove': key, 'data-filter-value': value});

        button.text(label + ' ×').appendTo(container);
    },

    updateTaskSummaryAvatars: function (container) {
        container.find('.project-summary-avatar').each(function () {
            this.textContent = Vtiger_AssigneeSelect_Js.getInitials(this.dataset.ownerLabel);
        });
    },

    /**
     * Function to get records according to ticket status
     */
    registerStatusChangeEventForWidget: function () {
        const self = this;

        self.getContainer().off('change.projectWidget', '[name="ticketstatus"],[name="projecttaskstatus"],[name="projecttaskprogress"]')
            .on('change.projectWidget', '[name="ticketstatus"],[name="projecttaskstatus"],[name="projecttaskprogress"]', function (e) {
                self.showRelatedRecords(jQuery(e.currentTarget).closest('.summaryWidgetContainer'));
            });
    },

    /**
     * Function to load module summary of Projects
     */
    loadModuleSummary: function () {
        var summaryParams = {};
        summaryParams['module'] = app.getModuleName();
        summaryParams['view'] = "Detail";
        summaryParams['mode'] = "showModuleSummaryView";
        summaryParams['record'] = jQuery('#recordId').val();

        app.request.post({data: summaryParams}).then(
            function (error, data) {
                jQuery('.summaryView').html(data);
            }
        );
    },

    /**
     * Function to load the gantt chart
     */
    loadGanttChart: function (container, retainedState = null) {
        const element = container.find('.project-chart')[0],
            previous = Project_Detail_Js.gantt,
            viewState = retainedState ?? (previous && previous.container.dataset.record === element?.dataset.record ? previous.getViewState() : {});

        if (Project_Detail_Js.gantt) {
            Project_Detail_Js.gantt.destroy();
            Project_Detail_Js.gantt = false;
        }

        if (element) {
            Project_Detail_Js.gantt = new Project_Chart_Js();
            Project_Detail_Js.gantt.setParentInstance(this);
            Project_Detail_Js.gantt.registerEvents(element, viewState);
        }
    },

    loadContents: function (url, data) {
        const detailContentsHolder = this.getContentHolder(),
            thisInstance = this,
            deferred = jQuery.Deferred(),
            requestId = (this.projectContentRequestId || 0) + 1;
        this.projectContentRequestId = requestId;

        if (url.indexOf('index.php') < 0) {
            url = 'index.php?' + url;
        }

        const params = {url},
            chartRequest = app.convertUrlToDataParams(url).mode === 'showChart';

        if (data !== undefined) {
            params.data = data;
        }

        app.helper.showProgress();
        // A detail-tab refresh must not enter the global PJAX/document event lifecycle.
        app.request.post(params).then(function (error, response) {
            if (requestId !== thisInstance.projectContentRequestId) {
                deferred.reject();
                return;
            }

            app.helper.hideProgress();

            if (error || typeof response !== 'string' || !response.trim()) {
                const failure = error || {message: app.vtranslate('JS_ERROR')};
                app.helper.showErrorNotification({message: failure.message || app.vtranslate('JS_ERROR')});
                deferred.reject(failure);
                return;
            }

            if (chartRequest) {
                const incoming = jQuery('<div>').append(jQuery.parseHTML(response)),
                    chartData = incoming.find('.project-chart-data').val();

                if (!chartData || !incoming.find('.project-chart-canvas, .project-chart-unscheduled, .project-chart').length) {
                    app.helper.showErrorNotification({message: app.vtranslate('JS_ERROR')});
                    deferred.reject();
                    return;
                }
            }

            const previous = Project_Detail_Js.gantt,
                viewState = previous ? previous.getViewState() : {};

            if (previous) {
                previous.destroy();
                Project_Detail_Js.gantt = false;
            }

            detailContentsHolder.html(response);
            thisInstance.setForm(detailContentsHolder.find('form#detailView').first());
            thisInstance.registerBlockStatusCheckOnLoad();

            if (detailContentsHolder.find('.project-chart').length) {
                thisInstance.loadGanttChart(detailContentsHolder, viewState);
            }

            // Consumers run only after the replacement chart is ready.
            deferred.resolve(response);
        }, function (error) {
            if (requestId === thisInstance.projectContentRequestId) {
                app.helper.hideProgress();
                app.helper.showErrorNotification({message: error?.message || error?.statusText || app.vtranslate('JS_ERROR')});
            }

            deferred.reject(error);
        });

        return deferred.promise();
    },

    /**
     * Function to register events for project tasks widget
     */
    registerEventsForTasksWidget: function () {
        const self = this,
            summaryViewContainer = self.getContainer(),
            selector = '[data-name="LBL_TASKS"] .editTaskDetails';

        summaryViewContainer.off('click.projectTaskWidget', selector).on('click.projectTaskWidget', selector, function (e) {
            let currentTarget = jQuery(e.currentTarget),
                tasksWidget = currentTarget.closest('[data-name="LBL_TASKS"]'),
                newValue = currentTarget.data('value'),
                element = currentTarget.closest('ul.dropdown-menu'),
                editElement = element.closest('.dropdown'),
                oldValue = element.data('oldValue');

            if (currentTarget.hasClass('emptyOption')) {
                newValue = '';
            }

            newValue = String(newValue ?? '');
            oldValue = String(oldValue ?? '');
            vtUtils.hideValidationMessage(editElement);

            if (element.data('mandatory') && newValue.length <= 0) {
                let result = app.vtranslate('JS_REQUIRED_FIELD');
                vtUtils.showValidationMessage(editElement, result);
                return false;
            }

            if (oldValue !== newValue && !tasksWidget.data('taskSavePending')) {
                let params = {
                    action: 'SaveAjax',
                    record: element.data('recordid'),
                    field: element.data('fieldname'),
                    value: newValue,
                    module: 'ProjectTask'
                };
                tasksWidget.data('taskSavePending', true);
                app.helper.showProgress();
                app.request.post({data: params}).then(function (error, data) {
                    tasksWidget.removeData('taskSavePending');
                    app.helper.hideProgress();

                    if (error) {
                        app.helper.showErrorNotification({message: error.message || error});
                        return;
                    }

                    app.helper.showSuccessNotification({message: app.vtranslate('JS_RECORD_UPDATED')});
                    self.showRelatedRecords(tasksWidget);
                });
            }
        })
    },

    /**
     * Function to get the related records list
     * summary view widget
     */
    showRelatedRecords: function (summaryWidgetContainer) {
        let self = this,
            form = summaryWidgetContainer.closest('form'),
            widgetHeaderContainer = summaryWidgetContainer.find('.widget_header'),
            widgetDataContainer = summaryWidgetContainer.find('.widget_contents'),
            referenceModuleName = widgetHeaderContainer.find('[name="relatedModule"]').val(),
            module = form.find('[name="module"]').val() ?? app.getModuleName(),
            record = form.find('[name="record"]').val() ?? self.getRecordId(),
            params = {};

        if (referenceModuleName === 'ProjectTask') {
            let statusCondition = {},
                selectedStatus = summaryWidgetContainer.find('.widget_filter [name="projecttaskstatus"]').val();

            if (typeof selectedStatus != "undefined" && selectedStatus.length > 0) {
                statusCondition['projecttaskstatus'] = ['projecttaskstatus', 'e', selectedStatus];
                params['whereCondition'] = statusCondition;
            }

            let selectedProgress = summaryWidgetContainer.find('.widget_filter [name="projecttaskprogress"]').val();

            if (typeof selectedProgress != "undefined" && selectedProgress.length > 0) {
                statusCondition['projecttaskprogress'] = ['projecttaskprogress', 'e', selectedProgress];
                params['whereCondition'] = statusCondition;
            }
        }

        if (referenceModuleName === 'HelpDesk') {
            const selectedStatus = summaryWidgetContainer.find('.widget_filter [name="ticketstatus"]').val();

            if (selectedStatus) {
                params.whereCondition = {ticketstatus: ['ticketstatus', 'e', selectedStatus]};
            }
        }

        params['record'] = record;
        params['view'] = 'Detail';
        params['module'] = module;
        params['page'] = widgetDataContainer.find('[name="page"]').val();
        params['limit'] = widgetDataContainer.find('[name="pageLimit"]').val();
        params['relatedModule'] = referenceModuleName;
        params['mode'] = 'showRelatedRecords';

        app.helper.showProgress();
        app.request.post({data: params}).then(function (error, data) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification({message: error.message || error});
                return;
            }

            widgetDataContainer.html(data);
            self.updateTaskSummaryAvatars(widgetDataContainer);
            self.restoreTaskSummaryTabs(widgetDataContainer);
        });
    },

    registerEvents: function () {
        var detailContentsHolder = this.getContentHolder();
        var thisInstance = this;

        if (this.getContainer().data('projectDetailEvents')) {
            return;
        }

        this.getContainer().data('projectDetailEvents', true);
        this._super();

        detailContentsHolder.off('click.projectRecentMilestones', '.moreRecentMilestones').on('click.projectRecentMilestones', '.moreRecentMilestones', function (e) {
            let recentMilestonesTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentMileStonesLabel);

            if (recentMilestonesTab) {
                e.preventDefault();
                recentMilestonesTab.trigger('click');
            }
        });

        detailContentsHolder.off('click.projectRecentTickets', '.moreRecentTickets').on('click.projectRecentTickets', '.moreRecentTickets', function (e) {
            let recentTicketsTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentTicketsTabLabel);

            if (recentTicketsTab) {
                e.preventDefault();
                recentTicketsTab.trigger('click');
            }
        });

        detailContentsHolder.off('click.projectRecentTasks', '.moreRecentTasks').on('click.projectRecentTasks', '.moreRecentTasks', function (e) {
            let recentTasksTab = thisInstance.getTabByLabel(thisInstance.detailViewRecentTasksTabLabel);

            if (recentTasksTab) {
                e.preventDefault();
                recentTasksTab.trigger('click');
            }
        });

        var detailViewContainer = jQuery('.detailViewContainer');

        if (detailViewContainer.find('.project-chart').length != 0) {
            this.loadGanttChart(detailViewContainer);
        }
    }
})
