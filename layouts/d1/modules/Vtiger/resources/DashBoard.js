/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_DashBoard_Js */
Vtiger.Class("Vtiger_DashBoard_Js", {

    gridster: false,

    //static property which will store the instance of dashboard
    currentInstance: false,
    dashboardTabsLimit: 10,

    addWidget: function (element, url) {
        var element = jQuery(element);
        var linkId = element.data('linkid');
        var name = element.data('name');

        // After adding widget, we should remove that widget from Add Widget drop down menu from active tab
        var activeTabId = Vtiger_DashBoard_Js.currentInstance.getActiveTabId();
        jQuery('a[data-name="' + name + '"]', "#tab_" + activeTabId).parent().hide();
        var widgetContainer = jQuery('<li class="new dashboardWidget loadcompleted rounded bg-body d-flex flex-column" id="' + linkId + '" data-name="' + name + '" data-mode="open"></li>');
        widgetContainer.data('url', url);
        var width = element.data('width');
        var height = element.data('height');
        Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
        Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);
    },

    addMiniListWidget: function (element, url) {
        // 1. Show popup window for selection (module, filter, fields)
        // 2. Compute the dynamic mini-list widget url
        // 3. Add widget with URL to the page.

        element = jQuery(element);

        app.request.post({"url": "index.php?module=Home&view=MiniListWizard&step=step1"}).then(function (err, res) {
            var callback = function (data) {
                var wizardContainer = jQuery(data);
                var form = jQuery('form', wizardContainer);

                var moduleNameSelectDOM = jQuery('select[name="module"]', wizardContainer);
                var filteridSelectDOM = jQuery('select[name="filterid"]', wizardContainer);
                var fieldsSelectDOM = jQuery('select[name="fields"]', wizardContainer);

                var moduleNameSelect2 = vtUtils.showSelect2ElementView(moduleNameSelectDOM, {
                    placeholder: app.vtranslate('JS_SELECT_MODULE')
                });
                var filteridSelect2 = vtUtils.showSelect2ElementView(filteridSelectDOM, {
                    placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION')
                });
                var fieldsSelect2 = vtUtils.showSelect2ElementView(fieldsSelectDOM, {
                    placeholder: app.vtranslate('JS_PLEASE_SELECT_ATLEAST_ONE_OPTION'),
                    closeOnSelect: true,
                    maximumSelectionSize: 2
                });
                var footer = jQuery('.modal-footer', wizardContainer);

                filteridSelectDOM.closest('tr').hide();
                fieldsSelectDOM.closest('tr').hide();
                footer.hide();

                moduleNameSelect2.change(function () {
                    if (!moduleNameSelect2.val()) return;

                    var moduleNameSelect2Params = {
                        module: 'Home',
                        view: 'MiniListWizard',
                        step: 'step2',
                        selectedModule: moduleNameSelect2.val()
                    };

                    app.request.post({"data": moduleNameSelect2Params}).then(function (err, res) {
                        filteridSelectDOM.empty().html(res).trigger('change');
                        filteridSelect2.closest('tr').show();
                        fieldsSelect2.closest('tr').hide();
                        footer.hide();
                    })
                });
                filteridSelect2.change(function () {
                    if (!filteridSelect2.val()) return;

                    var selectedModule = moduleNameSelect2.val();
                    var filteridSelect2Params = {
                        module: 'Home',
                        view: 'MiniListWizard',
                        step: 'step3',
                        selectedModule: selectedModule,
                        filterid: filteridSelect2.val()
                    };

                    app.request.post({"data": filteridSelect2Params}).then(function (err, res) {
                        fieldsSelectDOM.empty().html(res).trigger('change');
                        var translatedModuleNames = JSON.parse(jQuery("#minilistWizardContainer").find("#translatedModuleNames").val());
                        var fieldsLabelText = app.vtranslate('JS_EDIT_FIELDS', translatedModuleNames[selectedModule], translatedModuleNames[selectedModule]);
                        fieldsSelect2.closest('tr').find('.fieldLabel label').text(fieldsLabelText);
                        fieldsSelect2.closest('tr').show();
                    });
                });
                fieldsSelect2.change(function () {
                    if (!fieldsSelect2.val()) {
                        footer.hide();
                    } else {
                        footer.show();
                    }
                });

                form.submit(function (e) {
                    e.preventDefault();
                    //To disable savebutton after one submit to prevent multiple submits
                    jQuery("[name='saveButton']").attr('disabled', 'disabled');
                    var selectedModule = moduleNameSelect2.val();
                    var selectedFilterId = filteridSelect2.val();
                    var selectedFields = fieldsSelect2.val();
                    if (typeof selectedFields != 'object') selectedFields = [selectedFields];

                    // TODO mandatory field validation

                    finializeAdd(selectedModule, selectedFilterId, selectedFields);
                });
            }
            app.helper.showModal(res, {"cb": callback});
        });

        function finializeAdd(moduleName, filterid, fields) {
            var data = {
                module: moduleName
            }
            if (typeof fields != 'object') fields = [fields];
            data['fields'] = fields;

            url += '&filterid=' + filterid + '&data=' + JSON.stringify(data);
            var linkId = element.data('linkid');
            var name = element.data('name');
            var widgetContainer = jQuery('<li class="new dashboardWidget loadcompleted rounded bg-body d-flex flex-column" id="' + linkId + "-" + filterid + '" data-name="' + name + '" data-mode="open"></li>');
            widgetContainer.data('url', url);
            var width = element.data('width');
            var height = element.data('height');
            Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
            Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);
            app.helper.hideModal();
        }
    },

    addNoteBookWidget: function (element, url) {
        // 1. Show popup window for selection (module, filter, fields)
        // 2. Compute the dynamic mini-list widget url
        // 3. Add widget with URL to the page.

        element = jQuery(element);


        app.request.get({"url": "index.php?module=Home&view=AddNotePad"}).then(function (err, res) {
            var callback = function (data) {
                var wizardContainer = jQuery(data);
                var form = jQuery('form', wizardContainer);
                var params = {
                    submitHandler: function (form) {
                        //To prevent multiple click on save
                        var form = jQuery(form);
                        jQuery("[name='saveButton']").attr('disabled', 'disabled');
                        var notePadName = form.find('[name="notePadName"]').val();
                        var notePadContent = form.find('[name="notePadContent"]').val();
                        var linkId = element.data('linkid');
                        var noteBookParams = {
                            'module': app.getModuleName(),
                            'action': 'NoteBook',
                            'mode': 'NoteBookCreate',
                            'notePadName': notePadName,
                            'notePadContent': notePadContent,
                            'linkId': linkId,
                            'tab': jQuery(".tab-pane.active").data("tabid")
                        }
                        app.request.post({"data": noteBookParams}).then(function (err, data) {
                            if (data) {
                                var widgetId = data.widgetId;
                                app.helper.hideModal();

                                url += '&widgetid=' + widgetId;

                                var name = element.data('name');
                                var widgetContainer = jQuery('<li class="new dashboardWidget loadcompleted rounded bg-body d-flex flex-column" id="' + linkId + "-" + widgetId + '" data-name="' + name + '" data-mode="open"></li>');
                                widgetContainer.data('url', url);
                                var width = element.data('width');
                                var height = element.data('height');
                                Vtiger_DashBoard_Js.gridster.add_widget(widgetContainer, width, height);
                                Vtiger_DashBoard_Js.currentInstance.loadWidget(widgetContainer);
                            }
                        });
                        return false;
                    }
                }
                form.vtValidate(params);
            }
            app.helper.showModal(res, {"cb": callback});
        });

    }

}, {


    container: false,
    instancesCache: {},

    init: function () {
        Vtiger_DashBoard_Js.currentInstance = this;
        this.addComponents();
    },

    addComponents: function () {
        this.addComponent('Vtiger_Index_Js');
    },

    getDashboardContainer: function () {
        return jQuery(".dashBoardContainer");
    },

    getContainer: function (tabid) {
        if (typeof tabid == 'undefined') {
            tabid = this.getActiveTabId();
        }
        return jQuery(".gridster_" + tabid).find('ul');
    },

    getWidgetInstance: function (widgetContainer) {
        var id = widgetContainer.attr('id');
        if (!(id in this.instancesCache)) {
            var widgetName = widgetContainer.data('name');
            this.instancesCache[id] = Vtiger_Widget_Js.getInstance(widgetContainer, widgetName);
        } else {
            this.instancesCache[id].init(widgetContainer);
        }
        return this.instancesCache[id];
    },

    getActiveTabId: function () {
        return jQuery(".tab-pane.active").data("tabid");
    },

    getActiveTabName: function () {
        return jQuery(".tab-pane.active").data("tabname");
    },

    getgridColumns: function () {
        var _device_width = $(window).innerWidth();
        var gridWidth = _device_width;

        if (_device_width < 480) {
            gridWidth = 1;
        } else if (_device_width >= 480 && _device_width < 768) {
            gridWidth = 1;
        } else if (_device_width >= 768 && _device_width < 992) {
            gridWidth = 2;
        } else if (_device_width >= 992 && _device_width < 1440) {
            gridWidth = 3;
        } else {
            gridWidth = 4;
        }
        return gridWidth;
    },

    saveWidgetSize: function (widget) {
        const dashboardTabId = widget.closest('.tab-pane.active').data('tabid'),
            widgetSize = {
                'sizex': widget.attr('data-sizex'),
                'sizey': widget.attr('data-sizey')
            };

        if (widgetSize.sizex && widgetSize.sizey) {
            var params = {
                'module': 'Vtiger',
                'action': 'SaveWidgetSize',
                'id': widget.attr('id'),
                'size': widgetSize,
                'tabid': dashboardTabId
            };
            return app.request.post({"data": params});
        }

        return jQuery.Deferred().resolve(true).promise();
    },

    saveWidgetLayout: function (widgets, changedWidget, resized) {
        this.showWidgetPlaceholder(changedWidget, 'JS_SAVING_PLEASE_WAIT', 'fa-spinner fa-spin');
        app.helper.showProgress(app.vtranslate('JS_SAVING_PLEASE_WAIT'));
        const thisInstance = this,
            requests = [this.savePositions(widgets)];

        if (resized) {
            requests.push(this.saveWidgetSize(changedWidget));
        }

        let pending = requests.length,
            failed = false;

        jQuery.each(requests, function (index, request) {
            request.then(function (err) {
                failed = failed || !!err;
                pending--;

                if (pending > 0) {
                    return;
                }

                app.helper.hideProgress();

                if (failed) {
                    thisInstance.showWidgetContent(changedWidget);
                    app.helper.showErrorNotification({message: app.vtranslate('JS_FAILED_TO_SAVE')});
                } else {
                    app.helper.showSuccessNotification({message: app.vtranslate('JS_SAVED_SUCCESSFULLY')});
                    thisInstance.showWidgetPlaceholder(changedWidget, 'JS_LOADING_PLEASE_WAIT', 'fa-spinner fa-spin');
                    thisInstance.loadWidget(changedWidget, true).then(function () {
                        thisInstance.showWidgetContent(changedWidget);
                    });
                }
            });
        });
    },

    showWidgetPlaceholder: function (widget, label, icon, showHint) {
        widget.find('.dashboardLayoutPlaceholder').remove();
        const placeholder = jQuery('<div>', {
                class: 'dashboardLayoutPlaceholder flex-grow-1 d-flex flex-column align-items-center justify-content-center gap-2 m-2 p-3 border rounded bg-body-tertiary text-secondary text-center',
                role: 'status',
                'aria-live': 'polite'
            }),
            iconElement = jQuery('<i>', {class: 'fa fa-2x text-primary ' + icon, 'aria-hidden': 'true'});

        placeholder.append(iconElement, jQuery('<span>', {class: 'fw-semibold', text: app.vtranslate(label)}));

        if (showHint) {
            placeholder.append(jQuery('<small>', {text: app.vtranslate('JS_DASHBOARD_RELEASE_TO_SAVE')}));
        }

        widget.find('.dashboardWidgetContent').first().before(placeholder);
    },

    hideWidgetContent: function (widget, resized) {
        widget.find('.dashboardWidgetContent').addClass('hide');
        widget.attr('aria-busy', 'true');
        this.showWidgetPlaceholder(widget,
            resized ? 'JS_DASHBOARD_RESIZING' : 'JS_DASHBOARD_MOVING',
            resized ? 'fa-expand' : 'fa-arrows', true);
    },

    showWidgetContent: function (widget) {
        widget.find('.dashboardLayoutPlaceholder').remove();
        widget.find('.dashboardWidgetContent').removeClass('hide');
        widget.removeAttr('aria-busy');
    },

    registerGridster: function () {
        let thisInstance = this,
            widgetMargin = 5,
            activeTabId = this.getActiveTabId(),
            activeGridster = jQuery(".gridster_" + activeTabId),
            items = activeGridster.find('ul li');

        items.detach();

        // Constructing the grid based on window width
        let cols = this.getgridColumns(),
            col_width = (Math.floor((activeGridster.width()) / cols) - (2 * widgetMargin));

        Vtiger_DashBoard_Js.gridster = this.getContainer().gridster({
            widget_margins: [widgetMargin, widgetMargin],
            widget_base_dimensions: [col_width, 300],
            min_cols: 1,
            max_cols: cols,
            min_rows: 20,
            resize: {
                enabled: true,
                start: function (e, ui, widget) {
                    thisInstance.hideWidgetContent(widget, true);
                },
                stop: function (e, ui, widget) {
                    thisInstance.saveWidgetLayout(activeGridster.find('.dashboardWidget'), widget, true);
                }
            },
            draggable: {
                start: function () {
                    thisInstance.hideWidgetContent(this.$player, false);
                },
                'stop': function (event, ui) {
                    thisInstance.saveWidgetLayout(activeGridster.find('.dashboardWidget'), this.$player, false);
                }
            }
        }).data('gridster');


        items.sort(function (a, b) {
            const widgetA = jQuery(a),
                widgetB = jQuery(b),
                rowA = parseInt(widgetA.attr('data-row'), 10),
                rowB = parseInt(widgetB.attr('data-row'), 10),
                colA = parseInt(widgetA.attr('data-col'), 10),
                colB = parseInt(widgetB.attr('data-col'), 10);

            if (rowA === rowB && colA === colB) {
                return 0;
            }

            if (rowA > rowB || (rowA === rowB && colA > colB)) {
                return 1;
            }

            return -1;
        });
        jQuery.each(items, function (i, e) {
            const item = $(this),
                columns = Math.min(parseInt(item.attr('data-sizex'), 10), cols),
                rows = parseInt(item.attr('data-sizey'), 10),
                col = parseInt(item.attr('data-col'), 10),
                row = parseInt(item.attr('data-row'), 10);

            if (item.attr('data-position') === 'false') {
                Vtiger_DashBoard_Js.gridster.add_widget(item, columns, rows);
            } else if (col + columns - 1 <= cols) {
                Vtiger_DashBoard_Js.gridster.add_widget(item, columns, rows, col, row);
            } else {
                // A narrower viewport may need a temporary layout.
                Vtiger_DashBoard_Js.gridster.add_widget(item, columns, rows);
            }
        });
        // Loading a dashboard must not overwrite its saved layout.
    },

    savePositions: function (widgets) {
        const widgetRowColPositions = {};

        for (var index = 0, len = widgets.length; index < len; ++index) {
            var widget = jQuery(widgets[index]);
            widgetRowColPositions[widget.attr('id')] = JSON.stringify({
                row: widget.attr('data-row'), col: widget.attr('data-col')
            });
        }

        var params = {
            module: 'Vtiger',
            action: 'SaveWidgetPositions',
            tabid: this.getActiveTabId(),
            positionsmap: widgetRowColPositions
        };
        return app.request.post({"data": params});
    },

    getDashboardWidgets: function () {
        return jQuery('.dashboardWidget', jQuery('.tab-pane.active'));
    },

    loadWidgets: function () {
        var thisInstance = this;
        var widgetList = thisInstance.getDashboardWidgets();
        widgetList.each(function (index, widgetContainerELement) {
            if (thisInstance.isScrolledIntoView(widgetContainerELement)) {
                thisInstance.loadWidget(jQuery(widgetContainerELement));
                jQuery(widgetContainerELement).addClass('loadcompleted');
            }
        });
    },

    isScrolledIntoView: function (elem) {
        var viewportWidth = jQuery(window).width(),
            viewportHeight = jQuery(window).height(),

            documentScrollTop = jQuery(document).scrollTop(),
            documentScrollLeft = jQuery(document).scrollLeft(),

            minTop = documentScrollTop,
            maxTop = documentScrollTop + viewportHeight,
            minLeft = documentScrollLeft,
            maxLeft = documentScrollLeft + viewportWidth,

            $targetElement = jQuery(elem),
            elementOffset = $targetElement.offset();
        if (
            (elementOffset.top > minTop && elementOffset.top < maxTop) &&
            (elementOffset.left > minLeft && elementOffset.left < maxLeft)
        ) {
            return true;
        } else {
            return false;
        }
    },

    loadWidget: function (widgetContainer, replaceContents) {
        let thisInstance = this,
            urlParams = widgetContainer.data('url'),
            mode = widgetContainer.data('mode'),
            activeTabId = widgetContainer.closest('.tab-pane').data('tabid');

        urlParams += "&tab=" + activeTabId;

        app.helper.showProgress();

        if (mode == 'open') {
            return app.request.post({"url": urlParams}).then(function (err, data) {
                if (err) {
                    app.helper.hideProgress();
                    app.helper.showErrorNotification({message: err});

                    return;
                }

                if (replaceContents) {
                    thisInstance.clearWidgetContent(widgetContainer);
                }

                widgetContainer.prepend(data);
                vtUtils.applyFieldElementsView(widgetContainer);

                let widgetChartContainer = widgetContainer.find(".widgetChartContainer");

                if (widgetChartContainer.length > 0) {
                    widgetChartContainer.css("height", widgetContainer.height() - 110);
                }

                thisInstance.getWidgetInstance(widgetContainer);
                try {
                    widgetContainer.trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
                } catch (error) {
                    widgetContainer.find('[name="chartcontent"]').html('<div>' + app.vtranslate('JS_NO_DATA_AVAILABLE') + '</div>').css({
                        'text-align': 'center',
                        'position': 'relative',
                        'top': '100px'
                    });
                }
                app.helper.hideProgress();
            });
        }
    },

    clearWidgetContent: function (widgetContainer) {
        widgetContainer.find('canvas').each(function () {
            if (typeof Chart !== 'undefined') {
                const chart = Chart.getChart(this);

                if (chart) {
                    chart.destroy();
                }
            }
        });

        // Keep Gridster's widget node, coordinates and resize handle intact.
        widgetContainer.children().not('.gs-resize-handle').remove();
        widgetContainer.off(Vtiger_Widget_Js.widgetPostLoadEvent);
        widgetContainer.off(Vtiger_Widget_Js.widgetPostRefereshEvent);
        widgetContainer.off(Vtiger_Widget_Js.widgetPostResizeEvent);
        widgetContainer.off('mousedown.draggable');
        delete this.instancesCache[widgetContainer.attr('id')];
    },

    registerRefreshWidget: function () {
        var thisInstance = this;
        this.getContainer().on('click', 'a[name="drefresh"]', function (e) {
            var element = $(e.currentTarget);
            var parent = element.closest('li');
            var widgetInstnace = thisInstance.getWidgetInstance(parent);
            widgetInstnace.refreshWidget();
            return;
        });
    },

    removeWidget: function () {
        this.getContainer().on('click', 'li a[name="dclose"]', function (e) {
            var element = $(e.currentTarget);
            var listItem = jQuery(element).parents('li');
            var width = listItem.attr('data-sizex');
            var height = listItem.attr('data-sizey');

            var url = element.data('url');
            var parent = element.closest('.dashBoardWidgetFooter').parent();
            var widgetName = parent.data('name');
            var widgetTitle = parent.find('.dashboardTitle').attr('title');
            var activeTabId = element.closest(".tab-pane").data("tabid");

            var message = app.vtranslate('JS_ARE_YOU_SURE_TO_DELETE_WIDGET', widgetTitle);
            app.helper.showConfirmationBox({'message': message, 'htmlSupportEnable': false}).then(function (e) {
                app.helper.showProgress();
                app.request.post({"url": url}).then(
                    function (err, response) {
                        if (err == null) {

                            var nonReversableWidgets = ['MiniList', 'Notebook']

                            parent.fadeOut('slow', function () {
                                Vtiger_DashBoard_Js.gridster.remove_widget(parent);
                                parent.remove();
                            });
                            if (jQuery.inArray(widgetName, nonReversableWidgets) == -1) {
                                var data = '<li><a class="dropdown-item" onclick="Vtiger_DashBoard_Js.addWidget(this, \'' + response.url + '\')" href="javascript:void(0);"';
                                data += 'data-width=' + width + ' data-height=' + height + ' data-linkid=' + response.linkid + ' data-name=' + response.name + '>' + response.title + '</a></li>';
                                var divider = jQuery('.widgetsList .divider', '#tab_' + activeTabId);
                                if (divider.length) {
                                    jQuery(data).insertBefore(divider);
                                } else {
                                    jQuery(data).insertAfter(jQuery('.widgetsList li:last', '#tab_' + activeTabId));
                                }
                            }
                        }
                        app.helper.hideProgress();
                    }
                );
            });
        });
    },

    registerLazyLoadWidgets: function () {
        var thisInstance = this;
        jQuery(window).bind("scroll", function () {
            var widgetList = jQuery('.dashboardWidget').not('.loadcompleted');
            if (!widgetList[0]) {
                // We shouldn't unbind as we might have widgets in another tab
                //jQuery(window).unbind('scroll');
            }
            widgetList.each(function (index, widgetContainerELement) {
                if (thisInstance.isScrolledIntoView(widgetContainerELement)) {
                    thisInstance.loadWidget(jQuery(widgetContainerELement));
                    jQuery(widgetContainerELement).addClass('loadcompleted');
                }
            });
        });
    },

    registerWidgetFullScreenView: function () {
        var thisInstance = this;
        this.getContainer().on('click', 'a[name="widgetFullScreen"]', function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var widgetContainer = currentTarget.closest('li');
            var widgetName = widgetContainer.data('name');
            var widgetTitle = widgetContainer.find('.dashboardTitle').text();
            var widgetId = widgetContainer.attr('id');
            var data = widgetContainer.find('input.widgetData').val();
            var chartType = '';
            if (widgetContainer.find('input[name="charttype"]').length) {
                chartType = widgetContainer.find('input[name="charttype"]').val();
            }
            var clickThrough = 0;
            if (widgetContainer.find('input[name="clickthrough"]').length) {
                clickThrough = widgetContainer.find('input[name="clickthrough"]').val();
            }
            var fullscreenview = '<div class="fullscreencontents modal-dialog modal-xl">\n\
									<div class="modal-content">\n\
									<div class="modal-header backgroundColor">\n\
										<h4>' + widgetTitle + '</h4>\n\
										<button data-bs-dismiss="modal" class="btn btn-close" title="' + app.vtranslate('JS_CLOSE') + '"></button>\n\
									</div>\n\
									<div class="modal-body" style="overflow:auto;">\n\
										<ul style="list-style: none;" class="p-0 m-3"><li id="fullscreenpreview" class="dashboardWidget fullscreenview" data-name="' + widgetName + '">\n\
											<div class="dashboardWidgetContent overflow-auto h-100 w-100" style="min-height:500px;min-width:600px; margin: 0 auto" data-displaymode="fullscreen">';
            if (chartType != '') {
                fullscreenview += ' <input type="hidden" value="' + chartType + '" name="charttype">\n\
												<input type="hidden" value="' + clickThrough + '" name="clickthrough">\n\
												<div id="chartDiv" name="chartcontent h-100 w-100" data-mode="preview"></div> \n\
												<input class="widgetData" type="hidden" value="" name="data">';
            } else {
                fullscreenview += ' <div class="dashboardWidgetContent overflow-auto h-100 w-100" data-displaymode="fullscreen">\n\
                    <canvas id="chartDiv" class="widgetChartContainer h-100 w-100"></canvas>\n\
                    <input class="widgetData" type="hidden" value="" name="data">';
            }
            fullscreenview += '</div></ul></li></div></div></div>';

            var callback = function (modalData) {
                var element = jQuery(modalData);
                var modal = jQuery(".myModal", element);
                modal.parent().css({'top': '30px', 'left': '30px', 'right': '30px', 'bottom': '30px'});
                modal.css('height', '100%');
                var modalWidgetContainer = jQuery('.fullscreenview');
                modalWidgetContainer.find('.widgetData').val(data);
                if (chartType != '') {
                    //Chart report widget
                    var chartClassName = chartType.toCamelCase();
                    var chartClass = window["Report_" + chartClassName + "_Js"];
                    chartClass('Vtiger_ChartReportWidget_Widget_Js', {}, {
                        init: function () {
                            this._super(modalWidgetContainer);
                        }
                    });
                }
                var widgetInstance = Vtiger_Widget_Js.getInstance(modalWidgetContainer, widgetName);
                modalWidgetContainer.trigger(Vtiger_Widget_Js.widgetPostLoadEvent);
            }
            app.helper.showModal(fullscreenview, {"cb": callback});
        });
    },

    registerFilterInitiater: function () {
        var container = this.getContainer();
        container.on('click', 'a[name="dfilter"]', function (e) {
            var widgetContainer = jQuery(e.currentTarget).closest('.dashboardWidget');
            var filterContainer = widgetContainer.find('.filterContainer');
            var dashboardWidgetFooter = jQuery('.dashBoardWidgetFooter', widgetContainer);

            widgetContainer.toggleClass('dashboardFilterExpanded');
            filterContainer.slideToggle(500);

            //adding clickoutside event on the dashboardWidgetHeader
            app.helper.addClickOutSideEvent(dashboardWidgetFooter, function () {
                if (widgetContainer.is('.dashboardFilterExpanded')) {
                    widgetContainer.toggleClass('dashboardFilterExpanded');
                    filterContainer.slideToggle(500);
                }
            });

            return false;
        })
    },

    registerDeleteDashboardTab: function () {
        var self = this;
        var dashBoardContainer = this.getDashboardContainer();
        dashBoardContainer.off("click", '.deleteTab');
        dashBoardContainer.on("click", '.deleteTab', function (e) {
            // To prevent tab click event
            e.preventDefault();
            e.stopPropagation();

            var currentTarget = jQuery(e.currentTarget);
            var tab = currentTarget.closest(".dashboardTab");

            var tabId = tab.data("tabid");
            var tabName = tab.data("tabname");
            var message = app.vtranslate('JS_ARE_YOU_SURE_TO_DELETE_DASHBOARDTAB', tabName);
            app.helper.showConfirmationBox({'message': message, 'htmlSupportEnable': false}).then(function (e) {
                app.helper.showProgress();
                var data = {
                    'module': 'Vtiger',
                    'action': 'DashBoardTab',
                    'mode': 'deleteTab',
                    'tabid': tabId
                }

                app.request.post({"data": data}).then(function (err, data) {
                    app.helper.hideProgress();
                    if (err == null) {
                        jQuery('li[data-tabid="' + tabId + '"]').remove();
                        jQuery('.tab-content #tab_' + tabId).remove();

                        if (jQuery('.dashboardTab.active').length <= 0) {
                            // click the first tab if none of the tabs are active
                            var firstTab = jQuery('.dashboardTab').get(0);
                            jQuery(firstTab).find('a').click();
                        }


                        app.helper.showSuccessNotification({"message": ''});
                        if (jQuery('.dashboardTab').length < Vtiger_DashBoard_Js.dashboardTabsLimit) {
                            var element = dashBoardContainer.find('li.disabled');
                            self.removeQtip(element);
                        }

                    } else {
                        app.helper.showErrorNotification({"message": err});
                    }
                });
            });
        });
    },

    registerAddDashboardTab: function () {
        var self = this;
        var dashBoardContainer = this.getDashboardContainer();
        dashBoardContainer.off('click', '.addNewDashBoard');
        dashBoardContainer.on("click", ".addNewDashBoard", function (e) {
            if (jQuery('.dashboardTab').length >= Vtiger_DashBoard_Js.dashboardTabsLimit) {
                app.helper.showErrorNotification({"message": app.vtranslate("JS_TABS_LIMIT_EXCEEDED")});
                return;
            }
            var currentElement = jQuery(e.currentTarget);
            var data = {
                'module': 'Home',
                'view': 'DashBoardTab',
                'mode': 'showDashBoardAddTabForm'
            };

            app.request.post({"data": data}).then(function (err, res) {
                if (err === null) {
                    var cb = function (data) {
                        var form = jQuery(data).find('#AddDashBoardTab');
                        var params = {
                            submitHandler: function (form) {
                                var labelEle = jQuery(form).find('[name="tabName"]');
                                var tabName = labelEle.val().trim();
                                if (tabName.length > 50) {
                                    vtUtils.showValidationMessage(labelEle, app.vtranslate('JS_TAB_LABEL_EXCEEDS_CHARS', 50), {
                                        position: {
                                            my: 'bottom left',
                                            at: 'top left',
                                            container: jQuery(form)
                                        }
                                    });
                                    return false;
                                } else {
                                    vtUtils.hideValidationMessage(labelEle);
                                }

                                var params = jQuery(form).serializeFormData();
                                params['tabName'] = params['tabName'].trim();
                                app.request.post({"data": params}).then(function (err, data) {
                                    app.helper.hideModal();
                                    if (err) {
                                        app.helper.showErrorNotification({"message": err});
                                    } else {
                                        var tabid = data["tabid"];
                                        var tabname = data["tabname"];
                                        var tabEle = '<li class="dashboardTab" data-tabid="' + tabid + '" data-tabname="' + tabname + '">';
                                        tabEle += '<a class="nav-link" data-bs-toggle="tab" href="#tab_' + tabid + '">\n\
														<div class="d-flex align-items-center">\n\
															<span class="name text-truncate" value="' + tabname + '">\n\
															<strong>' + tabname + '</strong>\n\
															</span>\n\
															<span class="editTabName hide"><input class="form-control d-inline" type="text" name="tabName"></span>\n\
															<i class="fa fa-close deleteTab ms-2"></i>\n\
															<i class="fa fa-bars moveTab ms-2 hide"></i>\n\
														</div>\n\
														</a>';
                                        tabEle += '</li>';

                                        var tabContentEle = '<div id="tab_' + tabid + '" class="tab-pane fade" data-tabid="' + tabid + '"></div>';

                                        jQuery('.moreSettings').before(tabEle);
                                        jQuery('.moreSettings').prev().find('.name > strong').text(tabname);
                                        dashBoardContainer.find('.tab-content').append(tabContentEle);

                                        // selecting added tab
                                        var currentTab = jQuery('li[data-tabid="' + tabid + '"]');
                                        currentTab.find('a').click();
                                        if (jQuery('.dashboardTab').length >= Vtiger_DashBoard_Js.dashboardTabsLimit) {
                                            jQuery('#newDashBoardLi').addClass('disabled');
                                            self.registerQtipMessage();
                                        }


                                    }
                                });
                            }
                        }
                        form.vtValidate(params);
                    }
                    app.helper.showModal(res, {"cb": cb});
                }
            })

        })
    },
    removeQtip: function (element) {
        jQuery(element).qtip("destroy");
        element.removeClass('disabled');
    },

    registerQtipMessage: function () {
        var dashBoardContainer = this.getDashboardContainer();
        var element = dashBoardContainer.find('li.disabled');
        var title = app.vtranslate("JS_TABS_LIMIT_EXCEEDED")
        jQuery(element).qtip({
            content: title,
            hide: {
                event: 'click mouseleave',
            },
            position: {
                my: 'bottom center',
                at: 'top left',
                adjust: {
                    x: 30,
                    y: 10
                }
            },
            style: {
                classes: 'qtip-dark'
            }
        });
    },
    registerDashBoardTabRename: function () {
        var container = this.getContainer();
        var dashBoardContainer = jQuery(container).closest(".dashBoardContainer");

        dashBoardContainer.on("dblclick", ".dashboardTab", function (e) {
            e.preventDefault();
            e.stopPropagation();

            var currentTarget = jQuery(e.currentTarget);
            if (jQuery(".editTabName:visible").length > 0) {
                return;
            }
            var nameEle = currentTarget.find(".name");
            var oldName = nameEle.attr("value");
            var editEle = currentTarget.find(".editTabName");

            // Lock renaming default dashboard for user (which otherwise would be recreated)
            if (oldName == "My Dashboard") {
                return;
            }

            nameEle.addClass("hide");
            editEle.removeClass("hide");
            editEle.find("input").val(oldName);

            currentTarget.on("clickoutside", function (e) {
                var newName = editEle.find("input").val();
                var tabId = currentTarget.data("tabid");

                if (newName.trim() == "") {
                    vtUtils.showValidationMessage(editEle, app.vtranslate('JS_TAB_NAME_SHOULD_NOT_BE_EMPTY'), {
                        position: {
                            my: 'top left',
                            at: 'bottom left',
                            container: editEle.closest('.dashboardTab')
                        }
                    });
                    return false;
                }
                vtUtils.hideValidationMessage(editEle);

                if (newName.length > 50) {
                    vtUtils.showValidationMessage(editEle, app.vtranslate('JS_TAB_LABEL_EXCEEDS_CHARS', 50), {
                        position: {
                            my: 'bottom left',
                            at: 'top left',
                            container: jQuery('.module-action-content')
                        }
                    });
                    return false;
                } else {
                    vtUtils.hideValidationMessage(editEle);
                }
                currentTarget.off("clickoutside");
                if (newName != oldName) {
                    var data = {
                        'module': 'Vtiger',
                        'action': 'DashBoardTab',
                        'mode': 'renameTab',
                        'tabid': tabId,
                        'tabname': newName
                    }
                    currentTarget.find('.name > strong').text(newName);
                    app.helper.showProgress();
                    app.request.post({data: data}).then(function (err, data) {
                        app.helper.hideProgress();
                        if (err == null) {
                            app.helper.showSuccessNotification({"message": ''});
                            currentTarget.data('tabname', newName);
                        } else {
                            app.helper.showErrorNotification({"message": err});
                            currentTarget.find('.name > strong').text(oldName);
                        }
                    })
                }
                nameEle.attr("value", newName);

                editEle.addClass("hide");
                nameEle.removeClass("hide");
            })
        });
    },

    registerDashBoardTabClick: function () {
        var thisInstance = this;
        var container = this.getContainer();
        var dashBoardContainer = jQuery(container).closest(".dashBoardContainer");

        dashBoardContainer.on("shown.bs.tab", ".dashboardTab", function (e) {
            var currentTarget = jQuery(e.currentTarget);
            var tabid = currentTarget.data('tabid');
            app.changeURL("index.php?module=Home&view=DashBoard&tabid=" + tabid);

            // If tab is already loaded earlier then we shouldn't reload tab and register gridster
            if (typeof jQuery("#tab_" + tabid).find(".dashBoardTabContainer").val() !== 'undefined') {
                // We should overwrite gridster with current tab which is clicked

                var widgetMargin = 10;
                var cols = thisInstance.getgridColumns();
                $(".mainContainer").css('min-width', "500px");
                var col_width = (cols === 1) ? (Math.floor(($(".mainContainer").width() - 41) / cols) - (2 * widgetMargin)) : (Math.floor(($(window).width() - 41) / cols) - (2 * widgetMargin));

                Vtiger_DashBoard_Js.gridster = thisInstance.getContainer(tabid).gridster({
                    // Need to set the base dimensions to eliminate widgets overlapping
                    widget_base_dimensions: [col_width, 300]
                }).data("gridster");

                return;
            }
            var data = {
                'module': 'Home',
                'view': 'DashBoardTab',
                'mode': 'getTabContents',
                'tabid': tabid
            }

            app.request.post({"data": data}).then(function (err, data) {
                if (err === null) {
                    var dashBoardModuleName = jQuery("#tab_" + tabid, ".tab-content").html(data).find('[name="dashBoardModuleName"]').val();
                    if (typeof dashBoardModuleName != 'undefined' && dashBoardModuleName.length > 0) {
                        var dashBoardInstanceClassName = app.getModuleSpecificViewClass(app.view(), dashBoardModuleName);
                        if (dashBoardInstanceClassName != null) {
                            var dashBoardInstance = new window[dashBoardInstanceClassName]();
                        }
                    }
                    app.event.trigger("post.DashBoardTab.load", dashBoardInstance);
                }
            });
        });
    },

    registerRearrangeTabsEvent: function () {
        var dashBoardContainer = this.getDashboardContainer();

        dashBoardContainer.find('.moveTab').addClass('hide');

        // on click of Rearrange button
        dashBoardContainer.on("click", 'ul.moreDashBoards .reArrangeTabs', function (e) {
            var currentEle = jQuery(e.currentTarget);
            dashBoardContainer.find(".dashBoardDropDown").addClass('hide');

            var sortableContainer = dashBoardContainer.find(".tabContainer");
            var sortableEle = sortableContainer.find(".sortable");

            currentEle.addClass("hide");
            dashBoardContainer.find(".deleteTab").addClass("hide");

            dashBoardContainer.find(".moveTab").removeClass("hide");
            dashBoardContainer.find(".updateSequence").removeClass("hide");

            sortableEle.sortable({
                'containment': sortableContainer,
                stop: function () {
                }
            });
        });

        // On click of save sequence
        dashBoardContainer.find(".updateSequence").on("click", function (e) {
            var reArrangedList = {};
            var currEle = jQuery(e.currentTarget);
            jQuery(".sortable li").each(function (i, el) {
                var el = jQuery(el);
                var tabid = el.data("tabid");
                reArrangedList[tabid] = ++i;
            });

            var data = {
                "module": "Vtiger",
                "action": "DashBoardTab",
                "mode": "updateTabSequence",
                "sequence": JSON.stringify(reArrangedList)
            }

            app.request.post({"data": data}).then(function (err, data) {
                if (err == null) {
                    currEle.addClass("hide");
                    dashBoardContainer.find(".moveTab").addClass("hide");
                    dashBoardContainer.find(".reArrangeTabs").removeClass("hide");
                    dashBoardContainer.find(".deleteTab").removeClass("hide");
                    dashBoardContainer.find(".dashBoardDropDown").removeClass('hide');

                    var sortableEle = dashBoardContainer.find(".tabContainer").find(".sortable");
                    sortableEle.sortable('destroy');

                    app.helper.showSuccessNotification({"message": ''});
                } else {
                    app.helper.showErrorNotification({"message": err});
                }
            });
        });

    },

    registerEvents: function () {
        var thisInstance = this;
        this.registerLazyLoadWidgets();
        this.registerAddDashboardTab();
        this.registerDashBoardTabClick();
        this.registerDashBoardTabRename();
        this.registerDeleteDashboardTab();
        this.registerRearrangeTabsEvent();
        this.registerQtipMessage();
        app.event.off("post.DashBoardTab.load");
        app.event.on("post.DashBoardTab.load", function (event, dashBoardInstance) {
            var instance = thisInstance;
            if (typeof dashBoardInstance != 'undefined') {
                instance = dashBoardInstance;
                instance.registerEvents();
            }
            instance.registerGridster();
            instance.loadWidgets();
            instance.registerRefreshWidget();
            instance.removeWidget();
            instance.registerWidgetFullScreenView();
            instance.registerFilterInitiater();
        });
        app.event.trigger("post.DashBoardTab.load");
    }
});
