/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

Vtiger.Class('Settings_Vtiger_SummaryWidgets_Js', {}, {
    container: false,
    orderSaving: false,
    orderBeforeSort: false,

    init: function () {
        this.addComponents();
    },

    addComponents: function () {
        this.addComponent('Vtiger_Index_Js');
    },

    getContainer: function () {
        if (this.container === false) {
            this.container = jQuery('#summaryWidgetsSettings');
        }

        return this.container;
    },

    getRequestData: function (mode) {
        return {
            module: app.getModuleName(),
            parent: app.getParentModuleName(),
            action: 'SummaryWidgetsAjax',
            mode: mode,
            sourceModule: this.getContainer().data('source-module')
        };
    },

    updateEmptyStates: function () {
        this.getContainer().find('.summaryWidgetsColumn').each(function () {
            const column = jQuery(this),
                hasWidgets = column.children('.summaryWidgetSettingItem').length > 0;

            column.children('.summaryWidgetsEmpty').toggleClass('d-none', hasWidgets);
        });
    },

    addWidgetToLayout: function (widget) {
        const container = this.getContainer(),
            sequence = Number(widget.sequence),
            columnName = sequence % 2 === 0 ? 'left' : 'right',
            column = container.find('.summaryWidgetsColumn[data-column="' + columnName + '"]'),
            item = jQuery('<div>', {
                class: 'summaryWidgetSettingItem border rounded bg-body mb-2'
            }).attr({
                'data-link-id': widget.linkId,
                'data-widget-label': widget.label,
                'data-sequence': sequence
            }),
            row = jQuery('<div>', {class: 'row align-items-center g-0'}),
            dragColumn = jQuery('<div>', {class: 'col-auto'}),
            dragButton = jQuery('<button>', {
                type: 'button',
                class: 'btn text-secondary summaryWidgetDragHandle',
                title: container.data('move-label')
            }).append(jQuery('<i>', {class: 'fa-solid fa-grip-vertical'})),
            labelColumn = jQuery('<div>', {class: 'col overflow-hidden'}),
            label = jQuery('<span>', {
                class: 'summaryWidgetSettingLabel d-block text-truncate py-2',
                text: widget.title
            }),
            editColumn = jQuery('<div>', {class: 'col-auto'}),
            editButton = jQuery('<button>', {
                type: 'button',
                class: 'btn text-secondary editSummaryWidget',
                title: container.data('edit-label')
            }).append(jQuery('<i>', {class: 'fa fa-pencil'})),
            removeColumn = jQuery('<div>', {class: 'col-auto'}),
            removeButton = jQuery('<button>', {
                type: 'button',
                class: 'btn text-secondary removeSummaryWidget',
                title: container.data('remove-label')
            }).append(jQuery('<i>', {class: 'fa fa-trash-o'})),
            nextItem = column.children('.summaryWidgetSettingItem').filter(function () {
                return Number(jQuery(this).data('sequence')) > sequence;
            }).first();

        dragColumn.append(dragButton);
        labelColumn.append(label);
        editColumn.append(editButton);
        removeColumn.append(removeButton);
        row.append(dragColumn, labelColumn);

        if (widget.editable) {
            row.append(editColumn);
        }

        row.append(removeColumn);
        item.append(row);

        if (nextItem.length) {
            item.insertBefore(nextItem);
        } else {
            item.insertBefore(column.children('.summaryWidgetsEmpty'));
        }

        this.updateEmptyStates();
    },

    updateWidgetInLayout: function (widget) {
        const item = this.getContainer().find('.summaryWidgetSettingItem').filter(function () {
            return Number(jQuery(this).data('link-id')) === Number(widget.linkId);
        });

        item.attr('data-widget-label', widget.label).data('widget-label', widget.label);
        item.find('.summaryWidgetSettingLabel').text(widget.title);
    },

    removeAvailableWidget: function (widgetLabel) {
        const container = this.getContainer(),
            menu = container.find('.summaryWidgetAddMenu'),
            item = menu.find('.addSummaryWidget').filter(function () {
                return String(jQuery(this).data('widget-label')) === String(widgetLabel);
            }).closest('.summaryWidgetAvailableItem');

        item.remove();
        menu.find('.summaryWidgetNoAvailableItem').toggleClass(
            'd-none',
            menu.find('.summaryWidgetAvailableItem').length > 0
        );
    },

    updateWidgetSequences: function () {
        this.getContainer().find('.summaryWidgetsColumn').each(function () {
            const column = jQuery(this),
                offset = column.data('column') === 'left' ? 0 : 1;

            column.children('.summaryWidgetSettingItem').each(function (index) {
                const sequence = index * 2 + offset;

                jQuery(this).attr('data-sequence', sequence).data('sequence', sequence);
            });
        });
    },

    getWidgetOrder: function () {
        const columns = this.getContainer().find('.summaryWidgetsColumn');

        return {
            left: columns.filter('[data-column="left"]').children('.summaryWidgetSettingItem').map(function () {
                return String(jQuery(this).data('link-id'));
            }).get(),
            right: columns.filter('[data-column="right"]').children('.summaryWidgetSettingItem').map(function () {
                return String(jQuery(this).data('link-id'));
            }).get()
        };
    },

    restoreWidgetOrder: function (order) {
        const thisInstance = this,
            container = thisInstance.getContainer(),
            itemsByLinkId = Object.create(null);

        container.find('.summaryWidgetSettingItem').each(function () {
            const item = jQuery(this);

            itemsByLinkId[String(item.data('link-id'))] = item;
        });

        jQuery.each(['left', 'right'], function (index, columnName) {
            const column = container.find('.summaryWidgetsColumn[data-column="' + columnName + '"]'),
                emptyState = column.children('.summaryWidgetsEmpty');

            jQuery.each(order[columnName] || [], function (itemIndex, linkId) {
                if (itemsByLinkId[linkId]) {
                    itemsByLinkId[linkId].insertBefore(emptyState);
                }
            });
        });

        thisInstance.updateEmptyStates();
        thisInstance.updateWidgetSequences();
    },

    saveOrder: function (previousOrder) {
        if (this.orderSaving) {
            return;
        }

        const thisInstance = this,
            container = thisInstance.getContainer(),
            columns = container.find('.summaryWidgetsColumn'),
            params = thisInstance.getRequestData('saveOrder');

        params.leftLinkIds = columns.filter('[data-column="left"]').children('.summaryWidgetSettingItem').map(function () {
            return jQuery(this).data('link-id');
        }).get();
        params.rightLinkIds = columns.filter('[data-column="right"]').children('.summaryWidgetSettingItem').map(function () {
            return jQuery(this).data('link-id');
        }).get();

        thisInstance.orderSaving = true;
        columns.sortable('disable');
        app.helper.showProgress();

        app.request.post({data: params}).then(function (err, data) {
            app.helper.hideProgress();
            thisInstance.orderSaving = false;
            columns.sortable('enable');

            if (err === null) {
                app.helper.showSuccessNotification({message: data.message});
            } else {
                if (previousOrder) {
                    thisInstance.restoreWidgetOrder(previousOrder);
                }

                app.helper.showErrorNotification({message: err.message});
            }

            thisInstance.orderBeforeSort = false;
        });
    },

    registerModuleChange: function () {
        this.getContainer().find('[name="sourceModule"]').on('change', function () {
            const sourceModule = jQuery(this).val();

            window.location.href = 'index.php?parent=Settings&module=Vtiger&view=SummaryWidgets&sourceModule=' + encodeURIComponent(sourceModule);
        });
    },

    registerAddWidget: function () {
        const thisInstance = this,
            container = thisInstance.getContainer();

        container.on('click', '.addSummaryWidget', function () {
            const button = jQuery(this),
                params = thisInstance.getRequestData('addWidget');

            params.widgetLabel = button.data('widget-label');

            if (!params.widgetLabel) {
                return;
            }

            button.prop('disabled', true);
            app.helper.showProgress();
            app.request.post({data: params}).then(function (err, data) {
                app.helper.hideProgress();

                if (err === null) {
                    thisInstance.addWidgetToLayout(data.widget);
                    thisInstance.removeAvailableWidget(params.widgetLabel);
                    app.helper.showSuccessNotification({message: data.message});
                } else {
                    button.prop('disabled', false);
                    app.helper.showErrorNotification({message: err.message});
                }
            });
        });
    },

    registerRemoveWidget: function () {
        const thisInstance = this,
            container = thisInstance.getContainer();

        container.on('click', '.removeSummaryWidget', function () {
            const button = jQuery(this),
                item = button.closest('.summaryWidgetSettingItem');

            app.helper.showConfirmationBox({message: container.data('remove-confirmation')}).then(function () {
                const params = thisInstance.getRequestData('removeWidget');

                params.linkId = item.data('link-id');
                button.prop('disabled', true);
                app.helper.showProgress();
                app.request.post({data: params}).then(function (err, data) {
                    app.helper.hideProgress();

                    if (err === null) {
                        const menu = container.find('.summaryWidgetAddMenu'),
                            widgetLabel = item.data('widget-label'),
                            widgetTitle = item.find('.summaryWidgetSettingLabel').text().trim();

                        if (data.available) {
                            if (!menu.find('.addSummaryWidget').filter(function () {
                                return String(jQuery(this).data('widget-label')) === String(widgetLabel);
                            }).length) {
                                const availableItem = jQuery('<li>', {
                                        class: 'summaryWidgetAvailableItem'
                                    }),
                                    availableButton = jQuery('<button>', {
                                        type: 'button',
                                        class: 'dropdown-item addSummaryWidget'
                                    }).attr('data-widget-label', widgetLabel),
                                    icon = jQuery('<i>', {
                                        class: 'fa-solid fa-puzzle-piece me-2 text-secondary',
                                        'aria-hidden': 'true'
                                    }),
                                    label = jQuery('<span>', {text: widgetTitle});

                                availableButton.append(icon, label);
                                availableItem.append(availableButton);
                                availableItem.insertBefore(menu.find('.summaryWidgetAvailableDivider'));
                                menu.find('.summaryWidgetNoAvailableItem').addClass('d-none');
                            }
                        }

                        item.remove();
                        thisInstance.updateEmptyStates();
                        app.helper.showSuccessNotification({message: data.message});
                    } else {
                        button.prop('disabled', false);
                        app.helper.showErrorNotification({message: err.message});
                    }
                });
            });
        });
    },

    addGroupedSelectOptions: function (selectElement, optionGroups) {
        jQuery.each(optionGroups, function (group, options) {
            const optionGroup = jQuery('<optgroup>').attr('label', group);

            jQuery.each(options, function (value, label) {
                optionGroup.append(new Option(label, value));
            });
            selectElement.append(optionGroup);
        });
    },

    populateListWidgetOptions: function (form, data) {
        const fields = form.find('#summaryWidgetFields'),
            filters = form.find('[name="filterId"]'),
            referenceFields = form.find('[name="referenceField"]'),
            relations = form.find('[name="relationId"]'),
            initialFilter = filters.attr('data-initial-value'),
            initialFieldsValue = fields.attr('data-initial-value'),
            initialReferenceField = referenceFields.attr('data-initial-value'),
            initialRelation = relations.attr('data-initial-value');

        fields.empty();
        this.addGroupedSelectOptions(fields, data.fieldGroups);

        filters.empty();
        this.addGroupedSelectOptions(filters, data.filters);

        referenceFields.empty();
        this.addGroupedSelectOptions(referenceFields, data.referenceFieldGroups);

        relations.empty();
        jQuery.each(data.relations, function (value, label) {
            relations.append(new Option(label, value));
        });

        if (!referenceFields.find('option').length && relations.children().length) {
            form.find('[name="relationType"]').val('related_list');
        }

        if (Number(form.find('[name="linkId"]').val()) > 0 && initialFilter !== undefined) {
            const initialFields = initialFieldsValue ? initialFieldsValue.split(',') : [];

            filters.val(initialFilter);
            fields.val(initialFields);
            referenceFields.val(initialReferenceField);
            relations.val(initialRelation);
        }

        filters.add(fields).add(referenceFields).add(relations).removeAttr('data-initial-value');

        this.registerSelect2FieldSorting(form);
        fields.add(filters).add(referenceFields).add(relations).trigger('change');
        this.updateRelationFields(form);
    },

    registerSelect2FieldSorting: function (form) {
        const fields = form.find('#summaryWidgetFields');

        if (fields.data('summary-widget-sortable')) {
            return;
        }

        vtUtils.makeSelect2ElementSortable(fields, form.find('#summaryWidgetFieldsOrder'));
        fields.data('summary-widget-sortable', true);
    },

    loadListWidgetOptions: function (form) {
        const thisInstance = this,
            params = thisInstance.getRequestData('getListWidgetOptions');

        params.targetModule = form.find('[name="targetModule"]').val();
        form.find(':submit').prop('disabled', true);
        app.helper.showProgress();

        app.request.post({data: params}).then(function (err, data) {
            app.helper.hideProgress();

            if (err === null) {
                thisInstance.populateListWidgetOptions(form, data);
            } else {
                app.helper.showErrorNotification({message: err.message});
            }
        });
    },

    updateRelationFields: function (form) {
        const relationType = form.find('[name="relationType"]').val(),
            isReference = relationType === 'reference',
            referenceField = form.find('[name="referenceField"]'),
            relation = form.find('[name="relationId"]'),
            hasSelection = isReference ? referenceField.find('option').length > 0 : relation.children().length > 0;

        form.find('.summaryWidgetReferenceRow').toggleClass('d-none', !isReference);
        form.find('.summaryWidgetRelatedListRow').toggleClass('d-none', isReference);
        referenceField.prop('required', isReference);
        relation.prop('required', !isReference);
        form.find('.summaryWidgetNoRelation').toggleClass('d-none', hasSelection);
        form.find(':submit').prop('disabled', !hasSelection);
    },

    registerListWidgetForm: function (form) {
        const thisInstance = this;

        form.find('[name="targetModule"]').on('change', function () {
            thisInstance.loadListWidgetOptions(form);
        });
        form.find('[name="relationType"]').on('change', function () {
            thisInstance.updateRelationFields(form);
        });
        form.on('submit', function (event) {
            const params = thisInstance.getRequestData('saveListWidget'),
                formData = form.serializeArray();

            event.preventDefault();
            jQuery.each(formData, function (index, item) {
                if (item.name === 'fields[]') {
                    params.fields = params.fields || [];
                    params.fields.push(item.value);
                } else {
                    params[item.name] = item.value;
                }
            });

            form.find(':submit').prop('disabled', true);
            app.helper.showProgress();
            app.request.post({data: params}).then(function (err, data) {
                app.helper.hideProgress();

                if (err === null) {
                    app.helper.hideModal();

                    if (Number(params.linkId) > 0) {
                        thisInstance.updateWidgetInLayout(data.widget);
                    } else {
                        thisInstance.addWidgetToLayout(data.widget);
                    }

                    app.helper.showSuccessNotification({message: data.message});
                } else {
                    form.find(':submit').prop('disabled', false);
                    app.helper.showErrorNotification({message: err.message});
                }
            });
        });

        thisInstance.loadListWidgetOptions(form);
    },

    registerCreateListWidget: function () {
        const thisInstance = this,
            container = thisInstance.getContainer();

        container.find('.createSummaryListWidget').on('click', function () {
            thisInstance.showListWidgetForm();
        });
    },

    showListWidgetForm: function (linkId) {
        const thisInstance = this,
            params = {
                module: app.getModuleName(),
                parent: app.getParentModuleName(),
                view: 'SummaryWidgetEdit',
                sourceModule: thisInstance.getContainer().data('source-module')
            };

        if (linkId) {
            params.linkId = linkId;
        }

        app.request.post({data: params}).then(function (err, data) {
            if (err === null) {
                app.helper.showModal(data);
                thisInstance.registerListWidgetForm(jQuery('#summaryListWidgetForm'));
            } else {
                app.helper.showErrorNotification({message: err.message});
            }
        });
    },

    registerEditListWidget: function () {
        const thisInstance = this;

        thisInstance.getContainer().on('click', '.editSummaryWidget', function () {
            thisInstance.showListWidgetForm(jQuery(this).closest('.summaryWidgetSettingItem').data('link-id'));
        });
    },

    registerSortable: function () {
        const thisInstance = this,
            columns = thisInstance.getContainer().find('.summaryWidgetsColumn');

        columns.sortable({
            connectWith: '.summaryWidgetsColumn',
            items: '> .summaryWidgetSettingItem',
            handle: '.summaryWidgetDragHandle',
            cancel: '.removeSummaryWidget, .editSummaryWidget',
            placeholder: 'summaryWidgetSortablePlaceholder',
            tolerance: 'pointer',
            start: function () {
                thisInstance.orderBeforeSort = thisInstance.getWidgetOrder();
            },
            stop: function () {
                thisInstance.updateEmptyStates();
                thisInstance.updateWidgetSequences();
                thisInstance.saveOrder(thisInstance.orderBeforeSort);
            }
        });
    },

    registerEvents: function () {
        const settingsInstance = new Settings_Vtiger_Index_Js();

        settingsInstance.registerBasicSettingsEvents();
        this.registerModuleChange();
        this.registerAddWidget();
        this.registerRemoveWidget();
        this.registerCreateListWidget();
        this.registerEditListWidget();
        this.registerSortable();
        this.updateEmptyStates();
    }
});
