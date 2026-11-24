/** Defalto CRM. Licensed under the GNU AGPL v3 License. */
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
