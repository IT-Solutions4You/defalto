/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Reporting_Edit_Js */
Vtiger_Edit_Js('Reporting_Edit_Js', {
    modalFields: false,
    fieldLabels: false,
}, {
    advancedFilter: false,
    container: false,
    renderTableTimeOut: false,
    renderTableRequestCount: 0,
    renderTableLoadingStartedAt: false,
    renderTableLoadingTimeout: false,
    renderTableMinimumLoadingTime: 1000,
    tableWidthBarVisible: false,
    editFieldElement: false,
    getContainer() {
        return this.container;
    },
    registerBasicEvents(container) {
        this.container = container;
        this._super(container);
        this.registerTabs();
        this.registerFields();
        this.registerSorts();
        this.registerCalculations();
        this.registerLabels();
        this.registerFilters();
        this.registerGrouping();
        this.registerChartAxes();
        this.registerSharing();
        this.registerCurrencyBehavior();
        Reporting_Table_Js.getInstance().registerEvents(container);
        this.registerRenderedTable();
        this.registerWidth();
        this.registerAlign();
        this.registerSaveButtonState();
    },
    getFormFields() {
        const self = this,
            fields = [];

        $.each(self.getContainer().find('[name="fields[]"]'), function (index, element) {
            const field = $(element).val();

            if (field && -1 === $.inArray(field, fields)) {
                fields.push(field);
            }
        });

        return fields;
    },
    getFormValue(key) {
        return this.getContainer().find('[name="' + key + '"]').val();
    },
    getFormGrouping() {
        return this.getContainer().find('select[name="group_by_fields[]"]').val() || [];
    },
    getFormGroupingRequestValues() {
        const self = this,
            intervals = {};

        self.getContainer().find('.dateGroupingInterval').each(function () {
            const element = $(this),
                fieldName = element.attr('data-field'),
                interval = element.val() || '';

            if (fieldName) {
                intervals[fieldName] = interval;
            }
        });

        return $.map(self.getFormGrouping(), function (fieldName) {
            return JSON.stringify({
                field: fieldName,
                interval: intervals[fieldName] || '',
            });
        });
    },
    updateGroupingRequestValues() {
        const container = this.getContainer().find('.groupingRequestValues');

        container.empty().append($('<input>', {
            type: 'hidden',
            name: 'group_by[]',
            value: '',
        }));

        $.each(this.getFormGroupingRequestValues(), function (index, value) {
            container.append($('<input>', {
                type: 'hidden',
                name: 'group_by[]',
                value: value,
            }));
        });
    },
    getRequestCollectionValue(value) {
        const isEmpty = $.isArray(value) ? !value.length : $.isEmptyObject(value);

        return isEmpty ? [''] : value;
    },
    getTableColumnFields() {
        return this.getFormFields();
    },
    getFormLabels() {
        let self = this,
            labelsContainer = this.getContainer().find('.containerSelectedLabels'),
            labels = {};

        labelsContainer.find('.selectedLabels').each(function () {
            let field = $(this).attr('data-field');

            labels[field] = $(this).find('.fieldLabel').val();
        });

        return labels;
    },
    getFormCalculations() {
        const recordCount = this.getContainer().find('.recordCountCalculation'),
            calculations = {
                __record_count: {
                    name: '__record_count',
                    label: recordCount.attr('data-label'),
                    count: recordCount.find('.fieldRecordCount').is(':checked') ? 'Yes' : '',
                },
            };

        this.getContainer().find('.selectedCalculations').each(function () {
            const element = $(this),
                fieldName = element.attr('data-name');

            if (!fieldName) {
                return;
            }

            calculations[fieldName] = {
                name: fieldName,
                label: element.find('.fieldLabel').val(),
                sum: element.find('.fieldSum input').is(':checked') ? 'Yes' : '',
                avg: element.find('.fieldAvg input').is(':checked') ? 'Yes' : '',
                min: element.find('.fieldMin input').is(':checked') ? 'Yes' : '',
                max: element.find('.fieldMax input').is(':checked') ? 'Yes' : '',
            };
        });

        return calculations;
    },
    getCurrencyFields() {
        const currencyFieldsElement = this.getContainer().find('.currencyFields').first();

        if (!currencyFieldsElement.length) {
            return [];
        }

        try {
            return JSON.parse(currencyFieldsElement.text()) || [];
        } catch (error) {
            return [];
        }
    },
    hasSelectedCurrencyFields() {
        const currencyFields = this.getCurrencyFields();

        return this.getFormFields().some(function (fieldName) {
            return -1 !== $.inArray(fieldName, currencyFields);
        });
    },
    hasCurrencyFieldInUsedModules() {
        const currencyFields = this.getCurrencyFields(),
            selectedFields = this.getFormFields();

        return currencyFields.some(function (currencyField) {
            const currencyFieldInfo = currencyField.split(':');

            if (1 === currencyFieldInfo.length) {
                return true;
            }

            return selectedFields.some(function (selectedField) {
                const selectedFieldInfo = selectedField.split(':');

                return 3 === selectedFieldInfo.length
                    && currencyFieldInfo[0] === selectedFieldInfo[0]
                    && currencyFieldInfo[1] === selectedFieldInfo[1];
            });
        });
    },
    isGroupByCurrencyEnabled() {
        return this.getContainer().find('[name="group_by_currency"]:checkbox').is(':checked');
    },
    registerCurrencyBehavior() {
        const self = this,
            events = [
                'add.selected.fields',
                'delete.selected.fields',
                'change.selected.calculations',
            ];

        self.updateCurrencyBehavior();
        self.getContainer().on('change', '[name="group_by_currency"]:checkbox', function () {
            self.updateCurrencyBehavior();
            self.updateSaveButtonState();
            self.retrieveRenderTableTimeout();
        });

        $.each(events, function (index, eventName) {
            app.event.on(eventName, function () {
                self.updateCurrencyBehavior();
            });
        });
    },
    updateCurrencyBehavior() {
        const groupByCurrencyField = this.getContainer().find('[name="group_by_currency"]:checkbox'),
            groupByCurrencyContainer = groupByCurrencyField.closest('.py-2'),
            currencyField = this.getContainer().find('[name="currency_id"]'),
            currencyContainer = currencyField.closest('.py-2'),
            hasCurrencyFields = this.hasSelectedCurrencyFields(),
            hasCurrencyFieldInUsedModules = this.hasCurrencyFieldInUsedModules();

        groupByCurrencyContainer.toggleClass('d-none', !hasCurrencyFields);
        currencyContainer.toggleClass('d-none', !hasCurrencyFieldInUsedModules);

        if (!hasCurrencyFields && groupByCurrencyField.is(':checked')) {
            groupByCurrencyField.prop('checked', false);
        }

        const groupByCurrency = hasCurrencyFields && groupByCurrencyField.is(':checked'),
            currencyValueMirror = currencyContainer.find('.reportingCurrencyValueMirror');

        currencyContainer.toggleClass('opacity-50', groupByCurrency);
        currencyField.prop('disabled', groupByCurrency);
        currencyField.attr('aria-disabled', groupByCurrency ? 'true' : 'false');

        if (groupByCurrency) {
            if (currencyValueMirror.length) {
                currencyValueMirror.val(currencyField.val());
            } else {
                $('<input>', {
                    class: 'reportingCurrencyValueMirror',
                    name: 'currency_id',
                    type: 'hidden',
                    value: currencyField.val(),
                }).appendTo(currencyContainer);
            }
        } else {
            currencyValueMirror.remove();
        }
    },
    registerRenderedTable() {
        let self = this;

        self.retrieveRenderedTable();

        self.getContainer().on('click', '.renderTableButton', function () {
            self.retrieveRenderedTable();
        });
        self.getContainer().on('change', '[name="report_type"], [name="primary_module"]', function () {
            self.updatePreviewTutorial(self.getPreviewRequirements());
        });
        self.getContainer().on('change', '[name="currency_id"]', function () {
            self.retrieveRenderTableTimeout();
        });

        let events = [
            'add.selected.fields',
            'label.selected.fields',
            'delete.selected.fields',
            'sort.selected.fields',
            'delete.selected.sorts',
            'sort.selected.sorts',
            'add.selected.sorts',
            'change.selected.calculations',
            'change.selected.grouping',
            'change.selected.chart',
        ];

        $.each(events, function (index, value) {
            app.event.on(value, function () {
                self.retrieveRenderTableTimeout();
            });
        });
    },
    getPreviewRequirements() {
        const reportType = this.getFormValue('report_type'),
            primaryModule = this.getFormValue('primary_module'),
            currencyId = this.getFormValue('currency_id'),
            fields = this.getFormFields(),
            grouping = this.getFormGrouping(),
            isSummary = 'summary' === reportType;

        return {
            module: Boolean(reportType && primaryModule),
            currency: !this.hasCurrencyFieldInUsedModules() || this.isGroupByCurrencyEnabled() || Boolean(currencyId),
            columns: 0 < fields.length || (isSummary && 0 < grouping.length),
            grouping: !isSummary || 0 < grouping.length,
            summary: isSummary,
        };
    },
    isPreviewReady(requirements) {
        return requirements.module && requirements.currency && requirements.columns && requirements.grouping;
    },
    getMissingRequiredFields() {
        return this.getForm().find('[data-rule-required="true"]:enabled').filter(function () {
            const value = $(this).val();

            return $.isArray(value) ? !value.length : !$.trim(String(value || ''));
        });
    },
    getSaveValidationIssues() {
        const requirements = this.getPreviewRequirements(),
            missingRequiredFields = this.getMissingRequiredFields(),
            requiredBlocks = [],
            issues = [];

        missingRequiredFields.each(function () {
            const block = $(this).closest('[data-block]').attr('data-block') || '';

            if (-1 === $.inArray(block, requiredBlocks)) {
                requiredBlocks.push(block);
            }
        });
        $.each(requiredBlocks, function (index, block) {
            issues.push({
                block: block,
                message: app.vtranslate('JS_REPORT_SAVE_MISSING_REQUIRED_FIELDS'),
            });
        });
        if (missingRequiredFields.length && !requiredBlocks.length) {
            issues.push({
                block: '',
                message: app.vtranslate('JS_REPORT_SAVE_MISSING_REQUIRED_FIELDS'),
            });
        }
        if (!requirements.module) {
            issues.push({block: 'LBL_TABS', message: app.vtranslate('JS_REPORT_SAVE_SELECT_MODULE')});
        }
        if (!requirements.currency) {
            issues.push({block: 'LBL_DETAILS', message: app.vtranslate('JS_REPORT_SAVE_SELECT_CURRENCY')});
        }
        if (!requirements.columns) {
            issues.push({block: 'LBL_COLUMNS', message: app.vtranslate('JS_REPORT_SAVE_ADD_COLUMN')});
        }
        if (!requirements.grouping) {
            issues.push({block: 'LBL_GROUPING', message: app.vtranslate('JS_REPORT_SAVE_SELECT_GROUPING')});
        }
        if (requirements.summary && !this.getChartConfiguration().x.field) {
            issues.push({block: 'LBL_CHARTS', message: app.vtranslate('JS_REPORT_SAVE_SELECT_CHART_X')});
        }
        if (requirements.summary && !this.getChartConfiguration().series.length) {
            issues.push({block: 'LBL_CHARTS', message: app.vtranslate('JS_REPORT_SAVE_ADD_CHART_SERIES')});
        }
        if (!this.isSharingReady()) {
            issues.push({block: 'LBL_SHARING', message: app.vtranslate('JS_REPORT_SAVE_SELECT_SHARING')});
        }

        return issues;
    },
    getSharingType() {
        return String(this.getFormValue('sharing_type') || 'private').toLowerCase();
    },
    isSharingReady() {
        const selectedMembers = this.getContainer().find('[name="sharing[]"]').val() || [];

        return 'selected' !== this.getSharingType() || 0 < selectedMembers.length;
    },
    updateSaveButtonState() {
        const issues = this.getSaveValidationIssues();

        this.getForm().find('.saveButton').prop('disabled', 0 < issues.length);
        this.updateSaveTabStates(issues);
    },
    updateSaveTabStates(issues) {
        const tabs = this.getNavigationTabs();

        tabs
            .removeAttr('aria-invalid')
            .find('.reportingTabError')
            .removeData('messages')
            .addClass('d-none')
            .removeAttr('title aria-label');

        $.each(issues, function (index, issue) {
            const tab = tabs.filter('[data-show-block="' + issue.block + '"]'),
                error = tab.find('.reportingTabError'),
                messages = error.data('messages') || [];

            if (!tab.length || -1 !== $.inArray(issue.message, messages)) {
                return;
            }

            messages.push(issue.message);
            error.data('messages', messages);
        });

        tabs.each(function () {
            const tab = $(this),
                error = tab.find('.reportingTabError'),
                messages = error.data('messages') || [];

            if (!messages.length) {
                return;
            }

            tab.attr('aria-invalid', 'true');
            error
                .removeClass('d-none')
                .attr('title', messages.join(' · '))
                .attr('aria-label', messages.join(' · '))
                .removeData('messages');
        });
    },
    registerSaveButtonState() {
        const self = this,
            events = [
                'add.selected.fields',
                'delete.selected.fields',
                'change.selected.grouping',
                'change.selected.chart',
            ];

        self.updateSaveButtonState();
        self.getForm().on(
            'input change',
            '[data-rule-required="true"], [name="report_type"], [name="primary_module"], [name="currency_id"], [name="group_by_currency"], [name="fields[]"], select[name="group_by_fields[]"], .dateGroupingInterval, .chartXAxisField, .chartXAxisInterval, .chartYAxisCalculation',
            function () {
                self.updateSaveButtonState();
            }
        );

        $.each(events, function (index, eventName) {
            app.event.on(eventName, function () {
                self.updateSaveButtonState();
            });
        });
    },
    registerSharing() {
        const self = this;

        self.updateSharingFieldState();
        self.getContainer().on('change', '[name="sharing_type"]', function () {
            self.updateSharingFieldState();
            self.updateSaveButtonState();
        });
        self.getContainer().on('change', '[name="sharing[]"]', function () {
            self.updateSaveButtonState();
        });
    },
    updateSharingFieldState() {
        const sharingTypeField = this.getContainer().find('[name="sharing_type"]');
        let sharingType = this.getSharingType();

        if (!sharingTypeField.val() && sharingTypeField.find('option[value="private"]').length) {
            sharingTypeField.val('private').trigger('change.select2');
            sharingType = 'private';
        }

        const sharingField = this.getContainer().find('[name="sharing[]"]'),
            sharingContainer = sharingField.closest('.py-2'),
            selectedMembers = sharingField.val() || [],
            isSelected = 'selected' === sharingType;

        sharingContainer.toggleClass('d-none', !isSelected);
        sharingField.prop('disabled', !isSelected);

        if (!isSelected && selectedMembers.length) {
            sharingField.val([]).trigger('change');
        }
    },
    updatePreviewTutorial(requirements) {
        const container = this.getContainer(),
            tutorial = container.find('.reportingTableTutorial'),
            table = container.find('.renderedTableContainer'),
            groupingStep = tutorial.find('.reportingPreviewGroupingStep'),
            renderButton = container.find('.renderTableButton'),
            widthButton = container.find('.toggleTableWidthBarButton'),
            isReady = this.isPreviewReady(requirements);

        groupingStep.toggleClass('d-none', !requirements.summary);
        renderButton.prop('disabled', this.isRenderedTableLoading() || !isReady);
        widthButton.prop('disabled', !isReady || !this.getTableColumnFields().length || !table.find('.renderedTable').length);

        tutorial.find('.reportingPreviewStep').each(function () {
            const step = $(this),
                requirement = step.data('preview-requirement'),
                isComplete = Boolean(requirements[requirement]);

            step.toggleClass('border-success', isComplete);
            step.find('.previewStepPendingIcon').toggleClass('d-none', isComplete);
            step.find('.previewStepCompleteIcon').toggleClass('d-none', !isComplete);
        });

        if (isReady) {
            tutorial.addClass('d-none');
            table.removeClass('d-none');
        } else {
            tutorial.removeClass('d-none');
            table.addClass('d-none').empty();
        }

        this.updateTableWidthBarVisibility();
    },
    getFormSorts(container) {
        let self = this,
            sorts = [];

        $.each(self.getContainer().find('[name="sort_by[]"]'), function (index, element) {
            let value = $(element).val();

            if (value) {
                sorts.push(value);
            }
        });

        return sorts;
    },
    getFormFilters() {
        this.retrieveFilterValues();

        return this.getFormValue('filter')
    },
    retrieveRenderTableTimeout() {
        let self = this,
            timeout = self.renderTableTimeOut;

        if (timeout) {
            clearTimeout(timeout);
        }

        self.renderTableTimeOut = setTimeout(function () {
            self.retrieveRenderedTable();
        }, 500);
    },
    retrieveRenderedTable() {
        let self = this,
            requirements = self.getPreviewRequirements();

        self.updatePreviewTutorial(requirements);

        if (!self.isPreviewReady(requirements)) {
            return;
        }

        let params = {
                view: 'Edit',
                mode: 'renderTable',
                module: 'Reporting',
                record: self.getFormValue('record'),
                primary_module: self.getFormValue('primary_module'),
                currency_id: self.getFormValue('currency_id'),
                group_by_currency: self.isGroupByCurrencyEnabled() ? 1 : 0,
                report_type: self.getFormValue('report_type'),
                fields: self.getRequestCollectionValue(self.getFormFields()),
                labels: self.getRequestCollectionValue(self.getFormLabels()),
                sort_by: self.getRequestCollectionValue(self.getFormSorts()),
                calculation: self.getRequestCollectionValue(self.getFormCalculations()),
                group_by: self.getRequestCollectionValue(self.getFormGroupingRequestValues()),
                chart_type: self.getFormValue('chart_type'),
                chart_position: self.getFormValue('chart_position'),
                chart_config: JSON.stringify(self.getChartConfiguration()),
                filter: self.getFormFilters(),
                width: self.getRequestCollectionValue(self.getFormWidth()),
                align: self.getRequestCollectionValue(self.getFormAlign()),
            };

        self.setRenderedTableLoading(true);
        app.helper.showProgress()
        app.request.post({data: params}).then(function (error, data) {
            self.setRenderedTableLoading(false);
            app.helper.hideProgress()

            if (!error) {
                self.getContainer().find('.renderedTableContainer').html(data);
                self.updateTableWidthBar(self.getTableColumnFields());
                self.renderChartPreview();
            }
        });
    },
    renderChartPreview() {
        const containers = this.getContainer().find('.renderedTableContainer .reportingChart:not(.chartCreated)'),
            palette = ['#0d6efd', '#6f42c1', '#d63384', '#dc3545', '#fd7e14', '#ffc107', '#198754', '#20c997', '#0dcaf0'];

        containers.each(function () {
            const container = $(this),
                canvas = container.find('canvas'),
                dataElement = container.siblings('.reportingChartData');

            if (!canvas.length || !dataElement.length || 'undefined' === typeof Chart) {
                return;
            }

            let chartData;

            try {
                chartData = JSON.parse(dataElement.val());
            } catch (error) {
                return;
            }

            $.each(chartData.data.datasets, function (index, dataset) {
                if ('pie' === chartData.type || 'doughnut' === chartData.type) {
                    dataset.backgroundColor = palette.slice(0, dataset.data.length);
                } else {
                    dataset.backgroundColor = palette[index % palette.length] + '40';
                    dataset.borderColor = palette[index % palette.length];
                    dataset.borderWidth = 2;
                }
            });

            container.addClass('chartCreated');
            app.helper.showChart(canvas, chartData);
        });
    },
    setRenderedTableLoading(isLoading) {
        if (isLoading) {
            if (this.renderTableLoadingTimeout) {
                clearTimeout(this.renderTableLoadingTimeout);
                this.renderTableLoadingTimeout = false;
            }

            this.startRenderedTableLoadingRequest();
            this.updateRenderedTableLoadingState();

            return;
        }

        this.finishRenderedTableLoadingRequest();

        if (0 < this.renderTableRequestCount) {
            this.updateRenderedTableLoadingState();

            return;
        }

        const self = this,
            elapsedTime = Date.now() - (this.renderTableLoadingStartedAt || Date.now()),
            remainingTime = Math.max(0, this.renderTableMinimumLoadingTime - elapsedTime);

        if (0 < remainingTime) {
            this.renderTableLoadingTimeout = setTimeout(function () {
                self.renderTableLoadingTimeout = false;
                self.renderTableLoadingStartedAt = false;
                self.updateRenderedTableLoadingState();
            }, remainingTime);
        } else {
            this.renderTableLoadingStartedAt = false;
        }

        this.updateRenderedTableLoadingState();
    },
    startRenderedTableLoadingRequest() {
        this.renderTableLoadingStartedAt = Date.now();
        this.renderTableRequestCount++;
    },
    finishRenderedTableLoadingRequest() {
        this.renderTableRequestCount = Math.max(0, this.renderTableRequestCount - 1);
    },
    isRenderedTableLoading() {
        return 0 < this.renderTableRequestCount || Boolean(this.renderTableLoadingTimeout);
    },
    updateRenderedTableLoadingState() {
        const button = this.getContainer().find('.renderTableButton'),
            icon = button.find('.renderTableIcon'),
            isLoading = this.isRenderedTableLoading();

        if (isLoading) {
            icon.addClass('fa-spin');
        } else {
            icon.removeClass('fa-spin');
        }

        button.prop('disabled', isLoading || !this.isPreviewReady(this.getPreviewRequirements()));
    },
    getFormWidth() {
        let self = this,
            values = {};

        self.getContainer().find('.selectedWidth').each(function (index, element) {
            let value = $(element).find('.fieldValue').val();

            if (value) {
                values[$(element).data('field')] = value;
            }
        });

        return values;
    },
    getFormAlign() {
        let self = this,
            values = {};

        self.getContainer().find('.selectedAlign').each(function (index, element) {
            let value = $(element).find('.fieldValue').val();

            if (value) {
                values[$(element).data('field')] = value;
            }
        });

        return values;
    },
    registerTabs() {
        let self = this,
            container = self.getContainer(),
            typeElement = container.find('[name="report_type"]'),
            moduleElement = container.find('[name="primary_module"]'),
            href = window.location.href,
            hrefParams = app.convertUrlToDataParams(href);

        self.hideBlocks();

        if (moduleElement.val()) {
            self.showBlock(hrefParams['tab'] ? hrefParams['tab'] : 'LBL_DETAILS');
        }

        self.updateNavigationButtons();

        container.on('click', '[data-show-block]', function () {
            self.hideBlocks();
            self.showBlock($(this).attr('data-show-block'));
            self.updateNavigationButtons();
        });

        container.on('click', '.editViewBackButton, .editViewNextButton', function () {
            const direction = $(this).hasClass('editViewBackButton') ? -1 : 1;

            self.showAdjacentNavigationTab(direction);
        });

        container.on('click', '.selectModule', function () {
            let type = typeElement.val(),
                module = moduleElement.val(),
                href = window.location.href;

            if (!type || !module) {
                app.helper.showErrorNotification({message: app.vtranslate('LBL_SELECT_MODULE_AND_TYPE')})
                return;
            }

            window.onbeforeunload = null;
            window.location.href = href + '&primary_module=' + module + '&report_type=' + type;
        });
    },
    getNavigationTabs() {
        return this.getContainer().find('[data-show-block]');
    },
    showAdjacentNavigationTab(direction) {
        const tabs = this.getNavigationTabs(),
            activeIndex = tabs.index(tabs.filter('.active').first()),
            targetIndex = activeIndex + direction,
            targetTab = tabs.eq(targetIndex);

        if (activeIndex < 0 || targetIndex < 0 || targetIndex >= tabs.length) {
            return;
        }

        targetTab.trigger('click');
        window.scrollTo({
            behavior: 'smooth',
            top: this.getContainer().offset().top,
        });
    },
    updateNavigationButtons() {
        const container = this.getContainer(),
            tabs = this.getNavigationTabs(),
            activeIndex = tabs.index(tabs.filter('.active').first()),
            backButton = container.find('.editViewBackButton'),
            nextButton = container.find('.editViewNextButton');

        backButton.toggleClass('d-none', activeIndex <= 0);
        nextButton.toggleClass('d-none', activeIndex < 0 || activeIndex >= tabs.length - 1);
    },
    hideBlock(name) {
        this.getContainer().find('[data-block="' + name + '"]').addClass('visually-hidden')
        this.getContainer().find('[data-show-block="' + name + '"]').removeClass('active');
    },
    showBlock(name) {
        this.getContainer().find('[data-block="' + name + '"]').removeClass('visually-hidden');
        this.getContainer().find('[data-show-block="' + name + '"]').addClass('active');
    },
    hideBlocks() {
        let self = this;

        self.getContainer().find('[data-block]').each(function () {
            let label = $(this).attr('data-block');

            if ('LBL_TABS' !== label) {
                self.hideBlock(label);
            }
        })
    },
    registerLabels() {
        let self = this;

        self.updateLabels();

        app.event.on('add.selected.fields', function () {
            self.updateLabels();
        });

        app.event.on('label.selected.fields', function () {
            self.updateLabels();
        });

        app.event.on('delete.selected.fields', function (event, element) {
            let field = element.find('.fieldLabel').text();

            $('.selectedLabels[data-label="' + field + '"]').remove();
        });
    },
    updateLabels() {
        const self = this;

        $('.selectedFields').each(function (index, element) {
            self.updateLabel($(element));
        });
    },
    updateLabel(element) {
        let containerElement = $('.containerLabels'),
            cloneHtml = containerElement.find('.containerCloneLabels').html(),
            selectedElement = containerElement.find('.containerSelectedLabels'),
            displayElement = element.find('.fieldLabel'),
            display = displayElement.text(),
            valueElement = element.find('[name="fields[]"]'),
            value = valueElement.val(),
            cloneElement = $(cloneHtml),
            fieldName = 'labels[' + value + ']';

        if (!value) {
            return;
        }

        let labelElement = containerElement.find('.selectedLabels[data-field="' + value + '"]'),
            isLabelExists = labelElement.length;

        if (isLabelExists) {
            cloneElement = labelElement;
        }

        cloneElement.find('.fieldLabel').attr('name', fieldName).val(display);
        cloneElement.attr('data-label', display);
        cloneElement.attr('data-field', value);

        if (!isLabelExists) {
            selectedElement.append(cloneElement);
        }
    },
    isNumberField(value) {
        let self = this,
            container = self.getContainer(),
            numberFields = JSON.parse(container.find('.numberFieldsCalculations').val());

        return -1 !== $.inArray(value, numberFields);
    },
    updateCalculation(element) {
        let self = this,
            containerElement = $('.containerCalculations'),
            cloneHtml = containerElement.find('.containerCloneCalculations').html(),
            selectedElement = containerElement.find('.containerSelectedCalculations'),
            displayElement = element.find('.fieldLabel'),
            display = displayElement.text(),
            valueElement = element.find('[name="fields[]"]'),
            value = valueElement.val(),
            cloneElement = $(cloneHtml),
            fieldName = 'calculation[' + value + ']';

        if (!value || !display || containerElement.find('.selectedCalculations[data-name="' + value + '"]').length) {
            return;
        }

        if (!self.isNumberField(value)) {
            return;
        }

        cloneElement.find('.fieldLabel').attr('name', fieldName + '[label]').val(display);
        cloneElement.find('.fieldValue').attr('name', fieldName + '[name]').val(value);
        cloneElement.find('.fieldSum input').attr('name', fieldName + '[sum]');
        cloneElement.find('.fieldAvg input').attr('name', fieldName + '[avg]');
        cloneElement.find('.fieldMin input').attr('name', fieldName + '[min]');
        cloneElement.find('.fieldMax input').attr('name', fieldName + '[max]');
        cloneElement.attr('data-name', value);
        cloneElement.attr('data-label', display);
        selectedElement.append(cloneElement);
    },
    updateCalculations() {
        let self = this;

        $('.selectedFields').each(function (index, element) {
            self.updateCalculation($(element));
        });
    },
    registerCalculations() {
        let self = this;

        self.updateCalculations();

        self.getContainer().on('change', '.selectedCalculations :checkbox, .fieldRecordCount', function () {
            app.event.trigger('change.selected.calculations');
        });

        app.event.on('add.selected.fields', function () {
            self.updateCalculations();
        });

        app.event.on('delete.selected.fields', function (event, element) {
            const field = element.find('[name="fields[]"]').val();

            $('.selectedCalculations[data-name="' + field + '"]').remove();
            app.event.trigger('change.selected.calculations');
        });
    },
    registerGrouping() {
        const self = this,
            events = [
                'add.selected.fields',
                'delete.selected.fields',
                'sort.selected.fields',
                'label.selected.fields',
            ];

        self.updateGroupingOptions();

        self.getContainer().on('change', 'select[name="group_by_fields[]"]', function () {
            self.updateDateGroupingOptions();
            self.updateGroupingButtons();
            app.event.trigger('change.selected.grouping');
        });
        self.getContainer().on('change', '.dateGroupingInterval', function () {
            self.updateGroupingRequestValues();
            app.event.trigger('change.selected.grouping');
        });
        self.getContainer().on('click', '.groupSelected', function () {
            const button = $(this),
                fieldName = button.attr('data-field'),
                groupingField = self.getContainer().find('select[name="group_by_fields[]"]'),
                selectedGrouping = groupingField.val() || [],
                selectedIndex = $.inArray(fieldName, selectedGrouping);

            if (!fieldName || !groupingField.length) {
                return;
            }

            if (-1 === selectedIndex) {
                selectedGrouping.push(fieldName);
            } else {
                selectedGrouping.splice(selectedIndex, 1);
            }

            groupingField.val(selectedGrouping).trigger('change');
        });

        $.each(events, function (index, eventName) {
            app.event.on(eventName, function () {
                self.updateGroupingOptions();
            });
        });

    },
    getChartConfiguration() {
        const container = this.getContainer().find('.reportingChartAxes'),
            xAxisField = container.find('.chartXAxisField'),
            xField = xAxisField.attr('data-field') || '',
            interval = xAxisField.attr('data-interval') || '',
            selectedCalculations = container.find('.chartYAxisCalculation').val() || [],
            series = [];

        $.each(selectedCalculations, function (index, selectedCalculation) {
            const separatorIndex = selectedCalculation.indexOf(':');

            if (-1 === separatorIndex) {
                return;
            }
            series.push({
                field: selectedCalculation.substring(separatorIndex + 1),
                aggregation: selectedCalculation.substring(0, separatorIndex),
            });
        });

        return {
            x: {
                field: xField,
                interval: interval,
            },
            series: series,
        };
    },
    updateChartConfigurationValue() {
        this.getContainer().find('.chartConfigurationValue').val(JSON.stringify(this.getChartConfiguration()));
    },
    updateChartXAxisValue() {
        const container = this.getContainer(),
            chartField = container.find('.chartXAxisField'),
            groupingOptions = container.find('select[name="group_by_fields[]"] option:selected'),
            labels = [],
            firstGrouping = groupingOptions.first(),
            fieldName = firstGrouping.val() || '',
            firstInterval = container.find('.dateGroupingInterval').filter(function () {
                return fieldName === $(this).attr('data-field');
            }).val() || '';

        groupingOptions.each(function () {
            const groupingOption = $(this),
                groupingField = groupingOption.val(),
                interval = container.find('.dateGroupingInterval').filter(function () {
                    return groupingField === $(this).attr('data-field');
                }),
                intervalLabel = interval.val() ? interval.find('option:selected').text().trim() : '';

            labels.push(groupingOption.text().trim() + (intervalLabel ? ' (' + intervalLabel + ')' : ''));
        });

        chartField
            .attr('data-field', fieldName)
            .attr('data-interval', firstInterval)
            .val(labels.length ? labels.join(' / ') : chartField.data('placeholder'));
    },
    getCalculationChartOptions() {
        const select = this.getContainer().find('.chartYAxisCalculation'),
            calculations = this.getFormCalculations(),
            options = [];

        if ('Yes' === calculations.__record_count.count) {
            options.push({
                value: 'count:',
                label: select.data('count-label'),
            });
        }
        $.each(calculations, function (fieldName, calculation) {
            if ('__record_count' === fieldName) {
                return;
            }
            $.each(['sum', 'avg', 'min', 'max'], function (index, aggregation) {
                if ('Yes' === calculation[aggregation]) {
                    options.push({
                        value: aggregation + ':' + fieldName,
                        label: calculation.label + ' (' + select.data(aggregation + '-label') + ')',
                    });
                }
            });
        });

        return options;
    },
    updateChartCalculationOptions() {
        const select = this.getContainer().find('.chartYAxisCalculation'),
            selectedValues = select.val() || [],
            options = this.getCalculationChartOptions();

        select.empty();
        $.each(options, function (index, option) {
            select.append($('<option>', {value: option.value, text: option.label}));
        });
        const availableValues = $.map(options, function (option) {
                return option.value;
            }),
            retainedValues = selectedValues.filter(function (selectedValue) {
                return -1 !== $.inArray(selectedValue, availableValues);
            });

        select.val(retainedValues.length ? retainedValues : (options[0] ? [options[0].value] : []));
        select.prop('disabled', !options.length).trigger('change.select2');
        this.updateChartConfigurationValue();
    },
    registerChartAxes() {
        const self = this,
            container = self.getContainer().find('.reportingChartAxes');

        if (!container.length) {
            return;
        }

        self.updateChartXAxisValue();
        self.updateChartCalculationOptions();
        self.updateChartConfigurationValue();

        container.on('change', '.chartYAxisCalculation', function () {
            self.updateChartConfigurationValue();
            app.event.trigger('change.selected.chart');
        });
        app.event.on('change.selected.calculations', function () {
            self.updateChartCalculationOptions();
            app.event.trigger('change.selected.chart');
        });
        app.event.on('change.selected.grouping', function () {
            self.updateChartXAxisValue();
            self.updateChartConfigurationValue();
            app.event.trigger('change.selected.chart');
        });
        self.getContainer().on('change', '[name="chart_type"], [name="chart_position"]', function () {
            app.event.trigger('change.selected.chart');
        });
    },
    updateGroupingOptions() {
        const groupingField = this.getContainer().find('select[name="group_by_fields[]"]');

        this.getContainer().find('.selectedFields').each(function () {
            const selectedField = $(this),
                fieldName = selectedField.find('[name="fields[]"]').val(),
                fieldLabel = selectedField.find('.fieldLabel').text().trim(),
                groupingOption = groupingField.find('option').filter(function () {
                    return fieldName === $(this).val();
                });

            if (fieldName && fieldLabel && groupingOption.length) {
                groupingOption.text(fieldLabel);
            }
        });

        groupingField.trigger('change.select2');
        this.updateGroupingButtons();
        this.updateDateGroupingOptions();
    },
    updateDateGroupingOptions() {
        const self = this,
            groupingField = self.getContainer().find('select[name="group_by_fields[]"]'),
            container = self.getContainer().find('.reportingDateGroupingOptions'),
            intervals = container.data('intervals') || {},
            currentIntervals = container.data('current-intervals') || {};

        container.find('.dateGroupingInterval').each(function () {
            const element = $(this),
                fieldName = element.attr('data-field');

            if (fieldName) {
                currentIntervals[fieldName] = element.val() || '';
            }
        });

        container.empty();

        groupingField.find('option:selected[data-date-grouping="1"]').each(function () {
            const option = $(this),
                fieldName = option.val(),
                row = $('<div>', {class: 'row align-items-center g-2 mb-2 dateGroupingOption'}),
                label = $('<label>', {class: 'col-md-6 col-form-label'}).text(
                    container.attr('data-label') + ': ' + option.text().trim()
                ),
                selectContainer = $('<div>', {class: 'col-md-6'}),
                select = $('<select>', {
                    class: 'form-select dateGroupingInterval',
                    'data-field': fieldName,
                });

            $.each(intervals, function (interval, intervalLabel) {
                select.append($('<option>').attr('value', interval).text(intervalLabel));
            });

            select.val(currentIntervals[fieldName] || '');
            selectContainer.append(select);
            row.append(label, selectContainer);
            container.append(row);
        });

        container.data('current-intervals', currentIntervals);
        self.updateGroupingRequestValues();
    },
    updateGroupingButtons() {
        const selectedGrouping = this.getFormGrouping();

        this.getContainer().find('.groupSelected').each(function () {
            const button = $(this),
                isSelected = -1 !== $.inArray(button.attr('data-field'), selectedGrouping);

            button
                .attr('aria-pressed', isSelected ? 'true' : 'false')
                .toggleClass('fw-semibold', isSelected)
                .find('.groupSelectedCheck')
                .toggleClass('invisible', !isSelected);
        });
    },
    getFieldOptions() {
        return JSON.parse(this.getContainer().find('.fieldOptions').text());
    },
    updateFields(container) {
        let self = this,
            selectField = container.find('.selectFields'),
            selectModule = container.find('.selectModules'),
            fieldOptions = self.getFieldOptions(),
            module = selectModule.val() ? selectModule.val() : 'default';

        let html = '<optgroup label=""><option value="">' + app.vtranslate('JS_SELECT_FIELD') + '</option>',
            prevGroup = '';

        $.each(fieldOptions[module], function (key, value) {
            let valueInfo = value.split('##'),
                group = valueInfo[0];

            if (prevGroup !== group) {
                html += '</optgroup><optgroup label="' + valueInfo[0] + '">';
            }

            html += '<option value="' + key + '">' + valueInfo[1] + '</option>';

            prevGroup = group;
        })

        html += '</optgroup>';

        selectField.html(html);
        selectField.trigger('change');
    },
    isCreateFieldAllowed(value) {
        let self = this,
            duplicateElement = self.getFieldsByName(value);

        if (self.getEditField()) {
            if (!duplicateElement.length) {
                return true;
            }

            if (self.getEditField().val() === value) {
                return true;
            }

            if (self.getEditField() !== duplicateElement) {
                return false;
            }
        } else if (duplicateElement.length) {
            return false;
        }

        return true;
    },
    registerModalFieldEvents(modalContainer) {
        let self = this,
            containerElement = self.getFieldsContainer(),
            selectedElement = containerElement.find('.containerSelectedFields'),
            cloneHtml = containerElement.find('.containerCloneFields').html(),
            selectedFieldName = modalContainer.find('.selectedFieldName'),
            selectedSortBy = modalContainer.find('.selectedSortBy'),
            selectedFieldLabel = modalContainer.find('.selectedFieldLabel');

        modalContainer.on('click', '#selectFieldsButton', function () {
            let value = selectedFieldName.val(),
                sortValue = selectedSortBy.val(),
                widthValue = modalContainer.find('.selectedWidth').val(),
                alignValue = modalContainer.find('.selectedAlign.active').attr('data-align'),
                displayValue = selectedFieldLabel.val(),
                cloneElement = self.getEditField() ? self.getFieldsParent(self.getEditField()) : $(cloneHtml),
                data = {
                    field: value,
                    label: displayValue,
                };

            if (!value) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_FIELD')})
                return;
            }

            if (!displayValue) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_REQUIRED_LABEL')})
                return;
            }

            if (!self.isCreateFieldAllowed(value)) {
                app.helper.showErrorNotification({message: app.vtranslate('JS_FIELD_ALREADY_SELECTED')})
                return;
            }

            cloneElement.find('.fieldLabel').text(displayValue);
            cloneElement.find('.fieldValue').val(value);
            cloneElement.find('[data-field]').attr('data-field', value);
            cloneElement.find('[data-label]').attr('data-label', displayValue);

            if (!self.getEditField()) {
                selectedElement.append(cloneElement);
            }

            self.updateSortSelected(value, sortValue);
            self.updateWidthSelected(value, widthValue);
            self.updateAlignSelected(value, alignValue);

            app.event.trigger('add.selected.fields', cloneElement, data);
            app.helper.hideModal();
        });

        modalContainer.on('click', '.selectedAlign', function () {
            modalContainer.find('.selectedAlign').removeClass('active');
            $(this).addClass('active');
        });

        modalContainer.on('change', '.selectModules', function () {
            self.updateFields(modalContainer);
        });

        modalContainer.on('change', '.selectFields', function () {
            self.updateModalFieldValues(modalContainer);
        });
    },
    getSortValue(field) {
        return this.getSortElement(field).find('[name="sort_by[]"]').val() ?? field + ' ';
    },
    registerSelectFields(modalContainer) {
        let self = this,
            selectModules = modalContainer.find('.selectModules'),
            selectSortBy = modalContainer.find('.selectedSortBy'),
            selectedFieldName = modalContainer.find('.selectedFieldName'),
            selectedWidth = modalContainer.find('.selectedWidth'),
            selectedFieldLabel = modalContainer.find('.selectedFieldLabel');

        vtUtils.showSelect2ElementView(modalContainer.find('select'));

        self.updateFields(modalContainer);
        self.updateLabels(modalContainer);
        self.updateModalFieldValues(modalContainer);
        self.registerModalFieldEvents(modalContainer);

        if (self.getEditField()) {
            let editFieldData = self.getEditField(),
                editFieldId = editFieldData.val(),
                editFieldInfo = editFieldId.split(':'),
                sortBy = self.getSortValue(editFieldId).split(' '),
                width = self.getContainer().find('[name="width[' + editFieldId + ']"]').val(),
                align = self.getContainer().find('[name="align[' + editFieldId + ']"]').val();

            if (3 === editFieldInfo.length) {
                selectModules.val(editFieldInfo[0] + ':' + editFieldInfo[1]);
                selectModules.trigger('change');
            }

            if (sortBy[0]) {
                selectSortBy.val(sortBy[1]);
                selectSortBy.trigger('change');
            }

            selectedFieldName.val(editFieldId);
            selectedFieldLabel.val(self.getFieldLabel(editFieldId));
            selectedWidth.val(width);
            modalContainer.find('[data-align="' + align + '"]').addClass('active');
        }
    },
    updateModalFieldValues(modalContainer) {
        let self = this,
            value = modalContainer.find('.selectFields').val(),
            label = self.getFieldLabel(modalContainer.find('.selectFields').val());

        modalContainer.find('.selectedFieldLabel').val(label);
        modalContainer.find('.selectedFieldName').val(value);
    },
    getNewFieldModal() {
        let modalContainer = $(Reporting_Edit_Js.modalFields).clone(true, true);

        modalContainer.find('#addSelectFields').addClass('selectFieldsButton');

        return modalContainer;
    },
    retrieveFieldModal() {
        let containerElement = this.getFieldsContainer();

        Reporting_Edit_Js.modalFields = containerElement.find('.fieldsNewFieldModal').detach();
        Reporting_Edit_Js.modalLabels = containerElement.find('.fieldsEditLabelModal').detach();
    },
    setEditField(element) {
        this.editFieldElement = element;
    },
    getEditField() {
        return this.editFieldElement;
    },
    registerFields() {
        let self = this,
            containerElement = self.getFieldsContainer(),
            selectedElement = containerElement.find('.containerSelectedFields');

        self.retrieveFieldModal();

        selectedElement.sortable({
            stop() {
                app.event.trigger('sort.selected.fields')
            },
        });

        containerElement.on('click', '.openSelectFields', function () {
            self.setEditField(false);

            let modalContainer = self.getNewFieldModal();

            app.helper.showModal(modalContainer, {
                cb: function () {
                    self.registerSelectFields(modalContainer);
                }
            });
        });

        containerElement.on('click', '.editFieldSelected', function () {
            self.setEditField(self.getFieldsElement($(this)));

            let modalContainer = self.getNewFieldModal();

            app.helper.showModal(modalContainer, {
                cb: function () {
                    self.registerSelectFields(modalContainer);
                }
            });
        })

        containerElement.on('click', '.deleteSelected', function () {
            let element = $(this),
                selectedFields = self.getFieldsParent(element);

            selectedFields.remove();

            app.event.trigger('delete.selected.fields', selectedFields);
        });

        containerElement.on('click', '.moveSelected', function () {
            let direction = $(this).data('value'),
                parent = self.getFieldsParent($(this));

            if ('right' === direction && parent.next().length) {
                parent.next().after(parent.detach())
            }

            if ('left' === direction && parent.prev().length) {
                parent.prev().before(parent.detach())
            }

            app.event.trigger('sort.selected.fields');
        });

        containerElement.on('click', '.editLabelSelected', function () {
            let fieldId = self.getFieldsId($(this)),
                modalContainer = self.getChangeLabelsModal(fieldId);

            app.helper.showModal(modalContainer, {
                cb: function () {
                    self.registerChangeLabels(modalContainer);
                }
            });
        });
    },
    getChangeLabelsModal(fieldId) {
        let self = this,
            modalContainer = $(Reporting_Edit_Js.modalLabels);

        modalContainer.find('.selectedFieldName').val(fieldId);
        modalContainer.find('.selectedFieldLabel').val(self.getFieldLabel(fieldId));
        modalContainer.find('.modal-title').text(app.vtranslate('JS_EDIT_LABEL') + ': ' + self.getFieldLabel(fieldId, false) + ' [' + fieldId + ']');

        return modalContainer;
    },
    getFieldsByName(value) {
        return this.getContainer().find('[name="fields[]"][value="' + value + '"]');
    },
    registerChangeLabels(modalContainer) {
        let self = this,
            selectedFieldElement = modalContainer.find('.selectedFieldName'),
            selectedLabelElement = modalContainer.find('.selectedFieldLabel');

        modalContainer.on('click', '#changeLabelsButton', function () {
            let value = selectedFieldElement.val(),
                displayValue = selectedLabelElement.val(),
                fieldElement = self.getFieldsByName(value),
                parentElement = self.getFieldsParent(fieldElement),
                data = {
                    field: value,
                    label: displayValue,
                };

            parentElement.find('.fieldLabel').text(displayValue);
            parentElement.find('[data-label]').attr('data-label', displayValue);

            app.helper.hideModal();
            app.event.trigger('label.selected.fields', parentElement, data);
        });
    },
    getFieldsContainer() {
        return this.getContainer().find('.containerFields');
    },
    getSortElement(field) {
        return this.getSortContainer().find('.selectedSorts[data-field="' + field + '"]')
    },
    registerClickSortSelected() {
        const self = this;

        self.getFieldsContainer().on('click', '.sortSelected', function () {
            let element = $(this),
                field = element.data('field'),
                value = element.data('value');

            self.updateSortSelected(field, value)
        });
    },
    updateSortSelected(field, value) {
        let self = this,
            sortElement = self.getSortElement(field),
            isSortExists = sortElement.length;

        if (!isSortExists) {
            sortElement = self.getSortClone();
        }

        sortElement.attr('data-field', field);
        sortElement.attr('data-type', value);
        sortElement.find('.fieldValue').val(field + ' ' + value);
        sortElement.find('.fieldValueOrder').addClass('visually-hidden');
        sortElement.find('.fieldValueOrder[data-order="' + value + '"]').removeClass('visually-hidden');
        sortElement.find('.fieldLabel').val(self.getFieldLabel(field));
        sortElement.find('[title]').attr('title', app.vtranslate('JS_' + value));

        if (!isSortExists) {
            self.getSortContainer().append(sortElement);
        }

        if (!value) {
            sortElement.remove();

            app.event.trigger('delete.selected.sorts');
        } else {
            app.event.trigger('add.selected.sorts');
        }
    },
    registerSorts() {
        let self = this;

        self.retrieveSorts();
        self.registerClickSortSelected();

        let containerSelected = self.getSortContainer();

        containerSelected.sortable({
            stop() {
                app.event.trigger('sort.selected.sorts')
            },
        });

        containerSelected.on('click', '.selectedSortDelete', function () {
            $(this).parents('.selectedSorts').remove();

            app.event.trigger('delete.selected.sorts');
        });

        app.event.on('add.selected.sorts', function () {
            self.retrieveSorts();
        });

        app.event.on('delete.selected.sorts', function () {
            self.retrieveSorts();
        });

        app.event.on('label.selected.fields', function (event, element, data) {
            self.getSortElement(data['field']).find('.fieldLabel').val(data['label']);
        });

        app.event.on('delete.selected.fields', function (event, element) {
            self.getSortElement(self.getFieldsId(element)).remove();

            app.event.trigger('delete.selected.sorts');
        })
    },
    retrieveSorts() {
        let self = this,
            sortsSelected = $('[name="sort_by[]"]');

        if (!sortsSelected) {
            return;
        }

        self.getFieldsContainer().find('.sortSelected').removeClass('fw-bold')

        sortsSelected.each(function () {
            let sortsValue = $(this).val(),
                sortsData = sortsValue.split(' '),
                field = sortsData[0],
                order = sortsData[1],
                fieldElement = self.getFieldsContainer().find('[name="fields[]"][value="' + field + '"]'),
                parentElement = self.getFieldsParent(fieldElement);

            parentElement.find('.sortSelected[data-field="' + field + '"][data-value="' + order + '"]').addClass('fw-bold');
        });
    },
    getSortContainer() {
        return this.getContainer().find('.containerSelectedSorts');
    },
    getSortClone() {
        return $(this.getContainer().find('.containerCloneSorts').html());
    },
    getFieldLabel(field, useUpdatedLabel = true) {
        let labelElement = this.getContainer().find('[name="labels[' + field + ']"]')

        if (useUpdatedLabel && labelElement.length) {
            return labelElement.val();
        }

        if (!Reporting_Edit_Js.fieldLabels) {
            Reporting_Edit_Js.fieldLabels = JSON.parse($('.labelFields').text())
        }

        return Reporting_Edit_Js.fieldLabels[field];
    },
    getFieldsParent(element) {
        if (element.is('.selectedFields')) {
            return element;
        }

        return element.parents('.selectedFields');
    },
    getFieldsElement(element) {
        return this.getFieldsParent(element).find('[name="fields[]"]');
    },
    getFieldsId(element) {
        return this.getFieldsElement(element).val();
    },
    registerFilters() {
        this.registerFilterConditions()
        this.registerSubmit();
        this.registerFilterChange();
    },
    registerFilterChange() {
        const self = this;

        self.getContainer().on('change', '.fieldUiHolder', function () {
            self.retrieveRenderTableTimeout();
        })
    },
    getForm() {
        return $('#EditView')
    },
    getFormElement(name) {
        return this.getForm().find('[name="' + name + '"]');
    },
    registerFilterConditions() {
        let self = this,
            filterContainer = self.getForm().find('.filterContainer');

        self.advancedFilter = Vtiger_AdvanceFilter_Js.getInstance(filterContainer)
    },
    retrieveFilterValues() {
        let self = this,
            filters = JSON.stringify(self.advancedFilter.getValues());

        self.getFormElement('filter').text(filters);
    },
    registerSubmit() {
        const self = this;

        self.getForm().on('submit', function (e) {
            const isModuleSelectionStep = 0 < self.getContainer().find('.selectModule').length,
                groupingFields = self.getFormGrouping();

            if ('summary' === self.getFormValue('report_type') && !isModuleSelectionStep && !groupingFields.length) {
                e.preventDefault();
                app.helper.showErrorNotification({message: app.vtranslate('JS_SELECT_GROUPING_FIELD')});

                return false;
            }

            self.retrieveFilterValues();
            self.updateGroupingRequestValues();

            return true;
        })
    },
    registerWidth() {
        const self = this,
            events = [
                'add.selected.fields',
                'delete.selected.fields',
                'sort.selected.fields',
                'label.selected.fields',
                'change.selected.grouping',
            ];

        self.retrieveWidth();

        $.each(events, function (index, eventName) {
            app.event.on(eventName, function () {
                self.retrieveWidth();
            });
        });

        self.getContainer().on('change', '[name="report_type"]', function () {
            self.retrieveWidth();
        });

        self.getContainer().on('input change', '.reportingTableWidthInput', function (event) {
            const input = $(this),
                field = input.data('field'),
                width = 'change' === event.type ? self.getTableWidthInputValue(input.val()) : input.val();

            input.val(width);
            self.setWidthSelected(field, width);
            self.updateRenderedTableColumnWidth(field, width);
        });

        self.getContainer().on('click', '.resetTableWidths', function () {
            self.getContainer().find('.reportingTableWidthInput').each(function () {
                $(this).val('').trigger('input');
            });
        });

        self.getContainer().on('click', '.toggleTableWidthBarButton', function () {
            self.tableWidthBarVisible = !self.tableWidthBarVisible;
            self.updateTableWidthBarVisibility();
        });
    },
    retrieveWidth() {
        const self = this,
            container = self.getContainer(),
            containerSelected = container.find('.containerSelectedWidth'),
            cloneHtml = container.find('.containerCloneWidth').html(),
            fields = self.getTableColumnFields();

        $.each(fields, function (index, value) {
            const name = 'width[' + value + ']',
                clone = $(cloneHtml),
                fieldElement = containerSelected.find('[name="' + name + '"]');

            clone.find('.fieldValue').attr('name', name);
            clone.data('field', value);

            if (value && !fieldElement.length) {
                containerSelected.append(clone);
            }
        });

        containerSelected.find('.selectedWidth').each(function () {
            if (-1 === $.inArray($(this).data('field'), fields)) {
                $(this).remove();
            }
        });

        self.updateTableWidthBar(fields);
    },
    updateWidthSelected(field, width) {
        this.retrieveWidth();
        this.setWidthSelected(field, width);
    },
    setWidthSelected(field, width) {
        const container = this.getContainer();

        container.find('[name="width[' + field + ']"]').val(width);
        container.find('.reportingTableWidthInput').filter(function () {
            return field === $(this).data('field');
        }).val(width);
    },
    updateTableWidthBar(fields) {
        const self = this,
            container = self.getContainer(),
            table = container.find('.renderedTable'),
            config = container.find('.reportingTableWidthConfig'),
            autoLabel = config.data('auto-label'),
            widthLabel = config.data('width-label');

        if (!fields.length) {
            table.find('.reportingTableWidthBar, .reportingTableWidthToolbar').remove();
            self.updateTableWidthBarVisibility();

            return;
        }

        if (!table.length || !table.find('tr').length) {
            return;
        }

        let bar = table.find('.reportingTableWidthBar'),
            toolbar = table.find('.reportingTableWidthToolbar');

        if (!bar.length) {
            const firstRow = table.find('tr').first(),
                toolbarCell = $('<th>', {
                    class: 'bg-body-secondary bg-opacity-50 border-bottom p-2 text-end',
                    colspan: fields.length,
                }),
                resetButton = $('<button>', {
                    class: 'resetTableWidths btn btn-sm btn-outline-secondary text-nowrap',
                    type: 'button',
                }),
                resetIcon = $('<i>', {
                    class: 'fa-solid fa-rotate-left me-1',
                });

            toolbar = $('<tr>', {
                class: 'reportingTableWidthToolbar',
            });
            bar = $('<tr>', {
                class: 'reportingTableWidthBar bg-body-secondary bg-opacity-50 border-bottom',
            });
            resetButton.append(resetIcon, $('<span>', {text: autoLabel}));
            toolbarCell.append(resetButton);
            toolbar.append(toolbarCell);
            firstRow.before(toolbar, bar);
        } else {
            toolbar.find('th').attr('colspan', fields.length);
        }

        bar.empty();

        $.each(fields, function (index, field) {
            const label = self.getFieldLabel(field) || field,
                value = container.find('[name="width[' + field + ']"]').val() || '',
                inputId = 'reportingTableWidth' + index,
                cell = $('<th>', {
                    class: 'bg-body-secondary bg-opacity-50 p-2 align-middle',
                    scope: 'col',
                }),
                input = $('<input>', {
                    'aria-label': widthLabel + ': ' + label,
                    'aria-valuemin': 100,
                    class: 'reportingTableWidthInput form-control form-control-sm w-100',
                    'data-field': field,
                    'data-min-width': 100,
                    id: inputId,
                    inputmode: 'decimal',
                    min: 100,
                    placeholder: autoLabel,
                    size: 6,
                    type: 'text',
                    value: value,
                });

            cell.append(input);
            bar.append(cell);
        });

        self.updateTableWidthBarVisibility();
    },
    updateTableWidthBarVisibility() {
        const container = this.getContainer(),
            bar = container.find('.reportingTableWidthBar'),
            widthRows = container.find('.reportingTableWidthBar, .reportingTableWidthToolbar'),
            button = container.find('.toggleTableWidthBarButton'),
            isVisible = this.tableWidthBarVisible && Boolean(bar.length);

        widthRows.toggleClass('d-none', !isVisible);
        button
            .toggleClass('active', isVisible)
            .attr('aria-expanded', String(isVisible))
            .prop('disabled', !bar.length);
    },
    updateRenderedTableColumnWidth(field, value) {
        const columnIndex = $.inArray(field, this.getTableColumnFields()),
            width = this.getRenderedTableColumnWidth(value),
            table = this.getContainer().find('.renderedTable');

        if (-1 === columnIndex || !table.length) {
            return;
        }

        table.find('col').eq(columnIndex).css('width', width);
        table.find('tr').not('.reportingTableWidthToolbar').each(function () {
            $(this).children().eq(columnIndex).css({
                'min-width': 'auto' === width ? '' : width,
                width: width,
            });
        });

        this.updateRenderedTableWidth();
    },
    updateRenderedTableWidth() {
        const self = this,
            container = self.getContainer(),
            table = container.find('.renderedTable'),
            tableWidths = [];
        let hasConfiguredWidths = false;

        $.each(self.getTableColumnFields(), function (index, field) {
            const value = container.find('[name="width[' + field + ']"]').val(),
                width = self.getRenderedTableColumnWidth(value);

            if (width && 'auto' !== width) {
                hasConfiguredWidths = true;
            }

            tableWidths.push(width && 'auto' !== width ? width : '100px');
        });

        if (!hasConfiguredWidths) {
            table.css({
                'min-width': '',
                'table-layout': '',
                width: '',
            });

            return;
        }

        const tableWidth = 'calc(' + tableWidths.join(' + ') + ')';

        table.css({
            'min-width': tableWidth,
            'table-layout': 'fixed',
            width: tableWidth,
        });
    },
    getRenderedTableColumnWidth(value) {
        const width = $.trim(String(value || '').replace(/;+$/, '')),
            pixelWidth = width.match(/^(\d+(?:\.\d+)?)px$/i);

        if (!width) {
            return '';
        }

        if ($.isNumeric(width)) {
            return Math.max(100, Number(width)) + 'px';
        }

        if (pixelWidth) {
            return Math.max(100, Number(pixelWidth[1])) + 'px';
        }

        if ('auto' === width.toLowerCase()) {
            return 'auto';
        }

        return /^\d+(?:\.\d+)?(?:px|%|em|rem|ch|vw|cm|mm|in|pt|pc)$/i.test(width) ? width : '';
    },
    getTableWidthInputValue(value) {
        const width = $.trim(String(value || '').replace(/;+$/, '')),
            pixelWidth = width.match(/^(\d+(?:\.\d+)?)px$/i);

        if (pixelWidth) {
            return Math.max(100, Number(pixelWidth[1])) + 'px';
        }

        return $.isNumeric(width) ? String(Math.max(100, Number(width))) : width;
    },
    registerAlign() {
        let self = this;

        self.retrieveAlign();

        app.event.on('add.selected.fields', function () {
            self.retrieveWidth();
        });
    },
    retrieveAlign() {
        let self = this,
            container = self.getContainer(),
            containerSelected = container.find('.containerSelectedAlign'),
            cloneHtml = container.find('.containerCloneAlign').html(),
            fields = container.find('[name="fields[]"]');

        $.each(fields, function (index, element) {
            let value = $(element).val(),
                clone = $(cloneHtml),
                name = 'align[' + value + ']',
                align = self.isNumberField(value) ? 'end' : '';

            clone.attr('data-field', value);
            clone.attr('data-align', align);
            clone.find('.fieldValue').attr('name', name).val(align);
            clone.find('.fieldLabel').text(self.getFieldLabel(value));

            if (value && !containerSelected.find('[name="' + name + '"]').length) {
                containerSelected.append(clone);
            }
        });
    },
    updateAlignSelected(field, value) {
        this.retrieveAlign()
        this.getContainer().find('[name="align[' + field + ']"]').val(value);
    }
});
