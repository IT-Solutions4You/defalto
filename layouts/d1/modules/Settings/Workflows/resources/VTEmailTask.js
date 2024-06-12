/**
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/** @var VTEmailTask */
Vtiger.Class('VTEmailTask', {
    instance: false,
    getInstance: function () {
        if (!this.instance) {
            this.instance = new VTEmailTask();
        }

        return this.instance;
    },
}, {
    getContainer: function() {
        return $('#VtEmailTaskContainer');
    },
    registerEvents: function() {
        this.registerTemplateChange();
        this.registerVariables();
        this.registerSortableSelect2Element();
    },
    registerSortableSelect2Element: function () {
        const self = this,
            selectElement = $('#pdf_template_select'),
            valueElement = $('#pdf_template');

        if(selectElement.length && selectElement.length) {
            vtUtils.showSelect2ElementView(selectElement);

            selectElement.one('select2-loaded', function () {
                vtUtils.makeSelect2ElementSortable(selectElement, valueElement);
            });
        }
    },
    registerVariables: function () {
        const self = this,
            container = $('.customTemplateContainer');


        container.on('click', '.task_variables_subject', function () {
            let inputElement = $('#subject');

            inputElement.val(inputElement.val() + self.getVariable());
        });

        container.on('click', '.task_variables_body', function () {
            if ('undefined' !== typeof CKEDITOR.instances['content']) {
                CKEDITOR.instances['content'].insertHtml(self.getVariable());
            } else if (textareaElement) {
                let textareaElement = $('#content');

                textareaElement.text(textareaElement.text() + self.getVariable());
            }
        });
    },
    getVariable: function() {
        return $('#task_variables').val();
    },
    registerTemplateChange: function () {
        const self = this;

        $('#task_template').on('change', function() {
            self.updateTemplateFields();
        });

        self.updateTemplateFields();
    },
    updateTemplateFields: function () {
        let templateContainer = $('.templateContainer'),
            customTemplateContainer = $('.customTemplateContainer'),
            hideElement = customTemplateContainer,
            showElement = templateContainer;

        if ('custom_template' === $('#task_template').val()) {
            hideElement = templateContainer;
            showElement = customTemplateContainer;
        }

        hideElement.addClass('hide');
        hideElement.find('[data-rule-required]').data('rule-required', false)
        showElement.removeClass('hide');
        showElement.find('[data-rule-required]').data('rule-required', true)
    }
});

$(function() {
    VTEmailTask.getInstance().registerEvents();
})