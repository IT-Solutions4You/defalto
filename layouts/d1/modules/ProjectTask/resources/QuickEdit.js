/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
ProjectTask_Edit_Js('ProjectTask_QuickEdit_Js', {}, {
    registerModal(form) {
        if (form.data('projectTaskQuickEditController')) {
            return;
        }

        form.data('projectTaskQuickEditController', this);
        this.setModuleName('ProjectTask');
        this.setEditViewContainer(form);
        this.formElement = form;
        this.registerBasicEvents(form);
    },

    getValidationMeta(form) {
        const fields = JSON.parse(form.find('.project-task-field-info').val() || '{}');

        return {field: {
            get(name, property) {
                return property === undefined ? fields[name] : fields[name]?.[property];
            },
            isMandatory(name) {
                return Boolean(fields[name]?.mandatory);
            },
            getType(name) {
                return fields[name]?.type || false;
            }
        }};
    }
});
