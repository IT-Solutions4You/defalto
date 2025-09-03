/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
/** @var Vtiger_RelatedBlock_Js */
Vtiger_Index_Js('Vtiger_RelatedBlock_Js', {}, {
    advancedFilter: false,
    getContainer() {
        return $('.containerRelatedBlockEdit');
    },
    getForm() {
        return $('.formRelatedBlockEdit');
    },
    getFormElement(name) {
        return this.getForm().find('[name="' + name + '"]');
    },
    getFormValue(name) {
        return this.getFormElement(name).val();
    },
    getModuleName() {
        return this.getFormValue('module');
    },
    getEditUrl() {
        const self = this,
            params = {
                module: self.getModuleName(),
                view: 'RelatedBlock',
                mode: 'edit',
                related_module: self.getFormValue('related_module'),
            };

        return 'index.php?' + $.param(params);
    },
    registerEvents() {
        this.registerCKEditor();
        this.registerChangeRelatedModule()
        this.registerSortableRelatedFields();
        this.registerFilterConditions();
        this.registerSubmit();
        this.registerColumnsChange();
        vtUtils.registerReplaceCommaWithDot(this.getForm());
    },
    registerCKEditor() {
        let ckeditor = new Vtiger_CkEditor_Js()
        ckeditor.loadCkEditor($('#content'), {height: '40vh'});
    },
    registerChangeRelatedModule() {
        const self = this;

        self.getContainer().on('change', '[name="related_module"]', function () {
            window.location.href = self.getEditUrl();
        });
    },
    registerSortableRelatedFields() {
        let relatedFieldsSelect = this.getFormElement('related_fields_select'),
            relatedFields = this.getFormElement('related_fields');

        if (!relatedFieldsSelect.length) {
            return;
        }

        vtUtils.makeSelect2ElementSortable(relatedFieldsSelect, relatedFields);
    },
    registerFilterConditions() {
        let self = this,
            filterContainer = self.getForm().find('.filterContainer');

        self.advancedFilter = Vtiger_AdvanceFilter_Js.getInstance(filterContainer)
    },
    registerSubmit() {
        const self = this;

        self.getForm().on('submit', function (e) {
            let filters = JSON.stringify(self.advancedFilter.getValues());

            self.getFormElement('filters').text(filters)

            return true;
        })
    },
    registerColumnsChange: function () {
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
            table += '<td><b>%RB_' + upperCaseModule + '_' + element.toUpperCase() + '%</b></td>';
        });

        table += '</tr>'
        //table header end
        //table data
        table += '<tr><td colspan="' + fields.length + '">#RELATED_BLOCK_START#</td></tr>'
        table += '<tr>'

        $.each(fields, function (index, element) {
            table += '<td>$RB_' + upperCaseModule + '_' + element.toUpperCase() + '$</td>';
        });

        table += '</tr>'
        table += '<tr><td colspan="' + fields.length + '">#RELATED_BLOCK_END#</td></tr>'
        //table data end
        table += '</table>';

        return table;
    },
})