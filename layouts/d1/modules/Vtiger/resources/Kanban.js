/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var Vtiger_Kanban_Js */
Vtiger_Index_Js('Vtiger_Kanban_Js', {}, {
    registerEvents: function () {
        this._super();
        this.registerContentHeight();
        this.retrieveRecords();
        this.registerDraggable();
        this.registerDroppable();
        this.registerQuickPreview();
        this.registerFilterChange();
        this.registerMoreRecords();
    },
    classDraggable: '.kb-draggable',
    classDroppable: '.kb-droppable',
    getContainer: function () {
        return jQuery('.kanbanFieldValuesContainer');
    },
    registerDraggable: function () {
        const self = this,
            container = self.getContainer();

        container.find(self.classDraggable).draggable({
            appendTo: '.kanbanFieldValuesContainer',
            revert: 'invalid',
            helper: 'clone',
            cursor: 'move',
            cursorAt: {
                top: 100,
                left: 100,
            },
            scroll: true,
            scrollSpeed: 100,
            scrollSensitivity: 100,
            drag: function (e, ui) {
                ui.helper.css({
                    width: '15%',
                });
            }
        });
    },
    registerDroppable: function () {
        let self = this,
            container = self.getContainer();

        container.find(self.classDroppable).droppable({
            accept: self.classDraggable,
            classes: {
                'ui-droppable-hover': 'bg-body'
            },
            drop: function (event, ui) {
                let currentBlock = jQuery(this),
                    picklistValue = currentBlock.find('.kb-content').data('picklist_value'),
                    draggedElement = jQuery(ui.draggable),
                    draggedtaskElement = draggedElement.find('.kb-task'),
                    draggedRecord = draggedtaskElement.data('record_id'),
                    draggedValue = draggedtaskElement.data('picklist_value'),
                    draggedField = draggedtaskElement.data('picklist_field');

                if (draggedRecord && draggedField && picklistValue && draggedValue !== picklistValue) {
                    draggedtaskElement.data('picklist_value', picklistValue);

                    let params = {
                        module: app.getModuleName(),
                        action: 'SaveAjax',
                        record: draggedRecord,
                        field: draggedField,
                        value: picklistValue,
                    }

                    app.helper.showProgress();
                    app.request.post({data: params}).then(function (error, data) {
                        app.helper.hideProgress();

                        if (!error) {
                            let fieldName = self.getFieldName();

                            if (picklistValue === data[fieldName]['value']) {
                                currentBlock.find('.kb-content').append(draggedElement);

                                self.updateRecord(draggedElement, data);
                                self.updateFieldValuesCount(picklistValue, draggedValue);
                                self.registerMoreButtonVisibility();
                            }
                        }
                    });
                }
            }
        });
    },
    updateRecord: function (recordElement, data) {
        let self = this,
            fieldName = self.getFieldName();

        recordElement.attr('data-picklist_value', data[fieldName]['value']);
        recordElement.find('.kb-record-value').each(function () {
            let element = $(this),
                name = element.attr('data-name'),
                value = data[name]['display_value'] ?? data[name];

            element.html(value);
        });
    },
    updateFieldValuesCount: function (valueUp, valueDown) {
        let self = this,
            countUp = self.getFieldValueCountElement(valueUp),
            countUpValue = parseFloat(countUp.text()) + 1,
            countDown = self.getFieldValueCountElement(valueDown),
            countDownValue = parseFloat(countDown.text()) - 1;

        countUp.text(countUpValue);
        countDown.text(countDownValue);
    },
    getFieldValueElement: function (value) {
        return this.getContainer().find('.kb-content[data-picklist_value="' + value + '"]').closest('.kb-container');
    },
    getFieldValueCountElement: function (value) {
        return this.getFieldValueElement(value).find('.kb-value-count');
    },
    registerQuickPreview: function () {
        let self = this;

        self.getContainer().on('click', '.quickPreview', function (e) {
            let element = $(this),
                recordId = element.attr('href').replace('#', '');

            Vtiger_Index_Js.getInstance().showQuickPreviewForId(recordId, app.getModuleName(), app.getAppName());
        });

        self.getContainer().on('click', '.kb-record-value a', function (e) {
            e.preventDefault();

            let element = $(this),
                href = element.attr('href'),
                data = href ? app.convertUrlToDataParams(href) : null;

            if (data && data['record'] && data['module']) {
                Vtiger_Index_Js.getInstance().showQuickPreviewForId(data['record'], data['module'], app.getAppName());
            } else {
                window.location.href = href
            }
        });
    },
    getFieldName: function () {
        return this.getContainer().find('.kb-records-field').val();
    },
    getKanbanId: function () {
        return this.getContainer().find('.kb-records-id').val();
    },
    getNewRecord: function (data) {
        let self = this,
            clone = $('.kb-record-clone').clone(true, true),
            fieldName = self.getFieldName();

        clone.removeClass('kb-record-clone');
        clone.find('.kb-task')
            .attr('data-record_id', data['id'])
            .attr('data-picklist_field', fieldName)
            .attr('data-picklist_value', data['data'][fieldName]);
        clone.find('.kb-title').text(data['name']);
        clone.find('.kb-detail-link').attr('href', data['detail_url']);
        clone.find('.kb-edit-link').attr('href', data['edit_url']);

        let image = data['image'];

        if ('User' === image) {
            clone.find('.kb-user-icon').removeClass('hide');
        } else if ('Group' === image) {
            clone.find('.kb-group-icon').removeClass('hide');
        } else {
            clone.find('.kb-user-image').attr('src', image).removeClass('hide');
        }

        let headers = '';

        $.each(data['headers'], function (headerName, headerInfo) {
            let headerClone = clone.find('.kb-header-clone').clone(true, true);

            headerClone.removeClass('kb-header-clone');
            headerClone.attr('data-name', headerName);
            headerClone.attr('title', headerInfo['label']);
            headerClone.html(headerInfo['display_value']);

            headers += headerClone[0].outerHTML;
        });

        clone.find('.kb-headers').html(headers);

        return clone;
    },
    setNewRecord: function (recordInfo) {
        let self = this,
            element = self.getNewRecord(recordInfo),
            fieldName = self.getFieldName(),
            contentElement = $('.kb-content[data-picklist_value="' + recordInfo['data'][fieldName] + '"]');

        contentElement.append(element[0].outerHTML);
    },
    retrieveRecords: function () {
        let self = this,
            recordsInfo = $('.kb-records-info').text()

        if (recordsInfo) {
            recordsInfo = JSON.parse(recordsInfo)
        }

        $.each(recordsInfo, function (index, recordInfo) {
            self.setNewRecord(recordInfo);
        });
    },
    registerFilterChange: function () {
        const self = this;

        $('.kanbanFilterContainer').on('change', '.select2', function () {
            $('.kb-content').empty();

            let params = {
                module: app.getModuleName(),
                view: 'Kanban',
                mode: 'getRecords',
                view_name: self.getCustomView(),
                assigned_user: self.getAssignedUser(),
                field: self.getFieldName(),
                record: self.getKanbanId(),
            };

            app.request.post({data: params}).then(function (error, data) {
                if (!error && data['records_info']) {
                    $.each(data['records_info'], function (index, record_info) {
                        self.setNewRecord(record_info);
                    });

                    self.registerDraggable();
                }
            });
        })
    },
    getFilterContainer: function () {
        return $('.kanbanFilterContainer');
    },
    getCustomView: function () {
        return this.getFilterContainer().find('#custom_view').select2('val');
    },
    getAssignedUser: function () {
        return this.getFilterContainer().find('#assigned_user').select2('val');
    },
    registerMoreRecords: function () {
        let self = this;

        self.registerMoreButtonVisibility();
        self.getContainer().on('click', '.kb-more-records', function () {
            let contentElement = $(this).parents('.kb-container').find('.kb-content'),
                listPage = parseInt(contentElement.attr('data-list_page')) + 1;

            contentElement.attr('data-list_page', listPage);

            let params = {
                module: app.getModuleName(),
                view: 'Kanban',
                mode: 'getRecords',
                record: self.getKanbanId(),
                view_name: self.getCustomView(),
                view_page: listPage,
                assigned_user: self.getAssignedUser(),
                field: self.getFieldName(),
                field_values: contentElement.attr('data-picklist_value'),
            };

            app.request.post({data: params}).then(function (error, data) {
                if (!error && data['records_info']) {
                    $.each(data['records_info'], function (index, record_info) {
                        self.setNewRecord(record_info);
                    });

                    if (!data['records_info'] || 0 === data['records_info'].length) {
                        self.hideMoreButton(contentElement);
                    }
                    
                    self.registerMoreButtonVisibility();
                    self.registerDraggable();
                }
            });
        });
    },
    registerMoreButtonVisibility: function () {
        let self = this;

        self.getContainer().find('.kb-content').each(function () {
            let contentElement = $(this),
                taskElement = contentElement.find('.kb-task'),
                fieldValue = taskElement.attr('data-picklist_value'),
                loadedRecordsCount = taskElement.length,
                recordsCountText = parseInt(self.getFieldValueCountElement(fieldValue).text()),
                recordsCount = isNaN(recordsCountText) ? 0 : recordsCountText;

            if (recordsCount <= loadedRecordsCount) {
                self.hideMoreButton(contentElement);
            }
        });
    },
    hideMoreButton: function (contentElement) {
        contentElement.next('.kb-footer').find('.kb-more-records').hide();
    },
    registerContentHeight: function () {
        let self = this;

        self.getContainer().find('.kb-content').each(function () {
            let element = $(this);

            element.css({'max-height': element.outerHeight()})
        });
    },
})