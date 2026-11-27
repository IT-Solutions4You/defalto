/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
Vtiger.Class('Project_Chart_Js', {
    pendingTasks: new Set(),
    preferenceQueues: new Map(),
    active: null
}, {
    registerEvents(container, viewState = {}) {
        if (!container || (this.container === container && !this.destroyed)) {
            return;
        }

        this.destroyed = false;

        if (container.projectChartController) {
            container.projectChartController.destroy();
        }

        container.projectChartController = this;
        this.container = container;
        this.chart = window.Gantt.getGanttInstance();
        this.events = [];
        this.hasChart = false;
        this.originalTasks = new Map();
        this.filters = {search: '', owner: '', status: ''};
        this.viewState = viewState;
        this.restoring = true;
        this.dependencySaving = false;
        this.printView = container.dataset.print === 'true';

        if (!this.printView) {
            Project_Chart_Js.active = this;
        }

        this.initialize();
    },

    showTodayMarker() {
        const host = this.chart.$task_data;

        if (!host) {
            return;
        }

        if (!this.todayMarker) {
            this.todayMarker = document.createElement('div');
            this.todayMarker.className = 'project-chart-today-marker';
            this.todayMarker.title = this.container.dataset.todayLabel + ' · ' + this.formatDate(this.getDate(this.today));
            this.todayMarker.setAttribute('aria-label', this.todayMarker.title);
        }

        const position = this.chart.posFromDate(this.getDate(this.today));
        this.todayMarker.hidden = position === null;
        this.todayMarker.style.left = position + 'px';
        host.appendChild(this.todayMarker);
    },

    escape(value) {
        const element = document.createElement('span');
        element.textContent = value == null ? '' : String(value);
        return element.innerHTML;
    },

    getDate(value) {
        const parts = value.split('-').map(Number);
        // CRM date-only fields are floating calendar dates, never UTC timestamps.
        return new Date(parts[0], parts[1] - 1, parts[2]);
    },

    getEndDate(task) {
        return task.type === 'milestone' ? task.start_date : this.chart.date.add(task.end_date, -1, 'day');
    },

    getCalendarDayCount(task) {
        if (task.type === 'milestone') {
            return 0;
        }

        const start = task.start_date,
            end = this.getEndDate(task),
            first = Date.UTC(start.getFullYear(), start.getMonth(), start.getDate()),
            last = Date.UTC(end.getFullYear(), end.getMonth(), end.getDate());

        return Math.max(1, Math.round((last - first) / 86400000) + 1);
    },

    getInitialScale(start, end) {
        if (!start || !end) {
            return 2;
        }

        const first = this.getDate(start),
            last = this.getDate(end),
            days = Math.round((Date.UTC(last.getFullYear(), last.getMonth(), last.getDate())
                - Date.UTC(first.getFullYear(), first.getMonth(), first.getDate())) / 86400000) + 1;

        if (!Number.isFinite(days) || days < 1) {
            return 2;
        }

        return days <= 30 ? 0 : days <= 56 ? 1 : 2;
    },

    initialize() {
        const chart = this.chart,
            container = this.container,
            data = JSON.parse(container.querySelector('.project-chart-data').value),
            locale = container.dataset.locale.slice(0, 2),
            localeMap = {cz: 'cs', gb: 'en'},
            canvas = container.querySelector('.project-chart-canvas');
        this.viewState = {...(data.preferences || {}), scale: this.getInitialScale(data.project_start, data.project_end), ...this.viewState};
        this.filters = {search: this.viewState.search || '', owner: this.viewState.owner || '', status: this.viewState.status || ''};
        this.persistReady = !this.printView && data.preferences_ready;
        this.projectStart = data.project_start;
        this.projectEnd = data.project_end;
        this.today = data.today;

        if (this.printView) {
            this.filters = {search: container.dataset.filterSearch || '', owner: container.dataset.filterOwner || '',
                status: container.dataset.filterStatus || ''};
        }

        this.registerUnscheduledTasks(data.unscheduled || []);
        this.registerFilters([...data.data, ...(data.unscheduled || [])]);
        this.registerViewControls();
        this.registerContainerEvents();

        if (!canvas) {
            this.restoring = false;
            return;
        }

        chart.clearAll();
        chart.plugins({keyboard_navigation: true, tooltip: true});
        chart.i18n.setLocale(localeMap[locale] || locale);
        chart.config.readonly = this.printView;
        chart.config.drag_links = !this.printView && data.can_edit_dependencies;
        chart.config.drag_progress = false;
        chart.config.details_on_dblclick = false;
        chart.config.show_links = true;
        chart.config.work_time = false;
        chart.config.duration_unit = 'day';
        chart.config.keyboard_navigation = true;
        chart.config.auto_types = false;
        chart.config.round_dnd_dates = false;
        chart.config.auto_scheduling = false;
        chart.config.row_height = 42;
        chart.config.scale_height = 52;
        chart.config.grid_width = 520;
        chart.config.show_grid = this.printView || this.viewState.show_grid !== false;
        chart.config.start_date = this.printView && container.dataset.printFrom ? this.getDate(container.dataset.printFrom) : undefined;
        chart.config.end_date = this.printView && container.dataset.printTo ? chart.date.add(this.getDate(container.dataset.printTo), 1, 'day') : undefined;
        chart.config.min_column_width = 42;
        chart.config.autosize = this.printView ? 'xy' : false;
        chart.config.smart_rendering = !this.printView;
        chart.config.columns = [
            {name: 'text', label: container.dataset.taskLabel, tree: true, width: '*', min_width: 110,
                template: task => this.getTaskLabel(task)},
            {name: 'start', label: container.dataset.startLabel, width: 125,
                template: task => this.escape(this.formatDate(task.start_date))},
            {name: 'days', label: container.dataset.daysLabel, width: 100, align: 'center',
                template: task => '<span title="' + this.escape(this.formatDate(task.start_date) + ' – '
                    + this.formatDate(this.getEndDate(task))).replaceAll('"', '&quot;') + '">'
                    + this.getCalendarDayCount(task) + '</span>' + this.getWarningIcon(task)}
        ];
        chart.templates.task_text = (start, end, task) => this.escape(task.text) + this.getWarningIcon(task);
        chart.templates.progress_text = () => '';
        chart.templates.tooltip_text = (start, end, task) => this.escape(task.text) + '<br>' +
            this.escape(this.formatDate(start) + ' – ' + this.formatDate(this.getEndDate(task))) + '<br>' +
            this.escape(task.status || '') + (task.progress_visible ? ' · ' + Math.round(task.progress * 100) + '%' : '');
        chart.templates.task_class = (start, end, task) =>
            [task.readonly ? 'project-chart-readonly' : '', task.outside_project ? 'project-chart-outside' : '',
                task.dependency_violation ? 'project-chart-conflict' : '', task.is_project_range ? 'project-chart-range' : '',
                task.overdue ? 'project-chart-overdue' : '', task.is_milestone_range ? 'project-chart-milestone-range' : '',
                task.milestone_violation ? 'project-chart-conflict' : ''].join(' ');
        chart.templates.grid_row_class = (start, end, task) => task.is_project_range ? 'project-chart-range-row' : '';
        chart.templates.scale_cell_class = date => this.getPeriodClass(date, ['day', 'week', 'month'][this.scale]);
        chart.templates.timeline_cell_class = (task, date) => this.getPeriodClass(date, ['day', 'week', 'month'][this.scale]);
        chart.templates.link_class = link => link.violated ? 'project-chart-link-conflict' : '';
        chart.templates.grid_folder = chart.templates.grid_file = chart.templates.grid_blank = chart.templates.grid_indent = () => '';
        this.setScale(this.printView ? Number(container.dataset.printScale || 1) : this.viewState.scale ?? 1, false);

        for (const task of data.data) {
            task.start_date = this.getDate(task.start_date);
            task.end_date = task.type === 'milestone' ? task.start_date : chart.date.add(this.getDate(task.end_date), 1, 'day');
        }

        this.events.push(chart.attachEvent('onTaskDblClick', (id, event) => {
            if (event.target.closest('.project-chart-warning')) {
                return false;
            }

            this.openRecord(chart.getTask(id));
            return false;
        }));
        this.events.push(chart.attachEvent('onBeforeTaskDisplay', (id, task) => this.matchesFilters(task)));
        this.events.push(chart.attachEvent('onBeforeLightbox', () => false));
        this.events.push(chart.attachEvent('onTaskSelected', id => {
            this.viewState.selected = String(id);
            this.schedulePreferenceSave();
        }));
        this.events.push(chart.attachEvent('onGanttScroll', (x, y) => {
            this.viewState.scroll_x = x;
            this.viewState.scroll_y = y;
            this.updatePrintUrl();
            this.schedulePreferenceSave();
        }));
        this.events.push(chart.attachEvent('onBeforeLinkDisplay', (id, link) =>
            this.matchesFilters(chart.getTask(link.source)) && this.matchesFilters(chart.getTask(link.target))));
        this.events.push(chart.attachEvent('onBeforeTaskDrag', (id, mode, event) =>
            !event?.target.closest('.project-chart-warning')
            && !Project_Chart_Js.pendingTasks.has(String(chart.getTask(id).recordid)) && !chart.getTask(id).readonly
            && (chart.getTask(id).type !== 'milestone' || mode === chart.config.drag_mode.move)));
        this.events.push(chart.attachEvent('onTaskDrag', (id, mode, task, original) => this.updateDragDates(task, original, mode)));
        this.events.push(chart.attachEvent('onBeforeTaskChanged', (id, mode, original) => {
            this.originalTasks.set(id, original);
            return chart.getTask(id).type === 'milestone' || chart.getTask(id).end_date > chart.getTask(id).start_date;
        }));
        this.events.push(chart.attachEvent('onAfterTaskDrag', id => this.saveDates(chart.getTask(id))));
        this.events.push(chart.attachEvent('onBeforeLinkAdd', (id, link) => {
            if (this.isLinkEditable(link) && String(link.type) === '0') {
                this.saveDependency(link, 'create');
            } else {
                this.showError(this.container.dataset.dependencyHelp);
            }

            return false;
        }));
        this.events.push(chart.attachEvent('onLinkDblClick', id => {
            const link = chart.getLink(id);

            if (this.isLinkEditable(link)) {
                app.helper.showConfirmationBox({message: app.vtranslate('JS_ARE_YOU_SURE_YOU_WANT_TO_DELETE')})
                    .then(() => this.saveDependency(link, 'remove'));
            }

            return false;
        }));
        this.events.push(chart.attachEvent('onBeforeLinkDelete', () => false));
        this.events.push(chart.attachEvent('onGanttRender', () => this.showTodayMarker()));
        chart.init(canvas);
        this.hasChart = true;
        chart.parse(data);
        this.showTodayMarker();
        chart.addShortcut('enter', () => {
            const id = chart.getSelectedId();

            if (id && chart.isTaskExists(id)) {
                this.openRecord(chart.getTask(id));
            }
        }, 'taskRow');
        this.restoreView();
        this.restoring = false;
        this.updatePrintUrl();
    },

    getTaskLabel(task) {
        const title = this.escape(task.text).replaceAll('"', '&quot;'),
            owner = this.escape(task.owner || '').replaceAll('"', '&quot;'),
            avatar = task.module === 'ProjectTask' && task.owner && !this.printView
                ? '<span class="core-assignee-avatar project-chart-owner-avatar rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center me-1" title="'
                    + owner + '" aria-hidden="true">' + this.escape(Vtiger_AssigneeSelect_Js.getInitials(task.owner)) + '</span>' : '';

        return '<span class="project-chart-task-name" title="' + title + '">' + avatar + this.escape(task.text) + '</span>';
    },

    isWeekend(date) {
        return this.scale === 0 && (date.getDay() === 0 || date.getDay() === 6);
    },

    getPeriodClass(date, unit) {
        const today = this.getDate(this.today),
            end = this.chart.date.add(date, 1, unit),
            classes = [];

        if (unit === 'day' && this.isWeekend(date)) {
            classes.push('project-chart-weekend');
        }

        if (date <= today && today < end) {
            classes.push('project-chart-current-period');
        }

        return classes.join(' ');
    },

    getViewState() {
        return {...this.viewState, ...this.filters, scale: this.scale ?? this.viewState.scale ?? 1,
            show_grid: this.hasChart && !this.destroyed ? this.chart.config.show_grid : this.viewState.show_grid !== false};
    },

    registerViewControls() {
        this.container.querySelector('.project-chart-grid-toggle')?.setAttribute('aria-expanded', String(this.viewState.show_grid !== false));
    },

    registerContainerEvents() {
        if (this.containerEvents) {
            return;
        }

        this.containerEvents = {
            click: event => {
                const button = event.target.closest?.('button');

                if (!button || !this.container.contains(button)) {
                    return;
                }

                if (button.matches('.project-chart-warning')) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.showWarnings(button.dataset.taskId, button);
                } else if (button.matches('.project-chart-zoom-in')) {
                    this.setScale(this.scale - 1);
                } else if (button.matches('.project-chart-zoom-out')) {
                    this.setScale(this.scale + 1);
                } else if (button.matches('.project-chart-grid-toggle')) {
                    this.viewState.show_grid = !this.chart.config.show_grid;
                    this.chart.config.show_grid = this.viewState.show_grid;
                    button.setAttribute('aria-expanded', String(this.viewState.show_grid));
                    this.chart.render();
                    this.schedulePreferenceSave();
                } else if (button.matches('.project-chart-print')) {
                    window.print();
                } else if (button.matches('.project-chart-unscheduled-task')) {
                    this.openRecord(this.unscheduledTasks.get(button.dataset.taskId));
                }
            },
            keydown: event => {
                const button = event.target.closest?.('button.project-chart-warning');

                if (button && ['Enter', ' '].includes(event.key)) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (!event.repeat) {
                        this.showWarnings(button.dataset.taskId, button);
                    }
                }
            },
            input: event => {
                if (event.target.matches('.project-chart-filter-search')) {
                    this.updateFilters();
                }
            },
            change: event => {
                if (event.target.matches('.project-chart-filter-owner, .project-chart-filter-status')) {
                    this.updateFilters();
                }
            }
        };

        for (const [type, handler] of Object.entries(this.containerEvents)) {
            this.container.addEventListener(type, handler, ['click', 'keydown'].includes(type));
        }
    },

    restoreView() {
        const chart = this.chart;

        if (this.printView) {
            return;
        }

        if (this.viewState.selected && chart.isTaskExists(this.viewState.selected) && this.matchesFilters(chart.getTask(this.viewState.selected))) {
            chart.selectTask(this.viewState.selected);
        }

        if (this.viewState.scroll_x !== undefined) {
            chart.scrollTo(this.viewState.scroll_x, this.viewState.scroll_y || 0);
        } else if (this.projectStart) {
            const date = this.today >= this.projectStart && this.projectEnd && this.today <= this.projectEnd ? this.today : this.projectStart;
            chart.showDate(this.getDate(date));
        }
    },

    schedulePreferenceSave() {
        if (this.restoring || this.printView || this.destroyed) {
            return;
        }

        clearTimeout(this.preferenceTimer);
        this.preferenceTimer = setTimeout(() => {
            this.preferenceTimer = null;
            this.persistViewState();
        }, 400);
    },

    persistViewState(callback) {
        clearTimeout(this.preferenceTimer);
        this.preferenceTimer = null;

        if (!this.persistReady) {
            if (callback) {
                callback();
            }

            return;
        }

        const key = this.container.dataset.record,
            queues = Project_Chart_Js.preferenceQueues;

        if (!queues.has(key)) {
            queues.set(key, {saving: false, pending: null, callbacks: []});
        }

        const queue = queues.get(key);
        queue.pending = {settings: this.getViewState(), source: this};

        if (callback) {
            queue.callbacks.push(callback);
        }

        this.processPreferenceQueue(key);
    },

    processPreferenceQueue(key) {
        const queue = Project_Chart_Js.preferenceQueues.get(key);

        if (!queue || queue.saving) {
            return;
        }

        if (!queue.pending) {
            Project_Chart_Js.preferenceQueues.delete(key);

            for (const callback of queue.callbacks) {
                callback();
            }

            return;
        }

        const request = queue.pending;
        queue.pending = null;
        queue.saving = true;
        app.request.post({data: {module: 'Project', action: 'ChartPreference', record: key, settings: request.settings}})
            .then(error => this.completePreferenceSave(key, request, error),
                error => this.completePreferenceSave(key, request, error));
    },

    completePreferenceSave(key, request, error) {
        const queue = Project_Chart_Js.preferenceQueues.get(key);
        queue.saving = false;

        if (error && !request.source.destroyed) {
            request.source.showError(error);
        }

        this.processPreferenceQueue(key);
    },

    updateDragDates(task, original, mode) {
        const chart = this.chart,
            midnight = date => new Date(date.getFullYear(), date.getMonth(), date.getDate());

        if (task.type === 'milestone') {
            task.start_date = midnight(task.start_date);
            task.end_date = new Date(task.start_date);
            task.duration = 0;
            return;
        }

        if (task.is_milestone_range) {
            task.start_date = new Date(original.start_date);
            task.end_date = midnight(task.end_date);

            if (task.end_date <= task.start_date) {
                task.end_date = chart.date.add(task.start_date, 1, 'day');
            }

            return;
        }

        if (mode === chart.config.drag_mode.move) {
            // UTC ordinals count calendar boundaries, independent of 23/25-hour local days.
            const ordinal = date => Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()),
                days = Math.round((ordinal(original.end_date) - ordinal(original.start_date)) / 86400000);
            task.start_date = midnight(task.start_date);
            task.end_date = chart.date.add(task.start_date, days, 'day');
        } else if (mode === chart.config.drag_mode.resize) {
            if (task.start_date.getTime() !== original.start_date.getTime()) {
                task.end_date = new Date(original.end_date);
                task.start_date = midnight(task.start_date);

                if (task.start_date >= task.end_date) {
                    task.start_date = chart.date.add(task.end_date, -1, 'day');
                }
            } else {
                task.start_date = new Date(original.start_date);
                task.end_date = midnight(task.end_date);

                if (task.end_date <= task.start_date) {
                    task.end_date = chart.date.add(task.start_date, 1, 'day');
                }
            }
        }
    },

    getWarningIcon(task) {
        const warnings = this.getWarnings(task);

        return !this.printView && warnings.length ? ' <button type="button" class="project-chart-warning btn btn-link p-0 border-0 lh-1"'
            + ' data-task-id="' + this.escape(String(task.id)) + '" aria-label="'
            + this.escape(this.container.dataset.warningLabel + ': ' + task.text) + '">⚠</button>' : '';
    },

    showWarnings(id, trigger) {
        if (!this.chart.isTaskExists(id)) {
            return;
        }

        const task = this.chart.getTask(id),
            warnings = this.getWarnings(task),
            body = warnings.map(warning => '<li class="mb-3"><p class="mb-2">' + this.escape(warning.reason)
                + '</p><p class="mb-0 fw-semibold">' + this.escape(warning.action) + '</p></li>').join(''),
            html = '<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">'
                + '<div class="modal-header"><h5 class="modal-title" id="projectChartWarningsTitle">'
                + this.escape(this.container.dataset.warningLabel) + '</h5><button type="button" class="btn-close"'
                + ' data-bs-dismiss="modal" aria-label="' + this.escape(this.container.dataset.closeLabel) + '"></button></div>'
                + '<div class="modal-body"><h6>' + this.escape(task.text) + '</h6><ol class="ps-3 mb-0">' + body
                + '</ol></div></div></div>',
            modal = app.helper.showModal(html, {modalName: 'projectChartWarningsModal'});
        modal.attr('aria-labelledby', 'projectChartWarningsTitle');
        modal.one('hidden.bs.modal', () => {
            if (trigger.isConnected) {
                trigger.focus();
            }
        });
    },

    getWarnings(task) {
        const warnings = [],
            labels = this.container.dataset,
            date = value => this.formatDate(this.getDate(value));

        if (task.outside_project) {
            if (this.projectStart && task.start_date < this.getDate(this.projectStart)) {
                warnings.push({reason: labels.rangeWarning + ' ' + labels.startLabel + ': ' + this.formatDate(task.start_date)
                    + ' < ' + date(this.projectStart) + '.', action: labels.rangeRemedy});
            }

            if (this.projectEnd && this.getEndDate(task) > this.getDate(this.projectEnd)) {
                warnings.push({reason: labels.rangeWarning + ' ' + labels.endLabel + ': ' + this.formatDate(this.getEndDate(task))
                    + ' > ' + date(this.projectEnd) + '.', action: labels.rangeRemedy});
            }
        }

        if (task.dependency_violation) {
            for (const conflict of task.dependency_conflicts || []) {
                warnings.push({reason: labels.dependencyWarning + ' ' + task.text + ' (' + labels.startLabel + ': '
                    + date(conflict.target_start) + ') ≤ ' + conflict.source_name + ' (' + labels.endLabel + ': '
                    + date(conflict.source_end) + ').', action: labels.dependencyRemedy});
            }
        }

        if (task.milestone_violation) {
            for (const conflict of task.milestone_conflicts || []) {
                warnings.push({reason: labels.milestoneWarning + ' ' + conflict.name + ': ' + date(conflict.date)
                    + ' > ' + task.text + ': ' + date(conflict.deadline) + '.', action: labels.milestoneRemedy});
            }
        }

        if (task.overdue) {
            warnings.push({reason: labels.overdueLabel + '. ' + labels.endLabel + ': ' + this.formatDate(this.getEndDate(task))
                + ' < ' + labels.todayLabel + ': ' + date(this.today) + '.', action: labels.overdueRemedy});
        }

        return warnings;
    },

    registerFilters(tasks) {
        const container = this.container,
            search = container.querySelector('.project-chart-filter-search'),
            owner = container.querySelector('.project-chart-filter-owner'),
            status = container.querySelector('.project-chart-filter-status');

        if (!search || !owner || !status) {
            return;
        }

        Vtiger_AssigneeSelect_Js.register(container);
        this.addFilterOptions(status, tasks, 'status_value', 'status');
        search.value = this.filters.search;
        owner.closest('.core-assignee-select').assigneeController.setValues(this.filters.owner.split(',').filter(Boolean));
        status.value = this.filters.status;

        this.filterTasks = tasks;
        this.updateFilters();
    },

    updateFilters() {
        this.filters = {search: this.container.querySelector('.project-chart-filter-search').value,
            owner: Array.from(this.container.querySelector('.project-chart-filter-owner').selectedOptions, option => option.value).join(','),
            status: this.container.querySelector('.project-chart-filter-status').value};
        this.updateFilteredPanel(this.filterTasks);

        if (this.hasChart) {
            const selected = this.chart.getSelectedId();

            if (selected && !this.matchesFilters(this.chart.getTask(selected))) {
                this.chart.unselectTask();
            }

            this.chart.render();
        }

        if (!this.printView) {
            this.updatePrintUrl();
        }

        this.schedulePreferenceSave();
    },

    addFilterOptions(select, tasks, valueField, labelField) {
        const values = new Map(tasks.filter(task => task[valueField]).map(task => [task[valueField], task[labelField]]));

        for (const [value, label] of values) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = label;
            select.appendChild(option);
        }
    },

    matchesFilters(task) {
        if (task.is_project_range) {
            return true;
        }

        const matches = String(task.text || '').toLocaleLowerCase().includes(this.filters.search.toLocaleLowerCase())
            && (!this.filters.owner || this.filters.owner.split(',').includes(task.owner_id))
            && (!this.filters.status || task.status_value === this.filters.status);

        if (matches) {
            return true;
        }

        return task.module === 'ProjectMilestone' && this.hasChart
            && this.chart.getChildren(task.id).some(id => this.matchesFilters(this.chart.getTask(id)));
    },

    updateFilteredPanel(tasks) {
        const byId = new Map(tasks.map(task => [task.id, task]));
        let count = 0;

        for (const row of this.container.querySelectorAll('.project-chart-unscheduled-row')) {
            row.hidden = !this.matchesFilters(byId.get(row.dataset.taskId));

            if (!row.hidden) {
                count++;
            }
        }

        const counter = this.container.querySelector('.project-chart-unscheduled-count');

        if (counter) {
            counter.textContent = count;
        }
    },

    updatePrintUrl() {
        const link = this.container.querySelector('.project-chart-print-link');

        if (link) {
            const url = new URL(link.href, window.location.href);
            url.searchParams.set('chart_scope', 'current');
            url.searchParams.set('chart_scale', String(this.scale ?? 1));

            for (const [name, value] of Object.entries(this.filters)) {
                url.searchParams.set('chart_' + name, value);
            }

            if (this.hasChart && this.chart.$task?.clientWidth) {
                const scroll = this.chart.getScrollState(),
                    from = this.chart.dateFromPos(scroll.x),
                    to = this.chart.dateFromPos(scroll.x + this.chart.$task.clientWidth);

                if (from && to) {
                    url.searchParams.set('chart_from', this.getIsoDate(from));
                    url.searchParams.set('chart_to', this.getIsoDate(to));
                }
            }

            link.href = url.toString();
        }
    },

    isLinkEditable(link) {
        if (this.printView || !this.chart.config.drag_links || this.dependencySaving) {
            return false;
        }

        const source = this.chart.getTask(link.source),
            target = this.chart.getTask(link.target);

        return ['ProjectTask', 'ProjectMilestone'].includes(source.module) && source.module === target.module
            && !source.readonly && !target.readonly;
    },

    saveDependency(link, operation) {
        const source = this.chart.getTask(link.source),
            target = this.chart.getTask(link.target),
            params = {module: 'Project', action: 'Dependency', record: this.container.dataset.record,
                source: source.recordid, target: target.recordid, operation};
        this.dependencySaving = true;
        app.request.post({data: params}).then(error => {
            this.dependencySaving = false;

            if (error) {
                this.showError(error);
                this.refreshChart();
                return;
            }

            app.helper.showSuccessNotification({message: app.vtranslate('JS_RECORD_UPDATED')});
            this.refreshChart();
        }, error => {
            this.dependencySaving = false;
            this.showError(error);
            this.refreshChart();
        });
    },

    refreshChart() {
        if (this.destroyed) {
            const active = Project_Chart_Js.active;

            if (active && active !== this && active.container.dataset.record === this.container.dataset.record) {
                active.refreshChart();
            }

            return;
        }

        if (Project_Chart_Js.pendingTasks.size || this.dependencySaving) {
            return;
        }

        const detail = this.getParentInstance();

        if (detail) {
            detail.loadContents('index.php?module=Project&view=Detail&record=' + encodeURIComponent(this.container.dataset.record) + '&mode=showChart');
        }
    },

    showError(error) {
        const message = typeof error === 'string' ? error : error?.message || error?.error?.message
            || error?.responseJSON?.error?.message || error?.statusText || app.vtranslate('JS_ERROR');
        app.helper.showErrorNotification({message});
    },

    registerUnscheduledTasks(tasks) {
        this.unscheduledTasks = new Map(tasks.map(task => [task.id, task]));
    },

    formatDate(date) {
        const parts = {yyyy: date.getFullYear(), mm: String(date.getMonth() + 1).padStart(2, '0'), dd: String(date.getDate()).padStart(2, '0')};
        return this.container.dataset.dateFormat.replace(/yyyy|mm|dd/g, token => parts[token]);
    },

    getIsoDate(date) {
        return [date.getFullYear(), String(date.getMonth() + 1).padStart(2, '0'), String(date.getDate()).padStart(2, '0')].join('-');
    },

    setScale(scale, render = true) {
        const scales = [
            [{unit: 'month', step: 1, format: '%F %Y'}, {unit: 'day', step: 1, format: '%d'}],
            [{unit: 'month', step: 1, format: '%F %Y'}, {unit: 'week', step: 1, format: '%d %M'}],
            [{unit: 'year', step: 1, format: '%Y'}, {unit: 'month', step: 1, format: '%M'}]
        ];
        this.scale = Math.max(0, Math.min(2, scale));
        this.chart.config.scales = scales[this.scale];

        for (const row of this.chart.config.scales) {
            row.css = date => this.getPeriodClass(date, row.unit);
        }

        if (render) {
            this.chart.render();
            this.schedulePreferenceSave();
            this.updatePrintUrl();
        }
    },

    openRecord(task) {
        if (this.printView) {
            return;
        }

        this.viewState.selected = task.id;

        if (task.can_edit_record) {
            this.openRecordOverlay(task);
            return;
        }

        this.persistViewState(() => {
            window.location.href = 'index.php?module=' + task.module + '&view=Detail&record=' + task.recordid;
        });
    },

    openRecordOverlay(task) {
        if (this.editOpening) {
            return;
        }

        this.editOpening = true;
        app.helper.showProgress();
        app.request.post({data: {module: task.module, view: 'Edit', record: task.recordid, displayMode: 'overlay',
            returnmodule: 'Project', returnview: 'Detail', returnrecord: this.container.dataset.record,
            returnmode: 'showChart'}})
            .then((error, html) => {
                app.helper.hideProgress();

                this.editOpening = false;

                if (error || this.destroyed) {
                    if (error) {
                        this.showError(error);
                    }

                    return;
                }

                app.helper.loadPageContentOverlay(html, {backdrop: 'static', keyboard: false}).then(container => {
                    const form = container.find('form.recordEditView').addBack('form.recordEditView').first(),
                        controller = Vtiger_Edit_Js.getInstanceByModuleName(task.module);
                    controller.setModuleName(task.module);
                    controller.setEditViewContainer(form);
                    controller.formElement = form;
                    controller.registerBasicEvents(form);
                    form.vtValidate({validationMeta: controller.getValidationMeta(form), submitHandler: () => {
                        this.saveRecordOverlay(task, form);
                        return false;
                    }});
                });
            }, error => {
                this.editOpening = false;
                app.helper.hideProgress();
                this.showError(error);
            });
    },

    saveRecordOverlay(task, form) {
        if (form.data('chartSaving')) {
            return;
        }

        const event = jQuery.Event(Vtiger_Edit_Js.recordPresaveEvent);
        app.event.trigger(event);

        if (event.isDefaultPrevented()) {
            return;
        }

        form.find('textarea.ckeditor').each(function () {
            const editor = window.CKEDITOR?.instances[this.id];

            if (editor) {
                editor.updateElement();
            }
        });

        const data = new FormData(form[0]),
            buttons = form.find('[type="submit"]');
        // Use the existing AJAX save lifecycle; never follow the full-page Save redirect.
        data.set('module', task.module);
        data.set('action', 'SaveAjax');
        data.set('record', task.recordid);
        form.data('chartSaving', true);
        buttons.prop('disabled', true);
        app.helper.showProgress();
        app.request.post({data, contentType: false, processData: false}).then(error => {
            app.helper.hideProgress();
            form.data('chartSaving', false);
            buttons.prop('disabled', false);

            if (error) {
                this.showError(error);
                return;
            }

            if (jQuery('#overlayPageContent').find('form.recordEditView')[0] === form[0]) {
                app.helper.hidePageContentOverlay();
            }

            app.helper.showSuccessNotification({message: app.vtranslate('JS_RECORD_UPDATED')});
            this.refreshChart();
        }, error => {
            app.helper.hideProgress();
            form.data('chartSaving', false);
            buttons.prop('disabled', false);
            this.showError(error);
        });
    },

    saveDates(task) {
        const original = this.originalTasks.get(task.id),
            key = String(task.recordid);

        if (!original || this.printView || task.readonly || Project_Chart_Js.pendingTasks.has(key)) {
            return;
        }

        if (task.start_date.getTime() === original.start_date.getTime()
            && task.end_date.getTime() === original.end_date.getTime()) {
            return;
        }

        const params = task.module === 'ProjectMilestone'
            ? {module: 'ProjectMilestone', action: 'SaveDate', record: task.recordid, projectmilestonedate: this.formatDate(this.getEndDate(task))}
            : {module: 'ProjectTask', action: 'SaveTask', record: task.recordid,
                startdate: this.formatDate(task.start_date), enddate: this.formatDate(this.getEndDate(task))};
        Project_Chart_Js.pendingTasks.add(key);
        app.request.post({data: params}).then(error => {
            Project_Chart_Js.pendingTasks.delete(key);

            if (error) {
                this.restoreTask(task, original, error);
            } else {
                app.helper.showSuccessNotification({message: app.vtranslate('JS_RECORD_UPDATED')});
                this.refreshChart();
            }
        }, error => {
            Project_Chart_Js.pendingTasks.delete(key);
            this.restoreTask(task, original, error);
        });
    },

    restoreTask(task, original, error) {
        task.start_date = original.start_date;
        task.end_date = original.end_date;
        task.duration = original.duration;

        if (Project_Chart_Js.active === this && this.chart.isTaskExists(task.id)) {
            this.chart.updateTask(task.id);
        }

        this.showError(error);

        this.refreshChart();
    },

    destroy() {
        if (this.destroyed) {
            return;
        }

        this.viewState = this.getViewState();

        if (!this.printView && this.preferenceTimer) {
            this.persistViewState();
        }

        clearTimeout(this.preferenceTimer);
        this.destroyed = true;

        for (const [type, handler] of Object.entries(this.containerEvents || {})) {
            this.container.removeEventListener(type, handler, ['click', 'keydown'].includes(type));
        }

        this.containerEvents = null;

        if (this.container.projectChartController === this) {
            delete this.container.projectChartController;
        }

        for (const event of this.events) {
            this.chart.detachEvent(event);
        }

        if (this.hasChart) {
            this.todayMarker?.remove();
            this.chart.removeShortcut('enter', 'taskRow');
        }

        this.chart.destructor();
        this.hasChart = false;
        this.originalTasks.clear();

        if (Project_Chart_Js.active === this) {
            Project_Chart_Js.active = null;
        }
    }
});
