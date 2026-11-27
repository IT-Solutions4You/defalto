/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o.
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */
Vtiger_Edit_Js('ProjectTask_Edit_Js', {}, {
    registerBasicEvents(form) {
        this._super(form);
        this.registerDateValidation(form);
    },

    getPopUpParams(container) {
        const params = this._super(container),
            project = this.getForm().find('[name="projectid"]').val();

        if (params.module === 'ProjectMilestone' && project) {
            params.related_parent_module = 'Project';
            params.related_parent_id = project;
        }

        return params;
    },

    getReferenceSearchParams(element) {
        const params = this._super(element),
            project = this.getForm().find('[name="projectid"]').val();

        if (params.search_module === 'ProjectMilestone' && project) {
            params.parent_module = 'Project';
            params.parent_id = project;
        }

        return params;
    },

    registerDateValidation(form) {
        if (form.data('projectTaskDateValidation')) {
            return;
        }

        const fields = form.find('[name="startdate"], [name="enddate"]'),
            instance = Date.now().toString(36) + '-' + Math.random().toString(36).slice(2),
            invalidate = () => {
                fields.removeData('previousValue');
            },
            validate = () => {
                invalidate();
                const validator = form.data('validator');

                if (validator) {
                    fields.filter(':visible').each(function () {
                        validator.element(this);
                    });
                }
            };
        form.data('projectTaskDateValidation', true);
        fields.each((index, element) => {
            jQuery(element).data('ruleRemote', this.getDateValidationOptions(form, element, instance));
        });
        form.off('.projectTaskDates')
            .on('input.projectTaskDates', '[name="startdate"], [name="enddate"]', invalidate)
            .on('change.projectTaskDates', '[name="startdate"], [name="enddate"], [name="projectid"], [name="milestoneid"]', validate)
            .on('Vtiger.PostReference.Selection.projectTaskDates Vtiger.Reference.Deselection.projectTaskDates',
                '[name="projectid"], [name="milestoneid"]', validate);
    },

    getDateValidationOptions(form, element, instance) {
        const data = {module: 'ProjectTask', action: 'ValidateDates', record: () => form.find('[name="record"]').val() || ''};

        for (const name of ['projectid', 'milestoneid', 'startdate', 'enddate']) {
            const field = form.find('[name="' + name + '"]');

            if (field.length) {
                data[name] = () => field.val();
            }
        }

        return {
            url: 'index.php', type: 'POST', dataType: 'json', data,
            timeout: 15000,
            port: 'projectTaskDates-' + instance + '-' + element.name,
            converters: {'text json': raw => {
                if (!form[0].isConnected) {
                    return false;
                }

                const response = JSON.parse(raw);

                return response.success ? response.result : response.error?.message || app.vtranslate('JS_ERROR');
            }},
            error: (xhr, status) => {
                if (status === 'abort') {
                    return;
                }

                const validator = form.data('validator');
                jQuery(element).removeData('previousValue');

                if (validator) {
                    if (form[0].isConnected) {
                        validator.invalid[element.name] = true;
                        validator.showErrors({[element.name]: app.vtranslate('JS_ERROR')});
                    }

                    validator.stopRequest(element, false);
                }
            }
        };
    }
});
