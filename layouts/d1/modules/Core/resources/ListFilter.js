/** Bootstrap list filter editor. Applied state always comes from the rendered list. */
jQuery.Class('Vtiger_ListFilter_Js', {}, {
    init: function (list) {
        this.list = list;
        this.editing = null;
        this.pending = false;
    },

    getBar: function () {
        return this.list.getListViewContainer().find('.listFilterBar');
    },

    getFields: function () {
        return JSON.parse(this.getBar().find('.listFilterFields').val() || '{}');
    },

    getParams: function () {
        return JSON.parse(this.getBar().find('.listFilterState').val() || '[]');
    },

    initializeSelects: function () {
        const bar = this.getBar();

        bar.find('select').each(function () {
            const select = jQuery(this);

            if (!select.data('select2')) {
                vtUtils.showSelect2ElementView(select, {
                    width: '100%',
                    dropdownParent: select.parent().addClass('listFilterSelectContainer')
                });
            }
        });
    },

    updateEditorPosition: function () {
        const toggle = this.getBar().find('.listFilterAdd')[0];

        if (toggle) {
            bootstrap.Dropdown.getInstance(toggle)?.update();
        }
    },

    isEmptyOperator: function (operator) {
        return operator === 'y' || operator === 'ny';
    },

    isDateField: function (field) {
        return field.type === 'date' || field.type === 'datetime';
    },

    isCalendarClick: function (event) {
        const click = event.clickEvent,
            path = click?.composedPath?.() || [];

        // Month navigation replaces the clicked node before Bootstrap handles
        // the bubbling click; its original event path still contains the calendar.
        return path.some(function (node) {
            return node.id === 'ui-datepicker-div' || node.classList?.contains('ui-timepicker-wrapper');
        }) || jQuery(click?.target).closest('#ui-datepicker-div, .ui-timepicker-wrapper').length > 0;
    },

    hasValueOptions: function (field) {
        return ['picklist', 'multipicklist', 'owner', 'ownergroup', 'currencyList', 'boolean', 'documentsFolder'].includes(field.type) || Object.keys(field.values).length > 0;
    },

    isMultipleValueField: function (field) {
        return field.type !== 'boolean' && field.type !== 'currencyList';
    },

    isEditableCondition: function (field, condition) {
        return !!field && Object.prototype.hasOwnProperty.call(field.operators, condition[1]);
    },

    isValidDateValue: function (input) {
        const values = input.val().split(','),
            isRange = input.data('calendarType') === 'range',
            format = input.data('dateFormat').replace('yyyy', 'yy');

        if (values.length !== (isRange ? 2 : 1)) {
            return false;
        }

        try {
            const dates = values.map(function (value) {
                return jQuery.datepicker.parseDate(format, value.trim());
            });

            return dates.every(Boolean) && (!isRange || dates[0] <= dates[1]);
        } catch (error) {
            return false;
        }
    },

    getConditionLabel: function (condition, fields) {
        const field = fields[condition[0]],
            values = field ? field.values : {},
            value = String(condition[2]).split(',').map(function (item) {
                return Object.prototype.hasOwnProperty.call(values, item) ? values[item] : item;
            }).join(', ');

        return (field ? field.label : condition[0]) + ' ' +
            (field && field.operators[condition[1]] ? field.operators[condition[1]] : condition[1]) +
            (this.isEmptyOperator(condition[1]) ? '' : ': ' + value);
    },

    showFilterChips: function () {
        const self = this,
            bar = self.getBar(),
            chips = bar.find('.listFilterChips').empty(),
            fields = self.getFields(),
            labels = bar.find('.listFilterLabels').data(),
            params = self.getParams();
        let count = 0;

        params.forEach(function (group, groupIndex) {
            group.forEach(function (condition, index) {
                const text = self.getConditionLabel(condition, fields),
                    chip = jQuery('<div>', {class: 'btn-group btn-group-sm', role: 'group'}),
                    edit = jQuery('<button>', {type: 'button', class: 'btn btn-outline-primary listFilterEdit', title: labels.edit + ': ' + text}).text(text),
                    remove = jQuery('<button>', {type: 'button', class: 'btn btn-outline-primary listFilterRemove flex-grow-0', 'aria-label': labels.remove + ': ' + text});

                if (groupIndex === 1 && index === 0) {
                    chips.append(jQuery('<span>', {class: 'align-self-center'}).text((count ? labels.and + ' ' : '') + '('));
                } else if (groupIndex === 1) {
                    chips.append(jQuery('<span>', {class: 'align-self-center'}).text(labels.or));
                }

                edit.add(remove).attr({'data-group': groupIndex, 'data-index': index});
                edit.prop('disabled', !self.isEditableCondition(fields[condition[0]], condition));
                remove.append(jQuery('<i>', {class: 'fa fa-times', 'aria-hidden': 'true'}));
                chips.append(chip.append(edit, remove));
                count++;
            });

            if (groupIndex === 1 && group.length) {
                chips.append(jQuery('<span>', {class: 'align-self-center'}).text(')'));
            }
        });

        bar.find('.listFilterClear, .listFilterSave').toggleClass('d-none', count === 0);
        bar.find('.listFilterApplied').toggleClass('d-none', count === 0);
        const totalCount = count + Number(bar.attr('data-saved-count') || 0);

        bar.find('.listFilterCount').text(totalCount).toggleClass('d-none', totalCount === 0);
        bar.find('.listFilterAdd').toggleClass('text-primary', totalCount > 0).toggleClass('text-secondary', totalCount === 0);
        self.editing = null;
    },

    hideEditor: function () {
        const toggle = this.getBar().find('.listFilterAdd')[0];

        if (toggle) {
            bootstrap.Dropdown.getOrCreateInstance(toggle).hide();
            toggle.focus();
        }
    },

    showEditor: function (condition) {
        const bar = this.getBar();

        bar.find('.listFilterField').val(condition ? condition[0] : '').trigger('change.select2');
        this.updateOperators(condition);
        bootstrap.Dropdown.getOrCreateInstance(bar.find('.listFilterAdd')[0]).show();
        bar.find('.listFilterField').next('.select2-container').find('.select2-selection').trigger('focus');
    },

    updateField: function () {
        const name = this.getBar().find('.listFilterField').val(),
            field = this.getFields()[name],
            conditions = this.getParams()[0] || [],
            matches = [];

        if (!this.editing && field && field.type === 'owner') {
            conditions.forEach(function (condition, index) {
                if (condition[0] === name) {
                    matches.push(index);
                }
            });

            // Only reuse an unambiguous AND condition; OR conditions stay independent.
            if (matches.length === 1 && this.isEditableCondition(field, conditions[matches[0]])) {
                this.editing = [0, matches[0]];
                this.updateOperators(conditions[matches[0]]);
                return;
            }
        }

        this.updateOperators();
    },

    updateOperators: function (condition) {
        const bar = this.getBar(),
            field = this.getFields()[bar.find('.listFilterField').val()],
            select = bar.find('.listFilterOperator').empty();

        bar.find('.listFilterOperatorContainer').toggleClass('d-none', !field);
        select.prop('disabled', !field);

        if (field) {
            Object.entries(field.operators).forEach(function (entry) {
                select.append(new Option(entry[1], entry[0]));
            });

            if (condition) {
                select.val(condition[1]);
            } else if (this.isDateField(field)) {
                select.val('bw');
            } else if (field.operators.c) {
                select.val('c');
            }
        }

        select.trigger('change.select2');
        this.updateValue(condition ? String(condition[2]) : '');
        this.updateEditorPosition();
    },

    updateValue: function (value) {
        const self = this,
            bar = self.getBar(),
            field = self.getFields()[bar.find('.listFilterField').val()],
            operator = bar.find('.listFilterOperator').val(),
            holder = bar.find('.listFilterValueContainer'),
            labels = bar.find('.listFilterLabels').data();

        holder.find('select').each(function () {
            const select = jQuery(this);

            if (select.data('select2')) {
                select.select2('destroy');
            }
        });

        holder.find('.dateField').each(function () {
            jQuery(this).datepicker('destroy');
        });

        holder.find('.timepicker-default').timepicker('remove');

        holder.empty();

        if (!field || self.isEmptyOperator(operator)) {
            return;
        }

        holder.append(jQuery('<label>', {for: 'listFilterValue', class: 'form-label'}).text(labels.value));

        if (self.hasValueOptions(field)) {
            const select = jQuery('<select>', {id: 'listFilterValue', class: 'form-select listFilterValue', form: 'listFilterForm', multiple: self.isMultipleValueField(field), required: true}),
                selected = value.split(','),
                groups = field.valueGroups && field.valueGroups.length ? field.valueGroups : [{values: field.values}];

            if (self.isMultipleValueField(field) && operator !== 'e' && operator !== 'n') {
                select.attr('data-tags', 'true');
            }

            groups.forEach(function (group) {
                const parent = group.label ? jQuery('<optgroup>', {label: group.label}).appendTo(select) : select;

                Object.entries(group.values).forEach(function (entry) {
                    parent.append(new Option(entry[1], entry[0], false, selected.includes(String(entry[0]))));
                });
            });

            selected.forEach(function (item) {
                if (item && !Object.prototype.hasOwnProperty.call(field.values, item)) {
                    select.append(new Option(item, item, true, true));
                }
            });

            holder.append(select);
            self.initializeSelects();
        } else if (self.isDateField(field)) {
            self.showDateValue(holder, field, operator, value);
        } else if (field.type === 'time') {
            const values = value.split(','),
                count = operator === 'bw' ? 2 : 1;

            for (let index = 0; index < count; index++) {
                const group = jQuery('<div>', {class: 'input-group inputElement time'}),
                    input = jQuery('<input>', {
                        id: index === 0 ? 'listFilterValue' : 'listFilterValueEnd', type: 'text',
                        class: 'form-control timepicker-default listFilterValue', form: 'listFilterForm', required: true,
                        'data-format': bar.find('.listFilterHourFormat').val(), 'aria-label': labels.value
                    }).val(values[index] || ''),
                    icon = jQuery('<span>', {class: 'input-group-addon input-group-text'}).append(jQuery('<i>', {class: 'fa fa-clock-o', 'aria-hidden': 'true'}));

                holder.append(group.append(input, icon));
                vtUtils.registerEventForTimeFields(input);
            }
        } else {
            holder.append(jQuery('<input>', {id: 'listFilterValue', type: 'text', class: 'form-control listFilterValue', form: 'listFilterForm', required: true}).val(value));

            if (['reference', 'multireference'].includes(field.type) && Object.keys(field.referenceModules || {}).length) {
                self.showReferenceControls(holder, field);
            }
        }
    },

    showDateValue: function (holder, field, operator, value) {
        const info = jQuery.extend({}, field.info, {
                name: 'listFilterValue', value: app.htmlEncode(value),
                comparatorElementVal: operator, dateSpecificConditions: field.dateConditions
            }),
            model = Vtiger_Field_Js.getInstance(info, 'AdvanceFilter'),
            ui = jQuery(model.getUiTypeSpecificHtml());

        holder.append(ui);
        holder.find('input, select').attr('form', 'listFilterForm');
        holder.find('[name="listFilterValue"]').addClass('listFilterValue').attr('id', 'listFilterValue');
        holder.find('input:not([type="hidden"]), select').prop('required', true).addClass('form-control');
        holder.find('.dateField').attr('data-date-format', this.getBar().find('.listFilterDateFormat').val());

        if (model.getUiTypeModel()._specialDateComparator(operator) && operator !== 'lastperiod') {
            holder.find('.listFilterValue').attr({type: 'number', min: 0, step: 1});
        }

        vtUtils.registerEventForDateFields(holder.find('.dateField'));
        this.initializeSelects();
    },

    showReferenceControls: function (holder, field) {
        const modules = Object.entries(field.referenceModules),
            labels = this.getBar().find('.listFilterLabels').data(),
            group = jQuery('<div>', {class: 'input-group'}),
            input = holder.find('.listFilterValue'),
            button = jQuery('<button>', {type: 'button', class: 'btn btn-outline-secondary listFilterReferenceSelect', title: labels.search, 'aria-label': labels.search});

        if (modules.length > 1) {
            const select = jQuery('<select>', {class: 'form-select listFilterReferenceModule mb-2', form: 'listFilterForm', 'aria-label': labels.search});

            modules.forEach(function (entry) {
                select.append(new Option(entry[1], entry[0]));
            });

            holder.append(select);
        }

        button.append(jQuery('<i>', {class: 'fa fa-search', 'aria-hidden': 'true'}));
        holder.append(group.append(input, button));
        this.initializeSelects();
    },

    showReferencePopup: function () {
        const self = this,
            bar = self.getBar(),
            name = bar.find('.listFilterField').val(),
            field = self.getFields()[name],
            input = bar.find('.listFilterValue'),
            module = bar.find('.listFilterReferenceModule').val() || Object.keys(field.referenceModules || {})[0];

        if (!module) {
            return;
        }

        self.hideEditor();
        Vtiger_Popup_Js.getInstance().showPopup({
            module: module, view: 'Popup', src_module: field.sourceModule || self.list.getModuleName(), src_field: field.sourceField || name, multi_select: false
        }, function (data) {
            const record = Object.values(JSON.parse(data))[0];

            if (!record || !input[0].isConnected) {
                return;
            }

            // Reference filter conditions compare display names rather than record IDs.
            input.val(record.name).trigger('change');
        }, function (popup) {
            popup.one('hidden.bs.modal', function () {
                // Let the selection/dismiss click finish before reopening: Bootstrap's
                // document click handler would otherwise close the dropdown again.
                setTimeout(function () {
                    if (!input[0].isConnected) {
                        return;
                    }

                    bootstrap.Dropdown.getOrCreateInstance(bar.find('.listFilterAdd')[0]).show();
                    self.updateEditorPosition();
                    input.trigger('focus');
                }, 0);
            });
        });
    },

    updateList: function (params) {
        const self = this,
            bar = self.getBar(),
            buttons = bar.find('button:enabled'),
            labels = bar.find('.listFilterLabels').data();

        if (self.pending) {
            return;
        }

        self.hideEditor();
        self.pending = true;
        buttons.prop('disabled', true);
        self.list.loadListViewRecords({page: 1, search_params: JSON.stringify(params)}).then(function () {
            self.list.getDeSelectAllMsgDiv().trigger('click');
            buttons.prop('disabled', false);
            self.showEditor();
            app.helper.showSuccessNotification({message: labels.success});
        }, function () {
            app.helper.showErrorNotification({message: labels.error});

            buttons.prop('disabled', false);
            bootstrap.Dropdown.getOrCreateInstance(bar.find('.listFilterAdd')[0]).show();
        }).always(function () {
            self.pending = false;
            buttons.prop('disabled', false);
        });
    },

    showSaveFilter: function () {
        const self = this,
            labels = self.getBar().find('.listFilterLabels').data(),
            params = {
                module: 'CustomView', view: 'EditAjax', source_module: self.list.getModuleName(),
                source_viewname: self.list.getCurrentCvId(), list_search_params: JSON.stringify(self.getParams())
            };

        self.hideEditor();
        app.helper.showProgress();
        app.request.post({data: params}).then(function (error, data) {
            app.helper.hideProgress();

            if (error) {
                app.helper.showErrorNotification({message: error.message || labels.error});
                return;
            }

            new Vtiger_CustomView_Js().showCreateFilter(data);
        });
    },

    validateEditor: function () {
        const controls = this.getBar().find('input, select').filter(':enabled').get();

        for (const control of controls) {
            const input = jQuery(control);

            if (input.hasClass('dateField')) {
                control.setCustomValidity(this.isValidDateValue(input) ? '' : app.vtranslate('JS_PLEASE_ENTER_VALID_DATE'));
            }

            if (input.hasClass('timepicker-default')) {
                control.setCustomValidity(jQuery.validator.methods.time(input.val(), control) ? '' : app.vtranslate('JS_PLEASE_ENTER_VALID_VALUE'));
            }

            if (!control.checkValidity()) {
                const select = jQuery(control);

                if (select.data('select2')) {
                    select.select2('open');
                } else {
                    control.reportValidity();
                }

                return false;
            }
        }

        return true;
    },

    applyFilter: function () {
        const self = this,
            bar = self.getBar(),
            name = bar.find('.listFilterField').val(),
            field = self.getFields()[name],
            operator = bar.find('.listFilterOperator').val(),
            params = self.getParams();

        if (!self.validateEditor() || !field || !operator || self.pending) {
            return;
        }

        const inputs = bar.find('.listFilterValue'),
            values = bar.find('.listFilterValue').map(function () {
                return jQuery(this).val();
            }).get(),
            condition = [name, operator, values.join(',').trim()];

        if (!self.isEmptyOperator(operator) && !condition[2]) {
            inputs.first().val('').get(0).reportValidity();
            return;
        }

        if (self.editing) {
            params[self.editing[0]][self.editing[1]] = condition;
        } else {
            params[0] = params[0] || [];
            params[0].push(condition);
        }

        self.updateList(params);
    },

    registerEvents: function () {
        const self = this,
            container = self.list.getListViewContainer();

        container.on('shown.bs.dropdown', '.listFilterBar .dropdown', function () {
            self.initializeSelects();
            self.updateEditorPosition();
        });

        container.on('select2:open select2:close', '.listFilterBar select', function () {
            self.updateEditorPosition();
        });

        container.on('hide.bs.dropdown', '.listFilterBar .dropdown', function (event) {
            if (self.isCalendarClick(event)) {
                event.preventDefault();
                return;
            }

            self.getBar().find('.dateField').datepicker('hide');
            self.getBar().find('.timepicker-default').timepicker('hide');
            self.getBar().find('select').each(function () {
                const select = jQuery(this);

                if (select.data('select2')) {
                    select.select2('close');
                }
            });
        });

        container.on('click', '.listFilterAdd', function () {
            self.editing = null;
            self.getBar().find('.listFilterField').val('').trigger('change.select2');
            self.updateOperators();
        });

        container.on('change', '.listFilterField', function () {
            self.updateField();
        });

        container.on('change', '.listFilterOperator', function () {
            self.updateValue('');
            self.updateEditorPosition();
        });

        container.on('click', '.listFilterCancel', function () {
            self.hideEditor();
        });

        container.on('click', '.listFilterEdit', function (event) {
            event.stopPropagation();
            self.editing = [Number(this.dataset.group), Number(this.dataset.index)];
            self.showEditor(self.getParams()[self.editing[0]][self.editing[1]]);
        });

        container.on('click', '.listFilterRemove', function () {
            const params = self.getParams();

            params[Number(this.dataset.group)].splice(Number(this.dataset.index), 1);
            self.updateList(params);
        });

        container.on('click', '.listFilterClear', function () {
            self.updateList([]);
        });

        container.on('click', '.listFilterSave', function () {
            self.showSaveFilter();
        });

        container.on('click', '.listFilterSavedEdit', function () {
            self.hideEditor();
        });

        container.on('click', '.listFilterReferenceSelect', function (event) {
            event.preventDefault();
            self.showReferencePopup();
        });

        container.on('mousedown', '.listFilterApply, .listFilterCancel, .listFilterReferenceSelect', function (event) {
            // Select2's body handler would collapse the inline results and move
            // the button before mouseup, preventing its click from completing.
            event.stopPropagation();
        });

        container.on('click', '.listFilterApply', function (event) {
            event.preventDefault();
            self.applyFilter();
        });

        container.on('keydown', '.listFilterBar input.listFilterValue', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                self.applyFilter();
            }
        });

        container.on('submit', '.listFilterForm', function (event) {
            event.preventDefault();
            self.applyFilter();
        });

        self.showFilterChips();
    }
});
