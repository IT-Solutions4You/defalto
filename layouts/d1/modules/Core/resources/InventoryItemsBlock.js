/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
        this.registerSortableRelatedFields();
        this.registerSubmit();
        this.registerColumnsChange();
        vtUtils.registerReplaceCommaWithDot(this.getForm());
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
    registerColumnsChange() {
        let self = this,
            relatedModule = self.getFormElement('related_module').val();

        self.getForm().on('change', '#relateFieldsSelect', function () {
            let relatedFields = self.getFormElement('related_fields').val();

            if (relatedFields) {
                relatedFields = relatedFields.split(';');
            }

            let table = self.createTable(relatedModule, relatedFields)

            CKEDITOR.instances['content'].setData(table)
        });
    },
    createTable(module, fields) {
        let upperCaseModule = module.toUpperCase(),
            table = '<table style="border: 1px solid #000; border-collapse: collapse;">'

        //table header
        table += '<tr>'

        $.each(fields, function (index, element) {
            if(element) {
                table += '<td><b>%IB_' + upperCaseModule + '_' + element.toUpperCase() + '%</b></td>';
            }
        });

        table += '</tr>'
        //table header end
        //table data
        table += '<tr><td colspan="' + fields.length + '">#INVENTORY_BLOCK_START#</td></tr>'
        table += '<tr>'

        $.each(fields, function (index, element) {
            if(element) {
                table += '<td>$IB_' + upperCaseModule + '_' + element.toUpperCase() + '$</td>';
            }
        });

        table += '</tr>'
        table += '<tr><td colspan="' + fields.length + '">#INVENTORY_BLOCK_END#</td></tr>'
        //table data end
        table += '</table>';

        return table;
    },
})