/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var Reporting_Edit_Js */
Vtiger_Edit_Js('Reporting_Edit_Js', {
    modalFields: false,
    fieldLabels: false,
}, {
    advancedFilter: false,
    container: false,
    renderTableTimeOut: false,
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
        this.registerRenderedTable();
        this.registerWidth();
        this.registerAlign();
    },
    getFormFields() {
        let self = this,
            fields = [];

        $.each(self.getContainer().find('[name="fields[]"]'), function(index, element) {
            fields.push($(element).val())
        })

        return fields;
    },
    getFormValue(key) {
        return this.getContainer().find('[name="' + key + '"]').val();
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
    registerRenderedTable() {
        let self = this;

        self.retrieveRenderedTable();

        self.getContainer().on('click', '.renderedTable', function() {
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
        ];

        $.each(events, function(index, value) {
            app.event.on(value, function() {
                self.retrieveRenderTableTimeout();
            });
        });
    },
    getFormSorts(container) {
        let self = this,
            sorts = [];

        $.each(self.getContainer().find('[name="sort_by[]"]'), function(index, element) {
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
            params = {
                view: 'Edit',
                mode: 'renderTable',
                module: 'Reporting',
                record: self.getFormValue('record'),
                primary_module: self.getFormValue('primary_module'),
                fields: self.getFormFields(),
                labels: self.getFormLabels(),
                sort_by: self.getFormSorts(),
                filter: self.getFormFilters(),
                width: self.getFormWidth(),
                align: self.getFormAlign(),
            };

        app.helper.showProgress()
        app.request.post({data: params}).then(function (error, data) {
            app.helper.hideProgress()

            if (!error) {
                self.getContainer().find('.renderedTable').html(data);
            }
        });
    },
    getFormWidth() {
        let self = this,
            values = {};

        self.getContainer().find('.selectedWidth').each(function(index, element) {
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

        self.getContainer().find('.selectedAlign').each(function(index, element) {
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

        container.on('click', '[data-show-block]', function() {
            self.hideBlocks();
            self.showBlock($(this).attr('data-show-block'));
        });

        container.on('click', '.selectModule', function() {
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

        self.getContainer().find('[data-block]').each(function() {
            let label = $(this).attr('data-block');

            if('LBL_TABS' !== label) {
                self.hideBlock(label);
            }
        })
    },
    registerLabels() {
        let self = this;

        self.updateLabels();

        app.event.on('add.selected.fields', function() {
            self.updateLabels();
        });

        app.event.on('label.selected.fields', function() {
            self.updateLabels();
        });

        app.event.on('delete.selected.fields', function(event, element) {
            let field = element.find('.fieldLabel').text();

            $('.selectedLabels[data-label="' + field + '"]').remove();
        });
    },
    updateLabels() {
        const self = this;

        $('.selectedFields').each(function(index, element) {
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
        cloneElement.find('.fieldSum').attr('name', fieldName + '[sum]');
        cloneElement.find('.fieldAvg').attr('name', fieldName + '[avg]');
        cloneElement.find('.fieldMin').attr('name', fieldName + '[min]');
        cloneElement.find('.fieldMax').attr('name', fieldName + '[max]');
        cloneElement.attr('data-name', value);
        cloneElement.attr('data-label', display);
        selectedElement.append(cloneElement);
    },
    updateCalculations() {
        let self = this;

        $('.selectedFields').each(function(index, element) {
            self.updateCalculation($(element));
        });
    },
    registerCalculations() {
        let self = this;

        self.updateCalculations();

        app.event.on('add.selected.fields', function() {
            self.updateCalculations();
        });

        app.event.on('delete.selected.fields', function(event, element) {
            let field = element.find('[name="fields[]"]').val();

            $('.selectedCalculations[data-name="' + field + '"]').remove();
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

        $.each(fieldOptions[module], function(key, value) {
            let valueInfo = value.split('##'),
                group = valueInfo[0];

            if(prevGroup !== group) {
                html += '</optgroup><optgroup label="' + valueInfo[0] + '">';
            }

            html += '<option value="' + key + '">' + valueInfo[1] + '</option>';

            prevGroup = group;
        })

        html += '</optgroup>';

        selectField.html(html);
        selectField.trigger('change');
    },
    registerModalFieldEvents(modalContainer) {
        let self = this,
            containerElement = self.getFieldsContainer(),
            selectedElement = containerElement.find('.containerSelectedFields'),
            cloneHtml = containerElement.find('.containerCloneFields').html(),
            selectedFieldName = modalContainer.find('.selectedFieldName'),
            selectedSortBy = modalContainer.find('.selectedSortBy'),
            selectedFieldLabel = modalContainer.find('.selectedFieldLabel');

        modalContainer.on('click', '#selectFieldsButton', function() {
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

            let duplicateElement = self.getFieldsByName(value);

            if ((!self.getEditField() && duplicateElement.length) || (self.getEditField() && self.getEditField().val() !== duplicateElement.val())) {
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

        modalContainer.on('click', '.selectedAlign', function() {
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

            if(3 === editFieldInfo.length) {
                selectModules.val(editFieldInfo[0] + ':' + editFieldInfo[1]);
                selectModules.trigger('change');
            }

            if(sortBy[0]) {
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

        containerElement.on('click', '.openSelectFields', function() {
            self.setEditField(false);

            let modalContainer = self.getNewFieldModal();

            app.helper.showModal(modalContainer, {
                cb: function () {
                    self.registerSelectFields(modalContainer);
                }
            });
        });

        containerElement.on('click', '.editFieldSelected', function() {
            self.setEditField(self.getFieldsElement($(this)));

            let modalContainer = self.getNewFieldModal();

            app.helper.showModal(modalContainer, {
                cb: function () {
                    self.registerSelectFields(modalContainer);
                }
            });
        })

        containerElement.on('click', '.deleteSelected', function() {
            let element = $(this),
                selectedFields = self.getFieldsParent(element);

            selectedFields.remove();

            app.event.trigger('delete.selected.fields', selectedFields);
        });

        containerElement.on('click', '.moveSelected', function() {
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

        self.getFieldsContainer().on('click', '.sortSelected', function() {
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

        containerSelected.on('click', '.selectedSortDelete', function() {
           $(this).parents('.selectedSorts').remove();

           app.event.trigger('delete.selected.sorts');
        });

        app.event.on('add.selected.sorts', function() {
            self.retrieveSorts();
        });

        app.event.on('delete.selected.sorts', function() {
            self.retrieveSorts();
        });

        app.event.on('label.selected.fields', function (event, element, data) {
            self.getSortElement(data['field']).find('.fieldLabel').val(data['label']);
        });

        app.event.on('delete.selected.fields', function(event, element) {
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
        if(element.is('.selectedFields')) {
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
            self.retrieveFilterValues();

            return true;
        })
    },
    registerWidth() {
        let self = this;

        self.retrieveWidth();

        app.event.on('add.selected.fields', function () {
            self.retrieveWidth();
        });
    },
    retrieveWidth() {
        let self = this,
            container = self.getContainer(),
            containerSelected = container.find('.containerSelectedWidth'),
            cloneHtml = container.find('.containerCloneWidth').html(),
            fields = container.find('[name="fields[]"]');

        $.each(fields, function (index, element) {
            let value = $(element).val(),
                clone = $(cloneHtml),
                name = 'width[' + value + ']';

            clone.find('.fieldValue').attr('name', name);
            clone.data('field', value);
            clone.find('.fieldLabel').text(self.getFieldLabel(value));

            if (value && !containerSelected.find('[name="' + name + '"]').length) {
                containerSelected.append(clone);
            }
        });
    },
    updateWidthSelected(field, width) {
        this.retrieveWidth()
        this.getContainer().find('[name="width[' + field + ']"]').val(width);
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
