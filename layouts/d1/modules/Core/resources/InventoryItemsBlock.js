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
        this.registerCKEditor();
        this.registerSortableRelatedFields();
        this.registerSubmit();
        this.registerColumnsChange();
        vtUtils.registerNumberFormating(this.getForm());
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