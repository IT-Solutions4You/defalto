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
    },

    /**
     * Function to get records according to ticket status
     */
    registerStatusChangeEventForWidget: function () {
        const self = this;

        self.getContainer().off('change.projectWidget', '[name="ticketstatus"],[name="projecttaskstatus"],[name="projecttaskprogress"]')
            .on('change.projectWidget', '[name="ticketstatus"],[name="projecttaskstatus"],[name="projecttaskprogress"]', function (e) {
            let picklistName = this.name,
                statusCondition = {},
                params = {},
                currentElement = jQuery(e.currentTarget),
                form = currentElement.closest('form'),
                summaryWidgetContainer = currentElement.closest('.summaryWidgetContainer'),
                widgetDataContainer = summaryWidgetContainer.find('.widget_contents'),
                referenceModuleName = widgetDataContainer.find('[name="relatedModule"]').val(),
                recordId = form.find('[name="record"]').val() ?? app.getRecordId(),
                module = form.find('[name="module"]').val() ?? app.getModuleName(),
                selectedStatus = currentElement.find('option:selected').val(),
                searchInfo = null;

            if (selectedStatus.length > 0 && referenceModuleName == "HelpDesk") {
                searchInfo = [];
                searchInfo.push('ticketstatus');
                searchInfo.push('e');
                searchInfo.push(selectedStatus);
                statusCondition['ticketstatus'] = searchInfo;
                params['whereCondition'] = JSON.stringify(statusCondition);
            } else if (referenceModuleName == "ProjectTask" && picklistName == 'projecttaskstatus') {
                if (selectedStatus.length > 0) {
                    searchInfo = [];
                    searchInfo.push('projecttaskstatus');
                    searchInfo.push('e');
                    searchInfo.push(selectedStatus);
                    statusCondition['projecttaskstatus'] = searchInfo;
                    params['whereCondition'] = JSON.stringify(statusCondition);
                }
                jQuery('[name="projecttaskprogress"]').val('').select2("val", '');
            } else if (referenceModuleName == "ProjectTask" && picklistName == 'projecttaskprogress') {
                if (selectedStatus.length > 0) {
                    searchInfo = [];
                    searchInfo.push('projecttaskprogress');
                    searchInfo.push('e');
                    searchInfo.push(selectedStatus);
                    statusCondition['projecttaskprogress'] = searchInfo;
                    params['whereCondition'] = JSON.stringify(statusCondition);
                }
                jQuery('[name="projecttaskstatus"]').val('').select2("val", '');
            }

            params['record'] = recordId;
            params['view'] = 'Detail';
            params['module'] = module;
            params['page'] = widgetDataContainer.find('[name="page"]').val();
            params['limit'] = widgetDataContainer.find('[name="pageLimit"]').val();
            params['relatedModule'] = referenceModuleName;
            params['mode'] = 'showRelatedRecords';

            app.helper.showProgress();
            app.request.post({data: params}).then(
                function (error, data) {
                    app.helper.hideProgress();
                    widgetDataContainer.html(data);
                }
            );
        })
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

            vtUtils.hideValidationMessage(editElement);

            if (element.data('mandatory') && newValue.length <= 0) {
                let result = app.vtranslate('JS_REQUIRED_FIELD');
                vtUtils.showValidationMessage(editElement, result);
                return false;
            }

            if (oldValue !== newValue) {
                let params = {
                    action: 'SaveAjax',
                    record: element.data('recordid'),
                    field: element.data('fieldname'),
                    value: newValue,
                    module: 'ProjectTask'
                };
                app.helper.showProgress();
                app.request.post({data: params}).then(function (error, data) {
                    app.helper.hideProgress();
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
                selectedStatus = jQuery('[name="projecttaskstatus"]', widgetHeaderContainer).val();

            if (typeof selectedStatus != "undefined" && selectedStatus.length > 0) {
                statusCondition['vtiger_projecttask.projecttaskstatus'] = selectedStatus;
                params['whereCondition'] = statusCondition;
            }

            let selectedProgress = jQuery('[name="projecttaskprogress"]', widgetHeaderContainer).val();

            if (typeof selectedProgress != "undefined" && selectedProgress.length > 0) {
                statusCondition['vtiger_projecttask.projecttaskprogress'] = selectedProgress;
                params['whereCondition'] = statusCondition;
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
            widgetDataContainer.html(data);
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
