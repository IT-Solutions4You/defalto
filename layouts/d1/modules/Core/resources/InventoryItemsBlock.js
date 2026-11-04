/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_InventoryItemsBlock_Js */
Vtiger_RelatedBlock_Js('Vtiger_InventoryItemsBlock_Js', {}, {
    advancedFilter: false,
    registerEvents() {
        if (this.getForm().length) {
            this.registerCKEditor();
            this.registerSortableRelatedFields();
            this.registerSubmit();
            this.registerColumnsChange();
            vtUtils.registerNumberFormating(this.getForm());
        }

        this.registerListEvents();
    },
    getEditUrl() {
        const self = this,
            params = {
                module: self.getModuleName(),
                view: 'InventoryItemsBlock',
                mode: 'edit',
            };

        return 'index.php?' + $.param(params);
    },
    registerSortableRelatedFields() {
        let relatedFieldsSelect = this.getFormElement('related_fields_select'),
            relatedFields = this.getFormElement('related_fields');

        if (!relatedFieldsSelect.length) {
            return;
        }

        vtUtils.makeSelect2ElementSortable(relatedFieldsSelect, relatedFields);
    },
    registerSubmit() {
        const self = this;

        self.getForm().on('submit', function (e) {
            return true;
        });
    },
    registerListEvents() {
        const self = this,
            container = self.getContainer();

        container.on('change', '.inventoryBlockModule', function () {
            const sourceModule = $(this).val(),
                listUrl = $(this).data('list-url');

            window.location.href = listUrl + (sourceModule ? '&source_module=' + encodeURIComponent(sourceModule) : '');
        });

        container.on('click', '.createInventoryBlock', function () {
            const sourceModule = container.find('.inventoryBlockModule').val();

            if (sourceModule) {
                window.location.href = $(this).data('create-url') + '&source_module=' + encodeURIComponent(sourceModule);
            }
        });

        container.on('click', '.deleteInventoryBlock', function () {
            const button = $(this);

            app.helper.showConfirmationBox({message: button.data('message')}).then(function () {
                const params = {
                    module: button.data('module'),
                    view: 'InventoryItemsBlock',
                    mode: 'delete',
                    record: button.data('record'),
                };

                app.request.post({data: params}).then(function (error, response) {
                    if (!error && response && response.success) {
                        button.closest('tr').remove();
                        app.helper.showSuccessNotification({message: button.data('success')});
                    }
                });
            });
        });
    },
    createTable(module, fields) {
        let upperCaseModule = module.toUpperCase(),
            table = '<div><style>.ibTable {} .ibTr {} .ibTh {} .ibTd {}</style><table class="ibTable">'

        //table header
        table += '<tr class="ibTr">'

        $.each(fields, function (index, element) {
            if(element) {
                table += '<th class="ibTh">%IB_' + upperCaseModule + '_' + element.toUpperCase() + '%</th>';
            }
        });

        table += '</tr>'
        //table header end
        //table data
        table += '<tr class="ibTr"><td class="ibTd" colspan="' + fields.length + '">#INVENTORY_BLOCK_START#</td></tr>'
        table += '<tr>'

        $.each(fields, function (index, element) {
            if(element) {
                table += '<td class="ibTd">$IB_' + upperCaseModule + '_' + element.toUpperCase() + '$</td>';
            }
        });

        table += '</tr>'
        table += '<tr class="ibTr"><td class="ibTd" colspan="' + fields.length + '">#INVENTORY_BLOCK_END#</td></tr>'
        //table data end
        table += '</table></div>';

        console.log(table) ;

        return table;
    },
})
