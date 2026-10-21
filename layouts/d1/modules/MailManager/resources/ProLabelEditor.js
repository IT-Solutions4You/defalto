/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

/**
 * Email labels editor (Pro/partials/LabelsTable.tpl + LabelModal.tpl) — the
 * admin AI settings edit the global labels, My Settings the user's own;
 * they differ only in the save/delete actions passed to init()
 */
/** @var MailManager_ProLabelEditor_Js */
Vtiger.Class('MailManager_ProLabelEditor_Js', {}, {

    DEFAULT_COLOR: '#eff3fe',

    /** Luminance (0-255) below which a chip gets a light text — as in the Label model */
    DARK_COLOR_LUMINANCE: 150,

    editor: null,
    modal: null,
    options: null,
    strings: null,

    /**
     * @param {jQuery} editor .its4you-label-editor
     * @param {jQuery} modal .its4you-label-modal
     * @param {Object} options {module, parent, saveAction, deleteAction,
     *                          extra: Object — sent with every request}
     */
    init: function (editor, modal, options) {
        this.editor = editor;
        this.modal = modal;
        this.options = options;
        this.strings = editor.data('strings') || {};

        if (editor.length && modal.length) {
            this.registerTableActions();
            this.registerModal();
        }
    },

    /**
     * @param {string} key
     * @return {string}
     */
    text: function (key) {
        return this.strings[key] || key;
    },

    /**
     * @param {string} value
     * @return {string}
     */
    escapeHtml: function (value) {
        // quotes too — the value goes into attributes as well
        return jQuery('<div></div>').text(value === null || typeof value === 'undefined' ? '' : String(value)).html()
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    },

    /**
     * Text color readable on a label color — the same rule as
     * Settings_MailManager_Label_Model::getTextColor()
     *
     * @param {string} color '#rrggbb'
     * @return {string}
     */
    textColor: function (color) {
        let match = /^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(String(color || '').trim());
        if (!match) {
            return '#444444';
        }

        return 0.299 * parseInt(match[1], 16) + 0.587 * parseInt(match[2], 16) + 0.114 * parseInt(match[3], 16)
            < this.DARK_COLOR_LUMINANCE ? '#ffffff' : '#444444';
    },

    /**
     * @param {Object} params action specific request data
     * @return {Object} full request data
     */
    buildParams: function (params) {
        let data = {module: this.options.module};
        if (this.options.parent) {
            data.parent = this.options.parent;
        }

        return jQuery.extend(data, this.options.extra || {}, params);
    },

    /**
     * Opens the modal in add or edit mode
     *
     * @param {Object} label {labelid, name, color, ai_description, suggested_module, active, workspace}
     */
    openModal: function (label) {
        let modal = this.modal,
            title = modal.find('.its4you-label-modal-title'),
            color = label.color || this.DEFAULT_COLOR;

        title.text(label.labelid > 0 ? title.data('edit') : title.data('add'));
        modal.find('input[name="labelid"]').val(label.labelid || 0);
        modal.find('input[name="name"]').val(label.name || '');
        modal.find('textarea[name="ai_description"]').val(label.ai_description || '');
        modal.find('input[name="workspace"]').prop('checked', parseInt(label.workspace, 10) === 1);
        modal.find('select[name="suggested_module"]').val(label.suggested_module || '');
        modal.find('input[name="auto_action"]').prop('checked', parseInt(label.auto, 10) === 1);
        this.toggleAutoAction();
        modal.find('input[name="active"]').prop('checked', label.active !== 0 && label.active !== '0');
        modal.find('input[name="selectedColor"]').val(color);
        // pre-select the matching palette swatch; the full picker stays folded
        modal.find('.its4you-color-swatch').removeClass('selected')
            .filter('[data-color="' + String(color).toLowerCase() + '"]').addClass('selected');
        this.updatePreview();
        modal.modal('show');
    },

    /**
     * The preview chip under the palette — the label as it will look with
     * the name and the color picked so far
     */
    updatePreview: function () {
        let modal = this.modal,
            chip = modal.find('.its4you-label-preview-chip'),
            color = modal.find('input[name="selectedColor"]').val() || this.DEFAULT_COLOR;

        chip.text(String(modal.find('input[name="name"]').val() || '').trim() || chip.data('placeholder'))
            .css({background: color, color: this.textColor(color)});
    },

    /**
     * "Perform automatically" belongs to the cleanup actions only (Move to
     * Spam / Trash) — another choice hides and clears it
     */
    toggleAutoAction: function () {
        let modal = this.modal,
            isCleanup = String(modal.find('select[name="suggested_module"]').val() || '').indexOf('action:') === 0;

        modal.find('.its4you-auto-action-row').toggleClass('hide', !isCleanup);
        if (!isCleanup) {
            modal.find('input[name="auto_action"]').prop('checked', false);
        }
    },

    /**
     * Builds/updates one table row from the saved label
     *
     * @param {Object} label saved row incl. action_label from the save action
     */
    renderRow: function (label) {
        let self = this,
            table = self.editor.find('.its4you-labels-table tbody'),
            activeIcon = parseInt(label.active, 10) === 1 ? '<i class="fa-solid fa-check its4you-active-check"></i>' : '',
            workspaceIcon = parseInt(label.workspace, 10) === 1 ? '<i class="fa-solid fa-briefcase its4you-active-check"></i>' : '',
            html = '<tr data-labelid="' + parseInt(label.labelid, 10) + '"'
                + ' data-name="' + self.escapeHtml(label.name) + '"'
                + ' data-color="' + self.escapeHtml(label.color) + '"'
                + ' data-description="' + self.escapeHtml(label.ai_description) + '"'
                + ' data-module="' + self.escapeHtml(label.suggested_module) + '"'
                + ' data-active="' + parseInt(label.active, 10) + '"'
                + ' data-workspace="' + parseInt(label.workspace, 10) + '"'
                + ' data-auto="' + (parseInt(label.auto_action, 10) || 0) + '">'
                + '<td>'
                + '<a href="javascript:void(0);" class="its4you-edit-label"><i class="fa-solid fa-pencil"></i></a>'
                + '&nbsp;&nbsp;'
                + '<a href="javascript:void(0);" class="its4you-delete-label"><i class="fa-solid fa-trash"></i></a>'
                + '</td>'
                + '<td><span class="its4you-label-chip" style="background: ' + self.escapeHtml(label.color)
                + '; color: ' + self.textColor(label.color) + ';">'
                + self.escapeHtml(label.name) + '</span></td>'
                + '<td class="its4you-muted">' + self.escapeHtml(label.ai_description) + '</td>'
                + '<td>' + (label.action_label ? self.escapeHtml(label.action_label) : '&mdash;')
                + (parseInt(label.auto_action, 10) === 1 ? '&nbsp;<i class="fa-solid fa-bolt its4you-active-check"></i>' : '')
                + '</td>'
                + '<td class="text-center">' + workspaceIcon + '</td>'
                + '<td class="text-center">' + activeIcon + '</td>'
                + '</tr>',
            existing = table.find('tr[data-labelid="' + parseInt(label.labelid, 10) + '"]');

        table.find('.its4you-no-labels').remove();
        if (existing.length) {
            existing.replaceWith(html);
        } else {
            table.append(html);
        }
    },

    /**
     * Add / edit / delete on the table
     */
    registerTableActions: function () {
        let self = this,
            editor = self.editor;

        editor.on('click', '.its4you-add-label', function () {
            self.openModal({labelid: 0});
        });

        editor.on('click', '.its4you-edit-label', function () {
            let row = jQuery(this).closest('tr');
            self.openModal({
                labelid: row.data('labelid'),
                name: row.data('name'),
                color: row.data('color'),
                ai_description: row.data('description'),
                suggested_module: row.data('module'),
                active: row.data('active'),
                workspace: row.data('workspace'),
                auto: row.data('auto')
            });
        });

        editor.on('click', '.its4you-delete-label', function () {
            let row = jQuery(this).closest('tr'),
                message = self.text('JS_DELETE_LABEL_CONFIRM').replace('%s', app.htmlEncode(String(row.data('name'))));

            app.helper.showConfirmationBox({message: message}).then(function () {
                app.request.post({data: self.buildParams({
                    action: self.options.deleteAction,
                    labelid: row.data('labelid')
                })}).then(function (err, data) {
                    if (err || !data || !data.success) {
                        app.helper.showErrorNotification({
                            message: app.htmlEncode(data && data.message) || self.text('JS_SAVE_ERROR')
                        });
                        return;
                    }
                    row.remove();
                    app.helper.showSuccessNotification({message: self.text('JS_LABEL_DELETED')});
                });
            });
        });
    },

    /**
     * Color palette/picker and save — bound to the modal element, the
     * modal lives outside the page container
     */
    registerModal: function () {
        let self = this,
            modal = self.modal,
            colorInput = modal.find('input[name="selectedColor"]');

        modal.on('input', 'input[name="name"]', function () {
            self.updatePreview();
        });

        modal.on('change', 'select[name="suggested_module"]', function () {
            self.toggleAutoAction();
        });

        modal.on('click', '.its4you-color-swatch', function () {
            let swatch = jQuery(this);
            colorInput.val(swatch.data('color'));
            modal.find('.its4you-color-swatch').removeClass('selected');
            swatch.addClass('selected');
            self.updatePreview();
        });

        modal.on('click', '.its4you-save-label', function () {
            let button = jQuery(this),
                params = self.buildParams({
                    action: self.options.saveAction,
                    labelid: modal.find('input[name="labelid"]').val(),
                    name: modal.find('input[name="name"]').val().trim(),
                    color: colorInput.val(),
                    ai_description: modal.find('textarea[name="ai_description"]').val().trim(),
                    suggested_module: modal.find('select[name="suggested_module"]').val(),
                    active: modal.find('input[name="active"]').is(':checked') ? '1' : '0',
                    workspace: modal.find('input[name="workspace"]').is(':checked') ? '1' : '0',
                    auto_action: modal.find('input[name="auto_action"]').is(':checked') ? '1' : '0'
                });

            if (!params.name) {
                app.helper.showErrorNotification({message: self.text('JS_FILL_REQUIRED')});
                return;
            }

            button.prop('disabled', true);
            app.request.post({data: params}).then(function (err, data) {
                button.prop('disabled', false);
                if (err || !data || !data.success) {
                    app.helper.showErrorNotification({
                        message: app.htmlEncode(data && data.message) || self.text('JS_SAVE_ERROR')
                    });
                    return;
                }
                self.renderRow(data.label);
                modal.modal('hide');
                app.helper.showSuccessNotification({message: self.text('JS_LABEL_SAVED')});
            });
        });
    }
});

/**
 * Learned label rules (Pro/partials/LabelRulesTable.tpl) — only deletion; the
 * rules come from manual label changes
 */
/** @var MailManager_ProLabelRuleList_Js */
Vtiger.Class('MailManager_ProLabelRuleList_Js', {}, {

    /**
     * @param {jQuery} list .its4you-label-rules
     * @param {Object} options {module, parent, deleteAction}
     */
    init: function (list, options) {
        if (!list.length) {
            return;
        }

        list.on('click', '.its4you-delete-labelrule', function () {
            let row = jQuery(this).closest('tr'),
                data = {module: options.module, action: options.deleteAction, ruleid: row.data('ruleid')};

            if (options.parent) {
                data.parent = options.parent;
            }
            app.helper.showConfirmationBox({message: list.data('confirm')}).then(function () {
                app.request.post({data: data}).then(function (err, response) {
                    if (err || !response || !response.success) {
                        app.helper.showErrorNotification({message: app.vtranslate('JS_FAILED_TO_SAVE')});
                        return;
                    }
                    row.remove();
                    app.helper.showSuccessNotification({message: list.data('deleted')});
                });
            });
        });
    }
});
