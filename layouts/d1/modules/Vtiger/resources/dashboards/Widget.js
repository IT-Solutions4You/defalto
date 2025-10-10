/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_Widget_Js */
Vtiger.Class('Vtiger_Widget_Js', {

    widgetPostLoadEvent: 'Vtiget.Dashboard.PostLoad',
    widgetPostRefereshEvent: 'Vtiger.Dashboard.PostRefresh',
    widgetPostResizeEvent: 'Vtiger.DashboardWidget.PostResize',

    getInstance: function (container, widgetName, moduleName) {
        if (typeof moduleName == 'undefined') {
            moduleName = app.getModuleName();
        }
        var widgetClassName = widgetName;
        var moduleClass = window[moduleName + "_" + widgetClassName + "_Widget_Js"];
        var fallbackClass = window["Vtiger_" + widgetClassName + "_Widget_Js"];
        var basicClass = Vtiger_Widget_Js;
        if (typeof moduleClass != 'undefined') {
            var instance = new moduleClass(container);
        } else if (typeof fallbackClass != 'undefined') {
            var instance = new fallbackClass(container);
        } else {
            var instance = new basicClass(container);
        }
        return instance;
    }
}, {

    container: false,
    plotContainer: false,

    init: function (container) {
        this.setContainer(jQuery(container));
        this.registerWidgetPostLoadEvent(container);
        this.registerWidgetPostRefreshEvent(container);
        this.registerWidgetPostResizeEvent(container);
    },

    getContainer: function () {
        return this.container;
    },

    setContainer: function (element) {
        this.container = element;
        return this;
    },

    isEmptyData: function () {
        var container = this.getContainer();
        return (container.find('.noDataMsg').length > 0) ? true : false;
    },

    getUserDateFormat: function () {
        return jQuery('#userDateFormat').val();
    },


    getPlotContainer: function (useCache) {
        if (typeof useCache == 'undefined') {
            useCache = false;
        }
        if (this.plotContainer == false || !useCache) {
            var container = this.getContainer();
            this.plotContainer = container.find('.widgetChartContainer');
        }
        return this.plotContainer;
    },

    restrictContentDrag: function () {
        this.getContainer().on('mousedown.draggable', function (e) {
            var element = jQuery(e.target);
            var isHeaderElement = element.closest('.dashboardWidgetHeader').length > 0 ? true : false;
            var isResizeElement = element.is(".gs-resize-handle") ? true : false;
            if (isHeaderElement || isResizeElement) {
                return;
            }
            //Stop the event propagation so that drag will not start for contents
            e.stopPropagation();
        })
    },

    convertToDateRangePicketFormat: function (userDateFormat) {
        if ('dd.mm.yyyy' === userDateFormat) {
            return 'dd.MM.yyyy';
        } else if ('mm.dd.yyyy' === userDateFormat) {
            return 'MM.dd.yyyy'
        } else if ('yyyy.mm.dd' === userDateFormat) {
            return 'yyyy.MM.dd';
        } else if ('dd/mm/yyyy' === userDateFormat) {
            return 'dd/MM/yyyy';
        } else if ('mm/dd/yyyy' === userDateFormat) {
            return 'MM/dd/yyyy'
        } else if ('yyyy/mm/dd' === userDateFormat) {
            return 'yyyy/MM/dd';
        } else if ('yyyy-mm-dd' === userDateFormat) {
            return 'yyyy-MM-dd';
        } else if ('mm-dd-yyyy' === userDateFormat) {
            return 'MM-dd-yyyy';
        } else if ('dd-mm-yyyy' === userDateFormat) {
            return 'dd-MM-yyyy';
        }
    },

    loadChart: function () {

    },

    positionNoDataMsg: function () {
        var container = this.getContainer();
        var widgetContentsContainer = container.find('.dashboardWidgetContent');
        var noDataMsgHolder = widgetContentsContainer.find('.noDataMsg');
        noDataMsgHolder.position({
            'my': 'center center',
            'at': 'center center',
            'of': widgetContentsContainer
        })
    },

    postInitializeCalls: function () {
    },

    //Place holdet can be extended by child classes and can use this to handle the post load
    postLoadWidget: function () {
        if (!this.isEmptyData()) {
            this.loadChart();
            this.postInitializeCalls();
        } else {
            //this.positionNoDataMsg();
        }
        this.registerFilter();
        this.registerFilterChangeEvent();
        this.restrictContentDrag();
    },

    postResizeWidget: function () {
        if (!this.isEmptyData()) {
            this.loadChart();
            this.postInitializeCalls();
        } else {
            //this.positionNoDataMsg();
        }
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        widgetContent.css({height: "100%"});
    },

    postRefreshWidget: function () {
        if (!this.isEmptyData()) {
            this.loadChart();
            this.postInitializeCalls();
        } else {
//			this.positionNoDataMsg();
        }
    },

    getFilterData: function () {
        return {};
    },

    refreshWidget: function () {
        let parent = this.getContainer(),
            element = parent.find('a[name="drefresh"]'),
            url = element.data('url'),
            contentContainer = parent.find('.dashboardWidgetContent'),
            widgetFilters = parent.find('.widgetFilter'),
            params = {
                url: url
            };

        if (widgetFilters.length > 0) {
            params.url = url;
            params.data = {};
            widgetFilters.each(function (index, domElement) {
                let widgetFilter = jQuery(domElement);

                //Filter unselected checkbox, radio button elements
                if ((widgetFilter.is(":radio") || widgetFilter.is(":checkbox")) && !widgetFilter.is(":checked")) {
                    return true;
                }
                if (widgetFilter.is('.dateRange')) {
                    let name = widgetFilter.attr('name'),
                        start = widgetFilter.find('input[name="start"]').val(),
                        end = widgetFilter.find('input[name="end"]').val();

                    if (start.length <= 0 || end.length <= 0) {
                        return true;
                    }

                    params.data[name] = {};
                    params.data[name].start = start;
                    params.data[name].end = end;
                } else {
                    let filterName = widgetFilter.attr('name');

                    params.data[filterName] = widgetFilter.val();
                }
            });
        }

        let filterData = this.getFilterData();

        if (!jQuery.isEmptyObject(filterData)) {
            if (typeof params == 'string') {
                url = params;
                params = {};
                params.url = url;
                params.data = {};
            }
            params.data = jQuery.extend(params.data, this.getFilterData())
        }

        //Sending empty object in data results in invalid request
        if (jQuery.isEmptyObject(params.data)) {
            delete params.data;
        }

        app.helper.showProgress();
        app.request.post(params).then(function (err, data) {
            app.helper.hideProgress();

            contentContainer.html(data);
            contentContainer.find('.widgetChartContainer').css('height', parent.height() - 60);
            contentContainer.trigger(Vtiger_Widget_Js.widgetPostRefereshEvent);
        },);
    },

    registerFilter: function () {
        let thisInstance = this,
            container = this.getContainer(),
            dateRangeElement = container.find('.input-daterange');

        if (dateRangeElement.length <= 0) {
            return;
        }

        dateRangeElement.each(function () {
            let dateRangeElement = $(this),
                startDate = dateRangeElement.find('input[name="start"]'),
                endDate = dateRangeElement.find('input[name="end"]'),
                pickerParams = {
                    format: thisInstance.getUserDateFormat(),
                };

            startDate.addClass('dateField');
            endDate.addClass('dateField');

            vtUtils.registerEventForDateFields(startDate, pickerParams);
            vtUtils.registerEventForDateFields(endDate, pickerParams);

            dateRangeElement.on('changeDate', function (e) {
                let start = dateRangeElement.find('input[name="start"]').val(),
                    end = dateRangeElement.find('input[name="end"]').val();

                if (start && end && start !== end) {
                    container.find('a[name="drefresh"]').trigger('click');
                }
            });
            dateRangeElement.attr('data-date-format', thisInstance.getUserDateFormat());
        })
    },

    registerFilterChangeEvent: function () {
        this.getContainer().on('change', '.widgetFilter, .reloadOnChange', function (e) {
            var target = jQuery(e.currentTarget);
            if (target.hasClass('dateRange')) {
                var start = target.find('input[name="start"]').val();
                var end = target.find('input[name="end"]').val();
                if (start == '' || end == '') return false;
            }

            var widgetContainer = target.closest('li');
            widgetContainer.find('a[name="drefresh"]').trigger('click');
        })
    },

    registerWidgetPostLoadEvent: function (container) {
        var thisInstance = this;
        container.off(Vtiger_Widget_Js.widgetPostLoadEvent).on(Vtiger_Widget_Js.widgetPostLoadEvent, function (e) {
            thisInstance.postLoadWidget();
        })
    },

    registerWidgetPostRefreshEvent: function (container) {
        var thisInstance = this;
        container.on(Vtiger_Widget_Js.widgetPostRefereshEvent, function (e) {
            thisInstance.postRefreshWidget();
        });
    },

    registerWidgetPostResizeEvent: function (container) {
        var thisInstance = this;
        container.on(Vtiger_Widget_Js.widgetPostResizeEvent, function (e) {
            thisInstance.postResizeWidget();
        });
    },

    openUrl: function (url) {
        var win = window.open(url, '_blank');
        win.focus();
    },
    onClickEvent(event, elements) {
        if (!elements.length) {
            return;
        }

        const data = this['config']['_config']['data'];
        const dataIndex = elements[0]['index'];
        const datasetIndex = elements[0]['datasetIndex'];

        if (data['datasets'][datasetIndex]['links'][dataIndex]) {
            window.location.href = data['datasets'][datasetIndex]['links'][dataIndex];
        }
    },
    getWidgetData: function () {
        return JSON.parse(this.getContainer().find('.widgetData').val());
    },
});


Vtiger_Widget_Js('Vtiger_KeyMetrics_Widget_Js', {}, {
    postLoadWidget: function () {
        this._super();

        let widgetContent = jQuery('.dashboardWidgetContent', this.getContainer()),
            adjustedHeight = '100%';

        app.helper.showVerticalScroll(widgetContent, {'setHeight': adjustedHeight});
    },

    postResizeWidget: function () {
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 20;
        widgetContent.css({height: adjustedHeight});
        slimScrollDiv.css({height: adjustedHeight});
    }
});

Vtiger_Widget_Js('Vtiger_TopPotentials_Widget_Js', {}, {

    postLoadWidget: function () {
        this._super();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 50;
        app.helper.showVerticalScroll(widgetContent, {'setHeight': adjustedHeight});
        widgetContent.css({height: widgetContent.height() - 40});
    }
});

Vtiger_Widget_Js('Vtiger_History_Widget_Js', {}, {

    postLoadWidget: function () {
        this._super();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 110;
        app.helper.showVerticalScroll(widgetContent, {'setHeight': adjustedHeight});
        widgetContent.css({height: adjustedHeight});
        this.registerLoadMore();
    },

    postResizeWidget: function () {
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 110;
        widgetContent.css({height: adjustedHeight});
        slimScrollDiv.css({height: adjustedHeight});
    },

    initSelect2Elements: function (widgetContent) {
        var container = widgetContent.closest('.dashboardWidget');
        var select2Elements = container.find('.select2');
        if (select2Elements.length > 0 && jQuery.isArray(select2Elements)) {
            select2Elements.each(function (index, domElement) {
                domElement.chosen();
            });
        } else {
            select2Elements.chosen();
        }
    },

    postRefreshWidget: function () {
        this._super();
        this.registerLoadMore();
    },

    registerLoadMore: function () {
        var thisInstance = this;
        var parent = thisInstance.getContainer();
        var contentContainer = parent.find('.dashboardWidgetContent');

        var loadMoreHandler = contentContainer.find('.load-more');
        loadMoreHandler.off('click');
        loadMoreHandler.click(function () {
            var parent = thisInstance.getContainer();
            var element = parent.find('a[name="drefresh"]');
            var url = element.data('url');
            var params = url;

            var widgetFilters = parent.find('.widgetFilter');
            if (widgetFilters.length > 0) {
                params = {url: url, data: {}};
                widgetFilters.each(function (index, domElement) {
                    var widgetFilter = jQuery(domElement);
                    //Filter unselected checkbox, radio button elements
                    if ((widgetFilter.is(":radio") || widgetFilter.is(":checkbox")) && !widgetFilter.is(":checked")) {
                        return true;
                    }

                    if (widgetFilter.is('.dateRange')) {
                        var name = widgetFilter.attr('name');
                        var start = widgetFilter.find('input[name="start"]').val();
                        var end = widgetFilter.find('input[name="end"]').val();
                        if (start.length <= 0 || end.length <= 0) {
                            return true;
                        }

                        params.data[name] = {};
                        params.data[name].start = start;
                        params.data[name].end = end;
                    } else {
                        var filterName = widgetFilter.attr('name');
                        var filterValue = widgetFilter.val();
                        params.data[filterName] = filterValue;
                    }
                });
            }

            var filterData = thisInstance.getFilterData();
            if (!jQuery.isEmptyObject(filterData)) {
                if (typeof params == 'string') {
                    params = {url: url, data: {}};
                }
                params.data = jQuery.extend(params.data, thisInstance.getFilterData())
            }

            // Next page.
            params.data['page'] = loadMoreHandler.data('nextpage');

            app.helper.showProgress();
            app.request.post(params).then(function (err, data) {
                app.helper.hideProgress();
                loadMoreHandler.parent().parent().replaceWith(jQuery(data).html());
                thisInstance.registerLoadMore();
            }, function () {
                app.helper.hideProgress();
            });
        });
    }

});


Vtiger_Widget_Js('Vtiger_Funnel_Widget_Js', {}, {
    generateData: function () {
        let self = this,
            data = self.getWidgetData(),
            labels = [],
            datasets = [
                {data: [], links: []}
            ];

        $.each(data, function (index, value) {
            labels[index] = value[2];
            datasets[0]['data'][index] = value[1];
            datasets[0]['links'][index] = value[3];
        });

        return {
            labels: labels,
            datasets: datasets,
        }
    },
    loadChart: function () {
        let self = this,
            plot = self.getPlotContainer(false),
            data = self.generateData();

        app.helper.showChart(plot, {
            type: 'funnel',
            data: data,
            options: {
                indexAxis: 'y',
                onClick: self.onClickEvent,
            },
        });
    }
});


Vtiger_Widget_Js('Vtiger_Pie_Widget_Js', {}, {
    labelField: 'name',
    datasetNumberField: 'count',
    /**
     * Function which will give chart related Data
     */
    generateData() {
        let self = this,
            data = self.getWidgetData(),
            chartData = [],
            chartLinks = [],
            chartLabels = [];

        $.each(data, function (index, row) {
            chartLabels.push(row[self.labelField]);
            chartData.push(row[self.datasetNumberField]);
            chartLinks.push(row['links']);
        });

        return {
            labels: chartLabels,
            datasets: [{
                data: chartData,
                links: chartLinks,
            }],
        };
    },
    loadChart: function () {
        let self = this,
            plot = self.getPlotContainer(false),
            data = self.generateData();

        app.helper.showChart(plot, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    },
                },
                onClick: self.onClickEvent,
            },
        });
    }
});


Vtiger_Widget_Js('Vtiger_Barchat_Widget_Js', {}, {
    generateData() {
        let self = this,
            data = self.getWidgetData();

        let labels = [],
            datasets = [{
                links: [],
                label: '',
                showLine: false,
                data: [],
            }];

        $.each(data, function (index, value) {
            labels[index] = app.getDecodedValue(value[1]);
            datasets[0]['links'][index] = value['links'];
            datasets[0]['label'] = ''
            datasets[0]['data'][index] = parseInt(value[0]);
        })

        return {
            labels: labels,
            datasets: datasets,
        }
    },
    getMaxValue(values) {
        let max = 100;

        if (values) {
            max = Math.max.apply(null, values);
            max = parseInt(max + 2 + (max / 100 * 25));
        }

        return max;
    },
    loadChart: function () {
        let plot = this.getPlotContainer(false),
            data = this.generateData(),
            max = this.getMaxValue(data['datasets'][0]['data']);

        plot.addClass('w-100');

        app.helper.showChart(this.getPlotContainer(false), {
            type: 'bar',
            data: data,
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                onClick: this.onClickEvent,
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        max: max,
                        stacked: true
                    }
                },
            }
        });
    }

});
/** @var Vtiger_MultiBarchat_Widget_Js */
Vtiger_Widget_Js('Vtiger_MultiBarchat_Widget_Js', {
    labelField: 'name',
    datasetLabelField: 'stage',
    datasetNumberField: 'number',
    /**
     * Function which will give char related Data like data , x labels and legend labels as map
     */
    generateData: function() {
        let self = this,
            widgetData = self.getWidgetData(),
            labels = [],
            datasetLabels = [],
            datasets = [];

        $.each(widgetData, function (index, value) {
            if (-1 === $.inArray(value[self.labelField], labels)) {
                labels.push(value[self.labelField]);
            }

            if (-1 === $.inArray(value[self.datasetLabelField], datasetLabels)) {
                datasetLabels.push(value[self.datasetLabelField]);
            }
        });

        $.each(datasetLabels, function (datasetIndex, datasetLabel) {
            let data = [],
                links = [];

            $.each(labels, function (labelIndex, label) {
                links[labelIndex] = self.getDatasetLinks(widgetData, label, datasetLabel);
                data[labelIndex] = self.getDatasetNumber(widgetData, label, datasetLabel);
            });

            datasets[datasetIndex] = {
                label: datasetLabel,
                data: data,
                links: links,
            };
        })

        return {
            labels: labels,
            datasets: datasets,
        }
    },
    getDatasetNumber: function (widgetData, label, datasetLabel) {
        const self = this,
            info = self.getDatasetInfo(widgetData, label, datasetLabel),
            number = info[self.datasetNumberField];

        return number ?? 0;
    },
    getDatasetLinks: function (widgetData, label, datasetLabel) {
        return this.getDatasetInfo(widgetData, label, datasetLabel)['links'];
    },
    getDatasetInfo(widgetData, label, datasetLabel) {
        let self = this,
            info = {};

        $.each(widgetData, function (index, value) {
            if (value[self.labelField] === label && value[self.datasetLabelField] === datasetLabel) {
                info = value;
            }
        });

        return info;
    },
    loadChart: function () {
        let self = this,
            plot = this.getPlotContainer(false),
            chartData = self.generateData();

        plot.addClass('w-100');

        app.helper.showChart(plot, {
            type: 'bar',
            data: chartData,
            options: {
                onClick: self.onClickEvent,
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true
                    }
                }
            }
        });
    }

});

// NOTE Widget-class name camel-case convention
Vtiger_Widget_Js('Vtiger_MiniList_Widget_Js', {

    registerMoreClickEvent: function (e) {
        var moreLink = jQuery(e.currentTarget);
        var linkId = moreLink.data('linkid');
        var widgetId = moreLink.data('widgetid');
        var currentPage = jQuery('#widget_' + widgetId + '_currentPage').val();
        var nextPage = parseInt(currentPage) + 1;
        var params = {
            'module': app.getModuleName(),
            'view': 'ShowWidget',
            'name': 'MiniList',
            'linkid': linkId,
            'widgetid': widgetId,
            'content': 'data',
            'currentPage': currentPage
        }
        app.request.post({"data": params}).then(function (err, data) {
            var htmlData = jQuery(data);
            var htmlContent = htmlData.find('.miniListContent');
            moreLink.parent().before(htmlContent);
            jQuery('#widget_' + widgetId + '_currentPage').val(nextPage);
            var moreExists = htmlData.find('.moreLinkDiv').length;
            if (!moreExists) {
                moreLink.parent().remove();
            }
        });
    }

}, {
    postLoadWidget: function () {
        app.helper.hideModal();
        this.restrictContentDrag();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 50;
        app.helper.showVerticalScroll(widgetContent, {'setHeight': adjustedHeight});
        widgetContent.css({height: widgetContent.height() - 40});
    },

    postResizeWidget: function () {
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 100;
        widgetContent.css({height: adjustedHeight});
        slimScrollDiv.css({height: adjustedHeight});
    }
});

Vtiger_Widget_Js('Vtiger_TagCloud_Widget_Js', {}, {

    postLoadWidget: function () {
        this._super();
        this.registerTagCloud();
        this.registerTagClickEvent();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 50;
        app.helper.showVerticalScroll(widgetContent, {'setHeight': adjustedHeight});
        widgetContent.css({height: widgetContent.height() - 40});
    },

    registerTagCloud: function () {
        jQuery('#tagCloud').find('a').tagcloud({
            size: {
                start: parseInt('12'),
                end: parseInt('30'),
                unit: 'px'
            },
            color: {
                start: "#0266c9",
                end: "#759dc4"
            }
        });
    },

    registerChangeEventForModulesList: function () {
        jQuery('#tagSearchModulesList').on('change', function (e) {
            var modulesSelectElement = jQuery(e.currentTarget);
            if (modulesSelectElement.val() == 'all') {
                jQuery('[name="tagSearchModuleResults"]').removeClass('hide');
            } else {
                jQuery('[name="tagSearchModuleResults"]').removeClass('hide');
                var selectedOptionValue = modulesSelectElement.val();
                jQuery('[name="tagSearchModuleResults"]').filter(':not(#' + selectedOptionValue + ')').addClass('hide');
            }
        });
    },

    registerTagClickEvent: function () {
        var thisInstance = this;
        var container = this.getContainer();
        container.on('click', '.tagName', function (e) {
            var tagElement = jQuery(e.currentTarget);
            var tagId = tagElement.data('tagid');
            var params = {
                'module': app.getModuleName(),
                'view': 'TagCloudSearchAjax',
                'tag_id': tagId,
                'tag_name': tagElement.text()
            }
            app.request.post({"data": params}).then(
                function (err, data) {
                    app.helper.showModal(data);
                    vtUtils.applyFieldElementsView(jQuery(".myModal"));
                    thisInstance.registerChangeEventForModulesList();
                }
            )
        });
    },

    postRefreshWidget: function () {
        this._super();
        this.registerTagCloud();
    },

    postResizeWidget: function () {
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 20;
        widgetContent.css({height: adjustedHeight});
        slimScrollDiv.css({height: adjustedHeight});
    }
});

/* Notebook Widget */
Vtiger_Widget_Js('Vtiger_Notebook_Widget_Js', {}, {

    // Override widget specific functions.
    postLoadWidget: function () {
        this.reinitNotebookView();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 50;
        app.helper.showVerticalScroll(widgetContent, {'setHeight': adjustedHeight});
        //widgetContent.css({height: widgetContent.height()-40});
    },

    postResizeWidget: function () {
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var slimScrollDiv = jQuery('.slimScrollDiv', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 100;
        widgetContent.css({height: adjustedHeight});
        slimScrollDiv.css({height: adjustedHeight});
        widgetContent.find('.dashboard_notebookWidget_viewarea').css({height: adjustedHeight});
    },

    postRefreshWidget: function () {
        this.reinitNotebookView();
        var widgetContent = jQuery('.dashboardWidgetContent', this.getContainer());
        var adjustedHeight = this.getContainer().height() - 50;
        app.helper.showVerticalScroll(widgetContent, {'setHeight': adjustedHeight});
    },

    reinitNotebookView: function () {
        var self = this;
        jQuery('.dashboard_notebookWidget_edit', this.container).click(function () {
            self.editNotebookContent();
        });
        jQuery('.dashboard_notebookWidget_save', this.container).click(function () {
            self.saveNotebookContent();
        });
    },

    editNotebookContent: function () {
        jQuery('.dashboard_notebookWidget_text', this.container).show();
        jQuery('.dashboard_notebookWidget_view', this.container).hide();
    },

    saveNotebookContent: function () {
        var self = this;
        var refreshContainer = this.container.find('.refresh');
        var textarea = jQuery('.dashboard_notebookWidget_textarea', this.container);

        var url = this.container.data('url');
        var params = url + '&content=true&mode=save&contents=' + textarea.val();

        app.helper.showProgress();
        app.request.post({"url": params}).then(function (err, data) {
            app.helper.hideProgress();
            var parent = self.getContainer();
            var widgetContent = parent.find('.dashboardWidgetContent');
            widgetContent.mCustomScrollbar('destroy');
            widgetContent.html(data);
            var adjustedHeight = parent.height() - 50;
            app.helper.showVerticalScroll(widgetContent, {'setHeight': adjustedHeight});

            self.reinitNotebookView();
        });
    },

    refreshWidget: function () {
        var parent = this.getContainer();
        var element = parent.find('a[name="drefresh"]');
        var url = element.data('url');

        var contentContainer = parent.find('.dashboardWidgetContent');
        var params = {};
        params.url = url;

        app.helper.showProgress();
        app.request.post(params).then(
            function (err, data) {
                app.helper.hideProgress();

                if (contentContainer.closest('.mCustomScrollbar').length) {
                    contentContainer.mCustomScrollbar('destroy');
                    contentContainer.html(data);
                    var adjustedHeight = parent.height() - 50;
                    app.helper.showVerticalScroll(contentContainer, {'setHeight': adjustedHeight});
                } else {
                    contentContainer.html(data);
                }

                contentContainer.trigger(Vtiger_Widget_Js.widgetPostRefereshEvent);
            }
        );
    },
});
